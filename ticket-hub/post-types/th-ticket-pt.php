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
            'edit_post' => 'edit_th_ticket',
            'read_post' => 'read_th_ticket',
            'delete_post' => 'delete_th_ticket',
            'edit_posts' => 'edit_th_tickets',
            'edit_others_posts' => 'edit_others_th_tickets',
            'publish_posts' => 'publish_th_tickets',
            'read_private_posts' => 'read_private_th_tickets',
        ),
        'has_archive' => true,
    ));
}

add_action('admin_init', function () {
    $role = get_role('administrator');
    $role->add_cap('edit_th_ticket');
    $role->add_cap('read_th_ticket');
    $role->add_cap('delete_th_ticket');
    $role->add_cap('edit_th_tickets');
    $role->add_cap('edit_others_th_tickets');
    $role->add_cap('publish_th_tickets');
    $role->add_cap('read_private_th_tickets');
});

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
        echo "<label for='{$key}'><h3>" . esc_html($field['label']) . "</h3></label>";

        if ($field['type'] === 'select') {
            echo "<select id='{$key}' name='{$key}'>";
            foreach ($field['options'] as $optionKey => $optionValue) {
                $selected = ($value == $optionKey) ? 'selected' : '';
                echo "<option value='" . esc_attr($optionKey) . "' {$selected}>" . esc_html($optionValue) . "</option>";
            }
            echo "</select><br/><br/>";
        } elseif ($field['type'] === 'textarea') {
            echo "<textarea id='{$key}' name='{$key}' rows='4' cols='50'>" . esc_textarea($value) . "</textarea><br/><br/>";
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
        echo '<label><h3>' . esc_html__('Attachments:', 'tickethub') . '</h3></label>';
        echo '<ul>';
        foreach ($attachments as $attachment) {
            $attachment_url = wp_get_attachment_url($attachment->ID);
            echo '<li><a href="' . esc_url($attachment_url) . '" target="_blank">' . esc_html(basename($attachment_url)) . '</a></li>';
        }
        echo '</ul><br>';
    }

    $saved_custom_fields = get_option('th_custom_fields', []);

    foreach ($saved_custom_fields as $field) {
        $value = get_post_meta($post->ID, 'thcf_' . sanitize_title($field['label']), true);
        echo "<label for='thcf_" . esc_attr(sanitize_title($field['label'])) . "'><h3>" . esc_html($field['label']) . "</h3></label>";

        if ($field['type'] === 'select') {
            echo "<select id='thcf_" . esc_attr(sanitize_title($field['label'])) . "' name='thcf_" . esc_attr(sanitize_title($field['label'])) . "'>";
            foreach ($field['options'] as $option) {
                $selected = ($value == $option) ? 'selected' : '';
                echo "<option value='" . esc_attr($option) . "' {$selected}>" . esc_html($option) . "</option>";
            }
            echo "</select><br/><br/>";
        } elseif ($field['type'] === 'textarea') {
            echo "<textarea id='thcf_" . esc_attr(sanitize_title($field['label'])) . "' name='thcf_" . esc_attr(sanitize_title($field['label'])) . "' rows='4' cols='50'>" . esc_textarea($value) . "</textarea><br/><br/>";
        } else {
            echo "<input type='text' id='thcf_" . esc_attr(sanitize_title($field['label'])) . "' name='thcf_" . esc_attr(sanitize_title($field['label'])) . "' value='" . esc_attr($value) . "'/><br/><br/>";
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
            $message = 'The status of your ticket (ID: <a href="' . esc_url($ticket_link) . '">' . esc_html($id) . '</a>) has been updated to: ' . esc_html($meta_value) . '.';
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
            $subject = esc_html__('Ticket Status Update: ', 'tickethub') . esc_html($status);
            $message = esc_html__('The status of your ticket (ID: ', 'tickethub') . '<a href="' . esc_url($ticket_link) . '">' . esc_html($ticket_id) . '</a>) ' . esc_html__('has changed to: ', 'tickethub') . esc_html($status) . '.';
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
        $comment_author = esc_html($comment->comment_author);
        $comment_content = esc_html($comment->comment_content);

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $subject = esc_html__('New Comment on Ticket ID: ', 'tickethub') . esc_html($ticket_id);
            $message = esc_html__('A new comment has been posted by ', 'tickethub') . esc_html($comment_author) . esc_html__(' on your ticket (ID: ', 'tickethub') . '<a href="' . esc_url($ticket_link) . '">' . esc_html($ticket_id) . '</a>):<br/><br/> "' . esc_html($comment_content) . '"';
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
    $options = get_option('th_options');
    $archive_days = isset($options['archive_days']) ? intval($options['archive_days']) : 0; // Default to 0 days if not set

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
        if ($diff > $archive_days) {
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
            $id = esc_html(get_post_meta($post_id, 'th_ticket_id', true));
            $edit_link = get_edit_post_link($post_id);
            echo '<a href="' . esc_url($edit_link) . '">' . esc_html($id) . '</a>';
            break;
        case 'status':
            $status = esc_html(get_post_meta($post_id, 'th_ticket_status', true));
            echo esc_html($status);
            break;
        case 'type':
            $type = esc_html(get_post_meta($post_id, 'th_ticket_type', true));
            echo esc_html($type);
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

add_filter('bulk_actions-edit-th_ticket', function ($bulk_actions) {
    $bulk_actions['mark_as_archived'] = 'Mark as Archived';
    return $bulk_actions;
});

add_filter('handle_bulk_actions-edit-th_ticket', function ($redirect_to, $doaction, $post_ids) {
    if ($doaction === 'mark_as_archived') {
        foreach ($post_ids as $post_id) {
            // Update the post status to 'th_archive'
            $post = array(
                'ID' => $post_id,
                'post_status' => 'th_archive',
            );
            wp_update_post($post);
        }
        $redirect_to = add_query_arg('bulk_archived_posts', count($post_ids), $redirect_to);
    }
    return $redirect_to;
}, 10, 3);

add_action('admin_notices', function () {
    if (!empty($_REQUEST['bulk_archived_posts'])) {
        $archived_count = intval($_REQUEST['bulk_archived_posts']);
        printf('<div id="message" class="updated fade"><p>' .
            esc_html(_n('Archived %s post.', 'Archived %s posts.', $archived_count, 'tickethub')) .
            '</p></div>', esc_html($archived_count));
    }
});

// Delete attachments when a ticket is deleted
add_action('before_delete_post', function ($post_id) {
    $post_type = get_post_type($post_id);
    if ($post_type !== 'th_ticket') {
        return;
    }

    $attachments = get_posts([
        'post_type' => 'attachment',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'post_parent' => $post_id,
    ]);

    foreach ($attachments as $attachment) {
        wp_delete_attachment($attachment->ID, true);
    }
});

add_action('pre_get_posts', 'th_search_by_ticket_id');
function th_search_by_ticket_id($query)
{
    // Check if this is a search query in the admin area and for our custom post type
    if ($query->is_search() && $query->is_main_query() && is_admin() && $query->get('post_type') == 'th_ticket') {
        $search_term = $query->get('s');
        if (!empty($search_term)) {
            // Remove default search parameter
            $query->set('s', '');

            // Add meta query to search by ticket ID
            $meta_query = [
                'relation' => 'OR',
                [
                    'key'     => 'th_ticket_id',
                    'value'   => sanitize_text_field($search_term),
                    'compare' => 'LIKE',
                ]
            ];

            $query->set('meta_query', $meta_query);
        }
    }
}
