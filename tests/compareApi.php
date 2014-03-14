<?php
/**
 * Note: this test script requires the following:
 * - A new version janus available at: https://serviceregistry.demo.openconext.org
 * - An old version of janus available at:https://serviceregistry-janus-1.16.demo.openconext.org
 * - Both with a prod version of the db
 * - both with signature checking disabled
 *
 * Call with: export PHP_IDE_CONFIG="serverName=serviceregistry.demo.openconext.org" || export XDEBUG_CONFIG="idekey=PhpStorm, remote_connect_back=0, remote_host=192.168.56.1" &&  clear && php tests/compareApi.php
 */

// @todo use janus client to fix signing?
// https://github.com/OpenConext/OpenConext-engineblock/blob/master/bin/janus_client.php

require __DIR__ . "/../app/autoload.php";

$test = new OldApiTest();
$test->testSps();
$test->testIds();
echo 'end';

class OldApiTest
{
    private $defaultArguments = array(
        'rest' => 1,
        'user_id' => 'engine',
        'janus_key' => 'engine'
    );

//    'getIdpList' => array(),
//'getUser' => array(
//    'userid' => 'admin'
//),

    private $genericMethods = array(
        'getEntity' => array(
            'entityid' => 'entityid'
        )
    );

    private $idpMethods = array(
        'getAllowedSps' => array(
            'idpentityid' => 'entityid'
        )
    );

    private $spMethods = array(
        'getAllowedIdps' => array(
            'spentityid' => 'entityid'
        ),
        'isConnectionAllowed' => array(
            'spentityid' => 'entityid',
            'idpentityid' => 'https://surfguest.nl/test'

        ),
        'arp' => array(
            'entityid' => 'entityid'
        ),
        'getMetadata' => array(
            'entityid' => 'entityid'
        ),
        'findIdentifiersByMetadata' => array(
            'key' => 'name:en',
            'value' => 'e',
            'userid' => 'admin'
        )
    );

    /**
     * @var \Guzzle\Http\Client
     */
    private $oldHttpClient;

    /**
     * @var Guzzle\Http\Client
     */
    private $newHttpClient;

    /**
     *
     */
    public function __construct()
    {
        $this->oldHttpClient = new \Guzzle\Http\Client(
            'https://serviceregistry.demo.openconext.org/simplesaml/module.php/janus/services/rest/'
        );
        $this->newHttpClient = new \Guzzle\Http\Client(
            'https://serviceregistry-janus-1.16.demo.openconext.org/simplesaml/module.php/janus/services/rest/'
        );
    }

    // Exec all methods for each SP
    public function testSps()
    {
        $spMethods = array_merge($this->genericMethods, $this->spMethods);
        $spListResponse = $this->createResponse($this->oldHttpClient, array_merge(
//            array('method' => 'getSpList'),
            $this->defaultArguments));
        foreach ($spListResponse->json() as $entityId => $sp) {
//            echo "\nChecking SP '{$entityId}'";
            $this->execMethods($spMethods, array(
                'entityid' => $entityId
            ));
        }
    }

    // Exec all methods for each IDP
    public function testIdps()
    {
        $idpMethods = array_merge($this->genericMethods, $this->idpMethods);
        $idpListReidponse = $this->createReidponse($this->oldHttpClient, array_merge(
            array('method' => 'getIdpList'),
            $this->defaultArguments));
        foreach ($idpListReidponse->json() as $entityId => $idp) {
            echo "\nChecking IDP '{$entityId}'";
            $this->execMethods($idpMethods, array(
                'entityid' => $entityId
            ));
        }
    }

    //array_merge($this->genericMethods,

    /**
     * @param array $methods
     * @param array $parameters
     */
    private function execMethods(array $methods, array $parameters)
    {
        foreach ($methods as $method => $methodArguments) {
            $arguments['method'] = $method;
            $arguments = array_merge($arguments, $this->defaultArguments, $methodArguments);

            foreach ($arguments as $argument => $argumentValue) {
                if (isset($parameters[$argumentValue])) {
                    $arguments[$argument] = $parameters[$argumentValue];
                }
            }

            echo "\n - calling {$method}...old...";

            try {
                $oldResponse = $this->createResponse($this->oldHttpClient, $arguments);
            } catch (Exception $ex) {
                echo "Failed " . PHP_EOL .
                    $ex->getMessage() . PHP_EOL;
                break;
            }

            echo "new...";

            try {
                $newResponse = $this->createResponse($this->oldHttpClient, $arguments);
            } catch (Exception $ex) {
                echo "Failed " . PHP_EOL .
                    $ex->getMessage() . PHP_EOL;
                break;
            }

            echo "comparing...";

            $oldJson = $oldResponse->json();
            $newJson = $newResponse->json();

            $diff = $this->array_diff_recursive($oldJson, $newJson);
            if (!empty($diff)) {
                var_dump($diff);
            }

            echo "done";
        }
    }

    private function createResponse(\Guzzle\Http\Client $client, array $arguments)
    {
        $request = $client->get('', array(), array(
            'query' => $arguments
        ));
        $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYHOST, false);
        $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);

        return $request->send();
    }

    private function array_diff_recursive($array1, $array2)
    {
        $diff = array();
        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                if (is_array($value)) {
                    $subDiff = $this->array_diff_recursive($value, $array2[$key]);
                    if (count($subDiff)) {
                        $diff[$key] = $subDiff;
                    }
                } else {
                    if ($value != $array2[$key]) {
                        $diff[$key] = $value;
                    }
                }
            } else {
                $diff[$key] = $value;
            }
        }
        return $diff;
    }
}