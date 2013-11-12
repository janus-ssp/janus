<?php

function addRevisionCompare(SimpleSAML_XHTML_Template $et, $eid) {
    if (isset($_GET['compareRevision'])) {
        $currentRevisionId = $_GET['currentRevisiondid'];
        $revisionId = $_GET['revisionid'];

        $connectionService = sspmod_janus_DiContainer::getInstance()->getConnectionService();

        $revision = $connectionService->getRevisionByEidAndRevision($eid, $revisionId);
        $currRevision = $connectionService->getRevisionByEidAndRevision($eid, $currentRevisionId);
        $et->data['revision_compare'] = array($revision, $currRevision);

        $et->data['selectedtab'] = 7;
    }

}


