<?php

namespace Janus\ServiceRegistry\Security\Authorization\Voter;

use Janus\ServiceRegistry\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;

class SspVoter implements VoterInterface
{
    const ENTITY_CLASS = 'sspmod_janus_Entity';

    /**
     * @var \SimpleSAML_Configuration
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

    public function __construct(\SimpleSAML_Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->access = $configuration->getArray('access');
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
        return $class === self::ENTITY_CLASS;
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

        if (!$object instanceof \sspmod_janus_Entity) {
            throw new \RuntimeException('Unknown object to vote on?');
        }

        return $object;
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
        if ($right === "access") {
            $allowedUsers = $this->getEntityControllerForEntity($entity)->getUsers();

            if (array_key_exists($user->getUsername(), $allowedUsers)) {
                return true;
            }

            return $this->voteAttribute($user, 'allentities');
        }

        if (!$entity) {
            if (!isset($this->access[$right]['role'])) {
                return false;
            }
            $permissions = $this->access[$right]['role'];
        } else if (isset($this->access[$right][$entityWorkflowState])) {
            if(!isset($this->access[$right][$entityWorkflowState]['role'])) {
                return false;
            }
            $permissions = $this->access[$right][$entityWorkflowState]['role'];
        } else if (isset($this->access[$right]['default'])) {
            // Return default permission for element
            return (bool) $this->access[$right]['default'];
        } else {
            return false;
        }

        /** @var Role[] $roles */
        $roles = $user->getRoles();

        $roles_neg = array();
        foreach($roles AS $role) {
            $roles_neg[] = '-' . ($role instanceof Role ? $role->getRole() : $role);
        }
        $roles_neg[] = '-all';

        $intersect = array_intersect($roles, $permissions);
        $intersect_neg = array_intersect($roles_neg, $permissions);

        if (!empty($intersect)) {
            // User type is allowed
            return true;
        } else if (!empty($intersect_neg)) {
            // User type is disallowed
            return false;
        } else if (in_array('all', $permissions)) {
            // All user types are allowed
            return true;
        } else {
            // Usertype do not have permission
            return false;
        }
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
}
