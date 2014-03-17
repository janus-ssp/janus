<?php
/**
 * Note: this test script requires the following:
 * - A new version janus available at: https://serviceregistry.demo.openconext.org
 * - An old version of janus available at:https://serviceregistry-janus-1.16.demo.openconext.org
 * - Both with a prod version of the db
 *
 * Call with: export PHP_IDE_CONFIG="serverName=serviceregistry.demo.openconext.org" || export XDEBUG_CONFIG="idekey=PhpStorm, remote_connect_back=0, remote_host=192.168.56.1" &&  clear && php tests/compareApi.php
 */

require __DIR__ . "/../app/autoload.php";

ini_set('memory_limit', '200M');

class compareDatabasesTest extends \PHPUnit_Framework_TestCase
{
    public function testBothDatabasesContainTheSameData()
    {
        $oldDumpFile = '/tmp/oldConnections.php';
        $newDumpFile = '/tmp/newConnections.php';

        $pdoOld = new PDO('mysql:host=localhost;dbname=janus_prod', 'root', 'c0n3xt');
        $oldDumpFile = $this->selectData('old', $pdoOld);

        $pdoNew = new PDO('mysql:host=localhost;dbname=janus_migrations_test', 'root', 'c0n3xt');
        $newDumpFile = $this->selectData('new', $pdoNew);

        echo "Creating diff" . PHP_EOL;
        exec("colordiff {$oldDumpFile} {$newDumpFile}", $output);
        echo implode("\n", $output);
    }

    private function selectData($type, PDO $pdo)
    {
        echo "Quering connections from {$type} db" . PHP_EOL;
        $connections = $this->parseConnections(
            $this->query($pdo, file_get_contents(__DIR__ . '/compareDatabaseTestResources/' . $type . '/' . 'selectConnections.sql'))
        );
        echo "Quering metadata from {$type} db" . PHP_EOL;
        $this->addMetadata(
            $connections,
            $this->query($pdo, file_get_contents(__DIR__ . '/compareDatabaseTestResources/' . $type . '/' . 'selectMetadata.sql'))
        );
        echo "Quering arps from {$type} db" . PHP_EOL;
        $this->addArps(
            $connections,
            $this->query($pdo, file_get_contents(__DIR__ . '/compareDatabaseTestResources/' . $type . '/' . 'selectArps.sql'))
        );
        echo "Quering allowed connections from {$type} db" . PHP_EOL;
        $this->addAllowedConnections(
            $connections,
            $this->query($pdo, file_get_contents(__DIR__ . '/compareDatabaseTestResources/' . $type . '/' . 'selectAllowedConnections.sql'))
        );
        echo "Quering disable consent from {$type} db" . PHP_EOL;
        $this->addDisableConsents(
            $connections,
            $this->query($pdo, file_get_contents(__DIR__ . '/compareDatabaseTestResources/' . $type . '/' . 'selectDisableConsent.sql'))
        );
        echo "Quering users from {$type} db" . PHP_EOL;
        $this->addUsers(
            $connections,
            $this->query($pdo, file_get_contents(__DIR__ . '/compareDatabaseTestResources/' . $type . '/' . 'selectUsers.sql'))
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
    private function query(PDO $pdo, $query)
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
    private function parseConnections(array $result)
    {
        $parsedConnections = array();
        foreach ($result as $connection) {
            $parsedConnections[$this->getKey($connection)] = $connection;
        }

        return $parsedConnections;
    }

    private function getKey(array $row)
    {
        return $row['eid'] . '-' . $row['revisionid'];
    }

    private function addMetadata(array &$connections, $metadataResult)
    {
        foreach ($metadataResult as $metadata) {
            if (!isset($connections[$this->getKey($metadata)]['metadata'])) {
                $connections[$this->getKey($metadata)]['metadata'] = array();
            }
            $connections[$this->getKey($metadata)]['metadata'][] = array(
                'key' => $metadata['key'],
                'value' => $metadata['value']
            );
        }
    }

    private function addArps(array &$connections, $arpResult)
    {
        foreach ($arpResult as $arp) {
            $arpAttributes = unserialize($arp['arpAttributes']);
            if (empty($arpAttributes)) {
                $arpAttributes = null;
            }

            $connections[$this->getKey($arp)]['arpAttributes'] = $arpAttributes;
        }
    }

    private function addAllowedconnections(array &$connections, $allowedConnectionResult)
    {
        foreach ($allowedConnectionResult as $allowedConnection) {

            if (!isset($connections[$this->getKey($allowedConnection)]['allowedConnections'])) {
                $connections[$this->getKey($allowedConnection)]['allowedConnections'] = array();
            }
            $connections[$this->getKey($allowedConnection)]['allowedConnections'][] = $allowedConnection['allowedEntityid'];
        }
    }

    private function addDisableconsents(array &$connections, $disableConsentResult)
    {
        foreach ($disableConsentResult as $disableConsent) {

            if (!isset($connections[$this->getKey($disableConsent)]['disableConsent'])) {
                $connections[$this->getKey($disableConsent)]['disableConsent'] = array();
            }
            $connections[$this->getKey($disableConsent)]['disableConsent'][] = $disableConsent['disableConsentEntityid'];
        }
    }

    private function addUsers(array &$connections, $userResult)
    {
        foreach ($userResult as $user) {

            if (!isset($connections[$this->getKey($user)]['users'])) {
                $connections[$this->getKey($user)]['users'] = array();
            }
            $connections[$this->getKey($user)]['users'][] = $user['username'];
        }
    }
}
