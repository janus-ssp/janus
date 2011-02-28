<?php
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
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
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
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @see        sspmod_janus_Database
 * @since      Class available since Release 1.2.0
 */
class sspmod_janus_Postman extends sspmod_janus_Database
{
    /**
     * JANUS config
     * @var SimpleSAML_Configuration
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
        $this->_config = SimpleSAML_Configuration::getConfig('module_janus.php');

        // Send DB config to parent class
        parent::__construct($this->_config->getValue('store'));

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
     */
    public function post($subject, $message, $address, $from)
    {
        $addresses = array();
        if (!is_array($address)) {
            $addresses[] = $address;
        } else {
            $addresses = $address;
        }
        foreach ($addresses AS $ad) {
            $subscripers = $this->_getSubscripers($ad);
            $subscripers[] = 0;

            foreach ($subscripers AS $subscriper) {
                $st = self::execute(
                    'INSERT INTO `'. self::$prefix .'message`
                    (
                    `uid`, 
                    `subject`, 
                    `message`, 
                    `from`, 
                    `subscription`, 
                    `created`, 
                    `ip`
                    ) VALUES (?, ?, ?, ?, ?, ?, ?);',
                    array(
                        $subscriper,
                        $subject,
                        $message,
                        $from,
                        $ad,
                        date('c'),
                        $_SERVER['REMOTE_ADDR'],
                    )
                );

                if ($st === false) {
                    SimpleSAML_Logger::error('JANUS: Error fetching all entities');
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Subscribe to an address
     *
     * @param int    $uid          Uid of user
     * @param string $subscription The address to subscribe
     * @param string $type         Type of subscription
     *
     * @return bool Return true on success and false on error
     */
    public function subscribe($uid, $subscription, $type = 'INBOX')
    {
        // Check if subscription already exists
        $st = self::execute(
            'SELECT * 
             FROM `'. self::$prefix .'subscription`
             WHERE `uid` = ? AND `subscription` = ?',
            array($uid, $subscription)    
        );
        
        if ($st === false) {
            return false;
        }

        if($st->rowCount() > 0) {
            return false;
        }

        // Insert new subscription
        $st = self::execute(
            'INSERT INTO `'. self::$prefix .'subscription` 
            (`uid`, `subscription`, `type`, `created`, `ip`) 
            VALUES
            (?, ?, ?, ?, ?);',
            array(
                $uid,
                $subscription,
                $type,
                date('c'),
                $_SERVER['REMOTE_ADDR'],
            )
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching all entities');
            return false;
        }

        return self::$db->lastInsertId();
    }

    public function updateSubscription($sid, $uid, $type)
    {
        $st = self::execute(
            'UPDATE `'. self::$prefix .'subscription` 
             SET `type` = ?, `uid` = ?, `created` = ?, `ip` = ?
             WHERE `sid` = ?;',
            array(
                $type,
                $uid,
                date('c'),
                $_SERVER['REMOTE_ADDR'],
                $sid
            )
        );

        if ($st === false) {
            simplesaml_logger::error('janus: Error updating subscription - ' . var_export(array($sid, $uid, $subscription, $type), true));
            return false;
        }

        return true;

    }
    /**
     * Unsubscribe to an address
     *
     * @param int    $uid          Uid of user
     * @param string $subscription The address to unsubscribe from
     *
     * @return bool Return true on success and false on error
     */
    public function unSubscribe($uid, $sid)
    {
        $st = self::execute(
            'DELETE FROM `'. self::$prefix .'subscription`
            WHERE `uid` = ? AND `sid` = ?;',
            array(
                $uid,
                $sid,
            )
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching all entities');
            return false;
        }

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
                'SELECT * FROM `'. self::$prefix .'subscription` 
                WHERE `subscription` = ?;',
                array($a)
            );

            if ($st === false) {
                SimpleSAML_Logger::error('JANUS: Error fetching subscriptions');
                return false;
            }

            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                $subscripers[] = $row['uid'];
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
            FROM `'. self::$prefix .'subscription`;'
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching subscriptions');
            return false;
        }

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $subscriptionList[] = $row['subscription'];
        }

        $st = null;

        // Get subscription to all active users
        $st = self::execute(
            'SELECT `uid` FROM `'. self::$prefix .'user` WHERE `active` = ?;',
            array('yes')
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching subscriptions');
            return false;
        }

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $subscriptionList[] = 'USER-' . $row['uid'];
        }

        $st = null;

        // Get subscription to all active users
        $st = self::execute(
            'SELECT `eid` FROM `'. self::$prefix .'entity`;'
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching subscriptions');
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
            'SELECT `sid`, `subscription`, `type` FROM `'. self::$prefix .'subscription` 
            WHERE `uid` = ?;',
            array($uid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching subscriptions');
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
            'SELECT COUNT(*) FROM `'. self::$prefix .'message` WHERE `uid` = ?;',
            array($uid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error counting subscriptions');
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
        $sql = 'SELECT * FROM `'. self::$prefix .'message` WHERE `uid` = ? 
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
            SimpleSAML_Logger::error('JANUS: Error fetching subscriptions');
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
            'SELECT * FROM `'. self::$prefix .'message` WHERE `mid` = ?;',
            array($mid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching subscriptions');
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
            'UPDATE `'. self::$prefix .'message` SET `read` = ? WHERE `mid` = ?;',
            array('yes', $mid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching subscriptions');
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
