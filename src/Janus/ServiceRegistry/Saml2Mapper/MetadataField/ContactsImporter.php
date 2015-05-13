<?php

namespace Janus\ServiceRegistry\Importer;

use SAML2_XML_md_EntityDescriptor;
use SAML2_XML_md_RoleDescriptor;

class ContactsImporter implements MetadataFieldMapperInterface
{
    /**
     * @param SAML2_XML_md_EntityDescriptor $entity
     * @return array
     */
    public function import(SAML2_XML_md_EntityDescriptor $entity)
    {
        /** @var SAML2_XML_md_RoleDescriptor $role */
        $contacts = array();
        foreach ($entity->RoleDescriptor as $role) {
            foreach ($role->ContactPerson as $person) {
                $contact = array(
                    'contactType' => $person->contactType,
                );
                if (!empty($person->GivenName)) {
                    $contact['givenName'] = $person->GivenName;
                }
                if (!empty($person->SurName)) {
                    $contact['surName'] = $person->SurName;
                }
                if (!empty($person->EmailAddress)) {
                    $contact['emailAddress'] = $person->EmailAddress[0];
                }
                if (!empty($person->TelephoneNumber)) {
                    $contact['telephoneNumber'] = $person->TelephoneNumber[0];
                }
                $contacts[] = $contact;
            }
        }

        $fields = array();
        foreach ($contacts as $index => $contact) {
            $prefix = "contacts:$index:";
            foreach ($contact as $key => $value) {
                $fields[$prefix . $key] = $value;
            }
        }
        return $fields;
    }
}
