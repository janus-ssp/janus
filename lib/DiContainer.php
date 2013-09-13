<?php
require_once dirname(__DIR__) . "/autoload.php";

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

class sspmod_janus_DiContainer extends Pimple
{
    const CONFIG = 'config';
    const DB_PARAMS = 'dbParams';
    const METADATA_CONVERTER = 'metadata-converter';
    const DOCTRINE_CACHE_DRIVER = 'doctrineCacheDriver';
    const ENTITY_MANAGER = 'entityManager';
    const ANNOTATION_DRIVER = 'annotationDriver';

    /** @var sspmod_janus_DiContainer */
    private static $instance;

    public function __construct()
    {
        $this->registerConfig();
        $this->registerDbParams();
        $this->registerMetadataConverter();
        $this->registerDoctrineCacheDriver();
        $this->registerEntityManager();
        $this->registerAnnotationReader();
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
     * Parses db configuration into an array usable by doctrine
     * This is mainly meant to parse legacy config.
     *
     * @return array
     */
    protected function registerDbParams()
    {
        $this[self::DB_PARAMS] = $this->share(function (sspmod_janus_DiContainer $container)
        {
            $dbParams = $container->getConfig()->getArray('store');

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
            function (sspmod_janus_DiContainer $container)
            {
                $janusConfig = $container->getConfig();
                $metadataConverter = new sspmod_janus_Metadata_Converter_Converter();

                $metadataConverter->registerCommand(new sspmod_janus_Metadata_Converter_Command_FlattenValuesCommand());

                $metadataConverter->registerCommand(new sspmod_janus_Metadata_Converter_Command_FlattenKeysCommand());

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
        $this[self::DOCTRINE_CACHE_DRIVER] = function (sspmod_janus_DiContainer $container)
        {
            // @todo base this on config
            $isDevMode = false;

            // @todo get caching type from config instead of using $isDevMode
            // Configure caching
            if ($isDevMode) {
                $cacheDriver = new \Doctrine\Common\Cache\ArrayCache();
            } elseif(extension_loaded('apc') && ini_get('apc.enabled')) {
                $cacheDriver = new \Doctrine\Common\Cache\ApcCache();
            } elseif (class_exists('Memcache')) {
                $memcache = new Memcache();
                $memcache->connect('localhost', 11211);
                $cacheDriver = new \Doctrine\Common\Cache\MemcacheCache();
                $cacheDriver->setMemcache($memcache);
            } else {
                $cacheDriver = new \Doctrine\Common\Cache\FilesystemCache(sys_get_temp_dir());
            }

            return $cacheDriver;
        };
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
            $config = $container->getConfig();

            // @todo base this on config
            $isDevMode = true;

            $doctrineConfig = new \Doctrine\ORM\Configuration();

            $cacheDriver = $container->getDoctrineCacheDriver();
            $doctrineConfig->setMetadataCacheImpl($cacheDriver);
            $doctrineConfig->setQueryCacheImpl($cacheDriver);
            $doctrineConfig->setResultCacheImpl($cacheDriver);

            // Configure Proxy class generation
            $doctrineConfig->setAutoGenerateProxyClasses((bool) !$isDevMode);
            // @todo get from config
            $doctrineConfig->setProxyDir('tmp');
            $doctrineConfig->setProxyNamespace('Proxies');

            // Configure annotation reader
            $annotationReader = $container->getAnnotationReader();
            $paths = array(JANUS_ROOT_DIR  . "/lib/Model");
            $driverImpl =  new AnnotationDriver($annotationReader, $paths);
            $doctrineConfig->setMetadataDriverImpl($driverImpl);

            $dbParams = $container->getDbParams();

            $entityManager = EntityManager::create($dbParams, $doctrineConfig, $eventManager);

            return $entityManager;
        });
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

                // @todo enable caching
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
}