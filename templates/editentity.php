<?php
/**
 * Main template for JANUS.
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @author Sixto Martín, <smartin@yaco.es>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id: janus-main.php 11 2009-03-27 13:51:02Z jach@wayf.dk $
 * @todo     Use some sort of form generator to reduce to amount of code and make it more robust
 */
$janus_config = sspmod_janus_DiContainer::getInstance()->getConfig();
$ssp_config = SimpleSAML_Configuration::getConfig();
$csrf_provider = sspmod_janus_DiContainer::getInstance()->getCsrfProvider();
$csrf_ajax_token_json_encoded = json_encode($csrf_provider->generateCsrfToken('ajax'));

// Load custom translations for metadata fields
$customDictionaryLoader = new sspmod_janus_CustomDictionaryLoader($this);
$dictionaryDir = sspmod_janus_DiContainer::getInstance()->getRootDir() . '/dictionaries';
$customDictionaryLoader->addFromDir($dictionaryDir);

$this->cookie_name = $ssp_config->getString('session.cookie.name', 'SimpleSAMLSessionID');
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/style.css" />' . "\n";
$this->data['head'] .= '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/styles/simptip-mini.css" />'."\n";
$this->data['head'] .= '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/styles/jsondiff/jsondiffpatch.html.css" />'."\n";
$this->data['head'] .= '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/components/jqueryui/themes/smoothness/jquery-ui.min.css" />'."\n";
$this->data['head'] .= '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/styles/tablesorter.default.css" />'."\n";
$this->data['head'] .= '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/styles/validate.css" />'."\n";
$this->data['head'] .= '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/styles/revisions.css" />'."\n";
$this->data['head'] .= '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/styles/arp.css" />'."\n";
$this->data['head'] .= '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/styles/metadata.css" />'."\n";
$this->data['head'] .= '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/styles/editentity-wblist.css" />'."\n";

$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/components/jquery/jquery.min.js"></script>' . "\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/components/jqueryui/ui/minified/jquery-ui.custom.min.js"></script>' . "\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/json2-min.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/jquery.tmpl.min.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/datehelper.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/validate.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/validate.metadata.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/arp.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/metadata.js"></script>'."\n";

$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/jsondiff/jsondiffpatch.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/jsondiff/jsondiffpatch.html.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/jsondiff/diff_match_patch_uncompressed.js"></script>'."\n";

$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/edit-entity-module.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/ace.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/mode-php.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/theme-crimson_editor.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/jquery.tablesorter.min.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/jquery.tablesorter.widgets.min.js"></script>'."\n";

$this->data['head'] .= '
<style type="text/css">
li, ul {
    list-style: none;
    margin: 0 0 0 10px;
}

ul {
    margin: 0;
}
</style>';

$this->includeAtTemplateBase('includes/header.php');
$util = new sspmod_janus_AdminUtil();
$wfstate = $this->data['entity_state'];
$states = $janus_config->getArray('workflowstates');
/** @var sspmod_janus_Entity $entity */
$entity = $this->data['entity'];
/** @var \Symfony\Component\Security\Core\SecurityContext $securityContext */
$securityContext = $this->data['security.context'];

// @todo Define these in some sort of form helper class
define('JANUS_FORM_ELEMENT_CHECKED', 'checked="checked"');
define('JANUS_FORM_ELEMENT_DISABLED', 'disabled="disabled"');
?>
<form id="mainform" method="post" action="<?php echo SimpleSAML_Utilities::selfURLNoQuery(); ?>" data-revision-required="<?php echo $janus_config->getBoolean('revision.notes.required', false); ?>">
<input type="hidden" name="eid" value="<?php echo htmlspecialchars($entity->getEid()); ?>" />
<input type="hidden" name="revisionid" value="<?php echo htmlspecialchars($entity->getRevisionid()); ?>" />
<input type="hidden" name="selectedtab" value="<?php echo htmlspecialchars($this->data['selectedtab']); ?>" />
<input type="hidden" name="csrf_token" value="<?php echo $csrf_provider->generateCsrfToken('entity_update') ?>" />
<a href="<?php echo \SimpleSAML\Module::getModuleURL('janus/index.php'); ?>"><?php echo $this->t('text_dashboard'); ?></a>
<h2 <?php echo ($entity->getActive() == 'no') ? 'style="background-color: #A9D0F5;"' : '' ?>>
<?php echo $this->t('edit_entity_header'), ' - ', htmlspecialchars($entity->getEntityid()) . ' ('. $this->t('tab_edit_entity_connection_revision') .' '. $entity->getRevisionId() . ')'; ?>
<?php echo ($entity->getActive() == 'no') ? ' - ' . strtoupper($this->t('text_disabled')) : '' ?>
</h2>
<div id="tabdiv">

