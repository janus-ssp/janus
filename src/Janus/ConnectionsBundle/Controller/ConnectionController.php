<?php

namespace Janus\ConnectionsBundle\Controller;

use Janus\ConnectionsBundle\Form\ConnectionType;
use Janus\ConnectionsBundle\Model\Connection;
use Janus\ConnectionsBundle\Model\ConnectionCollection;

use FOS\RestBundle\Util\Codes;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\RouteRedirectView;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing connections.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many connections to return.")
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
        $session = $request->getSession();

        $offset = $paramFetcher->get('offset');
        $start = null == $offset ? 0 : $offset + 1;
        $limit = $paramFetcher->get('limit');

        $connections = $session->get(self::SESSION_CONTEXT_CONNECTION, array());
        $connections = array_slice($connections, $start, $limit, true);

        return new ConnectionCollection($connections, $offset, $limit);
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
     * @param Request $request the request object
     * @param int     $id      the connection id
     *
     * @return array
     *
     * @throws NotFoundHttpException when connection not exist
     */
    public function getConnectionAction(Request $request, $id)
    {
        $session = $request->getSession();
        $connections   = $session->get(self::SESSION_CONTEXT_CONNECTION);
        if (!isset($connections[$id])) {
            throw $this->createNotFoundException("Connection does not exist.");
        }

        return $connections[$id];
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
        $connections   = $session->get(self::SESSION_CONTEXT_CONNECTION);

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
     * @param Request $request the request object
     * @param int     $id      the connection id
     *
     * @return FormTypeInterface|RouteRedirectView
     *
     * @throws NotFoundHttpException when connection not exist
     */
    public function putConnectionsAction(Request $request, $id)
    {
        $session = $this->getRequest()->getSession();

        $connections   = $session->get(self::SESSION_CONTEXT_CONNECTION);
        if (!isset($connections[$id])) {
            $connection = new Connection();
            $connection->id = count($connections);
            $statusCode = Codes::HTTP_CREATED;
        } else {
            $connection = $connections[$id];
            $statusCode = Codes::HTTP_OK;
        }

        $form = $this->createForm(new ConnectionType(), $connection);

        $form->submit($request);
        if ($form->isValid()) {
            if (!isset($connection->secret)) {
                $connection->secret = base64_encode($this->get('security.secure_random')->nextBytes(64));
            }
            $connections[$id] = $connection;
            $session->set(self::SESSION_CONTEXT_CONNECTION, $connections);

            return $this->routeRedirectView('get_connections', array(), $statusCode);
        }

        return array(
            'form' => $form
        );
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
        $session = $request->getSession();

        $connections   = $session->get(self::SESSION_CONTEXT_CONNECTION);
        if (isset($connections[$id])) {
            unset($connections[$id]);
            $session->set(self::SESSION_CONTEXT_CONNECTION, $connections);
        }

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
