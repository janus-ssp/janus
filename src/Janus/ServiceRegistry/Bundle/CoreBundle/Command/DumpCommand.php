<?php
namespace Janus\ServiceRegistry\Bundle\CoreBundle\Command;

use Janus\ServiceRegistry\Service\ConnectionService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Helper\DialogHelper;

use Symfony\Component\Filesystem\Filesystem;

class DumpCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('janus:dump')
            ->setDescription('Dumps all connections');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Dumping connections");
    }
}
