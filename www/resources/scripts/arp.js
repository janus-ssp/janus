$(function () {

    var arpModule = {

        translations: {
            emptyAttribute: "Attribute value may not be blank",
            duplicateAttribute: "It is not possible to enter the same attribute twice for non-wildcards attributes"
        },

        specifyValueInputId: "specifyValueInputId",

        init: function () {

            //checkbox for no ARP at all (and hiding the attributes configuration)
            $('#arp_no_arp_attributes').change(function () {
                $('#arp_attributes')[this.checked ? 'slideUp' : 'slideDown']();
            });

            //all the enable / disable checkboxes for the ARP attributes
            $('input[type=checkbox][name=arp_attribute_enabled]').change(function () {
                if (this.checked) {
                    arpModule.enableArp($(this));
                } else {
                    arpModule.disableArp($(this));
                }
            });

            //the plus signs for adding another free text attribute (e.g. specify_values is TRUE)
            $('a[data-add-specify-value]').click(function(){
                arpModule.addSpecifyValue($(this));
                return false;
            });

        },

        enableArp: function ($checkBox) {
            var $parent = $checkBox.parent('div');
            var specifyValue = $parent.attr('data-attribute-specify-value');

            if (specifyValue) {
                //create a input box for the new attribute value
                var $input = $('<input id="' + this.specifyValueInputId + '" autocomplete="off">');

                $input.blur(function () {
                    arpModule.saveArpValue($(this));
                });

                $input.keydown(function (evt) {
                    var code = evt.keyCode || evt.which;
                    if (code == 13) {
                        evt.preventDefault();
                        evt.stopImmediatePropagation();
                        arpModule.saveArpValue($(this));
                        return false;
                    }
                    else if (code == 27) {
                        var $divs = $(this).parents('td').find('div');
                        if ($divs.length > 1) {
                            $($divs[$divs.length -1]).remove();
                        } else {
                            $(this).remove();
                            $checkBox.removeAttr('checked');
                        }
                    }
                });
                $parent.append($input);
                $input.focus();
            } else {
                arpModule.saveArpValue($checkBox, '*');
            }
        },

        validateArp: function ($input, val) {
            var message;
            var value = val || $input.val();
            if ($input.prop("checked") && (!value || value.trim() === '')) {
                message = this.translations.emptyAttribute;
            } else {
                $input.parents('td').find('input[type=hidden]').each(function (i) {
                    if ($(this).val() === value.trim()) {
                        message = arpModule.translations.duplicateAttribute;
                    }
                });
            }
            if (message) {
                var $html = $('<div style="color: darkred">' + message + '</div>');
                $input.parent('div').append($html);
                $input.val();
                $input.focus();
                setTimeout(function(){
                    $html.remove();
                }, 3000);
                return false;
            }
            return true;
        },

        saveArpValue: function ($input, $value) {
            if (!this.validateArp($input, $value)) {
                return false;
            }
            var val = ($value || $input.val()).trim();

            var $parent = $input.parent('div');
            var parentId = $parent.attr('id');

            var html = '<label class="arpSpecifiedValue">' + val + '</label>' +
                '<input type="hidden" name="arp_attributes[' + $parent.attr('data-attribute-name') +
                '][]" value="' + val + '">';
            var attr = $input.attr('type');
            if (attr !== 'checkbox') {
                $input.remove();
            }
            $parent.append(html);
            var matchRule = (val === '*') ? "Wildcard" : ( arpModule.endsWith(val, '*') ? "Prefix" : "Exact");
            var rule = '<div id="' + parentId + '_match_rule"><label>' + matchRule + '</label></div>';
            $parent.parents('tr.attribute_select_row').find('td[data-matching-rule]').append(rule);
            return true;
        },

        disableArp: function ($checkBox) {
            var $parent = $checkBox.parent('div');

            //if this a attribute which value can be specified we need to check if we want to delete it
            if ($parent.parents('td[data-specify-values]').find('input[type=checkbox]').length > 1) {
                $parent.remove();
            } else {
                $parent.find('label, input[type=hidden]').remove();
            }

            //remove the match rule column containing info about the removed Arp attribute value
            $('#' + $parent.attr('id') + '_match_rule').remove();

        },

        addSpecifyValue: function ($link) {
            var $td = $link.parents('tr').find('td[data-specify-values]');
            var $divs = $td.find('div');
            var $div = $($divs[$divs.length - 1]);
            if ($div.find('input[type=hidden]').length > 0) {
                $div = $div.clone(true);
                $div.find('label, input[type=hidden]').remove();
                $td.append($div);
            } else {
                $div.find('input[type=checkbox]').attr('checked','checked');
            }
            this.enableArp($div.find('input[type=checkbox]'));
        },

        endsWith: function endsWith(str, suffix) {
            if (str && suffix) {
                return str.indexOf(suffix, str.length - suffix.length) !== -1;
            }
            return false;
        }
    };

    arpModule.init();


});

