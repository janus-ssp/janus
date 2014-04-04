<?php
namespace Janus\ServiceRegistry\Bundle\CoreBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class BuildReleaseCommand extends Command
{

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }

    /**
     * Configures command.
     */
    protected function configure()
    {
        $this
            ->setName('janus:build-release')
            ->setDescription('Builds a release')
            ->setHelp('Builds a release')
            ->addArgument(
                'version',
                InputArgument::REQUIRED,
                'The version to create a release for'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getArgument('version');
        $output->writeln("<info>Creating a release for {$version} <info>");
    }
}