<?php
/*
 * REST v2 SERVICE ENDPOINT
 *
 * Supported URL formats, rest-full URI's:
 *  module.php/janus/services/rest-v2/entity
 *  module.php/janus/services/rest-v2/entity?id=314
 *
 * URI by query parameter:
 *  module.php/janus/services/rest-v2/index.php?uri=/entity
 *  module.php/janus/services/rest-v2/index.php?uri=/entity&id=314
 */

// load configuration file
$config = SimpleSAML_Configuration::getConfig('module_janus.php')
    ->getConfigItem('rest-api');

// set path info if traling slash is omitted on root
if (!isset($_SERVER['PATH_INFO'])) {
    $_SERVER['PATH_INFO'] = '/';
}

// support legacy URLs (URI by query parameter e.g. /rest-v2/?uri=/entities)
if (($_SERVER['PATH_INFO'] === '/') && (!empty($_REQUEST['uri']))) {
    $_SERVER['PATH_INFO'] = $_REQUEST['uri'];
}

// create request object
$request = new sspmod_janus_REST_Request(
    $_SERVER['REQUEST_METHOD'], $_SERVER['PATH_INFO'], $_REQUEST
);

// create response object
$response = new sspmod_janus_REST_Response();
$response->addHeader(
    'Content-Type', $config->getValue('vendor-mime-type')
);

// create Server instance
$server = new sspmod_janus_REST_Server(
    $request, $response
);

// execute request
$server->handle();

// send response headers and body
$response->send();
