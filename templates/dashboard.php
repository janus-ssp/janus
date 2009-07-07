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
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/metaedit/resources/style.css" />' . "\n";
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
								entityid: this.id.substr(12)
							},
							function(data) {
								$("#" + data.entityid + "-" + data.uid).remove();
								$("select#remove-user-" + data.entityid).hide(); 
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
								entityid: this.id.substr(9)
							},
							function(data) {
								$("tr#" + data.entityid + " > td.users").append("<span id=\"" + data.entityid + "-" + data.uid + "\">" + data.email + ", </span>");
								$("select#add-user-" + data.entityid).hide(); 
							},
							"json"
						);
					});
});
</script>';

$this->includeAtTemplateBase('includes/header.php');
?>
			<script type="text/javascript">
			function getEntityUsers(entityid) {
				if($("select#remove-user-" + entityid).is(':visible')) {
					$("select#remove-user-" + entityid).hide();		
				} else {		
					$("select#add-user-" + entityid).hide();		
				$.post(
						"AJAXRequestHandler.php", 
						{
							func: "getEntityUsers", 
							entityid: entityid	
						},
						function(data){
							if(data.status == 'success') {
							    var options = '<option value="0">-- Select user to remove --</option>';
								for (var i = 0; i < data.data.length; i++) {
							        options += '<option value="' + data.data[i].optionValue + '">' + data.data[i].optionDisplay + '</option>';
								}
								$("select#remove-user-" + entityid).html(options);
								$("select#remove-user-" + entityid).show();
							} else {
								$("select#remove-user-" + entityid).hide();		
							}
						}, 
						"json"
					);
				}
			}
			function getNonEntityUsers(entityid) {
				if($("select#add-user-" + entityid).is(':visible')) {
					$("select#add-user-" + entityid).hide();		
				} else {		
					$("select#remove-user-" + entityid).hide();		
				$.post(
						"AJAXRequestHandler.php", 
						{
							func: "getNonEntityUsers", 
							entityid: entityid	
						},
						function(data){
							if(data.status == 'success') {
							    var options = '<option value="0">-- Select user to add --</option>';
								for (var i = 0; i < data.data.length; i++) {
							        options += '<option value="' + data.data[i].optionValue + '">' + data.data[i].optionDisplay + '</option>';
								}
								$("select#add-user-" + entityid).html(options);
								$("select#add-user-" + entityid).show();
							} else {
								$("select#add-user-" + entityid).hide();		
							}
						}, 
						"json"
					);
				}
			}

			$("select.remove-user").change(function () {
				alert('tester');
				var str = "";
				$("select option:selected").each(function () {
					str += $(this).text() + " ";
				});
				$("div#tester").text(str);
			});
		</script>
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
	?>
	<h2><?php echo $this->t('tab_entities_new_entity_subheader'); ?></h2>
	<form method="post" action="">
		<input type="hidden" name="userid" value="<?php echo $this->data['userid']; ?>">
		<?php echo $this->t('tab_entities_new_entity_text'); ?>: <input type="text" name="entityid">&nbsp;&nbsp;<input type="submit" name="submit" value="<?php echo $this->t('text_submit_button'); ?>"><br/>
	</form>
	
	<h2><?php echo $this->t('tab_entities_entities_subheader'); ?></h2>
	<p><?php echo $this->t('text_entities_help'); ?></p>
	<!--<h2>List of entities</h2>-->
<?php
if(!$this->data['entities']) {
	$sps = array('Non');
	$idps = array('Non');
} else {
	$sps = array();
	$idps = array();

	foreach($this->data['entities'] AS $entity) {
		if($entity->getType() === 'sp') {
			$sps[] = '<a href="editentity.php?entityid='.$entity->getEntityid().'">'. $entity->getEntityid() . '</a><br>';
		} else {
			$idps[] = '<a href="editentity.php?entityid='.$entity->getEntityid().'">'. $entity->getEntityid() . '</a><br>';
		}
	}
}
?>
<table cellpadding="30" style="border-collapse: collapse;">
	<tr>
		<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000; padding: 4px;"><?php echo $this->t('text_service_table'); ?></td>
		<td style="border-bottom: 1px solid #000000; padding: 4px;"><?php echo $this->t('text_identity_table'); ?></td>
	</tr>
	<tr>
		<td valign="top" style="border-right: 1px solid #000000; padding-left: 4px;">
		<?php
		foreach($sps AS $sp) {
			echo $sp;
		}
		?>
		</td>
		<td valign="top" style="padding-left: 4px;">
		<?php
		foreach($idps AS $idp) {
			echo $idp;
		}
		?>
		</td>
	</tr>
</table>
</div>

<?php
if($this->data['user_type'] === 'admin') {
?>
<div id="admin">
	<script type="text/javascript">
	function deleteUser(uid, email) {
		if(confirm("Delete user: " + email)) {
			$.post(
				"AJAXRequestHandler.php", 
				{
					func: "deleteUser", 
					uid: uid	
				},
				function(data){
					if(data.status == 'success') {
						alert("User deleted");
						$("#delete-user-" + uid).hide();
					}
				}, 
				"json"
			);
		}
	}
	</script>
	<div id="admin_tabdiv">
		<ul>
			<li><a href="#admin_users"><?php echo $this->t('tab_admin_tab_users_header'); ?></a></li>
			<li><a href="#admin_entities"><?php echo $this->t('tab_admin_tab_entities_header'); ?></a></li>
		</ul>
		<div id="admin_users">
		<?php
			$users = $this->data['users'];
			echo '<table border="0" cellspacing="10">';
			echo '<thead><tr><th>Type</th><th>E-mail</th><th>Action</th></tr></thead>';
			echo '<tbody>';
			foreach($users AS $user) {
				echo '<tr id="delete-user-', $user['uid'],'">';
				echo '<td>', $user['type'], '</td><td>', $user['email']. '</td><td><a onClick="deleteUser(', $user['uid'], ', \'', $user['email'], '\');">DELETE</a></td>';
				echo '</tr>';
			}
			echo '</tbody';
			echo '</table>';
		?>
		</div>

		<div id="admin_entities">
		<?php
			$util = new sspmod_janus_AdminUtil();
			$entities = $util->getEntities();
		
			echo '<table border="0" cellspacing="10">';
			echo '<thead><tr><th>ID</th><th>Last update</th><th>Users</th><th>Action</th></tr></thead>';
			echo '<tbody>';
			foreach($entities AS $entity) {
				echo '<tr id="', $entity['entityid'], '">';
				$entity_users = $util->hasAccess($entity['entityid']);
				
				echo '<td>', $entity['entityid'] , '</td>';
				echo '<td>', $entity['created'] , '</td>';
			   	echo '<td class="users">';
				foreach($entity_users AS $entity_user) {
					echo '<span id="', $entity['entityid'],'-', $entity_user['uid'],'">',$entity_user['email'], ', </span>';
				}
				echo '</td>';
				echo '<td>';
				echo '<a onclick="getNonEntityUsers(\'', $entity['entityid'], '\');">Add</a> - ';
				echo '<a onclick="getEntityUsers(\'', $entity['entityid'], '\');">Remove</a>';
				echo '</td>';
				echo '<td>';
				echo '<select class="add-user" id="add-user-', $entity['entityid'], '" style="display:none"></select>';
				echo '<select class="remove-user" id="remove-user-', $entity['entityid'], '" style="display:none"></select></td>';
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
<!-- TABS END - ENTITIES -->


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
<input type="submit" name="usersubmit" value="save">
</form>
</div>
<!-- TABS END - USERDATA -->

</div><!-- TABS DIV END -->

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
