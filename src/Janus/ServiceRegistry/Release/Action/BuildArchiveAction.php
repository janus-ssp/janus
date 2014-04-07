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

    /**
     * @var \Liip\RMT\VCS\VCSInterface
     */
    private $vcs;

    /**
     * @var string
     */
    private $githubUrl = 'https://github.com/janus-ssp/janus.git';

    public function __construct()
    {
        $this->projectRootDir = realpath(__DIR__ . '/../../../../../');
        $this->releasesDir = "{$this->projectRootDir}/releases";
        if (!is_dir($this->releasesDir)) {
            mkdir($this->releasesDir);
        }

        $this->vcs = Context::get('vcs');
    }

    public function execute()
    {
        Context::get('output')->writeln("<info>Creating a self contained archive of the project.</info>");

        $versionSuffix = $this->getVersionSuffix();
        $releaseName = "janus-{$versionSuffix}";
        $releaseFile = "{$this->releasesDir}/{$releaseName}.tar.gz";
        $releaseDir = "{$this->releasesDir}/{$releaseName}";

        $curentBranch = $this->getCurrentBranch();

        Context::get('output')->writeln("<info>- Create a fresh clone of the project</info>");
        $gitCloneProcess = new Process(
            "rm -rf {$releaseDir} && git clone -b {$curentBranch} {$this->githubUrl} {$releaseDir}",
            $this->releasesDir
        );
        $gitCloneProcess->run();

        if (!$gitCloneProcess->isSuccessful()) {
            throw new \RuntimeException($gitCloneProcess->getErrorOutput());
        }

        // Run composer without dev
        Context::get('output')->writeln("<info>- Install (non-dev) dependencies using composer</info>");
        $composerInstallProcess = new Process(
            "curl -O http://getcomposer.org/composer.phar && chmod +x ./composer.phar && ./composer.phar install --no-dev",
            $releaseDir
        );
        $composerInstallProcess->run();

        if (!$composerInstallProcess->isSuccessful()) {
            throw new \RuntimeException($composerInstallProcess->getErrorOutput());
        }

        // Zip the copy
        Context::get('output')->writeln("<info>- Create archive</info>");
        $commandLine = $this->createArchiveCommand($releaseFile);
        $gzipProcess = new Process($commandLine, $releaseDir);
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
    private function    getCurrentBranch()
    {
        return $this->vcs->getCurrentBranch();
    }

    /**
     * @return string
     */
    private function getVersionSuffix()
    {
        $versionSuffix = '';
        $currentBranch = $this->getCurrentBranch();
        if ($currentBranch === 'master') {
            /** @var Liip\RMT\Version\Persister\PersisterInterface $versionPersister */
            $versionPersister = Context::get('version-persister');
            $versionSuffix .= $versionPersister->getCurrentVersion();

            return $versionSuffix;
        }
        $versionSuffix .= str_replace('/', '-', $currentBranch);

        // Add commit hash
        $colorOutput = false;
        $modifications = $this->vcs->getAllModificationsSince('1.17.0', $colorOutput);
        $lastModification = reset($modifications);
        $commitHash = substr($lastModification, 0, strpos($lastModification, ' '));
        $versionSuffix .= "-{$commitHash}";

        return $versionSuffix;
    }
}

