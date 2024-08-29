<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode('thub_documentation', function () {

    static $documentation_enqueue = false;

    if (!$documentation_enqueue) {
        wp_enqueue_script('thub-documentation-script', PLUGIN_ROOT . 'js/thub-documentation.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('thub-documentation-style', PLUGIN_ROOT . 'css/thub-documentation.css', array(), '1.0.0', 'all');
        $documentation_enqueue = true;
    }

    ob_start(); // Start output buffering

    $args = array(
        'post_type'      => 'thub_document', // Set to 'document' post type
        'posts_per_page' => -1, // Retrieve all document posts
        'post_status'    => 'publish', // Only select published posts
    );

    // Retrieve all documents to build the list of file types
    $file_types = [];
    $all_documents_query = new WP_Query($args);
    while ($all_documents_query->have_posts()) {
        $all_documents_query->the_post();
        $document_id = get_the_ID();
        $document_type = get_post_meta($document_id, 'type', true);
        if ($document_type === 'File') {
            $file_id = get_post_meta($document_id, 'file', true);
            $file_path = get_attached_file($file_id);
            $file_type = wp_check_filetype($file_path);
            $file_extension = strtoupper($file_type['ext']); // Get stored file extension
            if ($file_extension && !in_array($file_extension, $file_types)) {
                $file_types[] = $file_extension; // Add unique file type to the list
            }
        }
    }
    wp_reset_postdata();

    // Output the search field and dropdown for filtering by document type (File types or Link)
    echo '<div class="thub-document-controls">';
    echo '<input type="text" id="search" placeholder="' . esc_attr__('Search', 'tickethub') . '">';
    echo '<select id="thub-document-type" class="thub-select"><option value="">' . esc_html__('- Type -', 'tickethub') . '</option><option value="LINK">' . esc_html__('Link', 'tickethub') . '</option>';
    foreach ($file_types as $file_type) {
        echo '<option value="' . esc_attr($file_type) . '">' . esc_html($file_type) . '</option>';
    }
    echo '</select>';
    echo '</div>';

    // Query for the actual display
    $the_query = new WP_Query($args);
    echo '<table class="thub-document-table"><thead><tr><th>' . esc_html__('Type', 'tickethub') . '</th><th>' . esc_html__('Name', 'tickethub') . '</th><th></th></tr></thead><tbody>';
    while ($the_query->have_posts()) {
        $the_query->the_post();

        $document_id = get_the_ID();
        $document_name = get_the_title();
        $document_type = get_post_meta($document_id, 'type', true);

        $type_display = '';
        $document_url = '#';
        $button_text = '';
        $download_attribute = '';
        $icon_svg = '';

        if ($document_type === 'File') {
            $file_id = get_post_meta($document_id, 'file', true);
            $document_url = wp_get_attachment_url($file_id);
            $file_path = get_attached_file($file_id);
            $file_type = wp_check_filetype($file_path);
            $file_extension = strtoupper($file_type['ext']);
            $type_display = esc_html($file_extension);
            $button_text = '<span class="thub-hide-text-mobile">' . esc_html__('Download', 'tickethub') . '</span>';
            $download_attribute = ' download';
            // Set the download SVG icon for files
            // TODO: Turn SVG Sting into pseudo element with CSS
            $icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="15.07" height="15.907" viewBox="0 0 15.07 15.907">
                <g id="Gruppe_39" data-name="Gruppe 39">
                <path id="Pfad_8" data-name="Pfad 8" d="M7.361,8.863V.837a.837.837,0,0,0-1.674,0V8.863L2.93,6.107A.838.838,0,0,0,1.745,7.29l4.779,4.777L11.3,7.29a.838.838,0,1,0-1.185-1.184Z" transform="translate(1.011)" fill="#fff"/>
                <path id="Pfad_9" data-name="Pfad 9" d="M.837,6.5a.838.838,0,0,1,.837.837v1.34A1.171,1.171,0,0,0,2.847,9.849h9.377A1.171,1.171,0,0,0,13.4,8.677V7.337a.837.837,0,1,1,1.674,0v1.34a2.846,2.846,0,0,1-2.847,2.847H2.847A2.846,2.846,0,0,1,0,8.677V7.337A.838.838,0,0,1,.837,6.5" transform="translate(0 4.384)" fill="#fff"/>
                </g>
            </svg>';
        } elseif ($document_type === 'Link') {
            $document_url = esc_url(get_post_meta($document_id, 'link', true));
            $type_display = esc_html__('LINK', 'tickethub');
            $button_text = '<span class="thub-hide-text-mobile">' . esc_html__('Open', 'tickethub') . '</span>';
            // Set the open SVG icon for links
            // TODO: Turn SVG Sting into pseudo element with CSS
            $icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16" height="16" viewBox="0 0 16 16">
                <g id="Gruppe_99" data-name="Gruppe 99" transform="translate(-3 -0.01)">
                <g id="Gruppe_98" data-name="Gruppe 98" transform="translate(3 0.188)">
                    <path id="Pfad_27" data-name="Pfad 27" d="M39.331,2.313A2.317,2.317,0,0,0,37.018,0H33.934a.771.771,0,0,0,0,1.542H36.7L31.718,6.523a.771.771,0,0,0,0,1.09.78.78,0,0,0,1.09,0l4.981-4.981V5.4a.771.771,0,1,0,1.542,0Z" transform="translate(-23.398 -0.113)" fill="#fff"/>
                    <path id="Pfad_28" data-name="Pfad 28" d="M.648,20.165a3.813,3.813,0,0,0,1.064,1.064c.972.65,2.393.65,5.225.65s4.254,0,5.225-.65a3.813,3.813,0,0,0,1.064-1.064c.617-.923.648-2.251.65-4.711a.771.771,0,0,0-1.542,0c0,2.228-.041,3.334-.388,3.855a2.281,2.281,0,0,1-.637.637c-.583.391-1.928.391-4.369.391s-3.786,0-4.369-.391a2.315,2.315,0,0,1-.637-.637c-.391-.583-.391-1.928-.391-4.369s0-3.786.391-4.369a2.315,2.315,0,0,1,.637-.637c.522-.35,1.63-.388,3.855-.391A.771.771,0,1,0,6.425,8c-2.462,0-3.788.031-4.711.65A3.847,3.847,0,0,0,.65,9.714C0,10.686,0,12.107,0,14.94s0,4.254.65,5.225Z" transform="translate(0 -6.057)" fill="#fff"/>
                </g>
                </g>
            </svg>';
        }

        echo "<tr data-document-type='" . esc_attr($document_type === 'File' ? $file_extension : 'LINK') . "'>";
        echo '<td>' . esc_html($type_display) . '</td>';

        // Definiere die zulässigen HTML-Tags für das SVG-Icon, somit funktioniert wp_kses()
        //TODO: Ich habe keine Übersicht, welche hier nötig sind, ChatGPT hat das hier ausgespuckt, bitte einmal rüberschauen
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

        echo '<td><div>' . esc_html($document_name) . '</div></td>';
        echo '<td>' . '<a class="thub-button" href="' . esc_url($document_url) . '" target="_blank"' . esc_attr($download_attribute) . '>' . wp_kses($icon_svg, $allowed_tags) . ' ' . wp_kses_post($button_text) . '</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    wp_reset_postdata();

    return ob_get_clean(); // Return the buffered output
});
