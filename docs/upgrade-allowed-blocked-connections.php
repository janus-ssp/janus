<?php

echo "Welcome to the conversion of Whitelist / Blacklist to 1.12!" . PHP_EOL;

// Require CLI access
if (PHP_SAPI !== 'cli'){
    // Otherwise exit with an error code and message
    echo 'Command line usage only!' . PHP_EOL;
    exit(1);
}

// Get the JANUS configuration
$config = array();
require dirname(__FILE__) . '/../../../config/module_janus.php';

// Make sure there is a 'store' configuration
if (
    !isset($config['store']['dsn']) ||
    !isset($config['store']['username']) ||
    !isset($config['store']['password']) ||
    !isset($config['store']['prefix'])
) {
    // Otherwise exit with an error code and message
    echo 'Missing database configuration! (requires: store.dsn, store.username, store.password, store.prefix)' . PHP_EOL;
    exit(1);
}

echo "Connecting to the database..." . PHP_EOL;

// Establish a database connection
$db = new PDO($config['store']['dsn'], $config['store']['username'], $config['store']['password']);

$entityTable        = $config['store']['prefix'] . 'entity';

// First: allowed entities

echo "Converting whitelists" . PHP_EOL;

$blockedEntityTable = $config['store']['prefix'] . 'allowedEntity';

$statement = $db->query("
SELECT be.*, entities.eid AS newremoteeid
FROM (
    SELECT * 
    FROM $blockedEntityTable fbe
    WHERE revisionid=(
        SELECT MAX(revisionid) 
        FROM $blockedEntityTable
        WHERE fbe.eid = eid
)) be
JOIN (
    SELECT * 
    FROM $entityTable e
    WHERE revisionid = (
        SELECT MAX(revisionid) 
        FROM $entityTable
        WHERE eid = e.eid
    )) entities ON be.remoteentityid = entities.entityid
");

while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    echo "ALLOW ({$row['eid']}): {$row['remoteentityid']} => {$row['eid']} ";

    $query = "
        UPDATE $blockedEntityTable
        SET remoteeid = ?
        WHERE eid = ?
          AND revisionid = ?
          AND remoteentityid = ?";
    $params = array(
        $row['newremoteeid'], 
        $row['eid'], 
        $row['revisionid'], 
        $row['remoteentityid']
    );

    $update = $db->prepare($query);
    $update->execute($params);

    if ($update) {
        if ($update->rowCount() > 0) {
            echo "[SUCCESS]";        
        }
        else {
            echo "[NO CHANGE REQUIRED]";
        }
    }
    else {
        echo "[FAILURE]";
    }
    echo PHP_EOL;
}

// Next blocked entities

echo "Converting blacklists" . PHP_EOL;

$blockedEntityTable = $config['store']['prefix'] . 'blockedEntity';

$statement = $db->query("
SELECT be.*, entities.eid AS newremoteeid
FROM (
    SELECT * 
    FROM $blockedEntityTable fbe
    WHERE revisionid=(
        SELECT MAX(revisionid) 
        FROM $blockedEntityTable
        WHERE fbe.eid = eid
)) be
JOIN (
    SELECT * 
    FROM $entityTable e
    WHERE revisionid = (
        SELECT MAX(revisionid) 
        FROM $entityTable
        WHERE eid = e.eid
    )) entities ON be.remoteentityid = entities.entityid
");

while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    echo "BLOCK ({$row['eid']}): {$row['remoteentityid']} => {$row['eid']} ";

    $query = "
        UPDATE $blockedEntityTable
        SET remoteeid = ?
        WHERE eid = ?
          AND revisionid = ?
          AND remoteentityid = ?";
    $params = array(
        $row['newremoteeid'], 
        $row['eid'], 
        $row['revisionid'], 
        $row['remoteentityid']
    );

    $update = $db->prepare($query);
    $update->execute($params);

    if ($update && $update->rowCount() > 0) {
        if ($update->rowCount() > 0) {
            echo "[SUCCESS]";        
        }
        else {
            echo "[NO CHANGE REQUIRED]";
        }
    }
    else {
        echo "[FAILURE]";
    }
    echo PHP_EOL;
}

echo "All done, enjoy!" . PHP_EOL;
