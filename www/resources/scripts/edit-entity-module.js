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

    $("#historycontainer").hide();
    $("#showhide").click(function() {
        var $historyContainer = $("#historycontainer");
        $historyContainer.toggle("slow");
        if ($("#historycontainer p").size() > 0) {
            $historyContainer.load("history.php?eid=" + $historyContainer.attr('data-entity-eid')
                + "&currentRevisionId=" + $historyContainer.attr('data-current-revision-id')
                + "&historyTab="+ $historyContainer.attr('data-history-tab')
            );
        }
        return true;
    });

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
    if ($("#compareRevisions").length > 0) {
        jsondiffpatch.config.objectHash = function(obj) { return obj.id || JSON.stringify(obj); };

        var startRevision = parseInt($("#compareRevisions").attr('data-start-revision'));
        var endRevision = parseInt($("#compareRevisions").attr('data-end-revision'));

        for (var i = startRevision; i < endRevision ; i++) {
            var d = jsondiffpatch.diff(jsonCompareRevisions[i], jsonCompareRevisions[i+1]);
            if (typeof d == 'undefined') {
                $("#toggle_unchanged_attr_container" + i).hide();
                $("#compareRevisionsContent" + i).html('<p>No changes</p>');
            } else {
                var html = jsondiffpatch.html.diffToHtml(jsonCompareRevisions[i], jsonCompareRevisions[i+1], d);
                $("#compareRevisionsContent" + i).html(html);
            }
            $('.toggle_unchanged_attr').change(function(){
                var nbr = $(this).attr('data-revision-nrb');
                var selector = '#compareRevisionsContent' + nbr + ' li.jsondiffpatch-unchanged';
                $(selector)[this.checked ? 'slideDown' : 'slideUp']();
            });

        }

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
