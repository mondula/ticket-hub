/**
 * Admin form editor tab script
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
