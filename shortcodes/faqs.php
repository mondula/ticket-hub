<?php

add_shortcode('faqs', function()
{
    static $faqs_enqueue = false;

    if (!$faqs_enqueue) {
        wp_enqueue_script( 'faqs-script', PLUGIN_ROOT . 'js/faqs.js', array('jquery'), '', true );
        wp_enqueue_style( 'faqs-style', PLUGIN_ROOT . 'css/faqs.css', array(), '', 'all' );
        $faqs_enqueue = true;
    }

    ob_start(); // Start output buffering

    $args = array(
        'post_type' => 'faq', // Hardcoded to 'faq' post type
        'posts_per_page' => -1, // Retrieve all posts
    );

    $the_query = new WP_Query($args);

    if ($the_query->have_posts()) {
        echo '<div class="faq-accordion">';

        while ($the_query->have_posts()) {
            $the_query->the_post();

            echo '<div class="accordion-item">';
                echo '<div class="accordion-title" onclick="toggleAccordion(this)"><h2>' . esc_html(get_the_title()) . '</h2><h3>' . esc_html(get_the_date('F j, Y')) . '</h3><span class="accordion-toggle"></span></div>';
                echo '<div class="accordion-content">';
                // If the log content contains HTML, consider using the_content filter. Otherwise, for plain text, use esc_html().
                echo apply_filters('the_content', get_post_meta(get_the_ID(), '_mts_answer', true));
                echo '</div>';
            echo '</div>';
        }

        echo '</div>';
    } else {
        echo '<p>No changes found.</p>';
    }

    wp_reset_postdata();

    return ob_get_clean(); // Return the buffered output
});
