<?php
#require 'wp-includes/http.php';
add_action('init', function () {
    register_post_type('th_document', array(
        'labels' => array(
            'name' => __('Documents', 'tickethub'),
            'singular_name' => __('Document', 'tickethub'),
            'menu_name' => __('Documents', 'tickethub'),
            'all_items' => __('Documents', 'tickethub'),
            'edit_item' => __('Edit Document', 'tickethub'),
            'view_item' => __('View Document', 'tickethub'),
            'view_items' => __('View Documents', 'tickethub'),
            'add_new_item' => __('Add New Document', 'tickethub'),
            'add_new' => __('Add New Document', 'tickethub'),
            'new_item' => __('New Document', 'tickethub'),
            'parent_item_colon' => __('Parent Document:', 'tickethub'),
            'search_items' => __('Search Documents', 'tickethub'),
            'not_found' => __('No documents found', 'tickethub'),
            'not_found_in_trash' => __('No documents found in Trash', 'tickethub'),
            'archives' => __('Document Archives', 'tickethub'),
            'attributes' => __('Document Attributes', 'tickethub'),
            'insert_into_item' => __('Insert into document', 'tickethub'),
            'uploaded_to_this_item' => __('Uploaded to this document', 'tickethub'),
            'filter_items_list' => __('Filter documents list', 'tickethub'),
            'filter_by_date' => __('Filter documents by date', 'tickethub'),
            'items_list_navigation' => __('Documents list navigation', 'tickethub'),
            'items_list' => __('Documents list', 'tickethub'),
            'item_published' => __('Document published.', 'tickethub'),
            'item_published_privately' => __('Document published privately.', 'tickethub'),
            'item_reverted_to_draft' => __('Document reverted to draft.', 'tickethub'),
            'item_scheduled' => __('Document scheduled.', 'tickethub'),
            'item_updated' => __('Document updated.', 'tickethub'),
            'item_link' => __('Document Link', 'tickethub'),
            'item_link_description' => __('A link to a document.', 'tickethub'),
        ),
        'description' => __('Add links or files as your documentation', 'tickethub'),
        'public' => true,
        'show_in_menu' => 'th_main_menu',
        'menu_position' => 4,
        'show_in_rest' => true,
        'supports' => array('title'),
    ));
});

// Hook into the 'edit_form_after_title' to display custom fields after the post title.
add_action('edit_form_after_title', function ($post) {
    if ($post->post_type != 'th_document') {
        return;
    }

    wp_nonce_field(basename(__FILE__), 'th_document_fields_nonce');

    $type = get_post_meta($post->ID, 'type', true) ?: 'File';
    $file_id = get_post_meta($post->ID, 'file', true);
    $link = get_post_meta($post->ID, 'link', true);
    // Get the full file path on the server for the attachment
    $full_path = get_attached_file($file_id);

    // Use PHP's basename() function to extract just the file name
    $file_name = basename($full_path);

?>
    <h1><?php echo esc_html(__('Type: ', 'tickethub')) . esc_html(get_post_meta($post->ID, 'document_category', true)); ?></h1>
    <div>
        <label for="th-document-type">
            <h3><?php esc_html_e('Type', 'tickethub'); ?></h3>
        </label>
        <select name="type" id="th-document-type">
            <option value="File" <?php selected($type, 'File'); ?>><?php esc_html_e('File', 'tickethub'); ?></option>
            <option value="Link" <?php selected($type, 'Link'); ?>><?php esc_html_e('Link', 'tickethub'); ?></option>
        </select>
    </div><br />

    <div id="th-file-upload-section" style="<?php echo ($type == 'File' ? '' : 'display: none;'); ?>">
        <label for="th-document-file">
            <h3><?php esc_html_e('File', 'tickethub'); ?></h3>
        </label>
        <input type="hidden" id="th-document-file-id" name="file_id" value="<?php echo esc_attr($file_id); ?>" />
        <button type="button" id="th-upload-file-button" class="button"><?php esc_html_e('Select File', 'tickethub'); ?></button>
        <span id="th-file-name"><?php echo esc_html($file_name); ?></span>
    </div>

    <div id="th-link-section" style="<?php echo ($type == 'Link' ? '' : 'display: none;'); ?>">
        <label for="th-document-link">
            <h3><?php esc_html_e('Link', 'tickethub'); ?></h3>
        </label>
        <input type="url" name="link" id="th-document-link" value="<?php echo esc_url($link); ?>" />
    </div>

    <script>
        jQuery(document).ready(function($) {
            $('#th-document-type').change(function() {
                if ($(this).val() === 'File') {
                    $('#th-file-upload-section').show();
                    $('#th-link-section').hide();
                } else {
                    $('#th-file-upload-section').hide();
                    $('#th-link-section').show();
                }
            });

            $('#th-upload-file-button').click(function(e) {
                e.preventDefault();
                var fileFrame;

                if (fileFrame) {
                    fileFrame.open();
                    return;
                }

                fileFrame = wp.media({
                    title: 'Select or Upload a File',
                    button: {
                        text: 'Use this file'
                    },
                    multiple: false
                });

                fileFrame.on('select', function() {
                    var attachment = fileFrame.state().get('selection').first().toJSON();
                    $('#th-document-file-id').val(attachment.id);
                    $('#th-file-name').text(attachment.title);
                });

                fileFrame.open();
            });
        });
    </script>
<?php
    wp_enqueue_media();
});

add_action('save_post_th_document', function ($post_id) {
    // Security checks (nonce, autosave, permission).
    if (!isset($_POST['th_document_fields_nonce']) || !wp_verify_nonce($_POST['th_document_fields_nonce'], basename(__FILE__))) {
        return $post_id;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
    if (!current_user_can('edit_post', $post_id)) return $post_id;

    // Check if the title is empty and set it based on the document type
    $post_title = get_the_title($post_id);
    if (empty($post_title)) {
        if (isset($_POST['type']) && $_POST['type'] === 'File' && isset($_POST['file_id'])) {
            $file_id = sanitize_text_field($_POST['file_id']);
            $full_path = get_attached_file($file_id);
            $file_name = basename($full_path);
            $post_title = $file_name;
        } elseif (isset($_POST['type']) && $_POST['type'] === 'Link' && isset($_POST['link'])) {
            $url = esc_url_raw($_POST['link']);
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
        update_post_meta($post_id, 'type', sanitize_text_field($_POST['type']));
    }
    if (isset($_POST['link'])) {
        update_post_meta($post_id, 'link', esc_url_raw($_POST['link']));
    }
    // Save/update the 'file_id' and 'file_extension' meta fields
    if (isset($_POST['file_id'])) {
        $file_id = sanitize_text_field($_POST['file_id']);
        update_post_meta($post_id, 'file', $file_id);
    }
});
