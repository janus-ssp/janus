<?php
/**
 * Note: this test script requires the following:
 * - A new version janus available at: https://serviceregistry.demo.openconext.org
 * - An old version of janus available at:https://serviceregistry-janus-1.16.demo.openconext.org
 * - Both with a prod version of the db
 *
 * Call with: export PHP_IDE_CONFIG="serverName=serviceregistry.demo.openconext.org" || export XDEBUG_CONFIG="idekey=PhpStorm, remote_connect_back=0, remote_host=192.168.56.1" &&  clear && php tests/compareApi.php
 */

require __DIR__ . "/../app/autoload.php";

class compareApiTest extends \PHPUnit_Framework_TestCase
{
    private $defaultArguments = array(
        'rest' => 1,
        'user_id' => 'engine',
        'janus_key' => 'engine'
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
     * @var array
     */
    private static $spList;

    /**
     * @var array
     */
    private static $idpList;

    /**
     *
     */
    public function setUp()
    {
        $this->oldHttpClient = new \Guzzle\Http\Client(
            'https://serviceregistry-janus-1.16.demo.openconext.org/simplesaml/module.php/janus/services/rest/'
        );
        $this->newHttpClient = new \Guzzle\Http\Client(
            'https://serviceregistry.demo.openconext.org/simplesaml/module.php/janus/services/rest/'
        );
    }

    public function testBothApisProvideAnEqualUser()
    {
        $responses = $this->callOldAndNewApi('getUser', array(
            'userid' => 'admin'
        ));

        $this->assertEquals($responses['old']->json(), $responses['old']->json());
    }

    public function testBothApisProvideEqualIdentifiersByMetadata()
    {
        $responses = $this->callOldAndNewApi('findIdentifiersByMetadata', array(
            'key' => 'name:en',
            'value' => 'e',
            'userid' => 'admin'
        ));

        $this->assertEquals($responses['old']->json(), $responses['old']->json());
    }

    public function testBothApisProvideAnEqualListOfSps()
    {
        $responses = $this->getSpListApiResponses();
        $this->assertEquals($responses['old']->json(), $responses['old']->json());
    }

    /**
     * @dataProvider getSps
     */
    public function testBothApisProvideAnEqualSp($entityId)
    {
        $responses = $this->callOldAndNewApi('getEntity', array(
            'entityid' => $entityId
        ));

        $this->assertEquals($responses['old']->json(), $responses['old']->json());
    }

    /**
     * @dataProvider getSps
     */
    public function testBothApisProvideAnEqualAListOfIdpsTheSpCanConnectTo($entityId)
    {
        $responses = $this->callOldAndNewApi('getAllowedIdps', array(
            'spentityid' => $entityId
        ));

        $this->assertEquals($responses['old']->json(), $responses['old']->json());
    }

    /**
     * @dataProvider getSps
     */
    public function testBothApisProvideIfAnSpIsAllowedToConnectToIdp($entityId)
    {
        $responses = $this->callOldAndNewApi('isConnectionAllowed', array(
            'spentityid' => $entityId,
            'idpentityid' => 'https://surfguest.nl/test'

        ));

        $this->assertEquals($responses['old']->json(), $responses['old']->json());
    }

    /**
     * @dataProvider getSps
     */
    public function testBothApisProvideAnEqualSpArp($entityId)
    {
        $responses = $this->callOldAndNewApi('arp', array(
            'entityid' => $entityId
        ));

        $this->assertEquals($responses['old']->json(), $responses['old']->json());
    }

    /**
     * @dataProvider getSps
     */
    public function testBothApisProvideEqualSpMetadata($entityId)
    {
        $responses = $this->callOldAndNewApi('getMetadata', array(
            'entityid' => $entityId
        ));

        $this->assertEquals($responses['old']->json(), $responses['old']->json());
    }

    public function testBothApisProvideAnEqualListOfIdps()
    {
        $responses = $this->getIdpListApiResponses();
        $this->assertEquals($responses['old']->json(), $responses['old']->json());
    }

    /**
     * @dataProvider getIdps
     */
    public function testBothApisProvideAnEqualIdp($entityId)
    {
        $responses = $this->callOldAndNewApi('getEntity', array(
            'entityid' => $entityId
        ));

        $this->assertEquals($responses['old']->json(), $responses['old']->json());
    }

    /**
     * @dataProvider getIdps
     */
    public function testBothApisProvideEqualIdpMetadata($entityId)
    {
        $responses = $this->callOldAndNewApi('getMetadata', array(
            'entityid' => $entityId
        ));

        $this->assertEquals($responses['old']->json(), $responses['old']->json());
    }

    /**
     * @dataProvider getIdps
     */
    public function testBothApisProvideAnEqualAListOfSpsTheIdpCanConnectTo($entityId)
    {
        $responses = $this->callOldAndNewApi('getAllowedSps', array(
            'idpentityid' => $entityId
        ));

        $this->assertEquals($responses['old']->json(), $responses['old']->json());
    }

    public function getSps()
    {
        $this->getSpListApiResponses();
        return static::$spList;
    }

    private function getSpListApiResponses()
    {
        static $responses;

        if (empty($responses)) {
            $this->setUp();

            $responses = $this->callOldAndNewApi('getIdpList', array());

            // (Ab)use this method to reuse the result for dataproviding further SP tests
            static::$spList = $this->createEntityListFromResponse($responses['old']);
        }

        return $responses;
    }

    public function getIdps()
    {
        $this->getIdpListApiResponses();
        return static::$idpList;
    }

    private function getIdpListApiResponses()
    {
        static $responses;

        if (empty($responses)) {
            $this->setUp();

            $responses = $this->callOldAndNewApi('getIdpList', array());

            // (Ab)use this method to reuse the result for dataproviding further IDP tests
            static::$idpList = $this->createEntityListFromResponse($responses['old']);
        }

        return $responses;
    }

    /**
     * @param array $entites
     * @return array
     */
    private function createEntityListFromResponse(\Guzzle\Http\Message\Response $response)
    {

        $connections = array();
        foreach ($response->json() as $entityId => $connectionMetadata) {
            // Enable for testing just on iteration
//            if (count($connections) > 0) {
//                break;
//            }

            $connections[] = array($entityId);
        }
        return $connections;
    }

    /**
     * @param string $method
     * @param array $methodArguments
     */
    private function callOldAndNewApi($method, array $methodArguments)
    {
        $arguments['method'] = $method;
        $arguments = array_merge($arguments, $this->defaultArguments, $methodArguments);

        foreach ($arguments as $argument => $argumentValue) {
            if (isset($parameters[$argumentValue])) {
                $arguments[$argument] = $parameters[$argumentValue];
            }
        }

        echo PHP_EOL;
        $startTime = microtime(true);
        $responses['old'] = $this->createResponse($this->oldHttpClient, $arguments);
        $endTime = microtime(true);
        $timeOldMs = ($endTime - $startTime) * 1000;
        echo 'Time: old ' . round($timeOldMs) . 'ms' . PHP_EOL;

        $startTime = microtime(true);
        $responses['new'] = $this->createResponse($this->newHttpClient, $arguments);
        $endTime = microtime(true);
        $timeNewMs = ($endTime - $startTime) * 1000;
        echo 'Time: new ' . round($timeNewMs) . 'ms' . PHP_EOL;

        echo 'Diff: ' . round($timeNewMs - $timeOldMs) . 'ms' . PHP_EOL;
        echo 'Perc: ' . round(($timeNewMs / $timeOldMs) * 100) . '%' . PHP_EOL;

        return $responses;

    }

    private function createResponse(\Guzzle\Http\Client $client, array $arguments)
    {
        try {
            $request = $client->get('', array(), array(
                'query' => $this->addSignature($arguments)
            ));
            $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYHOST, false);
            $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);

            $response = $request->send();
            return $response;
        } catch (Exception $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Copied from EngineBlock
     */
    private function addSignature(array $arguments)
    {
        // don't sign an old signature if present
        if (isset($arguments["janus_sig"])) {
            unset($arguments["janus_sig"]);
        }

        $signatureData = $arguments;

        ksort($signatureData);

        $concatString = '';
        foreach($signatureData AS $key => $value) {
            if (!is_null($value)) { // zend rest will skip null values
                $concatString .= $key . $value;
            }
        }

        // Note that secret is empty in db
        $secret = '';
        $prependSecret = $secret . $concatString;

        $hashString = hash('sha512', $prependSecret);
        $arguments["janus_sig"] = $hashString;

        return $arguments;
    }
}
