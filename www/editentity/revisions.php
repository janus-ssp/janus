<?php

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;

function addRevisionCompare(SimpleSAML_XHTML_Template $et, $eid) {
    if (isset($_GET['compareRevision'])) {
        $compareRevisionId = $_GET['compareRevisiondid'];
        $revisionId = $_GET['revisionid'];

        $connectionService = sspmod_janus_DiContainer::getInstance()->getConnectionService();

        $revision = $connectionService->getRevisionByEidAndRevision($eid, $revisionId);
        $compareRevision = $connectionService->getRevisionByEidAndRevision($eid, $compareRevisionId);

        $serializer = sspmod_janus_DiContainer::getInstance()->getSerializerBuilder();

        $et->data['revision_compare'] = array(
            'compareRevisionId' => $compareRevisionId,
            'revisionId' => $revisionId,
            'data' => array(
                $serializer->serialize($compareRevision, 'json', SerializationContext::create()->setGroups(array('compare'))),
                $serializer->serialize($revision, 'json', SerializationContext::create()->setGroups(array('compare'))),
            )
        );

        $et->data['selectedtab'] = 7;
    }

}


