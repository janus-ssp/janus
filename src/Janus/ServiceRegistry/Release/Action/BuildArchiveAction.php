<?php

use Liip\RMT\Context;
use Liip\RMT\Action\BaseAction;

use \Symfony\Component\Process\Process;

/**
 * Update the changelog file
 */
class BuildArchiveAction extends BaseAction
{
    /**
     * @var string
     */
    private $projectRootDir;

    /**
     * @var string
     */
    private $releasesDir;

    public function __construct()
    {
        $this->projectRootDir = realpath(__DIR__ . '/../../../../../');
        $this->releasesDir = "{$this->projectRootDir}/releases";
        if (!is_dir($this->releasesDir)) {
            mkdir($this->releasesDir);
        }
    }

    public function execute()
    {
        Context::get('output')->writeln("<info>Creating a self contained archive of the project.</info>");

        $versionSuffix = $this->getVersionSuffix();
        $targetFile = "{$this->releasesDir}/janus-{$versionSuffix}.tar.gz";
        $commandLine = $this->createArchiveCommand($targetFile);
        Context::get('output')->writeln("<info>{$commandLine}</info>");
        $gzipProcess = new Process($commandLine, $this->projectRootDir);
        $gzipProcess->run();

        if (!$gzipProcess->isSuccessful()) {
            throw new \RuntimeException($gzipProcess->getErrorOutput());
        }

        Context::get('output')->writeln("<info>" . $gzipProcess->getOutput() . "</info>");
    }

    /**
     * Creates a command to make a tar gzip archive of all relevant project directories.
     *
     * @param string $targetFile
     * @return string
     */
    private function createArchiveCommand($targetFile)
    {
        return <<<COMMAND
tar -czf \
{$targetFile} \
app \
bin \
config-templates \
dictionaries \
docs \
hooks \
lib \
src \
templates \
vendor \
web \
www \
CHANGES \
default-enable \
LICENSE \
README.md \
UPGRADE
COMMAND;
    }

    /**
     * @return string
     */
    private function getVersionSuffix()
    {
        /** @var \Liip\RMT\VCS\VCSInterface $vcs */
        $vcs = Context::get('vcs');
        $currentBranch = $vcs->getCurrentBranch();

        $versionSuffix = '';
        if ($currentBranch === 'master') {
            /** @var Liip\RMT\Version\Persister\PersisterInterface $versionPersister */
            $versionPersister = Context::get('version-persister');
            $versionSuffix .= $versionPersister->getCurrentVersion();

            return $versionSuffix;
        }
        $versionSuffix .= str_replace('/', '-', $currentBranch);

        // Add commit hash
        $colorOutput = false;
        $modifications = $vcs->getAllModificationsSince('1.17.0', $colorOutput);
        $lastModification = reset($modifications);
        $commitHash = substr($lastModification, 0, strpos($lastModification, ' '));
        $versionSuffix .= "-{$commitHash}";

        return $versionSuffix;
    }
}

