<?php

add_shortcode('th_tickets', function ($atts) {

    // NOTE: This shortcode should only be unsed once per site!!!

    static $tickets_enqueue = false;

    $attributes = shortcode_atts(array(
        'user_id' => ''
    ), $atts);

    if (!$tickets_enqueue) {
        wp_enqueue_script('th-tickets-script', PLUGIN_ROOT . 'js/th-tickets.js', array('jquery'), '', true);
        wp_localize_script('tickets-script', 'ajax_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'user_id' => $attributes['user_id']
        ));
        wp_enqueue_style('th-tickets-style', PLUGIN_ROOT . 'css/th-tickets.css', array(), '', 'all');
        $tickets_enqueue = true;
    }

    ob_start();

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
    echo '<div class="th-ticket-controls">';
    echo '<input type="text" id="search" placeholder="Search">';
    echo '<div class="tickets-filter-container">';
    echo '<label for="toggleArchived" class="switch-container">';  // Start label here
    echo 'Archive';  // Label text
    echo '<div class="switch">';
    echo '<input type="checkbox" id="toggleArchived">';
    echo '<span class="slider round"></span>';
    echo '</div>';
    echo '</label>';  // Close label here
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

    // Modified table headers to include "Date" and "Created by"
    echo '<table class="ticket-table"><thead><tr><th>ID</th><th>Status</th><th>Type</th><th>Date</th>';
    if (empty($attributes['user_id'])) {
        echo '<th>Created by</th>';
    }
    echo '</tr></thead><tbody id="tickets-container">';
    echo '</tbody></table>';
    echo '<div id="ticket-pagination"></div>';  // Pagination container

    return ob_get_clean();
});

function fetch_tickets_ajax()
{
    $archive = $_POST['archive'] === 'true';
    $search = sanitize_text_field($_POST['search']);
    $status = sanitize_text_field($_POST['status']);
    $type = sanitize_text_field($_POST['type']);
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $user_id = intval($_POST['user_id']);

    $args = array(
        'post_type'      => 'ticket',
        'posts_per_page' => 10,
        'paged'          => $page,
        'post_status'    => $archive ? 'archive' : 'publish',
        'meta_query'     => array(
            'relation' => 'AND',
        )
    );

    if (!empty($search)) {
        $args['meta_query'][] = array(
            'key'     => 'id', // Assuming 'ticket_id' is the meta key where IDs are stored
            'value'   => $search,
            'compare' => 'LIKE'
        );
    }
    if (!empty($status)) {
        $args['meta_query'][] = array(
            'key'     => 'status',
            'value'   => $status,
            'compare' => '='
        );
    }
    if (!empty($user_id)) {
        $args['author'] = $user_id;
    }
    if (!empty($type)) {
        $args['meta_query'][] = array(
            'key'     => 'type',
            'value'   => $type,
            'compare' => '='
        );
    }

    $the_query = new WP_Query($args);
    $output = '';

    while ($the_query->have_posts()) {
        $the_query->the_post();
        $post_id = get_the_ID();
        $ticket_id = esc_html(get_post_meta($post_id, 'id', true));
        $ticket_status = esc_html(get_post_meta($post_id, 'status', true));
        $ticket_type = esc_html(get_post_meta($post_id, 'type', true));
        $ticket_link = get_permalink();
        $ticket_date = get_the_date();
        $author_id = get_the_author_meta('ID');
        $first_name = get_the_author_meta('first_name', $author_id);
        $last_name = get_the_author_meta('last_name', $author_id);
        $ticket_author = $first_name . ' ' . $last_name;

        if (empty($first_name) && empty($last_name)) {
            $ticket_author = get_the_author_meta('display_name', $author_id);
        }

        $output .= "<tr>";
        $output .= "<td><span class='mobile-table-header'>ID</span><a href='$ticket_link'>$ticket_id</a></td>";
        $output .= "<td><span class='mobile-table-header'>Status</span><span class='status-chip' data-status='$ticket_status'>$ticket_status</span></td>";
        $output .= "<td><span class='mobile-table-header'>Type</span>$ticket_type</td>";
        $output .= "<td class='comment-date'><span class='mobile-table-header'>Date</span>$ticket_date</td>";
        if (empty($user_id)) {
            $output .= "<td><span class='mobile-table-header'>Created by</span>$ticket_author</td>";
        }
        $output .= "</tr>";
    }

    wp_reset_postdata();

    $pagination = paginate_links(array(
        'base'      => admin_url('admin-ajax.php') . '?page=%#%',
        'format'    => '%#%',
        'total'     => $the_query->max_num_pages,
        'current'   => $page,
        'mid_size'  => 2,
        'prev_next' => true,
        'prev_text' => '', // Ensures the previous page is accessible
        'next_text' => '', // Special span with class for styling
        'end_size'  => 1, // Number of pages at the beginning and the end
        'type'      => 'array'
    ));

    // Add 'first' and 'last' links conditionally
    // $end_size = 1; // Typically keep this at 1
    // $dot_gap = $end_size + $mid_size + 1; // +1 accounts for the current page

    // if (!empty($pagination)) {
    //     // Conditionally add first page link
    //     if ($page > $dot_gap) { // Current page is farther than the first few pages
    //         array_unshift($pagination, '<a class="page-numbers first" href="' . admin_url('admin-ajax.php') . '?page=1"></a>');
    //     }
    //     // Conditionally add last page link
    //     if ($page < $the_query->max_num_pages - $dot_gap) { // Current page is farther than the last few pages
    //         $pagination[] = '<a class="page-numbers" href="' . admin_url('admin-ajax.php') . '?page=' . $the_query->max_num_pages . '">Last</a>';
    //     }
    // }

    // Convert array to string if needed for output
    $pagination_html = '';
    if (is_array($pagination)) {
        $pagination_html = implode(' ', $pagination);
        $pagination_html = "<div class='pagination-wrap'>";
        foreach ($pagination as $page) {
            if (strpos($page, 'current') !== false) {
                $pagination_html .= "<button class='page-number active'>" . strip_tags($page, '<a>') . "</button>";
            } else {
                $pagination_html .= "<button class='page-number'>" . strip_tags($page, '<a>') . "</button>";
            }
        }
        $pagination_html .= "</div>";
    }

    // Properly encode the entire output as JSON
    $final_output = json_encode(array('tickets' => $output, 'pagination' => $pagination_html));

    // Ensure that headers are set to return JSON content
    header('Content-Type: application/json');
    echo $final_output;
    die();
}
add_action('wp_ajax_fetch_tickets', 'fetch_tickets_ajax');
add_action('wp_ajax_nopriv_fetch_tickets', 'fetch_tickets_ajax');
