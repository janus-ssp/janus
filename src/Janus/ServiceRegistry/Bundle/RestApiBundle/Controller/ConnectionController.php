<?php

namespace Janus\ServiceRegistry\Bundle\RestApiBundle\Controller;

use Janus\ServiceRegistry\Connection\Metadata\MetadataDefinitionHelper;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\RouteRedirectView;

use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Janus\ServiceRegistry\Bundle\CoreBundle\Form\Type\ConnectionType;
use Janus\ServiceRegistry\Connection\ConnectionDtoCollection;
use Janus\ServiceRegistry\Connection\ConnectionDto;
use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Service\ConnectionService;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Rest controller for connections
 *
 * @package Janus\ServiceRegistryBundle\Controller
 */
class ConnectionController extends FOSRestController
{
    /**
     * List all connections, this includes both Service providers and Identity providers.
     *
     * List all connections.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param Request $request
     * @return ConnectionDtoCollection
     */
    public function getConnectionsAction(Request $request)
    {
        /** @var SecurityContext $securityContext */
        $securityContext = $this->get('security.context');


        $filters = array();

        // If this user may not see all entities, apply a filter.
        if (!$securityContext->isGranted('All Entities')) {
            /** @var TokenInterface $token */
            $token = $securityContext->getToken();
            $filters['allowedUserId'] = $token->getUsername();
        }

        $name = $request->get('name', false);
        if ($name) {
            $filters['name'] = $name;
        }

        /** @var ConnectionService $connectionService */
        $connectionService = $this->get('connection_service');
        $connectionsRevisions = $connectionService->findLatestRevisionsWithFilters(
            $filters,
            $request->get('sortBy', null),
            $request->get('sortOrder', 'DESC')
        );

        $connections = new ConnectionDtoCollection();
        foreach ($connectionsRevisions as $connectionRevision) {
            $connection = $connectionRevision->toDto($this->get('janus_config'));

            // Strip out Manipulation code, ARP attributes and metadata for brevity.
            $connection->setManipulationCode(null);
            $connection->setArpAttributes(array());
            $connection->removeMetadata();

            $connections->addConnection($connection);
        }

        return $connections;
    }

    /**
     * Get the latest revision of a single connection.
     *
     * @ApiDoc(
     *   resource = true,
     *   output = "\Janus\ServiceRegistry\Connection\ConnectionDto",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the connection is not found"
     *   }
     * )
     *
     * @ParamConverter("connectionRevision", options={"repository_method" = "findOneByConnectionId"})
     * @SecureParam(name="connectionRevision", permissions="access")
     *
     * @param Revision $connectionRevision Connection Revision
     *
     * @return ConnectionDto
     *
     * @throws NotFoundHttpException when connection not exist
     */
    public function getConnectionAction(Revision $connectionRevision)
    {
        $this->get('janus_logger')->info("Returning connection '{$connectionRevision->getConnection()->getId()}'");

        return $connectionRevision->toDto($this->get('janus_config'));
    }

    /**
     * Creates a new connection from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Janus\ServiceRegistryBundle\Form\Type\ConnectionType",
     *   statusCodes = {
     *     201 = "Returned when created",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Secure("Create new Entity")
     *
     * @param Request $request the request object
     *
     * @return FormTypeInterface|RouteRedirectView
     */
    public function postConnectionAction(Request $request)
    {
        $this->get('janus_logger')->info("Trying to create connection via POST");
        /** @var ConnectionService $connectionService */
        $connectionService = $this->get('connection_service');

        $connectionDto = $connectionService->createDefaultDto(
            $request->request->get('type')
        );

        return $this->saveRevision($connectionDto, $request);
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
     * @ParamConverter("connectionRevision", options={"repository_method" = "findOneByConnectionId"})
     * @todo this is a ridiculous right to demand here, but we use it because there is nothing better:
     * @Secure("Admin Tab")
     * @SecureParam(name="connectionRevision", permissions="access")
     *
     * @param Request $request
     * @param Revision $connectionRevision
     *
     * @return FormTypeInterface|RouteRedirectView
     *
     * @throws NotFoundHttpException when connection not exist
     */
    public function putConnectionAction(Revision $connectionRevision, Request $request)
    {
        $this->get('janus_logger')->info(
            "Trying to update connection '{$connectionRevision->getConnection()->getId()} via PUT'"
        );

        $connectionDto = $connectionRevision->toDto($this->get('janus_config'));

        return $this->saveRevision($connectionDto, $request);
    }

    /**
     * @param ConnectionDto $connectionDto
     * @param Request $request
     * @return array|RouteRedirectView
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Exception
     */
    private function saveRevision(ConnectionDto $connectionDto, Request $request)
    {
        $connectionDto->setArpAttributes(null);
        $form = $this->createForm(
            new ConnectionType($this->get('janus_config')),
            $connectionDto,
            array('csrf_protection' => false)
        );

        $form->submit($request, false);

        if (!$form->isValid()) {
            $this->get('janus_logger')->info("Creating revision failed due to invalid data");

            return array(
                'form' => $form
            );
        }

        try {
            /** @var ConnectionService $connectionService */
            $connectionService = $this->get('connection_service');
            $connection = $connectionService->save($connectionDto);

            if ($connection->getRevisionNr() == 0) {
                $this->get('janus_logger')->info(
                    "Connection '{$connection->getId()}' created"
                );
                $statusCode = Codes::HTTP_CREATED;
            } else {
                $this->get('janus_logger')->info(
                    "Connection '{$connection->getId()}' updated to revision '{$connection->getRevisionNr()}'"
                );
                /** HACK: because FOSRest does not allow us to return data with a 200 OK. */
                $statusCode = Codes::HTTP_CREATED;
            }

            $view = $this->routeRedirectView('get_connection', array('connection' => $connection->getId()), $statusCode);
            $view->setData($connection->createDto($this->get('janus_config')));
            return $view;
        }
        catch (\InvalidArgumentException $ex) {
            $this->get('janus_logger')->info("Creating revision failed, due to invalid data which was not catched by validation'");
            throw new BadRequestHttpException($ex->getMessage());
        } catch (\Exception $ex) {
            $this->get('janus_logger')->info("Creating revision failed, due to exception'");
            throw $ex;
        }
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
     * @ParamConverter("connectionRevision", options={"repository_method" = "findOneByConnectionId"})
     * @todo this is a ridiculous right to demand here, but we use it because there is nothing better:
     * @Secure("Admin Tab")
     * @SecureParam(name="connectionRevision", permissions="access")
     *
     * @param Revision  $connectionRevision Latest revision of the connection to be deleted.
     * @param Request   $request            HTTP Request object.
     *
     * @return RouteRedirectView
     *
     * @throws NotFoundHttpException when connection not exist
     */
    public function deleteConnectionAction(Revision $connectionRevision, Request $request)
    {
        /** @var ConnectionService $connectionService */
        $connectionService = $this->get('connection_service');
        $connectionService->deleteById($connectionRevision->getConnection()->getId());

        return $this->routeRedirectView('get_connections', array(), Codes::HTTP_NO_CONTENT);
    }

    /**
     * @return ConnectionService $connectionService
     */
    protected function getService()
    {
        return $this->get('connection_service');
    }
}
