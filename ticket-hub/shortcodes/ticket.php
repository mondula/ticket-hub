<?php

add_shortcode('ticket', function($atts) {

    static $ticket_enqueue = false;

    if (!$ticket_enqueue) {
        wp_enqueue_script( 'lightbox-script', PLUGIN_ROOT . 'js/lightbox.js', array('jquery'), '', true );
        wp_enqueue_style( 'ticket-style', PLUGIN_ROOT . 'css/ticket.css', array(), '', 'all' );
        $ticket_enqueue = true;
    }

    // Extract the ID from shortcode attributes
    $atts = shortcode_atts(array('id' => ''), $atts);
    $post_id = $atts['id'];

    // Start output buffering
    ob_start();

    // Ensure we have a post ID and it's a valid post
    if ($post_id && get_post($post_id)) {
        // Check post status
        if (get_post_status($post_id) != 'private') {
            // Get the author ID
            $author_id = get_post_field('post_author', $post_id);
            $email = get_the_author_meta('email', $author_id);
            $first_name = get_the_author_meta('first_name', $author_id);
            $last_name = get_the_author_meta('last_name', $author_id);
            $ticket_author = $first_name . ' ' . $last_name; // Concatenate first name and last name
            if (empty($first_name) && empty($last_name)) {
                $ticket_author = get_the_author_meta('display_name', $author_id); // Get the author's display name
            }
            // Get current ticket tags
            $current_tags = wp_get_post_terms($post_id, 'ticket_tag', array("fields" => "slugs"));

            // Query for related tickets
            $related_args = array(
                'post_type' => 'ticket',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'post__not_in' => array($post_id), // Exclude current ticket
                'tax_query' => array(
                    array(
                        'taxonomy' => 'ticket_tag',
                        'field' => 'slug',
                        'terms' => $current_tags
                    )
                )
            );
            $related_tickets = new WP_Query($related_args);
            ?>
            <div class="ticket-details">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="22" height="13" viewBox="0 0 22 13">
                    <g id="Gruppe_54" data-name="Gruppe 54" transform="translate(21.648 13) rotate(180)">
                        <g id="Gruppe_53" data-name="Gruppe 53" transform="translate(-0.355)">
                        <path id="Pfad_16" data-name="Pfad 16" d="M21.639,6.363a1.071,1.071,0,0,0-.258-.657L16.289.3A1.093,1.093,0,0,0,14.9.217a.994.994,0,0,0,.01,1.392l3.58,3.8H.955a.955.955,0,1,0,0,1.909H18.487l-3.58,3.8a1.052,1.052,0,0,0-.01,1.392,1.079,1.079,0,0,0,1.392-.08l5.092-5.41A.919.919,0,0,0,21.639,6.363Z" transform="translate(0.364 0)"/>
                        </g>
                    </g>
                </svg>
                <a onclick="history.back()" class="back-to-archive">Back</a>
                <?php
                $ticket_id = get_post_meta($post_id, 'id', true);
                if (!empty($ticket_id)) {
                    echo '<h3>' . esc_html($ticket_id) . '</h3>';
                }

                // Display related tickets
                if ($related_tickets->have_posts()) {
                    echo '<div class="related-tickets">';
                    echo '<div><span>Related Tickets</span></div>';
                    while ($related_tickets->have_posts()) {
                        $related_tickets->the_post();
                        echo '<div><a href="' . get_permalink() . '">' . get_the_title() . '</a></div>';
                    }
                    echo '</div>';
                }

                $value = get_post_meta($post_id, 'description', true);
                if (!empty($value)) {
                    echo '<div class="ticket-field"><h4>Description</h4><p>' . esc_html($value) . '<p></div>';
                }

               // Define the rest of the fields excluding the 'id'.
                $fields = [
                    'status' => 'Status',
                    'type' => 'Type',
                    'device' => 'Device',
                    'username' => 'Username',
                ];

                $zoomSVG = '<svg class="zoomIcon" version="1.1" id="Ebene_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
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

                // Iterate through fields and display
                echo '<div class="ticket-info">';
                $index = 0;
                foreach ($fields as $field => $label) {
                    $value = get_post_meta($post_id, $field, true);
                        echo '<div class="ticket-field"><h4>' . esc_html($label) . '</h4><p>' . esc_html($value) . '<p></div>';
                    if ($index == 1) {
                        echo '<div class="ticket-field"><h4>Name</h4><p>' . $ticket_author. '<p></div>';
                        echo '<div class="ticket-field"><h4>E-Mail</h4><p>' . $email . '<p></div>';
                    }
                    $index++;
                }
                echo '</div>';

                // Standard fields already included, now include custom fields
                $custom_fields = get_option('mts_custom_fields', []);
                foreach ($custom_fields as $field) {
                    $field_value = get_post_meta($post_id, 'custom_' . sanitize_title($field['label']), true);
                    echo '<div class="ticket-field">';
                    echo '<h4>' . esc_html($field['label']);
                    echo '</h4>';
                    if ($field['type'] === 'text' || $field['type'] === 'textarea') {
                        echo '<p>' . esc_html($field_value) . '</p>';
                    } elseif ($field['type'] === 'select') {
                        echo '<p>' . esc_html($field_value) . '</p>'; // Assuming the value is already stored as a simple string
                    }
                    echo '</div>';
                }

                // Retrieve all attachments for the post
                $attachments = get_posts(array(
                    'post_type' => 'attachment',
                    'posts_per_page' => -1,
                    'post_parent' => $post_id,
                ));

                // Initialize arrays for images and other files
                $image_attachments = [];
                $other_attachments = [];

                foreach ($attachments as $attachment) {
                    $file_path = get_attached_file($attachment->ID); // Get the physical file path
                    $file_type = wp_check_filetype($file_path); // Determine the file type

                    // Categorize attachments by type
                    if (strpos($file_type['type'], 'image') !== false) {
                        $image_attachments[] = $attachment;
                    } else {
                        $other_attachments[] = $attachment;
                    }
                }

                // Output the attachments, starting with images
                echo '<div class="ticket-field"><h4>Attachments</h4>';
                echo '<div class="ticket-attachments">';
                foreach ($image_attachments as $attachment) {
                    $image_url = wp_get_attachment_url($attachment->ID);
                    if ($image_url) {
                        echo '<a href="' . esc_url($image_url) . '" class="lightbox-trigger"><div class="image-container"><img src="' . esc_url($image_url) . '" alt="' . esc_attr($attachment->post_title) . '" class="ticket-image">' . $zoomSVG . '</div></a>';
                    }
                }
                echo '</div>';
                foreach ($other_attachments as $attachment) {
                    echo '<div class="file-link"><a href="' . esc_url(wp_get_attachment_url($attachment->ID)) . '" target="_blank">' . esc_html($attachment->post_title) . '</a></div>';
                }
                echo '</div>';

                ?>
            </div>
            <?php

            // Usage within your existing shortcode logic
            if (current_user_can('comment_tickets') || current_user_can('administrator')) {
                echo '<hr>';
                echo '<div class="ticket-comments">';
                echo '<h4>Comments</h4>';
    
                // Retrieve top-level comments for the current ticket post
                $top_level_comments = get_comments(array(
                    'post_id' => $post_id,
                    'status' => 'approve',
                    'parent' => 0 // Only get top-level comments
                ));
    
                if ($top_level_comments) {
                    foreach ($top_level_comments as $comment) {
                        // Display each top-level comment and its nested replies
                        display_comment_with_replies($comment);
                    }
                } else {
                    echo '<p>No comments yet.</p>';
                }
    
                echo '</div>'; // End of ticket-comments div
                // At the end of your shortcode function, after displaying the ticket details and comments
                echo '<div class="ticket-comment-form">';
                // Check if comments are open for the ticket post
                if (comments_open($post_id)) {
                    $args = array(
                        'post_id' => $post_id,
                        'title_reply' => '',
                        'comment_field' => '<textarea id="comment" name="comment" rows="10" cols="80" class="commentArea" placeholder="Type your comment here" required="required"></textarea>',
                        'fields' => array(),
                        'label_submit' => 'Comment', // Custom text for the submit button
                        'comment_notes_before' => '', // Custom text before the form
                        'comment_notes_after' => '', // Custom text after the form
                        'submit_button' => '<button type="submit" class="button1">%4$s</button>' // Add your custom class here
                    );
                    // Display the comment form for the ticket post
                    comment_form($args);
                } else {
                    echo '<p>Comments are closed for this ticket.</p>';
                }
                echo '</div>'; // End of ticket-comment-form div
            }

        } else {
            echo '<p>This ticket is private and cannot be displayed.</p>';
        }
    } else {
        echo '<p>Invalid ticket ID.</p>';
    }    

    // Return the content
    return ob_get_clean();
});

