<?php

add_shortcode('th_profile', function () {

    static $th_user_enqueue = false;


    if (!$th_user_enqueue) {
        wp_enqueue_style('th-profile-style', PLUGIN_ROOT . 'css/th-profile.css', array(), '', 'all');
        $th_user_enqueue = true;
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        // User is not logged in, display the WordPress login form
        $args = array(
            'echo'           => false,
            'redirect'       => (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // Redirect back to the current page
            'form_id'        => 'th-loginform',
            'label_username' => __('Username'),
            'label_password' => __('Password'),
            'label_remember' => __('Remember Me'),
            'label_log_in'   => __('Log In'),
            'id_username'    => 'user_login',
            'id_password'    => 'user_pass',
            'id_remember'    => 'rememberme',
            'id_submit'      => 'wp-submit',
            'remember'       => true,
            'value_username' => '',
            'value_remember' => false
        );

        return wp_login_form($args);
    } else {
        // User is logged in, display the custom message and a logout button

        ob_start();

        $current_user = wp_get_current_user();
        $first_name = $current_user->user_firstname; // Get first name

        // If first name is not set, fallback to displaying the username
        if (empty($first_name)) {
            $first_name = $current_user->display_name;
        }
        $logout_url = wp_logout_url(get_permalink()); // This will redirect users to the same page after logging out

        echo '<div class="th-profile-head"><h3>Profile</h3><div><a href="' . esc_url($logout_url) . '" class="th-button">Logout</a></div></div>';
        echo '<p>Hello ' . $first_name . '</p>';

        // Call the 'th_tickets' shortcode with the current user's ID
        echo '<h4>Your Tickets</h4>';
        $user_id = $current_user->ID;
        echo do_shortcode('[th_tickets user_id="' . $user_id . '"]');

        return ob_get_clean();
    }
});
