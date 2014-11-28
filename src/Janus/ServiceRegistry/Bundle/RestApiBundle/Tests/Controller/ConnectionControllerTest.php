<?php

namespace Janus\ServiceRegistryBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

use Doctrine\ORM\EntityManager;

use Nelmio\Alice\Fixtures;
use Nelmio\Alice\ORM\Doctrine as Persister;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @todo split the tests up so the do not do multiple assertions an can be named better
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

    public function setUp()
    {
        ini_set('date.timezone', 'GMT');

        $this->client = $this->createAuthenticatingClient();

        $application = new Application(static::$kernel);
        $application->setAutoExit(false);


        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $this->createDb($application, $this->entityManager);
        $this->createAdminUser($this->entityManager);
    }


    // GET Collection

    public function testReturnsHeadResponse()
    {
        $this->client->request("HEAD", "/api/connections.json");

        $response = $this->client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    }

    public function testReturnsEmptyCollection()
    {
        $this->client->request('GET', '/api/connections.json');
        $response = $this->client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{}', $response->getContent());
    }

    public function testReturnsCollection()
    {
        $this->createConnection();

        $this->client->request('GET', '/api/connections.json');
        $response = $this->client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());

        $expectedResponse = <<<JSON
{"connections":{"saml20-idp":{"1":{"updatedByUserName":"admin","updatedFromIp":"127.0.0.1","id":1,"name":"test-idp","revisionNr":0,"state":"testaccepted","type":"saml20-idp","allowAllEntities":true,"arpAttributes":{},"revisionNote":"initial revision","isActive":true,"createdAtDate":"1970-01-01T00:00:00+0000","updatedAtDate":"1970-01-01T00:00:00+0000","allowedConnections":[],"blockedConnections":[],"disableConsentConnections":[]}}}}
JSON;
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    // GET

    public function testReturnsErrorForNonExistingConnection()
    {
        $this->client->request('GET', '/api/connections/1.json');
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
    }

    public function testReturnsConnection()
    {
        $this->createConnection();

        $this->client->request('GET', '/api/connections/1.json');
        $response = $this->client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $expectedResponse = <<<JSON
{"updatedByUserName":"admin","updatedFromIp":"127.0.0.1","id":1,"name":"test-idp","revisionNr":0,"state":"testaccepted","type":"saml20-idp","allowAllEntities":true,"revisionNote":"initial revision","isActive":true,"createdAtDate":"1970-01-01T00:00:00+0000","updatedAtDate":"1970-01-01T00:00:00+0000","metadata":{"SingleSignOnService":[{"Location":"foo"}]},"allowedConnections":[],"blockedConnections":[],"disableConsentConnections":[]}
JSON;
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    // POST

    public function testCreatesConnection()
    {
        $this->createConnection();

        $response = $this->client->getResponse();
        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());
        $this->assertJsonHeader($response);
    }

    // PUT

    public function testDoesNotUpdateNonExistingConnection()
    {
        $this->client->request('PUT', '/api/connections/1.json', array(
            'name' => 'test',

        ));
        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
    }

    public function testDoesNotUpdateConnectionWhenDataIsInvalid()
    {
        $this->createConnection();

        $this->client->request('PUT', '/api/connections/1.json', array(
            'name' => null,
        ));
        $response = $this->client->getResponse();

        // @todo add validation tests
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testUpdatesConnection()
    {
        $this->createConnection();

        $this->client->request('PUT', '/api/connections/1.json', array(
            'name' => 'test',
            'type' => 'saml20-idp',
            'revisionNote' => 'test'
        ));
        $response = $this->client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());
    }

    // DELETE

    public function testDoesNotDeleteNonExistingConnection()
    {
        $this->deleteConnection();
        $response = $this->client->getResponse();

        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('', $response->getContent());
    }

    public function testDeletesConnection()
    {
        $this->createConnection();
        $this->deleteConnection();

        $response = $this->client->getResponse();

        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('location', 'http://localhost/api/connections'));
    }

    /**
     * @param Application $application
     * @param EntityManager $entityManager
     * @throws \Exception
     */
    private function createDb(Application $application, EntityManager $entityManager)
    {
        // To debug schema creation use ConsoleOutput instead of NullOutput
        $application->run(new StringInput('doctrine:schema:drop --force'), new NullOutput());
        $application->run(new StringInput('doctrine:schema:create'), new NullOutput());
    }

    /**
     * @param EntityManager $entityManager
     */
    private function createAdminUser(EntityManager $entityManager)
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
        $kernelOptions = array();
        $symfonyEnvironment = getenv('SYMFONY_ENV');
        if ($symfonyEnvironment) {
            $kernelOptions['environment'] = $symfonyEnvironment;
        }

        $params = array();
        if ($authenticated) {
            $params = array_merge($params, array(
                'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('admin:test'),
                'CONTENT_TYPE' => 'application/json',
            ));
        }

        return static::createClient($kernelOptions, $params);
    }

    private function deleteConnection()
    {
        $this->client->request('DELETE', '/api/connections/1.json');
    }

    protected function loadIdpConnectionFixture()
    {
        // Since updating a connection needs information about the user adding/changing data for audit
        // purposes login first.
        $this->logIn();

        $persister = new Persister($this->entityManager);
        $connection = Fixtures::load(__DIR__ . '/../Resources/fixtures/idp-connection.yml', $this->entityManager);
        $persister->persist($connection);

        $this->logOut();
    }

    /**
     * Simulates a login
     */
    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'secured_area';
        $token = new UsernamePasswordToken('admin', null, $firewall, array('ROLE_ADMIN'));
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * Resets information of logged in user
     */
    private function logOut()
    {
        $this->client->getContainer()->get('security.context')->setToken(null);
        $this->client->getContainer()->get('request')->getSession()->invalidate();
    }

    protected function assertJsonHeader($response)
    {
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            $response->headers
        );
    }

    private function createConnection()
    {
        $this->client->request('POST', '/api/connections.json', array(
            'name' => 'test-idp',
            'type' => 'saml20-idp',
            'revisionNote' => 'initial revision',
            'metadata' => array(
                'SingleSignOnService' => array(
                    array(
                        'Location' => 'foo'
                    )
                )
            )
        ));
    }
}