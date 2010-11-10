<?php
try {
    $request = sspmod_janus_REST_Utils::processRequest($_GET);

    if (is_object($request)) {
        $result = sspmod_janus_REST_Utils::callMethod($request);
        sspmod_janus_REST_Utils::sendResponse($result['status'], $result['data'], 'application/json');
    } else {
        sspmod_janus_REST_Utils::sendResponse(500);
    }
} catch(Exception $e) {
    sspmod_janus_REST_Utils::sendResponse(500);
}
