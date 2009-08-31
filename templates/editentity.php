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
	$("#allowall_check").change(function(){
		if($(this).is(":checked")) {
			$(".remote_check").each( function() {
				this.checked = false;
			});	
		}
	});
	$(".remote_check").change(function(){
		if($(this).is(":checked")) {
			$("#allowall_check").removeAttr("checked");
		}
	});
});
</script>';

$this->includeAtTemplateBase('includes/header.php');
$util = new sspmod_janus_AdminUtil();
$wfstate = $this->data['entity_state'];
?>
<form id="mainform" method="post" action="<?php echo SimpleSAML_Utilities::selfURLNoQuery(); ?>">
<input type="hidden" name="eid" value="<?php echo $this->data['entity']->getEid(); ?>">
<input type="hidden" name="revisionid" value="<?php echo $this->data['entity']->getRevisionid(); ?>">

<div id="tabdiv">
<a href="<?php echo SimpleSAML_Module::getModuleURL('janus/index.php'); ?>"><?php echo $this->t('text_dashboard'); ?></a>
<h2><?php echo $this->t('edit_entity_header'), ' - ', $this->data['entity']->getEntityid() . ' ('. $this->t('tab_edit_entity_connection_revision') . $this->data['entity']->getRevisionId() . ')'; ?></h2>

<!-- TABS -->
<ul>
	<li><a href="#entity"><?php echo $this->t('tab_edit_entity_connection'); ?></a></li>
	<?php
	if($this->data['entity']->getType() === 'saml20-sp') {
		echo '<li><a href="#remoteentities">'. $this->t('tab_remote_entity_saml20-sp') .'</a></li>';
	} else {
		echo '<li><a href="#remoteentities">'. $this->t('tab_remote_entity_saml20-idp') .'</a></li>';
	}
	?>
	<li><a href="#metadata"><?php echo $this->t('tab_metadata'); ?></a></li>
	<!-- <li><a href="#attributes">Attributes</a></li> -->
	<li><a href="#addmetadata"><?php echo $this->t('tab_import_metadata'); ?></a></li>
	<li><a href="#history"><?php echo $this->t('tab_edit_entity_history'); ?></a></li>
	<li><a href="#export"><?php echo $this->t('tab_edit_entity_export'); ?></a></li>
</ul>
<!-- TABS END -->

