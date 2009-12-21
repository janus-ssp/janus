<?php
/**
 * No user created main file
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
 * @subpackage Site
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @author     Lorenzo Gil Sanchez <lgs@yaco.es>
 * @author     Sixto Martín <smartin@yaco.es>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      File available since Release 1.0.0
 */
$session = SimpleSAML_Session::getInstance();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

$authsource = $janus_config->getValue('auth', 'login-admin');
$useridattr = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');

// Validate user
if ($session->isValid($authsource)) {
    $attributes = $session->getAttributes();
    // Check if userid exists
    if (!isset($attributes[$useridattr]))
        throw new Exception('User ID is missing');
    $userid = $attributes[$useridattr][0];
} else {
    SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
}

// Backwards compatible function for checking urls
function check_url ($url) {
    if (version_compare(PHP_VERSION, '5.2.0', '>')) {
        return filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
    } else {
        // backport from PHP 5.2
        // http://svn.php.net/viewvc/php/php-src/trunk/ext/filter/logical_filters.c
        // see php_filter_validate_url function
        $parts = parse_url($url);
        if ($parts == FALSE) {
            return FALSE;
        } else if (!isset($parts['scheme']) ||
                  (!isset($parts['host']) &&
                  ($parts['scheme'] !== 'mailto' &&
                   $parts['scheme'] !== 'news' &&
                   $parts['scheme'] !== 'file'))) {
            return FALSE;
        }
        return TRUE;
    }
}

if ($session->isValid($authsource)) {
    $attributes = $session->getAttributes();
    // Check if userid exists
    if (!isset($attributes[$useridattr]))
        throw new Exception('User ID is missing');
    $userid = $attributes[$useridattr][0];
} else {
    SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
}

$mcontrol = new sspmod_janus_UserController($janus_config);
$pm = new sspmod_janus_Postman();

if(!$user = $mcontrol->setUser($userid)) {
    die('Error in setUser');
}

$selectedtab = isset($_REQUEST['selectedtab']) ? $_REQUEST['selectedtab'] : 1;

if(isset($_POST['add_usersubmit'])) {
    if(empty($_POST['userid']) || empty($_POST['type'])) {
        $msg = 'error_user_not_created_due_params';
    } else {
        $new_user = new sspmod_janus_User($janus_config->getValue('store'));
        $new_user->setUserid($_POST['userid']);
        $new_user->setType($_POST['type']);
        if(isset($_POST['active']) && $_POST['active'] == 'on') {
            $active = 'yes';
        } else {
            $active = 'no';
        }
        $new_user->setActive($active);
        $new_user->setData($_POST['userdata']);
        if(!$new_user->save()) {
            $msg = 'error_user_not_created';
        }
    }
}

if(isset($_POST['submit'])) {
    if (check_url($_POST['entityid'])) {
        if(!isset($_POST['entityid']) || empty($_POST['entitytype'])) {
            $msg = 'error_no_type';
            $old_entityid = $_POST['entityid'];
        } else {
            $msg = $mcontrol->createNewEntity($_POST['entityid'], $_POST['entitytype']);
            if(is_int($msg)) {
                $entity = new sspmod_janus_Entity($janus_config);
                $pm->subscribe($user->getUid(), 'ENTITYUPDATE-'. $msg);
                $pm->post(
                    'New entity created',
                    "A new entity has been created.<br />Entityid: ". $_POST['entityid']. "<br />Entity type: ".$_POST['entitytype'],
                    'ENTITYCREATE',
                    $user->getUid()
                );
                $msg = 'text_entity_created';
            }
        }
    } else {
        $msg = 'error_entity_not_url';
        $old_entityid = $_POST['entityid'];
    }
}

if(isset($_POST['usersubmit'])) {
    $user->setData($_POST['userdata']);
    $user->setEmail($_POST['user_email']);
    $user->save();
    $pm->post(
        'Userinfo update',
        'User info updated:<br /><br />' . $_POST['userdata'] . '<br /><br />E-mail: ' . $_POST['user_email'],
        'USER-' . $user->getUid(),
        $user->getUid());
}

$subscriptions = $pm->getSubscriptions($user->getUid());
$subscriptionList = $pm->getSubscriptionList();

if(isset($_GET['page'])) {
    $page = $_GET['page'];
    $messages = $pm->getMessages($user->getUid(), $page);
} else {
    $page = 1;
    $messages = $pm->getMessages($user->getUid());
}
$messages_total = $pm->countMessages($user->getUid());

$et = new SimpleSAML_XHTML_Template($config, 'janus:dashboard.php', 'janus:janus');
$et->data['header'] = 'JANUS';
$et->data['entities'] = $mcontrol->getEntities();
$et->data['userid'] = $userid;
$et->data['user'] = $mcontrol->getUser();
$et->data['uiguard'] = new sspmod_janus_UIguard($janus_config->getValue('access'));
$et->data['user_type'] = $user->getType();
$et->data['subscriptions'] = $subscriptions;
$et->data['subscriptionList'] = $subscriptionList;
$et->data['messages'] = $messages;
$et->data['messages_total'] = $messages_total;
$et->data['current_page'] = $page;
$et->data['last_page'] = ceil((float)$messages_total / $pm->getPaginationCount());
$et->data['selectedtab'] = $selectedtab;
$et->data['logouturl'] = SimpleSAML_Module::getModuleURL('core/authenticate.php') . '?logout';

$et->data['users'] = $mcontrol->getUsers();

if(isset($old_entityid)) {
    $et->data['old_entityid'] = $old_entityid;
}
if(isset($msg)) {
    $et->data['msg'] = $msg;
}

$et->show();
?>
