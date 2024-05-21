<?php

add_action('init', function () {
	register_post_type('th_faq', array(
		'labels' => array(
			'name' => __('FAQs', TEXT_DOMAIN),
			'singular_name' => __('FAQ', TEXT_DOMAIN),
			'menu_name' => __('FAQs', TEXT_DOMAIN),
			'all_items' => __('FAQs', TEXT_DOMAIN),
			'edit_item' => __('Edit FAQ', TEXT_DOMAIN),
			'view_item' => __('View FAQ', TEXT_DOMAIN),
			'view_items' => __('View FAQs', TEXT_DOMAIN),
			'add_new_item' => __('Add New FAQ', TEXT_DOMAIN),
			'add_new' => __('Add New FAQ', TEXT_DOMAIN),
			'new_item' => __('New FAQ', TEXT_DOMAIN),
			'parent_item_colon' => __('Parent FAQ:', TEXT_DOMAIN),
			'search_items' => __('Search FAQs', TEXT_DOMAIN),
			'not_found' => __('No faqs found', TEXT_DOMAIN),
			'not_found_in_trash' => __('No faqs found in Trash', TEXT_DOMAIN),
			'archives' => __('FAQ Archives', TEXT_DOMAIN),
			'attributes' => __('FAQ Attributes', TEXT_DOMAIN),
			'insert_into_item' => __('Insert into faq', TEXT_DOMAIN),
			'uploaded_to_this_item' => __('Uploaded to this faq', TEXT_DOMAIN),
			'filter_items_list' => __('Filter faqs list', TEXT_DOMAIN),
			'filter_by_date' => __('Filter faqs by date', TEXT_DOMAIN),
			'items_list_navigation' => __('FAQs list navigation', TEXT_DOMAIN),
			'items_list' => __('FAQs list', TEXT_DOMAIN),
			'item_published' => __('FAQ published.', TEXT_DOMAIN),
			'item_published_privately' => __('FAQ published privately.', TEXT_DOMAIN),
			'item_reverted_to_draft' => __('FAQ reverted to draft.', TEXT_DOMAIN),
			'item_scheduled' => __('FAQ scheduled.', TEXT_DOMAIN),
			'item_updated' => __('FAQ updated.', TEXT_DOMAIN),
			'item_link' => __('FAQ Link', TEXT_DOMAIN),
			'item_link_description' => __('A link to a faq.', TEXT_DOMAIN),
		),
		'public' => true,
		'show_in_menu' => 'th_main_menu',
		'menu_position' => 3,
		'show_in_rest' => true,
		'supports' => array(
			0 => 'title',
		),
	));
});

add_action('edit_form_after_title', function ($post) {
	// Check if we're on the 'th_faq' post type
	if ($post->post_type !== 'th_faq') {
		return;
	}

	// Use nonce for verification to secure data handling
	wp_nonce_field('th_save_answer_meta', 'th_answer_meta_nonce');

	// Get the current value of the 'answer' field, if any
	$answer_content = get_post_meta($post->ID, '_th_answer', true);

	// Settings for the wp_editor
	$settings = array(
		'textarea_name' => 'th_answer',
		'media_buttons' => true,
		'teeny' => false,
		'tinymce' => true,
		'quicktags' => true
	);

	// Display the label
	echo '<h3>Answer</h3>';

	// Display the editor
	wp_editor($answer_content, 'th_answer_editor', $settings);
});

add_action('save_post', function ($post_id) {
	// Check for nonce security
	if (!isset($_POST['th_answer_meta_nonce']) || !wp_verify_nonce($_POST['th_answer_meta_nonce'], 'th_save_answer_meta')) {
		return;
	}

	// Check if the current user has permission to edit the post
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	// Save/update the meta field in the database
	if (isset($_POST['th_answer'])) {
		update_post_meta($post_id, '_th_answer', $_POST['th_answer']);
	}
});
