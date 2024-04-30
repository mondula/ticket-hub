<?php

add_shortcode('changelog', function() {
    static $changelog_enqueue = false;

    if (!$changelog_enqueue) {
        wp_enqueue_script('changelog-script', PLUGIN_ROOT . 'js/changelog.js', array('jquery'), '', true);
        wp_enqueue_style('changelog-style', PLUGIN_ROOT . 'css/changelog.css', array(), '', 'all');
        $changelog_enqueue = true;
    }

    ob_start();

    $args = array(
        'post_type' => 'change',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    );

    $the_query = new WP_Query($args);

    if ($the_query->have_posts()) {
        echo '<div class="changelog-accordion">';

        while ($the_query->have_posts()) {
            $the_query->the_post();

            echo '<div class="accordion-item">';
                echo '<div class="accordion-title" onclick="toggleAccordion(this)"><h2>' . esc_html(get_the_title()) . '</h2><h3>' . esc_html(get_the_date('F j, Y')) . '</h3><span class="accordion-toggle"></span></div>';
                echo '<div class="accordion-content">';
                // If the log content contains HTML, consider using the_content filter. Otherwise, for plain text, use esc_html().
                echo apply_filters('the_content', get_post_meta(get_the_ID(), '_mts_log', true));
                echo '</div>';
            echo '</div>';
        }

        echo '</div>';
    } else {
        echo '<p>No changes found.</p>';
    }

    wp_reset_postdata();

    return ob_get_clean();
});
