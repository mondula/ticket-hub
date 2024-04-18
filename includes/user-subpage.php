<?php

add_action('admin_menu', function() {
    add_submenu_page(
        'mts-main-menu', // plugin main menu slug if you have one
        'Add MTS User', // Page title
        'MTS Users', // Menu title
        'create_users', // Capability
        'add-mts-user', // Menu slug
        'mts_user_form_page' // Callback function for the page content
    );
});

function get_mts_users() {
   $args = array(
        'role'    => 'mts_user',
        'orderby' => 'user_nicename',
        'order'   => 'ASC'
    );
    $user_query = new WP_User_Query($args);
    return $user_query->get_results();
}

function mts_user_form_page() {
    // Check if the current user has the capability to create users
    if (!current_user_can('create_users')) {
        wp_die('You do not have permission to access this page.');
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_user_nonce']) && wp_verify_nonce($_POST['create_user_nonce'], 'create_mts_user')) {
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $username = strtolower($first_name . '-' . $last_name);

        // Ensure username is unique by appending numbers if needed
        $original_username = $username;
        $i = 1;
        while (username_exists($username)) {
            $username = $original_username . $i;
            $i++;
        }

        if (email_exists($email)) {
            echo '<div class="error"><p>Email already exists.</p></div>';
        } else {
            $user_id = wp_create_user($username, wp_generate_password(), $email);
            if (!is_wp_error($user_id)) {
                // Set the role to 'mts_user'
                $user = new WP_User($user_id);
                $user->set_role('mts_user');
                // Add first and last name to user meta
                update_user_meta($user_id, 'first_name', $first_name);
                update_user_meta($user_id, 'last_name', $last_name);

                wp_send_new_user_notifications($user_id, 'user');

                echo '<div class="updated"><p>New MTS User created.</p></div>';
            } else {
                echo '<div class="error"><p>Error creating user: ' . $user_id->get_error_message() . '</p></div>';
            }
        }
    }

    // Display the form
    ?>
    <div class="wrap">
        <h2>Add MTS User</h2>
        <form method="post">
            <?php wp_nonce_field('create_mts_user', 'create_user_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="first_name">First Name</label></th>
                    <td><input type="text" name="first_name" id="first_name" required></td>
                </tr>
                <tr>
                    <th><label for="last_name">Last Name</label></th>
                    <td><input type="text" name="last_name" id="last_name" required></td>
                </tr>
                <tr>
                    <th><label for="email">Email</label></th>
                    <td><input type="email" name="email" id="email" required></td>
                </tr>
            </table>
            <input type="submit" class="button button-primary" value="Create User">
        </form>
        <h2 style="margin-top: 50px;">List of MTS Users</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $mts_users = get_mts_users();
                if (!empty($mts_users)) {
                    foreach ($mts_users as $user) {
                        $first_name = get_user_meta($user->ID, 'first_name', true);
                        $last_name = get_user_meta($user->ID, 'last_name', true);
                        echo '<tr>';
                        echo '<td>' . esc_html($user->user_login) . '</td>';
                        echo '<td>' . esc_html($first_name) . '</td>';
                        echo '<td>' . esc_html($last_name) . '</td>';
                        echo '<td>' . esc_html($user->user_email) . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="4">No MTS Users found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}
