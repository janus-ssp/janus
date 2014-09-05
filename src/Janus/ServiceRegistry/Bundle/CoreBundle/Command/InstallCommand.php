<?php

namespace Janus\ServiceRegistry\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Helper\DialogHelper;

use Janus\ServiceRegistry\Entity\User;
use Symfony\Component\Filesystem\Filesystem;

class InstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('janus:install')
            ->setDescription('Install Janus Application');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Installing Janus");

        $this->createConfig($output);
        $this->runMigrations($input, $output);
        $this->createAdminUser($output);
    }

    /**
     * Creates example config.
     *
     * @param OutputInterface $output
     */
    private function createConfig(OutputInterface $output)
    {
        $appDir = $this->getContainer()->get('kernel')->getRootDir();
        $rootDir = dirname($appDir);

        $customConfigFile = $appDir . '/config/config_janus_core.yml';

        if (file_exists($customConfigFile)) {
            return;
        }

        $question = "Do you want to create an example config at '{$customConfigFile}'? [y/N]";
        $dialog = $this->getHelper('dialog');
        if (!$dialog->askConfirmation($output, $question, false)) {
            return;
        }

        $customConfigFileTemplate = $appDir . '/config-dist/config_janus_core.yml';
        $filesystem = new Filesystem();
        $filesystem->copy($customConfigFileTemplate, $customConfigFile);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function runMigrations(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:migrations:migrate');
        $command->run($input, $output);
    }

    /**
     * @param OutputInterface $output
     */
    private function createAdminUser(OutputInterface $output)
    {
        $dialog = $this->getHelper('dialog');
        $name = $this->getContainer()->getParameter('admin_name');
        $email = $this->getContainer()->getParameter('admin_email');

        $question = "Do you want to create an admin user with name: '{$name}' and email: '{$email}'? [Y/n]";
        if (!$dialog->askConfirmation($output, $question, true)) {
            return;
        }

        $adminUser = new User(
            $name,
            array('admin'),
            $email
        );
        $adminUser->setData('Navn: ' . $email);

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($adminUser);
        $entityManager->flush();
    }
}
