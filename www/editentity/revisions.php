<?php

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;

function addRevisionCompare(SimpleSAML_XHTML_Template $et, $eid) {

    $connectionService = sspmod_janus_DiContainer::getInstance()->getConnectionService();
    $serializer = sspmod_janus_DiContainer::getInstance()->getSerializerBuilder();

    $latestRevisionNbr = 0;

    $revisions = array();
    $allRevisions = $connectionService->findRevisionsByEid($eid);
    foreach ($allRevisions as $revision) {
        $json = $serializer->serialize($revision, 'json', SerializationContext::create()->setGroups(array('compare')));
        // we need to sanitize the JSON otherwise the compare display breaks
        $json = str_replace(array('\n', '\r', '\t', '\x09'), '', $json);
        $revisions[] = array('revision' => $revision, 'json' => $json);
        $latestRevisionNbr =  ($revision->getRevisionNr() > $latestRevisionNbr ? $revision->getRevisionNr() : $latestRevisionNbr);
    }

    $et->data['revisions'] = $revisions;
    $et->data['latestRevisionNbr'] = $latestRevisionNbr;

}


