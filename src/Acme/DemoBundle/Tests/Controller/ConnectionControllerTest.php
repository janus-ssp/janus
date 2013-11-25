<?php

namespace Acme\DemoBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;

class ConnectionControllerTest extends WebTestCase
{
    private function getClient($authenticated = false)
    {
        $params = array();
        if ($authenticated) {
            $params = array_merge($params, array(
                'PHP_AUTH_USER' => 'restapi',
                'PHP_AUTH_PW'   => 'secretpw',
            ));
        }

        return static::createClient(array(), $params);
    }
    public function testGetConnections()
    {
        $client = $this->getClient(true);

        // head request
        $client->request('HEAD', '/connections.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());

        // empty list
        $client->request('GET', '/connections.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"connections":[],"limit":5}', $response->getContent());

        // list
        $this->createConnection($client, 'my connection for list');

        $client->request('GET', '/connections.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"connections":[{"message":"my connection for list","links":{"self":{"href":"http:\/\/localhost\/connections\/0"}}}],"limit":5}', $response->getContent());
    }

    public function testGetConnection()
    {
        $client = $this->getClient(true);

        $client->request('GET', '/connections/0.json');
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"code":404,"message":"Connection does not exist."}', $response->getContent());

        $this->createConnection($client, 'my connection for get');

        $client->request('GET', '/connections/0.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"message":"my connection for get","links":{"self":{"href":"http:\/\/localhost\/connections\/0"}}}', $response->getContent());
    }

    public function testNewConnection()
    {
        $client = $this->getClient(true);

        $client->request('GET', '/connections/new.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"children":{"message":[]}}', $response->getContent());
    }

    public function testPostConnection()
    {
        $client = $this->getClient(true);

        $this->createConnection($client, 'my connection for post');

        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('location', 'http://localhost/connections'));
    }

    public function testEditConnection()
    {
        $client = $this->getClient(true);

        $client->request('GET', '/connections/0/edit.json');
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"code":404,"message":"Connection does not exist."}', $response->getContent());

        $this->createConnection($client, 'my connection for post');

        $client->request('GET', '/connections/0/edit.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"children":{"message":[]}}', $response->getContent());
    }

    public function testPutConnection()
    {
        $client = $this->getClient(true);

        $client->request('PUT', '/connections/0.json', array(
            'connection' => array(
                'message' => ''
            )
        ));
        $response = $client->getResponse();

        $this->assertEquals(400, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"code":400,"message":"Validation Failed","errors":{"children":{"message":{"errors":["This value should not be blank."]}}}}', $response->getContent());

        $this->createConnection($client, 'my connection for post');

        $client->request('PUT', '/connections/0.json', array(
            'connection' => array(
                'message' => 'my connection for put'
            )
        ));
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('location', 'http://localhost/connections'));
    }

    public function testRemoveConnection()
    {
        $client = $this->getClient(true);

        $client->request('GET', '/connections/0/remove.json');
        $response = $client->getResponse();

        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('', $response->getContent());

        $this->createConnection($client, 'my connection for get');

        $client->request('GET', '/connections/0/remove.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('location', 'http://localhost/connections'));
    }

    public function testDeleteConnection()
    {
        $client = $this->getClient(true);

        $client->request('DELETE', '/connections/0.json');
        $response = $client->getResponse();

        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('', $response->getContent());

        $this->createConnection($client, 'my connection for get');

        $client->request('DELETE', '/connections/0.json');
        $response = $client->getResponse();

        $this->assertJsonHeader($response);
        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('location', 'http://localhost/connections'));
    }

    protected function createConnection(Client $client, $message)
    {
        $client->request('POST', '/connections.json', array(
            'connection' => array(
                'message' => $message
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