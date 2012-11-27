<?php

class sspmod_janus_REST_Mapper_User extends sspmod_janus_REST_Mapper_Abstract
{
    /**
     * @return array
     */
    public function getCollection()
    {
        $result = array();

        foreach (self::getUserController()->getUsers() as $user) {
            $result[]= $this->_formatUser($user);
        }

        return $result;
    }

    /**
     * @param string $id
     * @return array
     */
    public function get($id)
    {
        $user = new sspmod_janus_User(
            self::getConfig()->getValue('store')
        );

        $user->setUserid($id);
        $result = $user->load(sspmod_janus_User::USERID_LOAD);

        if ($result === false) {
            throw new sspmod_janus_REST_Exception_NotFound(
                sprintf('User with ID \'%s\' not found', $id)
            );
        }

        return $this->_formatUser($user);
    }

    /**
     * @param sspmod_janus_User $user
     * @return array
     */
    protected function _formatUser(sspmod_janus_User $user)
    {
        $result = array();
        $result['uid']      = $user->getUid();
        $result['userid']   = $user->getUserid();
        $result['active']   = $user->getActive();
        $result['type']     = $user->getType();
        $result['data']     = $user->getdata();

        return $result;
    }
}
