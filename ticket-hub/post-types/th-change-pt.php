<?php

add_action('init', function () {
	register_post_type('th_change', array(
		'labels' => array(
			'name' => 'Changes',
			'singular_name' => 'Change',
			'menu_name' => 'Changes',
			'all_items' => 'Changes',
			'edit_item' => 'Edit Change',
			'view_item' => 'View Change',
			'view_items' => 'View Changes',
			'add_new_item' => 'Add New Change',
			'add_new' => 'Add New Change',
			'new_item' => 'New Change',
			'parent_item_colon' => 'Parent Change:',
			'search_items' => 'Search Changes',
			'not_found' => 'No changes found',
			'not_found_in_trash' => 'No changes found in Trash',
			'archives' => 'Change Archives',
			'attributes' => 'Change Attributes',
			'insert_into_item' => 'Insert into change',
			'uploaded_to_this_item' => 'Uploaded to this change',
			'filter_items_list' => 'Filter changes list',
			'filter_by_date' => 'Filter changes by date',
			'items_list_navigation' => 'Changes list navigation',
			'items_list' => 'Changes list',
			'item_published' => 'Change published.',
			'item_published_privately' => 'Change published privately.',
			'item_reverted_to_draft' => 'Change reverted to draft.',
			'item_scheduled' => 'Change scheduled.',
			'item_updated' => 'Change updated.',
			'item_link' => 'Change Link',
			'item_link_description' => 'A link to a change.',
		),
		'public' => true,
		'show_in_menu' => 'th_main_menu',
		'show_in_rest' => true,
		'menu_position' => 2,
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

add_action('edit_form_after_title', function ($post) {
	// Check if we're on the 'th_change' post type
	if ($post->post_type !== 'th_change') {
		return;
	}

	// Use nonce for verification to secure data handling
	wp_nonce_field('th_save_log_meta', 'th_log_meta_nonce');

	// Get the current value of the 'log' field, if any
	$log_content = get_post_meta($post->ID, '_th_log', true);

	// Settings for the wp_editor
	$settings = array(
		'textarea_name' => 'th_log',
		'media_buttons' => true,
		'teeny' => false,
		'tinymce' => true,
		'quicktags' => true
	);

	// Display the label
	echo '<h3>Log</h3>';

	// Display the editor
	wp_editor($log_content, 'th_log_editor', $settings);
});


add_action('save_post', function ($post_id) {
	// Check for nonce security
	if (!isset($_POST['th_log_meta_nonce']) || !wp_verify_nonce($_POST['th_log_meta_nonce'], 'th_save_log_meta')) {
		return;
	}

	// Check if the current user has permission to edit the post
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	// Save/update the meta field in the database
	if (isset($_POST['th_log'])) {
		update_post_meta($post_id, '_th_log', $_POST['th_log']);
	}
});
