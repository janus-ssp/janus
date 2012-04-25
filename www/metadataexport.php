<?php
// For debugging
error_reporting(-1);
ini_set('display_errors', 'on');
ini_set('display_startup_errors', TRUE);
// for debugging

// Get configuration
$session = SimpleSAML_Session::getInstance();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');
$util = new sspmod_janus_AdminUtil();

$access = false;
$user = null;

// Validate user
if ($session->isValid($janus_config->getValue('auth'))) {
    $useridattr = $janus_config->getValue('useridattr');
    $attributes = $session->getAttributes();

    // Check if userid exists
    if (!isset($attributes[$useridattr])) { 
        throw new Exception('User ID is missing');
    }
    $userid = $attributes[$useridattr][0];
    
    // Get the user
    $user = new sspmod_janus_User($janus_config->getValue('store'));
    $user->setUserid($userid);
    $user->load(sspmod_janus_User::USERID_LOAD);
    
    // Check for permission
    $guard = new sspmod_janus_UIguard($janus_config->getArray('access', array()));
    if($guard->hasPermission('exportallentities', null, $user->getType(), TRUE)) {
        $access = true;
    }
}

// Get default options
$md_options['types'] = array();
$md_options['states'] = array();
$md_options['exclude'] = array();
$md_options['postprocessor'] = null;
$md_options['ignore_errors'] = false;
$md_options = array_merge($md_options, $janus_config->getArray('mdexport.default_options'));
$allowed_mime = $janus_config->getArray('mdexport.allowed_mime');

