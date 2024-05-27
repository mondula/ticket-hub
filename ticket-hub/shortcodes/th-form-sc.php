<?php

add_shortcode('th_form', function () {
    if (!current_user_can('submit_tickets') && !current_user_can('administrator')) {
        return '';
    }

    static $ticket_form_enqueue = false;

    if (!$ticket_form_enqueue) {
        wp_enqueue_style('th-form-style', PLUGIN_ROOT . 'css/th-form.css', array(), '', 'all');
        wp_enqueue_script('th-form-script', PLUGIN_ROOT . 'js/th-form.js', array('jquery'), '', true);
        $ticket_form_enqueue = true;
    }

    // Fetch custom fields and attachment setting
    $custom_fields = get_option('th_custom_fields', []);
    $disable_attachments = get_option('th_disable_attachments', 0); // 0 is unchecked by default

    ob_start();
?>
    <form id="th-form" class="th-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('submit_ticket_nonce', 'ticket_nonce_field'); ?>

        <!-- Standard fields -->
        <label><?php _e('Short Description', 'tickethub'); ?><span>*</span>
            <input type="text" name="your-short-desc" required>
        </label>
        <label><?php _e('Description', 'tickethub'); ?><span>*</span>
            <textarea name="your-description" required></textarea>
        </label>
        <?php if (!$disable_attachments) : // Check if attachments are enabled 
        ?>
            <label><?php _e('Attachments (optional)', 'tickethub'); ?>
                <input type="file" name="your-attachments[]" class="th-file-upload" multiple accept=".jpg, .jpeg, .png, .gif, .pdf, .doc, .docx, .txt">
            </label>
        <?php endif; ?>

        <!-- Custom fields generated dynamically -->
        <?php foreach ($custom_fields as $field) : ?>
            <label> <?php echo esc_html($field['label']) . ($field['required'] ? '<span>*</span>' : ''); ?>
                <?php
                $required_attr = $field['required'] ? 'required' : ''; // Check if the field is marked as required
                if ($field['type'] == 'text') : ?>
                    <input type="text" name="thcf_<?php echo sanitize_title($field['label']); ?>" <?php echo $required_attr; ?>>
                <?php elseif ($field['type'] == 'textarea') : ?>
                    <textarea name="thcf_<?php echo sanitize_title($field['label']); ?>" <?php echo $required_attr; ?>></textarea>
                <?php elseif ($field['type'] == 'select' && !empty($field['options'])) : ?>
                    <select name="thcf_<?php echo sanitize_title($field['label']); ?>" <?php echo $required_attr; ?> class="th-select">
                        <?php foreach ($field['options'] as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </label>
        <?php endforeach; ?>

        <input type="hidden" name="action" value="submit_ticket_form">
        <input type="submit" class="th-button" value="<?php _e('Submit', 'tickethub'); ?>">
    </form>
<?php
    return ob_get_clean();
});


add_action('admin_post_submit_ticket_form', function () {
    if (!current_user_can('submit_tickets') && !current_user_can('administrator')) {
        wp_die(__('You do not have permission to submit tickets.', 'tickethub'));
    }

    if (!isset($_POST['ticket_nonce_field']) || !wp_verify_nonce($_POST['ticket_nonce_field'], 'submit_ticket_nonce')) {
        wp_die(__('Security check failed', 'tickethub'));
    }

    $current_user = wp_get_current_user();
    $first_name = get_user_meta($current_user->ID, 'first_name', true);
    $last_name = get_user_meta($current_user->ID, 'last_name', true);
    $email = $current_user->user_email;

    $title = sanitize_text_field($_POST['your-short-desc']);
    $description = sanitize_textarea_field($_POST['your-description']);

    $post_id = wp_insert_post([
        'post_status'  => 'pending',
        'post_type'    => 'th_ticket',
    ]);

    $options = get_option('th_options');
    $prefix = isset($options['ticket_prefix']) ? $options['ticket_prefix'] : '';
    $suffix = isset($options['ticket_suffix']) ? $options['ticket_suffix'] : '';
    $formatted_post_id = sprintf('%06d', $post_id);
    $id = $prefix . $formatted_post_id . $suffix;
    wp_update_post([
        'ID'         => $post_id,
        'post_title' => $title
    ]);

    update_post_meta($post_id, 'th_ticket_id', $id);
    update_post_meta($post_id, 'th_ticket_status', 'New');
    update_post_meta($post_id, 'th_ticket_description', $description);

    $custom_fields = get_option('th_custom_fields', []);
    $custom_fields_content = "";
    foreach ($custom_fields as $field) {
        if (isset($_POST['thcf_' . sanitize_title($field['label'])])) {
            $field_value = sanitize_text_field($_POST['thcf_' . sanitize_title($field['label'])]);
            update_post_meta($post_id, 'thcf_' . sanitize_title($field['label']), $field_value);
            $custom_fields_content .= $field['label'] . ": " . $field_value . "\n";
        }
    }

    $attachments = $_FILES['your-attachments'];
    $attachment_urls = [];

    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    foreach ($attachments['name'] as $key => $value) {
        if ($attachments['name'][$key]) {
            $file = [
                'name' => $attachments['name'][$key],
                'type' => $attachments['type'][$key],
                'tmp_name' => $attachments['tmp_name'][$key],
                'error' => $attachments['error'][$key],
                'size' => $attachments['size'][$key]
            ];
            $uploaded_file = wp_handle_upload($file, ['test_form' => false]);

            if (!isset($uploaded_file['error'])) {
                $attachment_urls[] = $uploaded_file['url'];
                $attach_id = wp_insert_attachment([
                    'post_mime_type' => $file['type'],
                    'post_title' => sanitize_file_name($file['name']),
                    'post_content' => '',
                    'post_status' => 'inherit'
                ], $uploaded_file['file'], $post_id);  // Notice the $post_id, which ties the attachment to the ticket

                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $uploaded_file['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);
            }
        }
    }

    $name = '';

    if (empty($first_name) && empty($last_name)) {
        $name = get_the_author_meta('display_name', $author_id); // Get the author's display name
    } else {
        $name = trim($first_name . ' ' . $last_name);
    }
    $message = "Name: $name\nEmail: $email\nDescription: $description\n";
    if (!empty($custom_fields_content)) {
        $message .= "Additional Fields:\n" . $custom_fields_content;
    }
    if (count($attachment_urls) > 0) {
        $message .= "Attachments:\n" . implode("\n", $attachment_urls) . "\n";
    }

    $subject = __('New Ticket Submitted', 'tickethub');
    $headers = ['Content-Type: text/plain; charset=UTF-8'];

    wp_mail(get_option('admin_email'), $subject, $message, $headers);

    $user_message = sprintf(__('Hello %s,\n\nThank you for submitting your ticket. It will now be reviewed.\n\nBest regards,\nYour Support Team', 'tickethub'), $name);
    wp_mail($email, __('Confirmation of Your Ticket Submission', 'tickethub'), $user_message, $headers);

    wp_send_json_success(__('Ticket submitted successfully', 'tickethub'));

    exit;
});
