<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_menu', 'thub_register_my_custom_menu_page');

function thub_register_my_custom_menu_page()
{
    // Add the submenu for managing Ticket Tags
    add_submenu_page(
        'thub_main_menu', // Parent slug
        esc_html__('Manage Ticket Tags', 'tickethub'), // Page title
        esc_html__('Ticket Tags', 'tickethub'), // Menu title
        'manage_options', // Capability
        'edit-tags.php?taxonomy=thub_ticket_tag&post_type=thub_ticket' // Menu slug (URL to the taxonomy management page)
    );
}
