<?php

/**
 * @todo This should really be autoloaded
 */

define('JANUS_LIBRARY_FOLDER', __DIR__ . '/../lib');

require JANUS_LIBRARY_FOLDER . '/Shell/Command/Interface.php';
require JANUS_LIBRARY_FOLDER . '/Shell/Command/Abstract.php';

require JANUS_LIBRARY_FOLDER . '/OpenSsl/Certificate/Chain.php';
require JANUS_LIBRARY_FOLDER . '/OpenSsl/Certificate/Utility.php';
require JANUS_LIBRARY_FOLDER . '/OpenSsl/Certificate/Validator.php';
require JANUS_LIBRARY_FOLDER . '/OpenSsl/Certificate/Chain/Factory.php';
require JANUS_LIBRARY_FOLDER . '/OpenSsl/Certificate/Chain/Validator.php';
require JANUS_LIBRARY_FOLDER . '/OpenSsl/Certificate/Chain/Exception/BuildingFailedIssuerUrlNotFound.php';
require JANUS_LIBRARY_FOLDER . '/OpenSsl/Certificate/Exception/NotAValidPem.php';

require JANUS_LIBRARY_FOLDER . '/OpenSsl/Command/SClient.php';
require JANUS_LIBRARY_FOLDER . '/OpenSsl/Command/Verify.php';
require JANUS_LIBRARY_FOLDER . '/OpenSsl/Command/X509.php';

require JANUS_LIBRARY_FOLDER . '/OpenSsl/Url/UnparsableUrlException.php';
require JANUS_LIBRARY_FOLDER . '/OpenSsl/Certificate.php';
require JANUS_LIBRARY_FOLDER . '/OpenSsl/Url.php';

require JANUS_LIBRARY_FOLDER . '/Exception/NoCertData.php';
require JANUS_LIBRARY_FOLDER . '/CertificateFactory.php';

require JANUS_LIBRARY_FOLDER . '/Metadata/Validator.php';