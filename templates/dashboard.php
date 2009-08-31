<?php
/**
 * Main template for JANUS.
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id: janus-main.php 11 2009-03-27 13:51:02Z jach@wayf.dk $
 */
//$this->data['header'] = 'JANUS';
$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/style.css" />' . "\n";
$this->data['head'] .= '<script type="text/javascript">
$(document).ready(function() {
				  	$("#tabdiv").tabs();
				  	$("#tabdiv").tabs("select", 1);
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
								$("tr#" + data.eid + " > td.users").append("<span id=\"" + data.eid + "-" + data.uid + "\">" + data.email + ", </span>");
								$("select#add-user-" + data.eid).hide(); 
							},
							"json"
						);
					});
});

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

			$("select.remove-user").change(function () {
				alert("tester");
				var str = "";
				$("select option:selected").each(function () {
					str += $(this).text() + " ";
				});
				$("div#tester").text(str);
			});

function deleteUser(uid, email) {
    if(confirm("Delete user: " + email)) {
        $.post(
               "AJAXRequestHandler.php", 
               {
                    func: "deleteUser", 
                    uid: uid	
                },
                function(data){
                    if(data.status == "success") {
                        //alert("User deleted");
                        $("#delete-user-" + uid).hide();
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
<h1><?php echo $this->t('text_dashboard').' for '. $this->data['user']->getEmail(); ?></h1>
<!-- TABS -->
<ul>
	<li><a href="#userdata"><?php echo $this->t('tab_user_data_header'); ?></a></li>
	<li><a href="#entities"><?php echo $this->t('tab_entities_header'); ?></a></li>
	<?php
	if($this->data['user_type'] === 'admin') {
		echo '<li><a href="#admin">', $this->t('tab_admin_header'), '</a></li>';
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
		
	if($this->data['uiguard']->hasPermission('createnewentity', $wfstate, $this->data['user']->getType(), TRUE)) {
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
            $enablematrix = $util->getAllowedTypes();
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
if(!$this->data['entities']) {
	$sps = array('None');
	$idps = array('None');
} else {
	$sps = array();
	$idps = array();

	foreach($this->data['entities'] AS $entity) {
		if($entity->getType() === 'saml20-sp') {
			$sps[] = '<a href="editentity.php?eid='.$entity->getEid().'">'. $entity->getEntityid() . '</a><br>';
		} else {
			$idps[] = '<a href="editentity.php?eid='.$entity->getEid().'">'. $entity->getEntityid() . '</a><br>';
		}
	}
}
?>
<table cellpadding="30" style="border-collapse: collapse; width: 100%;">
	<tr>
		<td style="border-bottom: 2px solid #000000; border-right: 2px solid #000000; padding: 4px; width: 50%;"><b><?php echo $this->t('text_service_table'); ?></b></td>
		<td style="border-bottom: 2px solid #000000; padding: 4px; width: 50%;"><b><?php echo $this->t('text_identity_table'); ?></b></td>
	</tr>
	<tr>
		<td valign="top" style="border-right: 2px solid #000000;">
		<?php
        $color='EEEEEE';
        echo '<table style="width: 100%;">';
		foreach($sps AS $sp) {
            $color = $color ^ 'EEEEEE';
			echo '<tr style="background-color: #'.$color.'"><td style="padding-left: 4px; padding-right: 4px;">'. $sp . '</td></tr>';
		}
        echo '</table>';
		?>
		</td>
		<td valign="top">
		<?php
        $color='EEEEEE';
        echo '<table style="width: 100%;">';
		foreach($idps AS $idp) {
            $color = $color ^ 'EEEEEE';
			echo '<tr style="background-color: #'.$color.'"><td style="padding-left: 4px; padding-right: 4px;">'. $idp . '</td></tr>';
		}
        echo '</table>';
		?>
		</td>
	</tr>
</table>
</div>

<!-- TAB - ADMIN -->
<?php
if($this->data['user_type'] === 'admin') {
?>
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
			echo '<thead><tr><th>'. $this->t('admin_type') .'</th><th>'. $this->t('admin_email') .'</th><th>'. $this->t('admin_action') .'</th></tr></thead>';
			echo '<tbody>';
			foreach($users AS $user) {
                $color = $color ^ 'EEEEEE';
				echo '<tr id="delete-user-', $user['uid'],'" style="background-color: #'. $color .';">';
				echo '<td style="padding: 3px;">', $user['type'], '</td>';
                echo '<td style="padding: 3px;">', $user['email']. '</td>';
                echo '<td style="padding: 3px;"><a class="janus_button" onClick="deleteUser(', $user['uid'], ', \'', $user['email'], '\');">'. $this->t('admin_delete') .'</a></td>';
				echo '</tr>';
			}
			echo '</tbody';
			echo '</table>';
		?>
		</div>

		<div id="admin_entities">
		<?php
			$entities = $util->getEntities();
		
			echo '<table style="border-collapse: collapse;">';
			echo '<thead><tr><th style="width: 40%;">'. $this->t('tab_admin_tab_entities_header') .'</th><th>'. $this->t('admin_users') .'</th><th style="width: 230px;">'. $this->t('admin_permission') .'</th></tr></thead>';
			echo '<tbody>';
            $color = 'EEEEEE';
			foreach($entities AS $entity) {
                $color = $color ^ 'EEEEEE';
				echo '<tr id="', $entity['eid'], '" style="background-color: #'. $color .'">';
				$entity_users = $util->hasAccess($entity['eid']);
				
				echo '<td style="padding: 3px;">', $entity['entityid'] , '</td>';
			   	echo '<td style="padding: 3px;" class="users">';
				foreach($entity_users AS $entity_user) {
					echo '<span id="', $entity['eid'],'-', $entity_user['uid'],'">',$entity_user['email'], ', </span>';
				}
				echo '</td>';
				echo '<td style="padding: 3px;">';
				echo '<a class="janus_button" onclick="getNonEntityUsers(\'', str_replace(array(':', '.', '#'), array('\\\\:', '\\\\.', '\\\\#'), $entity['eid']), '\');">'. $this->t('admin_add') .'</a> - ';
				echo '<a class="janus_button" onclick="getEntityUsers(\'', str_replace(array(':', '.', '#'), array('\\\\:', '\\\\.', '\\\\#'), $entity['eid']), '\');">'. $this->t('admin_remove') .'</a>';
                echo '<select class="add-user" id="add-user-', $entity['eid'], '" style="display:none"></select>';
				echo '<select class="remove-user" id="remove-user-', $entity['eid'], '" style="display:none"></select></td>';
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
<p><?php echo $this->t('tab_user_data_username');  ?>: <?php echo $this->data['user']->getEmail(); ?></p>
<!-- <p>Type: <?php echo $this->data['user']->getType(); ?></p> -->
<p><?php echo $this->t('tab_user_data_otherinfo');  ?>:</p>
<textarea name="userdata" cols="100" rows="10">
<?php
echo $this->data['user']->getData();
?>
</textarea>
<input type="submit" name="usersubmit" value="<?php echo $this->t('tab_edit_entity_save'); ?>">
</form>
</div>
<!-- TABS END - USERDATA -->

</div><!-- TABS DIV END -->

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
