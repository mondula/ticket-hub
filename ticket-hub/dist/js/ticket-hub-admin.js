/**
 * Admin post status script
 */

jQuery(document).ready(function ($) {
    console.log('thub_admin_vars', thub_admin_vars);
    // Get the archived text from localized variables
    var archivedText = thub_admin_vars.archived_text;

    // Append the new status to the status selector in the edit post and quick edit screens
    $("select[name='post_status']").append("<option value='thub_archive'>" + archivedText + "</option>");

    // Check if the current post status is 'thub_archive' and update the selector and display
    if (thub_admin_vars.post_status === 'thub_archive') {
        $("select[name='post_status']").val('thub_archive');
        $('#post-status-display').text(archivedText);
    }
});

/**
 * Document Script
 */

jQuery(document).ready(function ($) {

    $('#thub-document-type').change(function () {
        if ($(this).val() === 'File') {
            $('#thub-file-upload-section').show();
            $('#thub-link-section').hide();
        } else {
            $('#thub-file-upload-section').hide();
            $('#thub-link-section').show();
        }
    });

    $('#thub-upload-file-button').click(function (e) {
        e.preventDefault();
        var fileFrame;

        if (fileFrame) {
            fileFrame.open();
            return;
        }

        fileFrame = wp.media({
            title: 'Select or Upload a File',
            button: {
                text: 'Use this file'
            },
            multiple: false
        });

        fileFrame.on('select', function () {
            var attachment = fileFrame.state().get('selection').first().toJSON();
            $('#thub-document-file-id').val(attachment.id);
            $('#thub-file-name').text(attachment.title);
        });

        fileFrame.open();
    });
});
/**
 * Form editor tab Script
 */

jQuery(document).ready(function ($) {
    var $table = $('#thub_fields_table');

    function toggleOptionsTextarea(row) {
        row.find('.input-type').each(function () {
            var $this = $(this);
            var $optionsTextarea = $this.closest('tr').find('textarea[name="input_options[]"]');
            if ($this.val() === 'select') {
                $optionsTextarea.css('visibility', 'visible');
            } else {
                $optionsTextarea.css('visibility', 'hidden');
            }
        });
    }

    // Initial toggle based on saved fields
    $('.field-row').each(function () {
        toggleOptionsTextarea($(this));
    });

    // Add field row
    $('#add_field_button').click(function () {
        var newRow = $('<tr class="field-row">' +
            '<td><select name="input_type[]" class="input-type"><option value="text">' + thub_admin_vars.text + '</option><option value="textarea">' + thub_admin_vars.textarea + '</option><option value="select">' + thub_admin_vars.select + '</option></select></td>' +
            '<td><input type="text" name="input_label[]" class="regular-text" placeholder="' + thub_admin_vars.label + '" /></td>' +
            '<td><textarea name="input_options[]" class="regular-text" style="visibility: hidden;"></textarea></td>' +
            '<td><input type="checkbox" name="input_required[]"></td>' +
            '<td><button type="button" class="button remove_field_button"><span class="dashicons dashicons-no"></span></button></td>' +
            '</tr>');
        $('#add_field_row').before(newRow);
        toggleOptionsTextarea(newRow);
    });

    // Remove field row
    $table.on('click', '.remove_field_button', function () {
        $(this).closest('tr').remove();
        updateRequiredFieldIndices();
    });

    // Toggle options textarea based on input type selection
    $table.on('change', '.input-type', function () {
        toggleOptionsTextarea($(this).closest('tr'));
    });

    // Update the indices of required fields after adding or removing a row
    function updateRequiredFieldIndices() {
        $('.field-row').each(function (index) {
            $(this).find('input[name^="input_required"]').attr('name', 'input_required[' + index + ']');
        });
    }

    // Initial update of required field indices
    updateRequiredFieldIndices();
});
