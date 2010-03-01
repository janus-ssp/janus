<?php
/**
 * Main template for JANUS.
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @author Sixto Martín, <smartin@yaco.es>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id: janus-main.php 11 2009-03-27 13:51:02Z jach@wayf.dk $
 */
$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/style.css" />' . "\n";
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
    $("#entity_workflow_select").change(function () {
        var tmp;
        $("#entity_workflow_select option").each(function () {
            tmp = $(this).val();
            $("#wf-desc-" + tmp).hide();
        });
        var id = $("#entity_workflow_select option:selected").attr("value");
        $("#wf-desc-"+id).show();
    });

    $(":input").bind("change", function(e) {
        blinker(5);
    });
});


function blinker(x) {
    // Set the color the field should blink in
    var backgroundColor = \'#FF0000\';
    var existingBgColor;

    // Load the current background color
    existingBgColor = $("#master_submit").css(\'background-color\');

    // Set the new background color
    $("#master_submit").css(\'background-color\', backgroundColor);

    if(x == 0) {
        return;
    }

    // Set it back to old color after 500 ms
    setTimeout(
        function() {
            //$("#master_submit").css(\'background-color\', existingBgColor);
            $("#master_submit").css(\'background-color\', \'\');
        },
        500
    );

    var y = x-1;
    setTimeout("blinker(" + y + ");", 1000);
}

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
<h2><?php echo $this->t('edit_entity_header'), ' - ', $this->data['entity']->getEntityid() . ' ('. $this->t('tab_edit_entity_connection_revision') .' '. $this->data['entity']->getRevisionId() . ')'; ?></h2>

<!-- TABS -->
<ul>
    <li><a href="#entity"><?php echo $this->t('tab_edit_entity_connection'); ?></a></li>
    <?php
    if($this->data['entity']->getType() === 'saml20-sp') {
        echo '<li><a href="#remoteentities">'. $this->t('tab_remote_entity_saml20-sp') .'</a></li>';
    } else {
        echo '<li><a href="#remoteentities">'. $this->t('tab_remote_entity_saml20-idp') .'</a></li>';
    }
    if($this->data['entity']->getType() === 'saml20-idp') {
        echo '<li><a href="#disableconsent">' . $this->t('tab_disable_consent') . '</a></li>';
    }
    ?>
    <li><a href="#metadata"><?php echo $this->t('tab_metadata'); ?></a></li>
    <?php
    /*
    if($this->data['entity']->getType() == 'saml20-sp' || $this->data['entity']->getType() == 'shib13-sp') {
    ?>
    <li><a href="#attributes">Attributes</a></li>
    <?php
    }
    */
    ?>
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
<!-- ENTITY CONNECTION -->
<div id="entity">
    <h2><?php echo $this->t('tab_edit_entity_connection') .' - '. $this->t('tab_edit_entity_connection_revision') .' '. $this->data['revisionid']; ?></h2>

    <table>
        <tr>
            <td>
    <?php
    if(isset($this->data['msg']) && substr($this->data['msg'], 0, 5) === 'error') {
        echo '<div class="editentity_error">'. $this->t('error_header').'</div>';
        echo '<p>'. $this->t($this->data['msg']) .'</p>';
    } else if(isset($this->data['msg'])) {
        echo '<p>'. $this->t($this->data['msg']) .'</p>';
    }
    ?>

    <table>
        <tr>
            <td class="entity_top_data"><?php echo $this->t('tab_edit_entity_connection_entityid'); ?>:</td>
            <td><?php echo $this->data['entity']->getEntityid(); ?></td>
        </tr>
        <tr>
            <td><?php echo $this->t('tab_edit_entity_connection_metadataurl'); ?>:</td>
            <td><?php echo $this->data['entity']->getMetadataURL(); ?></td>
        </tr>
        <?php
        if($this->data['entity']->getType() == 'saml20-sp' || $this->data['entity']->getType() == 'shib13-sp') {
        ?>
        <tr>
            <td><?php echo $this->t('tab_edit_entity_connection_arp'); ?>:</td>
            <td>
            <?php    
            $current_arp = $this->data['entity']->getArp();
            if($this->data['uiguard']->hasPermission('changearp', $wfstate, $this->data['user']->getType())) {
            ?>
                <select id="entity_arp_select" name="entity_arp">
            <?php
            foreach($this->data['arp_list'] AS $arp) {
                if($current_arp == $arp['aid']) {
                    echo '<option value="'. $arp['aid'] .'" selected="selected">'. $arp['name'] .'</option>';
                } else {
                    echo '<option value="'. $arp['aid'] .'">'. $arp['name'] .'</option>';
                }
            }
            ?>
            </select>
            <?php 
            } else {
                echo '<input type="hidden" name="entity_arp" value="'. $current_arp .'">';
                foreach($this->data['arp_list'] AS $arp) {
                    if($current_arp == $arp['aid']) {
                        echo $arp['name'];
                    }
                }
            }
            ?>
        </tr>
        <?php
        }
        ?>
        <tr>
            <td class="entity_data_top"><?php echo $this->t('tab_edit_entity_revision_note'); ?></td>
            <td class="entity_data_top"><?php echo $this->data['entity']->getRevisionnote(); ?></td>
        </tr>
        <tr>
            <td class="entity_data_top"> <?php echo $this->t('tab_edit_entity_parent_revision'); ?>:</td>
            <td class="entity_data_top"><?php
            if ($this->data['entity']->getParent() === null) {
                echo 'No parent';
            } else {
                echo '<a href="?eid='. $this->data['entity']->getEid() .'&revisionid='. $this->data['entity']->getParent().'">r'. $this->data['entity']->getParent() .'</a>';
            }
            ?></td>
        </tr>
        <tr>
            <td class="entity_data_top"><?php echo $this->t('tab_edit_entity_state'); ?>:</td>
            <td class="entity_data_top">
            <?php
                reset($this->data['workflowstates']);
                $current = current($this->data['workflowstates']);

                if(isset($current['name'][$this->getLanguage()])) {
                    $curLang = $this->getLanguage();
                } else {
                    $curLang = 'en';
                }


                if($this->data['uiguard']->hasPermission('changeworkflow', $wfstate, $this->data['user']->getType())) {
                ?>
                <select id="entity_workflow_select" name="entity_workflow">
                <?php
                foreach($this->data['workflow'] AS $wf) {
                    if($wfstate == $wf) {
                        echo '<option value="'. $wf .'" selected="selected">'. $this->data['workflowstates'][$wf]['name'][$curLang] .'</option>';
                    } else {
                        echo '<option value="'. $wf .'">'. $this->data['workflowstates'][$wf]['name'][$curLang] .'</option>';
                    }
                }
                ?>
                </select>
                <?php
                } else {
                    echo '<input type="hidden" name="entity_workflow" value="'. $wfstate .'">';
                    echo $this->data['workflowstates'][$wfstate]['name'][$this->getLanguage()];

                }
                ?>

            </td>
        </tr>
        <tr>
            <td><?php echo $this->t('admin_type'); ?>:</td>
            <td>
            <?php
            $enablematrix = $util->getAllowedTypes();
            if($this->data['uiguard']->hasPermission('changeentitytype', $wfstate, $this->data['user']->getType())) {
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
                echo $enablematrix[$this->data['entity_type']]['name'];
                echo '<input type="hidden" name="entity_type" value ="' . $this->data['entity_type'] . '">';
            }
            ?>
                    </td>
                    </tr>
                    <tr>
                    </tr>
                    </table>
            </td>
            <td width="30%" class="entity_data_top">
            <?php
            foreach($this->data['workflow'] AS $wf) {
                echo '<div class="entity_help" id="wf-desc-'. $wf .'"><div class="entity_help_title">'. $this->t('text_help') .'</div>'. $this->data['workflowstates'][$wf]['description'][$curLang] .'</div>';
            }
?>
            </td>
        </tr>
    </table>
</div>

<?php
// DISABLE CONCENT TAB
if($this->data['entity']->getType() == 'saml20-idp' || $this->data['entity']->getType() == 'shib13-idp') {
?>
<div id="disableconsent">
    <h2><?php echo $this->t('tab_disable_consent'); ?></h2>
    <p><?php echo $this->t('tab_disable_consent_help'); ?></p>
    <?php
    if($this->data['uiguard']->hasPermission('disableconsent', $wfstate, $this->data['user']->getType())) {
        foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {
            if(array_key_exists($remote_entityid, $this->data['disable_consent'])) {
                echo '<input class="consent_check" type="checkbox" name="add-consent[]" value="'. $remote_entityid. '" checked />&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()] .'<br />';
            } else {
                echo '<input class="consent_check" type="checkbox" name="add-consent[]" value="'. $remote_entityid. '" />&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()] .'<br />';
            }
            echo '&nbsp;&nbsp;&nbsp;'. $remote_data['description'][$this->getLanguage()] .'<br />';
        }
    } else {
        foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {
            if(array_key_exists($remote_entityid, $this->data['disable_consent'])) {
                echo '<input class="remote_check" type="hidden" name="add-consent[]" value="'. $remote_entityid. '" />';
                echo '<input class="remote_check" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" checked disabled="disabled" />';
                echo '&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()] .'<br />';
            } else {
                echo '<input class="remote_check" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" disabled />';
                echo '&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()] .'<br />';
            }
            echo '&nbsp;&nbsp;&nbsp;'. $remote_data['description'][$this->getLanguage()] .'<br />';
        }
    }
    ?>
</div>
<?php
}
// DISABLE CONSENT TAB - END
?>

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
                echo '<input class="remote_check" type="checkbox" name="add[]" value="'. $remote_entityid. '" checked />&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()] .'<br />';
            } else {
                echo '<input class="remote_check" type="checkbox" name="add[]" value="'. $remote_entityid. '" />&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()] .'<br />';
            }
            echo '&nbsp;&nbsp;&nbsp;'. $remote_data['description'][$this->getLanguage()] .'<br />';
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
                echo '<input class="remote_check" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" checked disabled="disabled" />&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()] .'<br />';
            } else {
                echo '<input class="remote_check" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" disabled />&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()].'<br />';
            }
            echo '&nbsp;&nbsp;&nbsp;'. $remote_data['description'][$this->getLanguage()] .'<br />';
        }
    }
    ?>
