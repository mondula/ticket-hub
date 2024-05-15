<?php

function th_register_page_settings()
{
    add_option('th_options', array()); // Initialize the option if it doesn't exist
    register_setting('th_options_group', 'th_options'); // Register a setting group
}
add_action('admin_init', 'th_register_page_settings');

function th_add_admin_menu()
{
    // This creates the main menu item for TicketHub
    add_menu_page('TicketHub', 'TicketHub', 'manage_options', 'th_main_menu', 'th_page_options', 'dashicons-tickets', 6);

    // Add a submenu for Settings
    add_submenu_page('th_main_menu', 'Settings', 'Settings', 'manage_options', 'th-page-settings', 'th_page_options');
}
add_action('admin_menu', 'th_add_admin_menu');

function th_settings_init() {
    // General tab settings
    add_settings_section('th_page_section', 'Page Settings', 'th_settings_section_callback', 'th_general');
    $fields = array('th_form' => 'Ticket Form Page', 'th_tickets' => 'Tickets Page', 'th_changelog' => 'Changelog Page', 'th_faqs' => 'FAQs Page', 'th_documentation' => 'Documentation Page', 'th_profile' => 'TicketHub User Page');
    foreach ($fields as $field => $label) {
        add_settings_field($field, $label, 'th_settings_field_callback', 'th_general', 'th_page_section', array('label_for' => $field));
    }

    add_settings_section('th_ticket_id_section', 'Ticket ID Configuration', 'th_ticket_id_section_callback', 'th_general');
    add_settings_field('ticket_prefix', 'Ticket Prefix', 'th_text_field_callback', 'th_general', 'th_ticket_id_section', array('label_for' => 'ticket_prefix'));
    add_settings_field('ticket_suffix', 'Ticket Suffix', 'th_text_field_callback', 'th_general', 'th_ticket_id_section', array('label_for' => 'ticket_suffix'));

    // Plus tab settings
    add_settings_section('th_plus_settings_section', 'TicketHub Plus Settings', 'th_plus_settings_section_callback', 'th_plus');
    add_settings_field('plus_feature_enable', 'Enable Plus Features', 'th_checkbox_field_callback', 'th_plus', 'th_plus_settings_section', array('label_for' => 'plus_feature_enable'));
}
add_action('admin_init', 'th_settings_init');



function th_ticket_id_section_callback()
{
    echo 'Customize the prefix and suffix for ticket IDs.';
}

function th_text_field_callback($args)
{
    $options = get_option('th_options');
    $field = $args['label_for'];
    echo '<input type="text" id="' . esc_attr($field) . '" name="th_options[' . esc_attr($field) . ']" value="' . esc_attr($options[$field] ?? '') . '" />';
}

function th_settings_section_callback()
{
    echo 'Select the pages for each plugin functionality.';
}

function th_settings_field_callback($args)
{
    $options = get_option('th_options');
    $field = $args['label_for'];
    // Create a dropdown of all pages for each setting
    wp_dropdown_pages(array(
        'name' => 'th_options[' . $field . ']',
        'echo' => 1,
        'show_option_none' => '&mdash; Select &mdash;',
        'option_none_value' => '0',
        'selected' => isset($options[$field]) ? $options[$field] : ''
    ));
}


function th_plus_settings_section_callback() {
    echo 'Adjust settings for the TicketHub Plus plugin features here.';
}

function th_checkbox_field_callback($args) {
    $options = get_option('th_options');
    $field = $args['label_for'];
    $checked = isset($options[$field]) ? checked($options[$field], 1, false) : '';
    echo '<input type="checkbox" id="' . esc_attr($field) . '" name="th_options[' . esc_attr($field) . ']" value="1"' . $checked . ' />';
}
function th_page_options() {
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

    ?>
    <div class="wrap">
        <h2>TicketHub Settings</h2>
        <h2 class="nav-tab-wrapper">
            <a href="?page=th-page-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
            <a href="?page=th-page-settings&tab=plus" class="nav-tab <?php echo $active_tab == 'plus' ? 'nav-tab-active' : ''; ?>">Plus</a>
        </h2>
        <form action="options.php" method="post">
            <?php
            settings_fields('th_options_group');
            if ($active_tab == 'general') {
                do_settings_sections('th_general');
            } elseif ($active_tab == 'plus') {
                do_settings_sections('th_plus');
            }
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}


function th_append_shortcode_to_content($content)
{
    global $post;
    $options = get_option('th_options');

    // Check if the current page is one of the selected pages and append the corresponding shortcode
    foreach ($options as $key => $page_id) {
        if ($post->ID == $page_id) {
            $shortcode = '[' . $key . ']';
            if ($shortcode) {
                $content .= do_shortcode($shortcode);
            }
        }
    }

    return $content;
}
add_filter('the_content', 'th_append_shortcode_to_content');