// Get ID if set
if (isset($_GET['id'])) {
    // Only one ID is allowed
    if (!is_string($_GET['id'])) {
        header("HTTP/1.1 400 Syntax error in ID");
        exit;
    }
    // Validate ID syntax
    if (preg_match_all('/^[\w\-]+$/', $_GET['id'], $result) == 1){
        $id = $result[0][0];
        // Get metadata feed configuration
        $md_feeds = $janus_config->getArray('mdexport.feeds');
        if (isset($md_feeds[$id])) {
            $md_options = array_merge($md_options, $md_feeds[$id]);
            $md_options['ignore_errors'] = TRUE;
        } else {
            header("HTTP/1.1 404 Metadata feed not found");
            exit;
        }
    } else {
        // Error in parsed ID
        header("HTTP/1.1 400 Syntax error in ID");
        exit;
    }
} else if (isset($_GET['md'])) {
    $syntaxerrors = array();
    // Validate user
    if (is_null($user)) {
        // Redirect if no logged in user 
        $session->setData('string', 'refURL', SimpleSAML_Utilities::selfURL());
        SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
    }
    // Check for permission
    if (!$access) {
        header("HTTP/1.1 403 Permission not granted");
        exit;
    }

    // Get ignore errors
    if (isset($_GET['ignoreerrors'])) {
        if ($_GET['ignoreerrors'] == 'on') {
            $md_options['ignore_errors'] = true;
        }
    }

    // Get types
    if (isset($_GET['type'])) {
        $type = (array)$_GET['type'];
        $md_options['types'] = array_intersect_key($util->getAllowedTypes(), array_flip($type));
        foreach ($md_options['types'] AS $key => $val) {
            if (!$val['enable']) {
                unset($md_options['types'][$key]);
            }
        }
        $md_options['types'] = array_keys($md_options['types']);
        if (empty($md_options['types'])) {
            $syntaxerrors[] = 'No permissable type given';
        }
    } else {
        // Type not parsed
        $syntaxerrors[] = 'No permissable type given';
    }
    // Get state
    if(isset($_GET['state'])) {
        $state = (array)$_GET['state'];
        $md_options['states'] = array_intersect_key($janus_config->getArray('workflowstates'), array_flip($state));
        foreach ($md_options['states'] AS $key => $val) {
            if (!$val['isDeployable']) {
                unset($md_options['states'][$key]);
            }
        }
        $md_options['states'] = array_keys($md_options['states']);
        if (empty($md_options['states'])) {
            $syntaxerrors[] = 'No permissable state given';
        }
    } else {
        // Type not parsed
        $syntaxerrors[] = 'No permissable state given';
    }
    // Get mime
    if (isset($_GET['mime'])) {
        if (!is_string($_GET['mime'])) {
            $syntaxerrors[] = 'Syntax error on mime type';
        }
        $available_mime = array_intersect($allowed_mime, (array)$_GET['mime']);
        $md_options['mime'] = reset($available_mime);
        if (empty($md_options['mime'])) {
            $syntaxerrors[] = 'No supported mime type given';
        }
    } else {
        // Get mime from Accept header
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $possible_mime = explode(',', $_SERVER['HTTP_ACCEPT']);
            foreach ($possible_mime AS $key => &$mime) {
                if (preg_match_all('/^(\w+\/[\w\+]+)(;.*)*$/', $mime, $result) == 1) {
                    $possible_mime[$key] = $result[1][0];
                } else {
                    unset($possible_mime[$key]);
                }
            }
            if (!empty($possible_mime)) {
                $available_mime = array_intersect($allowed_mime, $possible_mime);
                $md_options['mime'] = reset($available_mime);
            } else {
                $syntaxerrors[] = 'No supported mime type given';
            }
        } else {
            $syntaxerrors[] = 'No supported mime type given';
        }
    }
    // Get filename
    if (isset($_GET['filename'])) {
        // Allow for empty filename - Default is used
        if (!empty($_GET['filename'])) {
            if (!is_string($_GET['filename'])) {
                $syntaxerrors[] = 'Syntax error in filename';
            } else if (preg_match_all('/^[\w\-.]+$/', $_GET['filename'], $result) == 1) {
                $md_options['filename'] = $result[0][0];
                // Get all metadata export options from config
            } else {
                // Error in parsed ID
                $syntaxerrors[] = 'Syntax error in filename';
            }
        }
    }
    // Get excluded entities
    if (isset($_GET['exclude'])) {
        if (!empty($_GET['exclude'])) {
            $exclude = is_array($_GET['exclude']) ? $_GET['exclude'] : explode(',', $_GET['exclude']);
            // Ignore extra white space
            $exclude = array_map('trim', $exclude);

            foreach ($exclude AS $uri) {
                if (preg_match('/^[a-z][a-z0-9+-\.]*:.+$/i', $uri) == 0) {
                    $syntaxerrors[] = 'Syntax error in excluded entity';
                }
                $md_options['exclude'][] = $uri;
            }
        }
    }
    // Get post processor
    if (isset($_GET['postprocessor'])) {
        if (!is_string($_GET['postprocessor'])) {
            $syntaxerrors[] = 'Syntax error in exporter';
        } else if (preg_match_all('/^[\w\-]+$/', $_GET['postprocessor'], $result) == 1) {
            $md_options['postprocessor'] = $result[0][0];
        } else {
            $syntaxerrors[] = 'Syntax error in exporter';
        }
    }
    // Display errors if any
    if (count($syntaxerrors) > 0) {
        $t = new SimpleSAML_XHTML_Template($config, 'janus:metadataexport.php', 'janus:metadataexport');
        $t->data['allowed_mime'] = $allowed_mime;
        $t->data['states'] = $janus_config->getArray('workflowstates');
        $t->data['types'] = $util->getAllowedTypes();
        $t->data['postprocessor'] = $janus_config->getArray('mdexport.postprocessor');
        $t->data['error_type'] = 'error_type_syntax_error';
        $t->data['errors'] = '<ul>';
        foreach ($syntaxerrors AS $val) {
            $t->data['errors'] .= "<li>{$val}</li>";
        }
        $t->data['errors'] .= '</ul>';
        $t->show();
        exit;
    }
} else {
    // Display UI for selecting metadata
    // Validate user
    if (is_null($user)) {
        // Redirect if no logged in user 
        $session->setData('string', 'refURL', SimpleSAML_Utilities::selfURL());
        SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
    }
    // Check for permission
    if (!$access) {
        header("HTTP/1.1 403 Permission not granted");
        exit;
    }
    $t = new SimpleSAML_XHTML_Template($config, 'janus:metadataexport.php', 'janus:metadataexport');
    $t->data['allowed_mime'] = $allowed_mime;
    $t->data['states'] = $janus_config->getArray('workflowstates');
    $t->data['types'] = $util->getAllowedTypes();
    $t->data['postprocessor'] = $janus_config->getArray('mdexport.postprocessor');
    $t->show();
    exit;
}

