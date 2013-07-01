<?php

class sspmod_janus_MetadataToJanus
{

    private static $instance;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Method to flatten the metadata keywords from array to
     * space separated string
     *
     * @param $md the arrayized metadata
     * @return arrayized metadata with flattened keywords
     */
    public function flattenKeywords(&$md = array())
    {
        if (isset($md['UIInfo']['Keywords']) && is_array($md['UIInfo']['Keywords'])) {
            foreach ($md['UIInfo']['Keywords'] as $lang => $value) {
                if (is_array($value)) {
                    $md['UIInfo']['Keywords'][$lang] = implode(" ", $value);
                }
            }
        }
    }
}
