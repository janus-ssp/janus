<?php
$functions = array(
    'lengteq20' => array(
        'code' => '
            return (strlen($value) >= 20);
        ',
    ),
    'leneq40' => array(
        'code' => '
            if(strlen($value) == 40) return true; 
            else return false;
        ',                  
    ),
    'isurl' => array(
        'code' => '
            if (version_compare(PHP_VERSION, "5.2.0", ">")) {
                return filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
            } else {
                $parts = parse_url($value);
                if ($parts == FALSE) {
                    return false;
                } else if (!isset($parts["scheme"]) ||
                          (!isset($parts["host"]) &&
                          ($parts["scheme"] !== "mailto" &&
                           $parts["scheme"] !== "news" &&
                           $parts["scheme"] !== "file"))) {
                    return false;
                }
                return true;
            }
        ',                  
    ),                    
);
