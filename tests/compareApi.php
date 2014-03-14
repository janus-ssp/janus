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

    private $genericMethods = array(
        'getEntity' => array(
            'entityid' => 'entityid'
        )
    );

    private $idpOnlyMethods = array(
        'getAllowedSps' => array(
            'idpentityid' => 'entityid'
        )
    );

    /**
     * @var array
     */
    private $idpMethods;

    private $spOnlyMethods = array(
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
     * @var array
     */
    private $spMethods;

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

        $this->spMethods = array_merge($this->genericMethods, $this->spOnlyMethods);
        $this->idpMethods = array_merge($this->genericMethods, $this->idpOnlyMethods);
    }

    /**
     * @dataProvider getSps
     */
    public function testSpCalls($entityId)
    {
        $this->execMethods($this->spMethods, array(
            'entityid' => $entityId
        ));
    }

    /**
     * @dataProvider getIdps
     */
    public function testIdpCalls($entityId)
    {
        $this->execMethods($this->idpMethods, array(
            'entityid' => $entityId
        ));
    }

    public function getSps()
    {
        $this->setUp();
        $spListResponse = $this->createResponse($this->oldHttpClient, array_merge(
                array('method' => 'getSpList'),
                $this->defaultArguments)
        );

        return $this->createEntityListFromResponse($spListResponse);
    }

    public function getIdps()
    {
        $this->setUp();
        $idpListResponse = $this->createResponse($this->oldHttpClient, array_merge(
                array('method' => 'getIdpList'),
                $this->defaultArguments)
        );

        return $this->createEntityListFromResponse($idpListResponse);
    }

    /**
     * @param array $entites
     * @return array
     */
    private function createEntityListFromResponse(\Guzzle\Http\Message\Response $response)
    {

        $sps = array();
        foreach ($response->json() as $entityId => $sp) {
            $sps[] = array($entityId);
        }
        return $sps;
    }

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

            $oldResponse = $this->createResponse($this->oldHttpClient, $arguments);
            $newResponse = $this->createResponse($this->oldHttpClient, $arguments);
            $this->assertEquals($oldResponse->json(), $newResponse->json());
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
}