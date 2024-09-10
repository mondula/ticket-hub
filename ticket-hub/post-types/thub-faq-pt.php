<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('init', function () {
	register_post_type('thub_faq', array(
		'labels' => array(
			'name' => __('FAQs', 'ticket-hub'),
			'singular_name' => __('FAQ', 'ticket-hub'),
			'menu_name' => __('FAQs', 'ticket-hub'),
			'all_items' => __('FAQs', 'ticket-hub'),
			'edit_item' => __('Edit FAQ', 'ticket-hub'),
			'view_item' => __('View FAQ', 'ticket-hub'),
			'view_items' => __('View FAQs', 'ticket-hub'),
			'add_new_item' => __('Add New FAQ', 'ticket-hub'),
			'add_new' => __('Add New FAQ', 'ticket-hub'),
			'new_item' => __('New FAQ', 'ticket-hub'),
			'parent_item_colon' => __('Parent FAQ:', 'ticket-hub'),
			'search_items' => __('Search FAQs', 'ticket-hub'),
			'not_found' => __('No faqs found', 'ticket-hub'),
			'not_found_in_trash' => __('No faqs found in Trash', 'ticket-hub'),
			'archives' => __('FAQ Archives', 'ticket-hub'),
			'attributes' => __('FAQ Attributes', 'ticket-hub'),
			'insert_into_item' => __('Insert into faq', 'ticket-hub'),
			'uploaded_to_this_item' => __('Uploaded to this faq', 'ticket-hub'),
			'filter_items_list' => __('Filter faqs list', 'ticket-hub'),
			'filter_by_date' => __('Filter faqs by date', 'ticket-hub'),
			'items_list_navigation' => __('FAQs list navigation', 'ticket-hub'),
			'items_list' => __('FAQs list', 'ticket-hub'),
			'item_published' => __('FAQ published.', 'ticket-hub'),
			'item_published_privately' => __('FAQ published privately.', 'ticket-hub'),
			'item_reverted_to_draft' => __('FAQ reverted to draft.', 'ticket-hub'),
			'item_scheduled' => __('FAQ scheduled.', 'ticket-hub'),
			'item_updated' => __('FAQ updated.', 'ticket-hub'),
			'item_link' => __('FAQ Link', 'ticket-hub'),
			'item_link_description' => __('A link to a faq.', 'ticket-hub'),
		),
		'public' => true,
		'show_in_menu' => 'thub_main_menu',
		'menu_position' => 3,
		'show_in_rest' => true,
		'supports' => array('title'),
	));
});

add_action('edit_form_after_title', function ($post) {
	// Check if we're on the 'thub_faq' post type
	if ($post->post_type !== 'thub_faq') {
		return;
	}

	// Use nonce for verification to secure data handling
	wp_nonce_field('thub_save_answer_meta', 'thub_answer_meta_nonce');

	// Get the current value of the 'answer' field, if any
	$answer_content = get_post_meta($post->ID, '_thub_answer', true);
	$answer_content = wp_specialchars_decode($answer_content, ENT_QUOTES);

	// Settings for the wp_editor
	$settings = array(
		'textarea_name' => 'thub_answer',
		'media_buttons' => true,
		'teeny' => false,
		'tinymce' => true,
		'quicktags' => true
	);

	// Display the label
	echo '<h3>' . esc_html__('Answer', 'ticket-hub') . '</h3>';

	// Display the editor
	wp_editor($answer_content, 'thub_answer_editor', $settings);
});

add_action('save_post_thub_faq', function ($post_id) {
	// Check for nonce security
	if (!isset($_POST['thub_answer_meta_nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash ($_POST['thub_answer_meta_nonce'])), 'thub_save_answer_meta')) {
		return;
	}

	// Check if the current user has permission to edit the post
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	// Save/update the meta field in the database
	if (isset($_POST['thub_answer'])) {
		update_post_meta($post_id, '_thub_answer', wp_kses_post(wp_unslash($_POST['thub_answer'])));
	}
});
