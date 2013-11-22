<?php
ini_set('memory_limit','200M');

$oldDumpFile = '/tmp/oldConnections.php';
$newDumpFile = '/tmp/newConnections.php';

//$pdoOld = new PDO('mysql:host=localhost;dbname=serviceregistry', 'root', 'c0n3xt');
$pdoOld = new PDO('mysql:host=localhost;dbname=janus_prod', 'root', 'c0n3xt');
$oldDumpFile = selectData('old', $pdoOld);

$pdoNew = new PDO('mysql:host=localhost;dbname=janus_migrations_test', 'root', 'c0n3xt');
$newDumpFile = selectData('new', $pdoNew);



echo "Creating diff" . PHP_EOL;
exec("colordiff {$oldDumpFile} {$newDumpFile}", $output);
echo implode("\n", $output);


function selectData($type, PDO $pdo)
{
    echo "Quering connections from {$type} db" . PHP_EOL;
    $connections = parseConnections(
        query($pdo, file_get_contents(__DIR__ . '/' . $type . '/' . 'selectConnections.sql'))
    );
    echo "Quering metadata from {$type} db" . PHP_EOL;
    addMetadata(
        $connections,
        query($pdo, file_get_contents(__DIR__ . '/' . $type . '/' . 'selectMetadata.sql'))
    );
    echo "Quering arps from {$type} db" . PHP_EOL;
    addArps(
        $connections,
        query($pdo, file_get_contents(__DIR__ . '/' . $type . '/' . 'selectArps.sql'))
    );
    echo "Quering allowed connections from {$type} db" . PHP_EOL;
    addAllowedConnections(
        $connections,
        query($pdo, file_get_contents(__DIR__ . '/' . $type . '/' . 'selectAllowedConnections.sql'))
    );
    echo "Quering disable consent from {$type} db" . PHP_EOL;
    addDisableConsents(
        $connections,
        query($pdo, file_get_contents(__DIR__ . '/' . $type . '/' . 'selectDisableConsent.sql'))
    );
    echo "Quering users from {$type} db" . PHP_EOL;
    addUsers(
        $connections,
        query($pdo, file_get_contents(__DIR__ . '/' . $type . '/' . 'selectUsers.sql'))
    );
    
    echo "dumping {$type} data" . PHP_EOL;
    $dumpFile = "/tmp/{$type}Connections.php";
    file_put_contents($dumpFile, var_export($connections, true));

    return $dumpFile;
}

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
function parseConnections(array $result)
{
    $parsedConnections = array();
    foreach ($result as $connection) {
//        echo "Parsing '{$connection['entityid']}'" . PHP_EOL;
        $parsedConnections[getKey($connection)] = $connection;
    }

    return $parsedConnections;
}

function getKey(array $row) {
    return $row['eid'] . '-' . $row['revisionid'];
}

function addMetadata(array &$connections, $metadataResult)
{
    foreach ($metadataResult as $metadata) {
        if (!isset($connections[getKey($metadata)]['metadata'])) {
            $connections[getKey($metadata)]['metadata'] = array();
        }
        $connections[getKey($metadata)]['metadata'][] = array(
            'key' => $metadata['key'],
            'value' => $metadata['value']
        );
    }
}

function addArps(array &$connections, $arpResult)
{
    foreach ($arpResult as $arp) {
        $arpAttributes = unserialize($arp['arpAttributes']);
        if (empty($arpAttributes)) {
            $arpAttributes = null;
        }

        $connections[getKey($arp)]['arpAttributes'] = $arpAttributes;
    }
}

function addAllowedconnections(array &$connections, $allowedConnectionResult)
{
    foreach ($allowedConnectionResult as $allowedConnection) {

        if (!isset($connections[getKey($allowedConnection)]['allowedConnections'])) {
            $connections[getKey($allowedConnection)]['allowedConnections'] = array();
        }
        $connections[getKey($allowedConnection)]['allowedConnections'][] = $allowedConnection['allowedEntityid'];
    }
}

function addDisableconsents(array &$connections, $disableConsentResult)
{
    foreach ($disableConsentResult as $disableConsent) {

        if (!isset($connections[getKey($disableConsent)]['disableConsent'])) {
            $connections[getKey($disableConsent)]['disableConsent'] = array();
        }
        $connections[getKey($disableConsent)]['disableConsent'][] = $disableConsent['disableConsentEntityid'];
    }
}

function addUsers(array &$connections, $userResult)
{
    foreach ($userResult as $user) {

        if (!isset($connections[getKey($user)]['users'])) {
            $connections[getKey($user)]['users'] = array();
        }
        $connections[getKey($user)]['users'][] = $user['username'];
    }
}
