<?php
ini_set('memory_limit','200M');

$configureGroupConcatQuery = 'SET SESSION group_concat_max_len = 1000000;';

$queryOld = file_get_contents(__DIR__ . '/' . 'selectDataOld.sql');
//$pdoOld = new PDO('mysql:host=localhost;dbname=serviceregistry', 'root', 'c0n3xt');
$pdoOld = new PDO('mysql:host=localhost;dbname=janus_prod', 'root', 'c0n3xt');
$pdoOld->query($configureGroupConcatQuery);
$oldConnections = parseResult(query($pdoOld, $queryOld));
$oldDumpFile = tempnam(sys_get_temp_dir(), 'old');
file_put_contents($oldDumpFile, var_export($oldConnections, true));
unset($oldConnections);

$queryNew = file_get_contents(__DIR__ . '/' . 'selectDataNew.sql');
$pdoNew = new PDO('mysql:host=localhost;dbname=janus_migrations_test', 'root', 'c0n3xt');
$pdoNew->query($configureGroupConcatQuery);
$newConnections = parseResult(query($pdoNew, $queryNew));
$newDumpFile = tempnam(sys_get_temp_dir(), 'new');
file_put_contents($newDumpFile, var_export($newConnections, true));
unset($newConnections);

exec("colordiff {$oldDumpFile} {$newDumpFile}", $output);
echo implode("\n", $output);

/**
 * @param PDO $pdo
 * @param string $query
 * @return array mixed
 */
function query(PDO $pdo, $query)
{
    $st = $pdo->prepare($query);
    $st->execute();
    $errorInfo = $st->errorInfo();
    if ($errorInfo[2]) {
        die ('MySQL Error: ' . $errorInfo[2]);
    }

    return $st->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @param array $result
 * @return array
 */
function parseResult(array $result)
{
    $parsedConnections = array();
    foreach ($result as $connection) {
        $connection['arpAttributes'] = unserialize($connection['arpAttributes']);
        if (empty($connection['arpAttributes'])) {
            $connection['arpAttributes'] = null;
        }

        $connection['allowedConnections'] = parseGroupedValue($connection['allowedConnections']);
        $connection['blockedConnections'] = parseGroupedValue($connection['blockedConnections']);
        $connection['disableConsentConnections'] = parseGroupedValue($connection['disableConsentConnections']);
        $connection['users'] = parseGroupedValue($connection['users']);

        $connection['metadata'] = parseMetadata($connection['metadata']);

        $parsedConnections[$connection['entityid']] = $connection;
    }

    return $parsedConnections;
}

/**
 * @param string|null $value
 * @return array|null
 */
function parseGroupedValue($value = null)
{
    if (isset($value)) {
        return explode("\n", $value);
    }

    return;
}


/**
 * @param string $metadata
 * @return array
 */
function parseMetadata($metadata)
{
    $parsedMetadata = array();
    $metadataValues = explode("\n", $metadata);
    foreach($metadataValues as $metadataKeyValue) {
        $splittedValues = explode('|SPLIT|', $metadataKeyValue);
        list($metadataKey, $metadataValue) = $splittedValues;
        $parsedMetadata[$metadataKey] = $metadataValue;
    }

    return $parsedMetadata;
}