<?php

namespace Janus\ServiceRegistry\Bundle\RestApiBundle\Controller;

use Janus\ServiceRegistry\Entity\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\RouteRedirectView;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
     * List all connections divided per type e.g. saml20-idp or saml20-sp
     *
     * @ApiDoc(
     *   resource = true,
     *   output="array<array>",
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

        return $this->getService()->findWithFilters(
            $filters,
            $request->get('sortBy', null),
            $request->get('sortOrder', 'DESC')
        );
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
     * @param int $id
     *
     * @return ConnectionDto
     *
     * @throws NotFoundHttpException when connection not exist
     */
    public function getConnectionAction($id)
    {
        $connection = $this->getService()->findById($id);

        if (!$connection instanceof Connection) {
            throw $this->createNotFoundException("Unable to find Connection entity '{$id}'");
        }

        $connectionDto = $connection->createDto($this->get('connection.metadata.definition_helper'));

        $this->get('janus_logger')->info("Returning connection '{$id}'");

        return $connectionDto;
    }

    /**
     * Creates a new connection from the submitted data.
     *
     * @ApiDoc(
     *   input = "Janus\ServiceRegistry\Connection\ConnectionDto",
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

        $connectionDto = $this->getService()->createDefaultDto(
            $request->request->get('type')
        );

        return $this->saveRevision($connectionDto, $request);
    }

    /**
     * Update existing connection from the submitted data or create a new connection at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Janus\ServiceRegistry\Connection\ConnectionDto",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *   }
     * )
     *
     * @todo this is a ridiculous right to demand here, but we use it because there is nothing better:
     * @Secure("Admin Tab")
     *
     * @param int $id
     * @param Request $request
     *
     * @return FormTypeInterface|RouteRedirectView
     *
     * @throws NotFoundHttpException when connection not exist
     */
    public function putConnectionAction($id, Request $request)
    {
        $this->get('janus_logger')->info(
            "Trying to update connection '{$id} via PUT'"
        );

        $connection = $this->getService()->findById($id);

        if (!$connection instanceof Connection) {
            throw $this->createNotFoundException("Connection does not exist '{$id}'");
        }

        $connectionDto = $connection->createDto($this->get('connection.metadata.definition_helper'));

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

        /** @var FormInterface $form */
        $form = $this->createForm(
            $this->get('janus.form.type.connection'),
            $connectionDto,
            array()
        );
        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            $this->get('janus_logger')->info("Creating revision failed due to invalid data");

            return array(
                'form' => $form
            );
        }

        try {
            $connection = $this->getService()->save($connectionDto);

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

            $view = $this->routeRedirectView('get_connection', array('id' => $connection->getId()), $statusCode);
            $view->setData($connection->createDto($this->get('connection.metadata.definition_helper')));
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
     * @todo this is a ridiculous right to demand here, but we use it because there is nothing better:
     * @Secure("Admin Tab")
     *
     * @param integer  $id id of the connection to be deleted.
     *
     * @return RouteRedirectView
     *
     * @throws NotFoundHttpException when connection not exist
     */
    public function deleteConnectionAction($id)
    {
        $this->getService()->deleteById($id);

        return $this->routeRedirectView('get_connections', array(), Codes::HTTP_NO_CONTENT);
    }

    /**
     * @return ConnectionService $this->getService()
     */
    protected function getService()
    {
        return $this->get('connection_service');
    }
}
