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
            'name' => __('Tickets', 'tickethub'),
            'singular_name' => __('Ticket', 'tickethub'),
            'menu_name' => __('Tickets', 'tickethub'),
            'all_items' => __('Tickets', 'tickethub'),
            'edit_item' => __('Edit Ticket', 'tickethub'),
            'view_item' => __('View Ticket', 'tickethub'),
            'view_items' => __('View Tickets', 'tickethub'),
            'add_new_item' => __('Add New Ticket', 'tickethub'),
            'add_new' => __('Add New Ticket', 'tickethub'),
            'new_item' => __('New Ticket', 'tickethub'),
            'parent_item_colon' => __('Parent Ticket:', 'tickethub'),
            'search_items' => __('Search Tickets', 'tickethub'),
            'not_found' => __('No tickets found', 'tickethub'),
            'not_found_in_trash' => __('No tickets found in Trash', 'tickethub'),
            'archives' => __('Ticket Archives', 'tickethub'),
            'attributes' => __('Ticket Attributes', 'tickethub'),
            'insert_into_item' => __('Insert into ticket', 'tickethub'),
            'uploaded_to_this_item' => __('Uploaded to this ticket', 'tickethub'),
            'filter_items_list' => __('Filter tickets list', 'tickethub'),
            'filter_by_date' => __('Filter tickets by date', 'tickethub'),
            'items_list_navigation' => __('Tickets list navigation', 'tickethub'),
            'items_list' => __('Tickets list', 'tickethub'),
            'item_published' => __('Ticket published.', 'tickethub'),
            'item_published_privately' => __('Ticket published privately.', 'tickethub'),
            'item_reverted_to_draft' => __('Ticket reverted to draft.', 'tickethub'),
            'item_scheduled' => __('Ticket scheduled.', 'tickethub'),
            'item_updated' => __('Ticket updated.', 'tickethub'),
            'item_link' => __('Ticket Link', 'tickethub'),
            'item_link_description' => __('A link to a ticket.', 'tickethub'),
        ),
        'description' => __('These are the tickets created by people.', 'tickethub'),
        'public' => true,
        'show_in_menu' => 'th_main_menu',
        'menu_position' => 1,
        'show_in_rest' => true,
        'supports' => array('title', 'author', 'comments'),
        'delete_with_user' => true,
        'taxonomies' => array('th_ticket_tag'), // Enable tag support
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'capabilities' => array(
            'publish_posts' => $auto_publish ? 'publish_th_tickets' : 'draft_th_tickets',
        ),
    ));
}

function register_th_ticket_tag_taxonomy()
{
    register_taxonomy(
        'th_ticket_tag',
        'th_ticket',
        array(
            'labels' => array(
                'name' => __('Ticket Tags', 'tickethub'),
                'singular_name' => __('Ticket Tag', 'tickethub'),
                'menu_name' => __('Ticket Tags', 'tickethub'),
                'all_items' => __('All Ticket Tags', 'tickethub'),
                'edit_item' => __('Edit Ticket Tag', 'tickethub'),
                'view_item' => __('View Ticket Tag', 'tickethub'),
                'update_item' => __('Update Ticket Tag', 'tickethub'),
                'add_new_item' => __('Add New Ticket Tag', 'tickethub'),
                'new_item_name' => __('New Ticket Tag Name', 'tickethub'),
                'search_items' => __('Search Ticket Tags', 'tickethub'),
                'popular_items' => __('Popular Ticket Tags', 'tickethub'),
                'separate_items_with_commas' => __('Separate ticket tags with commas', 'tickethub'),
                'add_or_remove_items' => __('Add or remove ticket tags', 'tickethub'),
                'choose_from_most_used' => __('Choose from the most used ticket tags', 'tickethub'),
                'not_found' => __('No ticket tags found', 'tickethub'),
            ),
            'public' => true,
            'show_in_rest' => true
        )
    );
}


add_action('edit_form_after_title', function ($post) {
    if ($post->post_type != 'th_ticket') return;

    $fields = [
        'th_ticket_id' => [
            'type' => 'text',
            'label' => __('ID', 'tickethub'),
        ],
        'th_ticket_status' => [
            'type' => 'select',
            'label' => __('Status', 'tickethub'),
            'options' => [
                'New' => __('New', 'tickethub'),
                'Processing' => __('Processing', 'tickethub'),
                'Done' => __('Done', 'tickethub'),
            ]
        ],
        'th_ticket_type' => [
            'type' => 'select',
            'label' => __('Type', 'tickethub'),
            'options' => [
                '' => __('- Select Type -', 'tickethub'),
                'Support' => __('Support', 'tickethub'),
                'Bug report' => __('Bug report', 'tickethub'),
                'Change request' => __('Change request', 'tickethub'),
            ]
        ],
        'th_ticket_description' => [
            'type' => 'textarea',
            'label' => __('Description', 'tickethub'),
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
        echo '<label><h3>' . __('Attachments:', 'tickethub') . '</h3></label>';
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
            $subject = __('Status Update Notification', 'tickethub');
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
