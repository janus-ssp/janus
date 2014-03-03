<?php
/**
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @author Sixto Martín, <smartin@yaco.es>
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */
require_once __DIR__ . '/../../app/autoload.php';

use Janus\ServiceRegistry\Entity\User;
use Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\DependencyInjection\SSPConfigFactory;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

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

        define('JANUS_INSTALL_MODE', '');
        SSPConfigFactory::setInstallConfig($config);

        $diContainer = sspmod_janus_DiContainer::getInstance();

        // Get database connection
        $entityManager = $diContainer->getEntityManager();

        $app = new Application($diContainer->getSymfonyKernel());
        $app->setAutoExit(false);

        $input = new StringInput('doctrine:migrations:migrate --no-interaction');
        $output = new BufferedOutput();

        $error = $app->run($input, $output);
        $msg = $output->fetch();
        if ($error) {
            $msg = 'Error ' . $error . ' ' . $msg;
        }

        $t->data['migrationLog'] = $msg;

        // Create user
        $adminUser = new User(
            $admin_name,
            array('admin'),
            $admin_email
        );
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