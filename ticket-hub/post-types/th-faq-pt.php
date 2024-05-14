<?php

add_action('init', function () {
	register_post_type('th_faq', array(
		'labels' => array(
			'name' => 'FAQs',
			'singular_name' => 'FAQ',
			'menu_name' => 'FAQs',
			'all_items' => 'FAQs',
			'edit_item' => 'Edit FAQ',
			'view_item' => 'View FAQ',
			'view_items' => 'View FAQs',
			'add_new_item' => 'Add New FAQ',
			'add_new' => 'Add New FAQ',
			'new_item' => 'New FAQ',
			'parent_item_colon' => 'Parent FAQ:',
			'search_items' => 'Search FAQs',
			'not_found' => 'No faqs found',
			'not_found_in_trash' => 'No faqs found in Trash',
			'archives' => 'FAQ Archives',
			'attributes' => 'FAQ Attributes',
			'insert_into_item' => 'Insert into faq',
			'uploaded_to_this_item' => 'Uploaded to this faq',
			'filter_items_list' => 'Filter faqs list',
			'filter_by_date' => 'Filter faqs by date',
			'items_list_navigation' => 'FAQs list navigation',
			'items_list' => 'FAQs list',
			'item_published' => 'FAQ published.',
			'item_published_privately' => 'FAQ published privately.',
			'item_reverted_to_draft' => 'FAQ reverted to draft.',
			'item_scheduled' => 'FAQ scheduled.',
			'item_updated' => 'FAQ updated.',
			'item_link' => 'FAQ Link',
			'item_link_description' => 'A link to a faq.',
		),
		'public' => true,
		'show_in_menu' => 'th_main_menu',
		'show_in_rest' => true,
		'menu_position' => 3,
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
