$(function () {

    var metaDataModule = {

        contactMetaData: ['contacts:0:contactType', 'contacts:1:contactType', 'contacts:2:contactType'],
        contactTypes: ['technical', 'support', 'administrative'],
        contactNbrOffset: 'contacts:'.length,

        init: function () {

            //checkbox for metadata id
//            $('select[name="meta_key"]').change(function(){
//                metaDataModule.reInitialize();
//            });

            //$('select[name="meta_value[contacts:0:contactType]"] option[value="technical"]:selected').size()
            $.each(this.contactMetaData, function (index, metaDataType) {
                var $selectContacts = $('select option[value="' + metaDataType + '"]:selected, ').parents('td');
                var $existingContacts = $("td:contains('" + metaDataType + "')").filter(function () {
                    return $(this).html().search(metaDataType) === 0;
                });
                var $contacts = $selectContacts.add($existingContacts).next('td');
                $.each([this.contactTypes], function (index, contactType) {
                    var $link = $('<a class="metaDataCopyLink" href="#" data-contact-nbr="' + metaDataType.substr(this.contactNbrOffset, 1)
                        + '" data-contact-type="' + contactType + '">' + contactType.substr(0, 1).toUpperCase() + '</a>');
                    $link.click(function () {
                        $contactTypeSource = $(this).attr('data-contact-type');
                        $contactNbrSource = $(this).attr('data-contact-nbr');
                        //find the number for the targeted contact info
                        $(this).parent('td').prev('td').html();
                        alert('hi');
                        return false;
                    });
                    $contacts.append($link);
                });


            });

            //$('input[name="meta_value[contacts:0:emailAddress]"]')

        },

        enableCopyPlugin: function () {
        }
    };

    metaDataModule.init();


});

