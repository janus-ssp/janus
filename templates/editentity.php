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
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/metaedit/resources/style.css" />' . "\n";
$this->data['head'] .= '<script type="text/javascript">
$(document).ready(function() {
	$("#tabdiv").tabs();
	$("#tabdiv").tabs("select", 0);
	$("#historycontainer").hide();
	$("#showhide").click(function() {
		$("#historycontainer").toggle("slow");
		return true;			
	});
});
</script>';

$this->includeAtTemplateBase('includes/header.php');
?>
<form id="mainform" method="post" action="<?php echo SimpleSAML_Utilities::selfURLNoQuery(); ?>">
<input type="hidden" name="entityid" value="<?php echo $this->data['entity']->getEntityid(); ?>">

<div id="tabdiv">
<h1><?php echo $this->t('edit_entity_header'); ?></h1>

<!-- TABS -->
<ul>
	<li><a href="#entity"><?php echo $this->t('tab_edit_entity_connection'); ?></a></li>
	<?php
	if($this->data['entity']->getType() === 'sp') {
		echo '<li><a href="#remoteentities">'. $this->t('tab_remote_entity_sp') .'</a></li>';
	} else {
		echo '<li><a href="#remoteentities">'. $this->t('tab_remote_entity_idp') .'</a></li>';
	}
	?>
	<li><a href="#metadata"><?php echo $this->t('tab_metadata'); ?></a></li>
	<!-- <li><a href="#attributes">Attributes</a></li> -->
	<li><a href="#addmetadata"><?php echo $this->t('tab_import_metadata'); ?></a></li>
	<li><a href="#history">History</a></li>
	<li><a href="#export">Export</a></li>
</ul>
<!-- TABS END -->

<div id="history">
	<?php
   		
	if(!$history = $this->data['mcontroller']->getHistory()) {
		echo "Not history fo entity ". $entityid. '<br /><br />';
	} else {
		if(count($history) > 10) {
			echo '<h2>History</h2>';
			echo '<a id="showhide">Show/Hide</a>';
			echo '<br /><br />';
		}
		$i = 0;
		$enddiv = FALSE;
		foreach($history AS $data) {
			if($i == 10) {
				echo '<div id="historycontainer">';
				$enddiv = TRUE;
			}
			echo '<a href="?entityid='. $data->getEntityid() .'&revisionid='. $data->getRevisionid().'">Revision '. $data->getRevisionid() .'</a><br>';
			$i++;
		}

		if($enddiv === TRUE) {
			echo '</div>';
		}
	}
?>
</div>

<div id="entity">
	<h2><?php echo $this->t('tab_edit_entity_connection') .' - '. $this->t('tab_edit_entity_connection_revision') .' '. $this->data['revisionid']; ?></h2>

	<?php
	if(isset($this->data['msg']) && substr($this->data['msg'], 0, 5) === 'error') {
		echo '<div style="font-weight: bold; color: #FF0000;">'. $this->t('error_header').'</div>';
		echo '<p>'. $this->t($this->data['msg']) .'</p>';
	} else if(isset($this->data['msg'])) {
		echo '<p>'. $this->t($this->data['msg']) .'</p>';	
	}
	?>

	<!-- Added to Software Børsen release -->
	<input type="hidden" name="entity_system" value="test" />
	<input type="hidden" name="entity_state" value="accepted" />
	<input type="hidden" name="entity_type" value="idp" />
	<table>
		<tr>
			<td><?php echo $this->t('tab_edit_entity_connection_entityid'); ?>:</td>
			<td><?php echo $this->data['entity']->getEntityid(); ?></td>
		</tr>
		<!--
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
		-->
		<tr>
			<td colspan="2"></td>
		</tr>
	</table>
</div>

