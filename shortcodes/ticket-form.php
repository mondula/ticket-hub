<?php

add_shortcode('ticket-form', function() {

    if (!current_user_can('submit_tickets')) {
        return '';
    }

    static $ticket_form_enqueue = false;

    if (!$ticket_form_enqueue) {
        wp_enqueue_style( 'ticket-form-style', PLUGIN_ROOT . 'css/ticket-form.css', array(), '', 'all' );
        $ticket_form_enqueue = true;
    }

    ob_start();
    ?>
    <form class="ticket-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('submit_ticket_nonce', 'ticket_nonce_field'); ?>
        <label> Username*
            <input type="text" name="your-username" required>
        </label>
        <label> Device*
            <select name="your-device" class="select1" required>
                <option value="Desktop">Desktop</option>
                <option value="Browser Typ und Version">Browser Type and Version</option>
                <option value="Smartphone">Smartphone</option>
                <option value="Tablet">Tablet</option>
            </select>
        </label>
        <label> Description*
            <textarea name="your-description" required></textarea>
        </label>
        <label> Attachments (optional)
            <input type="file" name="your-attachments[]" class="custom-file-upload" multiple accept=".jpg, .jpeg, .png, .gif, .pdf, .doc, .docx, .txt">
        </label>
        <input type="hidden" name="action" value="submit_ticket_form">
        <input type="submit" class="button1" value="Senden">
    </form>
    <?php
    return ob_get_clean();
});

add_action('admin_post_submit_ticket_form', function() {
    if (!current_user_can('submit_tickets')) {
        wp_die('You do not have permission to submit tickets.');
    }

    // Verify the nonce
    if (!isset($_POST['ticket_nonce_field']) || !wp_verify_nonce($_POST['ticket_nonce_field'], 'submit_ticket_nonce')) {
        wp_die('Security check failed');
    }

    $id = uniqid();

    $current_user = wp_get_current_user();
    $first_name = get_user_meta($current_user->ID, 'first_name', true);
    $last_name = get_user_meta($current_user->ID, 'last_name', true);
    $email = $current_user->user_email;

    $username = sanitize_text_field($_POST['your-username']);
    $device = sanitize_text_field($_POST['your-device']);
    $description = sanitize_textarea_field($_POST['your-description']);

    // Create a new 'ticket' post
    $post_id = wp_insert_post([
        'post_title'   => $id . ' - ' . $first_name . ' ' . $last_name . ' - ' . $device,
        'post_status'  => 'publish',
        'post_type'    => 'ticket',
    ]);

    // Add post meta
    update_post_meta($post_id, 'id', $id);
    update_post_meta($post_id, 'status', 'New');
    update_post_meta($post_id, 'username', $username);
    update_post_meta($post_id, 'device', $device);
    update_post_meta($post_id, 'description', $description);

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

    $name = trim($first_name . ' ' . $last_name);
    $message = "Name: $name\nEmail: $email\nUsername: $username\nDevice: $device\nDescription: $description\n";
    if (count($attachment_urls) > 0) {
        $message .= "Attachments:\n" . implode("\n", $attachment_urls) . "\n";
    }

    $subject = 'New Ticket Submitted';
    $headers = ['Content-Type: text/plain; charset=UTF-8'];

    wp_mail(get_option('admin_email'), $subject, $message, $headers);

    $user_message = "Hello $name,\n\nThank you for submitting your ticket. We will review it and contact you shortly.\n\nBest regards,\nYour Support Team";
    wp_mail($email, 'Confirmation of Your Ticket Submission', $user_message, $headers);

    wp_redirect(home_url('/tickets'));
    exit;
});
