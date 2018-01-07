<?php
use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use Janus\ServiceRegistry\Entity\User;
use Janus\ServiceRegistry\Entity\User\Message;
use Janus\ServiceRegistry\Entity\User\Subscription;

/**
 * JANUS postman
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/janus-ssp/janus/
 * @since      File available since Release 1.2.0
 */
/**
 * JANUS postman
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/janus-ssp/janus/
 * @see        sspmod_janus_Database
 * @since      Class available since Release 1.2.0
 */
class sspmod_janus_Postman extends sspmod_janus_Database
{
    /**
     * JANUS config
     * @var ConfigProxy
     */
    private $_config;

    /**
     * Pagination count
     * @var int
     */
    private $_paginate;

    /**
     * instantiate the postman
     *
     * @since Method available since Release 1.2.0
     */
    public function __construct()
    {
        $this->_config = sspmod_janus_DiContainer::getInstance()->getConfig();

        $this->_paginate = $this->_config->getValue('dashboard.inbox.paginate_by', 20);
    }

    /**
     * Retrive all entities from database
     *
     * The method retrives all entities from the database together with the
     * newest revision id.
     *
     * @param string        $subject The message title
     * @param string        $message The mesage body
     * @param arrayt|string $address Address for which the messege is sent to
     * @param int           $from    Uid of user responsible for sending the message
     *
     * @return false|array All entities from the database
     * @throws \Exception
     */
    public function post($subject, $message, $address, $from)
    {
        $external_messengers = $this->_config->getArray('messenger.external', array());

        $fromUser = $this->getUserService()->findById($from);

        // and prepend the userid to the message
        $message = 'User: ' . $fromUser->getUsername() . '<br />' . $message;

        $addresses = array();
        if (!is_array($address)) {
            $addresses[] = $address;
        } else {
            $addresses = $address;
        }

        $entityManager = $this->getEntityManager();
        $addSpecialUserForHooks = $this->checkIfSpecialHookUserExists();
        foreach ($addresses AS $ad) {
            $subscripers = $this->_getSubscripers($ad);
            if ($addSpecialUserForHooks) {
                $subscripers[] = array('uid' => '0', 'type' => 'INBOX');
            }

            foreach ($subscripers AS $subscriper) {
                $subscribingUser = $this->getUserService()->findById($subscriper['uid']);

                // Create message
                $messageEntity = new Janus\ServiceRegistry\Entity\User\Message(
                    $subscribingUser,
                    $subject,
                    $message,
                    $fromUser,
                    $ad
                );
                $entityManager->persist($messageEntity);

                if(array_key_exists($subscriper['type'], $external_messengers))
                {
                    $externalconfig = $external_messengers[$subscriper['type']];
                    try {
                        $messenger = sspmod_janus_Messenger::getInstance($externalconfig['class'], $externalconfig['option']);
                        $messenger->send(array(
                            'uid' => $subscriper['uid'],
                            'subject' => $subject,
                            'message' => $message,
                            'from' => $from,
                            'address' => $ad  
                        ));
                    }
                    catch(Exception $e) {
                        \SimpleSAML\Logger::error('JANUS: Error sending external message. ' . $e->getMessage());
                    }
                }
            }
        }

        $entityManager->flush();
        return true;
    }

    /**
     * Checks if a special user is configured for hooks
     *
     * @return bool
     */
    private function checkIfSpecialHookUserExists()
    {
        $st = self::execute('
            SELECT uid
            FROM `'. $this->getTablePrefix() .'user`
            WHERE uid  = 0;
        ');

        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error while find special user for message hook (uid: 0)');
            return false;
        }

        return $st->rowCount() === 1;
    }


    /**
     * Subscribe to an address
     *
     * @param int    $uid          Uid of user
     * @param string $subscriptionAddress The address to subscribe
     * @param string $type         Type of subscription
     *
     * @return bool Return true on success and false on error
     * @throws \Exception
     */
    public function subscribe($uid, $subscriptionAddress, $type = null)
    {
        if (is_null($type)) {
            $type = $this->_config->getString('messenger.default', 'INBOX');
        }

        $subscribingUser = $this->getUserService()->findById($uid);

        // Check if subscription already exists
        $entityManager = $this->getEntityManager();
        $existingSubscription = $entityManager->getRepository('Janus\ServiceRegistry\Entity\User\Subscription')->findOneBy(
            array(
                'user' => $subscribingUser,
                'address' => $subscriptionAddress
            )
        );

        if($existingSubscription instanceof Subscription) {
            return false;
        }

        // Create subscription
        $subscription = new Subscription(
            $subscribingUser,
            $subscriptionAddress,
            $type
        );

        $entityManager->persist($subscription);
        $entityManager->flush();

        return $subscription->getId();
    }

