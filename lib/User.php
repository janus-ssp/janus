<?php
use Janus\ServiceRegistry\Entity\User;

/**
 * A user
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @author     Sixto Martín, <smartin@yaco.es>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/janus-ssp/janus/
 * @since      File available since Release 1.0.0
 */
/**
 * A user
 *
 * Basic implementation of a user. NOTE that the way extra data regarding the
 * user is stored will change in the future.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @author     Sixto Martín, <smartin@yaco.es>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/janus-ssp/janus/
 * @since      Class available since Release 1.0.0
 */
class sspmod_janus_User extends sspmod_janus_Database
{
    /**
     * Constant telling load() to load the user using the uid
     */
    const UID_LOAD = '__LOAD_WITH_UID__';

    /**
     * Constant telling load() to load the user using the userid
     */
    const USERID_LOAD = '__LOAD_WITH_USERID__';

    /**
     * User uid
     * @var integer
     */
    private $_uid;

    /**
     * Username
     * @var string
     */
    private $_userid;

    /**
     * User email
     * @var string
     */
    private $_email;

    /**
     * User type
     * @var array
     */
    private $_type = array();

    /**
     * User active status
     * @var string
     */
    private $_active;

    /**
     * User data
     * @var array
     */
    private $_data;

    /**
     * User secret for REST access
     * @var string
     */
    private $_secret;

    /**
     * Indicates whether the user data has been modified or not
     * @var bool
     */
    private $_modified = false;

    /**
     * Saves the user data to the database.
     *
     * Method for saving the user data to the database. If the user data has not
     * been modified the methos just returns true. If an error occures and the
     * data is not saved the method returns false.
     *
     * @return bool true if data is saved end false if data is not saved.
     * @throws \Exception
     */
    public function save()
    {
        // If the user is not modified, just return
        if (!$this->_modified) {
            return true;
        }

        $entityManager = $this->getEntityManager();

        $this->validateUserTypes($this->_type);

        // uid is empty. This is a new user
        if (empty($this->_uid)) {
            // Test if username already exists
            $existingUser = $entityManager->getRepository('Janus\ServiceRegistry\Entity\User')->findOneBy(array('username' => $this->_userid));
            if ($existingUser instanceof User) {
                return false;
            }

            // Create new user
            $user = new User(
                $this->_userid,
                $this->_type,
                $this->_email,
                ($this->_active === 'yes')
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // Get new uid
            $this->_uid = $user->getId();

            $pm = new sspmod_janus_Postman();
            $pm->subscribe($this->_uid, 'USER-'.$this->_uid);
            $pm->post(
                'New user created',
                'A new user have been created. User ID: ' .
                $this->_userid .
                ' Uid: ' .
                $this->_uid,
                'USERCREATE',
                $this->_uid
            );
            unset($pm);
        } else {
            // Update existing user
            $existingUser = $this->getUserService()->findById($this->_uid);

            if (!$existingUser instanceof User) {
                throw new \Exception("User '{$this->_uid}' does not exist");
            }

            $existingUser->update(
                $this->_userid,
                $this->_type,
                $this->_email,
                ($this->_active === 'yes'),
                $this->_data,
                $this->_secret
            );

            $entityManager->persist($existingUser);
            $entityManager->flush();
        }

        $this->_modified = false;

        return true;
    }

    /**
     * @param array $types
     * @throws InvalidArgumentException
     */
    private function validateUserTypes(array $types)
    {
        $config = sspmod_janus_DiContainer::getInstance()->getConfig();
        $allowedTypes = $config->getArray('usertypes');

        foreach($types as $type) {
            if (!in_array($type, $allowedTypes)) {
                throw new \InvalidArgumentException("User Type '$type' is not allowed");
            }
        }
    }

    /**
     * Load user data from database
     *
     * The methos loades the user data from the database, either by uid or by
     * email. Which depends on the flag parsed to the method. uid is
     * preferrered.
     *
     * @param const $flag Flag to indicate load method
     *
     * @return PDOStatement|bool The statement or false if an error has occured.
     * @todo Skal kun returnere true/false (fjern exceptions)
     * @todo  Proper validation of $st
     */
    public function load($flag = self::UID_LOAD)
    {
        $load_type_map = array(
            self::UID_LOAD => array('uid', $this->_uid),
            self::USERID_LOAD => array('userid', $this->_userid),
        );

        if (!array_key_exists($flag, $load_type_map)) {
            throw new SimpleSAML_Error_Exception(
                'JANUS:User:load: Invalid flag parsed - '
                .var_export($flag)
            );
        }

        $current_type  = $load_type_map[$flag][0];
        $current_value = $load_type_map[$flag][1];

        $st = $this->execute(
            'SELECT * 
            FROM '. $this->getTablePrefix() .'user
            WHERE `'.$current_type.'` = ?',
            array($current_value)
        );

        if ($st === false) {
            throw new SimpleSAML_Error_Exception(
                'JANUS:User:save - Error executing statement : '
                .self::$db->errorInfo()
            );
            exit;
        }

        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rs)) {
            return false;
        }

