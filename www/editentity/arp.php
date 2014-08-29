<?php

use Janus\ServiceRegistry\SimpleSamlPhp\ConfigProxy;

function addArpConfiguration(SimpleSAML_XHTML_Template $et, ConfigProxy $janus_config) {
    $arp_attributes = array();
    $old_arp_attributes = $janus_config->getValue('attributes');
    foreach ($old_arp_attributes as $label => $arp_attribute) {
        if (is_array($arp_attribute)) {
            $arp_attributes[$label] = $arp_attribute;
        }
        else {
            $arp_attributes[$arp_attribute] = array('name' => $arp_attribute);
        }
    }
    $et->data['arp_attributes_configuration'] = $arp_attributes;
}
