<?php
require_once __DIR__ . "/../../app/autoload.php";

use Janus\ServiceRegistry\Entity\User;
use Janus\ServiceRegistry\Entity\Connection;

$em = sspmod_janus_DiContainer::getInstance()->getEntityManager();

/**
 * Tests if all Doctrine models can be stored in the database properly
 *
 * NOTE: before running this, change your database name to a TEST database
 */

$user = new User('admin', array('admin'));
$em->persist($user);
$em->flush();

$userData = new User\Data($user, 'testKey', 'testValue');
$em->persist($userData);
$em->remove($userData);
$em->flush();

$subscribingUser = new User('test', array('technical'));
$em->persist($subscribingUser);
$em->flush();

$userMessage = new User\Message($user, 'testSubject', 'testMessage', $subscribingUser, 'testSubscription');
$em->persist($userMessage);
$em->flush();
$em->remove($userMessage);
$em->flush();

$userSubscription = new User\Subscription(
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

$connection = new Connection(
    'test-idp' . time(),
    'saml20-idp'
);

$connection->update(
    $connection->getName(),
    $connection->getType(),
    null,
    'initial',
    'testaccepted',
    new \DateTime(),
    'http://test',
    true,
    array('foo'),
    null,
    true,
    null
);
$connectionRevision = $connection->getLatestRevision();

$em->persist($connection);
$em->flush();

$remoteConnection = new Connection(
    'test-sp' . time(),
    'saml20-sp'
);
$remoteConnection->update(
    $remoteConnection->getName(),
    $remoteConnection->getType(),
    null,
    'initial',
    'test',
    new \DateTime(),
    'http://test',
    true,
    null,
    null,
    true,
    null
);
$remoteConnectionRevision = $remoteConnection->getLatestRevision();

$em->persist($remoteConnection);
$em->flush();

$connectionAllowedConnectionRelation = new Connection\Revision\AllowedConnectionRelation($connectionRevision, $remoteConnection);
$em->persist($connectionAllowedConnectionRelation);
$em->flush();
$em->remove($connectionAllowedConnectionRelation);
$em->flush();

$connectionBlockedConnectionRelation = new Connection\Revision\BlockedConnectionRelation($connectionRevision, $remoteConnection);
$em->persist($connectionBlockedConnectionRelation);
$em->flush();
$em->remove($connectionBlockedConnectionRelation);
$em->flush();

$connectionDisableConsentRelation = new Connection\Revision\DisableConsentRelation($connectionRevision, $remoteConnection);
$em->persist($connectionDisableConsentRelation);
$em->flush();
$em->remove($connectionDisableConsentRelation);
$em->flush();

$connectionMetadata = new Connection\Revision\Metadata($connectionRevision, 'testKey', 'testValue');
$em->persist($connectionMetadata);
$em->flush();
$em->remove($connectionMetadata);
$em->flush();

$em->remove($remoteConnection);
$em->flush();

$userConnectionRelation = new User\ConnectionRelation($user, $connection);
$em->persist($userConnectionRelation);
$em->flush();
$em->remove($userConnectionRelation);
$em->flush();

$em->remove($user);
$em->remove($connection);
$em->flush();