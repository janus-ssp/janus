<?php

namespace Janus\ServiceRegistryBundle\Controller;

use Janus\ServiceRegistry\Service\ConnectionService;
use Janus\ServiceRegistryBundle\Form\Type\ConnectionType;
use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Connection\Dto;
use Janus\ServiceRegistryBundle\Model\ConnectionCollection;

use FOS\RestBundle\Util\Codes;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\View\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Rest controller for connections
 *
 * @package Janus\ServiceRegistryBundle\Controller
 */
class ConnectionController extends FOSRestController
{
    /**
     * List all connections.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\View()
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getConnectionsAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        $connectionRevisions = $this->get('connection_service')->load();
        $connections = array();
        /** @var $connectionRevision Revision */
        foreach ($connectionRevisions as $connectionRevision) {
            $connection = $connectionRevision->toDto();
            // @todo improve this with a view?
            // Manipulation code does not have to be in output
            $connection->setManipulationCode(null);
            $connection->setArpAttributes(null);
            $connections[$connection->getType()][$connection->getId()] = $connection;
        }

        return new ConnectionCollection($connections);
    }

    /**
     * Get a single connection.
     *
     * @ApiDoc(
     *   resource = true,
     *   output = "\Janus\ServiceRegistry\Connection\Dto",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the connection is not found"
     *   }
     * )
     *
     * @Annotations\View(templateVar="connection")
     *
     * @param int     $id      the connection id
     *
     * @return View
     *
     * @throws NotFoundHttpException when connection not exist
     */
    public function getConnectionAction($id)
    {
        $connectionService = $this->get('connection_service');
        $connection = $connectionService->getLatestRevision($id);
        if (!$connection instanceof Revision) {
            throw $this->createNotFoundException("Connection does not exist.");
        }
        $connections[$id] = $connection->toDto();
        $view = new View($connections[$id]);

        return $view;
    }

    /**
     * Presents the form to use to create a new connection.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\View()
     *
     * @return FormTypeInterface
     */
    public function newConnectionAction()
    {
        $dto = $this->createDefaultDto();

        /** @var \SimpleSAML_Configuration $janusConfig */
        $janusConfig = $this->get('janus_config');
        return $this->createForm(new ConnectionType($janusConfig), $dto);
    }

    /**
     * @return Dto
     */
    private function createDefaultDto()
    {
        $dto = new Dto();
        $dto->setState('testaccepted');
        $dto->setIsActive(true);
        $dto->setAllowAllEntities(true);

        return $dto;
    }

    /**
     * Creates a new connection from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Janus\ServiceRegistryBundle\Form\Type\ConnectionType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template = "JanusServiceRegistryBundle:Connection:newConnection.html.twig",
     *   statusCode = Codes::HTTP_BAD_REQUEST
     * )
     *
     * @param Request $request the request object
     *
     * @return FormTypeInterface|RouteRedirectView
     */
    public function postConnectionAction(Request $request)
    {
        $janusConfig = $this->get('janus_config');
        $connectionDto = $this->createDefaultDto();
        $form = $this->createForm(new ConnectionType($janusConfig), $connectionDto);

        $form->submit($request);
        if ($form->isValid()) {
// @todo fix secret checking?
//            if (!isset($connection->secret)) {
//                $connection->secret = base64_encode($this->get('security.secure_random')->nextBytes(64));
//            }
            $connectionService = $this->get('connection_service');
            $connectionService->createFromDto($connectionDto);

            return $this->routeRedirectView('get_connections');
        }

        return array(
            'form' => $form
        );
    }

    /**
     * Presents the form to use to update an existing connection.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes={
     *     200="Returned when successful",
     *     404={
     *       "Returned when the connection is not found",
     *     }
     *   }
     * )
     *
     * @Annotations\View()
     *
     * @param Request $request the request object
     * @param int     $id      the connection id
     *
     * @return FormTypeInterface
     *
     * @throws NotFoundHttpException when connection not exist
     */
    public function editConnectionAction(Request $request, $id)
    {
        $connectionService = $this->get('connection_service');
        $connections[$id] = $connectionService->getLatestRevision($id);
        if (!$connections[$id] instanceof Revision) {
            throw $this->createNotFoundException("Connection does not exist.");
        }

        $janusConfig = $this->get('janus_config');
        $form = $this->createForm(new ConnectionType($janusConfig), $connections[$id]->toDto());

        return $form;
    }

    /**
     * Update existing connection from the submitted data or create a new connection at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Janus\ServiceRegistryBundle\Form\Type\ConnectionType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *   }
     * )
     *
     * @Annotations\View(
     *   template="JanusServiceRegistryBundle:Connection:editConnection.html.twig"
     * )
     *
     * @param Request $request
     * @param int $id
     *
     * @return FormTypeInterface|RouteRedirectView
     *
     * @throws NotFoundHttpException when connection not exist
     */
    public function putConnectionAction(Request $request, $id)
    {
        /** @var ConnectionService $connectionService */
        $connectionService = $this->get('connection_service');
        $connectionRevision = $connectionService->getLatestRevision($id);
        if (!$connectionRevision instanceof Revision) {
            throw $this->createNotFoundException("Connection does not exist.");
        } else {
            $connectionDto = $connectionRevision->toDto();
        }

        $janusConfig = $this->get('janus_config');
        $form = $this->createForm(new ConnectionType($janusConfig), $connectionDto);

        $form->submit($request);
        if ($form->isValid()) {
// @todo fix secret checking?
//            if (!isset($connection->secret)) {
//                $connection->secret = base64_encode($this->get('security.secure_random')->nextBytes(64));
//            }

            $connection = $connectionService->createFromDto($connectionDto);
            if ($connection->getRevisionNr() == 0) {
                $statusCode = Codes::HTTP_CREATED;
            } else {
                $statusCode = Codes::HTTP_OK;
            }

            return $this->routeRedirectView('get_connections', array(), $statusCode);
        }

        return $form;
    }

    /**
     * Removes a connection.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes={
     *     204="Returned when successful",
     *   }
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the connection id
     *
     * @return RouteRedirectView
     *
     * @throws NotFoundHttpException when connection not exist
     */
    public function deleteConnectionAction(Request $request, $id)
    {
        $connectionService = $this->get('connection_service');
        $connectionService->deleteById($id);

        return $this->routeRedirectView('get_connections', array(), Codes::HTTP_NO_CONTENT);
    }

    /**
     * Removes a connection.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes={
     *     204="Returned when successful",
     *     404={
     *       "Returned when the connection is not found",
     *     }
     *   }
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the connection id
     *
     * @return RouteRedirectView
     */
    public function removeConnectionAction(Request $request, $id)
    {
        return $this->deleteConnectionAction($request, $id);
    }
}
