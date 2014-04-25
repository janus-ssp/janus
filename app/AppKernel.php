<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\DependencyInjection\SSPConfigFactory;

class AppKernel extends Kernel
{
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
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Janus\ServiceRegistry\Bundle\CoreBundle\JanusServiceRegistryCoreBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Egulias\ListenersDebugCommandBundle\EguliasListenersDebugCommandBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
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

        $configuration = SSPConfigFactory::getInstance($this->getEnvironment());
        $configuredDir = $configuration->getString('cache_dir', false);
        if ($configuredDir && (is_dir($configuredDir) || mkdir($configuredDir, 0777, true)) && is_writable($configuredDir)) {
            return $s_dir = $configuredDir;
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
            (
            $configuredDir ?
                "* Configured directory '$configuredDir' does not exist, can not be created or is not writable for the current user." :
                "* No configured directory ('cache_dir' setting in module_janus.php)."
            )
            . PHP_EOL .
            "* And default cache dir '$symfonyDefaultDir' does not exist or is not writable for the current user."
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

        $configuration = SSPConfigFactory::getInstance($this->getEnvironment());
        $configuredDir = $configuration->getString('logs_dir', false);

        if ($configuredDir && (is_dir($configuredDir) || mkdir($configuredDir, 0777, true)) && is_writeable($configuredDir)) {
            return $s_dir = $configuredDir;
        }

        $symfonyDefaultDir = parent::getLogDir();
        if (is_dir($symfonyDefaultDir) && is_writeable($symfonyDefaultDir)) {
            return $s_dir = $symfonyDefaultDir;
        }

        throw new \RuntimeException(
            "Unable to write log file!" . PHP_EOL .
            "This is because:" . PHP_EOL .
            (
            $configuredDir ?
                "* Configured directory '$configuredDir' does not exist, can not be created or is not writable for the current user." :
                "* No configured directory ('logs_dir' setting in module_janus.php)."
            )
            . PHP_EOL .
            "* And default logs dir '$symfonyDefaultDir' does not exist or is not writable for the current user."
        );
    }
}

