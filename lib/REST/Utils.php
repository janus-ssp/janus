<?php
class sspmod_janus_REST_Utils
{
    public static function processRequest($data)
    {
        $request = new sspmod_janus_REST_Request();

        $request->setRawdata($data);

        if(isset($data['janus_sig'])) {
            $request->setSignature($data['janus_sig']);
            unset($data['janus_sig']);    
        }
        if(isset($data['janus_key'])) {
            $request->setKey($data['janus_key']);
        }
        if(isset($data['method'])) {
            $request->setMethod($data['method']);
        } else {
            return false;
        }
        
        $request->setRequestVars($data);
        
        return $request;
    }

    public static function isSignatureValid(sspmod_janus_REST_Request $request)
    {    
        if(is_null($request->getKey())) {
            return false;
        }

        $config = SimpleSAML_Configuration::getConfig('module_janus.php');
        $user = new sspmod_janus_User($config->getValue('store'));
        $user->setUserid($request->getKey());
        $user->load(sspmod_janus_User::USERID_LOAD);
        $shared_secret = $user->getSecret();

        $data = $request->getRequestVars();

        // Sort params
        ksort($data);

        $concat_string = '';
        
        // Concat all params with values
        foreach($data AS $key => $value) {
            $concat_string .= $key . $value;
        }
        // Prepend shared secret
        $prepend_secret = $shared_secret . $concat_string;

        // Hash the string to the signature
        $calculated_signature = hash('sha512', $prepend_secret);

        return $request->getSignature() == $calculated_signature;
    }

    public static function sendResponse($status = 200, $body = '', $content_type = 'text/html')
    {
        // Set the status
        header('HTTP/1.0 ' . $status . ' ' . sspmod_janus_REST_Utils::getStatusCodeMessage($status));
        
        // Set caching headers
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        
        if($body != '') {
            switch($content_type) {
                case 'application/json':
                    header('Content-type: application/json; charset=UTF-8');
                    $body = json_encode($body);
                    break;
                case 'text/html':
                    header('Content-type: text/html; charset=UTF-8');
                    break;
                default:
                    header('Content-type: text/html; charset=UTF-8');
                    break;
            }
            echo $body;
            exit;
        } else {
            // If no body, send a generic response
            header('Content-type: text/html');
            
            // Servers don't always have a signature turned on (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            $statusMessage = sspmod_janus_REST_Utils::getStatusCodeMessage($status);
            $body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
                        <html>
                            <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                                <title>' . $status . ' ' . $statusMessage . '</title>
                            </head>
                            <body>
                                <h1>' . $statusMessage . '</h1>
                                <hr />
                                <address>' . $signature . '</address>
                            </body>
                        </html>';

            echo $body;
            exit;
        }
    }

    public static function getStatusCodeMessage($status)
    {
        $codes = Array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        );

        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    public static function callMethod(sspmod_janus_REST_request $request)
    {
        $method = 'method_' . $request->getMethod();

        if(method_exists('sspmod_janus_REST_Methods', $method)) {
            if(sspmod_janus_REST_Methods::isProtected($method)) {
                if(!sspmod_janus_REST_Utils::isSignatureValid($request)) {
                    return array('status' => 401, 'data' => '');
                }
            }

            $result = array('status' => 200);
            $result['data'] = sspmod_janus_REST_Methods::$method($request->getRequestVars(), $result['status']); 
        } else {
            $result = array('status' => 404, 'data' => '');
        }

        return $result;
    }
}
