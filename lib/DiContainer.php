<?php
require_once dirname(__DIR__) . "/app/autoload.php";
require_once dirname(__DIR__) .'/app/AppKernel.php';


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\DBAL\Migrations\Migration;
use Doctrine\DBAL\Migrations\Configuration\YamlConfiguration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Connection;
use JMS\Serializer\SerializerBuilder;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

use Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\DependencyInjection\AuthenticationProvider;
use Janus\ServiceRegistry\Entity\User;

class sspmod_janus_DiContainer extends Pimple
{
    const SYMFONY_CONTAINER = 'symfony_container';
    const CONFIG = 'config';
    const USER_CONTROLLER = 'userController';
    const ENTITY_CONTROLLER = 'entityController';
    const SESSION = 'session';
    const LOGGED_IN_USERNAME = 'logged-in-user';
    const METADATA_CONVERTER = 'metadata-converter';
    const MEMCACHE_CONNECTION = 'memcacheConnection';
    const ENTITY_MANAGER = 'entityManager';
    const SERIALIZER_BUILDER = "serializerBuilder";

    /** @var sspmod_janus_DiContainer */
    private static $instance;

    public function __construct()
    {
        $this->registerSymfonyContainer();
        $this->registerUserController();
        $this->registerEntityController();
        $this->registerLoggedInUsername();
        $this->registerMetadataConverter();
        $this->registerMemcacheConnection();
    }

