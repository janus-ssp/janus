<?php

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use Janus\ServiceRegistry\Bundle\CoreBundle\Service\ArpAttributeHelper;

function addArpConfiguration(SimpleSAML_XHTML_Template $et, ConfigProxy $janus_config, ArpAttributeHelper $arpHelper) {
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
    $et->data['arp_attribute_sources'] = $arpHelper->addDefaultAttributeSource($janus_config->getValue('attribute_sources'));
}
