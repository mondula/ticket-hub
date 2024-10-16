<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode('thub_form', function () {
    if (!current_user_can('submit_tickets') && !current_user_can('administrator')) {
        return '';
    }

    // Fetch custom fields and attachment setting
    $custom_fields = get_option('thub_custom_fields', []);
    $disable_attachments = get_option('thub_disable_attachments', 0); // 0 is unchecked by default

    // Get the maximum upload size from PHP configuration
    $max_upload_size = wp_max_upload_size();

    ob_start();
?>
    <form id="thub-form" class="thub-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('submit_ticket_nonce', 'ticket_nonce_field'); ?>

        <!-- Standard fields -->
        <label><?php esc_html_e('Short Description', 'ticket-hub'); ?><span>*</span>
            <input type="text" name="your-short-desc" required>
        </label>
        <label><?php esc_html_e('Description', 'ticket-hub'); ?><span>*</span>
            <textarea name="your-description" required></textarea>
        </label>
        <?php if (!$disable_attachments) : // Check if attachments are enabled 
        ?>
            <label>
                <?php
                    // translators: %s: Maximum upload size for attachments.
                    printf(esc_html__('Attachments (up to 3 files, max file size: %s)', 'ticket-hub'), esc_attr(size_format($max_upload_size))); 
                ?>
                <input type="file" name="your-attachments[]" class="thub-file-upload" multiple accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .txt, .xls, .xlsx, .csv" data-max-files="3" data-max-size="<?php echo esc_attr($max_upload_size); ?>">
            </label>
        <?php endif; ?>
        <!-- Custom fields generated dynamically -->
        <?php foreach ($custom_fields as $field) : ?>
            <label> <?php echo esc_html($field['label']) . ($field['required'] ? '<span>*</span>' : ''); ?>
                <?php
                $required_attr = $field['required'] ? 'required' : ''; // Check if the field is marked as required
                if ($field['type'] == 'text') : ?>
                    <input type="text" name="thcf_<?php echo esc_attr(sanitize_title($field['label'])); ?>" <?php echo esc_attr($required_attr); ?>>
                <?php elseif ($field['type'] == 'textarea') : ?>
                    <textarea name="thcf_<?php echo esc_attr(sanitize_title($field['label'])); ?>" <?php echo esc_attr($required_attr); ?>></textarea>
                <?php elseif ($field['type'] == 'select' && !empty($field['options'])) : ?>
                    <select name="thcf_<?php echo esc_attr(sanitize_title($field['label'])); ?>" <?php echo esc_attr($required_attr); ?> class="thub-select">
                        <?php foreach ($field['options'] as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </label>
        <?php endforeach; ?>

        <input type="hidden" name="action" value="submit_ticket_form">
        <input type="submit" class="thub-button" value="<?php esc_html_e('Submit', 'ticket-hub'); ?>">
    </form>
<?php
    return ob_get_clean();
});

add_action('admin_post_submit_ticket_form', function () {
    if (!current_user_can('submit_tickets') && !current_user_can('administrator')) {
        wp_send_json_error(__('You do not have permission to submit tickets.', 'ticket-hub'));
    }

    if (!isset($_POST['ticket_nonce_field']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ticket_nonce_field'])), 'submit_ticket_nonce')) {
        wp_send_json_error(__('Security check failed', 'ticket-hub'));
    }

    $attachments = [];
    $attachment_urls = [];
    $attachment_files = [];

    if (isset($_FILES['your-attachments'])) {
        $attachments = isset($_FILES['your-attachments']['name']) ? array_map('sanitize_file_name', $_FILES['your-attachments']['name']) : [];
        $attachments_tmp = isset($_FILES['your-attachments']['tmp_name']) ? array_map('sanitize_text_field', $_FILES['your-attachments']['tmp_name']) : [];
        $attachments_type = isset($_FILES['your-attachments']['type']) ? array_map('sanitize_mime_type', $_FILES['your-attachments']['type']) : [];
        $attachments_error = isset($_FILES['your-attachments']['error']) ? array_map('intval', $_FILES['your-attachments']['error']) : [];
        $attachments_size = isset($_FILES['your-attachments']['size']) ? array_map('intval', $_FILES['your-attachments']['size']) : [];
    }

    if (!empty($attachments)) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        // Check if more than 3 files are uploaded
        if (count($attachments) > 3) {
            wp_send_json_error(__('You can upload a maximum of 3 files.', 'ticket-hub'));
        }

        foreach ($attachments as $key => $value) {
            if ($value) {
                // File validation
                $file_type = wp_check_filetype($value);
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'csv', 'ppt', 'pptx', 'zip', 'rar'];
                if (!in_array($file_type['ext'], $allowed_types)) {
                    wp_send_json_error(__('Invalid file type. Only JPG, JPEG, PNG, GIF, PDF, DOC, DOCX, TXT, XLS, XLSX, CSV, PPT, PPTX, ZIP, and RAR files are allowed.', 'ticket-hub'));
                }

                $file = [
                    'name' => $value,
                    'type' => $attachments_type[$key],
                    'tmp_name' => $attachments_tmp[$key],
                    'error' => $attachments_error[$key],
                    'size' => $attachments_size[$key]
                ];
                $uploaded_file = wp_handle_upload($file, ['test_form' => false]);

                if (!isset($uploaded_file['error'])) {
                    $attachment_urls[] = esc_url($uploaded_file['url']);
                    $attachment_files[] = [
                        'file' => $uploaded_file['file'],
                        'type' => sanitize_mime_type($file['type']),
                        'name' => sanitize_file_name($file['name'])
                    ];
                } else {
                    wp_send_json_error(__('There was an error uploading your file: ', 'ticket-hub') . esc_html($uploaded_file['error']));
                }
            }
        }

        // If all validations pass, create the ticket
        $current_user = wp_get_current_user();
        $first_name = get_user_meta($current_user->ID, 'first_name', true);
        $last_name = get_user_meta($current_user->ID, 'last_name', true);
        $email = sanitize_email($current_user->user_email);

        $title = isset($_POST['your-short-desc']) ? sanitize_text_field(wp_unslash($_POST['your-short-desc'])) : '';
        $description = isset($_POST['your-description']) ? sanitize_textarea_field(wp_unslash($_POST['your-description'])) : '';

        if (empty($title) || empty($description)) {
            wp_send_json_error(__('Short description and description are required fields.', 'ticket-hub'));
        }

        $post_id = wp_insert_post([
            'post_status'  => 'pending',
            'post_type'    => 'thub_ticket',
        ]);

        if (is_wp_error($post_id)) {
            wp_send_json_error(__('Error creating the ticket. Please try again.', 'ticket-hub'));
        }

        $options = get_option('thub_options');
        $prefix = isset($options['thub_ticket_prefix']) ? sanitize_text_field($options['thub_ticket_prefix']) : '';
        $suffix = isset($options['thub_ticket_suffix']) ? sanitize_text_field($options['thub_ticket_suffix']) : '';
        $formatted_post_id = sprintf('%06d', $post_id);
        $id = $prefix . $formatted_post_id . $suffix;
        wp_update_post([
            'ID'         => $post_id,
            'post_title' => $title
        ]);

        update_post_meta($post_id, 'thub_ticket_id', $id);
        update_post_meta($post_id, 'thub_ticket_status', 'New');
        update_post_meta($post_id, 'thub_ticket_description', $description);

        $custom_fields = get_option('thub_custom_fields', []);
        $custom_fields_content = "";
        foreach ($custom_fields as $field) {
            $field_key = 'thcf_' . sanitize_title($field['label']);
            if (isset($_POST[$field_key])) {
                $field_value = sanitize_text_field(wp_unslash($_POST[$field_key]));
                update_post_meta($post_id, $field_key, $field_value);
                $custom_fields_content .= esc_html($field['label']) . ": " . esc_html($field_value) . "\n";
            }
        }

        // Attach files to the ticket
        foreach ($attachment_files as $file) {
            $attach_id = wp_insert_attachment([
                'post_mime_type' => $file['type'],
                'post_title' => $file['name'],
                'post_content' => '',
                'post_status' => 'inherit'
            ], $file['file'], $post_id);  // Attachments are tied to the ticket here

            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $file['file']);
            wp_update_attachment_metadata($attach_id, $attach_data);
        }

        $name = '';

        if (empty($first_name) && empty($last_name)) {
            $name = esc_html(get_the_author_meta('display_name', $current_user->ID));
        } else {
            $name = trim(esc_html($first_name) . ' ' . esc_html($last_name));
        }
        $message = "Name: $name\nEmail: $email\nDescription: $description\n";
        if (!empty($custom_fields_content)) {
            $message .= "Additional Fields:\n" . $custom_fields_content;
        }
        if (count($attachment_urls) > 0) {
            $message .= "Attachments:\n" . implode("\n", $attachment_urls) . "\n";
        }

        $subject = __('New Ticket Submitted', 'ticket-hub');
        $headers = ['Content-Type: text/plain; charset=UTF-8'];

        wp_mail(sanitize_email(get_option('admin_email')), $subject, esc_html($message), $headers);

        // translators: %s: User name.
        $user_message = sprintf(__('Hello %s,\n\nThank you for submitting your ticket. It will now be reviewed.\n\nBest regards,\nYour Support Team', 'ticket-hub'), $name);
        wp_mail($email, __('Confirmation of Your Ticket Submission', 'ticket-hub'), esc_html($user_message), $headers);

        wp_send_json_success(__('Ticket submitted successfully', 'ticket-hub'));
    }
});
