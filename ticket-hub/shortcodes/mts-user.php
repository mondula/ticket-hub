<?php

add_shortcode('mts-user', function() {

    static $mts_user_enqueue = false;


    if (!$mts_user_enqueue) {
        wp_enqueue_style( 'mts-user-style', PLUGIN_ROOT . 'css/mts-user.css', array(), '', 'all' );
        $mts_user_enqueue = true;
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        // User is not logged in, display the WordPress login form
        $args = array(
            'echo'           => false,
            'redirect'       => (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // Redirect back to the current page
            'form_id'        => 'loginform',
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

        echo '<div class="profile-head"><h3>Profile</h3><div><a href="' . esc_url($logout_url) . '" class="button1">Logout</a></div></div>';
        echo '<p>Hello ' . $first_name . '</p>';

        // Call the 'tickets' shortcode with the current user's ID
        echo '<h4>Your Tickets</h4>';
        $user_id = $current_user->ID;
        echo do_shortcode('[tickets user_id="' . $user_id . '"]');

        return ob_get_clean();
    }
});
