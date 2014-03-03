<?php

class sspmod_janus_Metadata_Converter_Command_ScopeConverterCommand implements sspmod_janus_Metadata_Converter_Command_CommandInterface
{
    /**
     * Based on the scope:0, scope:1 .. scope:5 we convert it to shibmd:scope:#:allowed. Currently we don't
     * support regular expressions in the IdP metadata. This can be manually overridden by editing the metadata directly in Janus.
     *
     * @param array $array The array to be flattened
     *
     * @return array array with the scope info
     */
    public function convert(array $array)
    {
        for ($i = 0; $i <= 5; $i++) {
            if (isset($array["scope:$i"])) {
                $array["shibmd:scope:$i:allowed"] = $array["scope:$i"];
                $array["shibmd:scope:$i:regexp"] = false;
                unset($array["scope:$i"]);
            }
        }
        return $array;
    }

}
