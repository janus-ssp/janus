<?php

use Liip\RMT\Context;

/**
 * Custom pre-release action for getting version number based on a git commit hash
 */
class JanusVersionPersister extends \Liip\RMT\Action\BaseAction
    implements \Liip\RMT\Version\Persister\PersisterInterface
{
    /**
     * @var \Liip\RMT\VCS\VCSInterface
     */
    private $vcs;

    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->vcs = Context::get('vcs');
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return "Gets version from vcs commit hash";
    }

    public function execute()
    {
        $this->confirmSuccess();
    }

    /**
     * @return string
     */
    public function getCurrentVersion()
    {
        return 'commit-#' . $this->getCurrentCommitHash();
    }

    /**
     * Gets hash of latest commit
     *
     * @return string
     */
    private function getCurrentCommitHash()
    {
        $colorOutput = false;
        $modifications = $this->vcs->getAllModificationsSince('HEAD~1', $colorOutput);
        $lastModification = reset($modifications);
        return substr($lastModification, 0, strpos($lastModification, ' '));
    }

    /**
     * @param string $versionNumber
     */
    public function save($versionNumber)
    {
        // No saving required
    }

    public function init()
    {
        // Not initialization required
    }
}
