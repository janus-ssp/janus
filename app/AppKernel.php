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
        $configuration = SSPConfigFactory::getInstance($this->getEnvironment());
        $configuredDir = $configuration->getString('cache_dir', false);
        if ($configuredDir && is_dir($configuredDir)) {
            return $configuredDir;
        }

        $symfonyDefaultDir = parent::getCacheDir();
        if (is_dir($symfonyDefaultDir)) {
            return $symfonyDefaultDir;
        }

        $systemDefault = '/tmp/janus/cache';
        if (is_dir($systemDefault)) {
            return $systemDefault;
        }

        throw new \RuntimeException("Unable to get the logging dir!");
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
        $configuration = SSPConfigFactory::getInstance($this->getEnvironment());
        $configuredDir = $configuration->getString('log_dir', false);
        if ($configuredDir && is_dir($configuredDir)) {
            return $configuredDir;
        }

        $symfonyDefaultDir = parent::getLogDir();
        if (is_dir($symfonyDefaultDir)) {
            return $symfonyDefaultDir;
        }

        $systemDefault = '/var/log/janus';
        if (is_dir($symfonyDefaultDir)) {
            return $systemDefault;
        }

        throw new \RuntimeException("Unable to get the logging dir!");
    }
}
