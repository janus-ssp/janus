<?php
/**
 * Main template for JANUS.
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id: janus-main.php 11 2009-03-27 13:51:02Z jach@wayf.dk $
 */
$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);

$this->data['head'] = '
<script type="text/javascript">
	$(document).ready(function() {
		$("#history").hide();
		$("#showhide").click(function() {
			$("#history").toggle("slow");
			return true;			
		});
	});
</script>
';

$this->includeAtTemplateBase('includes/header.php');
?>
<div id="content">
<h1>Edit entity</h1>
<h2>History</h2>
<a id="showhide">Show/Hide</a>
<div id="history">

<?php 
if(!$history = $this->data['mcontroller']->getHistory()) {
	echo "Not history fo entity ". $entityid. '<br /><br />';
} else {
	foreach($history AS $data) {
		echo '<a href="?entityid='. $data->getEntityid() .'&revisionid='. $data->getRevisionid().'">'. $data->getEntityid() .' - '. $data->getRevisionid() .'</a><br>';
	}
}
?>
</div>

<h2>Entity - Revision <?php echo $this->data['revisionid']; ?></h2>

<form method="post" action="<?php echo SimpleSAML_Utilities::selfURLNoQuery(); ?>">
	<input type="hidden" name="entityid" value="<?php echo $this->data['entity']->getEntityid(); ?>">
	<table>
		<tr>
			<td>Entityid:</td>
			<td><?php echo $this->data['entityid']; ?></td>
		</tr>
		<tr>
			<td>System:</td>
			<td>
				<select name="entity_system">
				<?php
				foreach($this->data['systems'] AS $system) {
					if($this->data['entity_system'] == $system) {
						echo '<option value="'. $system .'" selected="selected">'. $system .'</option>';
					} else {
						echo '<option value="'. $system .'">'. $system .'</option>';
					}
				}
				?>
				</select>		
			</td>
		</tr>
		<tr>
			<td>State:</td>
			<td>
				<select name="entity_state">
				<?php
				foreach($this->data['states'] AS $state) {
					if($this->data['entity_state'] == $state) {
						echo '<option value="'. $state .'" selected="selected">'. $state .'</option>';
					} else {
						echo '<option value="'. $state .'">'. $state .'</option>';
					}
				}
				?>
				</select>		
			</td>
		</tr>
		<tr>
			<td>Type:</td>
			<td>
				<select name="entity_type">
				<?php
				foreach($this->data['types'] AS $type) {
					if($this->data['entity_type'] == $type) {
						echo '<option value="'. $type .'" selected="selected">'. $type .'</option>';
					} else {
						echo '<option value="'. $type .'">'. $type .'</option>';
					}
				}
				?>
				</select>		
			</td>
		</tr>
		<tr>
			<td colspan="2"></td>
		</tr>
	</table>

<h2>Remote entities</h2>

	<?php
	if($this->data['entity']->getAllowedall() == 'yes') {
		$checked = 'checked';
	}
	?>
	<input type="hidden" name="entityid" value="<?php echo $this->data['entity']->getEntityid(); ?>">
	Allowed all: <input type="checkbox" name="allowedall" value="<?php echo $this->data['entity']->getAllowedall(); ?>" <?php echo $checked; ?>/><br />


<?php
foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {

	if(array_key_exists($remote_entityid, $this->data['blocked_entities'])) {
		echo $remote_entityid .' - BLOCKED - <input type="checkbox" name="delete[]" value="'. $remote_entityid. '" /><br />';
	} else {
		echo $remote_entityid .'<input type="checkbox" name="add[]" value="'. $remote_entityid. '" /><br />';
	}	
}
?>

<h2>Metadata</h2>
<?php
if(!$metadata = $this->data['mcontroller']->getMetadata()) {
	echo "Not metadata for entity ". $_GET['entityid']. '<br /><br />';
} else {
	foreach($metadata AS $data) {
		echo $data->getEntityid() .' - '. $data->getRevisionid().' - ' . $data->getkey() . ' - '. $data->getValue() .'<input type="text" name="edit-metadata-'. $data->getKey()  .'"><input type="checkbox" value="'. $data->getKey() .'" name="delete-metadata[]"><br>';
	}
}
?>

<h2>Attributes</h2>
<?php

if(!$attributes = $this->data['mcontroller']->getAttributes()) {
	echo "Not attributes for entity ". $_GET['entityid']. '<br /><br />';
} else {
	foreach($attributes AS $data) {
		echo $data->getEntityid() .' - '. $data->getRevisionid().' - ' . $data->getkey() . ' - '. $data->getValue() .'<input type="text" name="edit-attribute-'. $data->getKey()  .'"><input type="checkbox" value="'. $data->getKey() .'" name="delete-attribute[]"><br>';
	}
}
?>
<h2>Add metadata</h2>
	<table>
		<tr>
			<td>Key:</td>
			<td><input type="text" name="meta_key"></td>
		</tr>
		<tr>
			<td>Value:</td>
			<td><input type="text" name="meta_value"></td>
		</tr>
		<tr>
			<td>XML:</td>
			<td><textarea name="meta_xml" cols="80" rows="20"></textarea></td>
		</tr>
		<tr>
			<td colspan="2">Attribute:</td>
		</tr>
		<tr>
			<td>Key:</td>
			<td><input type="text" name="att_key"></td>
		</tr>
		<tr>
			<td>Value:</td>
			<td><input type="text" name="att_value"></td>
		</tr>
	</table>
	<input type="submit" name="submit" value="Update" />
</form>
<!-- END CONTENT -->
</div>
<?php
echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/'. $this->data['entity']->getType() .'-metadata.php') .'?entityid='. $this->data['entity']->getEntityid()  .'&revisionid='. $this->data['entity']->getRevisionid() .'&output=xhtml">Export Metadata</a><br /><br />';
echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/index.php') .'">Frontpage</a><br /><br />';

$this->includeAtTemplateBase('includes/footer.php'); 
?>