    /**
     * @return sspmod_janus_DiContainer
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof sspmod_janus_DiContainer) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function registerSymfonyContainer()
    {
        $this[self::SYMFONY_CONTAINER] = $this->share(function () {

            /**
             * @todo add support for setting environment dynamically
             * Since this container does not use much environment dependent
             * variables it doesn't really matter for now.
             */
            $kernel = new AppKernel('prod', true);
            $kernel->loadClassCache();
            $kernel->boot();
            Request::createFromGlobals();
            return $kernel->getContainer();
        });
    }

    /**
     * @return ContainerInterface
     */
    public function getSymfonyContainer()
    {
        return $this[self::SYMFONY_CONTAINER];
    }

    /**
     * @return SimpleSAML_Configuration
     */
    public function getConfig()
    {
        return $this->getSymfonyContainer()->get('janus_config');
    }

    /**
     * @return sspmod_janus_UserController
     */
    public function getUserController()
    {
        return $this[self::USER_CONTROLLER];
    }

    /**
     * Note that this method does not return a shared object, although it seems ridiculous this is to mimic the original behaviour
     */
    protected function registerUserController()
    {
        $this[self::USER_CONTROLLER] = function (sspmod_janus_DiContainer $container) {
            return new sspmod_janus_UserController($container->getConfig());
        };
    }

    /**
     * @return sspmod_janus_EntityController
     */
    public function getEntityController()
    {
        return $this[self::ENTITY_CONTROLLER];
    }

    /**
     * Note that this method does not return a shared object, although it seems ridiculous this is to mimic the original behaviour
     */
    protected function registerEntityController()
    {
        $this[self::ENTITY_CONTROLLER] = function (sspmod_janus_DiContainer $container) {
            return new sspmod_janus_EntityController($container->getConfig());
        };
    }

    /**
     * @return SimpleSAML_Session
     */
    public function getSession()
    {
        return $this->getSymfonyContainer()->get('ssp_session');
    }

    /**
     * @return User
     */
    public function getLoggedInUsername()
    {
        return $this[self::LOGGED_IN_USERNAME];
    }

    protected function registerLoggedInUsername()
    {
        $this[self::LOGGED_IN_USERNAME] = $this->share(function (sspmod_janus_DiContainer $container) {
            $authenticationProvider = new AuthenticationProvider($container->getSession(), $container->getConfig());
            return $authenticationProvider->getLoggedInUsername();
        });
    }

    /**
     * @return sspmod_janus_Metadata_Converter_Converter
     */
    public function getMetaDataConverter()
    {
        return $this[self::METADATA_CONVERTER];
    }

    protected function registerMetadataConverter()
    {
        $this[self::METADATA_CONVERTER] = $this->share(
            function (sspmod_janus_DiContainer $container) {
                $janusConfig = $container->getConfig();
                $metadataConverter = new sspmod_janus_Metadata_Converter_Converter();

                $metadataConverter->registerCommand(new sspmod_janus_Metadata_Converter_Command_FlattenValuesCommand());

                $metadataConverter->registerCommand(new sspmod_janus_Metadata_Converter_Command_FlattenKeysCommand());

                $metadataConverter->registerCommand(new sspmod_janus_Metadata_Converter_Command_ScopeConverterCommand());

                $mapping = $janusConfig->getArray('md.mapping', array());
                $mapKeysCommand = new sspmod_janus_Metadata_Converter_Command_MapKeysCommand();
                $mapKeysCommand->setMapping($mapping);
                $metadataConverter->registerCommand($mapKeysCommand);

                return $metadataConverter;
            }
        );
    }

    /**
     * @todo fix that doctrine can use this
     * @todo move to ssp integration bundle
     */
    private function registerMemcacheConnection()
    {
        $this[self::MEMCACHE_CONNECTION] = $this->share(
            function (sspmod_janus_DiContainer $container) {
                if (!extension_loaded('memcache')) {
                    throw new \Exception('Memcache cannot be used as since it is not installed or loaded');
                }

                $config = SimpleSAML_Configuration::getInstance();
                $memcacheServerGroupsConfig = $config->getArray('memcache_store.servers');

                if (empty($memcacheServerGroupsConfig)) {
                    throw new \Exception('Memcache cannot be used  since no servers are configured');
                }

                $memcache = new Memcache();
                foreach ($memcacheServerGroupsConfig as $serverGroup) {
                    foreach ($serverGroup as $server) {
                        // Converts SimpleSample memcache config to params Memcache::addServer requires
                        $createParams = function ($server) {
                            // Set hostname
                            $params = array($server['hostname']);

                            // Set port
                            if (!isset($server['port'])) {
                                return $params;
                            }
                            $params[] = $server['port'];

                            // Set weight  and non configurable persistence
                            if (!isset($server['weight'])) {
                                return $params;
                            }
                            $params[] = null; // Persistent
                            $params[] = $server['weight'];

                            // Set Timeout and non configurable interval/status/failure callback
                            if (!isset($server['timeout'])) {
                                return $params;
                            }
                            $params[] = null; // Retry interval
                            $params[] = null; // Status
                            $params[] = null; // Failure callback
                            $params[] = $server['timeout'];

                            return $params;
                        };

                        call_user_func_array(array($memcache, 'addserver'), $createParams($server));
                    }
                }

                return $memcache;
            }
        );
    }

    /** @return EntityManager */
    public function getEntityManager()
    {
        return $this->createEntityManager();
    }

    /**
     * @return EntityManager
     * @todo fix installer
     */
    public function createEntityManager()
    {
        return $this->getSymfonyContainer()->get('doctrine')->getManager();
    }

    /**
     * Creates a migration instance
     *
     * @param OutputWriter $outputWriter
     * @param Connection $dbConnection
     * @return Migration
     */
    public function createMigration(OutputWriter $outputWriter, Connection $dbConnection)
    {
        $configuration = new YamlConfiguration($dbConnection, $outputWriter);
        $configuration->load(JANUS_ROOT_DIR . '/migrations.yml');
        $migration = new Migration($configuration);

        return $migration;
    }

    /**
     * @return \Janus\ServiceRegistry\Service\ConnectionService
     */
    public function getConnectionService()
    {
        return $this->getSymfonyContainer()->get('connection_service');
    }

    /**
     * @return \Janus\ServiceRegistry\Service\UserService
     */
    public function getUserService()
    {
        return $this->getSymfonyContainer()->get('user_service');
    }

    /**
     * @return \JMS\Serializer\SerializerBuilder
     */
    public function getSerializerBuilder()
    {
        return $this->getSymfonyContainer()->get('jms_serializer');
    }
}