<?php

add_action( 'init', function() {
	register_post_type( 'ticket', array(
		'labels' => array(
			'name' => 'Tickets',
			'singular_name' => 'Ticket',
			'menu_name' => 'Tickets',
			'all_items' => 'Tickets',
			'edit_item' => 'Edit Ticket',
			'view_item' => 'View Ticket',
			'view_items' => 'View Tickets',
			'add_new_item' => 'Add New Ticket',
			'add_new' => 'Add New Ticket',
			'new_item' => 'New Ticket',
			'parent_item_colon' => 'Parent Ticket:',
			'search_items' => 'Search Tickets',
			'not_found' => 'No tickets found',
			'not_found_in_trash' => 'No tickets found in Trash',
			'archives' => 'Ticket Archives',
			'attributes' => 'Ticket Attributes',
			'insert_into_item' => 'Insert into ticket',
			'uploaded_to_this_item' => 'Uploaded to this ticket',
			'filter_items_list' => 'Filter tickets list',
			'filter_by_date' => 'Filter tickets by date',
			'items_list_navigation' => 'Tickets list navigation',
			'items_list' => 'Tickets list',
			'item_published' => 'Ticket published.',
			'item_published_privately' => 'Ticket published privately.',
			'item_reverted_to_draft' => 'Ticket reverted to draft.',
			'item_scheduled' => 'Ticket scheduled.',
			'item_updated' => 'Ticket updated.',
			'item_link' => 'Ticket Link',
			'item_link_description' => 'A link to a ticket.',
		),
		'description' => 'These are the tickets are were created by people.',
		'public' => true,
		'show_in_menu' => 'mts-main-menu',
		'show_in_rest' => false,
		'menu_position' => 1,
		'supports' => array(
			0 => 'title',
			1 => 'author',
			2 => 'comments',
		),
		'has_archive' => 'ticket-archive',
		'rewrite' => array(
			'feeds' => false,
			'pages' => false,
		),
		'can_export' => false,
		'delete_with_user' => false,
	));
});

add_action('edit_form_after_title', function($post) {
    if ($post->post_type != 'ticket') return; // Ensure this is a 'ticket' post type

    // Custom fields definitions with options for select fields
    $fields = [
        'id' => ['type' => 'text', 'label' => 'ID'],
        'status' => ['type' => 'select', 'label' => 'Status', 'options' => ['New' => 'New', 'Processing' => 'Processing', 'Done' => 'Done']],
        'type' => [
            'type' => 'select',
            'label' => 'Type',
            'options' => [
                '' => '- Select Type -', // Add this line to allow an empty option
                'Support' => 'Support',
                'Bug report' => 'Bug report',
                'Change request' => 'Change request'
            ]
        ],
        'username' => ['type' => 'text', 'label' => 'Username'],
        'device' => ['type' => 'select', 'label' => 'Device', 'options' => ['Desktop' => 'Desktop', 'Browser Type and Version' => 'Browser Typ and Version', 'Smartphone' => 'Smartphone', 'Tablet' => 'Tablet']],
        'description' => ['type' => 'textarea', 'label' => 'Description'],
    ];

    // Output HTML for each field
    foreach ($fields as $key => $field) {
        $value = get_post_meta($post->ID, $key, true);
        echo "<label for='{$key}'><h3>{$field['label']}</h3></label>";

        if ($field['type'] === 'select') {
            echo "<select id='{$key}' name='{$key}'>";
            foreach ($field['options'] as $optionKey => $optionValue) {
                $selected = ($value == $optionKey) ? 'selected' : '';
                echo "<option value='{$optionKey}' {$selected}>{$optionValue}</option>";
            }
            echo "</select><br/><br/>";
        } elseif ($field['type'] === 'textarea') {
            echo "<textarea id='{$key}' name='{$key}' rows='4' cols='50'>" . esc_attr($value) . "</textarea><br/><br/>";
        } else {
            echo "<input type='text' id='{$key}' name='{$key}' value='" . esc_attr($value) . "'/><br/><br/>";
        }
    }

    // Fetch attachments
    $args = array(
        'post_type'      => 'attachment',
        'posts_per_page' => -1,
        'post_status'    => 'inherit',
        'post_parent'    => $post->ID
    );
    $attachments = get_posts($args);

    if ($attachments) {
        echo '<label><h3>Attachments:</h3></label>';
        echo '<ul>';
        foreach ($attachments as $attachment) {
            $attachment_url = wp_get_attachment_url($attachment->ID);
            echo '<li><a href="' . esc_url($attachment_url) . '" target="_blank">' . basename($attachment_url) . '</a></li>';
        }
        echo '</ul><br>';
    }

    // Add a nonce field for security
    wp_nonce_field('save_ticket_meta', 'ticket_meta_nonce');
});

add_action('save_post', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['ticket_meta_nonce']) || !wp_verify_nonce($_POST['ticket_meta_nonce'], 'save_ticket_meta')) return;

    // Custom fields keys
    $fields = ['id', 'status', 'type', 'username', 'device', 'description'];

    // Save each field value
    foreach ($fields as $field) {
        if (array_key_exists($field, $_POST)) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
});

add_action('updated_post_meta', function($meta_id, $post_id, $meta_key, $meta_value) {
    if ($meta_key == 'status') {

        $author_id = get_post_field('post_author', $post_id);
        $email = get_the_author_meta('email', $author_id);
        $id = get_post_meta($post_id, 'id', true);
        
        // Check if a valid email address is retrieved
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Set up the email details
            $subject = 'Status Update Notification';
            $message = 'The status of your ticket (' . $id . ') has been updated to: ' . $meta_value . '.';
            $headers = array('Content-Type: text/html; charset=UTF-8');
            
            // Send the email notification
            wp_mail($email, $subject, $message, $headers);
        }
    }
}, 10, 4);

