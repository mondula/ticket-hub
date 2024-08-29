<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function thub_register_page_settings()
{
    add_option('thub_options', array()); // Initialize the option if it doesn't exist
    register_setting('thub_options_group', 'thub_options'); // Register a setting group
}
add_action('admin_init', 'thub_register_page_settings');

function thub_add_admin_menu()
{
    // This creates the main menu item for TicketHub
    add_menu_page(
        esc_html__('TicketHub', 'tickethub'),
        esc_html__('TicketHub', 'tickethub'),
        'manage_options',
        'thub_main_menu',
        'thub_page_options',
        'dashicons-tickets'
    );

    // Add a submenu for Settings
    add_submenu_page(
        'thub_main_menu',
        esc_html__('Settings', 'tickethub'),
        esc_html__('Settings', 'tickethub'),
        'manage_options',
        'thub-page-settings',
        'thub_page_options'
    );
}
add_action('admin_menu', 'thub_add_admin_menu');

function thub_settings_init()
{
    // General tab settings
    add_settings_section('thub_page_section', esc_html__('Page Settings', 'tickethub'), 'thub_settings_section_callback', 'thub_general');
    $fields = array(
        'thub_form' => esc_html__('Ticket Form Page', 'tickethub'),
        'thub_tickets' => esc_html__('Tickets Page', 'tickethub'),
        'thub_changelog' => esc_html__('Changelog Page', 'tickethub'),
        'thub_faqs' => esc_html__('FAQs Page', 'tickethub'),
        'thub_documentation' => esc_html__('Documentation Page', 'tickethub'),
        'thub_profile' => esc_html__('TicketHub Profile Page', 'tickethub')
    );
    foreach ($fields as $field => $label) {
        add_settings_field($field, $label, 'thub_settings_field_callback', 'thub_general', 'thub_page_section', array('label_for' => $field));
    }

    add_settings_section('thub_ticket_id_section', esc_html__('Ticket ID Configuration', 'tickethub'), 'thub_ticket_id_section_callback', 'thub_general');
    add_settings_field('ticket_prefix', esc_html__('Ticket Prefix', 'tickethub'), 'thub_text_field_callback', 'thub_general', 'thub_ticket_id_section', array('label_for' => 'ticket_prefix'));
    add_settings_field('ticket_suffix', esc_html__('Ticket Suffix', 'tickethub'), 'thub_text_field_callback', 'thub_general', 'thub_ticket_id_section', array('label_for' => 'ticket_suffix'));

    add_settings_section('thub_archive_settings_section', esc_html__('Ticket Archive Settings', 'tickethub'), 'thub_archive_settings_section_callback', 'thub_general');
    add_settings_field('archive_days', esc_html__('Days to Archive', 'tickethub'), 'thub_number_field_callback', 'thub_general', 'thub_archive_settings_section', array('label_for' => 'archive_days'));

    // Check if the Plus plugin is active
    if (function_exists('thub_is_tickethub_plus_active') && thub_is_tickethub_plus_active()) {
        // Plus tab settings
        add_settings_section('thub_plus_settings_section', esc_html__('TicketHub Plus Settings', 'tickethub'), 'thub_plus_settings_section_callback', 'thub_plus');
    }
}
add_action('admin_init', 'thub_settings_init');

function thub_archive_settings_section_callback()
{
    echo esc_html__('Configure the number of days after which completed tickets should be archived.', 'tickethub');
}

function thub_number_field_callback($args)
{
    $options = get_option('thub_options');
    $field = $args['label_for'];
    echo '<input type="number" id="' . esc_attr($field) . '" name="thub_options[' . esc_attr($field) . ']" value="' . esc_attr($options[$field] ?? '') . '" min="0" />';
}

function thub_ticket_id_section_callback()
{
    echo esc_html__('Customize the prefix and suffix for ticket IDs.', 'tickethub');
}

function thub_text_field_callback($args)
{
    $options = get_option('thub_options');
    $field = $args['label_for'];
    echo '<input type="text" id="' . esc_attr($field) . '" name="thub_options[' . esc_attr($field) . ']" value="' . esc_attr($options[$field] ?? '') . '" />';
}

function thub_settings_section_callback()
{
    echo esc_html__('Select the pages for each plugin functionality.', 'tickethub');
}

function thub_settings_field_callback($args)
{
    $options = get_option('thub_options');
    $field = $args['label_for'];
    // Create a dropdown of all pages for each setting
    wp_dropdown_pages(array(
        'name' => 'thub_options[' . esc_attr($field) . ']',
        'echo' => 1,
        'show_option_none' => '&mdash; ' . esc_html__('Select', 'tickethub') . ' &mdash;',
        'option_none_value' => '0',
        'selected' => isset($options[$field]) ? $options[$field] : ''
    ));
}

function thub_append_shortcode_to_content($content)
{
    global $post;
    $options = get_option('thub_options');

    // Check if the current page is one of the selected pages and append the corresponding shortcode
    foreach ($options as $key => $page_id) {
        if ($post->ID == $page_id) {
            $shortcode = '[' . esc_attr($key) . ']';
            if ($shortcode) {
                $content .= do_shortcode($shortcode);
            }
        }
    }

    return $content;
}
add_filter('the_content', 'thub_append_shortcode_to_content');

function thub_is_tickethub_plus_active()
{
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    return is_plugin_active('ticketHubPlus/ticketHubPlus.php');
}

function thub_page_options()
{
    // Check if the Plus plugin is active
    $is_plus_active = function_exists('thub_is_tickethub_plus_active') ? thub_is_tickethub_plus_active() : false;

    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
?>
    <div class="wrap">
        <h2><?php esc_html_e('TicketHub Settings', 'tickethub'); ?></h2>
        <h2 class="nav-tab-wrapper">
            <a href="?page=thub-page-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('General', 'tickethub'); ?></a>
            <a href="?page=thub-page-settings&tab=form_editor" class="nav-tab <?php echo $active_tab == 'form_editor' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Form Editor', 'tickethub'); ?></a>
            <a href="?page=thub-page-settings&tab=ticket_creators" class="nav-tab <?php echo $active_tab == 'ticket_creators' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Ticket Creators', 'tickethub'); ?></a>
            <?php if ($is_plus_active) : ?>
                <a href="?page=thub-page-settings&tab=plus" class="nav-tab <?php echo $active_tab == 'plus' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Plus', 'tickethub'); ?></a>
            <?php endif; ?>
        </h2>
        <?php
        if ($active_tab == 'general') {
        ?>
            <form action="options.php" method="post">
                <?php
                settings_fields('thub_options_group');
                do_settings_sections('thub_general');
                submit_button(esc_html__('Save Settings', 'tickethub'));
                ?>
            </form>
        <?php
        } elseif ($active_tab == 'form_editor') {
            thub_ticket_editor_page();
        } elseif ($active_tab == 'ticket_creators') {
            thub_ticket_creator_form_page();
        } elseif ($is_plus_active && $active_tab == 'plus') {
        ?>
            <form action="options.php" method="post">
                <?php
                settings_fields('thub_plus_options_group'); // Ensure correct option group for Plus settings
                do_settings_sections('thub_plus');
                submit_button(esc_html__('Save Settings', 'tickethub'));
                ?>
            </form>
        <?php
        }
        ?>
    </div>
<?php
}

include_once 'thub-form-editor-tab.php';
include_once 'thub-ticket-creators-tab.php';

?>