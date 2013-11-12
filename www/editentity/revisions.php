<?php

function addRevisionCompare(SimpleSAML_XHTML_Template $et, $eid, $revisionid) {
    $connectionService = sspmod_janus_DiContainer::getInstance()->getConnectionService();

    $revision = $connectionService->getRevisionByEidAndRevision($eid, $revisionid);
    $et->data['revision_compare'] = $revision;
}


