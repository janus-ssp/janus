<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\JanusServiceRegistrySSPIntegrationBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
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
     */
    public function getCacheDir()
    {
        $defaultDir = parent::getCacheDir();
        if (is_dir(dirname($defaultDir))) {
            return $defaultDir;
        }

        return '/tmp/janus/cache';
    }

    /**
     * Returns path to cache dir.
     *
     * See README on how to override this
     *
     * @return string
     */
    public function getLogDir()
    {
        $defaultDir = parent::getLogDir();
        if (is_dir($defaultDir)) {
            return $defaultDir;
        }

        return '/var/log/janus';
    }
}
