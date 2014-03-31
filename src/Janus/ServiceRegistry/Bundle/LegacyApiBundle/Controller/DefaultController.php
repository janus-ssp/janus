<?php

namespace Janus\ServiceRegistry\Bundle\LegacyApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use sspmod_janus_REST_Utils;
use sspmod_janus_REST_Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        // Check if correct request was made
        $request = sspmod_janus_REST_Utils::processRequest($_GET);
        if (!$request instanceof sspmod_janus_REST_Request) {
            throw new BadRequestHttpException('Could not process Janus REST request');
        }

        // Execute call and return result as json
        $result = sspmod_janus_REST_Utils::callMethod($request);
        $response = new Response(json_encode($result['data']));
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($result['status']);
        return $response;
    }
}
