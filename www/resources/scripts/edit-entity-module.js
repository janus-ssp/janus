/*
 * Make this a proper module - see arp.js
 */
$(document).ready(function() {
    $tabdiv = $("#tabdiv");
    $tabdiv.tabs({
        /**
         * Sets selected tab value when tab is clicked

         * @param Event event
         * @param {*}   tab
         */
        select : function(event, tab) {
            var tabElement = $(tab.tab).parent("li");
            var tabCount = tabElement.prevAll().length;
            $("#mainform input[name=selectedtab]").val(tabCount);
        }
    });
    $tabdiv.tabs("select", parseInt($tabdiv.attr('data-selected-tab')));

    $("#change_entity_id_link").click(function(){
        return makeInputEditable($("#change_entity_id"));
    });
    $("#change_entity_notes_link").click(function(){
        return makeInputEditable($("#change_entity_notes"));
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

    $("input.consent_check[name=add-consent[]]").change(function(){
        $("#consent_changed_input").val("changed");
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

    $("#mainform").submit(function(evt) {
        if ($(this).attr("data-revision-required")) {
            var $revision = $("#revision_note_input");
            if (!$revision.val().trim()) {
                alert("Revision notes are mandatory before a change can be made" );
                evt.preventDefault();
                $revision.focus();
            }
        }
    });

    revisionCompare();

    function revisionCompare() {
        jsondiffpatch.config.objectHash = function(obj) { return obj.id || JSON.stringify(obj); };

        var startRevision = 0;
        var endRevision = parseInt($("#latestRevisionNbr").val());

        for (var i = startRevision; i < endRevision ; i++) {
            var d = jsondiffpatch.diff(jsonCompareRevisions[i], jsonCompareRevisions[i+1]);
            if (typeof d == 'undefined') {
                $("#compare_revisions_content_" + i).html('<p>No changes</p>');
            } else {
                var html = jsondiffpatch.html.diffToHtml(jsonCompareRevisions[i], jsonCompareRevisions[i+1], d);
                $("#compare_revisions_content_" + i).append(html);
            }
            $('.jsondiffpatch-visualdiff-root').click(function(){
                $(this).find('li.jsondiffpatch-unchanged').toggle();
            });

        }
        $('.toggle_show_changes').change(function(){
            var selector = '#compare_revisions_content_' + $(this).attr('data-revision-nbr');
            $(selector)[this.checked ? 'slideDown' : 'slideUp']();
        });

        $('#show_all_changes').click(function(){
            if (this.checked) {
                $('.toggle_show_changes').attr('checked','checked');
            } else {
                $('.toggle_show_changes').removeAttr('checked');
            }
            $('.compareRevisionsContent')[this.checked ? 'slideDown' : 'slideUp']();
        });

    }

    function makeInputEditable($input) {
        if ($input.attr('disabled')) {
            $input.removeAttr('disabled');
            setTimeout(function() {
                $input.focus();
                tmpStr = $input.val();
                $input$input.val('');
                $changeEntity.val(tmpStr);
            }, 1);
        } else {
            $input.attr('disabled', 'true');
        }
        return false;
    }
});
