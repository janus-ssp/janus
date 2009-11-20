<?php
/**
 * Main template for JANUS.
 *
 * @author     Jacob Christiansen, <jach@wayf.dk>
 * @author     Sixto Martín, <smartin@yaco.es>
 * @package    simpleSAMLphp
 * @subpackage JANUS
 * @version    $Id: janus-main.php 11 2009-03-27 13:51:02Z jach@wayf.dk $
 */
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');
$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/style.css" />' . "\n";
$this->data['head'] .= '<script type="text/javascript">
$(document).ready(function() {
    $("#tabdiv").tabs();
    $("#tabdiv").tabs("select", '. $this->data['selectedtab'] .');
    $("#admin_tabdiv").tabs();

    // Remove user function
    $("select.remove-user").change(function () {
        $.post(
            "AJAXRequestHandler.php",
            {
                func: "removeUserFromEntity",
                uid: $(this).val(),
                eid: this.id.substr(12)
            },
            function(data) {
                $("#" + data.eid + "-" + data.uid).remove();
                $("select#remove-user-" + data.eid).hide();
            },
            "json"
        );
    });

    // Add user function
    $("select.add-user").change(function () {
        $.post(
            "AJAXRequestHandler.php",
            {
                func: "addUserToEntity",
                uid: $(this).val(),
                eid: this.id.substr(9)
            },
            function(data) {
                $("tr#" + data.eid + " > td.users").append("<span id=\"" + data.eid + "-" + data.uid + "\">" + data.userid + ", </span>");
                $("select#add-user-" + data.eid).hide();
            },
            "json"
        );
    });

    $("#admin_add_user_link").click(function () {
          $("#admin_add_user").toggle("slow");
    });
});

