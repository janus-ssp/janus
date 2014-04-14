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

    public function __construct($options = array())
    {
        $this->vcs = Context::get('vcs');
    }

    public function getTitle()
    {
        return "Gets version from vcs commit hash";
    }

    public function execute()
    {
        $this->confirmSuccess();
    }

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

    public function save($versionNumber)
    {
        // No saving required
    }

    public function init()
    {
        // Not initialization required
    }
}