// Generate metadata
try {
    $entities = $util->getEntitiesByStateType($md_options['states'], $md_options['types']);

    // Create entitiesDescriptor
    $xml = new DOMDocument();
    $entitiesDescriptor = $xml->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:EntitiesDescriptor');
    $entitiesDescriptorName = $janus_config->getString('export.entitiesDescriptorName', $md_options['entitiesDescriptorName']);
    $entitiesDescriptor->setAttribute('Name', $entitiesDescriptorName);

    // Set caching options
    if($md_options['maxCache'] !== NULL) {
        $entitiesDescriptor->setAttribute('cacheDuration', 'PT' . $md_options['maxCache'] . 'S');
    }
    if($md_options['maxDuration'] !== NULL) {
        $entitiesDescriptor->setAttribute('validUntil', SimpleSAML_Utilities::generateTimestamp(time() + $md_options['maxDuration']));
    }

    $xml->appendChild($entitiesDescriptor);

    $ssp_metadata = '// Metadata for state "' . $md_options['states'] . '"';

    $errors = array();
    // Process all selected entities
    foreach ($entities as $entity) {
        // Ignore excluded entities
        if (in_array($entity['entityid'], $md_options['exclude'])) {
            continue;
        }

        // Get metadata for entity
        // In XML
        $entityDescriptor = sspmod_janus_MetaExport::getXMLMetadata(
            $entity['eid'], 
            $entity['revisionid'], 
            array(
                'maxCache' => $md_options['maxCache'], 
                'maxDuration' => $md_options['maxDuration']
            )
        );
        // In flat file
        $ssp_metadata = $ssp_metadata . "\n\n" .  sspmod_janus_MetaExport::getFlatMetadata($entity['eid'], $entity['revisionid']);

        // Error handling
        if (empty($entityDescriptor) || !$entityDescriptor) {
            // Ignore errors
            if ($md_options['ignore_errors']) {
                continue;
            } else {
                $errors[$entity['entityid']] = sspmod_janus_MetaExport::getError();
            }
        } else {
            // Append metadata to entitiesDescriptor
            $entitiesDescriptor->appendChild($xml->importNode($entityDescriptor, TRUE));
        }
    }

    // Display errors if any and ignore_errors is not set
    if (count($errors) > 0 && !$md_options['ignore_errors']) {
        $t = new SimpleSAML_XHTML_Template($config, 'janus:metadataexport.php', 'janus:metadataexport');
        $t->data['allowed_mime'] = $allowed_mime;
        $t->data['states'] = $janus_config->getArray('workflowstates');
        $t->data['types'] = $util->getAllowedTypes();
        $t->data['postprocessor'] = $janus_config->getArray('mdexport.postprocessor');
        $t->data['error_type'] = 'error_type_required_metadata';
        $t->data['errors'] = "";
        foreach ($errors AS $key => $val) {
            $t->data['errors'] .= "<i>{$key}</i><ul>";
            foreach ($val AS $line) {
                $t->data['errors'] .= "<li>" . $line . "</li>";
            }
            $t->data['errors'] .= "</ul>";
        }
        $t->show();
        exit;
    }

    // Sign the metadata if enabled
    if ($md_options['sign.enable']) {
        $signer = new SimpleSAML_XML_Signer(
            array(
                'privatekey' => $md_options['sign.privatekey'],
                'privatekey_pass' => $md_options['sign.privatekey_pass'],
                'certificate' => $md_options['sign.certificate'],
                'id' => 'ID',
            )
        );
        $signer->sign($entitiesDescriptor, $entitiesDescriptor, $entitiesDescriptor->firstChild);
    }

    // Call post prosessor if set
    if(!is_null($md_options['postprocessor'])) {
        $postproces_config = $janus_config->getArray('mdexport.postprocessor');
        if(array_key_exists($md_options['postprocessor'], $postproces_config)) {
            $postproces_cofig = $postproces_config[$md_options['postprocessor']];
            try {
                $exporter = sspmod_janus_Exporter::getInstance($postptoces_config['class'], $postproces_config['option']);
                $exporter->export($xml->saveXML());
                exit;
            } catch(Exception $e) {
                SimpleSAML_Utilities::fatalError($session->getTrackID(), 'Can not post proces metadata', $e);
                exit;
            }
        }
    }

    // Show the metadata
    switch($md_options['mime']) {
        case 'application/simplesamlphp+text':
            header('Content-Type: application/simplesamlphp+text');
            echo($ssp_metadata);
            exit;
        default:
            header('Content-Type: ' . $md_options['mime']);
            if (isset($md_options['filename']) && !empty($md_options['filename'])) {
                header('Content-Disposition: attachment; filename="' . $md_options['filename'] . '"');
            }
            echo($xml->saveXML());
            exit;
    }
} catch (Exception $exception) {
    // Something went wrong
    SimpleSAML_Utilities::fatalError($session->getTrackID(), 'METADATAEXPORT', $exception);
    exit;
}
