<?php

add_action('admin_menu', 'register_my_custom_menu_page');

function register_my_custom_menu_page() {
    // Add the submenu for managing Ticket Tags
    add_submenu_page(
        'mts-main-menu', // Parent slug
        'Manage Ticket Tags', // Page title
        'Ticket Tags', // Menu title
        'manage_options', // Capability
        'edit-tags.php?taxonomy=ticket_tag&post_type=ticket' // Menu slug (URL to the taxonomy management page)
    );
}