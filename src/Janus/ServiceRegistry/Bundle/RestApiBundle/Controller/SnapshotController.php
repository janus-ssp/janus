<?php

namespace Janus\ServiceRegistry\Bundle\RestApiBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Janus\ServiceRegistry\Service\SnapshotService;


/**
 * Snapshot controller allows you to create / restore / delete data snapshots.
 *
 * Snapshots were created to support the integration tests with it's clean up.
 * As it can not be trusted to clean up properly (a test may be interrupted by failure or user input) it allows
 * the tests to detect an aborted previous run and restore the initial data.
 *
 * @package Janus\ServiceRegistryBundle\Controller
 */
class SnapshotController extends FOSRestController
{
    /**
     * @var SnapshotService
     */
    private $snapshotService;

    /**
     * Returns a 200 if a given snapshot exists or a 404 if it does not.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Snapshot exists!",
     *     404 = "Snapshot does not exist!",
     *     500 = "Error occurred"
     *   }
     * )
     *
     * @Annotations\View()
     *
     * @param int $id
     * @return array
     */
    public function getSnapshotAction($id)
    {
        if (!$this->snapshotService->find($id)) {
            return new Response('Did not find a snapshot with id: ' . $id, Codes::HTTP_NOT_FOUND);
        }

        return ;
    }

    /**
     * List all snapshots.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     500 = "Error occurred"
     *   }
     * )
     *
     * @Annotations\View()
     *
     * @return array
     */
    public function getSnapshotsAction()
    {
        return $this->snapshotService->findList();
    }

    /**
     * Create a new snapshot
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     500 = "Error occurred"
     *   }
     * )
     *
     * @Annotations\View()
     *
     * @return array
     */
    public function postSnapshotsAction()
    {
        $id = $this->snapshotService->create();

        $view = View::createRouteRedirect('get_snapshot', array('id' => $id), Codes::HTTP_CREATED);
        $view->setData($this->snapshotService->find($id));
        return $view;
    }

    /**
     * Restore a snapshot.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     500 = "Error occurred"
     *   }
     * )
     *
     * @Annotations\View()
     *
     * @param int $id
     *
     * @return array
     */
    public function postSnapshotsRestoreAction($id)
    {
        $this->snapshotService->restore($id);
        $this->snapshotService->delete($id);
        return true;
    }

    /**
     * Delete a snapshot.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     500 = "Error occurred"
     *   }
     * )
     *
     * @Annotations\View()
     *
     * @param int $id
     *
     * @return array
     */
    public function deleteSnapshotsAction($id)
    {
        $this->snapshotService->delete($id);
    }

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->snapshotService = $container->get('snapshot_service');
    }
}
