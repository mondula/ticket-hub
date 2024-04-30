<?php

function mts_register_page_settings() {
    add_option('mts_options', array()); // Initialize the option if it doesn't exist
    register_setting('mts_options_group', 'mts_options'); // Register a setting group
}
add_action('admin_init', 'mts_register_page_settings');

function mts_add_admin_menu() {
    // This creates the main menu item for TicketHub
    add_menu_page('TicketHub', 'TicketHub', 'manage_options', 'mts-main-menu', 'mts_page_options', 'dashicons-tickets', 6);

    // Add a submenu for Settings
    add_submenu_page('mts-main-menu', 'Settings', 'Settings', 'manage_options', 'mts-page-settings', 'mts_page_options');
}
add_action('admin_menu', 'mts_add_admin_menu');

function mts_settings_init() {
    // Register settings, sections, and fields
    add_settings_section('mts_page_section', 'Page Settings', 'mts_settings_section_callback', 'mts');
    add_settings_section(
        'mts_ticket_id_section', // Section ID
        'Ticket ID Configuration', // Title
        'mts_ticket_id_section_callback', // Callback for rendering the section description
        'mts' // Menu slug
    );

    // Add settings fields for selecting pages
    $fields = array('ticket-form' => 'Ticket Form Page', 'tickets' => 'Tickets Page', 'changelog' => 'Changelog Page', 'faqs' => 'FAQs Page', 'documentation' => 'Documentation Page', 'mts-user' => 'TicketHub User Page');
    foreach ($fields as $field => $label) {
        add_settings_field($field, $label, 'mts_settings_field_callback', 'mts', 'mts_page_section', array('label_for' => $field));
    }

    // Add fields for Ticket ID prefix and suffix in the new section
    add_settings_field(
        'ticket_prefix', // Field ID
        'Ticket Prefix', // Label
        'mts_text_field_callback', // Callback for rendering the field
        'mts', // Menu slug
        'mts_ticket_id_section', // Section ID
        array('label_for' => 'ticket_prefix') // Pass the ID to the callback
    );

    add_settings_field(
        'ticket_suffix',
        'Ticket Suffix',
        'mts_text_field_callback',
        'mts',
        'mts_ticket_id_section',
        array('label_for' => 'ticket_suffix')
    );
}
add_action('admin_init', 'mts_settings_init');

function mts_ticket_id_section_callback() {
    echo 'Customize the prefix and suffix for ticket IDs.';
}

function mts_text_field_callback($args) {
    $options = get_option('mts_options');
    $field = $args['label_for'];
    echo '<input type="text" id="' . esc_attr($field) . '" name="mts_options[' . esc_attr($field) . ']" value="' . esc_attr($options[$field] ?? '') . '" />';
}

function mts_settings_section_callback() {
    echo 'Select the pages for each plugin functionality.';
}

function mts_settings_field_callback($args) {
    $options = get_option('mts_options');
    $field = $args['label_for'];
    // Create a dropdown of all pages for each setting
    wp_dropdown_pages(array(
        'name' => 'mts_options[' . $field . ']',
        'echo' => 1,
        'show_option_none' => '&mdash; Select &mdash;',
        'option_none_value' => '0',
        'selected' => isset($options[$field]) ? $options[$field] : ''
    ));
}

function mts_page_options() {
    ?>
    <div class="wrap">
        <h2>TicketHub Settings</h2>
        <form action="options.php" method="post">
            <?php
            settings_fields('mts_options_group');
            do_settings_sections('mts');
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

function mts_append_shortcode_to_content($content) {
    global $post;
    $options = get_option('mts_options');

    // Check if the current page is one of the selected pages and append the corresponding shortcode
    foreach ($options as $key => $page_id) {
        if ($post->ID == $page_id) {
            $shortcode = '';
            switch ($key) {
                case 'ticket-form':
                    $shortcode = '[ticket-form]';
                    break;
                case 'changelog':
                    $shortcode = '[changelog]';
                    break;
                case 'faqs':
                    $shortcode = '[faqs]';
                    break;
                case 'documentation':
                    $shortcode = '[documentation]';
                    break;
                case 'tickets':
                    $shortcode = '[tickets]';
                    break;
                case 'mts-user':
                    $shortcode = '[mts-user]';
                    break;
            }
            if ($shortcode) {
                $content .= do_shortcode($shortcode);
            }
        }
    }

    return $content;
}
add_filter('the_content', 'mts_append_shortcode_to_content');
