<?php
/**
 * This REST endpoint is deprecated, please use the 'rest-v2' endpoint.
 *
 * All source code beloging to the old REST endpoint is contained in the
 * 'Legacy' namespace.
 *
 * @deprecated by the v2 REST endpoint
 */
$request = sspmod_janus_REST_Legacy_Utils::processRequest($_GET);

if (is_object($request)) {
    $result = sspmod_janus_REST_Legacy_Utils::callMethod($request);
    sspmod_janus_REST_Legacy_Utils::sendResponse($result['status'], $result['data'], 'application/json');
} else {
    throw new Exception('Could not process Janus REST request');
}