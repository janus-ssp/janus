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

class OldApiTest extends \PHPUnit_Framework_TestCase
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
     *
     */
    public function setUp()
    {
        $this->oldHttpClient = new \Guzzle\Http\Client(
            'https://serviceregistry.demo.openconext.org/simplesaml/module.php/janus/services/rest/'
        );
        $this->newHttpClient = new \Guzzle\Http\Client(
            'https://serviceregistry-janus-1.16.demo.openconext.org/simplesaml/module.php/janus/services/rest/'
        );
    }

    public function testBothApisProvideAnEqualUser()
    {
        $this->execMethod('getUser', array(
            'userid' => 'admin'
        ));
    }

    public function testBothApisProvideEqualIdentifiersByMetadata()
    {
        $this->execMethod('findIdentifiersByMetadata', array(
            'key' => 'name:en',
            'value' => 'e',
            'userid' => 'admin'
        ));
    }

    /**
     * @dataProvider getSps
     */
    public function testBothApisProvideAnEqualSp($entityId)
    {
        $this->execMethod('getEntity', array(
            'entityid' => $entityId
        ));
    }

    /**
     * @dataProvider getSps
     */
    public function testBothApisProvideAnEqualAListOfIdpsTheSpCanConnectTo($entityId)
    {
        $this->execMethod('getAllowedIdps', array(
            'spentityid' => $entityId
        ));
    }

    /**
     * @dataProvider getSps
     */
    public function testBothApisProvideIfAnSpIsAllowedToConnectToIdp($entityId)
    {
        $this->execMethod('isConnectionAllowed', array(
            'spentityid' => $entityId,
            'idpentityid' => 'https://surfguest.nl/test'

        ));
    }

    /**
     * @dataProvider getSps
     */
    public function testBothApisProvideAnEqualSpArp($entityId)
    {
        $this->execMethod('arp', array(
            'entityid' => $entityId
        ));
    }

    /**
     * @dataProvider getSps
     */
    public function testBothApisProvideEqualSpMetadata($entityId)
    {
        $this->execMethod('getMetadata', array(
            'entityid' => $entityId
        ));
    }

    /**
     * @dataProvider getIdps
     */
    public function testBothApisProvideAnEqualIdp($entityId)
    {
        $this->execMethod('getEntity', array(
            'entityid' => $entityId
        ));
    }

    /**
     * @dataProvider getIdps
     */
    public function testBothApisProvideEqualIdpMetadata($entityId)
    {
        $this->execMethod('getMetadata', array(
            'entityid' => $entityId
        ));
    }

    /**
     * @dataProvider getIdps
     */
    public function testBothApisProvideAnEqualAListOfSpsTheIdpCanConnectTo($entityId)
    {
        $this->execMethod('getAllowedSps', array(
            'idpentityid' => $entityId
        ));
    }

    public function getSps()
    {
        static $connections = array();

        if (empty($connections)) {
            $this->setUp();
            $spListResponse = $this->createResponse($this->oldHttpClient, array_merge(
                    array('method' => 'getSpList'),
                    $this->defaultArguments)
            );

            $connections = $this->createEntityListFromResponse($spListResponse);
        }

        return $connections;
    }

    public function getIdps()
    {
        static $idps = array();

        if (empty($idps)) {

            $this->setUp();
            $idpListResponse = $this->createResponse($this->oldHttpClient, array_merge(
                    array('method' => 'getIdpList'),
                    $this->defaultArguments)
            );

            $idps = $this->createEntityListFromResponse($idpListResponse);
        }

        return $idps;
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
     * @param array $methods
     * @param array $parameters
     */
    private function execMethods(array $methods, array $parameters)
    {
        foreach ($methods as $method => $methodArguments) {
            $this->execMethod($method, $methodArguments);
        }
    }

    /**
     * @param string $method
     * @param array $methodArguments
     */
    private function execMethod($method, array $methodArguments)
    {
        $arguments['method'] = $method;
        $arguments = array_merge($arguments, $this->defaultArguments, $methodArguments);

        foreach ($arguments as $argument => $argumentValue) {
            if (isset($parameters[$argumentValue])) {
                $arguments[$argument] = $parameters[$argumentValue];
            }
        }

        $oldResponse = $this->createResponse($this->oldHttpClient, $arguments);
        $newResponse = $this->createResponse($this->oldHttpClient, $arguments);
        $this->assertEquals($oldResponse->json(), $newResponse->json());
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
}