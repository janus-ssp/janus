<?php
require_once dirname(__DIR__) . "/autoload.php";

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Events;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Migrations\Migration;
use Doctrine\DBAL\Migrations\Configuration\YamlConfiguration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Connection;
use JMS\Serializer\SerializerBuilder;

class sspmod_janus_DiContainer extends Pimple
{
    const CONFIG = 'config';
    const DB_PARAMS = 'dbParams';
    const SESSION = 'session';
    const LOGGED_IN_USER = 'logged-in-user';
    const METADATA_CONVERTER = 'metadata-converter';
    const MEMCACHE_CONNECTION = 'memcacheConnection';
    const DOCTRINE_CACHE_DRIVER = 'doctrineCacheDriver';
    const ENTITY_MANAGER = 'entityManager';
    const ANNOTATION_DRIVER = 'annotationDriver';
    const CONNECTION_SERVICE = 'connectionService';
    const USER_SERVICE = 'userService';
    const SERIALIZER_BUILDER = "serializerBuilder";

    // Available cache driver types
    const DOCTRINE_CACHE_DRIVER_TYPE_ARRAY = 'array';
    const DOCTRINE_CACHE_DRIVER_TYPE_FILE = 'file';
    const DOCTRINE_CACHE_DRIVER_TYPE_APC = 'apc';
    const DOCTRINE_CACHE_DRIVER_TYPE_MEMCACHE = 'memcache';

    /** @var sspmod_janus_DiContainer */
    private static $instance;

