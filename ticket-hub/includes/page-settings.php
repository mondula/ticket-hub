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
    // General tab settings
    add_settings_section('mts_page_section', 'Page Settings', 'mts_settings_section_callback', 'mts');
    $fields = array('ticket-form' => 'Ticket Form Page', 'tickets' => 'Tickets Page', 'changelog' => 'Changelog Page', 'faqs' => 'FAQs Page', 'documentation' => 'Documentation Page', 'mts-user' => 'TicketHub User Page');
    foreach ($fields as $field => $label) {
        add_settings_field($field, $label, 'mts_settings_field_callback', 'mts', 'mts_page_section', array('label_for' => $field));
    }

    add_settings_section('mts_ticket_id_section', 'Ticket ID Configuration', 'mts_ticket_id_section_callback', 'mts');
    add_settings_field('ticket_prefix', 'Ticket Prefix', 'mts_text_field_callback', 'mts', 'mts_ticket_id_section', array('label_for' => 'ticket_prefix'));
    add_settings_field('ticket_suffix', 'Ticket Suffix', 'mts_text_field_callback', 'mts', 'mts_ticket_id_section', array('label_for' => 'ticket_suffix'));

    // Plus tab settings
    if (is_tickethub_plus_active()) {
        add_settings_section('mts_plus_settings_section', 'TicketHub Plus Settings', 'mts_plus_settings_section_callback', 'mts_plus');
        add_settings_field('plus_feature_enable', 'Enable Plus Features', 'mts_checkbox_field_callback', 'mts_plus', 'mts_plus_settings_section', array('label_for' => 'plus_feature_enable'));
    }
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
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

    ?>
    <div class="wrap">
        <h2>TicketHub Settings</h2>
        <h2 class="nav-tab-wrapper">
            <a href="?page=mts-page-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
            <?php if (is_tickethub_plus_active()): ?>
                <a href="?page=mts-page-settings&tab=plus" class="nav-tab <?php echo $active_tab == 'plus' ? 'nav-tab-active' : ''; ?>">Plus</a>
            <?php endif; ?>
        </h2>
        <form action="options.php" method="post">
            <?php
            settings_fields('mts_options_group');
            if ($active_tab == 'general') {
                do_settings_sections('mts');
            } elseif ($active_tab == 'plus') {
                do_settings_sections('mts_plus');
            }
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

function mts_admin_notices() {
    if (!is_tickethub_plus_active()) {
        echo '<div class="notice notice-warning"><p>TicketHub Plus is not active. Some settings may not be available.</p></div>';
    }
}
add_action('admin_notices', 'mts_admin_notices');


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

function is_tickethub_plus_active() {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    return is_plugin_active('ticketHubPlus/ticketHubPlus.php');
}

function mts_plus_settings_section_callback() {
    echo 'Adjust settings for the TicketHub Plus plugin features here.';
}

function mts_checkbox_field_callback($args) {
    $options = get_option('mts_options');
    $field = $args['label_for'];
    $checked = isset($options[$field]) ? checked($options[$field], 1, false) : '';
    echo '<input type="checkbox" id="' . esc_attr($field) . '" name="mts_options[' . esc_attr($field) . ']" value="1"' . $checked . ' />';
}
