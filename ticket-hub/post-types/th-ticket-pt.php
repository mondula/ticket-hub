<?php

add_action('init', function () {
    register_th_ticket_post_type();
    register_th_ticket_tag_taxonomy();
});

function register_th_ticket_post_type()
{
    // Get the option value
    $options = get_option('th_plus_options');
    $auto_publish = isset($options['auto_publish']) && $options['auto_publish'] ? true : false;

    register_post_type('th_ticket', array(
        'labels' => array(
            'name' => __('Tickets'),
            'singular_name' => __('Ticket', TEXT_DOMAIN),
            'menu_name' => __('Tickets', TEXT_DOMAIN),
            'all_items' => __('Tickets', TEXT_DOMAIN),
            'edit_item' => __('Edit Ticket', TEXT_DOMAIN),
            'view_item' => __('View Ticket', TEXT_DOMAIN),
            'view_items' => __('View Tickets', TEXT_DOMAIN),
            'add_new_item' => __('Add New Ticket', TEXT_DOMAIN),
            'add_new' => __('Add New Ticket', TEXT_DOMAIN),
            'new_item' => __('New Ticket', TEXT_DOMAIN),
            'parent_item_colon' => __('Parent Ticket:', TEXT_DOMAIN),
            'search_items' => __('Search Tickets', TEXT_DOMAIN),
            'not_found' => __('No tickets found', TEXT_DOMAIN),
            'not_found_in_trash' => __('No tickets found in Trash', TEXT_DOMAIN),
            'archives' => __('Ticket Archives', TEXT_DOMAIN),
            'attributes' => __('Ticket Attributes', TEXT_DOMAIN),
            'insert_into_item' => __('Insert into ticket', TEXT_DOMAIN),
            'uploaded_to_this_item' => __('Uploaded to this ticket', TEXT_DOMAIN),
            'filter_items_list' => __('Filter tickets list', TEXT_DOMAIN),
            'filter_by_date' => __('Filter tickets by date', TEXT_DOMAIN),
            'items_list_navigation' => __('Tickets list navigation', TEXT_DOMAIN),
            'items_list' => __('Tickets list', TEXT_DOMAIN),
            'item_published' => __('Ticket published.', TEXT_DOMAIN),
            'item_published_privately' => __('Ticket published privately.', TEXT_DOMAIN),
            'item_reverted_to_draft' => __('Ticket reverted to draft.', TEXT_DOMAIN),
            'item_scheduled' => __('Ticket scheduled.', TEXT_DOMAIN),
            'item_updated' => __('Ticket updated.', TEXT_DOMAIN),
            'item_link' => __('Ticket Link', TEXT_DOMAIN),
            'item_link_description' => __('A link to a ticket.', TEXT_DOMAIN),
        ),
        'description' => __('These are the tickets created by people.', TEXT_DOMAIN),
        'public' => true,
        'show_in_menu' => 'th_main_menu',
        'menu_position' => 1,
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
    if ($post->post_type != 'th_ticket') return;

    $fields = [
        'th_ticket_id' => [
            'type' => 'text',
            'label' => __('ID', TEXT_DOMAIN),
        ],
        'th_ticket_status' => [
            'type' => 'select',
            'label' => __('Status', TEXT_DOMAIN),
            'options' => [
                'New' => __('New', TEXT_DOMAIN),
                'Processing' => __('Processing', TEXT_DOMAIN),
                'Done' => __('Done', TEXT_DOMAIN),
            ]
        ],
        'th_ticket_type' => [
            'type' => 'select',
            'label' => __('Type', TEXT_DOMAIN),
            'options' => [
                '' => '- Select Type -',
                'Support' => __('Support', TEXT_DOMAIN),
                'Bug report' => __('Bug report', TEXT_DOMAIN),
                'Change request' => __('Change request', TEXT_DOMAIN),
            ]
        ],
        'th_ticket_description' => [
            'type' => 'textarea',
            'label' => __('Description', TEXT_DOMAIN),
        ],
    ];

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

    $saved_custom_fields = get_option('th_custom_fields', []);

    foreach ($saved_custom_fields as $field) {
        $value = get_post_meta($post->ID, 'thcf_' . sanitize_title($field['label']), true);
        echo "<label for='thcf_" . sanitize_title($field['label']) . "'><h3>" . esc_html($field['label']) . "</h3></label>";

        if ($field['type'] === 'select') {
            echo "<select id='thcf_" . sanitize_title($field['label']) . "' name='thcf_" . sanitize_title($field['label']) . "'>";
            foreach ($field['options'] as $option) {
                $selected = ($value == $option) ? 'selected' : '';
                echo "<option value='" . esc_attr($option) . "' {$selected}>" . esc_html($option) . "</option>";
            }
            echo "</select><br/><br/>";
        } elseif ($field['type'] === 'textarea') {
            echo "<textarea id='thcf_" . sanitize_title($field['label']) . "' name='thcf_" . sanitize_title($field['label']) . "' rows='4' cols='50'>" . esc_textarea($value) . "</textarea><br/><br/>";
        } else {
            echo "<input type='text' id='thcf_" . sanitize_title($field['label']) . "' name='thcf_" . sanitize_title($field['label']) . "' value='" . esc_attr($value) . "'/><br/><br/>";
        }
    }

    wp_nonce_field('save_ticket_meta', 'ticket_meta_nonce');
});

add_action('save_post_th_ticket', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['ticket_meta_nonce']) || !wp_verify_nonce($_POST['ticket_meta_nonce'], 'save_ticket_meta')) return;

    $fields = ['th_ticket_id', 'th_ticket_status', 'th_ticket_type', 'th_ticket_description'];

    foreach ($fields as $field) {
        if (array_key_exists($field, $_POST)) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

    $saved_custom_fields = get_option('th_custom_fields', []);

    foreach ($saved_custom_fields as $field) {
        $field_key = 'thcf_' . sanitize_title($field['label']);
        if (isset($_POST[$field_key])) {
            update_post_meta($post_id, $field_key, sanitize_text_field($_POST[$field_key]));
        }
    }
});

