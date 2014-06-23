<?php
use Janus\ServiceRegistry\Service\ConnectionService;
use Janus\ServiceRegistry\Service\UserService;

/**
 * Database wrapper
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @author     lorenzo.gil.sanchez <lorenzo.gil.sanchez@gmail.com>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/janus-ssp/janus/
 * @since      File available since Release 1.0.0
 * @todo       Rewrite storage engine (Issue 21)
 */
/**
 * Database warpper
 *
 * The class implements a constructor that parses the configuration and basic
 * methods for using the database. This classe is just a simplification of the
 * PDO functionality.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @author     lorenzo.gil.sanchez <lorenzo.gil.sanchez@gmail.com>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/janus-ssp/janus/
 * @see        PHP_MANUAL#PDO
 * @since      Class available since Release 1.0.0
 */
abstract class sspmod_janus_Database
{
    /**
     * DSN for the database.
     * @var string
     */
    private static $_dsn;

    /**
     * Username for the database.
     * @var string
     */
    private static $_username;

    /**
     * Password for the database.
     * @var string
     */
    private static $_password;

    /**
     * Database handler. Can not be serialized.
     * @var PDO
     * @see PHP_MANUAL#class.pdo
     */
    protected static $db = null;

    /**
     * Prefix for the tables in the database.
     * @var string
     */
    protected static $prefix;

    /**
     * Create a new Databse object
     *
     * The constructor takes the configuration and checks that all parameters is
     * corect. An exception will be throwen if the configuration parameters is
     * not known by the class. The constructor do not initiate the connection to
     * the database. This will be done when the method {@link execute()
     * execute} is called.
     *
     * @param array|null $parsedconfig Configuration for database
     *
     * @throws SimpleSAML_Error_Exception
     */
    protected function __construct($parsedconfig = null)
    {
        $config = sspmod_janus_DiContainer::getInstance()->getConfig();
        $config = $config->getArray('store');
        
        if (isset($parsedconfig) && is_array($parsedconfig)) {
            $config = $parsedconfig; 
        }
        
        foreach (array('dsn', 'username', 'password', 'prefix') as $id) {
            if (!array_key_exists($id, $config)) {
                throw new SimpleSAML_Error_Exception(
                    'JANUS:Database - Missing required option \'' . $id . '\'.'
                );
            }
            if (!is_string($config[$id])) {
                throw new SimpleSAML_Error_Exception(
                    'JANUS:Database - \''.$id.'\' is supposed to be a string.'
                );
            }
        }

        self::$_dsn = $config['dsn'];
        self::$_username = $config['username'];
        self::$_password = $config['password'];
        self::$prefix = $config['prefix'];
    }

    /**
     * @return sspmod_janus_DiContainer
     */
    private function getDiContainer()
    {
        return sspmod_janus_DiContainer::getInstance();
    }

    /**
     * Shortcut for getting entitymanager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getDiContainer()->getEntityManager();
    }

    /**
     * Shortcut for getting connection service
     *
     * @return ConnectionService
     */
    public function getConnectionService()
    {
        return $this->getDiContainer()->getConnectionService();
    }

    /**
     * Shortcut for getting user service
     *
     * @return UserService
     */
    public function getUserService()
    {
        return $this->getDiContainer()->getUserService();
    }

    /**
     * Prepare and execute a SQL statement.
     *
     * The method will initiate the connection to the database if it hva not
     * been done. The method uses the prepare and execute methods from PDO. Note
     * that not all datases support prepared queries.
     *
     * @param string $statement  The SQL statement to be executed
     * @param array  $parameters Parameters for the SQL statement
     *
     * @return PDOStatement|false The PDOstatement, or false if execution failed
     * @see PHP_MANUAL#PDOStatement
     * @todo Should throw exception on database error
     */
    protected function execute($statement, $parameters = array())
    {
        assert('is_string($statement)');
        assert('is_array($parameters)');

        $db = $this->_getDB();
        if ($db === null) {
            return false;
        }

        try {
            $st = $db->prepare($statement);
        } catch(PDOException $e) {
            SimpleSAML_Logger::error(
                'JANUS:Database - Error preparing statement \''.$statement
                . '\': ' . self::formatError($db->errorInfo())
                . ' - ' . var_export($e, true)
            );
            return false;
        }
        if ($st->execute($parameters) !== true) {
            SimpleSAML_Logger::error(
                'JANUS:Database - Error executing statement \'' . $statement
                . '\': ' . self::formatError($st->errorInfo())
            );
            return false;
        }

        return $st;
    }

    /**
     * Format PDO error.
     *
     * Formats a PDO error to a readable string, as returned from errorInfo.
     *
     * @param array $error The error information.
     *
     * @return string Error text.
     * @see PHP_MANUAL#PDO.errorInfo
     */
    protected static function formatError($error)
    {
        assert('is_array($error)');
        assert('count($error) >= 3');

        return $error[0] . ' - ' . $error[2] . ' (' . $error[1] . ')';
    }

    /**
     * Initiate a database connection
     *
     * If a connection have already been initiated that connection will be
     * returned.
     *
     * @return PDO|null Database handle, or null if the conection failes
     * @see PHP_MANUAL#class.pdo
     * @todo Throw exception on connection failiur.
     */
    private static function _getDB()
    {
        if (self::$db !== null) {
            return self::$db;
        }

        try {
            $options = array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            );
            self::$db = new PDO(self::$_dsn, self::$_username, self::$_password, $options);
            // Set the error reporting attribute
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            self::$db = null;
            throw new SimpleSAML_Error_Exception(
                'janus:Database - Failed to connect to \''
                . self::$_dsn . '\': '. $e->getMessage()
            );
        }
        return self::$db;
    }
}
?>
