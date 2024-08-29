<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode('thub_profile', function () {

    static $thub_ticket_creator_enqueue = false;

    if (!$thub_ticket_creator_enqueue) {
        wp_enqueue_style('thub-profile-style', PLUGIN_ROOT . 'css/thub-profile.css', array(), '1.0.0', 'all');
        $thub_ticket_creator_enqueue = true;
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        // User is not logged in, display the WordPress login form
        $args = array(
            'echo'           => false,
            'redirect'       => (is_ssl() ? 'https://' : 'http://') . sanitize_text_field($_SERVER['HTTP_HOST']) . sanitize_text_field($_SERVER['REQUEST_URI']), // Redirect back to the current page
            'form_id'        => 'thub-loginform',
            'label_username' => __('Username', 'tickethub'),
            'label_password' => __('Password', 'tickethub'),
            'label_remember' => __('Remember Me', 'tickethub'),
            'label_log_in'   => __('Log In', 'tickethub'),
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
        $first_name = esc_html($current_user->user_firstname); // Get first name and escape it

        // If first name is not set, fallback to displaying the username
        if (empty($first_name)) {
            $first_name = esc_html($current_user->display_name);
        }
        $logout_url = wp_logout_url(get_permalink()); // This will redirect users to the same page after logging out

        echo '<div class="thub-profile-head"><h3>' . esc_html__('Profile', 'tickethub') . '</h3><div><a href="' . esc_url($logout_url) . '" class="thub-button">' . esc_html__('Logout', 'tickethub') . '</a></div></div>';
        // translators: %s: User First name.
        echo '<p>' . sprintf(esc_html__('Hello %s', 'tickethub'), esc_attr($first_name)) . '</p>';

        // Call the 'thub_tickets' shortcode with the current user's ID
        echo '<h4>' . esc_html__('Your Tickets', 'tickethub') . '</h4>';
        $user_id = intval($current_user->ID); // Sanitize user ID
        echo do_shortcode('[thub_tickets user_id="' . esc_attr($user_id) . '"]');

        return ob_get_clean();
    }
});
