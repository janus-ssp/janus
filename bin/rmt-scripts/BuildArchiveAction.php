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
     * Default timeout for processes, especially composer can be quite slow.
     *
     * @var integer
     */
    const DEFAULT_PROCESS_TIMEOUT = 3000;

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
     * @var Liip\RMT\Output
     */
    private $output;

    public function __construct()
    {
        $this->projectRootDir = realpath(__DIR__ . '/../../');
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
        // Clone the current repo to prevent unwanted files or changes to end up in the archive
        // Clone it from the local repo since this contains the CHANGES file which is updated by RMT
        $gitCloneProcess = new Process(
            "rm -rf {$releaseDir}'
            . ' && git pull'
            . ' && git clone -l -b {$currentBranch} {$this->projectRootDir} {$releaseDir}",
            $this->releasesDir,
            null,
            null,
            self::DEFAULT_PROCESS_TIMEOUT
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
            # Copy over dist parameters to avoid interactive questioning.
            'cp "./app/config-dist/parameters.yml" "app/config/"'
            . ' && (curl -O -sS "https://getcomposer.org/installer" | php)'
            . ' && chmod +x "./composer.phar"'
            . ' && SYMFONY_ENV=build "./composer.phar" install -o --no-dev',
            $releaseDir,
            null,
            null,
            self::DEFAULT_PROCESS_TIMEOUT
        );
        $composerInstallProcess->run();

        if (!$composerInstallProcess->isSuccessful()) {
            throw new \RuntimeException("Error installing composer, process: " . print_r($composerInstallProcess, true));
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
        $gzipProcess = new Process(
            $commandLine,
            $releaseDir,
            null,
            null,
            self::DEFAULT_PROCESS_TIMEOUT);
        $gzipProcess->run();

        if (!$gzipProcess->isSuccessful()) {
            throw new \RuntimeException($gzipProcess->getErrorOutput());
        }

        $this->output->writeln("<info>" . $gzipProcess->getOutput() . "</info>");
        $this->output->writeln("<info>Location: '{$releaseFile}'</info>");
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
README.md
COMMAND;
    }

    /**
     * @return string
     */
    private function getCurrentBranch()
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
