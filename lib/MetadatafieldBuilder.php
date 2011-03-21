<?php
class sspmod_janus_MetadatafieldBuilder {

    protected $mf_config;
    protected $metadatafields = array();
    
    public function __construct($metadatafields)
    {
        $this->mf_config = $metadatafields;
    }

    protected function buildMetadatafields()
    {
        foreach($this->mf_config AS $key => $value) {
            // If supported is set, build multiple metadatafields
            if (isset($value['supported']) && is_array($value['supported'])) {
                $supported = $value['supported'];
                unset($value['supported']);
                foreach ($supported AS $supported_idiom) {
                    $supported_name = str_replace('#', $supported_idiom, $key);
                    $this->metadatafields[$supported_name] = new sspmod_janus_Metadatafield($supported_name, $value);
                }
            } else {
                $this->metadatafields[$key] = new sspmod_janus_Metadatafield($key, $value);
            }
        }
    }

    public function getMetadatafields()
    {
        if (empty($this->metadatafields)) {
            $this->buildMetadatafields();
        }

        return $this->metadatafields;
    }
}
