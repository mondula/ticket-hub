<?php

add_action('init', function () {
    register_post_type('th_document', array(
        'labels' => array(
            'name' => 'Documents',
            'singular_name' => 'Document',
            'menu_name' => 'Documents',
            'all_items' => 'Documents',
            'edit_item' => 'Edit Document',
            'view_item' => 'View Document',
            'view_items' => 'View Documents',
            'add_new_item' => 'Add New Document',
            'add_new' => 'Add New Document',
            'new_item' => 'New Document',
            'parent_item_colon' => 'Parent Document:',
            'search_items' => 'Search Documents',
            'not_found' => 'No documents found',
            'not_found_in_trash' => 'No documents found in Trash',
            'archives' => 'Document Archives',
            'attributes' => 'Document Attributes',
            'insert_into_item' => 'Insert into document',
            'uploaded_to_this_item' => 'Uploaded to this document',
            'filter_items_list' => 'Filter documents list',
            'filter_by_date' => 'Filter documents by date',
            'items_list_navigation' => 'Documents list navigation',
            'items_list' => 'Documents list',
            'item_published' => 'Document published.',
            'item_published_privately' => 'Document published privately.',
            'item_reverted_to_draft' => 'Document reverted to draft.',
            'item_scheduled' => 'Document scheduled.',
            'item_updated' => 'Document updated.',
            'item_link' => 'Document Link',
            'item_link_description' => 'A link to a document.',
        ),
        'description' => 'Add links or files as your documentation',
        'public' => true,
        'show_in_menu' => 'th_main_menu',
        'show_in_rest' => true,
        'menu_position' => 4,
        'supports' => array(
            0 => 'title',
        ),
        'has_archive' => false,
        'rewrite' => array(
            'feeds' => false,
            'pages' => false,
        ),
        'can_export' => true,
        'delete_with_user' => false,
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

?>
    <div>
        <label for="th-document-type">
            <h3>Type</h3>
        </label>
        <select name="type" id="th-document-type">
            <option value="File" <?php selected($type, 'File'); ?>>File</option>
            <option value="Link" <?php selected($type, 'Link'); ?>>Link</option>
        </select>
    </div><br />

    <div id="th-file-upload-section" style="<?php echo ($type == 'File' ? '' : 'display: none;'); ?>">
        <label for="th-document-file">
            <h3>File</h3>
        </label>
        <input type="hidden" id="th-document-file-id" name="file_id" value="<?php echo esc_attr($file_id); ?>" />
        <button type="button" id="th-upload-file-button" class="button">Select File</button>
        <span id="th-file-name"><?php echo esc_html(get_the_title($file_id)); ?></span>
    </div>

    <div id="th-link-section" style="<?php echo ($type == 'Link' ? '' : 'display: none;'); ?>">
        <label for="th-document-link">
            <h3>Link</h3>
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

// Hook into the 'save_post' action to save the custom field data.
add_action('save_post', function ($post_id) {
    // Security checks (nonce, autosave, permission).
    if (!isset($_POST['th_document_fields_nonce']) || !wp_verify_nonce($_POST['th_document_fields_nonce'], basename(__FILE__))) {
        return $post_id;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
    if (!current_user_can('edit_post', $post_id)) return $post_id;
    
    // Save/update custom fields data.
    if (isset($_POST['file_id'])) {
        update_post_meta($post_id, 'file', sanitize_text_field($_POST['file_id']));
    }
    if (isset($_POST['type'])) {
        update_post_meta($post_id, 'type', sanitize_text_field($_POST['type']));
    }
    if (isset($_POST['link'])) {
        update_post_meta($post_id, 'link', esc_url_raw($_POST['link']));
    }
});
