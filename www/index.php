<?php
/*
 * JANUS
 */
$config = SimpleSAML_Configuration::getInstance();

$janus_config = $config->copyFromBase('janus', 'module_janus.php');
echo "<pre>";
$janus_store = $janus_config->getValue('store');

$user = new sspmod_janus_User($janus_store);
$user->setUid('1');
$user->load();

var_dump($user);

echo "</pre>";

$t = new SimpleSAML_XHTML_Template($config, 'janus:janus-main.php');
$t->show();

?>
