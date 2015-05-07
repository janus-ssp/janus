<?php

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;

function addRevisionCompare(SimpleSAML_XHTML_Template $et, $eid) {
    $connectionService = sspmod_janus_DiContainer::getInstance()->getConnectionService();
    $serializer = sspmod_janus_DiContainer::getInstance()->getSerializerBuilder();

    $latestRevisionNr = 0;

    $revisions = $connectionService->findRevisionsByEid($eid, history_limit(), history_offset());

    $revisionsData = array();
    foreach ($revisions as $revision) {

        $json = $serializer->serialize($revision, 'json', SerializationContext::create()->setGroups(array('compare')));
        // we need to sanitize the JSON otherwise the compare display breaks
        $json = str_replace(array('\n', '\r', '\t', '\x09'), '', $json);
        $revisionsData[] = array('revision' => $revision, 'json' => $json);
        $latestRevisionNr =  ($revision->getRevisionNr() > $latestRevisionNr ? $revision->getRevisionNr() : $latestRevisionNr);
    }

    if (history_offset() > 0) {
        $et->data['history_prev_offset'] = history_prev();
    }
    if (count($revisions) === history_limit()) {
        $et->data['history_next_offset'] = history_next();
    }
    $et->data['revisions'] = $revisionsData;
    $et->data['latestRevisionNbr'] = $latestRevisionNr;
}

function history_limit()
{
    return isset($_GET['history_limit']) && $_GET['history_limit'] > 0 ? (int) $_GET['history_limit'] : 25;
}

function history_offset()
{
    return isset($_GET['history_offset']) && $_GET['history_offset'] > 0 ? (int) $_GET['history_offset'] : 0;
}

function history_prev()
{
    $prev = history_offset() - history_limit();
    return $prev > 0 ? $prev : 0;
}

function history_next()
{
    return history_offset() + history_limit();
}