<div id="remoteentities">
	<h2>Remote entities</h2>

	<?php
	if($this->data['entity']->getAllowedall() == 'yes') {
		$checked = 'checked';
	}
	?>
	<input type="hidden" name="entityid" value="<?php echo $this->data['entity']->getEntityid(); ?>">
	<input type="checkbox" name="allowedall" value="<?php echo $this->data['entity']->getAllowedall(); ?>" <?php echo $checked; ?>/> Allow all<hr>


	<?php
	foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {

		if(array_key_exists($remote_entityid, $this->data['blocked_entities'])) {
			echo '<input type="checkbox" name="delete[]" value="'. $remote_entityid. '" />&nbsp;&nbsp;'. $remote_data['name'] .' - BLOCKED<br />';
		} else {
			echo '<input type="checkbox" name="add[]" value="'. $remote_entityid. '" />&nbsp;&nbsp;'. $remote_data['name'] .'<br />';
		}
		echo '&nbsp;&nbsp;&nbsp;'. $remote_data['description'] .'<br />';	
	}
	?>
</div>

<div id="metadata">
	<h2>Metadata</h2>
	<table>
		<tr>
			<td>Key:</td>
			<td>
				<select name="meta_key">
					<option value="NULL">-- Vælg --</option>
					<?php
						foreach($this->data['metadata_select'] AS $metadata_val) {
							echo '<option value="', $metadata_val, '">', $metadata_val, '</option>';
						}
					?>	
				</select>
			</td>
		</tr>
		<!--
		<tr>
			<td>Key:</td>
			<td><input type="text" name="meta_key"></td>
		</tr>
		-->
		<tr>
			<td>Value:</td>
			<td><input type="text" name="meta_value"></td>
		</tr>
	</table>
	<br />
	<?php
	if(!$metadata = $this->data['mcontroller']->getMetadata()) {
		echo "Not metadata for entity ". $_GET['entityid']. '<br /><br />';
	} else {
		echo '<table border="0" style="width: 100%;">';
		foreach($metadata AS $data) {
			echo '<tr>';
			echo '<td width="1%">'. $data->getkey() . '</td>';
			echo '<td><input style="width: 100%;" type="text" name="edit-metadata-'. $data->getKey()  .'" value="'. $data->getValue()  .'"><input type="checkbox" style="display:none;" value="'. $data->getKey() .'" id="delete-matadata-'. $data->getKey() .'" name="delete-metadata[]"></td>';
			echo '<td width="80px;" align="right"><a onClick="javascript:if(confirm(\'Vil du slette metadata?\')){$(\'#delete-matadata-'. str_replace(array(':', '.', '#') , array('\\\\:', '\\\\.', '\\\\#'), $data->getKey()) .'\').attr(\'checked\', \'checked\');$(\'#mainform\').trigger(\'submit\');}">DELETE</a></td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	?>
</div>

<!--
<div id="attributes">
<h2>Attributes</h2>
	<table>
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
<?php

if(!$attributes = $this->data['mcontroller']->getAttributes()) {
	echo "Not attributes for entity ". $_GET['entityid']. '<br /><br />';
} else {
	foreach($attributes AS $data) {
		echo $data->getEntityid() .' - '. $data->getRevisionid().' - ' . $data->getkey() . ' - '. $data->getValue() .'<input type="text" name="edit-attribute-'. $data->getKey()  .'"><input type="checkbox" value="'. $data->getKey() .'" name="delete-attribute[]"><br>';
	}
}
?>
</div>
-->
<div id="addmetadata">
	<h2>Import XML</h2>
	<table>
		<tr>
			<td>XML:</td>
			<td><textarea name="meta_xml" cols="80" rows="20"></textarea></td>
		</tr>
	</table>
</div>

<div id="export">
<?php
echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/'. $this->data['entity']->getType() .'-metadata.php') .'?entityid='. $this->data['entity']->getEntityid()  .'&revisionid='. $this->data['entity']->getRevisionid() .'&output=xhtml">Export Metadata</a><br /><br />';
?>
</div>

<input type="submit" name="formsubmit" value="Update" />
<!-- END CONTENT -->
</div>

</form>

<?php
echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/index.php') .'">Dashboard</a><br /><br />';

$this->includeAtTemplateBase('includes/footer.php'); 
?>