function editUser(uid) {
    tr_editUser = $("#delete-user-" + uid);
    td_type = tr_editUser.children("[name=\'type\']");
    td_userid = tr_editUser.children("[name=\'userid\']");
    td_active = tr_editUser.children("[name=\'active\']");
    td_action = tr_editUser.children("[name=\'action\']");
    a_edit = td_action.children("[name=\'admin_edit\']");
    a_delete = td_action.children("[name=\'admin_delete\']");

    if (td_active.text() == "yes") {
        checkbox_active = "<input type=\"checkbox\" name=\"active\" checked=\"checked\">";
    } else {
        checkbox_active = "<input type=\"checkbox\" name=\"active\">";
    }
';
// This should be put into a asyncronous call instead
$usertypes = $janus_config->getValue('usertypes');

$select_type = '<select name="type">';
foreach($usertypes as $user_type) {
    $select_type .= '<option value="'.$user_type.'">'.$user_type.'</option>';
}
$select_type .= '</select>';

    $this->data['head'] .= '

    td_type.html($(\''.$select_type.'\').val(td_type.text()));
    td_userid.html($(\'<input name="userid">\').val(td_userid.text()));

    a_edit.hide();
    $("#<a name=\"admin_save\" class=\"janus_button\" onClick=\"saveUser("+uid+");\">'. $this->t('admin_save') .'</a>&nbsp;").insertBefore(a_delete);
    td_active.html($(checkbox_active));
}

function saveUser(uid) {
    tr_editUser = $("#delete-user-" + uid);
    type = tr_editUser.children("[name=\'type\']").children("[name=\'type\']").val();
    userid_input = tr_editUser.children("[name=\'userid\']").children("[name=\'userid\']");
    userid = userid_input.val();
    active = tr_editUser.children("[name=\'active\']").children("[name=\'active\']")[0].checked;

    if(active == true) {
        active = "yes";
    } else {
        active = "no";
    }

    $.post(
        "AJAXRequestHandler.php",
        {
            func: "editUser",
            uid: uid,
            type: type,
            userid: userid,
            active: active,
        },
        function(data){
            if(data.status == "success") {
                td_action = tr_editUser.children("[name=\'action\']");
                td_action.children("[name=\'admin_edit\']").show();
                td_action.children("[name=\'admin_save\']").remove();
                tr_editUser.children("[name=\'type\']").html(type);
                tr_editUser.children("[name=\'userid\']").html(userid);
                tr_editUser.children("[name=\'active\']").html(active);
            } else {
                userid_input = tr_editUser.children("[name=\'userid\']").children("[name=\'userid\']");
                userid_input.focus();
                userid_input.css("background-color", "#E94426");
            }
        },
        "json"
    );
}

function getEntityUsers(eid) {
    if($("select#remove-user-" + eid).is(":visible")) {
        $("select#remove-user-" + eid).hide();
    } else {
        $("select#add-user-" + eid).hide();
        $.post(
            "AJAXRequestHandler.php",
            {
                func: "getEntityUsers",
                eid: eid
            },
            function(data){
                if(data.status == "success") {
                    var options = "<option value=\"0\">-- '. $this->t('admin_select_remove_user') .' --</option>";
                    for (var i = 0; i < data.data.length; i++) {
                        options += "<option value=\"" + data.data[i].optionValue + "\">" + data.data[i].optionDisplay + "</option>";
                    }
                    $("select#remove-user-" + eid).html(options);
                    $("select#remove-user-" + eid).show();
                } else {
                    $("select#remove-user-" + eid).hide();
                }
            },
            "json"
        );
    }
}

function getNonEntityUsers(eid) {
    if($("select#add-user-" + eid).is(":visible")) {
        $("select#add-user-" + eid).hide();
    } else {
        $("select#remove-user-" + eid).hide();
        $.post(
            "AJAXRequestHandler.php",
            {
                func: "getNonEntityUsers",
                eid: eid
            },
            function(data){
                if(data.status == "success") {
                    var options = "<option value=\"0\">-- '. $this->t('admin_select_add_user') .' --</option>";
                    for (var i = 0; i < data.data.length; i++) {
                        options += "<option value=\"" + data.data[i].optionValue + "\">" + data.data[i].optionDisplay + "</option>";
                    }
                    $("select#add-user-" + eid).html(options);
                    $("select#add-user-" + eid).show();
                } else {
                    $("select#add-user-" + eid).hide();
                }
            },
            "json"
        );
    }
}

function deleteUser(uid, userid) {
    if(confirm("Delete user: " + userid)) {
        $.post(
            "AJAXRequestHandler.php",
            {
                func: "deleteUser",
                uid: uid
            },
            function(data){
                if(data.status == "success") {
                    $("#delete-user-" + uid).hide();
                }
            },
            "json"
        );
    }
}

function addSubscription(uid, subscription) {
    $.post(
        "AJAXRequestHandler.php",
        {
            func: "addSubscription",
            uid: uid,
            subscription: subscription
        },
        function(data) {
            if(data.status == "success") {
                $("#subscription_list").append("<div style=\"border-bottom: 1px solid #AAAAAA;\" id=\"subscription_list_" + subscription + "\">" + subscription + " - <a onclick=\"deleteSubscription(" + uid + ", \'" + subscription + "\');\">X</a></div>");
            }
        },
        "json"
    );
}

function deleteSubscription(uid, subscription) {
    $.post(
        "AJAXRequestHandler.php",
        {
            func: "deleteSubscription",
            uid: uid,
            subscription: subscription
        },
        function(data) {
            if(data.status == "success") {
                $("#subscription_list_" + subscription).remove();
            }
        },
        "json"
    );
}

function openMessage(mid) {
    if($("#message-"+mid).is(":visible")) {
        $("#message-"+mid).hide();
    } else {
        $.post(
            "AJAXRequestHandler.php",
            {
                func: "getMessage",
                mid: mid,
            },
            function(data) {
                if(data.status == "success") {
                    $("#message-"+mid).html(data.data);
                    $("#message-"+mid).show();
                    markRead(mid);
                }
            },
            "json"
        );
    }
}

function markRead(mid) {
    var success = false;
    $.post(
        "AJAXRequestHandler.php",
        {
            func: "markAsRead",
            mid: mid,
        },
        function(data) {
            if(data.status == "success") {
                $("#message-title-"+mid).css("font-weight", "normal");
            }
        },
        "json"
    );
}

function deleteEntity(eid, entityid) {
    if(confirm("Do you want to delete " + entityid)) {
        $.post(
            "AJAXRequestHandler.php",
            {
                func: "deleteEntity",
                eid: eid,
            },
            function(data) {
                if(data.status == "success") {
                    $("#" + eid).hide();
                    $("#" + eid + "-list").hide();
                }
            },
            "json"
        );
    }
}
</script>';
$this->includeAtTemplateBase('includes/header.php');
$util = new sspmod_janus_AdminUtil();
?>

<div id="tabdiv">
<h1><?php echo $this->t('text_dashboard').' for '. $this->data['user']->getUserid(); ?></h1>
<!-- TABS -->
<ul>
    <li><a href="#userdata"><?php echo $this->t('tab_user_data_header'); ?></a></li>
    <li><a href="#entities"><?php echo $this->t('tab_entities_header'); ?></a></li>
    <li><a href="#message"><?php echo $this->t('tab_message_header'); ?></a></li>
    <?php
    if($this->data['user_type'] === 'admin') {
        echo '<li><a href="#admin">', $this->t('tab_admin_header'), '</a></li>';
        echo '<li><a href="#federation">', $this->t('tab_federation_header'), '</a></li>';
    }
    ?>

</ul>
<!-- TABS END -->

<!-- TABS - ENTITIES -->
<div id="entities">
    <?php
        if(isset($this->data['msg']) && substr($this->data['msg'], 0, 5) === 'error') {
            echo '<div style="font-weight: bold; color: #FF0000;">'. $this->t('error_header').'</div>';
            echo '<p>'. $this->t($this->data['msg']) .'</p>';
        } else if(isset($this->data['msg'])) {
            echo '<p>'. $this->t($this->data['msg']) .'</p>';
        }


    $enablematrix = $util->getAllowedTypes();

    if($this->data['uiguard']->hasPermission('createnewentity', null, $this->data['user']->getType(), TRUE)) {
    ?>
    <h2><?php echo $this->t('tab_entities_new_entity_subheader'); ?></h2>
    <form method="post" action="">
        <table>
            <tr>
                <td>
        <input type="hidden" name="userid" value="<?php echo $this->data['userid']; ?>">
        <?php echo $this->t('tab_entities_new_entity_text'); ?>:
        </td>
        <td>
        <?php
            if (isset($this->data['old_entityid'])) {
                echo '<input type="text" name="entityid" value="'. $this->data['old_entityid'] .'">';
            } else {
                echo '<input type="text" name="entityid">';
            }
        ?>
        </td>
        <td>
<input class="janus_button" type="submit" name="submit" value="<?php echo $this->t('text_submit_button'); ?>">
        </td>
        </tr>
        <tr>
        <td></td>
        <td>
        <?php
            echo '<select name="entitytype"';
            echo '<option value="">' . $this->t('text_select_type') . '</option>';
            foreach ($enablematrix AS $typeid => $typedata) {
                if ($typedata['enable'] === true) {
                    echo '<option value="'. $typeid .'">'. $typedata['name'] .'</option>';
                }
            }
            echo '</select>';
        ?>
        </td>
        <td></td>
        </tr>
        </table>
    </form>
    <?php
        }
    ?>
    <h2><?php echo $this->t('tab_entities_entities_subheader'); ?></h2>
    <p><?php echo $this->t('text_entities_help'); ?></p>
    <!--<h2>List of entities</h2>-->
<?php
$connections = array();

foreach($enablematrix AS $typeid => $typedata) {
    if($typedata['enable'] === true) {
        $connections[$typeid] = array();
    }
}
$count_types = count($connections);
foreach($this->data['entities'] AS $entity) {
    $connections[$entity->getType()][] = $entity;
}

$theader = '';
$tfooter = '';

// Create table showing accessible entities
$theader .= '<tr>';
$tfooter .= '<tr>';
foreach($connections AS $ckey => $cval) {
    $theader.= '<td style="border-bottom: 1px solid #AAAAAA; border-right: 1px solid #AAAAAA; border-left: 1px solid #AAAAAA; padding: 4px; width: ' . (int) 100/$count_types . '%;"><b>' . $this->t('text_'.$ckey) . '</b></td>';

    $color = 'EEEEEE';
    $tfooter .= '<td valign="top" style="border-right: 1px solid #AAAAAA; border-left: 1px solid #AAAAAA;">';
    $tfooter .= '<table style="width: 100%;">';
    foreach($cval AS $sp) {
        $color = ($color == 'EEEEEE') ? 'FFFFFF' : 'EEEEEE';
        $tfooter .= '<tr id="'.$sp->getEid().'-list" style="background-color: #'.$color.'">';
        $tfooter .= '<td style="padding-left: 4px; padding-right: 4px;">';
        $tfooter .= '<a href="editentity.php?eid='.$sp->getEid().'">'. $sp->getEntityid() . '</a>';
        $tfooter .= '</td>';
        $tfooter .= '</tr>';
    }
    $tfooter .= '</table>';
    $tfooter .= '</td>';
}
$theader .= '</tr>';
$tfooter .= '</tr>';

// Show the table
echo '<table cellpadding="30" style="border-collapse: collapse; width: 100%;">';
echo $theader;
echo $tfooter;
echo '</table>';
?>

</div>

<!-- TAB - ADMIN -->
<?php
if($this->data['user_type'] === 'admin') {
?>
    <!-- TAB - FEDERATION -->
    <div id="federation">
    <?php
    echo '<h2>'.$this->t('tab_entities_federation_entity_subheader').'</h2>';
    echo '<a href="exportentities.php">'.$this->t('tab_entities_federation_entity_description').'</a>';
    ?>
    </div>


        <div id="admin">
        <div id="admin_tabdiv">
        <ul>
        <li><a href="#admin_users"><?php echo $this->t('tab_admin_tab_users_header'); ?></a></li>
        <li><a href="#admin_entities"><?php echo $this->t('tab_admin_tab_entities_header'); ?></a></li>
        </ul>
        <div id="admin_users">
        <?php
            $color = 'EEEEEE';
            $users = $this->data['users'];
            echo '<table style="border-collapse: collapse; width: 100%;">';
            echo '<thead><tr><th>'. $this->t('admin_type') .'</th><th>'. $this->t('admin_userid') .'</th><th>'. $this->t('admin_active') .'</th><th style="text-align: center;">'. $this->t('admin_action') .'</th></tr></thead>';
            echo '<tbody>';
            foreach($users AS $user) {
                $color = $color ^ 'EEEEEE';
                echo '<tr id="delete-user-', $user['uid'],'" style="background-color: #'. $color .';" >';
                echo '<td name="type" style="padding: 3px;">', $user['type'], '</td>';
                echo '<td name="userid" style="padding: 3px;">', $user['userid']. '</td>';
                echo '<td name="active" style="padding: 3px;">', $user['active']. '</td>';
                echo '<td name="action" style="padding: 3px; text-align: center;">';
                echo '<a name="admin_edit" class="janus_button" onClick="editUser(', $user['uid'], ');">'. $this->t('admin_edit') .'</a>';
                echo '  ';
                echo '<a name="admin_delete" class="janus_button" onClick="deleteUser(', $user['uid'], ', \'', $user['userid'], '\');">'. $this->t('admin_delete') .'</a>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody';
            echo '</table>';
            echo '<br><a id="admin_add_user_link" class="janus_button">'.$this->t('admin_add_user').'</a>';
        ?>
            <br />
            <br />
            <div id="admin_add_user" style="display: none;">
                <form id="admin_add_user_form" method="post" action="<?php echo SimpleSAML_Utilities::selfURLNoQuery(); ?>">
                    <?php echo $this->t('admin_type');  echo ': '.$select_type; ?>
                    <?php echo $this->t('admin_active'); ?>: <input type="checkbox" name="active" checked="checked"><br>
                    <?php echo $this->t('admin_userid'); ?>: <input type="text" name="userid" value="" size="20"><br>
                    <?php echo $this->t('tab_user_data_otherinfo');  ?>: <textarea name="userdata" cols="100" rows="3"></textarea><br>
                                        <input type="submit" name="add_usersubmit" value="<?php echo $this->t('tab_edit_entity_save'); ?>">
                </form>
            </div>
        </div>

        <div id="admin_entities">
        <?php
            $entities = $util->getEntities();

            echo '<table style="border-collapse: collapse;">';
            echo '<thead><tr><th style="width: 40%;">'. $this->t('tab_admin_tab_entities_header') .'</th><th>'. $this->t('admin_users') .'</th><th style="width: 230px;">'. $this->t('admin_permission') .'</th><th>' . $this->t('admin_action') . '</th></tr></thead>';
            echo '<tbody>';
            $color = 'EEEEEE';
            foreach($entities AS $entity) {
                $color = $color ^ 'EEEEEE';
                echo '<tr id="', $entity['eid'], '" style="background-color: #'. $color .'">';
                $entity_users = $util->hasAccess($entity['eid']);

                echo '<td style="padding: 3px;">', $entity['entityid'] , '</td>';
                echo '<td style="padding: 3px;" class="users">';
                foreach($entity_users AS $entity_user) {
                    echo '<span id="', $entity['eid'],'-', $entity_user['uid'],'">',$entity_user['userid'], ', </span>';
                }
                echo '</td>';
                echo '<td style="padding: 3px;">';
                echo '<a class="janus_button" onclick="getNonEntityUsers(\'', str_replace(array(':', '.', '#'), array('\\\\:', '\\\\.', '\\\\#'), $entity['eid']), '\');">'. $this->t('admin_add') .'</a>  ';
                echo '<a class="janus_button" onclick="getEntityUsers(\'', str_replace(array(':', '.', '#'), array('\\\\:', '\\\\.', '\\\\#'), $entity['eid']), '\');">'. $this->t('admin_remove') .'</a>';
                echo '<select class="add-user" id="add-user-', $entity['eid'], '" style="display:none"></select>';
                echo '<select class="remove-user" id="remove-user-', $entity['eid'], '" style="display:none"></select>';
                echo '</td>';
                echo '<td>';
                echo '<a class="janus_button" onclick="deleteEntity(\'', str_replace(array(':', '.', '#'), array('\\\\:', '\\\\.', '\\\\#'), $entity['eid']), '\', \'' . $entity['entityid'] . '\');">'. $this->t('admin_delete') .'</a>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody';
            echo '</table>';
        ?>
        </div>
    </div>
</div>
<?php
}
?>
<!-- TABS END - ADMIN -->

<!-- TABS - USERDATA -->
<div id="userdata">
    <form method="post" action="">
        <h2><?php echo $this->t('tab_user_data_subheader');  ?></h2>
        <p><?php echo $this->t('tab_user_data_username');  ?>: <?php echo $this->data['user']->getUserid(); ?></p>
        <p><?php echo $this->t('tab_user_data_email');  ?>: <input type="text" name="user_email" value="<?php echo $this->data['user']->getEmail(); ?>"></p>
        <!-- <p>Type: <?php echo $this->data['user']->getType(); ?></p> -->
        <p><?php echo $this->t('tab_user_data_otherinfo');  ?>:</p>
        <textarea name="userdata" cols="100" rows="10"><?php echo $this->data['user']->getData(); ?></textarea>
        <input type="submit" name="usersubmit" value="<?php echo $this->t('tab_edit_entity_save'); ?>">
    </form>
</div>
<!-- TABS END - USERDATE -->

<!-- TABS - INBOX -->
<div id="message">
    <table style="width: 100%;">
        <tr>
            <td style="width: 70%;" valign="top">
                <h2>Inbox</h2>
                <?php
                if(empty($this->data['messages'])) {
                    echo "Empty";
                } else {
                    foreach($this->data['messages'] AS $message) {
                        if($message['read'] == 'no') {
                            echo '<div style="border-bottom: 1px solid #AAAAAA;">';
                            echo '<a id="message-title-'. $message['mid'] .'" style="font-weight: bold;" onclick="openMessage('. $message['mid'] .')">'. $message['created'].' - '. $message['subject'] .'</a>';
                            echo '</div>';
                        } else {
                            echo '<div style="border-bottom: 1px solid #AAAAAA;">';
                            echo '<a id="message-title-'. $message['mid'] .'" onclick="openMessage('. $message['mid'] .')">'. $message['created'].' - '. $message['subject'] .'</a>';
                            echo '</div>';
                        }
                        echo '<div id="message-'. $message['mid'] .'" style="border-bottom: 1px solid #AAAAAA; border-right: 1px solid #AAAAAA; border-left: 1px solid #AAAAAA; display: none;"></div>';
                    }
                }
                ?>
            </td>
            <td style="width: 30%;" valign="top">
                <h2>Subscriptions</h2>
                <?php
                echo '<div id="subscription_list">';
                foreach($this->data['subscriptions'] AS $subscription) {
                    echo '<div style="border-bottom: 1px solid #AAAAAA;" id="subscription_list_' . $subscription['subscription'] . '">';
                    echo $subscription['subscription'];
                    echo ' - <a onclick="deleteSubscription(' . $this->data['user']->getUid() . ', \'' . $subscription['subscription'] . '\');">X</a>';
                    echo '</div>';
                }
                echo '</div>';

                if($this->data['user_type'] === 'admin') {
                    echo '<h2>Add subscriptions</h2>';
                    echo '<select name="subscriptions" id="subscriptions_select">';
                    echo '<option> -- select --</option>';
                    foreach($this->data['subscriptionList'] AS $subscription) {
                        echo '<option value="'. $subscription .'">' . $subscription . '</option>';
                    }
                    echo '</select>';
                 echo '<a class="janus_button" onclick="addSubscription(' . $this->data['user']->getUid() . ', $(\'select#subscriptions_select option:selected\').text());">Add</a>';
                }
                ?>
            </td>
        </tr>
    </table>
</div>
<!-- TABS END - INBOX -->

</div>
<!-- TABS DIV END -->

<p>[ <?php echo '<a href="' . htmlspecialchars($this->data['logouturl']) . '">' . $this->t('{status:logout}') . '</a>'; ?> ]</p>
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
