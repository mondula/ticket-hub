<?php

add_action('init', function () {
	register_post_type('th_change', array(
		'labels' => array(
			'name' => __('Changes', TEXT_DOMAIN),
			'singular_name' => __('Change', TEXT_DOMAIN),
			'menu_name' => __('Changes', TEXT_DOMAIN),
			'all_items' => __('Changes', TEXT_DOMAIN),
			'edit_item' => __('Edit Change', TEXT_DOMAIN),
			'view_item' => __('View Change', TEXT_DOMAIN),
			'view_items' => __('View Changes', TEXT_DOMAIN),
			'add_new_item' => __('Add New Change', TEXT_DOMAIN),
			'add_new' => __('Add New Change', TEXT_DOMAIN),
			'new_item' => __('New Change', TEXT_DOMAIN),
			'parent_item_colon' => __('Parent Change:', TEXT_DOMAIN),
			'search_items' => __('Search Changes', TEXT_DOMAIN),
			'not_found' => __('No changes found', TEXT_DOMAIN),
			'not_found_in_trash' => __('No changes found in Trash', TEXT_DOMAIN),
			'archives' => __('Change Archives', TEXT_DOMAIN),
			'attributes' => __('Change Attributes', TEXT_DOMAIN),
			'insert_into_item' => __('Insert into change', TEXT_DOMAIN),
			'uploaded_to_this_item' => __('Uploaded to this change', TEXT_DOMAIN),
			'filter_items_list' => __('Filter changes list', TEXT_DOMAIN),
			'filter_by_date' => __('Filter changes by date', TEXT_DOMAIN),
			'items_list_navigation' => __('Changes list navigation', TEXT_DOMAIN),
			'items_list' => __('Changes list', TEXT_DOMAIN),
			'item_published' => __('Change published.', TEXT_DOMAIN),
			'item_published_privately' => __('Change published privately.', TEXT_DOMAIN),
			'item_reverted_to_draft' => __('Change reverted to draft.', TEXT_DOMAIN),
			'item_scheduled' => __('Change scheduled.', TEXT_DOMAIN),
			'item_updated' => __('Change updated.', TEXT_DOMAIN),
			'item_link' => __('Change Link', TEXT_DOMAIN),
			'item_link_description' => __('A link to a change.', TEXT_DOMAIN),
		),
		'public' => true,
		'show_in_menu' => 'th_main_menu',
		'menu_position' => 2,
		'show_in_rest' => true,
		'supports' => array(
			0 => 'title',
		),
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
