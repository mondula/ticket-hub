<?php

add_action('admin_menu', function() {
    add_submenu_page(
        'mts-main-menu', // plugin main menu slug if you have one
        'Add Ticket Form Fields', // Page title
        'Form Editor', // Menu title
        'manage_options', // Capability
        'form-editor', // Menu slug
        'mts_ticket_editor_page' // Callback function for the page content
    );
});

function mts_ticket_editor_page() {
    // Check if the form has been submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('mts_save_ticket_fields', 'mts_ticket_fields_nonce')) {
        // Process saving multiple fields, their options, and required status
        $types = $_POST['input_type'] ?? [];
        $labels = $_POST['input_label'] ?? [];
        $options_list = $_POST['input_options'] ?? [];
        $requireds = $_POST['input_required'] ?? [];
        $fields = [];

        foreach ($types as $key => $type) {
            $options = array_filter(array_map('sanitize_text_field', explode("\n", $options_list[$key])));
            $required = !empty($requireds[$key]) ? true : false;
            if (!empty($labels[$key]) && !($type === 'select' && empty($options))) {
                $fields[] = [
                    'type' => sanitize_text_field($type),
                    'label' => sanitize_text_field($labels[$key]),
                    'options' => $type === 'select' ? $options : [],
                    'required' => $required
                ];
            }
        }

        // Save the fields as a serialized array
        update_option('mts_custom_fields', $fields);
        echo '<div class="notice notice-success"><p>Fields saved.</p></div>';
    }

    // Retrieve any existing values
    $saved_fields = get_option('mts_custom_fields', []);

    // The HTML form
    ?>
    <div class="wrap">
        <h2>Add Ticket Form Fields</h2>
        <form method="post" action="">
            <?php wp_nonce_field('mts_save_ticket_fields', 'mts_ticket_fields_nonce'); ?>
            <table class="form-table" id="custom_fields_table">
                <?php 
                if (!empty($saved_fields)) {
                    foreach ($saved_fields as $index => $field) { ?>
                        <tr class="field-row">
                            <td>
                                <select name="input_type[]" class="input-type">
                                    <option value="text" <?php selected($field['type'], 'text'); ?>>Text</option>
                                    <option value="textarea" <?php selected($field['type'], 'textarea'); ?>>Textarea</option>
                                    <option value="select" <?php selected($field['type'], 'select'); ?>>Select</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="input_label[]" value="<?php echo esc_attr($field['label']); ?>" />
                            </td>
                            <td class="options-cell">
                                <textarea name="input_options[]" placeholder="Enter options separated by newline" <?php echo ($field['type'] !== 'select') ? 'style="display:none;"' : ''; ?>><?php echo isset($field['options']) ? esc_textarea(implode("\n", $field['options'])) : ''; ?></textarea>
                            </td>
                            <td>
                                <input type="checkbox" name="input_required[]" <?php if ($field['required']) echo 'checked="checked"'; ?> />
                                <label>Required</label>
                            </td>
                            <td>
                                <button type="button" class="button remove_field_button">Remove</button>
                            </td>
                        </tr>
                <?php } 
                } ?>
                <tr id="add_field_row">
                    <td colspan="5">
                        <button type="button" class="button" id="add_field_button">Add Field</button>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Fields'); ?>
        </form>
    </div>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        var $table = $('#custom_fields_table');

        // Function to toggle options and required fields
        function toggleFieldSettings(row) {
            var type = $(row).find('.input-type').val();
            var optionsTextarea = $(row).find('.options-cell textarea');
            var requiredCheckbox = $(row).find('.required-cell input');

            if (type === 'select') {
                optionsTextarea.show();
            } else {
                optionsTextarea.hide();
            }

            requiredCheckbox.prop('disabled', false);  // Enable checkbox for all
        }

        // Add field row
        $('#add_field_button').click(function() {
            var newRow = $('<tr class="field-row">' +
                '<td><select name="input_type[]" class="input-type"><option value="text">Text</option><option value="textarea">Textarea</option><option value="select">Select</option></select></td>' +
                '<td><input type="text" name="input_label[]" placeholder="Label" /></td>' +
                '<td class="options-cell"><textarea name="input_options[]" style="display:none;"></textarea></td>' +
                '<td class="required-cell"><input type="checkbox" name="input_required[]" disabled /><label>Required</label></td>' +
                '<td><button type="button" class="button remove_field_button">Remove</button></td>' +
                '</tr>');
            $('#add_field_row').before(newRow);
            toggleFieldSettings(newRow);
        });

        // Remove field row
        $table.on('click', '.remove_field_button', function() {
            $(this).closest('tr').remove();
        });

        // Change field type
        $table.on('change', '.input-type', function() {
            toggleFieldSettings($(this).closest('tr'));
        });

        // Initial toggle on page load for existing rows
        $('.field-row').each(function() {
            toggleFieldSettings(this);
        });
    });
    </script>
    <?php
}
