<?php

/**
 * @todo This should really be autoloaded
 */

define('JANUS_LIBRARY_FOLDER', __DIR__ . '/../lib');

require JANUS_LIBRARY_FOLDER . '/Exception/NoCertData.php';
require JANUS_LIBRARY_FOLDER . '/CertificateFactory.php';
require JANUS_LIBRARY_FOLDER . '/Metadata/Validator.php';