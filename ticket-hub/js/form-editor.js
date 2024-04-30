jQuery(document).ready(function($) {

    // Load the form configuration when the page loads
    loadFormConfig();

    function loadFormConfig() {
        $.post(myPlugin.ajax_url, {
            action: 'get_form_config',
            nonce: myPlugin.nonce
        }, function(response) {
            console.log("AJAX Response:", response);  // Log the full response
            if (response.success && response.data) {
                populateForm(JSON.parse(response.data));
            } else {
                console.error('Failed to load form configuration');
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX Error:', textStatus, errorThrown); // Log AJAX errors if any
        });
    }

    function populateForm(fields) {
        fields.forEach(function(field) {
            var label = field.label;
            var fieldType = field.type;
            var fieldHtml = '<div class="form-field"><label>' + label + '</label>';
            if (fieldType === 'text') {
                fieldHtml += '<input type="text" placeholder="' + label + '">';
            } else if (fieldType === 'textarea') {
                fieldHtml += '<textarea placeholder="' + label + '"></textarea>';
            } else if (fieldType === 'select' && field.options) {
                fieldHtml += '<select>';
                field.options.forEach(function(option) {
                    fieldHtml += '<option value="' + option + '">' + option + '</option>';
                });
                fieldHtml += '</select>';
            }
            fieldHtml += '<button class="remove-field">Remove</button></div>';
            $('#dynamic-form').append(fieldHtml);
        });
    }

    $('#add-field').click(function() {
        var fieldType = $('#field-type').val();
        var fieldLabel = $('#field-label').val();
        var fieldOptions = $('#field-options').val();
        var fieldHtml = '<div class="form-field">';
        fieldHtml += '<label>' + fieldLabel + '</label>';

        if(fieldType === 'text') {
            fieldHtml += '<input type="text" placeholder="' + fieldLabel + '">';
        } else if (fieldType === 'textarea') {
            fieldHtml += '<textarea placeholder="' + fieldLabel + '"></textarea>';
        } else if (fieldType === 'select') {
            fieldHtml += '<select>';
            fieldOptions.split(',').forEach(function(option) {
                fieldHtml += '<option value="' + option.trim() + '">' + option.trim() + '</option>';
            });
            fieldHtml += '</select>';
        }

        fieldHtml += '<button class="remove-field">Remove</button></div>';
        $('#dynamic-form').append(fieldHtml);
    });

    $('#dynamic-form').on('click', '.remove-field', function() {
        $(this).parent('.form-field').remove();
    });

    $('#save-form').click(function() {
        var formConfig = [];
        $('#dynamic-form .form-field').each(function() {
            var field = {
                type: $(this).find(':input').first().attr('type'),
                label: $(this).find('label').text(),
            };
            if (field.type === 'select') {
                field.options = [];
                $(this).find('option').each(function() {
                    field.options.push($(this).val());
                });
            }
            formConfig.push(field);
        });
    
        console.log("Saving Form Config:", JSON.stringify(formConfig));  // Log the data being sent
    
        $.post(myPlugin.ajax_url, {
            action: 'save_form_config',
            config: JSON.stringify(formConfig),
            nonce: myPlugin.nonce
        }, function(response) {
            alert('Form saved successfully!');
        });
    });
});
