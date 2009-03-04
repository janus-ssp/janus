<?php
/*
 * JANUS
 */





$config = SimpleSAML_Configuration::getInstance();
$t = new SimpleSAML_XHTML_Template($config, 'janus:janus-main.php');
$t->show();

?>
