<?php

namespace Janus\ServiceRegistry\Security\Authorization\Voter;

use CG\Proxy\MethodInvocation;
use Janus\ServiceRegistry\SimpleSamlPhp\ConfigProxy;
use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;

class SspVoter implements VoterInterface
{
    const CONFIG_ACCESS             = 'access';
    const CONFIG_WORKFLOW_STATES    = 'workflow_states';
    const CONFIG_DEFAULT_PERMISSION = 'default';
    const CONFIG_WORKFLOW_STATE_ALL = 'all';

    const RIGHT_ACCESS          = 'access';
    const RIGHT_ALL_ENTITIES    = 'allentities';

    const REVISION_CLASS        = '\Janus\ServiceRegistry\Entity\Connection\Revision';
    const LEGACY_ENTITY_CLASS   = 'sspmod_janus_Entity';

    /**
     * @var ConfigProxy
     */
    private $configuration;

    /**
     * @var array
     */
    private $access;

    /**
     * @var \sspmod_janus_EntityController[]
     */
    private $entityControllers = array();

    /**
     * @param ConfigProxy $configuration
     */
    public function __construct(ConfigProxy $configuration)
    {
        $this->configuration = $configuration;
        $this->access = $configuration->getArray(self::CONFIG_ACCESS);
    }

    /**
     * Checks if the voter supports the given attribute.
     *
     * @param string $attribute An attribute
     *
     * @return Boolean true if this Voter supports the attribute, false otherwise
     */
    public function supportsAttribute($attribute)
    {
        return array_key_exists($attribute, $this->access);
    }

    /**
     * Checks if the voter supports the given class.
     *
     * @param string $class A class name
     *
     * @return Boolean true if this Voter can process the class
     */
    public function supportsClass($class)
    {
        return $class === self::LEGACY_ENTITY_CLASS || $class === self::REVISION_CLASS;
    }

    /**
     * Returns the vote for the given parameters.
     *
     * This method must return one of the following constants:
     * ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
     *
     * @param TokenInterface $token A TokenInterface instance
     * @param \sspmod_janus_Entity $object The object to secure
     * @param array $attributes An array of attributes associated with the method being invoked
     *
     * @return integer either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $entity = $this->getEntityForObject($object);

        $entityWorkflowState = null;
        if ($entity) {
            $entityWorkflowState = $entity->getWorkflow();
        }

        /** @var User $user */
        $user = $token->getUser();

        foreach ($attributes as $attribute) {
            if (!$this->voteAttribute($user, $attribute, $entity, $entityWorkflowState)) {
                return self::ACCESS_DENIED;
            }
        }

        return self::ACCESS_GRANTED;
    }

    /**
     * @param \stdClass $object
     * @return \sspmod_janus_Entity
     * @throws \RuntimeException
     */
    protected function getEntityForObject($object)
    {
        if (!$object) {
            return null;
        }

        // JMSAopBundle gives us a MethodInvocation proxy when we secure a controller action.
        // Cute, but we don't need it.
        if ($object instanceof MethodInvocation) {
            return null;
        }

        if ($object instanceof \sspmod_janus_Entity) {
            return $object;
        }

        if ($object instanceof Revision) {
            $entityId = $object->getConnection()->getId();
            $entityController = $this->getEntityControllerForEntityId($entityId);
            return $entityController->getEntity();
        }

        throw new \RuntimeException('Unknown object to vote on?');
    }

    /**
     * @param User                  $user
     * @param string                $right
     * @param \sspmod_janus_Entity  $entity
     * @param string                $entityWorkflowState
     * @return bool
     */
    protected function voteAttribute(User $user, $right, \sspmod_janus_Entity $entity = null, $entityWorkflowState = null)
    {
        // 'normalize' to all lowercase without whitespace
        $right = strtolower(str_replace(' ', '', $right));

        if ($right === static::RIGHT_ACCESS) {
            $allowedUsers = $this->getEntityControllerForEntity($entity)->getUsers();

            if (array_key_exists($user->getUsername(), $allowedUsers)) {
                return true;
            }

            return $this->voteAttribute($user, static::RIGHT_ALL_ENTITIES);
        }

        if ($entity && isset($this->access[$right][static::CONFIG_WORKFLOW_STATES][$entityWorkflowState])) {
            $allowedRoles = $this->access[$right][static::CONFIG_WORKFLOW_STATES][$entityWorkflowState];
        } elseif (isset($this->access[$right][static::CONFIG_WORKFLOW_STATES][static::CONFIG_WORKFLOW_STATE_ALL])) {
            $allowedRoles = $this->access[$right][static::CONFIG_WORKFLOW_STATES][static::CONFIG_WORKFLOW_STATE_ALL];
        } else if (isset($this->access[$right][static::CONFIG_DEFAULT_PERMISSION])) {
            // Return default permission for element
            return (bool) $this->access[$right][static::CONFIG_DEFAULT_PERMISSION];
        } else {
            return false;
        }

        $roles = $user->getRoles();

        // Role is explicitly allowed
        $intersect = array_intersect($roles, $allowedRoles);
        if (!empty($intersect)) {
            return true;
        }

        $rolesNegated = array();
        foreach($roles AS $role) {
            $rolesNegated[] = '-' . $role;
        }
        $rolesNegated[] = '-all';

        // Role is explicitly disallowed
        $intersectNegated = array_intersect($rolesNegated, $allowedRoles);
        if (!empty($intersectNegated)) {
            return false;
        }

        // All roles are allowed (and current role is not explicitly disallowed).
        if (in_array('all', $allowedRoles)) {
            return true;
        }

        // Default to no access.
        return false;
    }

    /**
     * @param \sspmod_janus_Entity $entity
     * @return \sspmod_janus_EntityController
     */
    protected function getEntityControllerForEntity(\sspmod_janus_Entity $entity)
    {
        if (!isset($this->entityControllers[$entity->getId()])) {
            $controller = new \sspmod_janus_EntityController($this->configuration);
            $controller->setEntity($entity);
            $this->entityControllers[$entity->getId()] = $controller;
        }

        return $this->entityControllers[$entity->getId()];
    }

    /**
     * @param $entityId
     * @return \sspmod_janus_EntityController
     */
    protected function getEntityControllerForEntityId($entityId)
    {
        if (!isset($this->entityControllers[$entityId])) {
            $controller = new \sspmod_janus_EntityController($this->configuration);
            $controller->setEntity($entityId);
            $this->entityControllers[$entityId] = $controller;
        }

        return $this->entityControllers[$entityId];
    }
}
