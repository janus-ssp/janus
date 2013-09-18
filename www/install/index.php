<?php
/**
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @author Sixto Martín, <smartin@yaco.es>
 */
require_once __DIR__ . '/../../autoload.php';

use Doctrine\DBAL\Migrations\OutputWriter;

$config = SimpleSAML_Configuration::getInstance();
$t = new SimpleSAML_XHTML_Template($config, 'janus:install.php', 'janus:install');
$t->data['header'] = 'JANUS - Install';

if(isset($_POST['action']) && $_POST['action'] == 'install') {

    // Get db config from post
    $type = $_POST['dbtype'];
    $host = $_POST['dbhost'];
    $name = $_POST['dbname'];
    $prefix = $_POST['dbprefix'];
    $user = $_POST['dbuser'];
    $pass = $_POST['dbpass'];

    $dsn = $type .':host='. $host . ';dbname='. $name;

    // Get admin user from post
    $admin_email = $_POST['admin_email'];
    $admin_name = $_POST['admin_name'];

    // Create example config
    $path = realpath('module.php');
    $config_path = str_replace('module.php','../modules/janus/config-templates/module_janus.php',$path);
    include($config_path);
    $config['store']['dsn'] = $dsn;
    $config['store']['username'] = $user;
    $config['store']['password'] = $pass;
    $config['store']['prefix'] = $prefix;
    $config['admin.name'] = $admin_name;
    $config['admin.email'] = $admin_email;

    try {
        // Create database by running Doctrine Migrations
        $migrationLog = '';
        $outputWriter = factoryOutputWriter($migrationLog);

        $diContainer = sspmod_janus_DiContainer::getInstance();

        // Get database connection
        $parsedDbParams = $diContainer->parseDbParams($config['store']);
        $entityManager = $diContainer->createEntityManager($parsedDbParams);

        $migration = $diContainer->createMigration($outputWriter, $entityManager->getConnection());
        $migration->migrate();
        $t->data['migrationLog'] = $migrationLog;

        // Create user
        $adminUser = new sspmod_janus_Model_User($admin_name, array('admin'));
        $adminUser->setEmail($admin_email);
        $adminUser->setData('Navn: '.$admin_name);

        $entityManager->persist($adminUser);
        $entityManager->flush();

        $t->data['success'] = true;
        $t->data['config_template'] = $config;
        $t->data['prefix'] = $prefix;
        $t->data['email'] = $admin_email;
        $t->data['dsn'] = $dsn;
        $t->data['user'] = $user;
        $t->data['pass'] = $pass;
    } catch(Exception $e) {
        $t->data['success'] = FALSE;
    }
}
$t->show();

/**
 * @param string &$output
 * @return OutputWriter
 */
function factoryOutputWriter(&$output)
{
    $outputWriter = new OutputWriter(function($message)  use (&$output) {
        // @todo find out how to let Doctrine generate messages which do not contain xml
        $output .= strip_tags($message) . PHP_EOL;
    });

    return $outputWriter;
}