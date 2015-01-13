<?php

namespace Janus\ServiceRegistry\Bundle\CoreBundle;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class JanusExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // do whatever tests you need - in this example I filter by path prefix
        $path = $event->getRequest()->getRequestUri();
        if (strpos($path, '/api/') === 0) {
            return;
        }

        $exception = $event->getException();
        $response = new JsonResponse($exception, 500);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        }

        // Send the modified response object to the event
        $event->setResponse($response);
    }
}
