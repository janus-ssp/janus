<?php

namespace Test;

use sspmod_janus_UserController;

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use Janus\ServiceRegistry\Connection\ConnectionDtoCollection;
use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Entity\User;
use Janus\ServiceRegistry\Connection\ConnectionDto;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Doctrine\ORM\EntityManager;

use Nelmio\Alice\Fixtures;
use Nelmio\Alice\ORM\Doctrine as Persister;

use Phake;

class UserControllerTest extends WebTestCase
{
    /** @var string */
    private $fixturesDir;

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

        $this->fixturesDir = realpath(__DIR__ . '/../Resources/fixtures');

        $this->client = $this->createTestClient();

        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $this->flushCache();
        $this->createDb($application, $this->entityManager);

        $this->createAdminUser($this->entityManager);
    }

    public function testReturnsListOfEntityModels()
    {
        $configProxy = new ConfigProxy(array());
        $securityContextMock = Phake::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $connectionService = Phake::mock('Janus\ServiceRegistry\Service\ConnectionService');

        $connectionDto = new ConnectionDto();
        $connectionDto->id = 1;
        $connectionDto->revisionNr = 1;
        Phake::when($connectionService)
            ->findDescriptorsForFilters(Phake::anyParameters())
            ->thenReturn(new ConnectionDtoCollection(array($connectionDto)));

        $userController = new sspmod_janus_UserController(
            $configProxy,
            $securityContextMock,
            $connectionService
        );
        $userMock = Phake::mock('sspmod_janus_User');
        $userController->setUser($userMock);

        $this->loadIdpConnectionFixture();

        $list = $userController->getEntities();
        $this->assertTrue(is_array($list));
        $this->assertInstanceOf('sspmod_janus_Entity', $list[0]);
    }

    /**
     * @return Client
     */
    protected  function createTestClient()
    {
        $kernelOptions = array();
        $symfonyEnvironment = getenv('SYMFONY_ENV');
        if ($symfonyEnvironment) {
            $kernelOptions['environment'] = $symfonyEnvironment;
        }

        $params = array();
        return static::createClient($kernelOptions, $params);
    }

    private function flushCache()
    {
        /** @var \Doctrine\Common\Cache\MemcacheCache $cacheProvider */
        $cacheProvider = $this->client->getContainer()->get('doctrine_cache.providers.memcache_cache');
        $cacheProvider->deleteAll();
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
        $users = Fixtures::load($this->fixturesDir . '/users.yml', $entityManager);
        $persister = new Persister($entityManager);
        $persister->persist($users);
    }

    /**
     * Simulates a login
     */
    private function logIn()
    {
        $user = $this->entityManager->getRepository('Janus\ServiceRegistry\Entity\User')->find(1);
        $firewall = 'secured_area';
        $token = new UsernamePasswordToken($user, null, $firewall, array('ROLE_ADMIN'));

        $this->client
            ->getContainer()
            ->get('security.context')
            ->setToken($token);
    }

    /**
     * Since updating a connection needs information about the user adding/changing data for audit
     * purposes login first.
     */
    protected function loadIdpConnectionFixture()
    {
        $this->logIn();

        $persister = new Persister($this->entityManager);

        $connection = Fixtures::load($this->fixturesDir . '/idp-connection.yml', $this->entityManager);
        $persister->persist($connection);
    }
}
