<?php
if ( ! defined( 'ABSPATH' ) ) exit;


function thub_ticket_editor_page()
{
    // Check if the form has been submitted
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('thub_save_ticket_fields', 'thub_ticket_fields_nonce')) {
        // Process saving multiple fields, their options, and required status
        update_option('thub_disable_attachments', isset($_POST['disable_attachments']) ? 1 : 0);
        $types = isset($_POST['input_type']) ? array_map('sanitize_text_field', wp_unslash($_POST['input_type'])) : [];
        $labels = isset($_POST['input_label']) ? array_map('sanitize_text_field', wp_unslash($_POST['input_label'])) : [];
        $options_list = isset($_POST['input_options']) ? array_map('sanitize_textarea_field', wp_unslash($_POST['input_options'])) : [];
        $requireds = isset($_POST['input_required']) ? array_map('sanitize_text_field', wp_unslash($_POST['input_required'])) : [];
        $fields = [];

        foreach ($types as $key => $type) {
            $options = array_filter(array_map('trim', explode("\n", $options_list[$key])));
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
        echo '<div class="notice notice-success"><p>' . esc_html__('Fields saved.', 'ticket-hub') . '</p></div>';
    }

    // Retrieve any existing values
    $saved_fields = get_option('thub_custom_fields', []);

    // The HTML form
?>
    <div class="wrap">
        <form method="post" action="">
            <?php wp_nonce_field('thub_save_ticket_fields', 'thub_ticket_fields_nonce'); ?>
            <h2><?php esc_html_e('General Settings', 'ticket-hub'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Disable Ticket Attachments', 'ticket-hub'); ?></th>
                    <td>
                        <input type="checkbox" name="disable_attachments" value="1" <?php checked(get_option('thub_disable_attachments'), 1); ?> />
                        <label for="disable_attachments"><?php esc_html_e('This option disables attachments in the ticket form.', 'ticket-hub'); ?></label>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e('Ticket Form Fields', 'ticket-hub'); ?></h2>
            <table class="wp-list-table widefat striped" id="thub_fields_table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Field Type', 'ticket-hub'); ?></th>
                        <th><?php esc_html_e('Label', 'ticket-hub'); ?></th>
                        <th><?php esc_html_e('Options (for Select)', 'ticket-hub'); ?></th>
                        <th><?php esc_html_e('Required', 'ticket-hub'); ?></th>
                        <th><?php esc_html_e('Actions', 'ticket-hub'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($saved_fields as $index => $field) {
                    ?>
                        <tr class="field-row">
                            <td>
                                <select name="input_type[]" class="input-type">
                                    <option value="text" <?php selected($field['type'], 'text'); ?>><?php esc_html_e('Text', 'ticket-hub'); ?></option>
                                    <option value="textarea" <?php selected($field['type'], 'textarea'); ?>><?php esc_html_e('Textarea', 'ticket-hub'); ?></option>
                                    <option value="select" <?php selected($field['type'], 'select'); ?>><?php esc_html_e('Select', 'ticket-hub'); ?></option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="input_label[]" value="<?php echo esc_attr($field['label']); ?>" class="regular-text" />
                            </td>
                            <td>
                                <textarea name="input_options[]" class="regular-text" <?php echo $field['type'] !== 'select' ? 'style="display: none;"' : ''; ?>><?php echo isset($field['options']) ? esc_textarea(implode("\n", $field['options'])) : ''; ?></textarea>
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
                            <button type="button" class="button" id="add_field_button"><span class="dashicons dashicons-plus"></span> <?php esc_html_e('Add Field', 'ticket-hub'); ?></button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button(esc_html__('Save Fields', 'ticket-hub')); ?>
        </form>
    </div>
<?php
}

?>