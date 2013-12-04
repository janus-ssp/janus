$(function () {

    var metaDataExportModule = {

        init: function () {

            var $jsonTextArea = $("#metadatajson").find('textarea');
            var pretty = JSON.stringify(JSON.parse($jsonTextArea.html()),null,2);
            $jsonTextArea.html(pretty);

            $(".metadatabox").css("width", "100%").autosize();

            $("#metadataphp").hide();
            $("#metadataxml").hide();

            $(".show-hide").click(function() {
                $(this).next('div').toggle("slow");
                return false;
            });
        }
    };

    metaDataExportModule.init();

});

