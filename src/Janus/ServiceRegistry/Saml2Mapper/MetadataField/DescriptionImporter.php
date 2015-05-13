<?php

namespace Janus\ServiceRegistry\Importer;

use SAML2_XML_md_EntityDescriptor;
use SAML2_XML_md_RoleDescriptor;
use SAML2_XML_mdui_UIInfo;

class DescriptionImporter implements MetadataFieldMapperInterface
{
    /**
     * @param SAML2_XML_md_EntityDescriptor $entity
     * @return array
     */
    public function import(SAML2_XML_md_EntityDescriptor $entity)
    {
        $fields = array();

        foreach ($entity->Extensions as $extension) {
            if (!$extension instanceof SAML2_XML_mdui_UIInfo) {
                continue;
            }

            foreach ($extension->DisplayName as $langCode => $value) {
                $fields["description:$langCode"] = $value;
            }
        }

        /** @var SAML2_XML_md_RoleDescriptor $role */
        foreach ($entity->RoleDescriptor as $role) {
            foreach ($role->Extensions as $extension) {
                if (!$extension instanceof SAML2_XML_mdui_UIInfo) {
                    continue;
                }

                foreach ($extension->DisplayName as $langCode => $value) {
                    $fields["description:$langCode"] = $value;
                }
            }
        }

        return $fields;
    }
}
