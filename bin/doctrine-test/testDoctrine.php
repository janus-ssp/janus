<?php
/**
 * Tests if all Doctrine models can be stored in the database properly
 *
 * NOTE: before running this, change your database name to a TEST database
 */

require_once __DIR__ . "/../../cli-config.php";
$user = new sspmod_janus_Model_User('admin', array('admin'));
$em->persist($user);
$em->flush();

$userData = new sspmod_janus_Model_User_Data($user, 'testKey', 'testValue');
$em->persist($userData);
$em->remove($userData);
$em->flush();

$subscribingUser = new sspmod_janus_Model_User('test', array('technical'));
$em->persist($subscribingUser);
$em->flush();

$userMessage = new sspmod_janus_Model_User_Message($user, 'testSubject', 'testMessage', $subscribingUser, 'testSubscription');
$em->persist($userMessage);
$em->flush();
$em->remove($userMessage);
$em->flush();

$userSubscription = new sspmod_janus_Model_User_Subscription(
    $subscribingUser,
    'testSubscription',
    'testType'
);
$em->persist($userSubscription);
$em->flush();
$em->remove($userSubscription);
$em->flush();

$em->remove($subscribingUser);
$em->flush();

$connection = new sspmod_janus_Model_Connection(
    'test-idp' . time(),
    'saml20-sp'
);
$em->persist($connection);
$em->flush();

$connectionRevision = new sspmod_janus_Model_Connection_Revision(
    $connection,
    0,
    null,
    'initial',
    'testaccepted',
    new \DateTime(),
    'http://test',
    true,
    array('foo'),
    null,
    true
);

$em->persist($connectionRevision);
$em->flush();

$remoteConnection = new sspmod_janus_Model_Connection(
    'test-sp' . time(),
    'saml20-sp'
);
$em->persist($remoteConnection);
$em->flush();

$remoteConnectionRevision = new sspmod_janus_Model_Connection_Revision(
    $remoteConnection,
    0,
    null,
    'initial',
    'test',
    new \DateTime(),
    'http://test',
    true,
    null,
    null,
    true
);
$em->persist($remoteConnectionRevision);
$em->flush();

$connectionAllowedConnectionRelation = new sspmod_janus_Model_Connection_Revision_AllowedConnectionRelation($connectionRevision, $remoteConnection);
$em->persist($connectionAllowedConnectionRelation);
$em->flush();
$em->remove($connectionAllowedConnectionRelation);
$em->flush();

$connectionBlockedConnectionRelation = new sspmod_janus_Model_Connection_Revision_BlockedConnectionRelation($connectionRevision, $remoteConnection);
$em->persist($connectionBlockedConnectionRelation);
$em->flush();
$em->remove($connectionBlockedConnectionRelation);
$em->flush();

$connectionDisableConsentRelation = new sspmod_janus_Model_Connection_Revision_DisableConsentRelation($connectionRevision, $remoteConnection);
$em->persist($connectionDisableConsentRelation);
$em->flush();
$em->remove($connectionDisableConsentRelation);
$em->flush();

$connectionMetadata = new sspmod_janus_Model_Connection_Revision_Metadata($connectionRevision, 'testKey', 'testValue');
$em->persist($connectionMetadata);
$em->flush();
$em->remove($connectionMetadata);
$em->flush();

$em->remove($remoteConnectionRevision);
$em->remove($remoteConnection);
$em->flush();

$userConnectionRelation = new sspmod_janus_Model_User_ConnectionRelation($user, $connection);
$em->persist($userConnectionRelation);
$em->flush();
$em->remove($userConnectionRelation);
$em->flush();

$em->remove($user);
$em->remove($connectionRevision);
$em->remove($connection);
$em->flush();