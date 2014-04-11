<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\RestApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\View\View;

use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Janus\ServiceRegistry\Bundle\CoreBundle\Form\Type\ConnectionType;
use Janus\ServiceRegistry\Bundle\CoreBundle\Model\ConnectionCollection;
use Janus\ServiceRegistry\Connection\ConnectionDto;
use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Service\ConnectionService;

use SimpleSAML_Configuration;
use Symfony\Component\Security\Core\SecurityContext;

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
     * @return array
     */
    public function getConnectionsAction()
    {
        /** @var SecurityContext $securityContext */
        $securityContext = $this->get('security.context');
        $filters = array();
        if (!$securityContext->isGranted('All Entities')) {
            $filters['allowedUserId'] = $securityContext->getToken()->getUsername();
        }

        /** @var ConnectionService $connectionService */
        $connectionService = $this->get('connection_service');
        $connectionRevisions = $connectionService->load($filters);

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

        $collection = new ConnectionCollection($connections);

        return $collection;
    }

    /**
     * Get a single connection.
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
     * @Annotations\View(templateVar="connection")
     *
     * @ParamConverter("connection", options={"repository_method" = "findOneByConnectionId"})
     * @SecureParam(name="connection", permissions="access")
     *
     * @param Revision    $connection      Connection
     *
     * @return View
     *
     * @throws NotFoundHttpException when connection not exist
     */
    public function getConnectionAction(Revision $connection)
    {
        $connectionId = $connection->getConnection()->getId();
        $connections[$connectionId] = $connection->toDto();
        $view = new View($connections[$connectionId]);

        $this->get('janus_logger')->info("Returned connection '{$connectionId}'");

        return $view;
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
     * @Annotations\View(
     *   template = "JanusServiceRegistryBundle:Connection:newConnection.html.twig",
     *   statusCode = Codes::HTTP_BAD_REQUEST
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
        $connectionDto = $connectionService->createDefaultDto($request->get('type'));

        return $this->createRevision($connectionDto, $request);
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

        $connectionDto = $connectionRevision->toDto();

        return $this->createRevision($connectionDto, $request);
    }

    /**
     * @param ConnectionDto $connectionDto
     * @param Request $request
     * @return array|View
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Exception
     */
    private function createRevision(ConnectionDto $connectionDto, Request $request)
    {
        $form = $this->createForm(
            new ConnectionType($this->get('janus_config')),
            $connectionDto,
            array('csrf_protection' => false)
        );

        $form->submit($request);

        if (!$form->isValid()) {
            $this->get('janus_logger')->info("Creating revision failed due to invalid data");

            return array(
                'form' => $form
            );
        }

        try {
            /** @var ConnectionService $connectionService */
            $connectionService = $this->get('connection_service');
            $connection = $connectionService->createFromDto($connectionDto);

            if ($connection->getRevisionNr() == 0) {
                $this->get('janus_logger')->info(
                    "Connection '{$connection->getId()}' created"
                );
                $statusCode = Codes::HTTP_CREATED;
            } else {
                $this->get('janus_logger')->info(
                    "Connection '{$connection->getId()}' updated to revision '{$connection->getRevisionNr()}'"
                );
                $statusCode = Codes::HTTP_OK;
            }

            $view = $this->routeRedirectView('get_connections', array(), $statusCode);
            $view->setData($connection);
            return $view;
        } // @todo Improve this with proper validation
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
}
