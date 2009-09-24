<?php
/**
 * JANUS postman 
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
 * @subpackage Core 
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen 
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version    SVN: $Id: AdminUtil.php 121 2009-09-02 08:56:54Z jach@wayf.dk $
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
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version    SVN: $Id: AdminUtil.php 121 2009-09-02 08:56:54Z jach@wayf.dk $
 * @link       http://code.google.com/p/janus-ssp/
 * @see        Sspmod_Janus_Database
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
     * instantiate the postman
     *
     * @since Method available since Release 1.2.0
     */
    public function __construct()
    {
        $this->_config = SimpleSAML_Configuration::getConfig('module_janus.php');

        // Send DB config to parent class
        parent::__construct($this->_config->getValue('store'));
    }

    /**V
     * Retrive all entities from database
     *
     * The method retrives all entities from the database together with the 
     * newest revision id.
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

        foreach($addresses AS $ad)
        {
            $subscripers = $this->getSubscripers($ad);
            $subscripers[] = 0;

            foreach($subscripers AS $subscriper) 
            {
                $st = self::execute(
                    'INSERT INTO `'. self::$prefix .'message`
                    (`uid`, `subject`, `message`, `from`, `subscription`, `created`, `ip`) 
                    VALUES 
                    (?, ?, ?, ?, ?, ?, ?);',
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

    public function subscribe($uid, $subscription, $type = 'INBOX')
    {
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
        
        return true;
    }

    public function unSubscribe($uid, $subscription)
    {
        $st = self::execute(
            'DELETE FROM `'. self::$prefix .'subscription`
            WHERE `uid` = ? AND `subscription` = ?;',
            array(
                $uid,
                $subscription,
            )            
        );
        
        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching all entities');
            return false;
        }
        
        return true;
    }
    private function getSubscripers($address) 
    {
        $ad = explode('-', $address);
        $addressses = array();
        foreach($ad AS $key => $value)
        {
            $tmp = array_slice($ad, 0, $key+1);
            $addressses[] = implode('-', $tmp);
        }
        $subscripers = array();
        foreach($addressses AS $a)
        {
            $st = self::execute(
                'SELECT * FROM `'. self::$prefix .'subscription` WHERE `subscription` = ?;',
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

    public function getSubscriptionList()
    {
        $subscriptionList = array('ENTITYUPDATE', 'USER', 'ENTITYCREATE', 'USERUPDATE');

        $st = self::execute(
            'SELECT DISTINCT(`subscription`) AS `subscription` FROM `'. self::$prefix .'subscription`;'
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching subscriptions');
            return false;
        }

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $subscriptionList[] = $row['subscription'];
        }
    
        $sl = array_unique($subscriptionList);
        asort($sl);

        return $sl;
    }

    public function getSubscriptions($uid)
    {
        $st = self::execute(
            'SELECT `sid`, `subscription` FROM `'. self::$prefix .'subscription` WHERE `uid` = ?;',
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

    public function getMessages($uid) 
    {
        $st = self::execute(
            'SELECT * FROM `'. self::$prefix .'message` WHERE `uid` = ? ORDER BY `created` DESC;',
            array($uid)
        );


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

    public function markAsRead($mid) {
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
}
?>
