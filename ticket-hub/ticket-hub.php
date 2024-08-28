<?php
/*
Plugin Name: TicketHub
Description: Streamline your support system with TicketHub, a powerful and user-friendly plugin for managing tickets, FAQs, and documentation efficiently.
Version:     1.0
Author:      Mondula GmbH
Author URI:  https://mondula.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: tickethub
Requires at least: 6.0
Tested up to: 6.5.3
Tags: tickets, support, faq, documentation, change log
*/

define('PLUGIN_ROOT', plugin_dir_url(__FILE__));

function mondula_require_files($directory, $files)
{
    // Include a set of files from a specific directory.
    $path = plugin_dir_path(__FILE__) . $directory . '/';
    foreach ($files as $file) {
        require_once $path . $file;
    }
}

mondula_require_files(
    'post-types',
    [
        'thub-ticket-pt.php',
        'thub-change-pt.php',
        'thub-faq-pt.php',
        'thub-document-pt.php'
    ]
);


add_action('init', function () {
    load_plugin_textdomain('tickethub', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    mondula_require_files('includes', ['thub-page-settings.php', 'thub-ticket-tag-subpage.php']);
    mondula_require_files('shortcodes', ['thub-changelog-sc.php', 'thub-documentation-sc.php', 'thub-faqs-sc.php', 'thub-form-sc.php', 'thub-ticket-sc.php', 'thub-tickets-sc.php', 'thub-profile-sc.php']);

    // Define custom capabilities for submitting and commenting on tickets.
    $capabilities = array(
        'submit_tickets' => true,
        'comment_tickets' => true,
    );

    register_post_status('thub_archive', array(
        'label'                     => 'Archived',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>')
    ));
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('ticket-hub-style', PLUGIN_ROOT . 'css/ticket-hub.css', array(), '1.0.0', 'all');
});

add_filter('single_template', function ($template) {
    global $post;

    // Check if the current post is of type 'thub_ticket'
    if (is_singular('thub_ticket')) {
        $custom_template = '';
        if (wp_is_block_theme()) {
            $custom_template = plugin_dir_path(__FILE__) . 'templates/single-ticket-blockified.php';
        } else {
            $custom_template = plugin_dir_path(__FILE__) . 'templates/single-ticket.php';
        }
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }

    // Return the default template if no custom template conditions are met
    return $template;
});

register_activation_hook(__FILE__, function () {
    add_role('thub_ticket_creator', __('Ticket Creator', 'tickethub'), ['submit_tickets' => true, 'comment_tickets' => true]);
});

register_deactivation_hook(__FILE__, function () {
    $users = get_users(array('role' => 'thub_ticket_creator'));
    foreach ($users as $user) {
        $user->set_role('subscriber');  // Change 'subscriber' to whatever default you consider appropriate
    }

    remove_role('thub_ticket_creator');
});

add_action('after_setup_theme', function () {
    if (in_array('thub_ticket_creator', (array) wp_get_current_user()->roles)) {
        show_admin_bar(false);
    }
});

function enqueue_admin_post_status_script($hook_suffix) {
    global $post;

    // Use get_plugin_data() if you need versioning based on your plugin version
    $version = '1.0.0';
    
    // Ensure the script is only loaded on post and edit screens for 'thub_ticket' post type
    if (($hook_suffix === 'post.php' || $hook_suffix === 'edit.php') && isset($post->post_type) && $post->post_type === 'thub_ticket') {
        // Register and enqueue the script
        wp_register_script(
            'thub-admin-post-status-script', // Handle for the script
            plugin_dir_url(__FILE__) . 'js/thub-admin-post-status.js', // Correct URL to your JS file
            array('jquery'), // Dependencies (in this case, jQuery)
            $version, // Version number
            true // Load in the footer
        );

        // Localize the script with necessary variables
        wp_localize_script('thub-admin-post-status-script', 'tickethub_status_vars', array(
            'archived_text' => esc_js(__('Archived', 'tickethub')),
            'post_status' => esc_js($post->post_status),
        ));

        // Enqueue the script
        wp_enqueue_script('thub-admin-post-status-script');
    }
}
add_action('admin_enqueue_scripts', 'enqueue_admin_post_status_script');


add_action('admin_enqueue_scripts', function () {
    // Use get_plugin_data() if you need versioning based on your plugin version
    $version = '1.0.0';

    // Properly form the URL to the stylesheet
    $admin_style_url = plugins_url('css/thub-admin-style.css', __FILE__);

    // Enqueue the stylesheet
    wp_enqueue_style('thub-admin-style', $admin_style_url, array(), $version);
});

//register and enqueue for form editor script
add_action('admin_enqueue_scripts', function () {

    // Use get_plugin_data() if you need versioning based on your plugin version
    $version = '1.0.0';

    // Properly form the URL to the stylesheet
    $admin_form_editor_tab_script_url = plugins_url('js/thub-form-editor-tab.js', __FILE__);

     // Register the script
     wp_register_script(
        'thub-form-editor-tab-script', // Handle for the script
        $admin_form_editor_tab_script_url, // URL of the script file
        array('jquery'), // Dependencies (in this case, jQuery)
        $version, // Version number
        true // Load in footer
    );

    // Localize the script with translation strings
    wp_localize_script('thub-form-editor-tab-script', 'tickethub_vars', array(
        'text' => esc_html__('Text', 'tickethub'),
        'textarea' => esc_html__('Textarea', 'tickethub'),
        'select' => esc_html__('Select', 'tickethub'),
        'label' => esc_html__('Label', 'tickethub')
    ));

    // Enqueue the script
    wp_enqueue_script('thub-form-editor-tab-script');
});
?>