<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('init', function () {
	register_post_type('thub_change', array(
		'labels' => array(
			'name' => __('Changes', 'ticket-hub'),
			'singular_name' => __('Change', 'ticket-hub'),
			'menu_name' => __('Changes', 'ticket-hub'),
			'all_items' => __('Changes', 'ticket-hub'),
			'edit_item' => __('Edit Change', 'ticket-hub'),
			'view_item' => __('View Change', 'ticket-hub'),
			'view_items' => __('View Changes', 'ticket-hub'),
			'add_new_item' => __('Add New Change', 'ticket-hub'),
			'add_new' => __('Add New Change', 'ticket-hub'),
			'new_item' => __('New Change', 'ticket-hub'),
			'parent_item_colon' => __('Parent Change:', 'ticket-hub'),
			'search_items' => __('Search Changes', 'ticket-hub'),
			'not_found' => __('No changes found', 'ticket-hub'),
			'not_found_in_trash' => __('No changes found in Trash', 'ticket-hub'),
			'archives' => __('Change Archives', 'ticket-hub'),
			'attributes' => __('Change Attributes', 'ticket-hub'),
			'insert_into_item' => __('Insert into change', 'ticket-hub'),
			'uploaded_to_this_item' => __('Uploaded to this change', 'ticket-hub'),
			'filter_items_list' => __('Filter changes list', 'ticket-hub'),
			'filter_by_date' => __('Filter changes by date', 'ticket-hub'),
			'items_list_navigation' => __('Changes list navigation', 'ticket-hub'),
			'items_list' => __('Changes list', 'ticket-hub'),
			'item_published' => __('Change published.', 'ticket-hub'),
			'item_published_privately' => __('Change published privately.', 'ticket-hub'),
			'item_reverted_to_draft' => __('Change reverted to draft.', 'ticket-hub'),
			'item_scheduled' => __('Change scheduled.', 'ticket-hub'),
			'item_updated' => __('Change updated.', 'ticket-hub'),
			'item_link' => __('Change Link', 'ticket-hub'),
			'item_link_description' => __('A link to a change.', 'ticket-hub'),
		),
		'public' => true,
		'show_in_menu' => 'thub_main_menu',
		'menu_position' => 2,
		'show_in_rest' => true,
		'supports' => array('title'),
	));
});

add_action('edit_form_after_title', function ($post) {
	// Check if we're on the 'thub_change' post type
	if ($post->post_type !== 'thub_change') {
		return;
	}

	// Use nonce for verification to secure data handling
	wp_nonce_field('thub_save_log_meta', 'thub_log_meta_nonce');

	// Get the current value of the 'log' field, if any
	$log_content = get_post_meta($post->ID, '_thub_log', true);
	$log_content = wp_specialchars_decode($log_content, ENT_QUOTES);

	// Settings for the wp_editor
	$settings = array(
		'textarea_name' => 'thub_log',
		'media_buttons' => true,
		'teeny' => false,
		'tinymce' => true,
		'quicktags' => true
	);

	// Display the label
	echo '<h3>' . esc_html__('Test', 'ticket-hub') . '</h3>';

	// Display the editor
	wp_editor($log_content, 'thub_log_editor', $settings);
});


add_action('save_post_thub_change', function ($post_id) {
	// Check for nonce security
	if (!isset($_POST['thub_log_meta_nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash ($_POST['thub_log_meta_nonce'])), 'thub_save_log_meta')) {
		return;
	}

	// Check if the current user has permission to edit the post
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	// Save/update the meta field in the database
	if (isset($_POST['thub_log'])) {
		update_post_meta($post_id, '_thub_log', wp_kses_post(wp_unslash($_POST['thub_log'])));
	}
});
