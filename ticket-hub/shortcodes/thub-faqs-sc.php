<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode('thub_faqs', function () {

    ob_start(); // Start output buffering

    $args = array(
        'post_type' => 'thub_faq', // Hardcoded to 'thub_faq' post type
        'posts_per_page' => -1, // Retrieve all posts
    );

    $the_query = new WP_Query($args);

    if ($the_query->have_posts()) {
        echo '<div class="thub-accordion">';

        while ($the_query->have_posts()) {
            $the_query->the_post();

            echo '<div class="thub-accordion-item">';
            echo '<div class="thub-accordion-title">';
            echo '<div><h2>' . esc_html(get_the_title()) . '</h2><h3>' . esc_html(get_the_date('F j, Y')) . '</h3></div>';
            echo '<span class="thub-accordion-toggle"></span></div>';
            echo '<div class="thub-accordion-content">';
            echo wp_kses_post(apply_filters('the_content', get_post_meta(get_the_ID(), '_thub_answer', true)));
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
    } else {
        echo '<p>' . esc_html__('No FAQs found.', 'ticket-hub') . '</p>';
    }

    wp_reset_postdata();

    return ob_get_clean(); // Return the buffered output
});
