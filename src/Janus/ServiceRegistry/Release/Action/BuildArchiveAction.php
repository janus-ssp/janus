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
     * @var
     */
    private $output;

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
        // Note that output is not yet available at construction time
        $this->output = Context::get('output');

        $this->output->writeln("<info>Creating a self contained archive of the project.</info>");

        $versionSuffix = $this->getVersionSuffix();
        $releaseName = "janus-{$versionSuffix}";
        $releaseDir = "{$this->releasesDir}/{$releaseName}";

        $this->createProjectCopy($this->getCurrentBranch(), $releaseDir);
        $this->updateDependencies($releaseDir);
        $this->removeSimpleSamlPhp($releaseDir);
        $this->createArchive($releaseDir);
    }

    /**
     * Creates a fresh copy of the project by doing a git clone.
     *
     * @param string $currentBranch
     * @param string $releaseDir
     * @throws RuntimeException
     */
    private function createProjectCopy($currentBranch, $releaseDir)
    {
        $this->output->writeln("<info>- Create a fresh clone of the project</info>");
        $gitCloneProcess = new Process(
            "rm -rf {$releaseDir} && git clone -b {$currentBranch} {$this->githubUrl} {$releaseDir}",
            $this->releasesDir
        );
        $gitCloneProcess->run();

        if (!$gitCloneProcess->isSuccessful()) {
            throw new \RuntimeException($gitCloneProcess->getErrorOutput());
        }
    }

    /**
     * Updates dependencies using composer.
     *
     * @param string $releaseDir
     * @throws RuntimeException
     */
    private function updateDependencies($releaseDir)
    {
        $this->output->writeln("<info>- Install (non-dev) dependencies using composer</info>");
        $composerInstallProcess = new Process(
            "curl -O http://getcomposer.org/composer.phar && chmod +x ./composer.phar && ./composer.phar install --no-dev",
            $releaseDir
        );
        $composerInstallProcess->run();

        if (!$composerInstallProcess->isSuccessful()) {
            throw new \RuntimeException($composerInstallProcess->getErrorOutput());
        }
    }

    /**
     * Removes the SimpleSamlPhp dependency since the release is meant as a SimpleSamlPhp plugin.
     *
     * @param string $releaseDir
     * @throws RuntimeException
     */
    private function removeSimpleSamlPhp($releaseDir)
    {
        $this->output->writeln("<info>- Removing embedded SimpleSamlPhp</info>");
        $removeSimpleSamlPhpProcess = new Process(
            "rm -rf vendor/simplesamlphp/simplesamlphp && ./composer.phar dump-autoload",
            $releaseDir
        );
        $removeSimpleSamlPhpProcess->run();

        if (!$removeSimpleSamlPhpProcess->isSuccessful()) {
            throw new \RuntimeException($removeSimpleSamlPhpProcess->getErrorOutput());
        }
    }

    /**
     * Creates an archive of the project copy.
     *
     * @param string $releaseDir
     * @throws RuntimeException
     */
    private function createArchive($releaseDir)
    {
        $this->output->writeln("<info>- Create archive</info>");
        $releaseFile = "{$releaseDir}.tar.gz";
        $commandLine = $this->createArchiveCommand($releaseFile);
        $gzipProcess = new Process($commandLine, $releaseDir);
        $gzipProcess->run();

        if (!$gzipProcess->isSuccessful()) {
            throw new \RuntimeException($gzipProcess->getErrorOutput());
        }

        $this->output->writeln("<info>" . $gzipProcess->getOutput() . "</info>");
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
            return $this->getCurrentVersion();
        }

        $versionSuffix .= str_replace('/', '-', $currentBranch);
        $commitHash = $this->getCurrentVersion();
        $versionSuffix .= "-{$commitHash}";

        return $versionSuffix;
    }

    /**
     * Get current git tag
     *
     * @return string
     */
    private function getCurrentVersion()
    {
        /** @var Liip\RMT\Version\Persister\PersisterInterface $versionPersister */
        $versionPersister = Context::get('version-persister');
        return $versionPersister->getCurrentVersion();
    }
}
