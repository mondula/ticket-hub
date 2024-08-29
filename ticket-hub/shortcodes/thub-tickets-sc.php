<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode('thub_tickets', function ($atts) {
    $options = get_option('thub_plus_options');
    $allow_export = isset($options['allow_export']) && $options['allow_export'] == 1;

    static $tickets_enqueue = false;

    $attributes = shortcode_atts(array(
        'user_id' => ''
    ), $atts);

    // Sanitize the user_id attribute
    $attributes['user_id'] = sanitize_text_field($attributes['user_id']);

    if (!$tickets_enqueue) {
        wp_enqueue_script('thub-tickets-script', PLUGIN_ROOT . 'js/thub-tickets.js', array('jquery'), '1.0.0', true);
        wp_localize_script('thub-tickets-script', 'ajax_params', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'user_id' => $attributes['user_id'],
            'nonce' => wp_create_nonce('fetch_tickets_nonce')
        ));
        wp_enqueue_style('thub-tickets-style', PLUGIN_ROOT . 'css/thub-tickets.css', array(), '1.0.0', 'all');
        $tickets_enqueue = true;
    }

    ob_start();

    $status_choices = array(
        'New' => __('New', 'tickethub'),
        'Processing' => __('Processing', 'tickethub'),
        'Done' => __('Done', 'tickethub')
    );
    $type_choices = array(
        'Support' => __('Support', 'tickethub'),
        'Bug report' => __('Bug report', 'tickethub'),
        'Change request' => __('Change request', 'tickethub')
    );

    echo '<div class="thub-ticket-controls">';
    echo '<input type="text" id="thub-ticket-search" placeholder="' . esc_attr__('Search', 'tickethub') . '">';
    echo '<div class="thub-tickets-filter-container">';
    echo '<label for="thub-toggle-archive" class="thub-switch-container">' . esc_html__('Archive', 'tickethub');
    echo '<div class="thub-switch">';
    echo '<input type="checkbox" id="thub-toggle-archive">';
    echo '<span class="thub-slider thub-round"></span>';
    echo '</div>';
    echo '</label>';
    echo '<select id="thub-ticket-status" class="thub-select"><option value="">' . esc_html__('- Status -', 'tickethub') . '</option>';
    foreach ($status_choices as $value => $label) {
        echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '<select id="thub-ticket-type" class="thub-select"><option value="">' . esc_html__('- Type -', 'tickethub') . '</option>';
    foreach ($type_choices as $value => $label) {
        echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '</div>';
    if ($allow_export) {
        echo '<button id="thub-export-tickets">' . esc_html__('Export Tickets', 'tickethub') . '</button>';
    }
    echo '</div>';

    echo '<table class="thub-ticket-table"><thead><tr><th>' . esc_html__('ID', 'tickethub') . '</th><th>' . esc_html__('Status', 'tickethub') . '</th><th>' . esc_html__('Type', 'tickethub') . '</th><th>' . esc_html__('Date', 'tickethub') . '</th>';
    if (empty($attributes['user_id'])) {
        echo '<th>' . esc_html__('Creator', 'tickethub') . '</th>';
    }
    echo '</tr></thead><tbody id="thub-tickets-container">';
    echo '</tbody></table>';
    echo '<div id="thub-ticket-pagination"></div>';

    return ob_get_clean();
});

function thub_fetch_tickets_ajax()
{
    check_ajax_referer('fetch_tickets_nonce', 'nonce');

    $is_archive = $_POST['isArchive'] === 'true';
    $search_value = sanitize_text_field($_POST['searchValue']);
    $status_value = sanitize_text_field($_POST['statusValue']);
    $type_value = sanitize_text_field($_POST['typeValue']);
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $user_id = intval($_POST['user_id']);

    $args = array(
        'post_type'      => 'thub_ticket',
        'posts_per_page' => 10,
        'paged'          => $page,
        'post_status'    => $is_archive ? 'thub_archive' : 'publish',
        //TODO: Plugin-Check beschwert sich: "Detected usage of meta_query, possible slow query." -> Entweder fixen oder Kommentar löschen und ignorieren.
        'meta_query'     => array(
            'relation' => 'AND',
        )
    );

    if (!empty($search_value)) {
        $args['meta_query'][] = array(
            'key'     => 'thub_ticket_id',
            'value'   => $search_value,
            'compare' => 'LIKE'
        );
    }
    if (!empty($status_value)) {
        $args['meta_query'][] = array(
            'key'     => 'thub_ticket_status',
            'value'   => $status_value,
            'compare' => '='
        );
    }
    if (!empty($user_id)) {
        $args['author'] = $user_id;
    }
    if (!empty($type_value)) {
        $args['meta_query'][] = array(
            'key'     => 'thub_ticket_type',
            'value'   => $type_value,
            'compare' => '='
        );
    }

    $the_query = new WP_Query($args);
    $output = '';

    while ($the_query->have_posts()) {
        $the_query->the_post();
        $post_id = get_the_ID();
        $ticket_id = esc_html(get_post_meta($post_id, 'thub_ticket_id', true));
        $ticket_status = esc_html(get_post_meta($post_id, 'thub_ticket_status', true));
        $ticket_type = esc_html(get_post_meta($post_id, 'thub_ticket_type', true));
        $ticket_link = esc_url(get_permalink());
        $ticket_date = esc_html(get_the_date());
        $author_id = get_the_author_meta('ID');
        $first_name = esc_html(get_the_author_meta('first_name', $author_id));
        $last_name = esc_html(get_the_author_meta('last_name', $author_id));
        $ticket_author = $first_name . ' ' . $last_name;

        if (empty($first_name) && empty($last_name)) {
            $ticket_author = esc_html(get_the_author_meta('display_name', $author_id));
        }

        $output .= "<tr>";
        $output .= "<td><span class='thub-mobile-table-header'>" . esc_html__('ID', 'tickethub') . "</span><a href='$ticket_link'>$ticket_id</a></td>";
        $output .= "<td><span class='thub-mobile-table-header'>" . esc_html__('Status', 'tickethub') . "</span><span class='thub-status-chip' data-status='$ticket_status'>$ticket_status</span></td>";
        $output .= "<td><span class='thub-mobile-table-header'>" . esc_html__('Type', 'tickethub') . "</span>$ticket_type</td>";
        $output .= "<td class='thub-comment-date'><span class='thub-mobile-table-header'>" . esc_html__('Date', 'tickethub') . "</span>$ticket_date</td>";
        if (empty($user_id)) {
            $output .= "<td><span class='thub-mobile-table-header'>" . esc_html__('Created by', 'tickethub') . "</span>$ticket_author</td>";
        }
        $output .= "</tr>";
    }

    wp_reset_postdata();

    $pagination = paginate_links(array(
        'base'      => esc_url(admin_url('admin-ajax.php')) . '?page=%#%',
        'format'    => '%#%',
        'total'     => $the_query->max_num_pages,
        'current'   => $page,
        'mid_size'  => 2,
        'prev_next' => true,
        'prev_text' => '',
        'next_text' => '',
        'end_size'  => 1,
        'type'      => 'array'
    ));

    $pagination_html = '';
    if (is_array($pagination)) {
        $pagination_html = "<div class='thub-pagination-wrap'>";
        foreach ($pagination as $page) {
            if (strpos($page, 'current') !== false) {
                $pagination_html .= "<button class='thub-page-number active'>" . strip_tags($page, '<a>') . "</button>";
            } else {
                $pagination_html .= "<button class='thub-page-number'>" . strip_tags($page, '<a>') . "</button>";
            }
        }
        $pagination_html .= "</div>";
    }

    $final_output = wp_json_encode(array('tickets' => $output, 'pagination' => $pagination_html));

    header('Content-Type: application/json');
    //TODO: Kann das noch weiter escaped werden? Ist ja schon wp_json_encode(), also sollte sicher sein?
    echo $final_output;
    die();
}
add_action('wp_ajax_fetch_tickets', 'thub_fetch_tickets_ajax');
add_action('wp_ajax_nopriv_fetch_tickets', 'thub_fetch_tickets_ajax');
?>