<!-- TABS -->
<ul>
    <li><a href="#entity"><?php echo $this->t('tab_edit_entity_connection'); ?></a></li>
    <?php
    if ($this->data['useblacklist'] || $this->data['usewhitelist']) {
        if ($entity->getType() === 'saml20-sp') {
            echo '<li><a href="#remoteentities">'. $this->t('tab_remote_entity_saml20-sp') .'</a></li>';
        } else {
            echo '<li><a href="#remoteentities">'. $this->t('tab_remote_entity_saml20-idp') .'</a></li>';
        }
    }
    if ($entity->getType() === 'saml20-idp') {
        echo '<li><a href="#disableconsent">' . $this->t('tab_disable_consent') . '</a></li>';
    }
    ?>
    <li><a href="#metadata"><?php echo $this->t('tab_metadata'); ?></a></li>
    <?php
        if ($entity->getType() === 'saml20-sp') {
            echo '<li><a href="#arp">' . $this->t('tab_edit_entity_connection_arp') . '</a></li>';
        }
    ?>
    <li><a href="#manipulation_tab">Manipulation</a></li>
        <?php if ($securityContext->isGranted('validatemetadata', $entity)): ?>
    <li><a href="#validate" id="validate_link"><?php echo $this->t('tab_edit_entity_validate'); ?></a></li>
    <?php endif; ?>
    <li><a href="#addmetadata"><?php echo $this->t('tab_import_metadata'); ?></a></li>
    <li><a href="#export"><?php echo $this->t('tab_edit_entity_export'); ?></a></li>
    <li><a href="#history"><?php echo $this->t('tab_edit_entity_history'); ?></a></li>
</ul>
<!-- TABS END -->

