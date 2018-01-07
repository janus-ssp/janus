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
            \SimpleSAML\Logger::error(
                'JANUS:Database - Error preparing statement \''.$statement
                . '\': ' . self::formatError($db->errorInfo())
                . ' - ' . var_export($e, true)
            );
            return false;
        }
        if ($st->execute($parameters) !== true) {
            \SimpleSAML\Logger::error(
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
     * Returns prefix for tables
     *
     * @return string
     */
    public function getTablePrefix()
    {
        static $tablePrefix;
        if (!$tablePrefix) {
            $tablePrefix = sspmod_janus_DiContainer::getInstance()
               ->getSymfonyContainer()
               ->getParameter('database_prefix');

        }

        return $tablePrefix;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    private function _getDB()
    {
        return $this->getEntityManager()->getConnection();
    }
}
?>
