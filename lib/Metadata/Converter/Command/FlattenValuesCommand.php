<?php

/**
 * Class to flatten certain values for storing them in JANUS database
 * format.
 *
 * For example: UIInfo:Keywords:en is an array of keywords of which the values
 * will be stored space separated in JANUS instead of having the keys
 * UIInfo:Keywords:en:0, UIInfo:Keywords:en:1, UIInfo:Keywords:en:2, ...
 */
class sspmod_janus_Metadata_Converter_Command_FlattenValuesCommand implements sspmod_janus_Metadata_Converter_Command_CommandInterface
{
    private static $instance;

    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function convert(array $md)
    {
        $md = $this->flattenKeywords($md);

        return $md;
    }

    private function flattenKeywords(array $md)
    {
        if (isset($md['UIInfo']['Keywords']) && is_array($md['UIInfo']['Keywords'])) {
            foreach ($md['UIInfo']['Keywords'] as $lang => $value) {
                if (is_array($value)) {
                    $md['UIInfo']['Keywords'][$lang] = implode(" ", $value);
                }
            }
        }

        return $md;
    }

}
