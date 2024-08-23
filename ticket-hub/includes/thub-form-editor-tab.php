<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function thub_ticket_editor_page()
{
    // Check if the form has been submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('thub_save_ticket_fields', 'thub_ticket_fields_nonce')) {
        // Process saving multiple fields, their options, and required status
        update_option('thub_disable_attachments', isset($_POST['disable_attachments']) ? 1 : 0);
        $types = isset($_POST['input_type']) ? array_map('sanitize_text_field', $_POST['input_type']) : [];
        $labels = isset($_POST['input_label']) ? array_map('sanitize_text_field', $_POST['input_label']) : [];
        $options_list = isset($_POST['input_options']) ? $_POST['input_options'] : [];
        $requireds = isset($_POST['input_required']) ? array_map('sanitize_text_field', $_POST['input_required']) : [];
        $fields = [];

        foreach ($types as $key => $type) {
            $options = array_filter(array_map('sanitize_text_field', explode("\n", $options_list[$key])));
            $required = isset($requireds[$key]) ? true : false;
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
        update_option('thub_custom_fields', $fields);
        echo '<div class="notice notice-success"><p>' . esc_html__('Fields saved.', 'tickethub') . '</p></div>';
    }

    // Retrieve any existing values
    $saved_fields = get_option('thub_custom_fields', []);

    // The HTML form
?>
    <div class="wrap">
        <form method="post" action="">
            <?php wp_nonce_field('thub_save_ticket_fields', 'thub_ticket_fields_nonce'); ?>
            <h2><?php esc_html_e('General Settings', 'tickethub'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Disable Ticket Attachments', 'tickethub'); ?></th>
                    <td>
                        <input type="checkbox" name="disable_attachments" value="1" <?php checked(get_option('thub_disable_attachments'), 1); ?> />
                        <label for="disable_attachments"><?php esc_html_e('This option disables attachments in the ticket form.', 'tickethub'); ?></label>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e('Ticket Form Fields', 'tickethub'); ?></h2>
            <table class="wp-list-table widefat striped" id="custom_fields_table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Field Type', 'tickethub'); ?></th>
                        <th><?php esc_html_e('Label', 'tickethub'); ?></th>
                        <th><?php esc_html_e('Options (for Select)', 'tickethub'); ?></th>
                        <th><?php esc_html_e('Required', 'tickethub'); ?></th>
                        <th><?php esc_html_e('Actions', 'tickethub'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($saved_fields as $index => $field) {
                    ?>
                        <tr class="field-row">
                            <td>
                                <select name="input_type[]" class="input-type">
                                    <option value="text" <?php selected($field['type'], 'text'); ?>><?php esc_html_e('Text', 'tickethub'); ?></option>
                                    <option value="textarea" <?php selected($field['type'], 'textarea'); ?>><?php esc_html_e('Textarea', 'tickethub'); ?></option>
                                    <option value="select" <?php selected($field['type'], 'select'); ?>><?php esc_html_e('Select', 'tickethub'); ?></option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="input_label[]" value="<?php echo esc_attr($field['label']); ?>" class="regular-text" />
                            </td>
                            <td>
                                <textarea name="input_options[]" class="regular-text" style="visibility: hidden;"><?php echo isset($field['options']) ? esc_textarea(implode("\n", $field['options'])) : ''; ?></textarea>
                            </td>
                            <td>
                                <input type="checkbox" name="input_required[<?php echo esc_attr($index); ?>]" <?php checked($field['required']); ?> />
                            </td>
                            <td>
                                <button type="button" class="button remove_field_button"><span class="dashicons dashicons-no"></span></button>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr id="add_field_row">
                        <td colspan="5">
                            <button type="button" class="button" id="add_field_button"><span class="dashicons dashicons-plus"></span> <?php esc_html_e('Add Field', 'tickethub'); ?></button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button(esc_html__('Save Fields', 'tickethub')); ?>
        </form>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var $table = $('#custom_fields_table');

            function toggleOptionsTextarea(row) {
                row.find('.input-type').each(function() {
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
            $('.field-row').each(function() {
                toggleOptionsTextarea($(this));
            });

            // Add field row
            $('#add_field_button').click(function() {
                var newRow = $('<tr class="field-row">' +
                    '<td><select name="input_type[]" class="input-type"><option value="text"><?php esc_html_e('Text', 'tickethub'); ?></option><option value="textarea"><?php esc_html_e('Textarea', 'tickethub'); ?></option><option value="select"><?php esc_html_e('Select', 'tickethub'); ?></option></select></td>' +
                    '<td><input type="text" name="input_label[]" class="regular-text" placeholder="<?php esc_html_e('Label', 'tickethub'); ?>" /></td>' +
                    '<td><textarea name="input_options[]" class="regular-text" style="visibility: hidden;"></textarea></td>' +
                    '<td><input type="checkbox" name="input_required[]"></td>' +
                    '<td><button type="button" class="button remove_field_button"><span class="dashicons dashicons-no"></span></button></td>' +
                    '</tr>');
                $('#add_field_row').before(newRow);
                toggleOptionsTextarea(newRow);
            });

            // Remove field row
            $table.on('click', '.remove_field_button', function() {
                $(this).closest('tr').remove();
                updateRequiredFieldIndices();
            });

            // Toggle options textarea based on input type selection
            $table.on('change', '.input-type', function() {
                toggleOptionsTextarea($(this).closest('tr'));
            });

            // Update the indices of required fields after adding or removing a row
            function updateRequiredFieldIndices() {
                $('.field-row').each(function(index) {
                    $(this).find('input[name^="input_required"]').attr('name', 'input_required[' + index + ']');
                });
            }

            // Initial update of required field indices
            updateRequiredFieldIndices();
        });
    </script>
<?php
}

?>