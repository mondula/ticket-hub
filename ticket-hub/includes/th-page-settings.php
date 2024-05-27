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

function th_settings_init()
{
    // General tab settings
    add_settings_section('th_page_section', 'Page Settings', 'th_settings_section_callback', 'th_general');
    $fields = array('th_form' => 'Ticket Form Page', 'th_tickets' => 'Tickets Page', 'th_changelog' => 'Changelog Page', 'th_faqs' => 'FAQs Page', 'th_documentation' => 'Documentation Page', 'th_profile' => 'TicketHub Profile Page');
    foreach ($fields as $field => $label) {
        add_settings_field($field, $label, 'th_settings_field_callback', 'th_general', 'th_page_section', array('label_for' => $field));
    }

    add_settings_section('th_ticket_id_section', 'Ticket ID Configuration', 'th_ticket_id_section_callback', 'th_general');
    add_settings_field('ticket_prefix', 'Ticket Prefix', 'th_text_field_callback', 'th_general', 'th_ticket_id_section', array('label_for' => 'ticket_prefix'));
    add_settings_field('ticket_suffix', 'Ticket Suffix', 'th_text_field_callback', 'th_general', 'th_ticket_id_section', array('label_for' => 'ticket_suffix'));

    // Check if the Plus plugin is active
    if (function_exists('is_tickethub_plus_active') && is_tickethub_plus_active()) {
        // Plus tab settings
        add_settings_section('th_plus_settings_section', 'TicketHub Plus Settings', 'th_plus_settings_section_callback', 'th_plus');
    }
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

function is_tickethub_plus_active()
{
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    return is_plugin_active('ticketHubPlus/ticketHubPlus.php');
}

function th_page_options()
{
    // Check if the Plus plugin is active
    $is_plus_active = function_exists('is_tickethub_plus_active') ? is_tickethub_plus_active() : false;

    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

?>
    <div class="wrap">
        <h2>TicketHub Settings</h2>
        <h2 class="nav-tab-wrapper">
            <a href="?page=th-page-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
            <a href="?page=th-page-settings&tab=form_editor" class="nav-tab <?php echo $active_tab == 'form_editor' ? 'nav-tab-active' : ''; ?>">Form Editor</a>
            <a href="?page=th-page-settings&tab=user_editor" class="nav-tab <?php echo $active_tab == 'user_editor' ? 'nav-tab-active' : ''; ?>">Ticket Creators</a>
            <?php if ($is_plus_active) : ?>
                <a href="?page=th-page-settings&tab=plus" class="nav-tab <?php echo $active_tab == 'plus' ? 'nav-tab-active' : ''; ?>">Plus</a>
            <?php endif; ?>
        </h2>
        <?php
        if ($active_tab == 'general') {
        ?>
            <form action="options.php" method="post">
                <?php
                settings_fields('th_options_group');
                do_settings_sections('th_general');
                submit_button('Save Settings');
                ?>
            </form>
        <?php
        } elseif ($active_tab == 'form_editor') {
            th_ticket_editor_page();
        } elseif ($active_tab == 'user_editor') {
            th_ticket_creator_form_page();
        } elseif ($is_plus_active && $active_tab == 'plus') {
        ?>
            <form action="options.php" method="post">
                <?php
                settings_fields('th_plus_options_group'); // Ensure correct option group for Plus settings
                do_settings_sections('th_plus');
                submit_button('Save Settings');
                ?>
            </form>
        <?php
        }
        ?>
    </div>
<?php
}

include_once 'th-form-editor-tab.php';
include_once 'th-ticket-creators-tab.php';

?>