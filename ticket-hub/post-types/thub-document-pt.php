<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('init', function () {
    register_post_type('thub_document', array(
        'labels' => array(
            'name' => __('Documents', 'ticket-hub'),
            'singular_name' => __('Document', 'ticket-hub'),
            'menu_name' => __('Documents', 'ticket-hub'),
            'all_items' => __('Documents', 'ticket-hub'),
            'edit_item' => __('Edit Document', 'ticket-hub'),
            'view_item' => __('View Document', 'ticket-hub'),
            'view_items' => __('View Documents', 'ticket-hub'),
            'add_new_item' => __('Add New Document', 'ticket-hub'),
            'add_new' => __('Add New Document', 'ticket-hub'),
            'new_item' => __('New Document', 'ticket-hub'),
            'parent_item_colon' => __('Parent Document:', 'ticket-hub'),
            'search_items' => __('Search Documents', 'ticket-hub'),
            'not_found' => __('No documents found', 'ticket-hub'),
            'not_found_in_trash' => __('No documents found in Trash', 'ticket-hub'),
            'archives' => __('Document Archives', 'ticket-hub'),
            'attributes' => __('Document Attributes', 'ticket-hub'),
            'insert_into_item' => __('Insert into document', 'ticket-hub'),
            'uploaded_to_this_item' => __('Uploaded to this document', 'ticket-hub'),
            'filter_items_list' => __('Filter documents list', 'ticket-hub'),
            'filter_by_date' => __('Filter documents by date', 'ticket-hub'),
            'items_list_navigation' => __('Documents list navigation', 'ticket-hub'),
            'items_list' => __('Documents list', 'ticket-hub'),
            'item_published' => __('Document published.', 'ticket-hub'),
            'item_published_privately' => __('Document published privately.', 'ticket-hub'),
            'item_reverted_to_draft' => __('Document reverted to draft.', 'ticket-hub'),
            'item_scheduled' => __('Document scheduled.', 'ticket-hub'),
            'item_updated' => __('Document updated.', 'ticket-hub'),
            'item_link' => __('Document Link', 'ticket-hub'),
            'item_link_description' => __('A link to a document.', 'ticket-hub'),
        ),
        'description' => __('Add links or files as your documentation', 'ticket-hub'),
        'public' => true,
        'show_in_menu' => 'thub_main_menu',
        'menu_position' => 4,
        'show_in_rest' => true,
        'supports' => array('title'),
    ));
});

// Hook into the 'edit_form_after_title' to display custom fields after the post title.
add_action('edit_form_after_title', function ($post) {
    if ($post->post_type != 'thub_document') {
        return;
    }

    wp_nonce_field(basename(__FILE__), 'thub_document_fields_nonce');

    $type = get_post_meta($post->ID, 'type', true) ?: 'File';
    $file_id = get_post_meta($post->ID, 'file', true);
    $link = get_post_meta($post->ID, 'link', true);
    // Get the full file path on the server for the attachment
    $full_path = get_attached_file($file_id);

    // Use PHP's basename() function to extract just the file name
    $file_name = basename($full_path);

?>
    <h1><?php echo esc_html(__('Type: ', 'ticket-hub')) . esc_html(get_post_meta($post->ID, 'document_category', true)); ?></h1>
    <div>
        <label for="thub-document-type">
            <h3><?php esc_html_e('Type', 'ticket-hub'); ?></h3>
        </label>
        <select name="type" id="thub-document-type">
            <option value="File" <?php selected($type, 'File'); ?>><?php esc_html_e('File', 'ticket-hub'); ?></option>
            <option value="Link" <?php selected($type, 'Link'); ?>><?php esc_html_e('Link', 'ticket-hub'); ?></option>
        </select>
    </div><br />

    <div id="thub-file-upload-section" style="<?php echo ($type == 'File' ? '' : 'display: none;'); ?>">
        <label for="thub-document-file">
            <h3><?php esc_html_e('File', 'ticket-hub'); ?></h3>
        </label>
        <input type="hidden" id="thub-document-file-id" name="file_id" value="<?php echo esc_attr($file_id); ?>" />
        <button type="button" id="thub-upload-file-button" class="button"><?php esc_html_e('Select File', 'ticket-hub'); ?></button>
        <span id="thub-file-name"><?php echo esc_html($file_name); ?></span>
    </div>

    <div id="thub-link-section" style="<?php echo ($type == 'Link' ? '' : 'display: none;'); ?>">
        <label for="thub-document-link">
            <h3><?php esc_html_e('Link', 'ticket-hub'); ?></h3>
        </label>
        <input type="url" name="link" id="thub-document-link" value="<?php echo esc_url($link); ?>" />
    </div>
<?php
    wp_enqueue_script('thub-document-script', PLUGIN_ROOT . 'js/thub-document.js', array('jquery'), '1.0.0', true);
    wp_enqueue_media();
});

add_action('save_post_thub_document', function ($post_id) {
    // Security checks (nonce, autosave, permission).
    if (!isset($_POST['thub_document_fields_nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash ($_POST['thub_document_fields_nonce'])), basename(__FILE__))) {
        return $post_id;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
    if (!current_user_can('edit_post', $post_id)) return $post_id;

    // Check if the title is empty and set it based on the document type
    $post_title = get_the_title($post_id);
    if (empty($post_title)) {
        if (isset($_POST['type']) && $_POST['type'] === 'File' && isset($_POST['file_id'])) {
            $file_id = sanitize_text_field(wp_unslash($_POST['file_id']));
            $full_path = get_attached_file($file_id);
            $file_name = basename($full_path);
            $post_title = $file_name;
        } elseif (isset($_POST['type']) && $_POST['type'] === 'Link' && isset($_POST['link'])) {
            $url = esc_url_raw(wp_unslash($_POST['link']));
            $parsed_url = wp_parse_url($url);
            $domain = $parsed_url['host'];
            $post_title = $domain;
        }
        // Update the post title
        wp_update_post(array(
            'ID'         => $post_id,
            'post_title' => sanitize_text_field($post_title)
        ));
    }

    // Save/update custom fields data.
    if (isset($_POST['type'])) {
        update_post_meta($post_id, 'type', sanitize_text_field(wp_unslash($_POST['type'])));
    }
    if (isset($_POST['link'])) {
        update_post_meta($post_id, 'link', esc_url_raw(wp_unslash($_POST['link'])));
    }
    // Save/update the 'file_id' and 'file_extension' meta fields
    if (isset($_POST['file_id'])) {
        $file_id = sanitize_text_field(wp_unslash($_POST['file_id']));
        update_post_meta($post_id, 'file', $file_id);
    }
});
