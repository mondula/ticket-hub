<?php

add_action('admin_menu', function() {
    add_submenu_page(
        'th_main_menu', // Plugin main menu slug if you have one
        'Add User', // Page title
        'Users', // Menu title
        'create_users', // Capability
        'add-th-user', // Menu slug
        'th_user_form_page' // Callback function for the page content
    );
});


function get_th_users()
{
    $args = array(
        'role'    => 'th_user',
        'orderby' => 'user_nicename',
        'order'   => 'ASC'
    );
    $user_query = new WP_User_Query($args);
    return $user_query->get_results();
}

function th_user_form_page()
{
    // Check if the current user has the capability to create users
    if (!current_user_can('create_users')) {
        wp_die('You do not have permission to access this page.');
    }

    // Handle form submission for single user creation
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_user_nonce']) && wp_verify_nonce($_POST['create_user_nonce'], 'create_th_user')) {
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
            echo '<div class="error"><p>' . __('Email already exists.', 'tickethub') . '</p></div>';
        } else {
            $user_id = wp_create_user($username, wp_generate_password(), $email);
            if (!is_wp_error($user_id)) {
                // Set the role to 'th_user'
                $user = new WP_User($user_id);
                $user->set_role('th_user');
                // Add first and last name to user meta
                update_user_meta($user_id, 'first_name', $first_name);
                update_user_meta($user_id, 'last_name', $last_name);

                wp_send_new_user_notifications($user_id, 'user');

                echo '<div class="updated"><p>' . __('New User created.', 'tickethub') . '</p></div>';
            } else {
                echo '<div class="error"><p>Error creating user: ' . $user_id->get_error_message() . '</p></div>';
            }
        }
    }

    // Handle CSV upload if the Plus plugin is active
    if (is_plugin_active('ticket-hub-plus/ticket-hub-plus.php')) {
        th_plus_handle_csv_upload();
    }

    // Display the form
    ?>
    <div class="wrap">
        <h2><?php _e('Add User', 'tickethub') ?></h2>
        <form method="post">
            <?php wp_nonce_field('create_th_user', 'create_user_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="first_name"><?php _e('First Name', 'tickethub') ?></label></th>
                    <td><input type="text" name="first_name" id="first_name" required></td>
                </tr>
                <tr>
                    <th><label for="last_name"><?php _e('Last Name', 'tickethub') ?></label></th>
                    <td><input type="text" name="last_name" id="last_name" required></td>
                </tr>
                <tr>
                    <th><label for="email"><?php _e('Email', 'tickethub') ?></label></th>
                    <td><input type="email" name="email" id="email" required></td>
                </tr>
            </table>
            <input type="submit" class="button button-primary" value="Create User">
        </form>

        <?php
        // Display bulk upload form if the Plus plugin is active
        if (is_plugin_active('ticketHubPlus/ticketHubPlus.php')) {
            th_plus_handle_csv_upload();
            th_plus_bulk_upload_form();
        }
        ?>

        <h2 style="margin-top: 50px;"><?php _e('List of Users', 'tickethub') ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Username', 'tickethub') ?></th>
                    <th><?php _e('First Name', 'tickethub') ?></th>
                    <th><?php _e('Last Name', 'tickethub') ?></th>
                    <th><?php _e('Email', 'tickethub') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $th_users = get_th_users();
                if (!empty($th_users)) {
                    foreach ($th_users as $user) {
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
                    echo '<tr><td colspan="4">' . __('No Users found.', 'tickethub') . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}