<div id="history">
	<?php
	if($this->data['uiguard']->hasPermission('entityhistory', $wfstate, $this->data['user']->getType())) {
   		
	if(!$history = $this->data['mcontroller']->getHistory()) {
		echo "Not history fo entity ". $this->data['entity']->getEntityId() . '<br /><br />';
	} else {
		if(count($history) > 10) {
			echo '<h2>'. $this->t('tab_edit_entity_history') .'</h2>';
			echo '<a id="showhide">'. $this->t('tab_edit_entity_show_hide') .'</a>';
			echo '<br /><br />';
		}
		$i = 0;
		$enddiv = FALSE;
		foreach($history AS $data) {
			if($i == 10) {
				echo '<div id="historycontainer">';
				$enddiv = TRUE;
			}
			echo '<a href="?eid='. $data->getEid() .'&revisionid='. $data->getRevisionid().'">'. $this->t('tab_edit_entity_connection_revision') .' '. $data->getRevisionid() .'</a>';
            if (strlen($data->getRevisionnote()) > 80) {
                echo ' - '. substr($data->getRevisionnote(), 0, 79) . '...';
            } else {
                echo ' - '. $data->getRevisionnote();
            }
            echo '<br>';
			$i++;
		}

		if($enddiv === TRUE) {
			echo '</div>';
		}
	}
	} else {
		echo $this->t('error_no_access');
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

	<table>
		<tr>
			<td><?php echo $this->t('tab_edit_entity_connection_entityid'); ?>:</td>
			<td><?php echo $this->data['entity']->getEntityid(); ?></td>
		</tr>
        <tr>
            <td><?php echo $this->t('tab_edit_entity_revision_note'); ?></td>
            <td><?php echo $this->data['entity']->getRevisionnote(); ?></td>
        </tr>
        <tr>
            <td><?php echo $this->t('tab_edit_entity_parent_revision'); ?></td>
			<td><?php 
            if ($this->data['entity']->getParent() === null) {
                echo 'No parent';
            } else {
                echo '<a href="?eid='. $this->data['entity']->getEid() .'&revisionid='. $this->data['entity']->getParent().'">r'. $this->data['entity']->getParent() .'</a>'; 
            }
            ?></td>
        </tr>
		<tr>
			<td><?php echo $this->t('tab_edit_entity_state'); ?>:</td>
			<td>
			<?php
				if($this->data['uiguard']->hasPermission('changeworkflow', $wfstate, $this->data['user']->getType())) {
				?>
				<select name="entity_workflow">
				<?php
				foreach($this->data['workflow'] AS $wf) {
					if($wfstate == $wf) {
						echo '<option value="'. $wf .'" selected="selected">'. $this->data['workflowstates'][$wf]['name'] .'</option>';
					} else {
						echo '<option value="'. $wf .'">'. $this->data['workflowstates'][$wf]['name'] .'</option>';
					}
				}
				?>
				</select>
				<?php
				} else {
					echo '<input type="hidden" name="entity_workflow" value="'. $wfstate .'">';
					echo $this->data['workflowstates'][$wfstate]['name'];
				
				}
				?>

			</td>
		</tr>
		<tr>
			<td><?php echo $this->t('admin_type'); ?>:</td>
            <td>
            <?php
            if($this->data['uiguard']->hasPermission('changeentitytype', $wfstate, $this->data['user']->getType())) {
                $enablematrix = $util->getAllowedTypes();
                echo '<select name="entity_type"';
                foreach ($enablematrix AS $typeid => $typedata) {
                    if ($typedata['enable'] === true) {
                        if($this->data['entity_type'] == $typeid) {
                            echo '<option value="'. $typeid .'" selected="selected">'. $typedata['name'] .'</option>';
                        } else {
                            echo '<option value="'. $typeid .'">'. $typedata['name'] .'</option>';
                        }
                    }
                }
                echo '</select>';
            } else {
                echo $this->data['entity_type'];
                echo '<input type="hidden" name="entity_type" value ="' . $this->data['entity_type'] . '">';
            }
            ?>
                    </td>
                    </tr>
                    <tr>
                    <td colspan="2"></td>
                    </tr>
                    </table>
                    </div>

                    <div id="remoteentities">
                    <h2><?php echo $this->t('tab_remote_entity_'. $this->data['entity']->getType()); ?></h2>
                    <p><?php echo $this->t('tab_remote_entity_help_'. $this->data['entity']->getType()); ?></p>
	<?php
	$checked = '';
	if($this->data['entity']->getAllowedall() == 'yes') {
		$checked = 'checked';
	}
	
	if($this->data['uiguard']->hasPermission('blockremoteentity', $wfstate, $this->data['user']->getType())) {
		// Access granted to block remote entities
		echo '<input id="allowall_check" type="checkbox" name="allowedall" value="' . $this->data['entity']->getAllowedall() . '" ' . $checked . ' > ' . $this->t('tab_remote_entity_allowall');
		echo '<hr>';

		foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {
			if(array_key_exists($remote_entityid, $this->data['blocked_entities'])) {
				echo '<input class="remote_check" type="checkbox" name="add[]" value="'. $remote_entityid. '" checked />&nbsp;&nbsp;'. $remote_data['name'] .'<br />';
			} else {
				echo '<input class="remote_check" type="checkbox" name="add[]" value="'. $remote_entityid. '" />&nbsp;&nbsp;'. $remote_data['name'] .'<br />';
			}
			echo '&nbsp;&nbsp;&nbsp;'. $remote_data['description'] .'<br />';	
		}
	} else {
		// Access not granted to block remote entities
		if($checked == 'checked') {
			echo '<input id="allowall_check" type="hidden" name="allowedall" value="' . $this->data['entity']->getAllowedall() . '" '. $checked . '>';
		}
		echo '<input type="checkbox" name="allowedall_dummy" value="' . $this->data['entity']->getAllowedall() . '" ' . $checked . ' disabled="disabled"> ' . $this->t('tab_remote_entity_allowall') . '<hr>';
		
		foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {
			if(array_key_exists($remote_entityid, $this->data['blocked_entities'])) {
				echo '<input class="remote_check" type="hidden" name="add[]" value="'. $remote_entityid. '" />';
				echo '<input class="remote_check" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" checked disabled="disabled" />&nbsp;&nbsp;'. $remote_data['name'] .'<br />';
			} else {
				echo '<input class="remote_check" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" disabled />&nbsp;&nbsp;'. $remote_data['name'] .'<br />';
			}
			echo '&nbsp;&nbsp;&nbsp;'. $remote_data['description'] .'<br />';	
		}
	}
	?>
</div>

<div id="metadata">
	<h2>Metadata</h2>
	<?php
	if($this->data['uiguard']->hasPermission('addmetadata', $wfstate, $this->data['user']->getType())) {
	?>
	<table>
		<tr>
			<td><?php echo $this->t('tab_edit_entity_entry'); ?>:</td>
			<td>
				<select name="meta_key">
					<option value="NULL">-- <?php echo $this->t('tab_edit_entity_select'); ?> --</option>
					<?php
						foreach($this->data['metadata_select'] AS $metadata_val) {
							echo '<option value="', $metadata_val, '">', $metadata_val, '</option>';
						}
					?>	
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $this->t('tab_edit_entity_value'); ?>:</td>
			<td><input type="text" name="meta_value"></td>
		</tr>
	</table>
	<br />
	<?php
	}
	?>

	<?php
	$deletemetadata = FALSE;
	if($this->data['uiguard']->hasPermission('deletemetadata', $wfstate, $this->data['user']->getType())) {
		$deletemetadata = TRUE;
	}
	$modifymetadata = 'readonly="readonly"';
	if($this->data['uiguard']->hasPermission('modifymetadata', $wfstate, $this->data['user']->getType())) {
		$modifymetadata = '';
	}
		
		
	if(!$metadata = $this->data['mcontroller']->getMetadata()) {
		echo "Not metadata for entity ". $this->data['entity']->getEntityId() . '<br /><br />';
	} else {
        $color = 'EEEEEE';
		echo '<table border="0" style="width: 100%;">';
		foreach($metadata AS $data) {
            $color = $color ^ 'EEEEEE';
			echo '<tr style="background-color: #'. $color.';">';
			echo '<td width="1%">'. $data->getkey() . '</td>';
			echo '<td>';
			echo '<input style="width: 100%;" type="text" name="edit-metadata-'. $data->getKey()  .'" value="'. $data->getValue()  .'" ' . $modifymetadata . '>';
			echo '<input type="checkbox" style="display:none;" value="'. $data->getKey() .'" id="delete-matadata-'. $data->getKey() .'" name="delete-metadata[]" >';
			echo '</td>';
			if($deletemetadata) {
				echo '<td width="80px;" align="right"><a onClick="javascript:if(confirm(\'Vil du slette metadata?\')){$(\'#delete-matadata-'. str_replace(array(':', '.', '#') , array('\\\\:', '\\\\.', '\\\\#'), $data->getKey()) .'\').attr(\'checked\', \'checked\');$(\'#mainform\').trigger(\'submit\');}">'. strtoupper($this->t('admin_delete')) .'</a></td>';
			}
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
	echo "Not attributes for entity ". $this->data['entity']->getEntityid() . '<br /><br />';
} else {
	foreach($attributes AS $data) {
		echo $data->getEntityid() .' - '. $data->getRevisionid().' - ' . $data->getkey() . ' - '. $data->getValue() .'<input type="text" name="edit-attribute-'. $data->getKey()  .'"><input type="checkbox" value="'. $data->getKey() .'" name="delete-attribute[]"><br>';
	}
}
?>
</div>
-->
<div id="addmetadata">
	<h2><?php echo $this->t('tab_edit_entity_import_xml'); ?></h2>
	<?php
	if($this->data['uiguard']->hasPermission('importmetadata', $wfstate, $this->data['user']->getType())) {
	?>
	<table>
		<tr>
            <td>XML:</td>
			<td><textarea name="meta_xml" cols="80" rows="20"></textarea></td>
		</tr>
	</table>
	<?php
	} else {
		echo 'You do not have permission to impoort metadata.';
	}
	?>
</div>

<!-- EXPORT TAB -->
<div id="export">
<?php
if($this->data['uiguard']->hasPermission('exportmetadata', $wfstate, $this->data['user']->getType())) {
	echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/'. $this->data['entity']->getType() .'-metadata.php') .'?eid='. $this->data['entity']->getEid()  .'&revisionid='. $this->data['entity']->getRevisionid() .'&output=xhtml">'. $this->t('tab_edit_entity_export_metadata') .'</a><br /><br />';
} else {
    echo $this->t('error_no_access');
}
?>
</div>
<hr>
<?php echo $this->t('tab_edit_entity_revision_note'); ?>: <input type="text" name="revisionnote" style="width: 700px;" />
<input type="submit" name="formsubmit" value="<?php echo $this->t('tab_edit_entity_save'); ?>" style="float: right;"/>
<!-- END CONTENT -->
</div>

</form>

<?php

$this->includeAtTemplateBase('includes/footer.php'); 
?>