    public function updateSubscription($sid, $uid, $type)
    {
        $entityManager = $this->getEntityManager();

        // Get subscription
        $subscription = $entityManager->getRepository('Janus\ServiceRegistry\Entity\User\Subscription')->findOneBy(
            array(
                'id' => $sid,
                'user' => $uid
            )
        );
        if(!$subscription instanceof Subscription) {
            throw new \Exception("User subscription '{$sid}' for user '{$uid}' does not exist");
        }

        try {
            $subscription->update($type);
            $entityManager->persist($subscription);
            $entityManager->flush();
        } catch(\Exception $ex) {
            \SimpleSAML\Logger::error('janus: Error updating subscription - ' . var_export(array($sid, $uid, $subscription, $type), true));
            throw $ex;
        }

        return true;
    }

    /**
     * Unsubscribe to an address
     *
     * @param int    $uid          Uid of user
     * @param string $sid The address to unsubscribe from
     *
     * @return bool Return true on success and false on error
     * @throws \Exception
     */
    public function unSubscribe($uid, $sid)
    {
        $entityManager = $this->getEntityManager();

        // Get subscription
        $subscription = $entityManager->getRepository('Janus\ServiceRegistry\Entity\User\Subscription')->findOneBy(
            array(
                'id' => $sid,
                'user' => $uid
            )
        );
        if(!$subscription instanceof Subscription) {
            throw new \Exception("User subscription '{$sid}' for user '{$uid}' does not exist");
        }

        $entityManager->remove($subscription);
        $entityManager->flush();

        return true;
    }

