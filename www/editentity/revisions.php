<?php

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;

function addRevisionCompare(SimpleSAML_XHTML_Template $et, $eid) {

    $connectionService = sspmod_janus_DiContainer::getInstance()->getConnectionService();
    $serializer = sspmod_janus_DiContainer::getInstance()->getSerializerBuilder();

    $latestRevisionNbr = $connectionService->getLatestRevision($eid);

    $revisions = array();
    for ($i = (int) $latestRevisionNbr; $i >= 0; $i--) {
        $revision = $connectionService->getRevisionByEidAndRevision($eid, $i);
        $json = $serializer->serialize($revision, 'json', SerializationContext::create()->setGroups(array('compare')));
        // we need to sanitize the JSON otherwise the compare display breaks
        $json = str_replace(array('\n', '\r', '\t', '\x09'), '', $json);
        $revisions[] = array('revision' => $revision, 'json' => $json);
    }

    $et->data['revisions'] = $revisions;
    $et->data['latestRevisionNbr'] = $latestRevisionNbr;

}


