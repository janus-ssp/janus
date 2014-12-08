<?php
require_once dirname(__DIR__) . "/app/autoload.php";
require_once dirname(__DIR__) .'/app/AppKernel.php';

use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Provider\PreAuthenticatedAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\SecurityContext;

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use Janus\ServiceRegistry\Entity\User;
use Janus\ServiceRegistry\Security\Authentication\Token\SspToken;
use Janus\ServiceRegistry\Security\Authentication\Provider\SspProvider;
use Janus\ServiceRegistry\Service\UserService;

class sspmod_janus_DiContainer extends Pimple
{
    const SYMFONY_CONTAINER     = 'symfony_container';
    const SYMFONY_KERNEL        = 'symfony_kernel';
    const SECURITY_CONTEXT      = 'security_context';
    const CONFIG                = 'config';
    const USER_CONTROLLER       = 'userController';
    const ENTITY_CONTROLLER     = 'entityController';
    const SESSION               = 'session';
    const LOGGED_IN_USERNAME    = 'logged-in-user';
    const METADATA_CONVERTER    = 'metadata-converter';
    const ENTITY_MANAGER        = 'entityManager';
    const SERIALIZER_BUILDER    = "serializerBuilder";

    /** @var sspmod_janus_DiContainer */
    protected static $instance;

    /** @var array */
    protected static $preAuth = array();

    /**
     * @var AppKernel
     */
    protected static $kernel;

    public function __construct()
    {
        $this->registerSymfonyKernel();
        $this->registerSymfonyContainer();
        $this->registerSecurityContext();
        $this->registerLoggedInUsername();
        $this->registerUserController();
        $this->registerEntityController();
        $this->registerMetadataConverter();
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

    public static function registerAppKernel(AppKernel $kernel)
    {
        self::$kernel = $kernel;
    }

    public static function preAuthenticate($user, $provider)
    {
        static::$preAuth = array('user' => $user, 'provider' => $provider);
    }

    public function registerSymfonyKernel()
    {
        $kernel = self::$kernel;
        $this[self::SYMFONY_KERNEL] = $this->share(function () use ($kernel) {
            if ($kernel) {
                return $kernel;
            }

            /**
             * @todo add support for setting environment dynamically
             * Since this container does not use much environment dependent
             * variables it doesn't really matter for now.
             */
            $environment = getenv('SYMFONY_ENV');
            if (!$environment) {
                $environment = 'prod';
            }
            $kernel = new AppKernel($environment, true);
            $kernel->loadClassCache();
            $kernel->boot();
            Request::createFromGlobals();
            return $kernel;
        });
    }

    /**
     * @return AppKernel
     */
    public function getSymfonyKernel()
    {
        return $this[self::SYMFONY_KERNEL];
    }

    public function registerSymfonyContainer()
    {
        $this[self::SYMFONY_CONTAINER] = $this->share(function (sspmod_janus_DiContainer $container) {
            return $container->getSymfonyKernel()->getContainer();
        });
    }

    /**
     * @return ContainerInterface
     */
    public function getSymfonyContainer()
    {
        return $this[self::SYMFONY_CONTAINER];
    }

    public function registerSecurityContext()
    {
        $this[self::SECURITY_CONTEXT] = $this->share(function (sspmod_janus_DiContainer $container) {

            $token = $container->authenticate();

            // Inject the authenticated token back into the Symfony SecurityContext
            /** @var SecurityContext $securityContext */
            $securityContext = $container->getSymfonyContainer()->get('security.context');
            $securityContext->setToken($token);

            // And register the username or the logged in user in our own container.
            // So any SF component (like the Doctrine AuditPropertiesUpdater) that gets the Token from
            // the SecurityContext can do so and not care if authentication was done via SSP or via Symfony.
            // And any legacy Janus component can directly get the logged in username with the shortcut.

            return $securityContext;
        });
    }

    /**
     * Authenticate with SimpleSAMLphp.
     *
     * @return null|\Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    public function authenticate()
    {
        $config = sspmod_janus_DiContainer::getInstance()->getConfig();

        // The User Provider, to look up users and their secrets.
        $userProvider = new UserService($this->getEntityManager(), $config);

        // In case of the REST API v1 or the Installer we are pre authenticated.
        if (self::$preAuth) {
            $token = new PreAuthenticatedToken(static::$preAuth['user'], '', static::$preAuth['provider']);
            $provider = new PreAuthenticatedAuthenticationProvider(
                $userProvider,
                new \Symfony\Component\Security\Core\User\UserChecker(),
                static::$preAuth['provider']
            );
        // Otherwise use SSP as our Authentication Provider.
        } else {
            $token = new SspToken();
            $provider = new SspProvider($userProvider, $config);
        }

        // And a custom authentication manager with a single provider.
        $authenticationManager = new AuthenticationProviderManager(array($provider));

        // And we use that provider to authenticate, which calls triggers SSP to authenticate and
        // puts it's information in our custom token.
        return $authenticationManager->authenticate($token);
    }

    /**
     * @return SecurityContext
     */
    public function getSecurityContext()
    {
        return $this[self::SECURITY_CONTEXT];
    }

    /**
     * @return ConfigProxy
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
            return new sspmod_janus_UserController($container->getConfig(), $container->getSecurityContext(), $container->getConnectionService());
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
        return SimpleSAML_Session::getInstance();
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
            return $container->getSecurityContext()->getToken()->getUsername();
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

    /**
     * @return object \Doctrine\Common\Cache\Cache
     */
    public function getCacheProvider()
    {
        return $this->getSymfonyContainer()->get('doctrine_cache.providers.memcache_cache');
    }

    public function getRootDir()
    {
        return realpath(__DIR__ . '/../');
    }
}
