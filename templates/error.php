<?php
/**
 * Main template for JANUS.
 *
 * @author     Jacob Christiansen, <jach@wayf.dk>
 * @author     pitbulk
 * @package    simpleSAMLphp
 * @subpackage JANUS
 * @version    $Id: janus-main.php 11 2009-03-27 13:51:02Z jach@wayf.dk $
 */
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');
$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/style.css" />' . "\n";

$this->includeAtTemplateBase('includes/header.php');

// Display an optional subtitle if present
if(isset($this->data['title'])) {
    echo '<h1>'. $this->t($this->data['title']) .'</h1>';
}

// Display error information
if(isset($this->data['error_data'])) {
    echo '<h2>'.$this->t($this->data['error'], $this->data['error_data']).'</h2>';
} else {
    echo '<h2>'.$this->t($this->data['error']).'</h2>';
}

// Display optional extra data about the error
if (isset($this->data['extra_data'])) {
    echo $this->data['extra_data'];
}

$this->includeAtTemplateBase('includes/footer.php');
