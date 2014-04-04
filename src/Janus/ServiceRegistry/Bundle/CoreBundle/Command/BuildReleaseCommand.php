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

        #!/bin/sh
        $releaseDir = "${HOME}/Releases";
        $githubUser = "OpenConext";
        $projectName = "OpenConext-serviceregistry";

        $tag = $version;

        $projectDirName = $(echo "{$projectName}-{$tag}" | sed - e "s/\//-/g")
        $projectDir = {
        $releaseDir}/ {
        $projectDirName}
        $janusDir = "{$projectDir}/vendor/janus-ssp/janus/"
        
        // Create empty dir
        mkdir($releaseDir, 0777, true);
        rmdir($projectDir);
        
        // get Composer
        cd($releaseDir);
        exec("curl - O http://getcomposer.org/composer.phar");
        
        // clone the tag
        cd($releaseDir);
        exec("git clone -b {$tag} https://github.com/{$githubUser}/{$projectName}.git {$projectDirName}", );
        
        // run Composer
        cd("{$projectDir}
        php {$releaseDir}/ composer . phar install--no - dev
        
        cd("{$janusDir}
        
        // remove files that are not required for production
        rmdir("{$projectDir}/.idea");
        rmdir("{$projectDir}/.git");
        unlink("{$projectDir}/.gitignore");
        unlink("{$projectDir}/ composer . json");
        unlink("{$projectDir}/ composer . lock");
        unlink("{$projectDir}/ makeRelease . sh");
        unlink("{$projectDir}/ bin / composer . phar");
        rmdir("{$projectDir}/ tests");
        rmdir("{$projectDir}/ janus - dictionaries");
        rmdir("{$projectDir}/ simplesamlphp_patches");
        rmdir("{$janusDir}/ www / install");
        
        // create tarball
        $releaseTarballName = {
        $projectDirName} . tar . gz
        $releaseTarballFile = {
        $releaseDir}/ {
        $releaseTarballName}
        cd("{$releaseDir}");
        tar - czf {
        $releaseTarballFile} {
        $projectDirName}
    }

    /**
     * Executes console command.
     *
     * @param string $command
     * @return mixed
     * @throws \RuntimeException
     */
    private function exec($command)
    {
        exec($command, $output, $returnVal);

        if ($returnVal != 0) {
            throw new \RuntimeException("Command failed with code {$returnVal}");
        }

        return $output;
    }
}