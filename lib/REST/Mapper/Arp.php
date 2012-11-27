<?php

class sspmod_janus_REST_Mapper_Arp extends sspmod_janus_REST_Mapper_Abstract
{
    /**
     * @return array
     */
    public function getCollection()
    {
        return $this->_getArpList();
    }

    /**
     * @return array
     */
    public function get($id)
    {
        foreach ($this->_getArpList() as $arp) {
            if ($arp['aid'] == $id) {
                return array($arp);
            }
        }

        throw new sspmod_janus_REST_Exception_NotFound(
            "ARP with id '$id' not found"
        );
    }

    /**
     * @return array
     */
    protected function _getArpList()
    {
        $list = array();
        foreach ($this->_getArpModel()->getARPList() as $arp) {
            if (!empty($arp['attributes'])) {
                $arp['attributes'] = unserialize($arp['attributes']);
            }

            $list[]= $arp;
        }

        return $list;
    }

    /**
     * @return sspmod_janus_ARP
     */
    protected function _getArpModel()
    {
        return new sspmod_janus_ARP();
    }
}