// Function to display a comment and its nested replies
function display_comment_with_replies($comment, $depth = 0) {
    echo '<div class="comment-wrapper" style="left: relative;">'; // Wrapper div

    if ($depth > 0) {
        // Add the vertical bar for replies (not for top-level comments)
        echo '<div style="margin-left:' . (($depth - 1) * 20) . 'px;" class="vertical-bar"></div>';
    }

    // Existing comment display code
    echo '<div class="ticket-comment" style="margin-left:' . ($depth * 30) . 'px;">'; // Indent nested comments
    echo '<div class="comment-author"><h5>' . esc_html($comment->comment_author) . '</h5></div>';
    echo '<div class="comment-content"><p>' . esc_html($comment->comment_content) . '</p></div>';
    echo '<div class="comment-date"><p>' . esc_html(get_comment_date('', $comment)) . '</p></div>';
    echo '</div>'; // Close .ticket-comment

    // Check for replies
    $replies = get_comments(array(
        'parent' => $comment->comment_ID,
        'status' => 'approve'
    ));

    if ($replies) {
        foreach ($replies as $reply) {
            // Recursively display replies
            display_comment_with_replies($reply, $depth + 1);
        }
    }

    echo '</div>'; // Close .comment-wrapper
}

// add_filter('the_content', function($content) {
//     if (is_singular('ticket') && is_main_query()) {
//         // Start output buffering
//         ob_start();

//         // Your custom content code here
//         // You can include your shortcode or directly integrate the HTML and PHP code that generates your ticket details
//         echo do_shortcode('[ticket id="' . get_the_ID() . '"]');

//         // Get the buffered content
//         $ticket_content = ob_get_clean();

//         // Return the modified content
//         return $ticket_content;
//     }

//     // Return the unmodified content for all other posts/pages
//     return $content;
// });

// add_filter('the_title', function($title, $id) {
//     if (get_post_type($id) === 'ticket') {
//         return '';
//     }
//     return $title;
// }, 10, 2);