    public function __construct()
    {
        $this->registerConfig();
        $this->registerDbParams();
        $this->registerSession();
        $this->registerLoggedInUser();
        $this->registerMetadataConverter();
        $this->registerMemcacheConnection();
        $this->registerDoctrineCacheDriver();
        $this->registerEntityManager();
        $this->registerAnnotationReader();
        $this->registerConnectionService();
        $this->registerUserService();
        $this->registerSerializerBuilder();
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

    /**
     * @return SimpleSAML_Configuration
     */
    public function getConfig()
    {
        return $this[self::CONFIG];
    }

    protected function registerConfig()
    {
        $this[self::CONFIG] = $this->share(function (sspmod_janus_DiContainer $container)
        {
            $config = SimpleSAML_Configuration::getConfig('module_janus.php');
            return $config;
        });
    }

    /**
     * @return SimpleSAML_Configuration
     */
    public function getDbParams()
    {
        return $this[self::DB_PARAMS];
    }

    /**
     * @return array
     */
    protected function registerDbParams()
    {
        $this[self::DB_PARAMS] = $this->share(function (sspmod_janus_DiContainer $container)
        {
            $dbParams = $container->getConfig()->getArray('store');
            return $container->parseDbParams($dbParams);
        });
    }

    /**
     ** Parses db configuration into an array usable by doctrine
     * This is mainly meant to parse legacy config.
     *
     * @param array $dbParams
     * @return array
     * @todo move to class?
     */
    public function parseDbParams(array $dbParams)
    {
        // Doctrine uses user instead of username
        if (isset($dbParams['username'])) {
            $dbParams['user'] = $dbParams['username'];
            unset($dbParams['username']);
        }

        // Doctrine does not use dsn
        if (isset($dbParams['dsn'])) {

            $dsnParts = preg_split('/[:;]/', $dbParams['dsn']);
            unset($dbParams['dsn']);

            // Set driver (always use pdo)
            $dbParams['driver'] = 'pdo_' . array_shift($dsnParts);

            // Set host, dbname etc.
            foreach ($dsnParts as $value) {
                if (empty($value)) {
                    continue;
                }

                $entryParts = explode('=', $value);
                if (count($entryParts) === 1) {
                    $dbParams[$entryParts[0]] = true;
                } else {
                    $dbParams[$entryParts[0]] = $entryParts[1];
                }
            }
        }

        return $dbParams;
    }

    /**
     * @return SimpleSAML_Session
     */
    public function getSession()
    {
        return $this[self::SESSION];
    }

    protected function registerSession()
    {
        $this[self::SESSION] = $this->share(function (sspmod_janus_DiContainer $container)
        {
            return SimpleSAML_Session::getInstance();
        });
    }

    /**
     * @return sspmod_janus_Model_User
     */
    public function getLoggedInUser()
    {
        return $this[self::LOGGED_IN_USER];
    }

    protected function registerLoggedInUser()
    {
        $this[self::LOGGED_IN_USER] = $this->share(
            function (sspmod_janus_DiContainer $container)
            {
                $session = $container->getSession();
                $config = $container->getConfig();

                $authsource = $config->getValue('auth', 'login-admin');
                $useridattr = $config->getValue('useridattr', 'eduPersonPrincipalName');

                // @todo improve this by creating a test DI
                if (php_sapi_name() == 'cli') {
                    $username = $authsource;
                } else {
                    if (!$session->isValid($authsource)) {
                        throw new Exception("Authsoruce is invalid");
                    }
                    $attributes = $session->getAttributes();
                    // Check if userid exists
                    if (!isset($attributes[$useridattr])) {
                        throw new Exception('User ID is missing');
                    }
                    $username = $attributes[$useridattr][0];
                }

                // Get the user
                $user = $container->getEntityManager()->getRepository('sspmod_janus_Model_User')->findOneBy(array(
                    'username' => $username
                ));

                if (!$user instanceof sspmod_janus_Model_User) {
                    throw new Exception("No User logged in");
                }

                return $user;
            }
        );
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
            function (sspmod_janus_DiContainer $container)
            {
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
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    public function getDoctrineCacheDriver()
    {
        return $this[self::DOCTRINE_CACHE_DRIVER];
    }

    protected function registerDoctrineCacheDriver()
    {
        $this[self::DOCTRINE_CACHE_DRIVER] = $this->share(function (sspmod_janus_DiContainer $container)
        {
            $cacheDriverType = $container->getConfig()->getString(
                'doctrine.cache_driver_type',
                $container::DOCTRINE_CACHE_DRIVER_TYPE_ARRAY
            );

            switch ($cacheDriverType) {
                case $container::DOCTRINE_CACHE_DRIVER_TYPE_ARRAY:
                    return new \Doctrine\Common\Cache\ArrayCache();
                case $container::DOCTRINE_CACHE_DRIVER_TYPE_APC:
                    if (!extension_loaded('apc')) {
                        throw new \Exception('Apc cannot be used as Doctrine Cachedriver since it is not installed or loaded');
                    }
                    if (!ini_get('apc.enabled')) {
                        throw new \Exception('Apc cannot be used as Doctrine Cachedriver since it is not enabled');
                    }
                    return new \Doctrine\Common\Cache\ApcCache();
                 case $container::DOCTRINE_CACHE_DRIVER_TYPE_MEMCACHE:
                    $memcache = $container[$container::MEMCACHE_CONNECTION];
                     $cacheDriver = new \Doctrine\Common\Cache\MemcacheCache();
                     $cacheDriver->setMemcache($memcache);
                     return $cacheDriver;
                case $container::DOCTRINE_CACHE_DRIVER_TYPE_FILE:
                    return new \Doctrine\Common\Cache\FilesystemCache(sys_get_temp_dir());
            }
        });
    }

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
                foreach($memcacheServerGroupsConfig as $serverGroup) {
                    foreach($serverGroup as $server) {
                        // Converts SimpleSample memcache config to params Memcache::addServer requires
                        $createParams = function ($server)
                        {
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
                            $params[] =  $server['timeout'];

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
        return $this[self::ENTITY_MANAGER];
    }

    protected function registerEntityManager()
    {
        $this[self::ENTITY_MANAGER] = $this->share(function (sspmod_janus_DiContainer $container)
        {
            $dbParams = $container->getDbParams();
            return $container->createEntityManager($dbParams);
        });
    }

    /**
     * @param array $dbParams
     * @return EntityManager
     */
    public function createEntityManager(array $dbParams)
    {
        $doctrineConfig = new \Doctrine\ORM\Configuration();

        $cacheDriver = $this->getDoctrineCacheDriver();
        $doctrineConfig->setMetadataCacheImpl($cacheDriver);
        $doctrineConfig->setQueryCacheImpl($cacheDriver);
        $doctrineConfig->setResultCacheImpl($cacheDriver);

        // Configure Proxy class generation
        $doctrineConfig->setAutoGenerateProxyClasses($this->getConfig()->getBoolean('doctrine.proxy_auto_generate', true));

        // Set proxy dir
        $proxyDir = $this->getConfig()->getString('doctrine.proxy_dir', 'doctrine/proxy');
        if (empty($proxyDir)) {
            throw new \Exception("Proxy dir must be configured and not empty");
        }
        $isProxyDirAbsolute = ($proxyDir[0] === '/');
        if (!$isProxyDirAbsolute) {
            $proxyDir = JANUS_ROOT_DIR . '/' . $proxyDir;
        }
        $doctrineConfig->setProxyDir($proxyDir);

        $doctrineConfig->setProxyNamespace($this->getConfig()->getString('doctrine.proxy_namespace', 'Proxy'));

        // Configure annotation reader
        $annotationReader = $this->getAnnotationReader();
        $paths = array(JANUS_ROOT_DIR  . "/lib/Model");
        $driverImpl =  new AnnotationDriver($annotationReader, $paths);
        $doctrineConfig->setMetadataDriverImpl($driverImpl);

        // Configure table name refix
        $tablePrefix = new sspmod_janus_Doctrine_Extensions_TablePrefixListener($dbParams['prefix']);
        $eventManager = new \Doctrine\Common\EventManager;
        $eventManager->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $tablePrefix);

        $entityManager = EntityManager::create($dbParams, $doctrineConfig, $eventManager);

        $entityManager->getEventManager()->addEventListener(
            array(Events::onFlush),
            new sspmod_janus_Doctrine_Listener_AuditPropertiesUpdater($this)
        );

        // Setup custom mapping type
        Type::addType(sspmod_janus_Doctrine_Type_JanusBooleanType::NAME, 'sspmod_janus_Doctrine_Type_JanusBooleanType');
        Type::addType(sspmod_janus_Doctrine_Type_JanusIpType::NAME, 'sspmod_janus_Doctrine_Type_JanusIpType');
        Type::addType(sspmod_janus_Doctrine_Type_JanusDateTimeType::NAME, 'sspmod_janus_Doctrine_Type_JanusDateTimeType');
        Type::addType(sspmod_janus_Doctrine_Type_JanusUserTypeType::NAME, 'sspmod_janus_Doctrine_Type_JanusUserTypeType');
        $entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('janusBoolean', 'janusBoolean');
        // Current schema may contain enums which Doctrine cannot natively handle
        $entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        return $entityManager;
    }

    /**
     * @return \Doctrine\Common\Annotations\AnnotationReader
     */
    public function getAnnotationReader()
    {
        return $this[self::ANNOTATION_DRIVER];
    }

    /**
     * Creates annotation reader
     *
     * @return Doctrine\Common\Annotations\CachedReader
     */
    protected function registerAnnotationReader()
    {
        $this[self::ANNOTATION_DRIVER] = $this->share(
            function (sspmod_janus_DiContainer $container)
            {
                $annotationReader = new AnnotationReader();

                AnnotationRegistry::registerFile(VENDOR_DIR . '/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');
                AnnotationRegistry::registerAutoloadNamespace(
                    'JMS\Serializer\Annotation',
                    VENDOR_DIR . "/jms/serializer/src"
                );
                $cacheDriver = $container->getDoctrineCacheDriver();
                $cacheDriver->setNamespace('doctrine-annotation-cache');

                $annotationReader = new \Doctrine\Common\Annotations\CachedReader(
                    $annotationReader,
                    $cacheDriver,
                    false
                );

                return $annotationReader;
            }
        );
    }

    /**
     * Creates a migration instance
     *
     * @param OutputWriter $outputWriter
     * @param array $dbParams
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
     * @return \sspmod_janus_ConnectionService
     */
    public function getConnectionService()
    {
        return $this[self::CONNECTION_SERVICE];
    }

    /**
     * Creates Service Layer for connections
     */
    protected function registerConnectionService()
    {
        $this[self::CONNECTION_SERVICE] = $this->share(
            function (sspmod_janus_DiContainer $container)
            {
                $janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');
                return new sspmod_janus_ConnectionService($container->getEntityManager(), $janus_config);
            }
        );
    }

    /**
     * @return \sspmod_janus_UserService
     */
    public function getUserService()
    {
        return $this[self::USER_SERVICE];
    }

    /**
     * Creates Service Layer for users 
     */
    protected function registerUserService()
    {
        $this[self::USER_SERVICE] = $this->share(
            function (sspmod_janus_DiContainer $container)
            {
                $janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');
                return new sspmod_janus_UserService($container->getEntityManager(), $janus_config);
            }
        );
    }

    /**
     * @return \JMS\Serializer\SerializerBuilder
     */
    public function getSerializerBuilder()
    {
        return $this[self::SERIALIZER_BUILDER];
    }

    /**
     * Creates Service Layer for users
     */
    protected function registerSerializerBuilder()
    {
        $this[self::SERIALIZER_BUILDER] = $this->share(
            function (sspmod_janus_DiContainer $container)
            {

                $serializer = SerializerBuilder::create()
                        ->setCacheDir(sys_get_temp_dir())
                        ->setDebug(false)
                        ->build();
                return $serializer;
            }
        );
        $this->getAnnotationReader();
    }

}