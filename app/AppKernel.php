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
        if ($configuredDir && (is_dir($configuredDir) || mkdir($configuredDir, 0777, true))) {
            return $s_dir = $configuredDir;
        }

        $symfonyDefaultDir = parent::getCacheDir();
        $mainCacheDir = dirname($symfonyDefaultDir);
        if ((is_dir($symfonyDefaultDir) && is_writable($symfonyDefaultDir)) ||
            (is_dir($mainCacheDir) && is_writable($mainCacheDir))) {
            return $s_dir = $symfonyDefaultDir;
        }

        throw new \RuntimeException("Unable to get the cache dir!");
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

        if ($configuredDir && (is_dir($configuredDir) || mkdir($configuredDir, 0777, true))) {
            return $s_dir = $configuredDir;
        }

        $symfonyDefaultDir = parent::getLogDir();
        if (is_dir($symfonyDefaultDir) && is_writeable($symfonyDefaultDir)) {
            return $s_dir = $symfonyDefaultDir;
        }

        throw new \RuntimeException("Unable to get the logging dir!");
    }
}
