<?php
/**
 * No user created main file
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Site
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/janus-ssp/janus/
 * @since      File available since Release 1.5.0
 */

require __DIR__ . '/_includes.php';

$session = SimpleSAML_Session::getSessionFromRequest();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = sspmod_janus_DiContainer::getInstance()->getConfig();

$authsource = $janus_config->getValue('auth', 'login-admin');
$useridattr = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');

$as = new \SimpleSAML\Auth\Simple($authsource);

if ($as->isAuthenticated()) {
    $attributes = $as->getAttributes();
    // Check if userid exists
    if (!isset($attributes[$useridattr]))
        throw new Exception('User ID is missing');
    $userid = $attributes[$useridattr][0];
} else {
    $session->setData('string', 'refURL', SimpleSAML_Utilities::selfURL());
    SimpleSAML_Utilities::redirectTrustedUrl(SimpleSAML_Module::getModuleURL('janus/index.php'));
}

$session->doLogout($authsource);

$et = new SimpleSAML_XHTML_Template($config, 'janus:nonewuser.php', 'janus:nonewuser');
$et->data['admin_email'] = $janus_config->getValue('admin.email', '');
$et->show();
?>