</div>
<!-- TAB METADATA -->
<div id="metadata">
    <h2>Metadata</h2>

    <script>
        var metadata = new Array();

        metadata["NULL"] = '';
        <?php
        foreach($this->data['metadata_fields'] AS $metadata_key => $metadata_val) {
            if(isset($metadata_val['supported'])) {
                $supported_idioms = $metadata_val['supported'];
                foreach($supported_idioms as $supported_idiom) {
                    $name = $metadata_key.':'.$supported_idiom;
                    echo 'metadata["'. $name .'"] = new Array();';
                    echo 'metadata["'. $name .'"]["type"] = "'. $metadata_val['type'] .'";';
                    echo 'metadata["'. $name .'"]["default"] = "'. $metadata_val['default'] .'";';
                    if(isset($metadata_val['select_values'])) {
                        $select_values = $metadata_val['select_values'];
                        if(is_array($metadata_val['select_values']) && !empty($metadata_val['select_values'])) {
                            echo 'metadata["'. $name .'"]["select_values"] = new Array(\''. implode("','", $metadata_val['select_values']) .'\');';
                        }
                    }
                }   
            }
            else {
                echo 'metadata["'. $metadata_key .'"] = new Array();';
                echo 'metadata["'. $metadata_key .'"]["type"] = "'. $metadata_val['type'] .'";';
                echo 'metadata["'. $metadata_key .'"]["default"] = "'. $metadata_val['default'] .'";';
                if(isset($metadata_val['validate'])) {
                    echo 'metadata["'. $metadata_key .'"]["validate"] = "'. $metadata_val['validate'] .'";';
                }
                if(isset($metadata_val['select_values'])) {
                    $select_values = $metadata_val['select_values'];
                    if(is_array($metadata_val['select_values']) && !empty($metadata_val['select_values'])) {
                        
                        echo 'metadata["'. $metadata_key .'"]["select_values"] = new Array(\''. implode("','", $metadata_val['select_values']) .'\');';
                    }
                }
            }

            echo "\n";
        }
        ?>

        function changeId(elm) {
            makker = $(elm).parent().next();
            makker.children().remove();
            var index = $(elm).val();
            switch(metadata[index]["type"]) {
                case 'boolean':
                    if(metadata[index]["default"] == 'true') {
                        var checkedtrue = 'checked="checked"';
                        var checkedfalse = '"';
                    } else {
                        var checkedfalse = 'checked="checked"';
                        var checkedtrue = '"';
                    }
                    $('<input clas="metadata_checkbox" type="checkbox" value="true" name="meta_value[' + index + '-TRUE]" onclick="changeFalse(this);" ' + checkedtrue + '>').appendTo(makker);
                    $('<input class="display_none" type="checkbox" value="false", name="meta_value[' + index + '-FALSE]" ' + checkedfalse + '">').appendTo(makker);
                    break;
                case 'text':
                    if(metadata[index]["validate"]) {
                        $('<input type="text" name="meta_value[' + index + ']" class="width_100" value="' + metadata[index]["default"] + '" onfocus="this.value=\'\';" onKeyup="validateInput(this, \'' + metadata[index]["validate"] + '\');">').appendTo(makker);
                    } else {
                        $('<input type="text" name="meta_value[' + index + ']" class="width_100" value="' + metadata[index]["default"] + '" onfocus="this.value=\'\';">').appendTo(makker);
                    }
                    break;
                case 'select':
                    if(metadata[index]["select_values"] !== "undefined" && 
                       typeof(metadata[index]["select_values"]) == "object") {
                        var default_value = null;
                        if(metadata[index]["default"] !== "undefined") {
                            default_value = metadata[index]["default"];
                        }
                        $('<select name="meta_value[' + index + ']" ></select>').appendTo(makker);
                        select_html = document.getElementsByName('meta_value[' + index + ']')[0];
                        select_values = metadata[index]["select_values"];
                        for (i  in select_values) {
                            if(select_values[i] == default_value) {
                                select_html.options[select_html.length] = new Option(select_values[i], select_values[i], "defaultSelected");
                            }
                            else {
                                select_html.options[select_html.length] = new Option(select_values[i], select_values[i]);
                            }
                        }
                        break;
                    }
                default:
                    $('<input type="text" name="meta_value[' + index + ']" class="width_100" value="' + metadata[index]["default"] + '" onfocus="this.value=\'\';">').appendTo(makker);
            }

            $(elm).children().each(function () {
                $("#metadata-desc-" + $(this).val().replace(/:/g,"\\:").replace(/\./g,"\\.")).hide();
            });
            var tmp = "metadata-desc-"+$(elm).val().replace(/:/g,"\\:").replace(/\./g,"\\.");
            $("#"+tmp).show()
        }

        function addMetadataInput() {
            newelm = $("#add_meta").clone();
            newelm.find("input").attr("value", "");
            newelm.find("span").text("");
            newelm.insertBefore("#mata_delim");
        }

        
        function delete_metadata(metadata_name) {
            if(confirm('<?php echo $this->t('delete_metadata_question'); ?>')) {
                input_delete_metadata = "delete-matadata-"+metadata_name;
                $("#"+input_delete_metadata).attr('checked', 'checked');
                $('#mainform').trigger('submit');
            }
        }
        


        var timer;

        function validateInput(elm, func) {
            clearTimeout(timer);
            timer = setTimeout(function(){
                    $.post(
                        "AJAXRequestHandler.php",
                        {
                            func: "validateMetadataField",
                            userfunc: func,
                            value: elm.value
                        },    
                        function(data){
                            var tmp = $(elm).parent().parent().find(".metadata_control");
                            if(data.valid) {
                                tmp.find("span").text("Valid");
                            } else {
                                tmp.find("span").text("Not valid");
                            }
                        },
                        "json"   
                    );
                },
                500       
            );
        }

    </script>
    <?php
    $deletemetadata = FALSE;
    if($this->data['uiguard']->hasPermission('deletemetadata', $wfstate, $this->data['user']->getType())) {
        $deletemetadata = TRUE;
    }
    $modifymetadata = 'readonly="readonly"';
    if($this->data['uiguard']->hasPermission('modifymetadata', $wfstate, $this->data['user']->getType())) {
        $modifymetadata = '';
    }

    echo '<table border="0" class="width_100">';
    echo '<tr>';
    echo '<td width="20%"><h3>'. $this->t('tab_edit_entity_entry') .'</h3></td>';
    echo '<td><h3>'. $this->t('tab_edit_entity_value') .'</h3></td>';
    echo '</tr>';

    if(!$metadata = $this->data['metadata']) {
        echo "Not metadata for entity ". $this->data['entity']->getEntityId() . '<br /><br />';
    } else {
        $i = 0;
        foreach($metadata AS $data) {
            $supported_idiom = null;
            $base_field_name = $this->data['metadata_base_field_names'][$data->getKey()];
            $metadata_field = $this->data['metadata_fields'][$base_field_name];
            if ($base_field_name != $data->getKey()) {
                $supported_idiom = str_replace($base_field_name.':', '',  $data->getKey());
            }
            echo '<tr class="'. ($i % 2 == 0 ? 'even' : 'odd'). '">';
            echo '<td>'. $data->getkey() . '</td>';
            echo '<td>';
            if(isset($metadata_field['required'])) {
                $requiredfield = $metadata_field['required'];
            } else {
                $requiredfield = false;
            }

            switch($metadata_field['type']) {
                case 'text':
                    $validate = isset($this->data['metadata_fields'][$data->getKey()]['validate']) ? 'onKeyup="validateInput(this, \'' . $this->data['metadata_fields'][$data->getKey()]['validate'] . '\');"' : '';
                    echo '<input class="width_100" type="text" name="edit-metadata-'. $data->getKey()  .'" value="'. $data->getValue()  .'" ' . $modifymetadata . ' ' . $validate . '>';
                    break;
                case 'boolean':
                    if($data->getValue() == 'true') {
                        $checked_true = 'checked="checked"';
                        $checked_false = '';
                    } else {
                        $checked_false = 'checked="checked"';
                        $checked_true = '';
                    }
                    echo '<input value="true" type="checkbox" class="metadata_checkbox" name="edit-metadata-'. $data->getKey()  .'-TRUE" '. $checked_true .' ' . $modifymetadata . ' onclick="changeFalse(this);">';
                    echo '<input value="false" type="checkbox" class="display_none" name="edit-metadata-'. $data->getKey()  .'-FALSE" '. $checked_false .' ' . $modifymetadata . '>';
                    break;
                case 'select':
                    if(isset($metadata_field['select_values']) && 
                       is_array($metadata_field['select_values'])) {
                        $default = null;
                        if(isset($metadata_field['default'])) {
                            $default = $metadata_field['default'];
                        }
                        $select_values = $metadata_field['select_values'];
                        $actual_value = $data->getValue();
                        echo '<select name="edit-metadata-'. $data->getKey()  .'">';
                        foreach($select_values as $select_value) {
                            echo '<option value="'.$select_value.'"';
                            if($select_value == $actual_value || 
                               (empty($value) && $select_value == $default)) {
                                echo 'selected="selected"';
                               }
                            echo '>'.$select_value.'</option>';
                        }
                        echo '</select>';
                        break;
                    }
                default:
                    $validate = isset($this->data['metadata_fields'][$data->getKey()]['validate']) ? 'onKeyup="validateInput(this, \'' . $this->data['metadata_fields'][$data->getKey()]['validate'] . '\');"' : '';
                    echo '<input class="width_100" type="text" name="edit-metadata-'. $data->getKey()  .'" value="'. $data->getValue()  .'" ' . $modifymetadata . ' ' . $validate . '>';
            }
            if(isset($supported_idiom)) {
                $index = array_search($supported_idiom, $this->data['metadata_fields'][$base_field_name]['supported']);
                if($index !== false) {
                    unset($this->data['metadata_fields'][$base_field_name]['supported'][$index]);
                }
            }
            else {
                unset($this->data['metadata_fields'][$base_field_name]);
            }
            echo '<input type="checkbox" class="display_none" value="'. $data->getKey() .'" id="delete-matadata-'. $data->getKey() .'" name="delete-metadata[]" >';
            echo '</td>';
            if($deletemetadata && !$requiredfield) {
                $metadata_key_parsed = str_replace(array(':', '.', '#') , array('\\\\:', '\\\\.', '\\\\#'), $data->getKey());
                echo '<td width="100px" align="right" class="metadata_control"><img onClick="javascript:{delete_metadata(\''. $metadata_key_parsed .'\');}" src="resources/images/pm_delete_16.png" alt="'. strtoupper($this->t('admin_delete')) .'" /></td>';
            } else {
                echo '<td align="right" width="100px" class="metadata_control"><b><span></span></b></td>';
            }
            echo '</tr>';
            $i++;
        }
    }

    if($this->data['uiguard']->hasPermission('addmetadata', $wfstate, $this->data['user']->getType())) {
        echo '<tr id="add_meta">';
        echo '<td>';
        echo '<select id="metadata_select" name="meta_key" onchange="changeId(this);" class="metadata_selector">';
        echo '<option value="NULL">-- '. $this->t('tab_edit_entity_select') .' --</option>';
        foreach($this->data['metadata_fields'] AS $metadata_key => $metadata_val) {
            if(isset($metadata_val['supported'])) {
                $supported_idioms = $metadata_val['supported'];
                foreach($supported_idioms as $supported_idiom) {
                    $name = $metadata_key.':'.$supported_idiom;
                    if(array_key_exists('required', $metadata_val) && $metadata_val['required'] === true) {
                        echo '<option class="addmetadata" value="'. $name . '">'. $name. '</option>';
                    } else {
                        echo '<option value="', $name, '">'. $name . '</option>';
                    }
                }
            }
            else {
                if(array_key_exists('required', $metadata_val) && $metadata_val['required'] === true) {
                    echo '<option class="addmetadata" value="', $metadata_key, '">', $metadata_key, '</option>';
                } else {
                    echo '<option value="', $metadata_key, '">', $metadata_key, '</option>';
                }
            }
        }
        echo '</select>';
        echo '</td>';
        echo '<td>';
        echo '</td>';
        echo '<td align="right" width="100px" class="metadata_control"><b><span></span></b>';
        echo '</td>';
        echo '</tr>';
        echo '<tr id="mata_delim">';
        echo '<td height="10px">';
        echo '<img onclick="addMetadataInput(this);" src="resources/images/pm_plus_16.png" alt="Plus" />';
        echo '</td>';
        echo '<td colspan="2">';
        foreach($this->data['metadata_fields'] AS $metadata_key => $metadata_val) {
            if(isset($metadata_val['supported'])) {
                $supported_idioms = $metadata_val['supported'];
                foreach($supported_idioms as $supported_idiom) {
                    $name = $metadata_key.':'.$supported_idiom;
                    echo '<div class="metadata_help_desc" id="metadata-desc-'. $name .'">';
                    echo '<div class="metadata_help_title">';
                    echo $this->t('text_help');
                    echo '</div>';
                    echo $metadata_val['description'][$this->getLanguage()];
                    echo '</div>';
                }
            } else {
                $name = $metadata_key;
                echo '<div class="metadata_help_desc" id="metadata-desc-'. $name .'">';
                echo '<div class="metadata_help_title">';
                echo $this->t('text_help');
                echo '</div>';
                echo $metadata_val['description'][$this->getLanguage()];
                echo '</div>';
            }
        }
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td colspan="3" height="10px">';
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';
    ?>
</div>
<?php
/*
if($this->data['entity']->getType() == 'saml20-sp' || $this->data['entity']->getType() == 'shib13-sp') {
?>
<script>
// change hidden checkbox to post false
function changeFalse(elm) {
    if($(elm).is(":checked")) {
        $(elm).next().removeAttr("checked");
    } else {
        $(elm).next().attr("checked", "checked");
    }
}
</script>
<script>
var attributes = new Array();

attributes["NULL"] = '';
<?php
foreach($this->data['attribute_fields'] AS $attribute_key => $attribute_val) {
    echo 'attributes["'. $attribute_key .'"] = new Array();';
    if(isset($attribute_val['description'][$this->getLanguage()])) {
        echo 'attributes["'. $attribute_key .'"]["description"] = "'. $attribute_val['description'][$this->getLanguage()] .'";';
    } else {
        echo 'attributes["'. $attribute_key .'"]["description"] = "'. $attribute_val['description']['en'] .'";'; 
    }
    echo 'attributes["'. $attribute_key .'"]["default"] = "";';
}
?>

function changeAttributeKey(elm) {
    makker = $(elm).parent().next();
    makker.children().remove();
    var index = $(elm).val();
    $('<input type="text" name="attr_value[' + $(elm).val() + ']" class="width_100" value="' + attributes[index]["default"] + '" onfocus="this.value=\'\';">').appendTo(makker);

    if($(elm).val() == 'NULL') {
        makker.children().remove();
        $("#attribute_desc_container").hide();
    } else {
        $("#attribute_desc").html(attributes[$(elm).val()]["description"]);
        $("#attribute_desc_container").show();
    }
}

function addAttributeInput() {
    newelm = $("#add_attr").clone();
    newelm.find("input").remove();
    newelm.insertBefore("#attr_delim");
}

function delete_attribute(attribute_name) {
    if(confirm('<?php echo $this->t('delete_attribute_question'); ?>')) {
        input_delete_attribute = "delete-attribute-"+attribute_name;
        $("#"+input_delete_attribute).attr('checked', 'checked');
        $('#mainform').trigger('submit');
    }
}

</script>
<!-- TAB - ATTRIBUTES -->
<div id="attributes">
<h2>Attributes</h2>
    <?php
    $deleteattribute = FALSE;
    if($this->data['uiguard']->hasPermission('deleteattribute', $wfstate, $this->data['user']->getType())) {
        $deleteattribute = TRUE;
    }
    $modifyattribute = 'readonly="readonly"';
    if($this->data['uiguard']->hasPermission('modifyattribute', $wfstate, $this->data['user']->getType())) {
        $modifyattribute = '';
    }

    echo '<table border="0" class="width_100">';
    echo '<tr>';
    echo '<td width="20%"><h3>'. $this->t('tab_edit_entity_entry') .'</h3></td>';
    echo '<td><h3>'. $this->t('tab_edit_entity_value') .'</h3></td>';
    echo '</tr>';

    if($this->data['uiguard']->hasPermission('addattribute', $wfstate, $this->data['user']->getType())) {
        echo '<tr id="add_attr">';
        echo '<td>';
        echo '<select id="attribute_select" name="attribute_key"i onChange="changeAttributeKey(this);" class="attribute_selector">';
        echo '<option value="NULL">-- '. $this->t('tab_edit_entity_select') .' --</option>';
        foreach($this->data['attribute_fields'] AS $attribute_key => $attribute_val) {
            echo '<option value="', $attribute_key, '">', $attribute_key, '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '<td>';
        echo '</td>';
        echo '<td>';
        echo '</td>';
        echo '</tr>';
        echo '<tr id="attr_delim">';
        echo '<td height="10px">';
        echo '<img onclick="addAttributeInput(this);" src="resources/images/pm_plus_16.png" alt="Plus" />';
        echo '</td>';
        echo '<td colspan="2">';
        echo '<div id="attribute_desc_container" class="attribute_desc">';
        echo '<div class="attribute_help_title">';
        echo $this->t('text_help');
        echo '</div>';
        echo '<div id="attribute_desc"></div>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
    if(!$attributes = $this->data['mcontroller']->getAttributes()) {
        echo '<tr><td colspan="3">Not attributes for entity '. $this->data['entity']->getEntityid() . '</td></tr>';
    } else {
        $i = 0;
        foreach($attributes AS $data) {
            echo '<tr class="'. ($i % 2 == 0 ? 'even' : 'odd') .'">';
            echo '<td width="1%">'. $data->getkey() . '</td>';
            echo '<td>';
            echo '<input class="width_100" type="text" name="edit-attribute-'. $data->getKey()  .'" value="'. $data->getValue()  .'" '. $modifyattribute .'>';
            echo '<input type="checkbox" class="display_none" value="'. $data->getKey() .'" id="delete-attribute-'. $data->getKey() .'" name="delete-attribute[]" >';
            echo '</td>';
            if($deleteattribute) {
                $attribute_parsed = str_replace(array(':', '.', '#') , array('\\\\:', '\\\\.', '\\\\#'), $data->getKey());
                echo '<td align="right"><img onClick="javascript:{delete_attribute(\''.$attribute_parsed.'\');}" src="resources/images/pm_delete_16.png" alt="'. strtoupper($this->t('admin_delete')) .'" /></td>';
            } else {
                echo '<td>';
                echo '</td>';
            }
            echo '</tr>';
            $i++;
        }
    }
?>
    </table>
</div>
<?php } 
*/
?>
<!-- TAB END - ATTRIBUTES -->
<div id="addmetadata">
    <h2><?php echo $this->t('tab_edit_entity_import_from_url'); ?></h2>
    <p>
    <?php
    if($this->data['uiguard']->hasPermission('importmetadata', $wfstate, $this->data['user']->getType())) {
        echo($this->t('add_metadata_from_url_desc') . '<br/>');
        echo('<input type="text" name="meta_url" size="70" />');
        echo('<input type="submit" name="add_metadata_from_url" value="'.$this->t('get_metadata').'"/>');
    }
    ?>
    </p>

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
        echo $this->t('error_import_metadata_permission');
    }
    ?>
</div>

<!-- EXPORT TAB -->
<div id="export">
<?php
if($this->data['uiguard']->hasPermission('exportmetadata', $wfstate, $this->data['user']->getType())) {
    echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/exportentity.php') .'?eid='. $this->data['entity']->getEid()  .'&revisionid='. $this->data['entity']->getRevisionid() .'&output=xhtml">'. $this->t('tab_edit_entity_export_metadata') .'</a><br /><br />';
} else {
    echo $this->t('error_no_access');
}
?>
</div>
<hr>
<?php echo $this->t('tab_edit_entity_revision_note'); ?>: <input type="text" name="revisionnote" class="revision_note" />
<input type="submit" name="formsubmit" id="master_submit" value="<?php echo $this->t('tab_edit_entity_save'); ?>" class="save_button"/>
<!-- END CONTENT -->
</div>

</form>

<?php

$this->includeAtTemplateBase('includes/footer.php');
?>
