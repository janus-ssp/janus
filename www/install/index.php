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

use Doctrine\ORM\EntityManager;

$sspConfig = SimpleSAML_Configuration::getInstance();
$t = new SimpleSAML_XHTML_Template($sspConfig, 'janus:install.php', 'janus:install');
$t->data['header'] = 'JANUS - Install';

$path = realpath('module.php');
$config_path = str_replace('module.php','../modules/janus/config-templates/module_janus.php',$path);
include($config_path);
$t->data['dbprefix'] = $config['store']['prefix'];

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
    $config['store']['dsn'] = $dsn;
    $config['store']['username'] = $user;
    $config['store']['password'] = $pass;
    $config['store']['prefix'] = $prefix;
    $config['admin.name'] = $admin_name;
    $config['admin.email'] = $admin_email;
    SSPConfigFactory::setInstallConfig($config);

    try {
        $diContainer = sspmod_janus_DiContainer::getInstance();
        $entityManager = $diContainer->getEntityManager();

        $t->data['migrationLog'] = createDatabaseSchema(
            $entityManager,
            $diContainer->getSymfonyKernel()
        );

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
        $t->data['error_message'] = $e->getMessage();
        $t->data['success'] = FALSE;
    }
}
$t->show();

/**
 * Creates database by running Doctrine Migrations.
 *
 * @param EntityManager $entityManager
 * @param AppKernel $symfonyKernel
 * @return string
 */
function createDatabaseSchema(
    EntityManager $entityManager,
    AppKernel $symfonyKernel
) {

    $app = new Application($symfonyKernel);
    $app->setAutoExit(false);

    $input = new StringInput('doctrine:migrations:migrate --no-interaction');
    $output = new BufferedOutput();

    // Pre-authenticate as 'admin' for logging purposes.
    sspmod_janus_DiContainer::preAuthenticate('admin', 'install');

    $error = $app->run($input, $output);
    $msg = $output->fetch();
    if ($error) {
        $msg = 'Error ' . $error . ' ' . $msg;
    }

    return $msg;
}
