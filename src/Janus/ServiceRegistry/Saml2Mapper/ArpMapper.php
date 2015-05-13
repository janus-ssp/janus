<?php

namespace Janus\ServiceRegistry\Importer;

use SAML2_XML_md_EntityDescriptor;
use SAML2_XML_md_SPSSODescriptor;

class ArpMapper
{
    public function mapToArp(SAML2_XML_md_EntityDescriptor $entity)
    {
        $arp = array();
        foreach ($entity->RoleDescriptor as $role) {
            if (!$role instanceof SAML2_XML_md_SPSSODescriptor) {
                continue;
            }

            foreach ($role->AttributeConsumingService as $service) {
                foreach ($service->RequestedAttribute as $attribute) {
                    if (empty($attribute->AttributeValue)) {
                        $arp[$attribute->Name] = array('*');
                        continue;
                    }

                    $arpValues = array();
                    foreach ($attribute->AttributeValue as $attributeValue) {
                        $arpValues[] = $attributeValue->getString();
                    }
                    $arp[$attribute->Name] = $arpValues;
                }
            }
        }
        return $arp;
    }
}
