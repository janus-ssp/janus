<?php

namespace Janus\ServiceRegistryBundle\Tests\Controller;

use Guzzle\Common\Collection;
use Guzzle\Http\Message\Response;
use Phake;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\ArrayInput;

use Doctrine\ORM\EntityManager;

use Nelmio\Alice\Fixtures;
use Nelmio\Alice\ORM\Doctrine as Persister;

/**
 * @todo split test for web and api test cases
 */
class ConnectionControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /** @var \Guzzle\Http\Client */
    private $oauthHttpClient;

    public function setUp()
    {
        ini_set('date.timezone', 'GMT');

        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $this->client = $this->createAuthenticatingClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        // re-create db
        $params = $this->entityManager->getConnection()->getParams();
        if (file_exists($params['path'])) {
            unlink($params['path']);
        }
        $application->run(new StringInput('doctrine:schema:create'), new NullOutput());

        $this->loadFixtures($this->entityManager);

        $this->oauthHttpClient = $this->client->getContainer()->get('janus_security_bundle.http_client');

        $messageRequest = Phake::mock('Guzzle\Http\Message\Request');
        Phake::when($messageRequest)->getQuery()->thenReturn(Phake::mock('Guzzle\Http\QueryString'));
        Phake::when($messageRequest)->getCurlOptions()->thenReturn(new Collection());
        Phake::when($messageRequest)->send()->thenReturn(new Response(200, null, <<<JSON
{
    "audience":"test-client",
    "scopes":[
        "actions"
    ],
    "principal":{
        "name":"test-client",
        "attributes":[]
    }
}
JSON
));
        Phake::when($this->oauthHttpClient)
            ->get('v1/tokeninfo')
            ->thenReturn($messageRequest);
    }

    private function loadFixtures(EntityManager $entityManager)
    {
        $users = Fixtures::load(__DIR__ . '/../Resources/fixtures/users.yml', $entityManager);
        $persister = new Persister($entityManager);
        $persister->persist($users);
    }

    /**
     * @param bool $authenticated
     * @return Client
     */
    private function createAuthenticatingClient($authenticated = true)
    {
        $params = array();
        if ($authenticated) {
            $params = array_merge($params, array(
                'HTTP_AUTHORIZATION' => "Bearer ca2b078e-3316-4bf9-8f46-26ed2fb8ca18",
                'CONTENT_TYPE' => 'application/json',
            ));
        }

        return static::createClient(array(), $params);
    }

    public function testGetConnectionsHead()
    {
        $this->client->request("HEAD", "/api/connections.json");

        $response = $this->client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    }

    public function testGetConnectionsReturnsEmptyCollection()
    {
        $this->client->request('GET', '/api/connections.json');
        $response = $this->client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"connections":[]}', $response->getContent());
    }

    public function testGetConnectionsReturnCollection()
    {
        $this->loadIdpConnectionFixture();

        $this->client->request('GET', '/api/connections.json');
        $response = $this->client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());

        $expectedResponse = <<<JSON
{"connections":{"saml20-idp":{"1":{"updated_by_user_id":1,"updated_from_ip":"127.0.0.1","id":1,"name":"test-idp","revision_nr":0,"type":"saml20-idp","revision_note":"initial revision","created_at_date":"1970-01-01T00:00:00+0000"}}}}
JSON;
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testGetConnectionFailsWhenConnectionDoesNotExist()
    {
        $this->client->request('GET', '/api/connections/1.json');
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"code":404,"message":"Connection does not exist."}', $response->getContent());
    }

    public function testGetConnection()
    {
        $this->loadIdpConnectionFixture();

        $this->client->request('GET', '/api/connections/1.json');
        $response = $this->client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $expectedResponse = <<<JSON
{"updated_by_user_id":1,"updated_from_ip":"127.0.0.1","id":1,"name":"test-idp","revision_nr":0,"type":"saml20-idp","revision_note":"initial revision","created_at_date":"1970-01-01T00:00:00+0000"}
JSON;
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testNewConnection()
    {
        $this->client->request('GET', '/api/connections/new.json');
        $response = $this->client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());

        /**
         * @todo this is a workaround, create a better solution for this using special
         * test metadata config to make responses predictable
         */
        $expectedKeys = array(
            'name',
            'state',
            'type',
            'expirationDate',
            'metadataUrl',
            'metadataValidUntil',
            'metadataCacheUntil',
            'allowAllEntities',
            'arpAttributes',
            'manipulationCode',
            'parentRevisionNr',
            'revisionNote',
            'notes',
            'isActive',
            'metadata'
        );
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('children', $content);
        $contentKeys = array_keys($content['children']);
        $this->assertEquals($expectedKeys, $contentKeys);
    }

    public function testPostConnection()
    {
        $this->client->request('POST', '/api/connections.json', array(
            'connection' => array(
                'name' => 'test-idp',
                'type' => 'saml20-idp',
                'revisionNote' => 'initial revision'
            )
        ));

        $response = $this->client->getResponse();
        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());
        $this->assertJsonHeader($response);
        $this->assertTrue($response->headers->contains('location', 'http://localhost/api/connections'));
    }

    public function testEditConnectionFailsWhenConnectionDoesNotExist()
    {
        $this->client->request('GET', '/api/connections/1 /edit.json');
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"code":404,"message":"Connection does not exist."}', $response->getContent());
    }

    public function testEditConnection()
    {
        $this->loadIdpConnectionFixture();

        $this->client->request('GET', '/api/connections/1/edit.json');
        $response = $this->client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        /** @todo create a special metadata field configuration for test to make results predictable */
        $expectedResponse = <<<JSON
{"children":{"name":[],"state":[],"type":[],"expirationDate":{"children":{"date":{"children":{"year":[],"month":[],"day":[]}},"time":{"children":{"hour":[],"minute":[]}}}},"metadataUrl":[],"metadataValidUntil":{"children":{"date":{"children":{"year":[],"month":[],"day":[]}},"time":{"children":{"hour":[],"minute":[]}}}},"metadataCacheUntil":{"children":{"date":{"children":{"year":[],"month":[],"day":[]}},"time":{"children":{"hour":[],"minute":[]}}}},"allowAllEntities":[],"arpAttributes":[],"manipulationCode":[],"parentRevisionNr":[],"revisionNote":[],"notes":[],"isActive":[],"metadata":{"children":{"SingleSignOnService":[],"SingleLogoutService":[],"certData":[],"certData2":[],"certData3":[],"certFingerprint":[],"certificate":[],"name":[],"description":[],"url":[],"icon":[],"contacts":[],"OrganizationName":[],"OrganizationDisplayName":[],"OrganizationURL":[],"redirect":{"children":{"sign":[],"validate":[]}},"base64attributes":[],"assertion":{"children":{"encryption":[]}},"NameIDFormat":[]}}}}
JSON;
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testPutConnectionFailsWhenConnectionDoesNotExist()
    {
        $this->client->request('PUT', '/api/connections/1.json', array(
            'connection' => array(
                'name' => 'test',
            )
        ));
        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"code":404,"message":"Connection does not exist."}', $response->getContent());
    }

    public function testPutConnectionFailsWhenInvalidDataIsSupplied()
    {
        // Test with incorrect data
        $this->loadIdpConnectionFixture();

        $this->client->request('PUT', '/api/connections/1.json', array(
            'connection' => array(
                'name' => 'test',
            )
        ));
        $response = $this->client->getResponse();

        // @todo add validation tests
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testPutConnectionIsUpdated()
    {
        $this->loadIdpConnectionFixture();

        $this->client->request('PUT', '/api/connections/1.json', array(
            'connection' => array(
                'name' => 'test',
                'type' => 'saml20-idp',
                'revisionNote' => 'test'
            )
        ));
        $response = $this->client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('location', 'http://localhost/api/connections'));
    }

    public function testRemoveConnectionDoesNotReturnLocationWhenConnectionDoesNotExist()
    {
        $this->client->request('GET', '/api/connections/1/remove.json');
        $response = $this->client->getResponse();

        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('', $response->getContent());
    }

    public function testRemoveConnection()
    {
        $this->loadIdpConnectionFixture();

        $this->client->request('GET', '/api/connections/1/remove.json');
        $response = $this->client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('location', 'http://localhost/api/connections'));
    }

    public function testDeleteConnectionDoesNotReturnLocationWhenConnectionDoesNotExist()
    {
        $this->client->request('DELETE', '/api/connections/1.json');
        $response = $this->client->getResponse();

        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('', $response->getContent());
    }

    public function testDeleteConnection()
    {
        $this->loadIdpConnectionFixture();

        $this->client->request('DELETE', '/api/connections/1.json');
        $response = $this->client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('location', 'http://localhost/api/connections'));
    }

    protected function loadIdpConnectionFixture()
    {
        $persister = new Persister($this->entityManager);
        $connection = Fixtures::load(__DIR__ . '/../Resources/fixtures/idp-connection.yml', $this->entityManager);
        $persister->persist($connection);
    }

    protected function assertJsonHeader($response)
    {
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            $response->headers
        );
    }
}