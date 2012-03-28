var ARP = {
    translations: {
        confirmDeleteArp: 'This ARP is used by %s entities, removing this will remove the ARP from all these entities.',
        emptyName: 'Name is empty, must have a name',
        unusedArp: 'ARP is not used by any entity'
    },
    attributes: {},
    availableAttributes: {},
    arpEntities: {},

    setEntityForArp: function(aid, entity) {
        if (typeof this.arpEntities[aid] === 'undefined') {
            this.arpEntities[aid] = [];
        }
        this.arpEntities[aid].push(entity);
    },

    edit: function(id) {
        if(!id || id < 1) {
            return;
        }

        $.post(
            "AJAXRequestHandler.php",
            {
                func: "getARP",
                aid: id
            },
            function(data) {
                ARP._loadArp(data);
            },
            "json"
        );
    },

    _loadArp: function(data) {
        $("#arpEdit").show();

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
        if (data['is_default']) {
            $('#arp_is_default').attr('checked', 'checked');
        }

        $("tr[id^='attr_row_']").remove();

        for(attribute in ARP.attributes) {
            if (!ARP.attributes.hasOwnProperty(attribute)) {
                continue;
            }
            this._addAttribute(attribute);
        }
        if (typeof this.arpEntities[+data['aid']] === 'undefined') {
            $('#arpEditEntities').html('<p>' + this.translations.unusedArp + '</p>');
        }
        else {
            var html = '<ul>';
            var entity;
            var arpEntities = this.arpEntities[+data['aid']];
            for (var i = 0; i < arpEntities.length; i++) {
                entity = arpEntities[i];
                var linkTemplate = $('<a title=""'+
                    ' href="editentity.php?eid=' + encodeURIComponent(entity.eid) +
                                            '&amp;revisionid=' + encodeURIComponent(entity.revision) + '">'+
                    '</a>');
                var link = linkTemplate.attr('title', entity.entityId).text(entity.name + ' - r' + entity.revision);
                html += '<li>' + link.wrap('<div>').parent().html() + '</li>';
            }
            html += '</ul>';
            $('#arpEditEntities').html(html);
        }
    },

    _addAttribute: function(attribute) {
        if (!ARP.attributes.hasOwnProperty(attribute)) {
            return;
        }

        var attributeName = this._getAttributeNameForAttribute(attribute);

        for (var i in ARP.attributes[attribute]) {
            if (!ARP.attributes[attribute].hasOwnProperty(i)) {
                continue;
            }
            var attributeValue = ARP.attributes[attribute][i];
            $("#attribute_select_row").before(
                    '<tr id="attr_row_' + ARP.hashCode(attribute) + '">'+
                        '<td>' + ARP.encodeForHtml(attributeName) +
                            '<input type="hidden"'+
                                  ' name="arp_attributes[' + ARP.encodeForHtml(attributeName) + '][]"'+
                                  ' value="' + ARP.encodeForHtml(attributeValue) + '" />'+
                        '</td>'+
                        '<td style="text-align: center">' + ARP.encodeForHtml(attributeValue) + '</td>' +
                        '<td>'+
                            '<img src="resources/images/pm_delete_16.png"'+
                                ' alt="' + ARP.translations.deleteArp + '"' +
                                ' onclick="ARP.removeAttribute(\'' + attribute + '\')"'+
                                ' style="cursor: pointer;">'+
                        '</td>'+
                    '</tr>'
            );
        }
        // apply row coloring
        $("tr[id^='attr_row_']:even").css("background-color", "#EEEEEE");
    },

    _getAttributeNameForAttribute: function(attribute) {
        var attributeName = attribute;
        for (var i in ARP.availableAttributes) {
            if (!ARP.availableAttributes.hasOwnProperty(i)) {
                continue;
            }
            if (ARP.availableAttributes[i].name !== attribute) {
                continue;
            }

            attributeName = i;
        }
        return attributeName;
    },

    validate: function() {
        if (!$('#arp_name').val().trim()) {
            alert(ARP.translations.emptyName);
            return false;
        }
        return true;
    },

    create: function() {
        $('#arp_id').val('');
        $('#arp_name').val('');
        $('#arp_description').val('');
        $('#arp_is_default').removeAttr('checked');
        ARP.attributes = [];
        $("tr[id^='attr_row_']").remove();
        $('#arpEditEntities').html('<p>' + this.translations.unusedArp + '</p>');

        $('#arpEdit').show();
    },

    addAttribute: function(el) {
        var attribute = $(el).val();
        if (!attribute) {
            return;
        }

        var attributeName = attribute, mustSpecifyValue = false;
        for (var i in this.availableAttributes) {
            if (!this.availableAttributes.hasOwnProperty(i)) {
                continue;
            }
            if (this.availableAttributes[i].name !== attribute) {
                continue;
            }
            attributeName = i;
            if (typeof this.availableAttributes[i].specify_values !== 'undefined' && this.availableAttributes[i].specify_values) {
                mustSpecifyValue = true;
            }
        }

        var attributeValue = "*";
        if (mustSpecifyValue) {
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

        this._addAttribute(attribute);
    },

    removeAttribute: function(value) {
        $("#attr_row_" + this.hashCode(value)).remove();
        delete this.attributes[value];

        // reapply row coloring
        $("tr[id^='attr_row_']").css("background-color", "#FFFFFF");
        $("tr[id^='attr_row_']:even").css("background-color", "#EEEEEE");
    },

    remove: function(aid) {
        console.log('remove', aid);
        if (typeof this.arpEntities[+aid] === 'undefined') {
            // no linked entities, okay to delete.
            return true;
        }

        var linkedEntitiesCount = this.arpEntities[+aid].length;
        return confirm(this.translations.confirmDeleteArp.replace(/\%s/, linkedEntitiesCount));
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
    },

    /**
     * Use the browser to do HTML encoding because it's probably better than we are.
     *
     * @param {String} text
     * @return {String}
     */
    encodeForHtml: function(text) {
        return $('<div />').text(text).html();
    }
};
