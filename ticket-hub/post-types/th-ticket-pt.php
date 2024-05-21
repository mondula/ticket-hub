<?php

add_action('init', 'register_ticket_taxonomy');

function register_ticket_taxonomy()
{
    register_taxonomy(
        'th_ticket_tag',  // Taxonomy name
        'th_ticket',      // Post type name
        array(
            'labels' => array(
                'name' => 'Ticket Tags',
                'singular_name' => 'Ticket Tag',
                'menu_name' => 'Ticket Tags',
                'all_items' => 'All Ticket Tags',
                'edit_item' => 'Edit Ticket Tag',
                'view_item' => 'View Ticket Tag',
                'update_item' => 'Update Ticket Tag',
                'add_new_item' => 'Add New Ticket Tag',
                'new_item_name' => 'New Ticket Tag Name',
                'search_items' => 'Search Ticket Tags',
                'popular_items' => 'Popular Ticket Tags',
                'separate_items_with_commas' => 'Separate ticket tags with commas',
                'add_or_remove_items' => 'Add or remove ticket tags',
                'choose_from_most_used' => 'Choose from the most used ticket tags',
                'not_found' => 'No ticket tags found'
            ),
            'public' => true,
            'show_in_nav_menus' => true,
            'show_ui' => true,
            'show_tagcloud' => true,
            'hierarchical' => false, // This is false as tags are not hierarchical like categories
            'rewrite' => array(
                'slug' => false, // Customize the permalink structure
            ),
            'show_in_rest' => true // Enable the REST API endpoint
        )
    );
}

add_action('init', function () {
    // Get the option value
    $options = get_option('th_plus_options');
    $auto_publish = isset($options['auto_publish']) && $options['auto_publish'] ? true : false;

    register_post_type('th_ticket', array(
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
        'description' => 'These are the tickets created by people.',
        'public' => true,
        'show_in_menu' => 'th_main_menu',
        'show_in_rest' => true,
        'menu_position' => 1,
        'supports' => array('title', 'author', 'comments'),
        'has_archive' => false,
        'rewrite' => array(
            'feeds' => false,
            'pages' => false,
        ),
        'can_export' => true,
        'delete_with_user' => false,
        'taxonomies' => array('th_ticket_tag'), // Enable tag support
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'capabilities' => array(
            'publish_posts' => $auto_publish ? 'publish_th_tickets' : 'draft_th_tickets',
        ),
    ));
});


add_action('edit_form_after_title', function ($post) {
    if ($post->post_type != 'th_ticket') return; // Ensure this is a 'th_ticket' post type

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

    // Retrieve the saved custom fields
    $saved_custom_fields = get_option('th_custom_fields', []);

    // Output HTML for each saved custom field
    foreach ($saved_custom_fields as $field) {
        $value = get_post_meta($post->ID, 'custom_' . sanitize_title($field['label']), true);
        echo "<label for='custom_" . sanitize_title($field['label']) . "'><h3>" . esc_html($field['label']) . "</h3></label>";

        if ($field['type'] === 'select') {
            echo "<select id='custom_" . sanitize_title($field['label']) . "' name='custom_" . sanitize_title($field['label']) . "'>";
            foreach ($field['options'] as $option) {
                $selected = ($value == $option) ? 'selected' : '';
                echo "<option value='" . esc_attr($option) . "' {$selected}>" . esc_html($option) . "</option>";
            }
            echo "</select><br/><br/>";
        } elseif ($field['type'] === 'textarea') {
            echo "<textarea id='custom_" . sanitize_title($field['label']) . "' name='custom_" . sanitize_title($field['label']) . "' rows='4' cols='50'>" . esc_textarea($value) . "</textarea><br/><br/>";
        } else {
            echo "<input type='text' id='custom_" . sanitize_title($field['label']) . "' name='custom_" . sanitize_title($field['label']) . "' value='" . esc_attr($value) . "'/><br/><br/>";
        }
    }

    // Add a nonce field for security
    wp_nonce_field('save_ticket_meta', 'ticket_meta_nonce');
});

add_action('save_post', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['ticket_meta_nonce']) || !wp_verify_nonce($_POST['ticket_meta_nonce'], 'save_ticket_meta')) return;

    // Custom fields keys
    $fields = ['id', 'status', 'type', 'description'];

    // Save each field value
    foreach ($fields as $field) {
        if (array_key_exists($field, $_POST)) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

    // Retrieve the saved custom fields
    $saved_custom_fields = get_option('th_custom_fields', []);

    // Save each custom field value
    foreach ($saved_custom_fields as $field) {
        $field_key = 'custom_' . sanitize_title($field['label']);
        if (isset($_POST[$field_key])) {
            update_post_meta($post_id, $field_key, sanitize_text_field($_POST[$field_key]));
        }
    }
});

