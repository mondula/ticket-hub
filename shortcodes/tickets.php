<?php

add_shortcode('tickets', function($atts) {

    static $tickets_enqueue = false;

    if (!$tickets_enqueue) {
        wp_enqueue_script('tickets-script', PLUGIN_ROOT . 'js/tickets.js', array('jquery'), '', true);
        wp_enqueue_style('tickets-style', PLUGIN_ROOT . 'css/tickets.css', array(), '', 'all');
        $tickets_enqueue = true;
    }

    ob_start();

    $attributes = shortcode_atts(array(
        'user_id' => ''
    ), $atts);

    $args = array(
        'post_type'      => 'ticket',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    if (!empty($attributes['user_id'])) {
        $args['author'] = $attributes['user_id'];
    }

    // Status and Type choices remain unchanged
    $status_choices = array(
        'New' => 'New',
        'Processing' => 'Processing',
        'Done' => 'Done'
    );
    $type_choices = array(
        'Support' => 'Support',
        'Bug report' => 'Bug report',
        'Change request' => 'Change request'
    );

    // Ticket controls remain unchanged
    echo '<div class="ticket-controls">';
    echo '<input type="text" id="search" placeholder="Search">';
    echo '<div>';
    echo '<select id="ticket_status" class="select1"><option value="">Status</option>';
    foreach ($status_choices as $value => $label) {
        echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '<select id="ticket_type" class="select1"><option value="">Type</option>';
    foreach ($type_choices as $value => $label) {
        echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';

    $the_query = new WP_Query($args);

    // Modified table headers to include "Date" and "Created by"
    echo '<table class="ticket-table"><thead><tr><th>ID</th><th>Status</th><th>Type</th><th>Date</th>';
    if (empty($attributes['user_id'])) {
        echo '<th>Created by</th>';
    }
    echo '</tr></thead><tbody>';
    while ($the_query->have_posts()) {
        $the_query->the_post();

        $post_id = get_the_ID();
        $ticket_id = esc_html(get_post_meta($post_id, 'id', true));
        $ticket_status = esc_html(get_post_meta($post_id, 'status', true));
        $ticket_type = esc_html(get_post_meta($post_id, 'type', true));
        $ticket_link = get_permalink();
        $ticket_date = get_the_date(); // Fetch the publish date
        $author_id = get_the_author_meta('ID'); // Get the author's user ID
        $first_name = get_the_author_meta('first_name', $author_id); // Get the author's first name
        $last_name = get_the_author_meta('last_name', $author_id); // Get the author's last name
        $ticket_author = $first_name . ' ' . $last_name; // Concatenate first name and last name
        
        if (empty($first_name) && empty($last_name)) {
            $ticket_author = get_the_author_meta('display_name', $author_id); // Get the author's display name
        }


        echo "<tr>";
        echo "<td><a href='$ticket_link'>$ticket_id</a></td>";
        echo "<td><span class='status-chip' data-status='$ticket_status'>$ticket_status</span></td>";
        echo "<td>$ticket_type</td>";
        echo "<td class='comment-date'>$ticket_date</td>";
        if (empty($attributes['user_id'])) {
            echo "<td>$ticket_author</td>";
        }
        echo "</tr>";
    }
    echo '</tbody></table>';

    wp_reset_postdata();

    return ob_get_clean();
});
