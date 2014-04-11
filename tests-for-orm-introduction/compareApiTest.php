<?php
/**
 * Note: this test script requires the following:
 * - A new version janus available at: https://serviceregistry-janus-test-new.test.surfconext.nl
 * - An old version of janus available at: https://serviceregistry-janus-test-old.test.surfconext.nl
 * - Both with a prod version of the db
 *
 *
 * Call with:
 * ./bin/phpunit tests-for-orm-introduction/compareApiTest.php
 *
 * Optionally you can use the --debug option for phpunit to see which connection a test is executed for.
 *
 * Also you can append (something like) these two commands before phpunit to enable xdebugging:
 *
 * export PHP_IDE_CONFIG="serverName=serviceregistry.demo.openconext.org" && \\
 * export XDEBUG_CONFIG="idekey=PhpStorm, remote_connect_back=0, remote_host=192.168.56.1" &&  \\
 *
 * By default the script tests each connection once, you can test fewer connections and/or run
 * duplicate requests in parallel by changing the MAX_xxx constants.
 */

require __DIR__ . "/../app/autoload.php";

class compareApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Change this to a number to limit the number of connections being tested.
     */
    const MAX_CONNECTIONS_TO_TEST = null;

    /**
     * Change this to a higher number to test parallel requests
     */
    const MAX_PARALLEL_REQUESTS = 1;

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
     * @var array
     */
    private static $percentages = array();

    /**
     * @var array
     */
    private static $averagePercentages = array();

    /**
     *
     */
    public function setUp()
    {
        $this->oldHttpClient = new \Guzzle\Http\Client(
            'https://serviceregistry-janus-test-old.test.surfconext.nl/simplesaml/module.php/janus/services/rest/'
        );
        $this->newHttpClient = new \Guzzle\Http\Client(
            'https://serviceregistry-janus-test-new.test.surfconext.nl/simplesaml/module.php/janus/services/rest/'
        );
    }

    public function testBothApisProvideAnEqualUser()
    {
        $responses = $this->callOldAndNewApi('getUser', array(
            'userid' => 'admin'
        ));

        $this->assertEquals($responses['new']->json(), $responses['old']->json());
    }

    public function testBothApisProvideEqualIdentifiersByMetadata()
    {
        $responses = $this->callOldAndNewApi('findIdentifiersByMetadata', array(
            'key' => 'name:en',
            'value' => 'e',
            'userid' => 'admin'
        ));

        $this->assertEquals($responses['new']->json(), $responses['old']->json());
    }

    public function testBothApisProvideAnEqualListOfSps()
    {
        $responses = $this->getSpListApiResponses();
        $this->assertEquals($this->sortConnections($responses['new']->json()), $this->sortConnections($responses['old']->json()));
    }

    /**
     * @dataProvider getSps
     */
    public function testBothApisProvideAnEqualSp($entityId)
    {
        $responses = $this->callOldAndNewApi('getEntity', array(
            'entityid' => $entityId
        ));

        $oldSp = $responses['old']->json();
        $newSp = $responses['new']->json();

        // Strip arp since arp id is replace by attributes which will never compare as equal
        unset($oldSp['arp']);
        unset($newSp['arp']);

        // Strip user since it might have been set to null by db migrations
        // when the user did not exist
        unset($oldSp['user']);
        unset($newSp['user']);

        $this->assertEquals($newSp, $oldSp);
    }

    /**
     * @dataProvider getSps
     */
    public function testBothApisProvideAnEqualAListOfIdpsTheSpCanConnectTo($entityId)
    {
        $responses = $this->callOldAndNewApi('getAllowedIdps', array(
            'spentityid' => $entityId
        ));

        $this->assertEquals($this->sortAcl($responses['new']->json()), $this->sortAcl($responses['old']->json()));
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

        $this->assertEquals($responses['new']->json(), $responses['old']->json());
    }

    /**
     * @dataProvider getSps
     */
    public function testBothApisProvideAnEqualSpArp($entityId)
    {
        $responses = $this->callOldAndNewApi('arp', array(
            'entityid' => $entityId
        ));

        $oldArp = $responses['old']->json();
        $newArp = $responses['new']->json();

        // Compare only attributes since name and description are lost since
        // arp attributes are merged into connections
        unset ($oldArp['name']);
        unset ($oldArp['description']);
        unset ($newArp['name']);
        unset ($newArp['description']);

        $this->assertEquals($newArp, $oldArp);
    }

    /**
     * @dataProvider getSps
     */
    public function testBothApisProvideEqualSpMetadata($entityId)
    {
        $responses = $this->callOldAndNewApi('getMetadata', array(
            'entityid' => $entityId
        ));

        $this->assertEquals($responses['new']->json(), $responses['old']->json());
    }

    public function testBothApisProvideAnEqualListOfIdps()
    {
        $responses = $this->getIdpListApiResponses();
        $this->assertEquals($this->sortConnections($responses['new']->json()), $this->sortConnections($responses['old']->json()));
    }

    /**
     * @dataProvider getIdps
     */
    public function testBothApisProvideAnEqualIdp($entityId)
    {
        $responses = $this->callOldAndNewApi('getEntity', array(
            'entityid' => $entityId
        ));

        $oldIdp = $responses['old']->json();
        $newIdp = $responses['new']->json();

        // Strip arp since arp id is replace by attributes which will never compare as equal
        unset($oldIdp['arp']);
        unset($newIdp['arp']);

        // Strip user since it might have been set to null by db migrations
        // when the user did not exist
        unset($oldIdp['user']);
        unset($newIdp['user']);

        $this->assertEquals($newIdp, $oldIdp);
    }

    /**
     * @dataProvider getIdps
     */
    public function testBothApisProvideEqualIdpMetadata($entityId)
    {
        $responses = $this->callOldAndNewApi('getMetadata', array(
            'entityid' => $entityId
        ));

        $this->assertEquals($responses['new']->json(), $responses['old']->json());
    }

    /**
     * @dataProvider getIdps
     */
    public function testBothApisProvideAnEqualAListOfSpsTheIdpCanConnectTo($entityId)
    {
        $responses = $this->callOldAndNewApi('getAllowedSps', array(
            'idpentityid' => $entityId
        ));

        $this->assertEquals($this->sortAcl($responses['new']->json()), $this->sortAcl($responses['old']->json()));
    }

    public function testShowReports()
    {

        print_r(static::$averagePercentages);
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

            $responses = $this->callOldAndNewApi('getSpList', array());

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
            if (static::MAX_CONNECTIONS_TO_TEST && count($connections) > static::MAX_CONNECTIONS_TO_TEST) {
                break;
            }

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
        echo $method . ' | old: ' . str_pad(round($timeOldMs), 5, ' ', STR_PAD_LEFT) . 'ms';

        $startTime = microtime(true);
        $responses['new'] = $this->createResponse($this->newHttpClient, $arguments);
        $endTime = microtime(true);
        $timeNewMs = ($endTime - $startTime) * 1000;
        echo ' | new: ' . str_pad(round($timeNewMs), 5, ' ', STR_PAD_LEFT) . 'ms';

        // Show time difference
        echo ' | diff: ' . str_pad(round($timeNewMs - $timeOldMs), 5, ' ', STR_PAD_LEFT) . 'ms';
        $percentage = round(($timeNewMs / $timeOldMs) * 100);

        // Show percentual time difference
        echo ' | perc: ' . str_pad($percentage, 3, ' ', STR_PAD_LEFT) . '%';
        static::$percentages[$method][] = $percentage;
        $averagePercentage = round(array_sum(static::$percentages[$method]) / count(static::$percentages[$method]));
        static::$averagePercentages[$method] = $averagePercentage;
        echo ' | average perc ' . str_pad($averagePercentage, 3, ' ', STR_PAD_LEFT) . '%';
        echo PHP_EOL;
        return $responses;
    }

    private function createResponse(\Guzzle\Http\Client $client, array $arguments)
    {
        try {
            $requests = array();
            for ($i = 0; $i < static::MAX_PARALLEL_REQUESTS; $i++) {
                $request = $client->get('', array(), array(
                    'query' => $this->addSignature($arguments)
                ));
                $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYHOST, false);
                $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);
                $requests[] = $request;
            }
            $responses = $client->send($requests);
            return $responses[0];
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
        foreach ($signatureData AS $key => $value) {
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

    /**
     * Sorts Acl for comparison
     *
     * @param array $acl
     * @return array
     */
    private function sortAcl(array $acl)
    {
        sort($acl);
        return $acl;
    }

    /**
     * Sorts metadata of each connection so it can be compared.
     *
     * @param array $connections
     */
    private function sortConnections(array $connections)
    {
        foreach ($connections as &$sp) {
            $sp = $this->sortMetadata($sp);
        }

        return $connections;
    }

    /**
     * Sorts disable consent entries in metadata so they can be compared.
     *
     * @param array $metadata
     */
    private function sortMetadata(array $metadata)
    {
        $disableConsentPrefix = 'disableConsent:';

        $metadataSorted = array();
        $disableConsentConnections = array();
        foreach ($metadata as $key => $value) {
            // Remove disable consent items from metadata
            if (strstr($key, $disableConsentPrefix)) {
                $disableConsentConnections[] = $value;
                continue;
            }

            $metadataSorted[$key] = $value;
        }

        // Add sorted disabled consent items back to metadata
        sort($disableConsentConnections);
        foreach ($disableConsentConnections as $index => $value) {
            $metadataSorted[$disableConsentPrefix . $index] = $value;
        }

        return $metadataSorted;
    }
}
