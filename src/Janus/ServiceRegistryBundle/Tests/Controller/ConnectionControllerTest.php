<?php

namespace Janus\ServiceRegistryBundle\Tests\Controller;

use Phake;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\ArrayInput;

use Doctrine\ORM\EntityManager;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;

use Nelmio\Alice\Fixtures;
use Nelmio\Alice\ORM\Doctrine as Persister;

class ConnectionControllerTest extends WebTestCase
{
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $application = new Application(static::$kernel);
        $this->createDb($application);

        $client = $this->getClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $this->loadFixtures($entityManager);
    }

    private function createDb(\Symfony\Component\Console\Application $application)
    {
        // drop the database
        $command = new DropDatabaseDoctrineCommand();
        $application->add($command);
        $input = new ArrayInput(array(
            'command' => 'doctrine:database:drop',
            '--force' => true
        ));
        $command->run($input, new NullOutput());

        // we have to close the connection after dropping the database so we don't get "No database selected" error
        $connection = $application->getKernel()->getContainer()->get('doctrine')->getConnection();
        if ($connection->isConnected()) {
            $connection->close();
        }

        // create the database
        $command = new CreateDatabaseDoctrineCommand();
        $application->add($command);
        $input = new ArrayInput(array(
            'command' => 'doctrine:database:create',
        ));
        $command->run($input, new NullOutput());

        // create schema
        $command = new CreateSchemaDoctrineCommand();
        $application->add($command);
        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:create',
        ));
        $command->run($input, new NullOutput());

        // get the Entity Manager
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    private function loadFixtures(EntityManager $entityManager)
    {
        $users = Fixtures::load(__DIR__ . '/../Resources/fixtures/users.yml', $entityManager);
        $persister = new Persister($entityManager);
        $persister->persist($users);
    }

    private function getClient($authenticated = false)
    {
        $params = array();
        if ($authenticated) {
            $params = array_merge($params, array(
                'PHP_AUTH_USER' => 'restapi',
                'PHP_AUTH_PW' => 'secretpw',
            ));
        }

        return static::createClient(array(), $params);
    }

    public function testGetConnections()
    {
        $client = $this->getClient(true);

        // head request
        $client->request('HEAD', '/api/connections.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());

        // empty list
        $client->request('GET', '/api/connections.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"connections":[]}', $response->getContent());

        // list
        $this->createConnection($client, 'test-idp');

        $client->request('GET', '/api/connections.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());

        $expectedResponse = <<<JSON
{"connections":{"saml20-idp":{"1":{"updated_by_user_id":1,"updated_from_ip":"127.0.0.1","id":1,"name":"test-idp","revision_nr":0,"type":"saml20-idp","allow_all_entities":false,"revision_note":"Test revision","is_active":false,"created_at_date":"1970-01-01T00:00:00+0100","metadata":{"items":[]},"allowed_connections":[],"blocked_connections":[],"disable_consent_connections":[]}}}}
JSON;
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testGetConnection()
    {
        $client = $this->getClient(true);

        $client->request('GET', '/api/connections/1.json');
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"code":404,"message":"Connection does not exist."}', $response->getContent());

        $this->createConnection($client, 'test-idp');

        $client->request('GET', '/api/connections/1.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $expectedResponse = <<<JSON
{"updated_by_user_id":1,"updated_from_ip":"127.0.0.1","id":1,"name":"test-idp","revision_nr":0,"type":"saml20-idp","allow_all_entities":false,"revision_note":"Test revision","is_active":false,"created_at_date":"1970-01-01T00:00:00+0100","metadata":{"items":[]},"allowed_connections":[],"blocked_connections":[],"disable_consent_connections":[]}
JSON;
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testNewConnection()
    {
        $client = $this->getClient(true);

        $client->request('GET', '/api/connections/new.json');
        $response = $client->getResponse();

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
        $client = $this->getClient(true);

        $this->createConnection($client, 'test-idp');

        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('location', 'http://localhost/api/connections'));
    }

    public function testEditConnection()
    {
        $client = $this->getClient(true);

        $client->request('GET', '/api/connections/1 /edit.json');
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"code":404,"message":"Connection does not exist."}', $response->getContent());

        $this->createConnection($client, 'test-idp');

        $client->request('GET', '/api/connections/1/edit.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        /** @todo create a special metadata field configuration for test to make results predictable */
        $expectedResponse = <<<JSON
{"children":{"name":[],"state":[],"type":[],"expirationDate":{"children":{"date":{"children":{"year":[],"month":[],"day":[]}},"time":{"children":{"hour":[],"minute":[]}}}},"metadataUrl":[],"metadataValidUntil":{"children":{"date":{"children":{"year":[],"month":[],"day":[]}},"time":{"children":{"hour":[],"minute":[]}}}},"metadataCacheUntil":{"children":{"date":{"children":{"year":[],"month":[],"day":[]}},"time":{"children":{"hour":[],"minute":[]}}}},"allowAllEntities":[],"arpAttributes":[],"manipulationCode":[],"parentRevisionNr":[],"revisionNote":[],"notes":[],"isActive":[],"metadata":{"children":{"name":[],"displayName":[],"description":[],"certData":[],"certData2":[],"certData3":[],"SingleLogoutService_Binding":[],"SingleLogoutService_Location":[],"NameIDFormat":[],"contacts":[],"OrganizationName":[],"OrganizationDisplayName":[],"OrganizationURL":[],"logo":[],"redirect":{"children":{"sign":[]}},"coin":{"children":{"publish_in_edugain":[],"publish_in_edugain_date":[],"additional_logging":[],"guest_qualifier":[],"schachomeorganization":[],"institution_id":[],"disable_scoping":[],"hidden":[]}},"SingleSignOnService":[],"keywords":[],"shibmd":{"children":{"scope":[]}}}}}}
JSON;
        $this->assertEquals($expectedResponse, $response->getContent());
    }

//    public function testPutConnection()
//    {
//        $client = $this->getClient(true);
//
//        $client->request('PUT', '/api/connections/1.json', array(
//            'connection' => array(
//                'name' => 'test',
//            )
//        ));
//        $response = $client->getResponse();
//
//        $this->assertEquals(400, $response->getStatusCode(), $response->getContent());
//        $content = json_decode($response->getContent());
//        $this->assertEquals('400', $content->code);
//        $this->assertEquals('Validation Failed', $content->message);
//        $this->assertEquals('This value should not be blank.', $content->errors->children->name->errors[0]);
//
//        $this->createConnection($client, 'test-idp');
//
//        $client->request('PUT', '/api/connections/1.json', array(
//            'connection' => array(
//                'name' => 'test',
//                'type' => 'saml20-idp'
//            )
//        ));
//        $response = $client->getResponse();
//
//        // @todo test changing as well as creating
//
//        $this->assertJsonHeader($response);
//        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
//        $this->assertTrue($response->headers->contains('location', 'http://localhost/api/connections'));
//    }

    public function testRemoveConnection()
    {
        $client = $this->getClient(true);

        $client->request('GET', '/api/connections/1/remove.json');
        $response = $client->getResponse();

        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('', $response->getContent());

        $this->createConnection($client, 'test-idp');

        $client->request('GET', '/api/connections/1/remove.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('location', 'http://localhost/api/connections'));
    }

    public function testDeleteConnection()
    {
        $client = $this->getClient(true);

        $client->request('DELETE', '/api/connections/1.json');
        $response = $client->getResponse();

        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('', $response->getContent());

        $this->createConnection($client, 'test-idp');

        $client->request('DELETE', '/api/connections/1.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('location', 'http://localhost/api/connections'));
    }

    protected function createConnection(Client $client, $name)
    {
        $client->request('POST', '/api/connections.json', array(
            'connection' => array(
                'name' => $name,
                'type' => 'saml20-idp',
                'revisionNote' => 'Test revision'
            )
        ));
        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());
    }

    protected function assertJsonHeader($response)
    {
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            $response->headers
        );
    }
}