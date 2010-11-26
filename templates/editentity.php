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
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');
$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/style.css" />' . "\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/swfupload.js"></script>' . "\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/jquery-asyncUpload-0.1.js"></script>' . "\n";
$this->data['head'] .= '<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'module.php/janus/resources/scripts/json2-min.js"></script>'."\n";
$this->data['head'] .= '<script type="text/javascript">
function var_dump(obj) {
    if(typeof obj == "object") {
        return "Type: "+typeof(obj)+((obj.constructor) ? "\nConstructor: "+obj.constructor : "")+"\nValue: " + obj;
    } else {
        return "Type: "+typeof(obj)+"\nValue: "+obj;                                                                                                              }
}

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

    /*
    $(":input").bind("change", function(e) {
        blinker(5);
    });
    */
    // ARP edit
    $("#arp_edit_close").click(function(){  
        disablePopup();  
    });  
    $("#arp_edit_close").hover(
        function () {
            //$(this).css("text-decoration", "underline");
            $(this).css("font-weight", "bold");
        }, 
        function () {
            //$(this).css("text-decoration", "none");
            $(this).css("font-weight", "normal");
        } 
    );
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
    if ($this->data['useblacklist'] || $this->data['usewhitelist']) {
        if($this->data['entity']->getType() === 'saml20-sp') {
            echo '<li><a href="#remoteentities">'. $this->t('tab_remote_entity_saml20-sp') .'</a></li>';
        } else {
            echo '<li><a href="#remoteentities">'. $this->t('tab_remote_entity_saml20-idp') .'</a></li>';
        }
        if($this->data['entity']->getType() === 'saml20-idp') {
            echo '<li><a href="#disableconsent">' . $this->t('tab_disable_consent') . '</a></li>';
        }
    }
    ?>
    <li><a href="#metadata"><?php echo $this->t('tab_metadata'); ?></a></li>
    <li><a href="#addmetadata"><?php echo $this->t('tab_import_metadata'); ?></a></li>
    <li><a href="#history"><?php echo $this->t('tab_edit_entity_history'); ?></a></li>
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
        foreach($history AS $data) {
            echo '<a href="?eid='. $data->getEid() .'&revisionid='. $data->getRevisionid().'">'. $this->t('tab_edit_entity_connection_revision') .' '. $data->getRevisionid() .'</a>';
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
            echo '<br>';
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
    <script>
    var popupStatus = 0; 

    //loading popup with jQuery magic!  
    function loadPopup(){  
        //loads popup only if it is disabled  
        if(popupStatus==0){  
            $("#backgroundPopup").css({  
                "opacity": "0.7"  
            });  
            $("#backgroundPopup").fadeIn("slow");
            $("#arp_edit").fadeIn("slow");  
            //$("#popupContact").fadeIn("slow");  
            popupStatus = 1;  
        }
    }

    //disabling popup with jQuery magic!  
    function disablePopup(){  
        //disables popup only if it is enabled  
        if(popupStatus==1){  
            $("#backgroundPopup").fadeOut("slow");  
            $("#arp_edit").fadeOut("slow");  
            popupStatus = 0;  
        }  
    } 
       
    function centerPopup(){  
        //request data for centering  
        var windowWidth = document.documentElement.clientWidth;  
        var windowHeight = document.documentElement.clientHeight;  
        var popupHeight = $("#arp_edit").height();  
        var popupWidth = $("#arp_edit").width();  
        //centering  
        $("#arp_edit").css({  
            "position": "absolute",  
                "top": windowHeight/2-popupHeight/2,  
                "left": windowWidth/2-popupWidth/2  
        });  
        //only need force for IE6  
        $("#backgroundPopup").css({  
            "height": windowHeight  
        });  
    }  

    // Global array for keeping attributes
    var attributes = new Array();
    // Variable for timer for auto saving
    var t;

    function fetchARP(aid) {
        if(aid == 0) {
            disablePopup();
            return;
        }
        $.post(
            "AJAXRequestHandler.php",
        {
            func: "getARP",
                aid: aid
        },
        function(data) {
            attributes = new Array();
            for(x in data["attributes"]) {
                attributes.push(data["attributes"][x]);
            }
            $("#edit_arp_table").show();
            $("#arp_id").val(data["aid"]);
            $("#arp_name").val(data["name"]);
            $("#arp_name_headline").html(data["name"]);
            $("#arp_description").val(data["description"]);
            $("tr[id^='attr_row_']").remove();
            for(x in data["attributes"]) {
                $("#arp_attributes").prepend('<tr id="attr_row_' + data["attributes"][x] + '"><td>' + data["attributes"][x] + '</td><td><img src="resources/images/pm_delete_16.png" alt="Delete" onClick="setSavestatus(false); deleteAttribute(\'' + data["attributes"][x] + '\')" style="cursor: pointer;"></td></tr>');
            }
            $("tr[id^='attr_row_']:even").css("background-color", "#EEEEEE");
            setSavestatus(true); 
        },
            "json"
        );
    }

    function saveARP() {
        $.post(
            "AJAXRequestHandler.php",
            {
            func: "setARP",
                aid: $("#arp_id").val(),
                name: $("#arp_name").val(),
                description: $("#arp_description").val(),
                'attributes[]': attributes
            },
            function(data) {
                if(data["status"] == "success") {
                    if($("#arp_id").val() == '') {    
                        $("#arp_id").val(data["aid"]);
                        $("#entity_arp_select").append('<option value="' + $('#arp_id').val() + '"></option>');
                        $("#entity_arp_select").val($('#arp_id').val()); 
                        fetchNewARP();
                    } else {
                        $("#arp_id").val(data["aid"]);
                    }
                    setSavestatus(true);
                } else {
                    alert("NOT SAVE");
                }
            },
            "json"
        );
    }

    function addAttribute(elm) {
        if($.inArray($(elm).val(), attributes) == -1) {
            attributes.push($(elm).val());
            $("#attribute_select_row").before('<tr id="attr_row_' + $(elm).val() + '"><td>' + $(elm).val() + '</td><td><img src="resources/images/pm_delete_16.png" alt="Delete" onClick="setSavestatus(false); deleteAttribute(\'' + $(elm).val() + '\')" style="cursor: pointer;"></td></tr>');
            saveARP();
            $("tr[id^='attr_row_']:even").css("background-color", "#EEEEEE");
        }
    }

    function deleteAttribute(elm) {
        $("#attr_row_" + elm).remove();
        attributes.splice(attributes.indexOf(elm),1);
        saveARP();
        $("tr[id^='attr_row_']").css("background-color", "#FFFFFF");
        $("tr[id^='attr_row_']:even").css("background-color", "#EEEEEE");
    }

    function updateName() {
        $("#entity_arp_select option:selected").each(function(){
            $(this).text($("#arp_name").val());
        });
    }

    function newARP() {
        $('#arp_id').val('');
        $('#arp_name').val('');
        $('#arp_desription').val('');
        saveARP(); 
    }

    function fetchNewARP() {
        var id = $('#arp_id').val();
        fetchARP(id);
        $("#arp_add").before('<tr id="arp_row_' + id +  '"><td></td><td><img src="resources/images/pencil.png" alt="Edit" width="16" height="16" onclick="fetchARP(' + id + ');"></td><td><img src="resources/images/pm_delete_16.png" alt="Delete" width="16" height="16" onclick="deleteARP(' + id +');"></td></tr>');
    }

    function deleteARP(aid) {
        if(window.confirm("Delete ARP")) {
            $.post(
                "AJAXRequestHandler.php",
        {
            func: "deleteARP",
                aid: aid
        },
        function(data) {
            if(data["status"] == "success") {
                $("#arp_row_" + aid).remove();
                $("tr[id^=\'arp_row_\']").css("background-color", "#FFFFFF");
                $("tr[id^=\'arp_row_\']:even").css("background-color", "#EEEEEE");
            } else {
                alert("Error: Not deleted");
            }
        },
            "json"
        );
        }
    }

    function setSavestatus(val) {
        if(val == true) {
            $("#arp_save_status").html('Saved');
            $("#arp_save_status").css('color', 'green');
        } else {
            $("#arp_save_status").html('Not saved'); 
            $("#arp_save_status").css('color', '#CCCCCC');
        }
    }
    </script>
    <div id="backgroundPopup" style="  
display:none;  
position:fixed;  
_position:absolute; /* hack for internet explorer 6*/  
height:100%;  
width:100%;  
top:0;  
left:0;  
background:#000000;  
border:1px solid #cecece;  
z-index:1;  
"></div>
    <div id="arp_edit" style="
display:none;  
position:fixed;  
_position:absolute; /* hack for internet explorer 6*/  
//height:384px;  
width:408px;  
background:#FFFFFF;  
border:2px solid #cecece;  
z-index:2;  
padding:12px;  
font-size:13px;  
    ">
    <?php    
    echo '<input type="hidden" id="arp_id">'; 
    echo '<table border="0" class="width_100" id="edit_arp_table" style="border: 1px solid #CCCCCC;">';
    echo '<tr>';
    echo '<td colspan="2">';
    echo '<span style="float: right; font-size: 10px; cursor: pointer;" id="arp_edit_close">[CLOSE]</span></h3>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td><b>Name</b></td>';
    echo '<td><input type="text" name="arp_name" id="arp_name" onKeypress="clearTimeout(t); setSavestatus(false); t = setTimeout(\'saveARP(); updateName()\', 800);"></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td><b>Description</b></td>';
    echo '<td><input type="text" name="arp_description" id="arp_description" onKeypress="clearTimeout(t); setSavestatus(false); t = setTimeout(\'saveARP()\', 800);"></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td valign="top"><b>Attribute</b></td>';
    echo '<td>';
        echo '<table id="arp_attributes" border="0">';
        echo '<tr id="attribute_select_row"><td>';
        echo '<select id="attribute_select" name="attribute_key" onChange="setSavestatus(false); addAttribute(this);" class="attribute_selector">';
        echo '<option value="NULL">-- '. $this->t('tab_edit_entity_select') .' --</option>';
        foreach($this->data['attribute_fields'] AS $attribute_key => $attribute_val) {
            echo '<option value="', $attribute_key, '">', $attribute_key, '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    echo '<span id="arp_save_status" style="color: #CCCCCC; float: right"></span>';
    ?>

    </div>
    
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
            <?php
            if($this->data['uiguard']->hasPermission('changeentityid', $wfstate, $this->data['user']->getType())) {
                echo' <td><input type="text" name="entityid" class="width_100" value="' . $this->data['entity']->getEntityid() . '"></td>';
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
            <td>
                <table border="0">
                    <tr>
                        <td>
            <?php    
            $current_arp = $this->data['entity']->getArp();
            if($this->data['uiguard']->hasPermission('changearp', $wfstate, $this->data['user']->getType())) {
                 echo '<select id="entity_arp_select" name="entity_arp" style="display: inline;">';
                foreach($this->data['arp_list'] AS $arp) {
                    if($current_arp == $arp['aid']) {
                        echo '<option value="'. $arp['aid'] .'" selected="selected">'. $arp['name'] .'</option>';
                    } else {
                        echo '<option value="'. $arp['aid'] .'">'. $arp['name'] .'</option>';
                    }
                }
                echo '</select>';
                echo '</td>';
                echo '<td>';
                // Show edit and new link if access is granted
                if($this->data['uiguard']->hasPermission('editarp', $wfstate, $this->data['user']->getType())) {
                    echo ' <a onClick="centerPopup(); loadPopup(); fetchARP($(\'#entity_arp_select\').val());">Edit</a>';
                }
                if($this->data['uiguard']->hasPermission('addarp', $wfstate, $this->data['user']->getType())) {
                    echo ' <a onClick="centerPopup(); loadPopup(); newARP();">New</a>';
                }
            } else {
                echo '<input type="hidden" name="entity_arp" value="'. $current_arp .'">';
                foreach($this->data['arp_list'] AS $arp) {
                    if($current_arp == $arp['aid']) {
                        echo $arp['name'];
                    }
                }
            }
            ?>
                        </td>
                    </tr>
                </table>
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
                echo '<input class="remote_check_b" type="hidden" name="add-consent[]" value="'. $remote_entityid. '" />';
                echo '<input class="remote_check_b" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" checked disabled="disabled" />';
                echo '&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()] .'<br />';
            } else {
                echo '<input class="remote_check_b" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" disabled />';
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

<?php if ($this->data['useblacklist'] || $this->data['usewhitelist']) { ?>
<div id="remoteentities">
   <?php      
        $checked = '';
        if($this->data['uiguard']->hasPermission('blockremoteentity', $wfstate, $this->data['user']->getType())) {
            
            if ($this->data['useblacklist']) {
                if($this->data['entity']->getAllowedAll() == 'yes') {
                    $checked = 'checked';
                }
      
                // Access granted to block remote entities
                echo '<input id="allowall_check" type="checkbox" name="allowall" value="' . $this->data['entity']->getAllowedAll() . '" ' . $checked . ' > ' . $this->t('tab_remote_entity_allowall');
            }
            if ($this->data['usewhitelist']) {            
                if($this->data['entity']->getAllowedAll() != 'yes' && count($this->data['allowed_entities'])==0 && count($this->data['blocked_entities'])==0) {
                    $checked = 'checked';
                }
     
                echo '<br/><input id="allownone_check" type="checkbox" name="allownone" value="1" ' . $checked . ' > ' . $this->t('tab_remote_entity_allownone');
            }   
        } ?>
        
        
      <?php if ($this->data['useblacklist']) { ?>
   
        
        <h2><?php echo $this->t('tab_remote_entity_'. $this->data['entity']->getType()); ?> <?php echo $this->t('tab_remote_entity_blacklist'); ?></h2>
        <p><?php echo $this->t('tab_remote_entity_help_blacklist_'. $this->data['entity']->getType()); ?></p>
        <?php
    
        if($this->data['uiguard']->hasPermission('blockremoteentity', $wfstate, $this->data['user']->getType())) {
        
            echo '<hr>';
    
            foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {
                if(array_key_exists($remote_entityid, $this->data['blocked_entities'])) {
                    echo '<input class="remote_check_b" type="checkbox" name="addBlocked[]" value="'. $remote_entityid. '" checked />&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()] .'<br />';
                } else {
                    echo '<input class="remote_check_b" type="checkbox" name="addBlocked[]" value="'. $remote_entityid. '" />&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()] .'<br />';
                }
                echo '&nbsp;&nbsp;&nbsp;'. $remote_data['description'][$this->getLanguage()] .'<br />';
            }
        } else {
            // Access not granted to block remote entities
            if($checked == 'checked') {
                echo '<input id="allowall_check" type="hidden" name="allowall" value="' . $this->data['entity']->getAllowedAll() . '" '. $checked . '>';
            }
            echo '<input type="checkbox" name="allowall_dummy" value="' . $this->data['entity']->getAllowedAll() . '" ' . $checked . ' disabled="disabled"> ' . $this->t('tab_remote_entity_allowall') . '<hr>';
    
            foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {
                if(array_key_exists($remote_entityid, $this->data['blocked_entities'])) {
                    echo '<input class="remote_check_b" type="hidden" name="addBlocked[]" value="'. $remote_entityid. '" />';
                    echo '<input class="remote_check_b" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" checked disabled="disabled" />&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()] .'<br />';
                } else {
                    echo '<input class="remote_check_b" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" disabled />&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()].'<br />';
                }
                echo '&nbsp;&nbsp;&nbsp;'. $remote_data['description'][$this->getLanguage()] .'<br />';
            }
        }
    } 
    if ($this->data['usewhitelist']) { ?>
    
        <h2><?php echo $this->t('tab_remote_entity_'. $this->data['entity']->getType()); ?> <?php echo $this->t('tab_remote_entity_whitelist'); ?></h2>
        <p><?php echo $this->t('tab_remote_entity_help_whitelist_'. $this->data['entity']->getType()); ?></p>
        <?php
    
        if($this->data['uiguard']->hasPermission('blockremoteentity', $wfstate, $this->data['user']->getType())) {
            // Access granted to block remote entities
            echo '<hr>';
    
            foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {
                if(array_key_exists($remote_entityid, $this->data['allowed_entities'])) {
                    echo '<input class="remote_check_w" type="checkbox" name="addAllowed[]" value="'. $remote_entityid. '" checked />&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()] .'<br />';
                } else {
                    echo '<input class="remote_check_w" type="checkbox" name="addAllowed[]" value="'. $remote_entityid. '" />&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()] .'<br />';
                }
                echo '&nbsp;&nbsp;&nbsp;'. $remote_data['description'][$this->getLanguage()] .'<br />';
            }
        } else {
            // Access not granted to block remote entities
            if($checked == 'checked') {
                echo '<input id="allownone_check" type="hidden" name="allownone" value="not used" '. $checked . '>';
            }
            echo '<input type="checkbox" name="allownone_dummy" value="not used" ' . $checked . ' disabled="disabled"> ' . $this->t('tab_remote_entity_allownone') . '<hr>';
    
            foreach($this->data['remote_entities'] AS $remote_entityid => $remote_data) {
                if(array_key_exists($remote_entityid, $this->data['allowed_entities'])) {
                    echo '<input class="remote_check_w" type="hidden" name="addAllowed[]" value="'. $remote_entityid. '" />';
                    echo '<input class="remote_check_w" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" checked disabled="disabled" />&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()] .'<br />';
                } else {
                    echo '<input class="remote_check_w" type="checkbox" name="add_dummy[]" value="'. $remote_entityid. '" disabled />&nbsp;&nbsp;'. $remote_data['name'][$this->getLanguage()].'<br />';
                }
                echo '&nbsp;&nbsp;&nbsp;'. $remote_data['description'][$this->getLanguage()] .'<br />';
            }
        }
    } ?>
</div>
<?php } ?>
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
            } else {
                if(isset($metadata_val['type'])) {
                    echo 'metadata["'. $metadata_key .'"] = new Array();';
                    echo 'metadata["'. $metadata_key .'"]["type"] = "'. $metadata_val['type'] .'";';
                } else {
                    // Skip this metadata field if 'type' is not set
                    continue;
                }
                if(isset($metadata_val['default'])) {
                    echo 'metadata["'. $metadata_key .'"]["default"] = "'. $metadata_val['default'] .'";';
                } else {
                    echo 'metadata["'. $metadata_key .'"]["default"] = "";';
                }
                if(isset($metadata_val['maxsize'])) {
                    echo 'metadata["'. $metadata_key .'"]["maxsize"] = "'. $metadata_val['maxsize'] .'";';
                }
                if(isset($metadata_val['filetype'])) {
                    echo 'metadata["'. $metadata_key .'"]["filetype"] = "'. $metadata_val['filetype'] .'";';
                }
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
                    if(metadata[index]["default"] == true) {
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
            newelm.insertBefore("#meta_delim");
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

    $meta_desc = $this->data['metadata_fields'];
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
            echo '<tr class="'. ($i % 2 == 0 ? 'even' : 'odd'). '"  onMouseOut="$(\'#metadata-desc-' . strtr($data->getkey(), array(':' => '\\\:', '.' => '\\\.')) . '\').hide();" onMouseOver="$(\'#metadata-desc-' . strtr($data->getkey(), array(':' => '\\\:', '.' => '\\\.')) . '\').show();">';
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
                    if($data->getValue() == true) {
                        $checked_true = 'checked="checked"';
                        $checked_false = '';
                    } else {
                        $checked_false = 'checked="checked"';
                        $checked_true = '';
                    }
                    if($modifymetadata == 'readonly="readonly"') {
                        echo '<input value="true" type="checkbox" class="metadata_checkbox" name="edit-metadata-'. $data->getKey()  .'-TRUE" '. $checked_true .' disabled="disabled" onclick="changeFalse(this);">';
                    } else {
                        echo '<input value="true" type="checkbox" class="metadata_checkbox" name="edit-metadata-'. $data->getKey()  .'-TRUE" '. $checked_true .' onclick="changeFalse(this);">';
                    }
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
                case 'file':
                    echo '<input type="file" name="edit-metadata-'. $data->getKey()  .'" id="edit-metadata-'. $data->getKey()  .'" />';
                    echo '<script type="text/javascript">
                    $("input:file[name=edit-metadata-'. $data->getKey() .']").makeAsyncUploader({
                	    upload_url: "/'. $this->data['baseurlpath'] .'module.php/janus/AJAXRequestHandler.php",
                        flash_url: "/'. $this->data['baseurlpath'] .'module.php/janus/resources/scripts/swfupload.swf",
                        button_image_url: "/'. $this->data['baseurlpath'] .'module.php/janus/resources/scripts/blankButton.png",
                        existingFilename: "'. $data->getValue() .'",
                        disableDuringUpload: "INPUT[type=submit]",
                        button_text: "<font face=\"Arial\" size=\"13pt\">'. $this->t('choose_file') .'</font>",';
                    if(isset($metadata_field['maxsize'])) {
                        echo 'file_size_limit: "' . $metadata_field['maxsize'] . '",' . "\n";   
                    }
                    if(isset($metadata_field['filetype'])) {
                        echo 'file_types: "' . $metadata_field['filetype'] . '",' . "\n";   
                    }
                    echo 'post_params: {
                            "PHPSESSID" : "'. $_COOKIE['PHPSESSID'] .'",
                            "func" : "uploadFile",
                            "eid" : "'. $this->data['entity']->getEid() .'",
                            "index" : "edit-metadata-'. $data->getKey() .'"
                        }
                    });
                    </script>';
                    break;
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
        echo '<tr id="meta_delim">';
        echo '<td height="70px" style="vertical-align: top;">';
        echo '<img onclick="addMetadataInput(this);" src="resources/images/pm_plus_16.png" alt="Plus" />';
        echo '</td>';
        echo '<td colspan="2">';
        foreach($meta_desc AS $metadata_key => $metadata_val) {
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
