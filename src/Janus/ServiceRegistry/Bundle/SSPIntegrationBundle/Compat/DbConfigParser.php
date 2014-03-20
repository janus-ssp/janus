<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\Compat;

/**
 * Parses legcay db config to symfony params
 *
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */
class DbConfigParser
{
    /**
     * @param array $dbParams
     * @return array
     */
    public function parse(array $dbParams)
    {
        // Always set a value for port
        $dbParams['port'] = null;

        // Doctrine uses user instead of username
        if (isset($dbParams['username'])) {
            $dbParams['user'] = $dbParams['username'];
            unset($dbParams['username']);
        }

        // Doctrine does not use dsn
        if (isset($dbParams['dsn'])) {

            $dsnParts = preg_split('/[:;]/', $dbParams['dsn']);
            unset($dbParams['dsn']);

            // Set driver (always use pdo)
            $dbParams['driver'] = 'pdo_' . array_shift($dsnParts);

            // Set host, dbname etc.
            foreach ($dsnParts as $value) {
                if (empty($value)) {
                    continue;
                }

                $entryParts = explode('=', $value);
                if (count($entryParts) === 1) {
                    $dbParams[$entryParts[0]] = true;
                } else {
                    $dbParams[$entryParts[0]] = $entryParts[1];
                }
            }
        }

        // Doctrine convention is name instead of dbname
        $dbParams['name'] = $dbParams['dbname'];
        unset($dbParams['dbname']);

        return $dbParams;
    }
}