<?php

class sspmod_janus_MetadataFieldBuilder
{
    /**
     * Configuration for metadata fields.
     *
     * @var array
     */
    protected $metadataFieldConfiguration;

    /**
     * Built array of metadata fields.
     *
     * @var sspmod_janus_Metadatafield[]
     */
    protected $metadataFields = array();

    /**
     * Create a new Builder for metadata field definitions. Give it the raw Janus configuration.
     *
     * @param $metadataFields
     */
    public function __construct($metadataFields)
    {
        $this->metadataFieldConfiguration = $metadataFields;
    }

    /**
     * Get the a list of defined fields with their definition.
     *
     * @return sspmod_janus_Metadatafield[]
     */
    public function getMetadataFields()
    {
        if (empty($this->metadataFields)) {
            $this->buildMetadataFields();
        }

        return $this->metadataFields;
    }

    /**
     * Turn JANUS configuration into a fieldName indexed array of Metadatafield definitions.
     */
    protected function buildMetadataFields()
    {
        foreach($this->metadataFieldConfiguration AS $fieldName => $fieldOptions) {

            // If supported is set, build multiple metadata fields
            if (isset($fieldOptions['supported']) && is_array($fieldOptions['supported'])) {
                $this->buildMultiSupportedFields($fieldName, $fieldOptions);
                continue;
            }

            $this->metadataFields[$fieldName] = new sspmod_janus_Metadatafield($fieldName, $fieldOptions);
        }
    }

    /**
     * Given a field with multiple supported options, like "Sso:#:Binding" and options [0,1,2]
     * adds "Sso:0:Binding", "Sso:1:Binding" and "Sso:2:Binding".
     *
     * @param string $fieldName     Name of the field.
     * @param array  $fieldOptions  Options for the field.
     */
    protected function buildMultiSupportedFields($fieldName, $fieldOptions)
    {
        $supported = $fieldOptions['supported'];
        foreach ($supported AS $supportedNamePart) {
            $supportedName = str_replace('#', $supportedNamePart, $fieldName);
            $this->metadataFields[$supportedName] = new sspmod_janus_Metadatafield($supportedName, $fieldOptions);
        }
    }
}
