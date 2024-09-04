<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode('thub_ticket', function ($atts) {
    static $ticket_enqueue = false;

    if (!$ticket_enqueue) {
        wp_enqueue_script('thub-lightbox-script', PLUGIN_ROOT . 'js/thub-lightbox.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('thub-ticket-style', PLUGIN_ROOT . 'css/thub-ticket.css', array(), '1.0.0', 'all');
        $ticket_enqueue = true;
    }

    $atts = shortcode_atts(array('id' => ''), $atts);
    $post_id = intval($atts['id']);

    // Get the options
    $options = get_option('thub_options');
    $tickets_page_id = isset($options['thub_tickets']) ? intval($options['thub_tickets']) : 0;
    $tickets_page_url = $tickets_page_id ? get_permalink($tickets_page_id) : home_url('/');

    ob_start();

    if ($post_id && get_post($post_id)) {
        if (get_post_status($post_id) != 'private') {
            $author_id = get_post_field('post_author', $post_id);
            $email = get_the_author_meta('email', $author_id);
            $first_name = get_the_author_meta('first_name', $author_id);
            $last_name = get_the_author_meta('last_name', $author_id);
            $ticket_author = $first_name . ' ' . $last_name;
            if (empty($first_name) && empty($last_name)) {
                $ticket_author = get_the_author_meta('display_name', $author_id);
            }
            $current_tags = wp_get_post_terms($post_id, 'thub_ticket_tag', array("fields" => "slugs"));

            $related_args = array(
                'post_type' => 'thub_ticket',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                // herausgenommen, damit cache reused werden kann, auslassen dann in Schleife
                //'post__not_in' => array($post_id),
                //TODO: Plugin-Check beschwert sich: "Detected usage of meta_query, possible slow query." -> Entweder fixen oder Kommentar lschen und ignorieren.
                'tax_query' => array(
                    array(
                        'taxonomy' => 'thub_ticket_tag',
                        'field' => 'slug',
                        'terms' => $current_tags
                    )
                )
            );
            $related_tickets = new WP_Query($related_args);
?>
            <div class="thub-ticket-details">
                <a href="<?php echo esc_url($tickets_page_url); ?>" class="thub-back-to-archive"><?php esc_attr_e('Back', 'ticket-hub') ?></a>
                <?php
                $ticket_id = get_post_meta($post_id, 'thub_ticket_id', true);
                if (!empty($ticket_id)) {
                    echo '<h3>' . esc_html($ticket_id) . '</h3>';
                }

                if ($related_tickets->have_posts()) {
                    echo '<div class="thub-related-tickets"><span>' . esc_html__('Related Tickets', 'ticket-hub') . '</span>';
                    while ($related_tickets->have_posts()) {
                        $related_tickets->the_post();
                        // gleichen Post ausschlieen
                        if (get_the_ID() != $post_id) {
                            echo '<div><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></div>';
                        }
                    }
                    echo '</div>';
                }

                echo '<div class="thub-ticket-field"><h4>' . esc_html__('Description', 'ticket-hub') . '</h4><p>' . esc_html(get_post_meta($post_id, 'thub_ticket_description', true)) . '</p></div>';

                $fields = [
                    'thub_ticket_status' => __('Status', 'ticket-hub'),
                    'thub_ticket_type' => __('Type', 'ticket-hub'),
                ];

                echo '<div class="thub-ticket-info">';
                $index = 0;
                foreach ($fields as $field => $label) {
                    $value = get_post_meta($post_id, $field, true);
                    echo '<div class="thub-ticket-field"><h4>' . esc_html($label) . '</h4><p>' . esc_html($value) . '<p></div>';
                    if ($index == 1) {
                        echo '<div class="thub-ticket-field"><h4>' . esc_html__('Creator', 'ticket-hub') . '</h4><p>' . esc_html($ticket_author) . '</p></div>';
                        echo '<div class="thub-ticket-field"><h4>' . esc_html__('E-Mail', 'ticket-hub') . '</h4><p>' . esc_html($email) . '</p></div>';
                    }
                    $index++;
                }
                echo '</div>';

                $custom_fields = get_option('thub_custom_fields', []);
                $custom_field_values = array_filter($custom_fields, function ($field) use ($post_id) {
                    return get_post_meta($post_id, 'thcf_' . sanitize_title($field['label']), true);
                });

                if (!empty($custom_field_values)) {
                    foreach ($custom_field_values as $field) {
                        $field_value = get_post_meta($post_id, 'thcf_' . sanitize_title($field['label']), true);
                        echo '<div class="thub-ticket-field">';
                        echo '<h4>' . esc_html($field['label']);
                        echo '</h4>';
                        if ($field['type'] === 'text' || $field['type'] === 'textarea') {
                            echo '<p>' . esc_html($field_value) . '</p>';
                        } elseif ($field['type'] === 'select') {
                            echo '<p>' . esc_html($field_value) . '</p>';
                        }
                        echo '</div>';
                    }
                }

                $attachments = get_posts(array(
                    'post_type' => 'attachment',
                    'posts_per_page' => -1,
                    'post_parent' => $post_id,
                ));

                $image_attachments = [];
                $other_attachments = [];

                foreach ($attachments as $attachment) {
                    $file_path = get_attached_file($attachment->ID);
                    $file_type = wp_check_filetype($file_path);

                    if (strpos($file_type['type'], 'image') !== false) {
                        $image_attachments[] = $attachment;
                    } else {
                        $other_attachments[] = $attachment;
                    }
                }

                if (!empty($image_attachments) || !empty($other_attachments)) {
                    echo '<div class="thub-ticket-field"><h4>' . esc_html__('Attachments', 'ticket-hub') . '</h4>';
                    echo '<div class="thub-ticket-attachments">';
                    foreach ($image_attachments as $attachment) {
                        $image_url = wp_get_attachment_url($attachment->ID);
                        if ($image_url) {
                            echo '<a href="' . esc_url($image_url) . '" class="thub-lightbox-trigger"><div class="thub-image-container"><img src="' . esc_url($image_url) . '" alt="' . esc_attr($attachment->post_title) . '" class="thub-ticket-image"></div></a>';
                        }
                    }
                    echo '</div>';
                    foreach ($other_attachments as $attachment) {
                        echo '<div><a href="' . esc_url(wp_get_attachment_url($attachment->ID)) . '" target="_blank">' . esc_html($attachment->post_title) . '</a></div>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
<?php

            if (current_user_can('comment_tickets') || current_user_can('administrator')) {
                echo '<hr>';
                echo '<div class="thub-ticket-comments">';
                echo '<h4>' . esc_html__('Comments', 'ticket-hub') . '</h4>';

                $top_level_comments = get_comments(array(
                    'post_id' => $post_id,
                    'status' => 'approve',
                    'parent' => 0,
                ));

                if ($top_level_comments) {
                    foreach ($top_level_comments as $comment) {
                        thub_display_comment_withub_replies($comment);
                    }
                } else {
                    echo '<p>' . esc_html__('No comments yet.', 'ticket-hub') . '</p>';
                }

                echo '</div>';
                echo '<div class="thub_ticket-comment-form">';
                if (comments_open($post_id)) {
                    $args = array(
                        'post_id' => $post_id,
                        'title_reply' => '',
                        'comment_field' => '<textarea id="comment" name="comment" rows="10" cols="80" class="thub-comment-area" placeholder="' . esc_attr__('Type your comment here', 'ticket-hub') . '" required="required"></textarea>',
                        'fields' => array(),
                        'label_submit' => esc_html__('Comment', 'ticket-hub'),
                        'comment_notes_before' => '',
                        'comment_notes_after' => '',
                        'submit_button' => '<button type="submit" class="thub-button">%4$s</button>',
                    );
                    comment_form($args);
                } else {
                    echo '<p>' . esc_html__('Comments are closed for this ticket', 'ticket-hub') . '</p>';
                }
                echo '</div>';
            }
        } else {
            echo '<p>' . esc_html__('This ticket is private and cannot be displayed.', 'ticket-hub') . '</p>';
        }
    } else {
        echo '<p>' . esc_html__('Invalid ticket ID.', 'ticket-hub') . '</p>';
    }

    return ob_get_clean();
});

function thub_display_comment_withub_replies($comment, $depth = 0)
{
    echo '<div class="thub-comment-wrapper" style="left: relative;">'; // Wrapper div

    if ($depth > 0) {
        echo '<div style="margin-left:' . esc_attr((($depth - 1) * 20)) . 'px;" class="thub-vertical-bar"></div>';
    }

    echo '<div class="thub-ticket-comment" style="margin-left:' . esc_attr(($depth * 30)) . 'px;">'; // Indent nested comments
    echo '<div class="comment-author"><h5>' . esc_html($comment->comment_author) . '</h5></div>';
    echo '<div class="comment-content"><p>' . esc_html($comment->comment_content) . '</p></div>';
    echo '<div class="thub-comment-date"><p>' . esc_html(get_comment_date('', $comment)) . '</p></div>';
    echo '</div>';
    $replies = get_comments(array(
        'parent' => $comment->comment_ID,
        'status' => 'approve'
    ));

    if ($replies) {
        foreach ($replies as $reply) {
            thub_display_comment_withub_replies($reply, $depth + 1);
        }
    }

    echo '</div>';
}
?>
