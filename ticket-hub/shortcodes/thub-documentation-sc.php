<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode('thub_documentation', function () {

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
    echo '<input type="text" id="thub-doc-search" placeholder="' . esc_attr__('Search', 'ticket-hub') . '">';
    echo '<select id="thub-document-type" class="thub-select"><option value="">' . esc_html__('- Type -', 'ticket-hub') . '</option><option value="LINK">' . esc_html__('Link', 'ticket-hub') . '</option>';
    foreach ($file_types as $file_type) {
        echo '<option value="' . esc_attr($file_type) . '">' . esc_html($file_type) . '</option>';
    }
    echo '</select>';
    echo '</div>';

    // Query for the actual display
    $the_query = new WP_Query($args);
    echo '<table class="thub-document-table"><thead><tr><th>' . esc_html__('Type', 'ticket-hub') . '</th><th>' . esc_html__('Name', 'ticket-hub') . '</th><th></th></tr></thead><tbody>';
    while ($the_query->have_posts()) {
        $the_query->the_post();

        $document_id = get_the_ID();
        $document_name = get_the_title();
        $document_type = get_post_meta($document_id, 'type', true);

        $type_display = '';
        $document_url = '#';
        $button_text = '';
        $download_attribute = '';
        $icon_class = '';

        if ($document_type === 'File') {
            $file_id = get_post_meta($document_id, 'file', true);
            $document_url = wp_get_attachment_url($file_id);
            $file_path = get_attached_file($file_id);
            $file_type = wp_check_filetype($file_path);
            $file_extension = strtoupper($file_type['ext']);
            $type_display = esc_html($file_extension);
            $button_text = '<span class="thub-hide-text-mobile">' . esc_html__('Download', 'ticket-hub') . '</span>';
            $download_attribute = ' download';
            $icon_class = 'thub-icon-download';
        } elseif ($document_type === 'Link') {
            $document_url = esc_url(get_post_meta($document_id, 'link', true));
            $type_display = esc_html__('LINK', 'ticket-hub');
            $button_text = '<span class="thub-hide-text-mobile">' . esc_html__('Open', 'ticket-hub') . '</span>';
            $icon_class = 'thub-icon-open';
        }

        echo "<tr data-document-type='" . esc_attr($document_type === 'File' ? $file_extension : 'LINK') . "'>";
        echo '<td>' . esc_html($type_display) . '</td>';
        echo '<td><div>' . esc_html($document_name) . '</div></td>';
        echo '<td>' . '<a class="thub-button ' . esc_attr($icon_class) . '" href="' . esc_url($document_url) . '" target="_blank"' . esc_attr($download_attribute) . '>' . wp_kses_post($button_text) . '</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    wp_reset_postdata();

    return ob_get_clean(); // Return the buffered output
});
