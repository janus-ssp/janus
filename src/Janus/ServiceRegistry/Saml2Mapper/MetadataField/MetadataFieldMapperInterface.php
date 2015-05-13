<?php

namespace Janus\ServiceRegistry\Importer;

use SAML2_XML_md_EntityDescriptor;

interface MetadataFieldMapperInterface
{
    /**
     * @param SAML2_XML_md_EntityDescriptor $entity
     * @return array
     */
    public function import(SAML2_XML_md_EntityDescriptor $entity);
}
