$(function () {

    var metaDataModule = {

        init: function () {
            this.enableContactCopy();
            this.enableLanguageCopy();
        },

        enableContactCopy: function () {
            $.each([0, 1, 2], function (index, notUsed) {
                var metaDataType = 'contacts:' + index + ':contactType';
                var $selectContacts = $('select option[value="' + metaDataType + '"]:selected').parents('td');
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
                            var $dropDown = $('select option[value="' + $contactTypeSource + '"]:selected');
                            if ($dropDown.size() === 0) {
                                alert('No contactType ' + $contactTypeSource + ' is currently selected...');
                                return false;
                            }
                            var $contactSource = $($dropDown[0]).parents('td').prev('td');
                            var $contactSourceNbr;
                            if ($contactSource.find('select').size() > 0) {
                                $contactSourceNbr = metaDataModule.getContactTypeNbr($contactSource.find('select').val());
                            } else {
                                $contactSourceNbr = metaDataModule.getContactTypeNbr($contactSource.html());
                            }
                            $.each(['emailAddress', 'givenName', 'surName'], function (index, type) {
                                var $source = metaDataModule.getContactInputField($contactSourceNbr, type);
                                var sourceValue = $source.val();
                                var $target = metaDataModule.getContactInputField($contactNbrTarget, type);
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
            $.each(['description', 'keywords', 'name'], function (index, type) {
                var typeSelector = type + ':nl';
                var $selectTypes = $('select option[value="' + typeSelector + '"]:selected').parents('td');
                var $existingTypes = $("td:contains('" + typeSelector + "')").filter(function () {
                    return $(this).html().search(typeSelector) === 0;
                });
                var $types = $selectTypes.add($existingTypes).next('td');
                $types.each(function (i) {
                    var $currentType = $(this).find('input');
                    $currentType.removeClass('width_100').addClass('width_95');
                    var $link = $('<a class="metaDataCopyLink leftLink" href="#" data-type="' + type + '" >EN</a>');
                    $link.click(function () {
                        var $source = metaDataModule.getInputField($(this).attr('data-type') + ':en');
                        $(this).parents('td').find('input').val($source.val());
                        return false;
                    });
                    $(this).prepend($link);
                });
            });
        },

        getContactInputField: function ($contactNbr, type) {
            return this.getInputField("contacts:" + $contactNbr + ":" + type);
        },

        getInputField: function ($name) {
            var $result = $("input[name='meta_value[" + $name + "]']");
            if ($result.size() === 0) {
                $result = $("input[name='edit-metadata-" + $name + "']");
            }
            return $result;
        },

        getContactTypeNbr: function (string) {
            return string.substr(9, 1);
        }
    };

    metaDataModule.init();


});

