$(function () {

    var metaDataModule = {

        init: function () {
            this.enableContactCopy();
            this.enableLanguageCopy();
        },

        enableContactCopy: function () {
            $.each([0, 1, 2], function (index, notUsed) {
                var metaDataType = 'contacts:' + index + ':contactType';
                var $selectContacts = $('select option[value="' + metaDataType + '"]:selected, ').parents('td');
                var $existingContacts = $("td:contains('" + metaDataType + "')").filter(function () {
                    return $(this).html().search(metaDataType) === 0;
                });
                var $contacts = $selectContacts.add($existingContacts).next('td');
                $contacts.each(function (i) {
                    var currentType = $(this).find('select').val();
                    $.each(['technical', 'support', 'administrative'], function (index, contactType) {
                        var $link = $('<a class="metaDataCopyLink" href="#" data-contact-nbr="' + metaDataModule.getContactTypeNbr(metaDataType)
                            + '" data-contact-type="' + contactType + '">' + contactType.substr(0, 1).toUpperCase() + '</a>');
                        $link.click(function () {
                            var $contactTypeSource = $(this).attr('data-contact-type');
                            var $contactNbrTarget = $(this).attr('data-contact-nbr');
                            //find the number of the $contactTypeSource (two options, either plain html in td or dropdown)
                            var $contactSource = $('select option[value="' + $contactTypeSource + '"]:selected').parents('td').prev('td');
                            if ($contactSource.size() === 0) {
                                alert('No contactType ' + $contactTypeSource + ' is currently selected...');
                                return false;
                            }
                            var $contactSourceNbr;
                            if ($contactSource.find('select').size() > 0) {
                                $contactSourceNbr = metaDataModule.getContactTypeNbr($contactSource.find('select').val());
                            } else {
                                $contactSourceNbr = metaDataModule.getContactTypeNbr($contactSource.html());
                            }
                            $.each(['emailAddress', 'givenName', 'surName'], function (index, type) {
                                var $source = metaDataModule.getInputField($contactSourceNbr, type);
                                var sourceValue = $source.val();
                                var $target = metaDataModule.getInputField($contactNbrTarget, type);
                                $target.attr('value', sourceValue);
                            });
                            return false;
                        });
                        $contacts.append($link);
                    });
                });
            });
        },

        enableLanguageCopy: function () {

        },

        getInputField: function ($contactNbr, type) {
            var $result = $("input[name='meta_value[contacts:" + $contactNbr + ":" + type + "]']");
            if ($result.size() === 0) {
                $result = $("input[name='edit-metadata-contacts:" + $contactNbr + ":" + type + "']");
            }
            return $result;
        },

        getContactTypeNbr: function (string) {
            return string.substr(9, 1);
        }
    };

    metaDataModule.init();


});

