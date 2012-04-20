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
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');
$ssp_config = SimpleSAML_Configuration::getConfig();
$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/style.css" />' . "\n";
$this->data['head'] .= '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/styles/validate.css" />'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/swfupload.js"></script>' . "\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/jquery-asyncUpload-0.1.js"></script>' . "\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/json2-min.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/jquery.tmpl.min.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/datehelper.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/validate.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/validate.metadata.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/arp.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript">
$(document).ready(function() {
    $("#tabdiv").tabs();
    $("#tabdiv").tabs("select", 0);
    $("#historycontainer").hide();
    $("#showhide").click(function() {
        $("#historycontainer").toggle("slow");
	    if ($("#historycontainer p").size() > 0) {
            $("#historycontainer").load("history.php?eid=' . $this->data['entity']->getEid() . '");
        }
        return true;
    });
    $("#allowall_check").change(function(){
        if($(this).is(":checked")) {
            $(".remote_check_b").each( function() {
                this.checked = false;
            });
            $(".remote_check_w").each( function() {
                this.checked = false;
            });
            $("#allownone_check").removeAttr("checked");
        }
    });
    $("#allownone_check").change(function(){
        if($(this).is(":checked")) {
            $(".remote_check_w").each( function() {
                this.checked = false;
            });
            $(".remote_check_b").each( function() {
                this.checked = false;
            });
            $("#allowall_check").removeAttr("checked");
        }
    });
    $(".remote_check_b").change(function(){
        if($(this).is(":checked")) {
            $("#allowall_check").removeAttr("checked");
            $("#allownone_check").removeAttr("checked");
             $(".remote_check_w").each( function() {
                this.checked = false;
            });
        }
    });
    $(".remote_check_w").change(function(){
        if($(this).is(":checked")) {
            $("#allowall_check").removeAttr("checked");
            $("#allownone_check").removeAttr("checked");
             $(".remote_check_b").each( function() {
                this.checked = false;
            });
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
});
</script>';
$this->data['head'] .= '
<style>
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

// @todo Define these in some sort of form helper class
define('JANUS_FORM_ELEMENT_CHECKED', 'checked="checked"');
define('JANUS_FORM_ELEMENT_DISABLED', 'disabled="disabled"');
?>
<form id="mainform" method="post" action="<?php echo SimpleSAML_Utilities::selfURLNoQuery(); ?>">
<input type="hidden" name="eid" value="<?php echo $this->data['entity']->getEid(); ?>" />
<input type="hidden" name="revisionid" value="<?php echo $this->data['entity']->getRevisionid(); ?>" />

<div id="tabdiv">
<a href="<?php echo SimpleSAML_Module::getModuleURL('janus/index.php'); ?>"><?php echo $this->t('text_dashboard'); ?></a>
<h2><?php echo $this->t('edit_entity_header'), ' - ', $this->data['entity']->getEntityid() . ' ('. $this->t('tab_edit_entity_connection_revision') .' '. $this->data['entity']->getRevisionId() . ')'; ?></h2>

<!-- TABS -->
<ul>
    <li><a href="#entity"><?php echo $this->t('tab_edit_entity_connection'); ?></a></li>
    <?php
    if ($this->data['useblacklist'] || $this->data['usewhitelist']) {
        if($this->data['entity']->getType() === 'saml20-sp') {
            echo '<li><a href="#remoteentities">'. $this->t('tab_remote_entity_saml20-sp') .'</a></li>';
        } else {
            echo '<li><a href="#remoteentities">'. $this->t('tab_remote_entity_saml20-idp') .'</a></li>';
        }
    }
    if($this->data['entity']->getType() === 'saml20-idp') {
        echo '<li><a href="#disableconsent">' . $this->t('tab_disable_consent') . '</a></li>';
    }
    ?>
    <li><a href="#metadata"><?php echo $this->t('tab_metadata'); ?></a></li>
    <li><a href="#addmetadata"><?php echo $this->t('tab_import_metadata'); ?></a></li>
    <li><a href="#history"><?php echo $this->t('tab_edit_entity_history'); ?></a></li>
    <?php if($this->data['uiguard']->hasPermission('validatemetadata', $wfstate, $this->data['user']->getType())): ?>
    <li><a href="#validate" id="validate_link"><?php echo $this->t('tab_edit_entity_validate'); ?></a></li>
    <?php endif; ?>
    <li><a href="#export"><?php echo $this->t('tab_edit_entity_export'); ?></a></li>
</ul>
<!-- TABS END -->

<div id="history">
    <?php
    if($this->data['uiguard']->hasPermission('entityhistory', $wfstate, $this->data['user']->getType())) {

    $history_size = $this->data['mcontroller']->getHistorySize();

    if ($history_size === 0) {
        echo "Not history fo entity ". $this->data['entity']->getEntityId() . '<br /><br />';
    } else {
        echo '<h2>'. $this->t('tab_edit_entity_history') .'</h2>';
        if ($history_size > 10) {
            $history = $this->data['mcontroller']->getHistory(0, 10);
            echo '<p><a id="showhide">'. $this->t('tab_edit_entity_show_hide') .'</a></p>';
        } else {
            $history = $this->data['mcontroller']->getHistory();
        }

        $user = new sspmod_janus_User($janus_config->getValue('store'));
        $wstates = $janus_config->getArray('workflowstates');
        if(isset($current['name'][$this->getLanguage()])) {
            $curLang = $this->getLanguage();
        } else {
            $curLang = 'en';
        }
        foreach($history AS $data) {
            echo '<a href="?eid='. $data->getEid() .'&amp;revisionid='. $data->getRevisionid().'">'. $this->t('tab_edit_entity_connection_revision') .' '. $data->getRevisionid() .'</a>';
            if (strlen($data->getRevisionnote()) > 80) {
                echo ' - '. substr($data->getRevisionnote(), 0, 79) . '...';
            } else {
                echo ' - '. $data->getRevisionnote();
            }
            // Show edit user if present
            $user->setUid($data->getUser());
            if($user->load()) {
                echo ' - ' . $user->getUserid();
            }
            echo ' - ' . date('Y-m-d H:i', strtotime($data->getCreated()));
            if (isset($wstates[$data->getWorkflow()]['name'][$curLang])) {
                echo ' - ' . $wstates[$data->getWorkflow()]['name'][$curLang];
            } else {
                echo ' - ' . $data->getWorkflow();
            }
            echo '<br />';
        }

	echo '<div id="historycontainer"><p>';
	echo $this->t('tab_edit_entity_loading_revisions');
	echo '</p></div>';
    }
    } else {
        echo $this->t('error_no_access');
    }
?>
</div>
<!-- ENTITY CONNECTION -->
<div id="entity">
    <h2><?php
        echo $this->t('tab_edit_entity_connection') .' - '.
                $this->t('tab_edit_entity_connection_revision') .' '.
                $this->data['revisionid'] . ' - ' .
                date('Y-m-d H:i', strtotime($this->data['entity']->getCreated()));
    ?></h2>

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
                        <?php
                        if($this->data['uiguard']->hasPermission('changeentityid', $wfstate, $this->data['user']->getType())) {
                            echo' <td><input type="text" name="entityid" class="width_100" value="' . $this->data['entity']->getEntityid() . '" /></td>';
                        } else {
                            echo '<td>' . $this->data['entity']->getEntityid() . '</td>';
                        }
                        ?>
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
                        <td><?php
                            $current_arp = $this->data['entity']->getArp();
                            foreach($this->data['arp_list'] AS $arp) {
                                if($current_arp == $arp['aid']) {
                                    $current_arp_name = $arp['name'];
                                }
                            }
                            if ($this->data['uiguard']->hasPermission('changearp', $wfstate, $this->data['user']->getType())):
                            ?>

                            <select id="entity_arp_select" name="entity_arp" style="display: inline;">
                            <?php foreach($this->data['arp_list'] AS $arp): ?>
                                <option value="<?php echo $arp['aid'] ?>"
                                <?php if($current_arp == $arp['aid']) { echo 'selected="selected"'; } ?>
                                        >
                                    <?php echo $arp['name'] ?>
                                </option>
                            <?php endforeach; ?>
                            </select>

                            <?php else: ?>

                            <input type="hidden" name="entity_arp" value="<?php $current_arp; ?>" />
                            <?php echo $current_arp_name; ?>

                            <?php endif; ?>
                        </td>
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
                            echo '<a href="?eid='. $this->data['entity']->getEid() .'&amp;revisionid='. $this->data['entity']->getParent().'">r'. $this->data['entity']->getParent() .'</a>';
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
                                echo '<input type="hidden" name="entity_workflow" value="'. $wfstate .'" />';
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
                            echo '<select name="entity_type">';
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
                            echo '<input type="hidden" name="entity_type" value ="' . $this->data['entity_type'] . '" />';
                        }
                        ?>
                        </td>
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
// DISABLE CONSENT TAB
if($this->data['entity']->getType() == 'saml20-idp' || $this->data['entity']->getType() == 'shib13-idp') {
?>
<div id="disableconsent">
    <h2><?php echo $this->t('tab_disable_consent'); ?></h2>
    <p><?php echo $this->t('tab_disable_consent_help'); ?></p>
    <?php
    if($this->data['uiguard']->hasPermission('disableconsent', $wfstate, $this->data['user']->getType())) {
        foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {
            if(array_key_exists($remote_entityid, $this->data['disable_consent'])) {
                echo '<input class="consent_check" type="checkbox" name="add-consent[]" value="'. $remote_entityid. '" ' . JANUS_FORM_ELEMENT_CHECKED . ' />&nbsp;&nbsp;'. htmlentities($remote_data['name'][$this->getLanguage()]) .'<br />';
            } else {
                echo '<input class="consent_check" type="checkbox" name="add-consent[]" value="'. $remote_entityid. '" />&nbsp;&nbsp;'. htmlentities($remote_data['name'][$this->getLanguage()]) .'<br />';
            }
            echo '&nbsp;&nbsp;&nbsp;'. $remote_data['description'][$this->getLanguage()] .'<br />';
        }
    } else {
        foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {
            if(array_key_exists($remote_entityid, $this->data['disable_consent'])) {
                echo '<input class="remote_check_b" type="hidden" name="add-consent[]" value="'. $remote_entityid. '" />';
                echo '<input class="remote_check_b" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" ' . JANUS_FORM_ELEMENT_CHECKED . ' ' . JANUS_FORM_ELEMENT_DISABLED . ' />';
                echo '&nbsp;&nbsp;'. htmlentities($remote_data['name'][$this->getLanguage()]) .'<br />';
            } else {
                echo '<input class="remote_check_b" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" ' . JANUS_FORM_ELEMENT_DISABLED . ' />';
                echo '&nbsp;&nbsp;'. htmlentities($remote_data['name'][$this->getLanguage()]) .'<br />';
            }
            echo '&nbsp;&nbsp;&nbsp;'. htmlentities($remote_data['description'][$this->getLanguage()]) .'<br />';
        }
    }
    ?>
</div>
<?php
}
// DISABLE CONSENT TAB - END
?>

<?php if ($this->data['useblacklist'] || $this->data['usewhitelist']) { ?>
<div id="remoteentities">
   <?php
        define('JANUS_ALLOW_BLOCK_REMOTE_ENTITY', $this->data['uiguard']->hasPermission('blockremoteentity', $wfstate, $this->data['user']->getType()));

        if ($this->data['useblacklist']) {
            $checked = '';
            if($this->data['entity']->getAllowedAll() == 'yes') {
                $checked = JANUS_FORM_ELEMENT_CHECKED;
            }

            if(JANUS_ALLOW_BLOCK_REMOTE_ENTITY) {
                echo '<input id="allowall_check" type="checkbox" name="allowall" value="' . $this->data['entity']->getAllowedAll() . '" ' . $checked . ' /> ' . $this->t('tab_remote_entity_allowall');
            } else {
                echo '<input id="allowall_check" type="hidden" name="allowall" value="' . $this->data['entity']->getAllowedAll() . '" '. $checked . ' />';
                echo '<input type="checkbox" name="allowall_dummy" value="' . $this->data['entity']->getAllowedAll() . '" ' . $checked . ' ' . JANUS_FORM_ELEMENT_DISABLED. ' /> ' . $this->t('tab_remote_entity_allowall');
            }
        }

        if ($this->data['usewhitelist']) {
            $checked = '';
            if($this->data['entity']->getAllowedAll() != 'yes' && count($this->data['allowed_entities'])==0 && count($this->data['blocked_entities'])==0) {
                $checked = JANUS_FORM_ELEMENT_CHECKED;
            }

            if(JANUS_ALLOW_BLOCK_REMOTE_ENTITY) {
                echo '<br/><input id="allownone_check" type="checkbox" name="allownone" value="1" ' . $checked . ' /> ' . $this->t('tab_remote_entity_allownone');
            } else {
                echo '<input id="allownone_check" type="hidden" name="allownone" value="not used" '. $checked . ' />';
                echo '<br/><input type="checkbox" name="allownone_dummy" value="not used" ' . $checked . ' ' . JANUS_FORM_ELEMENT_DISABLED. ' /> ' . $this->t('tab_remote_entity_allownone');
            }
        }
    ?>

      <?php if ($this->data['useblacklist']) { ?>


        <h2><?php echo $this->t('tab_remote_entity_'. $this->data['entity']->getType()); ?> <?php echo $this->t('tab_remote_entity_blacklist'); ?></h2>
        <p><?php echo $this->t('tab_remote_entity_help_blacklist_'. $this->data['entity']->getType()); ?></p>
        <?php

        if(JANUS_ALLOW_BLOCK_REMOTE_ENTITY) {

            echo '<hr />';

            foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {
                if(array_key_exists($remote_entityid, $this->data['blocked_entities'])) {
                    echo '<input class="remote_check_b" type="checkbox" name="addBlocked[]" value="'. $remote_entityid. '" ' . JANUS_FORM_ELEMENT_CHECKED . ' />&nbsp;&nbsp;'. htmlentities($remote_data['name'][$this->getLanguage()]) .'<br />';
                } else {
                    echo '<input class="remote_check_b" type="checkbox" name="addBlocked[]" value="'. $remote_entityid. '" />&nbsp;&nbsp;'. htmlentities($remote_data['name'][$this->getLanguage()]) .'<br />';
                }
                echo '&nbsp;&nbsp;&nbsp;'. htmlentities($remote_data['description'][$this->getLanguage()]) .'<br />';
            }
        } else {
            foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {
                if(array_key_exists($remote_entityid, $this->data['blocked_entities'])) {
                    echo '<input class="remote_check_b" type="hidden" name="addBlocked[]" value="'. $remote_entityid. '" />';
                    echo '<input class="remote_check_b" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" ' . JANUS_FORM_ELEMENT_CHECKED . ' ' . JANUS_FORM_ELEMENT_DISABLED . ' />&nbsp;&nbsp;'. htmlentities($remote_data['name'][$this->getLanguage()]) .'<br />';
                } else {
                    echo '<input class="remote_check_b" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" ' . JANUS_FORM_ELEMENT_DISABLED . ' />&nbsp;&nbsp;'. htmlentities($remote_data['name'][$this->getLanguage()]) .'<br />';
                }
                echo '&nbsp;&nbsp;&nbsp;'. htmlentities($remote_data['description'][$this->getLanguage()]) .'<br />';
            }
        }
    }
    if ($this->data['usewhitelist']) { ?>

        <h2><?php echo $this->t('tab_remote_entity_'. $this->data['entity']->getType()); ?> <?php echo $this->t('tab_remote_entity_whitelist'); ?></h2>
        <p><?php echo $this->t('tab_remote_entity_help_whitelist_'. $this->data['entity']->getType()); ?></p>
        <?php

        if(JANUS_ALLOW_BLOCK_REMOTE_ENTITY) {
            // Access granted to block remote entities
            echo '<hr />';

            foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {
                if(array_key_exists($remote_entityid, $this->data['allowed_entities'])) {
                    echo '<input class="remote_check_w" type="checkbox" name="addAllowed[]" value="'. $remote_entityid. '" ' . JANUS_FORM_ELEMENT_CHECKED . ' />&nbsp;&nbsp;'. htmlentities($remote_data['name'][$this->getLanguage()]) .'<br />';
                } else {
                    echo '<input class="remote_check_w" type="checkbox" name="addAllowed[]" value="'. $remote_entityid. '" />&nbsp;&nbsp;'. htmlentities($remote_data['name'][$this->getLanguage()]) .'<br />';
                }
                echo '&nbsp;&nbsp;&nbsp;'. htmlentities($remote_data['description'][$this->getLanguage()]) .'<br />';
            }
        } else {

            foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {
                if(array_key_exists($remote_entityid, $this->data['allowed_entities'])) {
                    echo '<input class="remote_check_w" type="hidden" name="addAllowed[]" value="'. $remote_entityid. '" />';
                    echo '<input class="remote_check_w" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" ' . JANUS_FORM_ELEMENT_CHECKED . ' ' . JANUS_FORM_ELEMENT_DISABLED . ' />&nbsp;&nbsp;'. htmlentities($remote_data['name'][$this->getLanguage()]) .'<br />';
                } else {
                    echo '<input class="remote_check_w" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" ' . JANUS_FORM_ELEMENT_DISABLED . ' />&nbsp;&nbsp;'. htmlentities($remote_data['name'][$this->getLanguage()]) .'<br />';
                }
                echo '&nbsp;&nbsp;&nbsp;'. htmlentities($remote_data['description'][$this->getLanguage()]) .'<br />';
            }
        }
    } ?>
</div>
<?php } ?>
<!-- TAB METADATA -->
<div id="metadata">
    <h2>Metadata</h2>

    <script type="text/javascript">
        var metadata = new Array();

        metadata["NULL"] = '';

        <?php
        foreach($this->data['metadatafields'] AS $mf) {
            if(isset($mf->type)) {
                echo 'metadata["'. $mf->name .'"] = new Array();';
                echo 'metadata["'. $mf->name .'"]["type"] = "'. $mf->type .'";';
            } else {
                // Skip this metadata field if 'type' is not set
                continue;
            }
            if(isset($mf->default)) {
                echo 'metadata["'. $mf->name .'"]["default"] = "'. $mf->default .'";';
            } else {
                echo 'metadata["'. $mf->name .'"]["default"] = "";';
            }
            if(isset($mf->maxsize)) {
                echo 'metadata["'. $mf->name .'"]["maxsize"] = "'. $mf->maxsize .'";';
            }
            if(isset($mf->filetype)) {
                echo 'metadata["'. $mf->name .'"]["filetype"] = "'. $mf->filetype .'";';
            }
            if(isset($mf->validate)) {
                echo 'metadata["'. $mf->name .'"]["validate"] = "'. $mf->validate .'";';
            }
            if(isset($mf->select_values) && is_array($mf->select_values) && !empty($mf->select_values)) {
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
                    if(metadata[index]["default"] == true) {
                        var checkedtrue = '<?php echo JANUS_FORM_ELEMENT_CHECKED;?>';
                        var checkedfalse = '';
                    } else {
                        var checkedfalse = '<?php echo JANUS_FORM_ELEMENT_CHECKED;?>';
                        var checkedtrue = '';
                    }
                    $('<input clas="metadata_checkbox" type="checkbox" value="true" name="meta_value[' + index + '-TRUE]" onclick="changeFalse(this);" ' + checkedtrue + ' />').appendTo(makker);
                    $('<input class="display_none" type="checkbox" value="false", name="meta_value[' + index + '-FALSE]" ' + checkedfalse + '" />').appendTo(makker);
                    break;
                case 'text':
                    if(metadata[index]["validate"]) {
                        $('<input type="text" name="meta_value[' + index + ']" class="width_100" value="' + metadata[index]["default"] + '" onfocus="this.value=\'\';" onkeyup="validateInput(this, \'' + metadata[index]["validate"] + '\');" onblur="$(this).removeAttr(\'onfocus\');" />').appendTo(makker);
                    } else {
                        $('<input type="text" name="meta_value[' + index + ']" class="width_100" value="' + metadata[index]["default"] + '" onfocus="this.value=\'\';" onblur="$(this).removeAttr(\'onfocus\');" />').appendTo(makker);
                    }
                    break;
                case 'select':
                    if(metadata[index]["select_values"] !== "undefined" &&
                    	typeof(metadata[index]["select_values"]) == "object") {
                    	var default_value = null;
                        if(metadata[index]["default"] !== "undefined") {
                            default_value = metadata[index]["default"];
                        }
                        $('<select name="meta_value[' + index + ']">').appendTo(makker);
                        $('<\/select>').appendTo(makker);
                        select_html = document.getElementsByName('meta_value[' + index + ']')[0];
                        select_values = metadata[index]["select_values"];
                        for (i in select_values) {
                            if(select_values[i] == default_value) {
                                select_html.options[select_html.length] = new Option(select_values[i], select_values[i], "defaultSelected");
                            } else {
                                select_html.options[select_html.length] = new Option(select_values[i], select_values[i]);
                            }
                        }
                	}
                    break;
                case 'file':
                    $('<input type="file" name="meta_value[' + index + ']" id="meta_value[' + index + ']" />').appendTo(makker);
                    var config = {
                        upload_url: '/<?php echo $this->data['baseurlpath']; ?>module.php/janus/AJAXRequestHandler.php',
                        flash_url: '/<?php echo $this->data['baseurlpath']; ?>module.php/janus/resources/scripts/swfupload.swf',
                        button_image_url: '/<?php echo $this->data['baseurlpath']; ?>module.php/janus/resources/scripts/blankButton.png',
                        existingFilename: metadata[index]["default"],
                        disableDuringUpload: "INPUT[type=submit]",
                        button_text: "<font face=\"Arial\" size=\"13pt\"><?php echo $this->t('choose_file'); ?></font>",
                        post_params: {
                            "PHPSESSID" : "<?php echo $_COOKIE['PHPSESSID']; ?>",
                            "SimpleSAMLAuthToken" : "<?php echo isset($_COOKIE['SimpleSAMLAuthToken']) ? $_COOKIE['SimpleSAMLAuthToken'] : ''; ?>",
                            "func" : "uploadFile",
                            "eid" : "<?php echo $this->data['entity']->getEid(); ?>",
                            "index" : "meta_value[" + index + "]"
                        }
                    };

                    if(metadata[index]["filetype"] !== undefined) {
                        config.file_types = metadata[index]["filetype"];
                    }

                    if(metadata[index]["maxsize"] !== undefined) {
                        config.file_size_limit = metadata[index]["maxsize"];
                    }

                    $("input:file[name=meta_value[" + index + "]]").makeAsyncUploader(config);
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
            newelm.find("input").attr("value", "");
            newelm.find("span").text("");
            newelm.insertBefore("#meta_delim");
        }


        function delete_metadata(metadata_name) {
            if(confirm('<?php echo $this->t('delete_metadata_question'); ?>')) {
                var input_delete_metadata = "delete-matadata-"+metadata_name;
                $("#"+input_delete_metadata).attr('checked', 'checked');
                $('#mainform').trigger('submit');
            }
        }

        /**
         * Pre-add a metadata field for the user.
         * @param String metadata_name
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

        // change hidden checkbox to post false
        function changeFalse(elm) {
            if($(elm).is(":checked")) {
                $(elm).next().removeAttr("checked");
            } else {
                $(elm).next().attr("checked", "checked");
            }
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

    // Check for and remove metadata without a definition
    if (isset($this->data['metadata']) && !empty($this->data['metadata'])) {
        $undefinedMetadataFields = array();
        foreach($this->data['metadata'] AS $index => $data) {
            if (!isset($this->data['metadatafields'][$data->getKey()])) {
                $undefinedMetadataFields[] = $data;
                unset($this->data['metadata'][$index]);
            }
        }
        if (!empty($undefinedMetadataFields)) {
            echo '<h3 style="color: red;">Metadata found without a definition?</h3>';
            echo "<table><thead><th>Entry</th><th>Value</th></thead><tbody>";
            foreach ($undefinedMetadataFields as $undefinedMetadataField) {
                echo '<tr>
                        <td style="padding-right: 1em">' .
                            $undefinedMetadataField->getKey() .
                       '</td><td>' .
                            $undefinedMetadataField->getValue() .
                            '<input type="checkbox"
                                    class="display_none"
                                    value="'. $undefinedMetadataField->getKey() .'"
                                    id="delete-matadata-'. $undefinedMetadataField->getKey() .'"
                                    name="delete-metadata[]" />';
                       '</td>';
                if ($deletemetadata) {
                    $metadata_key_parsed = str_replace(array(':', '.', '#') , array('\\\\:', '\\\\.', '\\\\#'), $undefinedMetadataField->getKey());
                    echo '<td width="100px" align="right" class="metadata_control">
                            <b><span></span></b>&nbsp;
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

    $metadatafields = $this->data['metadatafields'];
    $metadata       = $this->data['metadata'];

    if (!$metadata) {
        echo "<p>No metadata for entity ". $this->data['entity']->getEntityId() . '</p>';
    }

    echo '<table border="0" class="width_100">';
    echo   '<tr>';
    echo      '<td width="20%"><h3>'. $this->t('tab_edit_entity_entry') .'</h3></td>';
    echo      '<td><h3>'. $this->t('tab_edit_entity_value') .'</h3></td>';
    echo   '</tr>';

    if ($metadata) {
        $i = 0;
        foreach($metadata AS $index => $data) {
            if (!isset($this->data['metadatafields'][$data->getKey()])) {
                echo '<div style="color: red; font-size: large;">Metadata value found without definition:</div>';
                echo "<dl><dt>" . $data->getKey() . "</dt><dd>" . $data->getValue() . "</dd></dl>";
                continue;
            }
            $metadata_field = $this->data['metadatafields'][$data->getKey()];
            echo '<tr class="'. ($i % 2 == 0 ? 'even' : 'odd'). '"  onmouseout="$(\'#metadata-desc-' . strtr($data->getkey(), array(':' => '\\\:', '.' => '\\\.')) . '\').hide();" onmouseover="$(\'#metadata-desc-' . strtr($data->getkey(), array(':' => '\\\:', '.' => '\\\.')) . '\').show();">';
            echo '<td>'. $data->getkey() . '</td>';
            echo '<td>';

            switch($metadata_field->type) {
                case 'text':
                    $validate = isset($metadata_field->validate) ? 'onkeyup="validateInput(this, \'' . $metadata_field->validate . '\');"' : '';
                    echo '<input class="width_100" type="text" name="edit-metadata-'. $data->getKey()  .'" value="'. $data->getValue()  .'" ' . $modifymetadata . ' ' . $validate . ' />';
                    break;
                case 'boolean':
                    if($data->getValue() == true) {
                        $checked_true = JANUS_FORM_ELEMENT_CHECKED;
                        $checked_false = '';
                    } else {
                        $checked_false = JANUS_FORM_ELEMENT_CHECKED;
                        $checked_true = '';
                    }
                    if($modifymetadata == 'readonly="readonly"') {
                        echo '<input value="true" type="checkbox" class="metadata_checkbox" name="edit-metadata-'. $data->getKey()  .'-TRUE" '. $checked_true . ' ' . JANUS_FORM_ELEMENT_DISABLED . ' onclick="changeFalse(this);" />';
                    } else {
                        echo '<input value="true" type="checkbox" class="metadata_checkbox" name="edit-metadata-'. $data->getKey()  .'-TRUE" '. $checked_true .' onclick="changeFalse(this);" />';
                    }
                    echo '<input value="false" type="checkbox" class="display_none" name="edit-metadata-'. $data->getKey()  .'-FALSE" '. $checked_false .' ' . $modifymetadata . ' />';
                    break;
                case 'select':
                    if(isset($metadata_field->select_values) &&
                       is_array($metadata_field->select_values)) {
                        $default = null;
                        if(isset($metadata_field->default)) {
                            $default = $metadata_field->default;
                        }
                        $select_values = $metadata_field->select_values;
                        $actual_value = $data->getValue();
                        echo '<select name="edit-metadata-'. $data->getKey()  .'">';
                        foreach($select_values as $select_value) {
                            echo '<option value="'.$select_value.'"';
                            if($select_value == $actual_value ||
                               (empty($actual_value) && $select_value == $default)) {
                                echo 'selected="selected"';
                               }
                            echo '>'.$select_value.'</option>';
                        }
                        echo '</select>';
                        break;
                    }
                case 'file':
                    echo '<input type="file" name="edit-metadata-'. $data->getKey()  .'" id="edit-metadata-'. $data->getKey()  .'" />';
                    echo '<script type="text/javascript">
                    <!--
                    $("input:file[name=edit-metadata-'. $data->getKey() .']").makeAsyncUploader({
                	    upload_url: "/'. $this->data['baseurlpath'] .'module.php/janus/AJAXRequestHandler.php",
                        flash_url: "/'. $this->data['baseurlpath'] .'module.php/janus/resources/scripts/swfupload.swf",
                        button_image_url: "/'. $this->data['baseurlpath'] .'module.php/janus/resources/scripts/blankButton.png",
                        existingFilename: "<a href=\"/'. $this->data['baseurlpath'] .'module.php/janus/upload/' . $this->data['entity']->getEid() . '/' . $data->getValue() . '\" target=\"_blank\">'. $data->getValue() .'</a>",
                        disableDuringUpload: "INPUT[type=submit]",
                        button_text: "<font face=\"Arial\" size=\"13pt\">'. $this->t('choose_file') .'</font>",';
                    if(isset($metadata_field->maxsize)) {
                        echo 'file_size_limit: "' . $metadata_field->maxsize . '",' . "\n";
                    }
                    if(isset($metadata_field->filetype)) {
                        echo 'file_types: "' . $metadata_field->filetype . '",' . "\n";
                    }
                    echo 'post_params: {
                            "PHPSESSID" : "'. $_COOKIE['PHPSESSID'] .'",
                            "SimpleSAMLAuthToken" : "'. (isset($_COOKIE['SimpleSAMLAuthToken'])?$_COOKIE['SimpleSAMLAuthToken']:'') .'",
                            "func" : "uploadFile",
                            "eid" : "'. $this->data['entity']->getEid() .'",
                            "index" : "edit-metadata-'. $data->getKey() .'"
                        }
                    });
                    -->
                    </script>';
                    break;
                default:
                    $validate = isset($metadata_field->validate) ? 'onkeyup="validateInput(this, \'' . $metadata_field->validate . '\');"' : '';
                    echo '<input class="width_100" type="text" name="edit-metadata-'. $data->getKey()  .'" value="'. $data->getValue()  .'" ' . $modifymetadata . ' ' . $validate . ' />';
            }

            unset($metadatafields[$data->getKey()]);

            echo '<input type="checkbox" class="display_none" value="'. $data->getKey() .'" id="delete-matadata-'. $data->getKey() .'" name="delete-metadata[]" />';
            echo '</td>';
            if($deletemetadata && !(isset($metadata_field->required) ? $metadata_field->required : false)) {
                $metadata_key_parsed = str_replace(array(':', '.', '#') , array('\\\\:', '\\\\.', '\\\\#'), $data->getKey());
                echo '<td width="100px" align="right" class="metadata_control">
                        <b><span></span></b>&nbsp;
                        <img onclick="javascript:{delete_metadata(\''. $metadata_key_parsed .'\');}"
                             src="resources/images/pm_delete_16.png"
                             alt="'. strtoupper($this->t('admin_delete')) .'"
                             style="display: inline;" />
                      </td>';
            } else {
                echo '<td align="right" width="100px" class="metadata_control"><b><span></span></b></td>';
            }
            echo '</tr>';
            $i++;
        }
    }

    if ($this->data['uiguard']->hasPermission('addmetadata', $wfstate, $this->data['user']->getType())) {
        echo '<tr class="new_metadata_field">';
        echo '  <td>';
        echo '      <select name="meta_key" onchange="changeId(this);" class="metadata_selector">';
        echo '          <option value="NULL">-- '. $this->t('tab_edit_entity_select') .' --</option>';
        foreach($metadatafields AS $mf) {
            echo '      <option value="', $mf->name, '">', $mf->name, '</option>';
        }
        echo '      </select>';
        echo '  </td>';
        echo '  <td>';
        echo '  </td>';
        echo '  <td align="right" width="100px" class="metadata_control"><b><span></span></b>';
        echo '  </td>';
        echo '</tr>';
        echo '<tr id="meta_delim">';
        echo '  <td height="70px" style="vertical-align: top;">';
        echo '      <img onclick="addMetadataInput(this);" src="resources/images/pm_plus_16.png" alt="Plus" />';
        echo '  </td>';
        echo '  <td colspan="2">';
        $available_languages = $ssp_config->getArray('language.available');
        foreach($available_languages AS &$alang) {
            $alang = '/:' . $alang . '/';
        }
        foreach($this->data['metadatafields'] AS $mf) {
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

        echo '<script type="text/javascript">';
        /**
         * @var sspmod_janus_Metadatafield $definition
         */
        foreach($this->data['metadatafields'] as $key => $definition) {
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

    echo '</table>';
    ?>
</div>

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
if($this->data['uiguard']->hasPermission('exportmetadata', $wfstate, $this->data['user']->getType())) {
    echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/exportentity.php') .'?eid='. $this->data['entity']->getEid()  .'&amp;revisionid='. $this->data['entity']->getRevisionid() .'&amp;output=xhtml">'. $this->t('tab_edit_entity_export_metadata') .'</a><br /><br />';
} else {
    echo $this->t('error_no_access');
}
?>
</div>
<!-- VALIDATE TAB -->
<?php if($this->data['uiguard']->hasPermission('validatemetadata', $wfstate, $this->data['user']->getType())): ?>
<div id="validate">
    <h2>Metadata Validation</h2>
    <div id="MetadataValidation" class="<?php echo $this->data['entity']->getEid() ?>">
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
    <h2>Certificate & Endpoints validation</h2>
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
                                <span class="entity-eid" style="display: none;"><?php echo $this->data['entity']->getEid() ?></span>
                                <a href="<?php echo $this->data['entity']->getEntityid() ?>" class="entity-id">
                                    <?php echo $this->data['entity']->getEntityid() ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Metadata URL</th>
                            <td>
                                <a href="<?php echo $this->data['entity']->getMetadataURL() ?>">
                                    <?php echo $this->data['entity']->getMetadataURL() ?>
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
                                <img style="display: inline;" height="24px" width="24px" src="/<?php echo $this->data['baseurlpath']; ?>module.php/janus/resources/images/icons/endpoint.png" alt="" />
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
<hr />
<?php echo $this->t('tab_edit_entity_revision_note'); ?>: <input type="text" name="revisionnote" class="revision_note" />
<input type="submit" name="formsubmit" id="master_submit" value="<?php echo $this->t('tab_edit_entity_save'); ?>" class="save_button"/>
<!-- END CONTENT -->
</div>

</form>

<?php

$this->includeAtTemplateBase('includes/footer.php');
?>
