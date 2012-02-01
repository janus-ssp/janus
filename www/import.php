<?php
$session = SimpleSAML_Session::getInstance();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

$authsource = $janus_config->getValue('auth', 'login-admin');
$useridattr = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');

// Validate user
if ($session->isValid($authsource)) {
    $attributes = $session->getAttributes();
    // Check if userid exists
    if (!isset($attributes[$useridattr]))
        throw new Exception('User ID is missing');
    $userid = $attributes[$useridattr][0];
} else {
    $session->setData('string', 'refURL', SimpleSAML_Utilities::selfURL());
    SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
}

$mcontrol = new sspmod_janus_UserController($janus_config);
if(!$user = $mcontrol->setUser($userid)) {
    die('Error in setUser');
}

echo '<h1>SAML20 IdP</h1>';
include('saml20-idp-remote.php');
foreach($metadata AS $key => $val) {
    $msg = $mcontrol->createNewEntity($key, 'saml20-idp');
    echo "Id: " . $msg . '<br />';
    if(is_int($msg)) {
        $econtroller = new sspmod_janus_EntityController($janus_config);
        $econtroller->setEntity((string)$msg);
        $econtroller->loadEntity();

        foreach($val AS $k => $v) {
            if($k == 'name') $k = 'entity:name';
            if($k == 'description') $k = 'entity:description';
            echo '<br>Key: ' . $k . '<br>';
            if(is_string($v)) {
                echo '<br/>Insert ' . $v . '<br/>';
                if(!$econtroller->addMetadata($k, $v)) {
                    $econtroller->updateMetadata($k, $v);
                    echo 'Updated<br>';
                } else {
                    echo 'Added<br>';
                }
            } else if(is_array($v)){
                foreach($v AS $sk => $sv) {
                    if(is_string($sk)) {
                        $newkey = $k .':' . $sk;
                    } else {
                        $newkey = $k;
                    }
                    echo '<br/>Insert ' . $sv . '<br/>';
                    if(!$econtroller->addMetadata($newkey, $sv)) {
                        $econtroller->updateMetadata($newkey, $sv);
                        echo 'Updated<br>';
                    } else {
                        echo 'Added<br>';
                    }
                }
            } else {
                echo '<br/>Insert ' . (string)$v . '<br/>';
                if(!$econtroller->addMetadata($k, (string)$v)) {
                    $econtroller->updateMetadata($k, (string)$v);
                    echo 'Updated<br>';
                } else {
                    echo 'Added<br>';
                }
            }
            echo '-------------------------------------';
        }
        $econtroller->saveEntity();

        echo $key . ' imported<br />';
    }
}

echo '<h1>SAML20 SP</h1>';
include('saml20-sp-remote.php');
foreach($metadata AS $key => $val) {
    $msg = $mcontrol->createNewEntity($key, 'saml20-sp');
    echo "Id: " . $msg . '<br />';
    if(is_int($msg)) {
        $econtroller = new sspmod_janus_EntityController($janus_config);
        $econtroller->setEntity((string)$msg);
        $econtroller->loadEntity();

        foreach($val AS $k => $v) {
            if($k == 'attributes') {
                $arp = new sspmod_janus_ARP();
                $arp->setName($key);
                $arp->setAttributes($v);
                $arp->save();
                $econtroller->setArp($arp->getAid());
            }
            if($k == 'name') $k = 'entity:name';
            if($k == 'description') $k = 'entity:description';
            echo '<br>Key: ' . $k . '<br>';
            if(is_string($v)) {
                echo '<br/>Insert ' . $v . '<br/>';
                if(!$econtroller->addMetadata($k, $v)) {
                    $econtroller->updateMetadata($k, $v);
                    echo 'Updated<br>';
                } else {
                    echo 'Added<br>';
                }
            } else if(is_array($v)){
                foreach($v AS $sk => $sv) {
                    if(is_string($sk)) {
                        $newkey = $k .':' . $sk;
                    } else {
                        $newkey = $k;
                    }
                    echo '<br/>Insert ' . $sv . '<br/>';
                    if(!$econtroller->addMetadata($newkey, $sv)) {
                        $econtroller->updateMetadata($newkey, $sv);
                        echo 'Updated<br>';
                    } else {
                        echo 'Added<br>';
                    }
                }
            } else {
                echo '<br/>Insert ' . (string)$v . '<br/>';
                if(!$econtroller->addMetadata($k, (string)$v)) {
                    $econtroller->updateMetadata($k, (string)$v);
                    echo 'Updated<br>';
                } else {
                    echo 'Added<br>';
                }
            }
            echo '-------------------------------------';
        }



        $econtroller->saveEntity();

        echo $key . ' imported<br />';
    }
}
