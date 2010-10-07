<?php
try {
    $request = sspmod_janus_REST_Utils::processRequest($_GET);

    $result = sspmod_janus_REST_Utils::callMethod($request);
    
    sspmod_janus_REST_Utils::sendResponse($result['status'], $result['data'], 'application/json');
} catch(Exception $e) {
    sspmod_janus_REST_Utils::sendResponse(500);
}