add_action('updated_post_meta', function ($meta_id, $post_id, $meta_key, $meta_value) {
    if ($meta_key == 'status') {

        $author_id = get_post_field('post_author', $post_id);
        $email = get_the_author_meta('email', $author_id);
        $id = get_post_meta($post_id, 'id', true);
        $ticket_link = get_permalink($post_id);

        if ($meta_value == 'Done') {
            update_post_meta($post_id, 'completed_date', current_time('mysql'));
        }

        // Check if a valid email address is retrieved
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Set up the email details
            $subject = 'Status Update Notification';
            $message = 'The status of your ticket (ID: <a href="' . $ticket_link . '">' . $id . '</a>) has been updated to: ' . $meta_value . '.';
            $headers = array('Content-Type: text/html; charset=UTF-8');

            // Send the email notification
            wp_mail($email, $subject, $message, $headers);
        }
    }
}, 10, 4);

add_action('transition_post_status', function ($new_status, $old_status, $post) {
    if ($post->post_type != 'th_ticket') {
        return;
    }

    // Check if the status is changing to 'publish' or 'archive'
    if (($new_status == 'publish' || $new_status == 'archive') && $old_status != $new_status) {
        $author_id = $post->post_author;
        $email = get_the_author_meta('email', $author_id);
        $ticket_id = get_post_meta($post->ID, 'id', true);
        $status = ucfirst($new_status);
        $ticket_link = get_permalink($post->ID);

        // Check if a valid email address is retrieved
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Set up the email details
            $subject = "Ticket Status Update: $status";
            $message = "The status of your ticket (ID: <a href='$ticket_link'>$ticket_id</a>) has changed to: $status.";
            $headers = array('Content-Type: text/html; charset=UTF-8');

            // Send the email notification
            wp_mail($email, $subject, $message, $headers);
        }
    }
}, 10, 3);

add_action('wp_insert_comment', function ($comment_id, $comment) {
    if (!isset($comment->comment_post_ID)) {
        return;
    }

    $post_id = $comment->comment_post_ID;
    $post = get_post($post_id);

    // Check if the comment is made on a 'th_ticket' post type
    if ($post->post_type == 'th_ticket') {
        $author_id = $post->post_author;
        $email = get_the_author_meta('email', $author_id);
        $ticket_id = get_post_meta($post_id, 'id', true);
        $ticket_link = get_permalink($post_id);
        $comment_author = $comment->comment_author;
        $comment_content = $comment->comment_content;

        // Check if a valid email address is retrieved
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Set up the email details
            $subject = "New Comment on Ticket ID: $ticket_id";
            $message = "A new comment has been posted by $comment_author on your ticket (ID: <a href='$ticket_link'>$ticket_id</a>):<br/><br/> \"$comment_content\"";
            $headers = array('Content-Type: text/html; charset=UTF-8');

            // Send the email notification
            wp_mail($email, $subject, $message, $headers);
        }
    }
}, 10, 2);

add_action('wp', function () {
    if (!wp_next_scheduled('archive_done_tickets')) {
        wp_schedule_event(time(), 'daily', 'archive_done_tickets');
    }
});

add_action('archive_done_tickets', function () {
    $args = array(
        'post_type'      => 'th_ticket',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'status',
                'value'   => 'Done',
                'compare' => '='
            )
        )
    );

    $tickets = get_posts($args);
    $current_time = current_time('timestamp');
    foreach ($tickets as $ticket) {
        $completed_date = strtotime(get_post_meta($ticket->ID, 'completed_date', true));
        $diff = ($current_time - $completed_date) / DAY_IN_SECONDS;
        if ($diff > 0) { // 0 days after being marked as done
            wp_update_post(array(
                'ID'          => $ticket->ID,
                'post_status' => 'archive' // Ensure 'archive' status is registered in your WordPress
            ));
        }
    }
});

add_filter('manage_ticket_posts_columns', function ($columns) {
    unset($columns['title']);  // Remove the title column
    $new_columns = [
        'cb' => $columns['cb'],  // Keep the checkbox for bulk actions
        'id' => 'ID',
        'status' => 'Status',
        'type' => 'Type'
    ];
    return array_merge($new_columns, $columns);
});

add_filter('manage_edit-ticket_sortable_columns', function ($columns) {
    $columns['id'] = 'id';
    $columns['status'] = 'status';
    $columns['type'] = 'type';
    return $columns;
});

add_action('manage_ticket_posts_custom_column', function ($column, $post_id) {
    switch ($column) {
        case 'id':
            $id = get_post_meta($post_id, 'id', true);
            $edit_link = get_edit_post_link($post_id);
            echo '<a href="' . $edit_link . '">' . $id . '</a>';
            break;
        case 'status':
            $status = get_post_meta($post_id, 'status', true);
            echo $status;
            break;
        case 'type':
            $type = get_post_meta($post_id, 'type', true);
            echo $type;
            break;
    }
}, 10, 2);


add_action('save_post_th_ticket', function ($post_id, $post, $update) {
    if ($update) return; // Only set post status on creation

    $options = get_option('th_plus_options');
    $auto_publish = isset($options['auto_publish']) && $options['auto_publish'];

    if ($auto_publish) {
        wp_update_post(array(
            'ID' => $post_id,
            'post_status' => 'publish'
        ));
    }
}, 10, 3);
