<?php

namespace Janus\ServiceRegistry\DoctrineMigrations\Base;

use Doctrine\DBAL\Migrations\AbstractMigration;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class JanusMigration
    extends AbstractMigration
    implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $tablePrefix;

    /**
     * Sets
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->setTablePrefix($container);
    }

    /**
     * @param ContainerInterface $container
     */
    private function setTablePrefix(ContainerInterface $container)
    {
        $this->tablePrefix = $container->getParameter('database_prefix');
    }

    /**
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }
}