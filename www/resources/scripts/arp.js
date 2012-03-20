var ARP = {
    translations: {
        confirmDeleteArp: 'Delete ARP? This will remove the ARP from all entities and leave them without ARP. ' +
                'If this is not what you want, you should manually assign new ARPs to the entities with this ARP.',
        deleteArp: 'Delete',
        saveArpError: 'Unable to save?!?!',
        removeArpError: "Error: Not deleted",
        removeArpAttribute: 'Delete'
    },
    popupMode: false,
    popupOpen : false,
    attributes: {},
    attributesWithRestrictedValues: [],

    load: function(id) {
        if(!id || id < 1) {
            if (this.popupMode) {
                this.closePopup();
            }
            return;
        }

        $.post(
                "AJAXRequestHandler.php",
                {
                    func: "getARP",
                    aid: id
                },
                function(data) {
                    $("#edit_arp_table").show();
                    if (ARP.popupMode) {
                        ARP.openPopup();
                    }

                    // convert (< v.1.11.0) legacy format
                    var attribute;
                    for (attribute in data['attributes']) {
                        if (!data['attributes'].hasOwnProperty(attribute)) {
                            break;
                        }
                        if (typeof data['attributes'][attribute] === 'string') {
                            data['attributes'][data['attributes'][attribute]] = ['*'];
                            delete data['attributes'][attribute];
                        }
                    }

                    ARP.attributes = data['attributes'];

                    $("#arp_id").val(data["aid"]);
                    $("#arp_name").val(data["name"]);
                    $("#arp_name_headline").html(data["name"]);
                    $("#arp_description").val(data["description"]);

                    $("tr[id^='attr_row_']").remove();

                    for(attribute in ARP.attributes) {
                        if (!ARP.attributes.hasOwnProperty(attribute)) {
                            break;
                        }
                        for (var i in ARP.attributes[attribute]) {
                            if (!ARP.attributes[attribute].hasOwnProperty(i)) {
                                break;
                            }
                            var attributeValue = ARP.attributes[attribute][i];
                            $("#arp_attributes").prepend(
                                    '<tr id="attr_row_' + ARP.hashCode(attribute) + '">'+
                                        '<td>' + attribute + '</td>'+
                                        '<td style="text-align: center">' + attributeValue + '</td>' +
                                        '<td>'+
                                            '<img src="resources/images/pm_delete_16.png"'+
                                                ' alt="' + ARP.translations.deleteArp + '"' +
                                                ' onclick="ARP.removeAttribute(\'' + attribute + '\')"'+
                                                ' style="cursor: pointer;">'+
                                        '</td>'+
                                    '</tr>'
                            );
                        }
                    }
                    // apply row coloring
                    $("tr[id^='attr_row_']:even").css("background-color", "#EEEEEE");
                },
                "json"
        );
    },

    save: function() {
        var data = {
            func            : "setARP",
            aid             : $("#arp_id").val(),
            name            : $("#arp_name").val(),
            description     : $("#arp_description").val()
        };
        for (var attributeName in this.attributes) {
            for (var i=0; i < this.attributes[attributeName].length; i++) {
                data['attributes['+ attributeName + ']['+i+']'] = this.attributes[attributeName][i];
            }
        }
        $.post(
            "AJAXRequestHandler.php",
            data,
            function(data) {
                if(data["status"] !== "success") {
                    alert(ARP.translations.saveArpError);
                    return;
                }

                if($("#arp_id").val() == '') {
                    $("#arp_id").val(data["aid"]);

                    // Select the new ARP for the entity
                    if ($("#entity_arp_select").length > 0) {
                        $("#entity_arp_select").
                                append('<option value="' + data["aid"] + '"></option>').
                                val(data["aid"]);
                    }
                    if ($('#arpadmin').length > 0 && $('#arp_row_' + data['aid']).length === 0) {
                        $('#arpadmin tbody tr:last').before(
                            '<tr id="arp_row_' + data['aid'] + '">'+
                                '<td class="arp_name">' + $("#arp_name").val() + '</td>'+
                                '<td>'+
                                    '<img src="resources/images/pencil.png"'+
                                    ' alt="Edit"'+
                                    ' width="16"'+
                                    ' height="16"'+
                                    ' onclick="ARP.load(' + data['aid'] + ');"'+
                                    ' style="cursor: pointer; margin-left: auto; margin-right: auto; display: block;"'+
                                    '    />'+
                                '</td>'+
                                '<td>'+
                                '   <img src="resources/images/pm_delete_16.png"'+
                                '         alt="Delete"'+
                                '         width="16"'+
                                '         height="16"'+
                                '         onclick="ARP.remove(' + data['aid'] + ');"'+
                                '         style="cursor: pointer; margin-left: auto; margin-right: auto; display: block;"'+
                                '            />'+
                                '  </td>'+
                            '</tr>');
                    }

                    ARP.load(data['aid']);
                } else {
                    $("#arp_id").val(data["aid"]);
                }

                ARP.updateName();

                if (ARP.popupMode) {
                    ARP.closePopup();
                }
                $("#edit_arp_table").hide();
            },
            "json"
        );
    },

    create: function() {
        $('#arp_id').val('');
        $('#arp_name').val('');
        $('#arp_desription').val('');
        ARP.attributes = [];
        $("tr[id^='attr_row_']").remove();

        this.save();
    },

    remove: function(id) {
        if (!window.confirm(ARP.translations.confirmDeleteArp)) {
            return;
        }

        $.post(
            "AJAXRequestHandler.php",
            {
                func: "deleteARP",
                aid: id
            },
            function(data) {
                if(data["status"] == "success") {
                    $("#arp_row_" + id).remove();

                    // Reapply row coloring
                    $("tr[id^=\'arp_row_\']").css("background-color", "#FFFFFF");
                    $("tr[id^=\'arp_row_\']:even").css("background-color", "#EEEEEE");
                } else {
                    alert(ARP.translations.removeArpError);
                }
            },
            "json"
        );
    },

    addAttribute: function(el) {
        var attribute = $(el).val();
        if (!attribute) {
            return;
        }

        var attributeValue = "*";
        if ($.inArray(attribute, this.attributesWithRestrictedValues) !== -1) {
            if ($('##attribute_select_row .arp_select_attribute_value').is(':hidden')) {
                $('#attribute_select_row .arp_select_attribute_value').show();
                return;
            }
            else if ($('#attribute_select_value').val() === "") {
                return;
            }
            else {
                attributeValue = $('#attribute_select_value').val();
            }
        }
        // Reset any values that were set.
        $('#attribute_select_value').val('');
        $('#attribute_select_row .arp_select_attribute_value').hide();
        // Reset select box
        $('#attribute_select').val('');

        if (typeof this.attributes[attribute] !== 'undefined' && $.inArray(attributeValue, this.attributes[attribute]) !== -1) {
            return;
        }

        if (typeof this.attributes[attribute] === 'undefined') {
            this.attributes[attribute] = [];
        }
        this.attributes[attribute].push(attributeValue);

        $("#attribute_select_row").before(
            '<tr id="attr_row_' + this.hashCode(attribute) + '">'+
                '<td>' + attribute + '</td>'+
                '<td style="text-align: center">'+ attributeValue + '</td>' +
                '<td>'+
                    '<img src="resources/images/pm_delete_16.png"'+
                        ' alt="' + ARP.translations.removeArpAttribute + '"'+
                        ' onclick="ARP.removeAttribute(\'' + attribute + '\')"'+
                        ' style="cursor: pointer;">'+
                '</td>'+
            '</tr>'
        );

        // reapply row coloring
        $("tr[id^='attr_row_']:even").css("background-color", "#EEEEEE");
    },

    removeAttribute: function(value) {
        $("#attr_row_" + this.hashCode(value)).remove();
        delete this.attributes[value];

        // reapply row coloring
        $("tr[id^='attr_row_']").css("background-color", "#FFFFFF");
        $("tr[id^='attr_row_']:even").css("background-color", "#EEEEEE");
    },

    updateName: function() {
        var arpId   = $("#arp_id").val();
        var arpName = $("#arp_name").val();
        console.log('updating the name', arpId, arpName);
        if (!arpId || !arpName) {
            return;
        }

        // Update the selected ARP rule for editentity
        $("#entity_arp_select option:selected").each(function(){
            $(this).text(arpName);
        });

        // Update the name in the listing on the dashboard
        $('#arp_row_' + arpId + ' .arp_name').text(arpName);
    },

    setAttributeWithRestrictedValues: function(attribute) {
        this.attributesWithRestrictedValues.push(attribute);
    },

    openPopup: function() {
        this._centerPopup();

        //loads popup only if it is disabled
        if (!this.popupOpen) {
            $("#backgroundPopup").css({
                "opacity": "0.7"
            });
            $("#backgroundPopup").fadeIn("slow");
            $("#arp_edit").fadeIn("slow");
            //$("#popupContact").fadeIn("slow");
            this.popupOpen = true;
        }
    },

        _centerPopup: function() {
            var popupHeight = $('#arp_edit').height();
            var popupWidth  = $('#arp_edit').width();
            $('#arp_edit').css({
                    top:'50%',
                    left:'50%',
                    margin:'-' + (popupHeight / 2) + 'px 0 0 -' + (popupWidth / 2) + 'px'
                }
            );
        },

    closePopup: function() {
        //disables popup only if it is enabled
        if (this.popupOpen){
            $("#backgroundPopup").fadeOut("slow");
            $("#arp_edit").fadeOut("slow");
            this.popupOpen = false;
        }
    },

    /**
     * Very simple hash function, similar to Javas hashCode, transforms a string to a 32 bit integer.
     * Note that this will usually wrap around and is not intended for use with long strings
     * (which will wrap around multiple times causing collisions).
     * @param {String} source
     * @return {Number}
     */
    hashCode: function(source) {
        if (source.length == 0) {
            return "";
        }
        var hash = 0, charCode;
        for (var i = 0; i < source.length; i++) {
            charCode = source.charCodeAt(i);
            hash = ((hash<<5) - hash) + charCode;
            hash = hash & hash;
        }
        return hash;
    }
};

$(function(){
    // ARP edit
    $("#arp_edit_close").click(function(){
        ARP.closePopup();
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