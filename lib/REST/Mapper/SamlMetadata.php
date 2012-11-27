<?php

abstract class sspmod_janus_REST_Mapper_SamlMetadata extends sspmod_janus_REST_Mapper_Abstract
{
    /**
     * @var string idp/sp saml20 type
     */
    protected $_samlType = 'unknown';

    /**
     * @return array
     */
    public function getCollection()
    {
        $mb = new sspmod_janus_MetadatafieldBuilder(
            self::getConfig()->getArray('metadatafields.' . $this->_samlType)
        );

        $result = array();
        foreach ($mb->getMetadatafields() as $field) {
            $row = array(
                'name'    => $field->name,
                'type' => $field->type,
                'default' => isset($field->default)
                    ? $field->default : null,
                'default_allow' => isset($field->default_allow)
                    ? $field->default_allow : null,
                'required' => isset($field->required)
                    ? $field->required : null,
                'validate' => isset($field->validate)
                    ? $field->validate : null,
            );

            switch($field->type) {
                case 'file':
                    $row['filetype'] = isset($field->filetype)
                        ? $field->filetype : null;
                    $row['maxsize'] = isset($field->maxsize)
                        ? $field->maxsize : null;
                    break;
                case 'select':
                    $row['select_values'] = isset($field->select_values)
                        ? $field->select_values : null;
                    break;
            }

            $result[] = $row;
        }

        return $result;
    }

    /**
     * @param string $id
     * @return array
     */
    public function get($id)
    {
        foreach ($this->getCollection() as $field) {
            if ($field['name'] === $id) {
                return $field;
            }
        }

        return null;
    }
}
