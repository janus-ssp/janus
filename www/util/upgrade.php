<?php
/**
 * Upgrade script fra JANUS 1.5 to JANUS 1.6
 *
 * This script should be deleted after use or if its use is not required.
 *
 * PHP version 5
 *
 * JANUS is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * JANUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with JANUS. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Upgrade
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2010 Jacob Christiansen
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      File available since Release 1.6.0
 */

// Uncomment line below to grant access to the script
die('No access');

// Modify variables below to fit your DA
$type = null;
$host = null;
$name = null;
$prefix = null;
$user = null;
$pass = null;

// Modify variable below to fit your DB
$dsn = $type .':host='. $host . ';dbname='. $name;

// Metadata fields to be updated
$fields = array(
    'SingleSignOnService' => 'SingleSignOnService:0:Location',                
    'SingleLogoutService' => 'SingleLogoutService:0:Location',
    'certFingerprint' => 'certFingerprint:0',
    'AssertionConsumerService' => 'AssertionConsumerService:0:Location',
    'SingleLogoutService' => 'SingleLogoutService:0:Location',
    'contacts:contactType' => 'contacts:0:contactType',
    'contact:name' => 'contact:0:name',
    'contact:surName' => 'contact:0:surName',
    'contact:givenName' => 'contact:0:givenName',
    'contact:telephoneNumber' => 'contact:0:telephoneNumber',
    'contact:company' => 'contact:0:company',
    'contact:emailAddress' => 'contact:0:emailAddress',
    'entity:description:da' => 'description:da',
    'entity:description:en' => 'description:en',
    'entity:description:es' => 'description:es',
    'entity:name:da' => 'name:da',
    'entity:name:en' => 'name:en',
    'entity:name:es' => 'name:es',
    'entity:url:da' => 'url:da',
    'entity:url:en' => 'url:en',
    'entity:url:es' => 'url:es',
);

$error = false;

//--------------------------- Script start -----------------------------------
echo '<h1>DB update for JANUS 1.5 to 1.6</h1>';
echo '<p>The entity table will be updated to include a column for the user ID.</p>';
echo '<p>The following metadata fields will be updated</p>';
echo '<pre>';
print_r($fields);
echo '</pre>';
echo '<hr><h2>Updating started</h2>';

$dbh = new PDO($dsn, $user, $pass);

$dbh->beginTransaction();

// Update entity table 
$st = $dbh->exec('
    ALTER TABLE `' . $prefix  . 'entity` 
    ADD `user` INT NOT NULL AFTER `arp`;'
);

if($st === false) {
    echo '<p><b style="color: #FF0000;">ERROR:</b> entity table not updated</p>';
    echo '<p>Error information</p>';
    echo '<pre>';
    print_r($dbh->errorInfo());
    echo '</pre>';
    $error = true;
} else {
    echo '<p><b style="color: #00FF00">OK:</b> entity table updated</p>';
}

// Update the metadata fields
foreach($fields AS $old => $new) {
    $st = $dbh->prepare('
        UPDATE `' . $prefix  . 'metadata`
        SET `key` = ?
        WHERE `key` = ?'            
    );
    
    $st->execute(array($new, $old));

    if($st === false) {
        echo '<p><b style="color: #FF0000;">ERROR:</b> ' . $old . ' not updated</p>';
        echo '<p>Error information</p>';
        echo '<pre>';
        print_r($dbh->errorInfo());
        echo '</pre>';
        $error = true;
    } else {
        echo '<p><b style="color: #00FF00">OK:</b> ' . $old . ' changed to ' . $new . '</p>';
    }
}

if(!$error) {
    $dbh->commit();
    echo '<h2 style="color: 00FF00">Update successful</h2>';
} else {
    $dbh->rollBack();
    echo '<h2 style="color: #FF0000">Update was not succesfull. Changes have been rolled back</h2>';
}
