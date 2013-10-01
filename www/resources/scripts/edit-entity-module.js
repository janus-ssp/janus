$(document).ready(function() {
    $("#tabdiv").tabs({
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
    $("#tabdiv").tabs("select", $(this).attr('data-selected-tab'));
    $("#historycontainer").hide();
    $("#showhide").click(function() {
        var $historyContainer = $("#historycontainer");
        $historyContainer.toggle("slow");
        if ($("#historycontainer p").size() > 0) {
            $historyContainer.load("history.php?eid=" + $historyContainer.attr('data-entity-eid'));
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
});