    /**
     * Retrive that users who subscribe to a particular address
     *
     * @param string $address An address
     *
     * @return array An array of uid's of user who subscribe to the address
     */
    private function _getSubscripers($address)
    {
        $addtp = array($address);
        $addressses = array();
        while (list($akey, $address) = each($addtp))
        {
            $ad = explode('-', $address);
            foreach ($ad AS $key => $value) {
                $tmp = array_slice($ad, 0, $key+1);
                $addressses[] = implode('-', $tmp);
                // Insert wildcard address
                if (ctype_digit($ad[$key])) {
                    $oldval = $ad[$key];
                    $ad[$key] = '#';
                    $addtp[] = implode('-', $ad);
                    $ad[$key] = $oldval;
                }
            }
            unset($addtp[$akey]);
        }
        $addressses = array_unique($addressses);
        $subscripers = array();
        foreach ($addressses AS $a) {
            $st = self::execute(
                'SELECT * FROM `'. $this->getTablePrefix() .'subscription`
                WHERE `subscription` = ?;',
                array($a)
            );

            if ($st === false) {
                \SimpleSAML\Logger::error('JANUS: Error fetching subscriptions');
                return false;
            }

            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                $subscripers[] = $row;
            }
            $st = null;
        }
        return $subscripers;
    }

    /**
     * Get all addresses in JANUS
     *
     * @return array All addresses in JANUS
     */
    public function getSubscriptionList()
    {
        // Predifined subscriptions
        $subscriptionList = array('ENTITYUPDATE', 'USER', 'USER-NEW', 'ENTITYCREATE');

        // Get all existing subscriptions
        $st = self::execute(
            'SELECT DISTINCT(`subscription`) AS `subscription` 
            FROM `'. $this->getTablePrefix() .'subscription`;'
        );

        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error fetching subscriptions');
            return false;
        }

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $subscriptionList[] = $row['subscription'];
        }

        $st = null;

        // Get subscription to all active users
        $st = self::execute(
            'SELECT `uid` FROM `'. $this->getTablePrefix() .'user` WHERE `active` = ?;',
            array('yes')
        );

        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error fetching subscriptions');
            return false;
        }

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $subscriptionList[] = 'USER-' . $row['uid'];
        }

        $st = null;

        // Get subscription to all active users
        $st = self::execute(
            'SELECT `eid` FROM `'. $this->getTablePrefix() .'connectionRevision`;'
        );

        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error fetching subscriptions');
            return false;
        }

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $subscriptionList[] = 'ENTITYUPDATE-' . $row['eid'];
        }

        $workflowstates = $this->_config->getArray('workflowstates');

        foreach($workflowstates AS $key => $value) {
            $subscriptionList[] = 'ENTITYUPDATE-#-CHANGESTATE-' . $key;
        }
        $subscriptionList[] = 'ENTITYUPDATE-#-CHANGESTATE';
        
        // Remove dublicates
        $sl = array_unique($subscriptionList);
        asort($sl);

        return $sl;
    }

    /**
     * Get all subscription on a user
     *
     * @param int $uid The users uid
     *
     * @return array An array of addresses
     */
    public function getSubscriptions($uid)
    {
        $st = self::execute(
            'SELECT `sid`, `subscription`, `type` FROM `'. $this->getTablePrefix() .'subscription`
            WHERE `uid` = ?;',
            array($uid)
        );

        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error fetching subscriptions');
            return false;
        }

        $subscriptions = array();
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $subscriptions[] = $row;
        }

        return $subscriptions;
    }

    /**
     * Get the number of messages for a user
     *
     * @param int $uid User uid
     *
     * @return int Number og messages
     */
    public function countMessages($uid)
    {
        $st = self::execute(
            'SELECT COUNT(*) FROM `'. $this->getTablePrefix() .'message` WHERE `uid` = ?;',
            array($uid)
        );

        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error counting subscriptions');
            return false;
        }

        $count = array_values($st->fetch(PDO::FETCH_ASSOC));
        return $count[0];
    }

    /**
     * Get the messages for a user
     *
     * The method will only retrive a subset of the messages defined by the page 
     * parameter and the paginate_by option set in the config file.
     *
     * @param int $uid   User uid
     * @param int &$page The page for which the messages should me retrived
     *
     * @return array Array og messages
     */
    public function getMessages($uid, &$page=0)
    {
        $sql = 'SELECT * FROM `'. $this->getTablePrefix() .'message` WHERE `uid` = ?
        ORDER BY `created` DESC LIMIT ' . $this->_paginate;
        if ($page == 0) {
            $st = self::execute(
                $sql . ';',
                array($uid)
            );
        } else {
            $st = self::execute(
                $sql . ' OFFSET '. ($page-1)*$this->_paginate .';',
                array($uid)
            );
        }

        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error fetching subscriptions');
            return false;
        }

        $messages = array();
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $messages[] = $row;
        }

        return $messages;
    }

    /**
     * Retrive a particular message
     *
     * @param int $mid The message id
     *
     * @return array Array containing the message including the subject and 
     * metadata.
     */
    public function getMessage($mid)
    {
        $st = self::execute(
            'SELECT * FROM `'. $this->getTablePrefix() .'message` WHERE `mid` = ?;',
            array($mid)
        );

        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error fetching subscriptions');
            return false;
        }

        $message = '';
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $message = $row;
        }

        return $message;
    }

    /**
     * Mark a message as read
     *
     * @param int $mid Message id
     *
     * @return bool True on success and false on error
     */
    public function markAsRead($mid)
    {
        $st = self::execute(
            'UPDATE `'. $this->getTablePrefix() .'message` SET `read` = ? WHERE `mid` = ?;',
            array('yes', $mid)
        );

        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error fetching subscriptions');
            return false;
        }

        return true;
    }
    
    /**
     * Get pagination cout
     *
     * @return int pagination count
     * @since Method available since Release 1.5.0
     */
    public function getPaginationCount()
    {
        return $this->_paginate;
    }
    
    /**
     * Set pagination cout
     *
     * @param int $pagination Pagination count
     *
     * @return void
     * @since Method available since Release 1.5.0
     */
    public function setPaginationCount($pagination)
    {
        assert('is_int($pagination)');

        $this->_paginate = $pagination;
    }
}
?>
