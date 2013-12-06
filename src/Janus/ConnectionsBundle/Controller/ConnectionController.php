<?php

namespace Janus\ConnectionsBundle\Controller;

use Doctrine\ORM\NoResultException;

use Janus\ConnectionsBundle\Form\ConnectionType;
use Janus\ConnectionsBundle\Model\Connection;
use Janus\ConnectionsBundle\Model\ConnectionCollection;

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
 * @package Janus\ConnectionsBundle\Controller
 */
class ConnectionController extends FOSRestController
{
    const SESSION_CONTEXT_CONNECTION = 'connections';

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
        $connectionRevisions = \sspmod_janus_DiContainer::getInstance()->getConnectionService()->load(array("state" => "prodaccepted"), null, null);
        $connections = array();
        /** @var $connectionRevision \sspmod_janus_Model_Connection_Revision */
        foreach ($connectionRevisions as $connectionRevision) {
            $connection = $connectionRevision->toDto();
            // Manipulation code does not have to be in output
            $connection->setManipulationCode(null);
            $connection->setArpAttributes(null);
            $connections[] = $connection;
        }

        return new ConnectionCollection($connections);
    }

    /**
     * Get a single connection.
     *
     * @ApiDoc(
     *   resource = true,
     *   output = "Janus\ConnectionsBundle\Model\Connection",
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
        try {
            $connection = \sspmod_janus_DiContainer::getInstance()
                ->getEntityManager()
                ->getRepository('sspmod_janus_Model_Connection_Revision')
                ->getLatest($id);
        } catch (NoResultException $ex) {
            // @todo see if this can be done more neatly
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
        return $this->createForm(new ConnectionType());
    }

    /**
     * Creates a new connection from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Janus\ConnectionsBundle\Form\ConnectionType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template = "JanusConnectionsBundle:Connection:newConnection.html.twig",
     *   statusCode = Codes::HTTP_BAD_REQUEST
     * )
     *
     * @param Request $request the request object
     *
     * @return FormTypeInterface|RouteRedirectView
     */
    public function postConnectionsAction(Request $request)
    {
        $session = $this->getRequest()->getSession();
        $connections = $session->get(self::SESSION_CONTEXT_CONNECTION);

        $connection = new Connection();
        $connection->id = count($connections);
        $form = $this->createForm(new ConnectionType(), $connection);

        $form->submit($request);
        if ($form->isValid()) {
            $connection->secret = base64_encode($this->get('security.secure_random')->nextBytes(64));
            $connections[] = $connection;
            $session->set(self::SESSION_CONTEXT_CONNECTION, $connections);

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
    public function editConnectionsAction(Request $request, $id)
    {
        $session = $request->getSession();

        $connections = $session->get(self::SESSION_CONTEXT_CONNECTION);
        if (!isset($connections[$id])) {
            throw $this->createNotFoundException("Connection does not exist.");
        }

        $form = $this->createForm(new ConnectionType(), $connections[$id]);

        return $form;
    }

    /**
     * Update existing connection from the submitted data or create a new connection at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Janus\ConnectionsBundle\Form\ConnectionType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *   }
     * )
     *
     * @Annotations\View(
     *   template="JanusConnectionsBundle:Connection:editConnection.html.twig"
     * )
     *
     * @ParamConverter("dto", class="sspmod_janus_Model_Connection_Revision_Dto")
     *
     * @param sspmod_janus_Model_Connection_Revision_Dto $dto
     *
     * @return FormTypeInterface|RouteRedirectView
     *
     * @throws NotFoundHttpException when connection not exist
     */
    public function putConnectionsAction(\sspmod_janus_Model_Connection_Revision_Dto $dto)
    {
        $connectionService = \sspmod_janus_DiContainer::getInstance()->getConnectionService();
        $connection = $connectionService->createFromDto($dto);
        if ($connection->getRevisionNr() == 0) {
            $statusCode = Codes::HTTP_CREATED;
        } else {
            $statusCode = Codes::HTTP_OK;
        }

        return new View(null, $statusCode);
// @todo find out how to use this form code
//        $form = $this->createForm(new ConnectionType(), $connection);
//
//        $form->submit($request);
//        if ($form->isValid()) {
//            if (!isset($connection->secret)) {
//                $connection->secret = base64_encode($this->get('security.secure_random')->nextBytes(64));
//            }

//            return $this->routeRedirectView('get_connections', array(), $statusCode);
//        }
//
//        return array(
//            'form' => $form
//        );
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
    public function deleteConnectionsAction(Request $request, $id)
    {
        $connectionService = \sspmod_janus_DiContainer::getInstance()->getConnectionService();
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
    public function removeConnectionsAction(Request $request, $id)
    {
        return $this->deleteConnectionsAction($request, $id);
    }
}