        if ($row = $rs[0]) {
            $this->_uid    = $row['uid'];
            $this->_userid = $row['userid'];
            $this->_email  = $row['email'];
            $this->_type   = unserialize($row['type']);
            $this->_active = $row['active'];
            $this->_data   = $row['data'];
            $this->_secret = $row['secret'];

            $this->_modified = false;
        } else {
            return false;
        }
        return $st;
    }

    /**
     * Set uid
     *
     * Method to set the uid. Method sets _modified to true.
     *
     * @param int $uid Uid
     *
     * @return void
     */
    public function setUid($uid)
    {
        assert('ctype_digit($uid)');

        $this->_uid = $uid;

        $this->_modified = true;
    }

    /**
     * Set user id
     *
     * Method to set the user id. Method sets _modified to true.
     *
     * @param string $userid User id
     *
     * @return void
     */
    public function setUserid($userid)
    {
        assert('ctype_graph($userid)');

        $this->_userid = $userid;

        $this->_modified = true;
    }

    /**
     * Set user email
     *
     * Method for setting the user email. The method does not validate the
     * correctness of the email, only that it is a string and that is is not
     * longer that 320 chars. Method sets _modified to true.
     *
     * @param string $email User email
     *
     * @return void
     */
    public function setEmail($email)
    {
        assert('is_string($email)');
        assert('strlen($email) <= 320');

        $this->_email = $email;

        $this->_modified = true;
    }

    /**
     * Set user type
     *
     * Method for setting the user type. Method sets _modified to true.
     *
     * @param string $type User type
     *
     * @return void
     * @todo Test that type is valid according to the config.
     */
    public function setType($type)
    {
        //assert('is_string($type)');
        if (is_string($type)) {
            $this->_type[] = $type;
            $this->_type   = array_unique($this->_type);
        } else if (is_array($type)) {
            $this->_type = $type;
        }

        $this->_modified = true;
    }

    /**
     * Set user active
     *
     * @param string $active 'yes'/'no'
     *
     * @return void
     * @since Method available since Release 1.0.0
     * @todo Change active type to bool
     */
    public function setActive($active)
    {
        assert('is_string($active)');

        $this->_active = $active;

        $this->_modified = true;
    }

    /**
     * Get user active
     *
     * @return string 'yes'/'no'
     * @since Method available since Release 1.0.0
     */
    public function getActive()
    {
        return $this->_active;
    }

    /**
     * Get uid
     *
     * Method for getting the uid
     *
     * @return int The uid.
     */
    public function getUid()
    {
        return $this->_uid;
    }

    /**
     * Get user id
     *
     * Method for getting the user id.
     *
     * @return string The user id.
     */
    public function getUserid()
    {
        return $this->_userid;
    }

    /**
     * Get user email
     *
     * Method for getting the user email.
     *
     * @return string The user email.
     */
    public function getEmail()
    {
        return $this->getUserid();
    }

    /**
     * Get user type
     *
     * Method for getting the user types
     *
     * @return array The user types
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get user data
     *
     * @return string The user data
     * @since Method available since Release 1.0.0
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Get modified information.
     *
     * Method for getting the status of the _modified variable.
     *
     * @return bool true in user data is modified.
     */
    public function isModified()
    {
        return $this->_modified;
    }

    /**
     * Set user data
     *
     * The way additional data about a user is stored will be changed in the
     * future.
     *
     * @param string $data The user data
     *
     * @return void
     * @since Method available since Release 1.0.0
     */
    public function setData($data)
    {
        assert('is_string($data)');

        $this->_data = $data;

        $this->_modified = true;
    }

    /**
     * Delete the user from the database.
     *
     * Method for deleting the user from the database. If deletion sucessful or
     * if the user do not exist true will be returned. If an error occures and
     * the data is not deleted the method returns false.
     *
     * @return bool true if data is deleted end false if data is not deleted.
     * @since  Method available since Release 1.2.0
     */
    public function delete()
    {
        $st = $this->execute(
            'DELETE FROM '. $this->getTablePrefix() .'user
            WHERE `uid` = ?;',
            array($this->_uid)
        );

        if ($st === false) {
             throw new SimpleSAML_Error_Exception(
                 'JANUS:User:save - Error executing statement : '
                 .$st->errorInfo()
             );
        }
        return true;
    }

    /**
     * Set API secret
     *
     * @param string $secret The secret
     *
     * @return void
     */
    public function setSecret($secret)
    {
        assert('is_string($secret)');

        $this->_secret = $secret;

        $this->_modified = true;
    }

    /**
     * Retuern the API secret
     *
     * @return string The secret
     */
    public function getSecret()
    {
        return $this->_secret;
    }
}
?>
