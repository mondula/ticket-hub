<?php

add_action('init', function () {
	register_post_type('th_change', array(
		'labels' => array(
			'name' => __('Changes', 'tickethub'),
			'singular_name' => __('Change', 'tickethub'),
			'menu_name' => __('Changes', 'tickethub'),
			'all_items' => __('Changes', 'tickethub'),
			'edit_item' => __('Edit Change', 'tickethub'),
			'view_item' => __('View Change', 'tickethub'),
			'view_items' => __('View Changes', 'tickethub'),
			'add_new_item' => __('Add New Change', 'tickethub'),
			'add_new' => __('Add New Change', 'tickethub'),
			'new_item' => __('New Change', 'tickethub'),
			'parent_item_colon' => __('Parent Change:', 'tickethub'),
			'search_items' => __('Search Changes', 'tickethub'),
			'not_found' => __('No changes found', 'tickethub'),
			'not_found_in_trash' => __('No changes found in Trash', 'tickethub'),
			'archives' => __('Change Archives', 'tickethub'),
			'attributes' => __('Change Attributes', 'tickethub'),
			'insert_into_item' => __('Insert into change', 'tickethub'),
			'uploaded_to_this_item' => __('Uploaded to this change', 'tickethub'),
			'filter_items_list' => __('Filter changes list', 'tickethub'),
			'filter_by_date' => __('Filter changes by date', 'tickethub'),
			'items_list_navigation' => __('Changes list navigation', 'tickethub'),
			'items_list' => __('Changes list', 'tickethub'),
			'item_published' => __('Change published.', 'tickethub'),
			'item_published_privately' => __('Change published privately.', 'tickethub'),
			'item_reverted_to_draft' => __('Change reverted to draft.', 'tickethub'),
			'item_scheduled' => __('Change scheduled.', 'tickethub'),
			'item_updated' => __('Change updated.', 'tickethub'),
			'item_link' => __('Change Link', 'tickethub'),
			'item_link_description' => __('A link to a change.', 'tickethub'),
		),
		'public' => true,
		'show_in_menu' => 'th_main_menu',
		'menu_position' => 2,
		'show_in_rest' => true,
		'supports' => array('title'),
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
	echo '<h3>' . esc_html__('Log', 'tickethub') . '</h3>';

	// Display the editor
	wp_editor(esc_textarea($log_content), 'th_log_editor', $settings);
});


add_action('save_post_th_change', function ($post_id) {
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
		update_post_meta($post_id, '_th_log', wp_kses_post($_POST['th_log']));
	}
});
