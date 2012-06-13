<?php

/**
 * This script updates the ARP attributes values from 1.10 to 1.11 format.
 *
 * See also: http://code.google.com/p/janus-ssp/issues/detail?id=327
 */

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

// Establish a database connection
$db = new PDO($config['store']['dsn'], $config['store']['username'], $config['store']['password']);

// Query the database for the id and attributes of all ARPs
$arpTableName = $config['store']['prefix'] . 'arp';
$statement = $db->query("SELECT aid, attributes FROM $arpTableName");
if ($statement === false) {
    // Should this fail, exit with an error code and message
    echo "Error retrieving ARPs from $arpTableName?!?" . PHP_EOL;
    exit(1);
}
$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

// Loop through all the ARPs, assume we will succeed in the upgrade
$exitCode = 0;
foreach ($rows as $row) {
    // Thaw the attributes from a string back to a PHP array
    $attributes = unserialize($row['attributes']);

    // Start with a new attributes array that is empty
    $newAttributes = array();
    // Assume we're not going to have to do any conversion
    $oldStyleDetected = false;

    if (is_array($attributes)) {
        // If the attributes is a proper array, loop through the attributes
        foreach ($attributes as $attributeKey => $attributeValue) {
            // if the the value is an array, like so:
            // $attributes['urn:mace:dir:attribute-def:cn'] = array('*')
            // then this attribute is stored in the new style, otherwise, if it's stored like this:
            // $attributes[1] = "urn:mace:dir:attribute-def:gn"
            // then it's old style and needs to be converted.
            if (is_array($attributeValue)) {
                // New style, so re-add
                $newAttributes[$attributeKey] = $attributeValue;
            }
            else {
                // Convert old style.
                $oldStyleDetected = true;
                $newAttributes[$attributeValue] = array('*');
            }
        }
    }
    else {
        // If it is not, we have an error, but continue with the other ARPs anyway.
        $exitCode = 1;
        echo "Unable to convert ARP with id '{$row['aid']}', not a proper array stored?" . PHP_EOL;
        continue;
    }

    // Now if for this entire ARP no attribute has been detected as having the old style,
    // we don't need to actually update
    if (!$oldStyleDetected) {
        // Display a notice and continue with the next ARP
        echo "Not updating ARP with id '{$row['aid']}', already new style" . PHP_EOL;
        continue;
    }

    // Otherwise we do need to update the ARP with the new attributes
    $statement = $db->prepare("UPDATE $arpTableName SET attributes=? WHERE aid = ?");
    if (!$statement) {
        // If for some reason we can't modify the ARP, return an error code and message, BUT be sure to try the
        // other ARPs too.
        $exitCode = 1;
        echo "Error updating ARP with aid {$row['aid']}!" . PHP_EOL;
        continue;
    }

    // Store the new attributes in serialized format again.
    $executed = $statement->execute(array(
        serialize($newAttributes),
        $row['aid']
    ));

    // Depending if the saving actually worked
    if ($executed) {
        // Display a notice that we have updated the ARP
        echo "Updated ARP with aid '{$row['aid']}'" . PHP_EOL;
    }
    else {
        // Or throw an error
        $exitCode = 1;
        echo "Error updating ARP with aid '{$row['aid']}'!" . PHP_EOL;
    }

    // Continue with the next ARP until there are no more ARPs left to process
}

// Then return the exit code
exit($exitCode);