<?php
/*
Plugin Name: Mondula Ticket System
Plugin URI:  https://mondula.com
Description: Mondula Ticket System - Description
Version:     1.0
Author:      Mondula GmbH
Author URI:  https://mondula.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: mondula-ticket-system
*/

define('PLUGIN_ROOT', plugin_dir_url(__FILE__));

function mondula_require_files($directory, $files) {
    // Include a set of files from a specific directory.
    $path = plugin_dir_path(__FILE__) . $directory . '/';
    foreach ($files as $file) {
        require_once $path . $file;
    }
}

add_action('plugins_loaded', function() {
    mondula_require_files('post-types', ['ticket-post-type.php', 'change-post-type.php', 'faq-post-type.php', 'document-post-type.php']);
});


add_action('init', function() {
    mondula_require_files('includes', ['page-settings.php', 'user-subpage.php', 'ticket-form-subpage.php']);
    mondula_require_files('shortcodes',['changelog.php', 'documentation.php', 'faqs.php', 'ticket-form.php', 'ticket.php', 'tickets.php', 'mts-user.php']);

    // Define custom capabilities for submitting and commenting on tickets.
    $capabilities = array(
        'submit_tickets' => true,
        'comment_tickets' => true,
    );

    add_role('mts_user', 'MTS User', $capabilities);
});

add_filter( 'template_include', function($template) {
    global $post;

    // Check if it's a single ticket post. Adjust 'ticket' to your custom post type.
    if ( is_singular('ticket') ) {
        // Check if the theme or child theme has a single-ticket.php file.
        $theme_file = locate_template('single-ticket.php');

        if ( $theme_file ) {
            $template = $theme_file;
        } else {
            // Use plugin's template as a fallback.
            $plugin_template = plugin_dir_path(__FILE__) . 'templates/single-ticket.php';
            if ( file_exists( $plugin_template ) ) {
                $template = $plugin_template;
            }
        }
    }

    return $template;
}, 99 );

register_deactivation_hook(__FILE__, function() {
    $users = get_users(array('role' => 'mts_user'));
    foreach ($users as $user) {
        $user->set_role('subscriber');  // Change 'subscriber' to whatever default you consider appropriate
    }

    remove_role('mts_user');
});

add_action('after_setup_theme', function() {
    // Get the current user object
    $user = wp_get_current_user();

    // Check if the user has the 'mts_user' role
    if (in_array('mts_user', (array) $user->roles)) {
        show_admin_bar(false);
    }
});
