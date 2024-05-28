<?php

add_action('init', function () {
	register_post_type('th_faq', array(
		'labels' => array(
			'name' => __('FAQs', 'tickethub'),
			'singular_name' => __('FAQ', 'tickethub'),
			'menu_name' => __('FAQs', 'tickethub'),
			'all_items' => __('FAQs', 'tickethub'),
			'edit_item' => __('Edit FAQ', 'tickethub'),
			'view_item' => __('View FAQ', 'tickethub'),
			'view_items' => __('View FAQs', 'tickethub'),
			'add_new_item' => __('Add New FAQ', 'tickethub'),
			'add_new' => __('Add New FAQ', 'tickethub'),
			'new_item' => __('New FAQ', 'tickethub'),
			'parent_item_colon' => __('Parent FAQ:', 'tickethub'),
			'search_items' => __('Search FAQs', 'tickethub'),
			'not_found' => __('No faqs found', 'tickethub'),
			'not_found_in_trash' => __('No faqs found in Trash', 'tickethub'),
			'archives' => __('FAQ Archives', 'tickethub'),
			'attributes' => __('FAQ Attributes', 'tickethub'),
			'insert_into_item' => __('Insert into faq', 'tickethub'),
			'uploaded_to_this_item' => __('Uploaded to this faq', 'tickethub'),
			'filter_items_list' => __('Filter faqs list', 'tickethub'),
			'filter_by_date' => __('Filter faqs by date', 'tickethub'),
			'items_list_navigation' => __('FAQs list navigation', 'tickethub'),
			'items_list' => __('FAQs list', 'tickethub'),
			'item_published' => __('FAQ published.', 'tickethub'),
			'item_published_privately' => __('FAQ published privately.', 'tickethub'),
			'item_reverted_to_draft' => __('FAQ reverted to draft.', 'tickethub'),
			'item_scheduled' => __('FAQ scheduled.', 'tickethub'),
			'item_updated' => __('FAQ updated.', 'tickethub'),
			'item_link' => __('FAQ Link', 'tickethub'),
			'item_link_description' => __('A link to a faq.', 'tickethub'),
		),
		'public' => true,
		'show_in_menu' => 'th_main_menu',
		'menu_position' => 3,
		'show_in_rest' => true,
		'supports' => array('title'),
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
	echo '<h3>' . esc_html__('Answer', 'tickethub') . '</h3>';

	// Display the editor
	wp_editor(esc_textarea($answer_content), 'th_answer_editor', $settings);
});

add_action('save_post_th_faq', function ($post_id) {
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
		update_post_meta($post_id, '_th_answer', wp_kses_post($_POST['th_answer']));
	}
});