<!-- START ENTITY CONNECTION -->
<div id="entity">
    <h2><?php
        echo $this->t('tab_edit_entity_connection') .' - '.
                $this->t('tab_edit_entity_connection_revision') .' '.
                $this->data['revisionid'] . ' - ' .
                date('Y-m-d H:i', strtotime($entity->getCreated()));
    ?></h2>

    <table>
        <tr>
            <td>
                <?php
                if (isset($this->data['msg']) && substr($this->data['msg'], 0, 5) === 'error') {
                    echo '<div class="editentity_error">'. $this->t('error_header').'</div>';
                    echo '<p>'. $this->t($this->data['msg']) .'</p>';
                } else if (isset($this->data['msg'])) {
                    echo '<p>'. $this->t($this->data['msg']) .'</p>';
                }
                ?>

                <table>
                    <tr>
                        <td class="entity_top_data"><?php echo $this->t('tab_edit_entity_connection_entityid'); ?>:</td>
                        <td>
                            <input type="text" style="display: inline;" size="<?php echo strlen($entity->getEntityid()) + 15; ?>" id="change_entity_id" disabled="disabled" name="entityid" value="<?php echo htmlspecialchars($entity->getEntityid()); ?>" />
                            <a id="change_entity_id_link" href="#" class="no-border"><img style="display: inline;" src="resources/images/pencil.png" alt="Edit" /></a>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $this->t('tab_edit_entity_connection_metadataurl'); ?>:</td>
                        <td><?php echo htmlspecialchars($entity->getMetadataURL()); ?></td>
                    </tr>
                    <?php
                    if ($entity->getType() == 'saml20-sp' || $entity->getType() == 'shib13-sp') {
                    ?>
                    <?php
                    }
                    ?>
                    <tr>
                        <td class="entity_data_top"><?php echo $this->t('tab_edit_entity_revision_note'); ?></td>
                        <td class="entity_data_top"><?php echo htmlspecialchars($entity->getRevisionnote()); ?></td>
                    </tr>
                    <tr>
                        <td class="entity_data_top"> <?php echo $this->t('tab_edit_entity_parent_revision'); ?>:</td>
                        <td class="entity_data_top"><?php
                        if ($entity->getParent() === null) {
                            echo 'No parent';
                        } else {
                            echo '<a href="?eid='. $entity->getEid() .'&amp;revisionid='. $entity->getParent().'">r'. $entity->getParent() .'</a>';
                        }
                        ?></td>
                    </tr>
                    <tr>
                        <td class="entity_data_top"><?php echo $this->t('tab_edit_entity_state'); ?>:</td>
                        <td class="entity_data_top">
                        <?php
                            reset($this->data['workflowstates']);
                            $current = current($this->data['workflowstates']);

                            if (isset($current['name'][$this->getLanguage()])) {
                                $curLang = $this->getLanguage();
                            } else {
                                $curLang = 'en';
                            }


                            if ($securityContext->isGranted('changeworkflow', $entity)) {
                            ?>
                            <select id="entity_workflow_select" name="entity_workflow">
                            <?php
                            foreach ($this->data['workflow'] as $wf) {
                                if ($wfstate == $wf) {
                                    echo '<option value="' . htmlspecialchars($wf) .'" selected="selected">'. htmlspecialchars($this->data['workflowstates'][$wf]['name'][$curLang]) .'</option>';
                                } else {
                                    echo '<option value="' . htmlspecialchars($wf) .'">'. htmlspecialchars($this->data['workflowstates'][$wf]['name'][$curLang]) .'</option>';
                                }
                            }
                            ?>
                            </select>
                            <?php
                            } else {
                                echo '<input type="hidden" name="entity_workflow" value="' . htmlspecialchars($wfstate) .'" />';
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
                        if ($securityContext->isGranted('changeentitytype', $entity)) {
                            echo '<select name="entity_type">';
                            foreach ($enablematrix as $typeid => $typedata) {
                                if ($typedata['enable'] === true) {
                                    if ($this->data['entity_type'] == $typeid) {
                                        echo '<option value="' . htmlspecialchars($typeid) .'" selected="selected">'. htmlspecialchars($typedata['name']) .'</option>';
                                    } else {
                                        echo '<option value="' . htmlspecialchars($typeid) .'">'. htmlspecialchars($typedata['name']) .'</option>';
                                    }
                                }
                            }
                            echo '</select>';
                        } else {
                            echo $enablematrix[$this->data['entity_type']]['name'];
                            echo '<input type="hidden" name="entity_type" value ="' . $this->data['entity_type'] . '" />';
                        }
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $this->t('notes'); ?>:</td>
                        <td>
                            <textarea name="notes" id="change_entity_notes" rows="4" cols="50" disabled="disabled"><?php echo htmlspecialchars($entity->getNotes()); ?></textarea>
                            <a id="change_entity_notes_link" href="#" class="no-border"><img style="display: inline;" src="resources/images/pencil.png" alt="Edit" /></a>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="30%" class="entity_data_top">
            <?php
            foreach ($this->data['workflow'] as $wf) {
                echo '<div class="entity_help" id="wf-desc-'. $wf .'"><div class="entity_help_title">'. $this->t('text_help') .'</div>'. $this->data['workflowstates'][$wf]['description'][$curLang] .'</div>';
            }
            ?>
            </td>
        </tr>
    </table>
</div>
<!-- ENTITY CONNECTION - END-->
<!-- DISABLE CONSENT TAB - START -->
<?php

if ($entity->getType() == 'saml20-idp' || $entity->getType() == 'shib13-idp'):
?>
<div id="disableconsent">
    <h2><?php echo $this->t('tab_disable_consent'); ?></h2>
    <p><?php echo $this->t('tab_disable_consent_help'); ?></p>
    <?php
    if ($securityContext->isGranted('disableconsent', $entity)) {
        foreach ($this->data['remote_entities'] as $remote_entityid => $remote_data) {
            if (array_key_exists($remote_entityid, $this->data['disable_consent'])) {
                echo '<input class="consent_check" type="checkbox" name="add-consent[]" value="' . htmlspecialchars($remote_data['eid']) . '" ' . JANUS_FORM_ELEMENT_CHECKED . ' />&nbsp;&nbsp;'. htmlentities($remote_data['name'][$this->getLanguage()]) .'<br />';
            } else {
                echo '<input class="consent_check" type="checkbox" name="add-consent[]" value="' . htmlspecialchars($remote_data['eid']) . '" />&nbsp;&nbsp;'. htmlentities($remote_data['name'][$this->getLanguage()]) .'<br />';
            }
            echo '&nbsp;&nbsp;&nbsp;'. htmlspecialchars($remote_data['description'][$this->getLanguage()]) .'<br />';
        }
        echo '<input type="hidden" name="consent-changed" id="consent_changed_input"/>';
    } else {
        foreach ($this->data['remote_entities'] as $remote_entityid => $remote_data) {
            if (array_key_exists($remote_entityid, $this->data['disable_consent'])) {
                echo '<input class="remote_check_b" type="hidden" name="add-consent[]" value="' . htmlspecialchars($remote_entityid) . '" />';
                echo '<input class="remote_check_b" type="checkbox" name="add_dummy[]" value="' . htmlspecialchars($remote_entityid) . '" ' . JANUS_FORM_ELEMENT_CHECKED . ' ' . JANUS_FORM_ELEMENT_DISABLED . ' />';
                echo '&nbsp;&nbsp;'. htmlspecialchars($remote_data['name'][$this->getLanguage()]) .'<br />';
            } else {
                echo '<input class="remote_check_b" type="checkbox" name="add_dummy[]" value="' . htmlspecialchars($remote_entityid) . '" ' . JANUS_FORM_ELEMENT_DISABLED . ' />';
                echo '&nbsp;&nbsp;'. htmlspecialchars($remote_data['name'][$this->getLanguage()]) .'<br />';
            }
            echo '&nbsp;&nbsp;&nbsp;'. htmlspecialchars($remote_data['description'][$this->getLanguage()]) .'<br />';
        }
    }
    ?>
</div>
<?php endif; ?>
<!-- DISABLE CONSENT TAB - END -->
<!-- TAB SP / IDP white/blacklisting  -->
<?php
	if ($this->data['useblacklist'] || $this->data['usewhitelist']) {
        require __DIR__ . '/editentity/whiteblacklist.php';
	}
?>
<!-- TAB METADATA -->
<div id="metadata">
    <h2>Metadata</h2>

    <script type="text/javascript">
        var metadata = [];

        metadata["NULL"] = '';

        <?php
        foreach ($this->data['metadatafields'] as $mf) {
            if (isset($mf->type)) {
                echo 'metadata["'. $mf->name .'"] = new Array();';
                echo 'metadata["'. $mf->name .'"]["type"] = "'. $mf->type .'";';
            } else {
                // Skip this metadata field if 'type' is not set
                continue;
            }
            if (isset($mf->default)) {
                echo 'metadata["'. $mf->name .'"]["default"] = "'. $mf->default .'";';
            } else {
                echo 'metadata["'. $mf->name .'"]["default"] = "";';
            }
            if (isset($mf->validate)) {
                echo 'metadata["'. $mf->name .'"]["validate"] = "'. $mf->validate .'";';
            }
            if (isset($mf->select_values) && is_array($mf->select_values) && !empty($mf->select_values)) {
                echo 'metadata["'. $mf->name .'"]["select_values"] = new Array("' . implode('","', $mf->select_values) . '");';
            }
            echo "\n";
        }
        ?>

        function changeId(elm) {
            var makker = $(elm).parent().next();
            makker.children().remove();
            var index = $(elm).val();
            switch(metadata[index]["type"]) {
                case 'boolean':
                    if (metadata[index]["default"] == true) {
                        var checkedtrue = '<?php echo JANUS_FORM_ELEMENT_CHECKED;?>';
                        var checkedfalse = '';
                    } else {
                        var checkedfalse = '<?php echo JANUS_FORM_ELEMENT_CHECKED;?>';
                        var checkedtrue = '';
                    }
                    $('<input class="metadata_checkbox" type="checkbox" value="true"  name="meta_value[' + index + '-TRUE]" onclick="changeFalse(this);" ' + checkedtrue + ' />').appendTo(makker);
                    $('<input class="display_none"      type="checkbox" value="false" name="meta_value[' + index + '-FALSE]" ' + checkedfalse + '" />').appendTo(makker);
                    break;
                case 'text':
                    if (metadata[index]["validate"]) {
                        $('<input type="text" name="meta_value[' + index + ']" class="width_100" value="' + metadata[index]["default"] + '" onfocus="this.value=\'\';" onkeyup="validateInput(this, \'' + metadata[index]["validate"] + '\');" onblur="$(this).removeAttr(\'onfocus\');" />').appendTo(makker);
                    } else {
                        $('<input type="text" name="meta_value[' + index + ']" class="width_100" value="' + metadata[index]["default"] + '" onfocus="this.value=\'\';" onblur="$(this).removeAttr(\'onfocus\');" />').appendTo(makker);
                    }
                    break;
                case 'select':
                    if (metadata[index]["select_values"] !== "undefined" &&
                    	typeof(metadata[index]["select_values"]) == "object") {
                    	var default_value = null;
                        if (metadata[index]["default"] !== "undefined") {
                            default_value = metadata[index]["default"];
                        }
                        $('<select name="meta_value[' + index + ']">').appendTo(makker);
                        $('<\/select>').appendTo(makker);
                        select_html = document.getElementsByName('meta_value[' + index + ']')[0];
                        select_values = metadata[index]["select_values"];
                        for (i in select_values) {
                            if (select_values[i] == default_value) {
                                select_html.options[select_html.length] = new Option(select_values[i], select_values[i], "defaultSelected");
                            } else {
                                select_html.options[select_html.length] = new Option(select_values[i], select_values[i]);
                            }
                        }
                    }
                    break;
                default:
                    $('<input type="text" name="meta_value[' + index + ']" class="width_100" value="' + metadata[index]["default"] + '" onfocus="this.value=\'\';" />').appendTo(makker);
            }

            $(elm).children().each(function () {
                $("#metadata-desc-" + $(this).val().replace(/:/g,"\\:").replace(/\./g,"\\.")).hide();
            });
            var tmp = "metadata-desc-"+$(elm).val().replace(/:/g,"\\:").replace(/\./g,"\\.");
            $("#"+tmp).show()
        }

        function addMetadataInput() {
            var newelm = $($(".new_metadata_field")[0]).clone();
            newelm.find("input").attr("value", "").attr("name", "");
            newelm.find("span").text("");
            newelm.insertBefore("#meta_delim");
        }


        function delete_metadata(metadata_name) {
            if (confirm('<?php echo $this->t('delete_metadata_question'); ?>')) {
                var input_delete_metadata = "delete-matadata-"+metadata_name;
                $("#"+input_delete_metadata).attr('checked', 'checked');
                $('#mainform').trigger('submit');
            }
        }

        /**
         * Pre-add a metadata field for the user.
         * @param {String} metadata_name
         */
        function preAddMetadataInput(metadata_name) {
            var metadataSelectors = $('.metadata_selector');
            if (metadataSelectors.length === 0) {
                // No 'select type for new metadata field'? Maybe we don't have the right to add metadata?
                return false;
            }
            var lastMetadataSelector = $(metadataSelectors[metadataSelectors.length - 1]);
            lastMetadataSelector.val(metadata_name);
            changeId(lastMetadataSelector[0]);
            addMetadataInput();
        }

        var timer;

        function validateInput(elm, func) {
            clearTimeout(timer);
            timer = setTimeout(function(){
                    $.post(
                        "AJAXRequestHandler.php",
                        {
                            "func": "validateMetadataField",
                            "userfunc": func,
                            "value": elm.value,
                            "csrf_token": <?=$csrf_ajax_token_json_encoded?>
                        },
                        function(data){
                            var tmp = $(elm).parent().parent().find(".metadata_control");
                            if (data.valid) {
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

        // change hidden checkbox to post false
        function changeFalse(elm) {
            if ($(elm).is(":checked")) {
                $(elm).next().removeAttr("checked");
            } else {
                $(elm).next().attr("checked", "checked");
            }
    }
    </script>
    <?php
    $deletemetadata = FALSE;
    if ($securityContext->isGranted('deletemetadata', $entity)) {
        $deletemetadata = TRUE;
    }
    $modifymetadata = 'readonly="readonly"';
    if ($securityContext->isGranted('modifymetadata', $entity)) {
        $modifymetadata = '';
    }

    // Check for and remove metadata without a definition
    if (isset($this->data['metadata']) && !empty($this->data['metadata'])) {
        /** @var sspmod_janus_Metadata[] $undefinedMetadataFields */
        $undefinedMetadataFields = array();
        /** @var sspmod_janus_Metadata $data */
        foreach ($this->data['metadata'] as $index => $data) {
            if (!isset($this->data['metadatafields'][$data->getKey()])) {
                $undefinedMetadataFields[] = $data;
                unset($this->data['metadata'][$index]);
            }
        }
        if (!empty($undefinedMetadataFields)) {
            echo '<h3 style="color: red;">Metadata found without a definition?</h3>';
            echo "<table><thead><tr><th>Entry</th><th>Value</th></tr></thead><tbody>";
            foreach ($undefinedMetadataFields as $undefinedMetadataField) {
                echo '<tr>
                        <td style="padding-right: 1em">' .
                            $undefinedMetadataField->getKey() .
                       '</td><td>' .
                            $undefinedMetadataField->getValue() .
                            '<input type="checkbox"
                                    class="display_none"
                                    value="' . htmlspecialchars($undefinedMetadataField->getKey()) .'"
                                    id="delete-matadata-'. htmlspecialchars($undefinedMetadataField->getKey()) .'"
                                    name="delete-metadata[]" />'.
                       '</td>';
                if ($deletemetadata) {
                    $metadata_key_parsed = str_replace(array(':', '.', '#') , array('\\\\:', '\\\\.', '\\\\#'), $undefinedMetadataField->getKey());
                    echo '<td width="100px" align="right" class="metadata_control">
                            <span style="font-weight: bold;"></span>&nbsp;
                            <img onclick="javascript:{delete_metadata(\''. $metadata_key_parsed .'\');}"
                                 src="resources/images/pm_delete_16.png"
                                 alt="'. strtoupper($this->t('admin_delete')) .'"
                                 style="display: inline;" />
                          </td>';
                }
                echo '</tr>';
            }
            echo "</tbody></table><br />";
        }
    }

    /** @var \sspmod_janus_MetadataField[] $metadataFields */
    $metadataFields = $this->data['metadatafields'];
    /** @var \sspmod_janus_Metadata[] $metadata */
    $metadata       = $this->data['metadata'];

    if (!$metadata) {
        echo "<p>No metadata for entity ". htmlspecialchars($entity->getEntityId()) . '</p>';
    }

    echo '<table border="0" class="width_100">';
    echo   '<tr>';
    echo      '<td width="20%"><h3>'. $this->t('tab_edit_entity_entry') .'</h3></td>';
    echo      '<td><h3>'. $this->t('tab_edit_entity_value') .'</h3></td>';
    echo   '</tr>';

    if ($metadata) {
        $i = 0;
        foreach ($metadata as $index => $data) {
            if (!isset($metadataFields[$data->getKey()])) {
                echo '<div style="color: red; font-size: large;">Metadata value found without definition:</div>';
                echo "<dl><dt>" . $data->getKey() . "</dt><dd>" . $data->getValue() . "</dd></dl>";
                continue;
            }
            $metadata_field = $metadataFields[$data->getKey()];
            echo '<tr class="'. ($i % 2 == 0 ? 'even' : 'odd'). '"  onmouseout="$(\'#metadata-desc-' . strtr($data->getkey(), array(':' => '\\\:', '.' => '\\\.')) . '\').hide();" onmouseover="$(\'#metadata-desc-' . strtr($data->getkey(), array(':' => '\\\:', '.' => '\\\.')) . '\').show();">';
            echo '<td>'. $data->getkey() . '</td>';
            echo '<td>';

            switch ($metadata_field->type) {
                case 'text':
                    $validate = isset($metadata_field->validate) ? 'onkeyup="validateInput(this, \'' . $metadata_field->validate . '\');"' : '';
                    echo '<input class="width_100" type="text" name="edit-metadata-'. $data->getKey()  .'" value="' . htmlspecialchars($data->getValue()) .'" ' . $modifymetadata . ' ' . $validate . ' />';
                    break;
                case 'boolean':
                    if ($data->getValue() == true) {
                        $checked_true = JANUS_FORM_ELEMENT_CHECKED;
                        $checked_false = '';
                    } else {
                        $checked_false = JANUS_FORM_ELEMENT_CHECKED;
                        $checked_true = '';
                    }
                    if ($modifymetadata == 'readonly="readonly"') {
                        echo '<input value="true" type="checkbox" class="metadata_checkbox" name="edit-metadata-'. $data->getKey()  .'-TRUE" '. $checked_true . ' ' . JANUS_FORM_ELEMENT_DISABLED . ' onclick="changeFalse(this);" />';
                    } else {
                        echo '<input value="true" type="checkbox" class="metadata_checkbox" name="edit-metadata-'. $data->getKey()  .'-TRUE" '. $checked_true .' onclick="changeFalse(this);" />';
                    }
                    echo '<input value="false" type="checkbox" class="display_none" name="edit-metadata-'. $data->getKey()  .'-FALSE" '. $checked_false .' ' . $modifymetadata . ' />';
                    break;
                case 'select':
                    if ($modifymetadata == 'readonly="readonly"') {
                        echo '<input class="width_100" type="text" name="edit-metadata-'. $data->getKey()  .'" value="' . htmlspecialchars($data->getValue()) .'" ' . $modifymetadata . ' />';
                    } else {
                        if (isset($metadata_field->select_values) && is_array($metadata_field->select_values)) {
                            $default = null;
                            if (isset($metadata_field->default)) {
                                $default = $metadata_field->default;
                            }
                            $select_values = $metadata_field->select_values;
                            $actual_value = $data->getValue();
                            echo '<select name="edit-metadata-'. $data->getKey()  .'">';
                            foreach ($select_values as $select_value) {
                                echo '<option value="' . htmlspecialchars($select_value) .'"';
                                if ($select_value == $actual_value ||
                                    (empty($actual_value) && $select_value == $default)) {
                                        echo ' selected="selected"';
                                    }
                                echo '>'. htmlspecialchars($select_value) . '</option>';
                            }
                            echo '</select>';
                        }
                    }
                    break;
                default:
                    $validate = isset($metadata_field->validate) ? 'onkeyup="validateInput(this, \'' . $metadata_field->validate . '\');"' : '';
                    echo '<input class="width_100" type="text" name="edit-metadata-'. $data->getKey()  .'" value="' . htmlspecialchars($data->getValue()) .'" ' . $modifymetadata . ' ' . $validate . ' />';
            }

            unset($metadataFields[$data->getKey()]);

            echo '<input type="checkbox" class="display_none" value="' . htmlspecialchars($data->getKey()) .'" id="delete-matadata-'. $data->getKey() .'" name="delete-metadata[]" />';
            echo '</td>';
            if ($deletemetadata && !(isset($metadata_field->required) ? $metadata_field->required : false)) {
                $metadata_key_parsed = str_replace(array(':', '.', '#') , array('\\\\:', '\\\\.', '\\\\#'), $data->getKey());
                echo '<td width="100px" align="right" class="metadata_control">
                        <b><span></span></b>&nbsp;
                        <img onclick="javascript:{delete_metadata(\''. $metadata_key_parsed .'\');}"
                             src="resources/images/pm_delete_16.png"
                             alt="'. strtoupper($this->t('admin_delete')) .'"
                             style="display: inline;" />
                      </td>';
            } else {
                echo '<td align="right" width="100px" class="metadata_control"><span style="font-weight: bold;"></span></td>';
            }
            echo '</tr>';
            $i++;
        }
    }

    if ($securityContext->isGranted('addmetadata', $entity)) {
        echo '<tr class="new_metadata_field">';
        echo '  <td>';
        echo '      <select name="meta_key" onchange="changeId(this);" class="metadata_selector">';
        echo '          <option value="NULL">-- '. $this->t('tab_edit_entity_select') .' --</option>';
        foreach ($metadataFields as $mf) {
            echo '      <option value="', htmlspecialchars($mf->name), '">', htmlspecialchars($mf->name), '</option>';
        }
        echo '      </select>';
        echo '  </td>';
        echo '  <td>';
        echo '  </td>';
        echo '  <td align="right" width="100px" class="metadata_control"><span style="font-weight: bold;"></span>';
        echo '  </td>';
        echo '</tr>';
        echo '<tr id="meta_delim">';
        echo '  <td height="70px" style="vertical-align: top;">';
        echo '      <img onclick="addMetadataInput(this);" src="resources/images/pm_plus_16.png" alt="Plus" />';
        echo '  </td>';
        echo '  <td colspan="2">';
        $available_languages = $ssp_config->getArray('language.available');
        foreach ($available_languages as &$alang) {
            $alang = '/:' . $alang . '/';
        }
        foreach ($metadataFields as $mf) {
            $desc_key = preg_replace('/:\d{1,2}/', '', $mf->name);
            $desc_key = preg_replace($available_languages, '', $desc_key);
            $desc_key = str_replace(':', '_', $desc_key);
            echo '<div class="metadata_help_desc" id="metadata-desc-'. $mf->name .'">';
            echo '<div class="metadata_help_title">';
            echo $this->t('text_help');
            echo '</div>';
            echo $this->t('{janus:metadatafields:' . $desc_key . '}');
            echo '</div>';
        }
        echo '  </td>';
        echo '</tr>';

    }

    echo '</table>';

    if ($securityContext->isGranted('addmetadata', $entity)) {
        echo '<script type="text/javascript">';
        /**
         * @var sspmod_janus_MetadataField $definition
         */
        foreach ($metadataFields as $key => $definition) {
            if (isset($definition->required) && $definition->required) {
                /**
                 * @var sspmod_janus_Metadata $metadataEntry
                 */
                foreach ($metadata as $metadataEntry) {
                    if ($metadataEntry->getKey() === $definition->name) {
                        continue 2;
                    }
                }

                echo "preAddMetadataInput('$key');\n";
            }
        }
        echo '</script>';
    }
    ?>
</div>

<?php
    if ($entity->getType() === 'saml20-sp') {
        require __DIR__ . '/editentity/arp.php';
    }
?>

<div id="manipulation_tab">
    <pre><?php echo htmlspecialchars(<<<'PHPCODE'
/**
 * PHP code for advanced Response Manipulation.
 * The following variables are available:
 *
 * @var string &$subjectId  NameID (empty for IdPs)
 * @var array  &$attributes URN attributes (example: array('urn:mace:terena.org:attribute-def:schacHomeOrganization'=>array('example.edu')))
 * @var array  &$response   XmlToArray formatted Response
 */
PHPCODE
    );?>
    </pre>
    <?php
/**
 * @var SimpleSAML_Session $session
 */
    $session = $this->data['session'];
    $syntaxErrors = $session->getData('string', 'manipulation_syntax_errors');
    if ($syntaxErrors) {
        $session->setData('string', 'manipulation_syntax_errors', '');
        echo '<p class="syntax-errors" style="color: red">' . $syntaxErrors . '</p>';
    }
?>
    <p>
        <a href="https://github.com/OpenConext/OpenConext-engineblock/wiki/Attribute-Manipulations">
            Documentation on attribute manipulations
        </a>
    </p>
    <textarea id="manipulation" name="entity_manipulation" rows="25" cols="80"><?php
        echo $session->getData('string', 'manipulation_code') ?
            $session->getData('string', 'manipulation_code') :
            htmlentities($entity->getManipulation());
        $session->setData('string', 'manipulation_code', '');
    ?></textarea>
    <div class="editor-container">
        <div id="manipulation_edit" class="editor"></div>
    </div>
    <script type="text/javascript">
        $(function() {
            var editor = ace.edit("manipulation_edit"),
                editorSession = editor.getSession(),
                textArea = $('textarea[name="entity_manipulation"]');

            textArea.hide();
            editorSession.setValue(textArea.val());
            editorSession.on('change', function(){
                textArea.val(editor.getSession().getValue());
            });

            editorSession.setMode("ace/mode/php");
            editor.setTheme("ace/theme/crimson_editor");

            return {
                editor: editor
            };
        });
    </script>
</div>

<div id="addmetadata">
    <h2><?php echo $this->t('tab_edit_entity_import_from_url'); ?></h2>
    <p>
    <?php
    if ($securityContext->isGranted('importmetadata', $entity)) {
        echo($this->t('add_metadata_from_url_desc') . '<br/>');
        echo('<input type="text" name="meta_url" size="70" />');
        echo('<input type="submit" name="add_metadata_from_url" value="'.$this->t('get_metadata').'" />');
    }
    ?>
    </p>

    <h2><?php echo $this->t('tab_edit_entity_import_xml'); ?></h2>
    <?php
    if ($securityContext->isGranted('importmetadata', $entity)) {
    ?>
    <table>
        <tr>
            <td style="width: 40px">XML:</td>
            <td><textarea name="meta_xml" cols="80" rows="20"></textarea></td>
        </tr>
    </table>

    <h2><?php echo $this->t('tab_edit_entity_import_json'); ?></h2>
    <table>
        <tr>
            <td style="width: 40px">JSON:</td>
            <td><textarea name="meta_json" cols="80" rows="3"></textarea></td>
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
if ($securityContext->isGranted('exportmetadata', $entity)) {
    echo '<a href="'. \SimpleSAML\Module::getModuleURL('janus/exportentity.php') .'?eid='. $entity->getEid()  .'&amp;revisionid='. $entity->getRevisionid() .'&amp;output=xhtml">'. $this->t('tab_edit_entity_export_metadata') .'</a><br /><br />';
} else {
    echo $this->t('error_no_access');
}
?>
</div>
<!-- VALIDATE TAB -->
<?php if ($securityContext->isGranted('validatemetadata', $entity)): ?>
<div id="validate">
    <h2>Metadata Validation</h2>
    <div id="MetadataValidation" class="<?php echo $entity->getEid() ?>">
        <div class="metadata-messages messages">
        </div>
        <script class="metadata-messages-template" type="text/x-jquery-tmpl">
            {{each Errors}}
            <p class="error">${$value}</p>
            {{/each}}
            {{each Warnings}}
            <p class="warning">${$value}</p>
            {{/each}}
        </script>
        <br />
        <div class="entity-metadata-validation">
        </div>
        <script class="entity-metadata-validation-template" type="text/x-jquery-tmpl">
            <table class="entity-metadata-table">
                <thead>
                    <tr>
                        <th>Entry</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    {{each Validations}}
                    <tr>
                        <td>${name}</td>
                        <td class="${status}">
                            {{if message.length == 1}}
                                {{each message}} <span class="message">${$value}</span> {{/each}}
                            {{else}}
                                <ul class="entity-metadata">
                                    {{each message}}
                                        <li>${$value}</li>
                                    {{/each}}
                                </ul>
                            {{/if}}</td>
                    </tr>
                    {{/each}}
                </tbody>
            </table>
        </script>
    </div>
    <h2>Certificate &amp; Endpoints validation</h2>
    <ul>
        <li class="entity-type">
            <ul>
                <li class="entity">
                    <div class="entity-messages messages">
                    </div>

                    <script class="messages-template" type="text/x-jquery-tmpl">
                        {{each Errors}}
                        <p class="error">${$value}</p>
                        {{/each}}
                        {{each Warnings}}
                        <p class="warning">${$value}</p>
                        {{/each}}
                    </script>

                    <table class="entity-information">
                        <tr>
                            <th>Entity ID</th>
                            <td>
                                <span class="entity-eid" style="display: none;"><?php echo $entity->getEid() ?></span>
                                <a href="<?php echo htmlspecialchars($entity->getEntityid()) ?>" class="entity-id">
                                    <?php echo htmlspecialchars($entity->getEntityid()) ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Metadata URL</th>
                            <td>
                                <a href="<?php echo htmlspecialchars($entity->getMetadataURL()) ?>">
                                    <?php echo htmlspecialchars($entity->getMetadataURL()) ?>
                                </a>
                            </td>
                        </tr>
                    </table>

                    <br />

                    <p class="header-25">
                        Signing Certificate
                    </p>

                    <div class="entity-certificate-representation">
                    </div>

                    <div class="entity-certificate-information">
                        <img class="loading-image" alt='Loading...' src="/<?php echo $this->data['baseurlpath']; ?>module.php/janus/resources/images/icons/spinner.gif" />
                    </div>

                    <script class="entity-certificate-information-template" type="text/x-jquery-tmpl">
                        <table>
                            <tr>
                                <th>Subject:</th>
                                <td>${Subject}</td>
                            </tr>
                            <tr>
                                <th>Starts / started:</th>
                                <td>${Starts_natural} (${Starts_relative})</td>
                            </tr>
                            <tr>
                                <th>Ends / ended:</th>
                                <td>${Ends_natural} (${Ends_relative})</td>
                            </tr>
                        </table>
                    </script>

                    <br />

                    <p class="header-25">
                        Endpoints
                    </p>
                    <img class="loading-image" alt='Loading...' src="/<?php echo $this->data['baseurlpath']; ?>module.php/janus/resources/images/icons/spinner.gif" />
                    <ul class="entity-endpoints">
                    </ul>

                    <script class="entity-endpoint-template" type="text/x-jquery-tmpl">
                        <li>
                            <h3>
                                <img style="display: inline;" height="24px" width="24px" src="/<?php echo $this->data['baseurlpath']; ?>module.php/janus/resources/images/icons/endpoint.png" alt="Endpoint" />
                                ${Name}
                            </h3>
                            <a href="${Url}">${Url}</a>

                            <div class="entity-endpoint-messages messages">
                            </div>

                            <div class="entity-endpoint-certificate-representation">
                            </div>

                            <div class="entity-endpoint-certificate-information">
                            </div>
                        </li>
                    </script>
                </li>
            </ul>
        </li>
    </ul>
</div>
<?php endif; ?>
<?php
    require __DIR__ . '/editentity/history.php';
?>
<hr />
<?php echo $this->t('tab_edit_entity_revision_note'); ?>: <input type="text" id="revision_note_input" name="revisionnote" class="revision_note" />
<input type="submit" name="formsubmit" id="master_submit" value="<?php echo $this->t('tab_edit_entity_save'); ?>" class="save_button" />
<!-- END CONTENT -->
</div>

</form>

<?php

$this->includeAtTemplateBase('includes/footer.php');
?>
