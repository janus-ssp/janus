<?php

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;

function addRevisionCompare(SimpleSAML_XHTML_Template $et, $eid) {
    if (isset($_GET['compareRevision'])) {
        $compareRevisionId = $_GET['compareRevisiondid'];
        $revisionId = $_GET['revisionid'];

        $connectionService = sspmod_janus_DiContainer::getInstance()->getConnectionService();
        $serializer = sspmod_janus_DiContainer::getInstance()->getSerializerBuilder();

        $revisions = array();
        for ($i = $revisionId; $i >= $compareRevisionId; $i--) {
            $revision = $connectionService->getRevisionByEidAndRevision($eid, $i);
            $json = $serializer->serialize($revision, 'json', SerializationContext::create()->setGroups(array('compare')));
            $revisions[$i] = $json;
        }

        $et->data['revision_compare'] = array(
            'compareRevisionId' => $compareRevisionId,
            'revisionId' => $revisionId,
            'data' => $revisions
        );
    }

}