add_action('updated_post_meta', function ($meta_id, $post_id, $meta_key, $meta_value) {
    if ($meta_key == 'th_ticket_status') {

        $author_id = get_post_field('post_author', $post_id);
        $email = get_the_author_meta('email', $author_id);
        $id = get_post_meta($post_id, 'th_ticket_id', true);
        $ticket_link = get_permalink($post_id);

        if ($meta_value == 'Done') {
            update_post_meta($post_id, 'completed_date', current_time('mysql'));
        }

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $subject = 'Status Update Notification';
            $message = 'The status of your ticket (ID: <a href="' . $ticket_link . '">' . $id . '</a>) has been updated to: ' . $meta_value . '.';
            $headers = array('Content-Type: text/html; charset=UTF-8');

            wp_mail($email, $subject, $message, $headers);
        }
    }
}, 10, 4);

add_action('transition_post_status', function ($new_status, $old_status, $post) {
    if ($post->post_type != 'th_ticket') {
        return;
    }

    if (($new_status == 'publish' || $new_status == 'th_archive') && $old_status != $new_status) {
        $author_id = $post->post_author;
        $email = get_the_author_meta('email', $author_id);
        $ticket_id = get_post_meta($post->ID, 'th_ticket_id', true);
        $status = ucfirst($new_status);
        $ticket_link = get_permalink($post->ID);

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $subject = "Ticket Status Update: $status";
            $message = "The status of your ticket (ID: <a href='$ticket_link'>$ticket_id</a>) has changed to: $status.";
            $headers = array('Content-Type: text/html; charset=UTF-8');

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

    if ($post->post_type == 'th_ticket') {
        $author_id = $post->post_author;
        $email = get_the_author_meta('email', $author_id);
        $ticket_id = get_post_meta($post_id, 'th_ticket_id', true);
        $ticket_link = get_permalink($post_id);
        $comment_author = $comment->comment_author;
        $comment_content = $comment->comment_content;

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $subject = "New Comment on Ticket ID: $ticket_id";
            $message = "A new comment has been posted by $comment_author on your ticket (ID: <a href='$ticket_link'>$ticket_id</a>):<br/><br/> \"$comment_content\"";
            $headers = array('Content-Type: text/html; charset=UTF-8');

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
                'post_status' => 'th_archive'
            ));
        }
    }
});

add_filter('manage_th_ticket_posts_columns', function ($columns) {
    unset($columns['title']);
    $new_columns = [
        'cb' => $columns['cb'],
        'id' => 'ID',
        'status' => 'Status',
        'type' => 'Type'
    ];
    return array_merge($new_columns, $columns);
});

add_filter('manage_edit-th_ticket_sortable_columns', function ($columns) {
    $columns['id'] = 'id';
    $columns['status'] = 'status';
    $columns['type'] = 'type';
    return $columns;
});

add_action('manage_th_ticket_posts_custom_column', function ($column, $post_id) {
    switch ($column) {
        case 'id':
            $id = get_post_meta($post_id, 'th_ticket_id', true);
            $edit_link = get_edit_post_link($post_id);
            echo '<a href="' . $edit_link . '">' . $id . '</a>';
            break;
        case 'status':
            $status = get_post_meta($post_id, 'th_ticket_status', true);
            echo $status;
            break;
        case 'type':
            $type = get_post_meta($post_id, 'th_ticket_type', true);
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
