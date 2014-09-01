<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    const DEFAULT_CACHE_DIR = '/var/cache/janus-ssp/janus';
    const DEFAULT_LOGS_DIR = '/var/log/janus-ssp/janus';

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\JanusServiceRegistrySSPIntegrationBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),

            # Then the Janus Core bundle
            new Janus\ServiceRegistry\Bundle\CoreBundle\JanusServiceRegistryCoreBundle(),

            # Then the REST stuff
            new FOS\RestBundle\FOSRestBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new FSC\HateoasBundle\FSCHateoasBundle(),
            new Janus\ServiceRegistry\Bundle\RestApiBundle\JanusServiceRegistryRestApiBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();

            // Only add the listener debugger if it has been installed by composer.
            $listenerDebuggerClass = 'Egulias\ListenersDebugCommandBundle\EguliasListenersDebugCommandBundle';
            if (class_exists($listenerDebuggerClass)) {
                $bundles[] = new $listenerDebuggerClass();
            }
        }

        return $bundles;
    }

    /**
     * Load config files
     * Both environment config and custom config (or template if config has not yet been created)
     *
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
        $customConfigFile = __DIR__ . '/config/config_janus_core.yml';

        if (is_readable($customConfigFile)) {
            $loader->load($customConfigFile);
        } else {
            $configTemplate = __DIR__ . '/config-dist/config_janus_core.yml';
            $loader->load($configTemplate);
        }
    }

    /**
     * Returns path to cache dir.
     *
     * See README on how to override this
     *
     * @return string
     * @throws RuntimeException
     */
    public function getCacheDir()
    {
        static $s_dir;

        if ($s_dir) {
            return $s_dir;
        }

        if (self::DEFAULT_CACHE_DIR && (is_dir(self::DEFAULT_CACHE_DIR) || @mkdir(self::DEFAULT_CACHE_DIR, 0777, true)) && is_writable(self::DEFAULT_CACHE_DIR)) {
            return $s_dir = self::DEFAULT_CACHE_DIR;
        }

        $symfonyDefaultDir = parent::getCacheDir();
        $mainCacheDir = dirname($symfonyDefaultDir);
        if ((is_dir($symfonyDefaultDir) && is_writable($symfonyDefaultDir)) ||
            (is_dir($mainCacheDir) && is_writable($mainCacheDir))) {
            return $s_dir = $symfonyDefaultDir;
        }

        throw new \RuntimeException(
            "Unable to write cache files!" . PHP_EOL .
            "This is because:" . PHP_EOL .
            "* The prefered directory '" . self::DEFAULT_CACHE_DIR . "' does not exist, can not be created or is not writable for the current user." . PHP_EOL .
            "* And default cache dir '$symfonyDefaultDir' does not exist, is not a softlink, or is not writable for the current user."
        );
    }

    /**
     * Returns path to cache dir.
     *
     * See README on how to override this
     *
     * @return string
     * @throws RuntimeException
     */
    public function getLogDir()
    {
        static $s_dir;

        if ($s_dir) {
            return $s_dir;
        }

        if (self::DEFAULT_LOGS_DIR && (is_dir(self::DEFAULT_LOGS_DIR) || @mkdir(self::DEFAULT_LOGS_DIR, 0777, true)) && is_writeable(self::DEFAULT_LOGS_DIR)) {
            return $s_dir = self::DEFAULT_LOGS_DIR;
        }

        $symfonyDefaultDir = parent::getLogDir();
        if (is_dir($symfonyDefaultDir) && is_writeable($symfonyDefaultDir)) {
            return $s_dir = $symfonyDefaultDir;
        }

        throw new \RuntimeException(
            "Unable to write log file!" . PHP_EOL .
            "This is because:" . PHP_EOL .
            (
            self::DEFAULT_LOGS_DIR ?
                "* Configured directory '" . self::DEFAULT_LOGS_DIR . "' does not exist, can not be created or is not writable for the current user." :
                "* No configured directory ('logs_dir' setting in module_janus.php)."
            )
            . PHP_EOL .
            "* And default logs dir '$symfonyDefaultDir' does not exist or is not writable for the current user."
        );
    }
}

