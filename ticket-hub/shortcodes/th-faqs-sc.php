<?php

add_shortcode('th_faqs', function () {
    global $th_accordion_enqueue;

    if (!$th_accordion_enqueue) {
        wp_enqueue_script('th-accordion-script', PLUGIN_ROOT . 'js/th-accordion.js', array('jquery'), '', true);
        wp_enqueue_style('th-accordion-style', PLUGIN_ROOT . 'css/th-accordion.css', array(), '', 'all');
        $th_accordion_enqueue = true;
    }

    ob_start(); // Start output buffering

    $args = array(
        'post_type' => 'th_faq', // Hardcoded to 'th_faq' post type
        'posts_per_page' => -1, // Retrieve all posts
    );

    $the_query = new WP_Query($args);

    if ($the_query->have_posts()) {
        echo '<div class="th-accordion">';

        while ($the_query->have_posts()) {
            $the_query->the_post();

            echo '<div class="th-accordion-item">';
            echo '<div class="th-accordion-title" onclick="toggleAccordion(this)"><h2>' . esc_html(get_the_title()) . '</h2><h3>' . esc_html(get_the_date('F j, Y')) . '</h3><span class="accordion-toggle"></span></div>';
            echo '<div class="th-accordion-content">';
            // If the log content contains HTML, consider using the_content filter. Otherwise, for plain text, use esc_html().
            echo apply_filters('the_content', get_post_meta(get_the_ID(), '_th_answer', true));
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
    } else {
        echo '<p>No faqs found.</p>';
    }

    wp_reset_postdata();

    return ob_get_clean(); // Return the buffered output
});
