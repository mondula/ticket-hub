<?php

add_shortcode('ticket-form', function() {
    if (!current_user_can('submit_tickets') && !current_user_can('administrator')) {
        return '';
    }

    static $ticket_form_enqueue = false;

    if (!$ticket_form_enqueue) {
        wp_enqueue_style('ticket-form-style', PLUGIN_ROOT . 'css/ticket-form.css', array(), '', 'all');
        wp_enqueue_script('ticket-form-script', PLUGIN_ROOT . 'js/ticket-form.js', array('jquery'), '', true);
        $ticket_form_enqueue = true;
    }

    // Fetch custom fields
    $custom_fields = get_option('mts_custom_fields', []);

    ob_start();
    ?>
    <form id="ticket-form" class="ticket-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('submit_ticket_nonce', 'ticket_nonce_field'); ?>
        
        <!-- Standard fields -->
        <label> Username<span>*</span>
            <input type="text" name="your-username" required>
        </label>
        <label> Device<span>*</span>
            <select name="your-device" class="select1" required>
                <option value="Desktop">Desktop</option>
                <option value="Browser Typ und Version">Browser Type and Version</option>
                <option value="Smartphone">Smartphone</option>
                <option value="Tablet">Tablet</option>
            </select>
        </label>
        <label> Description<span>*</span>
            <textarea name="your-description" required></textarea>
        </label>
        <label> Attachments (optional)
            <input type="file" name="your-attachments[]" class="custom-file-upload" multiple accept=".jpg, .jpeg, .png, .gif, .pdf, .doc, .docx, .txt">
        </label>

        <!-- Custom fields generated dynamically -->
        <?php foreach ($custom_fields as $field): ?>
            <label> <?php echo esc_html($field['label']) . $required_attr = $field['required'] ? '<span>*</span>' : '' ; ?>
                <?php 
                $required_attr = $field['required'] ? 'required' : ''; // Check if the field is marked as required
                if ($field['type'] == 'text'): ?>
                    <input type="text" name="custom_<?php echo sanitize_title($field['label']); ?>" <?php echo $required_attr; ?>>
                <?php elseif ($field['type'] == 'textarea'): ?>
                    <textarea name="custom_<?php echo sanitize_title($field['label']); ?>" <?php echo $required_attr; ?>></textarea>
                <?php elseif ($field['type'] == 'select' && !empty($field['options'])): ?>
                    <select name="custom_<?php echo sanitize_title($field['label']); ?>" <?php echo $required_attr; ?> class="select1">
                        <?php foreach ($field['options'] as $option): ?>
                            <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </label>
        <?php endforeach; ?>

        <input type="hidden" name="action" value="submit_ticket_form">
        <input type="submit" class="button1" value="Submit">
    </form>
    <?php
    return ob_get_clean();
});

add_action('admin_post_submit_ticket_form', function() {
    if (!current_user_can('submit_tickets') && !current_user_can('administrator')) {
        wp_die('You do not have permission to submit tickets.');
    }

    // Verify the nonce
    if (!isset($_POST['ticket_nonce_field']) || !wp_verify_nonce($_POST['ticket_nonce_field'], 'submit_ticket_nonce')) {
        wp_die('Security check failed');
    }

    $current_user = wp_get_current_user();
    $first_name = get_user_meta($current_user->ID, 'first_name', true);
    $last_name = get_user_meta($current_user->ID, 'last_name', true);
    $email = $current_user->user_email;

    $username = sanitize_text_field($_POST['your-username']);
    $device = sanitize_text_field($_POST['your-device']);
    $description = sanitize_textarea_field($_POST['your-description']);

    // Create a new 'ticket' post
    $post_id = wp_insert_post([
        'post_status'  => 'pending',
        'post_type'    => 'ticket',
    ]);

    // Check for prefix and suffix options
    $options = get_option('mts_options');
    $prefix = isset($options['ticket_prefix']) ? $options['ticket_prefix'] : '';
    $suffix = isset($options['ticket_suffix']) ? $options['ticket_suffix'] : '';
    $formatted_post_id = sprintf('%06d', $post_id);
    $id = $prefix . $formatted_post_id . $suffix;
    wp_update_post([
        'ID'         => $post_id,
        'post_title' => sprintf('%s - %s %s - %s', $id, $first_name, $last_name, $device)
    ]);

    // Add post meta
    update_post_meta($post_id, 'id', $id);
    update_post_meta($post_id, 'status', 'New');
    update_post_meta($post_id, 'username', $username);
    update_post_meta($post_id, 'device', $device);
    update_post_meta($post_id, 'description', $description);

    // Save custom fields as post meta
    $custom_fields = get_option('mts_custom_fields', []);
    $custom_fields_content = "";
    foreach ($custom_fields as $field) {
        if (isset($_POST['custom_' . sanitize_title($field['label'])])) {
            $field_value = sanitize_text_field($_POST['custom_' . sanitize_title($field['label'])]);
            update_post_meta($post_id, 'custom_' . sanitize_title($field['label']), $field_value);
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
    $message = "Name: $name\nEmail: $email\nUsername: $username\nDevice: $device\nDescription: $description\n";
    if (!empty($custom_fields_content)) {
        $message .= "Additional Fields:\n" . $custom_fields_content;
    }
    if (count($attachment_urls) > 0) {
        $message .= "Attachments:\n" . implode("\n", $attachment_urls) . "\n";
    }

    $subject = 'New Ticket Submitted';
    $headers = ['Content-Type: text/plain; charset=UTF-8'];

    wp_mail(get_option('admin_email'), $subject, $message, $headers);

    $user_message = "Hello $name,\n\nThank you for submitting your ticket. It will be now be reviewed.\n\nBest regards,\nYour Support Team";
    wp_mail($email, 'Confirmation of Your Ticket Submission', $user_message, $headers);

    wp_send_json_success('Ticket submitted successfully');

    exit;
});
