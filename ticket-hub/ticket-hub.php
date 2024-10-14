<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
Plugin Name: TicketHub
Description: Streamline your support system with TicketHub, a powerful and user-friendly plugin for managing tickets, FAQs, and documentation efficiently.
Version:     1.0.1
Author:      Mondula GmbH
Author URI:  https://mondula.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ticket-hub
Requires at least: 6.0
Tested up to: 6.6.1
Tags: tickets, support, faq, documentation, change log
*/

define('THUB_PLUGIN_ROOT', plugin_dir_url(__FILE__));

function thub_require_files($directory, $files)
{
    // Include a set of files from a specific directory.
    $path = plugin_dir_path(__FILE__) . $directory . '/';
    foreach ($files as $file) {
        require_once $path . $file;
    }
}

thub_require_files(
    'post-types',
    [
        'thub-ticket-pt.php',
        'thub-change-pt.php',
        'thub-faq-pt.php',
        'thub-document-pt.php'
    ]
);


add_action('init', function () {
    load_plugin_textdomain('ticket-hub', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    thub_require_files('includes', ['thub-page-settings.php', 'thub-ticket-tag-subpage.php']);
    thub_require_files('shortcodes', ['thub-changelog-sc.php', 'thub-documentation-sc.php', 'thub-faqs-sc.php', 'thub-form-sc.php', 'thub-ticket-sc.php', 'thub-tickets-sc.php', 'thub-profile-sc.php']);

    // Define custom capabilities for submitting and commenting on tickets.
    $capabilities = array(
        'submit_tickets' => true,
        'comment_tickets' => true,
    );

    register_post_status('thub_archive', array(
        'label'                     => _x('Archived', 'post status', 'ticket-hub'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        // translators: %s: Number of archived posts
        'label_count'               => _n_noop(
            'Archived <span class="count">(%s)</span>',
            'Archived <span class="count">(%s)</span>',
            'ticket-hub'
        )
    ));
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
    add_role('thub_ticket_creator', __('Ticket Creator', 'ticket-hub'), ['submit_tickets' => true, 'comment_tickets' => true]);
});

register_deactivation_hook(__FILE__, function () {
    $users = get_users(array('role' => 'thub_ticket_creator'));
    foreach ($users as $user) {
        $user->set_role('subscriber');  // Change 'subscriber' to whatever default you consider appropriate
    }
    remove_role('thub_ticket_creator');

    $timestamp = wp_next_scheduled('thub_archive_done_tickets');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'thub_archive_done_tickets');
    }
});

add_action('after_setup_theme', function () {
    if (in_array('thub_ticket_creator', (array) wp_get_current_user()->roles)) {
        show_admin_bar(false);
    }
});

function thub_enqueue_admin_scripts() {
    $plugin_url = plugin_dir_url(__FILE__);
    $version = '1.0.1'; // You might want to use a dynamic version number

    // Enqueue admin scripts and styles
    wp_enqueue_script('thub-admin-js', $plugin_url . 'dist/js/ticket-hub-admin.min.js', array('jquery'), $version, true);
    wp_enqueue_style('thub-admin-css', $plugin_url . 'dist/css/ticket-hub-admin.min.css', array(), $version);

    // Localize the script with necessary variables
    wp_localize_script('thub-admin-js', 'thub_admin_vars', array(
        'archived_text' => esc_js(__('Archived', 'ticket-hub')),
        'post_status' => isset($GLOBALS['post']) ? esc_js($GLOBALS['post']->post_status) : '',
        'text' => esc_html__('Text', 'ticket-hub'),
        'textarea' => esc_html__('Textarea', 'ticket-hub'),
        'select' => esc_html__('Select', 'ticket-hub'),
        'label' => esc_html__('Label', 'ticket-hub')
    ));
}

function thub_enqueue_public_scripts() {
    $plugin_url = plugin_dir_url(__FILE__);
    $version = '1.0.1'; // You might want to use a dynamic version number

    // Enqueue public scripts and styles
    wp_enqueue_script('thub-public-js', $plugin_url . 'dist/js/ticket-hub.min.js', array('jquery'), $version, true);
    wp_enqueue_style('thub-public-css', $plugin_url . 'dist/css/ticket-hub.min.css', array(), $version);

    // Localize script
    wp_localize_script('thub-public-js', 'thub_public_vars', array(
        'ajax_url' => esc_url(admin_url('admin-ajax.php')),
        'user_id' => get_current_user_id(),
        'nonce' => wp_create_nonce('fetch_tickets_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'thub_enqueue_public_scripts');
add_action('admin_enqueue_scripts', 'thub_enqueue_admin_scripts');
?>