<?php

add_shortcode('th_ticket', function ($atts) {
    static $ticket_enqueue = false;

    if (!$ticket_enqueue) {
        wp_enqueue_script('th-lightbox-script', PLUGIN_ROOT . 'js/th-lightbox.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('th-ticket-style', PLUGIN_ROOT . 'css/th-ticket.css', array(), '1.0.0', 'all');
        $ticket_enqueue = true;
    }

    $atts = shortcode_atts(array('id' => ''), $atts);
    $post_id = intval($atts['id']);

    // Get the options
    $options = get_option('th_options');
    $tickets_page_id = isset($options['th_tickets']) ? intval($options['th_tickets']) : 0;
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
            $current_tags = wp_get_post_terms($post_id, 'th_ticket_tag', array("fields" => "slugs"));

            $related_args = array(
                'post_type' => 'th_ticket',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                // herausgenommen, damit cache reused werden kann, auslassen dann in Schleife
                //'post__not_in' => array($post_id),
                //TODO: Plugin-Check beschwert sich: "Detected usage of meta_query, possible slow query." -> Entweder fixen oder Kommentar lˆschen und ignorieren.
                'tax_query' => array(
                    array(
                        'taxonomy' => 'th_ticket_tag',
                        'field' => 'slug',
                        'terms' => $current_tags
                    )
                )
            );
            $related_tickets = new WP_Query($related_args);
?>
            <div class="th-ticket-details">
                <!-- TODO: Turn SVG into pseudo element with CSS -->
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="22" height="13" viewBox="0 0 22 13">
                    <g id="Gruppe_54" data-name="Gruppe 54" transform="translate(21.648 13) rotate(180)">
                        <g id="Gruppe_53" data-name="Gruppe 53" transform="translate(-0.355)">
                            <path id="Pfad_16" data-name="Pfad 16" d="M21.639,6.363a1.071,1.071,0,0,0-.258-.657L16.289.3A1.093,1.093,0,0,0,14.9.217a.994.994,0,0,0,.01,1.392l3.58,3.8H.955a.955.955,0,1,0,0,1.909H18.487l-3.58,3.8a1.052,1.052,0,0,0-.01,1.392,1.079,1.079,0,0,0,1.392-.08l5.092-5.41A.919.919,0,0,0,21.639,6.363Z" transform="translate(0.364 0)" />
                        </g>
                    </g>
                </svg>
                <a href="<?php echo esc_url($tickets_page_url); ?>" class="th-back-to-archive"><?php esc_attr_e('Back', 'tickethub') ?></a>
                <?php
                $ticket_id = get_post_meta($post_id, 'th_ticket_id', true);
                if (!empty($ticket_id)) {
                    echo '<h3>' . esc_html($ticket_id) . '</h3>';
                }

                if ($related_tickets->have_posts()) {
                    echo '<div class="th-related-tickets"><span>' . esc_html__('Related Tickets', 'tickethub') . '</span>';
                    while ($related_tickets->have_posts()) {
                        $related_tickets->the_post();
                        // gleichen Post ausschlieﬂen
                        if (get_the_ID() != $post_id) {
                            echo '<div><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></div>';
                        }
                    }
                    echo '</div>';
                }

                echo '<div class="th-ticket-field"><h4>' . esc_html__('Description', 'tickethub') . '</h4><p>' . esc_html(get_post_meta($post_id, 'th_ticket_description', true)) . '</p></div>';

                $fields = [
                    'th_ticket_status' => __('Status', 'tickethub'),
                    'th_ticket_type' => __('Type', 'tickethub'),
                ];

                // TODO: Turn SVG into pseudo element with CSS
                $zoomSVG = '<svg class="th-zoom-icon" version="1.1" id="Ebene_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                    viewBox="0 0 67.3 67.24" style="enable-background:new 0 0 67.3 67.24;" xml:space="preserve">
                    <style type="text/css">
                        .st0{fill:#FFFFFF;}
                    </style>
                    <g id="Gruppe_50" transform="translate(0 -0.17)">
                        <path id="Pfad_14" class="st0" d="M33.83,2.35c-17.72,0-32.08,14.36-32.08,32.08s14.36,32.08,32.08,32.08s32.08-14.36,32.08-32.08
                            C65.9,16.72,51.54,2.37,33.83,2.35 M33.83,62.95c-15.75,0-28.52-12.76-28.53-28.51c0-15.75,12.76-28.52,28.51-28.53
                            c15.75,0,28.52,12.76,28.53,28.51c0,0,0,0.01,0,0.01C62.33,50.17,49.57,62.93,33.83,62.95"/>
                        <path id="Pfad_15" class="st0" d="M44.52,32.65h-8.91v-8.91c0-0.98-0.8-1.78-1.78-1.78c-0.98,0-1.78,0.8-1.78,1.78l0,0v8.91h-8.91
                            c-0.98,0-1.78,0.8-1.78,1.78c0,0.98,0.8,1.78,1.78,1.78l0,0h8.91v8.91c0,0.98,0.8,1.78,1.78,1.78c0.98,0,1.78-0.8,1.78-1.78v-8.91
                            h8.91c0.98,0,1.78-0.8,1.78-1.78C46.31,33.45,45.51,32.65,44.52,32.65"/>
                    </g>
                    </svg>';

                echo '<div class="th-ticket-info">';
                $index = 0;
                foreach ($fields as $field => $label) {
                    $value = get_post_meta($post_id, $field, true);
                    echo '<div class="th-ticket-field"><h4>' . esc_html($label) . '</h4><p>' . esc_html($value) . '<p></div>';
                    if ($index == 1) {
                        echo '<div class="th-ticket-field"><h4>' . esc_html__('Creator', 'tickethub') . '</h4><p>' . esc_html($ticket_author) . '</p></div>';
                        echo '<div class="th-ticket-field"><h4>' . esc_html__('E-Mail', 'tickethub') . '</h4><p>' . esc_html($email) . '</p></div>';
                    }
                    $index++;
                }
                echo '</div>';

                $custom_fields = get_option('th_custom_fields', []);
                $custom_field_values = array_filter($custom_fields, function ($field) use ($post_id) {
                    return get_post_meta($post_id, 'thcf_' . sanitize_title($field['label']), true);
                });

                if (!empty($custom_field_values)) {
                    foreach ($custom_field_values as $field) {
                        $field_value = get_post_meta($post_id, 'thcf_' . sanitize_title($field['label']), true);
                        echo '<div class="th-ticket-field">';
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
                    echo '<div class="th-ticket-field"><h4>' . esc_html__('Attachments', 'tickethub') . '</h4>';
                    echo '<div class="th-ticket-attachments">';
                    foreach ($image_attachments as $attachment) {
                        $image_url = wp_get_attachment_url($attachment->ID);
                        if ($image_url) {
                            // Definiere die zul‰ssigen HTML-Tags f¸r das SVG-Icon, somit funktioniert wp_kses()
                            //TODO: Ich habe keine ‹bersicht, welche hier nˆtig sind, ChatGPT hat das hier ausgespuckt, bitte einmal r¸berschauen
                            $allowed_tags = array(
                                'svg'   => array(
                                    'xmlns'    => array(),
                                    'xmlns:xlink' => array(),
                                    'width'    => array(),
                                    'height'   => array(),
                                    'viewBox'  => array(),
                                ),
                                'g'     => array(
                                    'id'       => array(),
                                    'transform'=> array(),
                                    'data-name'=> array(),
                                ),
                                'path'  => array(
                                    'id'       => array(),
                                    'data-name'=> array(),
                                    'd'        => array(),
                                    'transform'=> array(),
                                    'fill'     => array(),
                                ),
                            );
                            echo '<a href="' . esc_url($image_url) . '" class="th-lightbox-trigger"><div class="th-image-container"><img src="' . esc_url($image_url) . '" alt="' . esc_attr($attachment->post_title) . '" class="th-ticket-image">' . wp_kses($zoomSVG, $allowed_tags) . '</div></a>';
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
                echo '<div class="th-ticket-comments">';
                echo '<h4>' . esc_html__('Comments', 'tickethub') . '</h4>';

                $top_level_comments = get_comments(array(
                    'post_id' => $post_id,
                    'status' => 'approve',
                    'parent' => 0,
                ));

                if ($top_level_comments) {
                    foreach ($top_level_comments as $comment) {
                        display_comment_with_replies($comment);
                    }
                } else {
                    echo '<p>' . esc_html__('No comments yet.', 'tickethub') . '</p>';
                }

                echo '</div>';
                echo '<div class="th_ticket-comment-form">';
                if (comments_open($post_id)) {
                    $args = array(
                        'post_id' => $post_id,
                        'title_reply' => '',
                        'comment_field' => '<textarea id="comment" name="comment" rows="10" cols="80" class="th-comment-area" placeholder="' . esc_attr__('Type your comment here', 'tickethub') . '" required="required"></textarea>',
                        'fields' => array(),
                        'label_submit' => esc_html__('Comment', 'tickethub'),
                        'comment_notes_before' => '',
                        'comment_notes_after' => '',
                        'submit_button' => '<button type="submit" class="th-button">%4$s</button>',
                    );
                    comment_form($args);
                } else {
                    echo '<p>' . esc_html__('Comments are closed for this ticket', 'tickethub') . '</p>';
                }
                echo '</div>';
            }
        } else {
            echo '<p>' . esc_html__('This ticket is private and cannot be displayed.', 'tickethub') . '</p>';
        }
    } else {
        echo '<p>' . esc_html__('Invalid ticket ID.', 'tickethub') . '</p>';
    }

    return ob_get_clean();
});

function display_comment_with_replies($comment, $depth = 0)
{
    echo '<div class="th-comment-wrapper" style="left: relative;">'; // Wrapper div

    if ($depth > 0) {
        echo '<div style="margin-left:' . esc_attr((($depth - 1) * 20)) . 'px;" class="th-vertical-bar"></div>';
    }

    echo '<div class="th-ticket-comment" style="margin-left:' . esc_attr(($depth * 30)) . 'px;">'; // Indent nested comments
    echo '<div class="comment-author"><h5>' . esc_html($comment->comment_author) . '</h5></div>';
    echo '<div class="comment-content"><p>' . esc_html($comment->comment_content) . '</p></div>';
    echo '<div class="th-comment-date"><p>' . esc_html(get_comment_date('', $comment)) . '</p></div>';
    echo '</div>';
    $replies = get_comments(array(
        'parent' => $comment->comment_ID,
        'status' => 'approve'
    ));

    if ($replies) {
        foreach ($replies as $reply) {
            display_comment_with_replies($reply, $depth + 1);
        }
    }

    echo '</div>';
}
?>
