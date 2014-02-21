<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */
namespace Janus\ServiceRegistry\Bundle\GuiBundle\Controller;

use Janus\ServiceRegistry\Service\ConnectionService;
use Janus\ServiceRegistry\Entity\Connection\Revision;

use Janus\ServiceRegistryBundle\Form\Type\ConnectionType;
use Janus\ServiceRegistryBundle\Model\ConnectionCollection;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\View\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use SimpleSAML_Configuration;

/**
 * Gui controller for connections
 *
 * @package Janus\ServiceRegistryBundle\Controller
 * @todo remove all reference to FOS rest
 * @todo remove duplication with RestApiController
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
     *
     * @return array
     */
    public function getConnectionsAction(Request $request)
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

        $collection = new ConnectionCollection($connections);

        return $collection;
    }

    /**
     * @param int $id
     * @return Revision
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getLatestRevision($id)
    {
        /** @var ConnectionService $connectionService */
        $connectionService = $this->get('connection_service');
        $connectionRevision = $connectionService->getLatestRevision($id);
        if (!$connectionRevision instanceof Revision) {
            $this->get('janus_logger')->info("Connection '{$id}' was not found");
            throw $this->createNotFoundException("Connection does not exist.");
        }

        return $connectionRevision;
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
        $this->get('janus_logger')->info("Trying to get connection '{$id}'");

        $connection = $this->getLatestRevision($id);
        $connections[$id] = $connection->toDto();
        $view = new View($connections[$id]);

        $this->get('janus_logger')->info("Returned connection '{$id}'");

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
        $this->get('janus_logger')->info("Trying to show edit form for new connection");

        $dto = $this->get('connection_service')->createDefaultDto();

        /** @var SimpleSAML_Configuration $janusConfig */
        $janusConfig = $this->get('janus_config');

        $form = $this->createForm(new ConnectionType($janusConfig), $dto);

        $this->get('janus_logger')->info("Showing create form for new connection");

        return $form;
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
        $this->get('janus_logger')->info("Trying to show edit form for Connection '{$id}'");

        $connections[$id] = $this->getLatestRevision($id);
        /** @var SimpleSAML_Configuration $janusConfig */
        $janusConfig = $this->get('janus_config');
        $form = $this->createForm(new ConnectionType($janusConfig), $connections[$id]->toDto());

        $this->get('janus_logger')->info("Showing edit form for Connection '{$id}'");

        return $form;
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
        /** @var ConnectionService $connectionService */
        $connectionService = $this->get('connection_service');
        $connectionService->deleteById($id);

        return $this->routeRedirectView('get_connections', array(), Codes::HTTP_NO_CONTENT);
    }
}
