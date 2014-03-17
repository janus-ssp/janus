$(function(){
    var pathPrefix = window.location.pathname.substr(0, window.location.pathname.indexOf('module.php'));

    var entityEl = $('#MetadataValidation');
    if (entityEl.length === 0) {
        return;
    }
    var entityId = entityEl.attr('class');

    $.getJSON(pathPrefix + 'module.php/janus/pages/get-entity-metadata-validations.php?eid=' + encodeURIComponent(entityId), function(data) {
        entityEl.find('.metadata-messages-template').tmpl({
              Errors: data.Errors,
              Warnings: data.Warnings
        }).appendTo(entityEl.find('.entity-messages'));

        var validationInfoEl = entityEl.find('.entity-metadata-validation');

        var formattedValidations = {};
        var validations = data.Validations;
        for (var keyVar in validations ) {
            if (validations[keyVar].errors != null) {
                formattedValidations[keyVar] = {
                    'name': keyVar,
                    'status': 'error',
                    'message': validations[keyVar].errors
                };
            } else if (validations[keyVar].warnings != null) {
                formattedValidations[keyVar] = {
                    'name': keyVar,
                    'status': 'warning',
                    'message': validations[keyVar].warnings
                };
            } else {
                formattedValidations[keyVar] = {
                    'name' : keyVar,
                    'status': 'good',
                    'message': [validations[keyVar]]
                };
            }
        }

        entityEl.find('.entity-metadata-validation-template').tmpl({
            'Validations': formattedValidations
        }).appendTo(validationInfoEl);

        fixTableLayout($('table.entity-metadata-table'));
    });

    var fixTableLayout = function(table) {
        if (table instanceof jQuery) {
            // Add odd and even classes to odd and even rows
            table.find('tbody tr:nth-child(even)').addClass('even');
            table.find('tbody tr:nth-child(odd)').addClass('odd');
      }
    }
});