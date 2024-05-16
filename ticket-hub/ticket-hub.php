<?php
/*
Plugin Name: TicketHub
Plugin URI:  https://mondula.com
Description: TicketHub - Description
Version:     1.0
Author:      Mondula GmbH
Author URI:  https://mondula.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: tickethub
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

add_action('plugins_loaded', function () {
    mondula_require_files('post-types', ['th-ticket-pt.php', 'th-change-pt.php', 'th-faq-pt.php', 'th-document-pt.php']);
});


add_action('init', function () {
    mondula_require_files('includes', ['th-page-settings.php', 'th-user-subpage.php', 'th-form-subpage.php', 'th-ticket-tag-subpage.php']);
    mondula_require_files('shortcodes', ['th-changelog-sc.php', 'th-documentation-sc.php', 'th-faqs-sc.php', 'th-form-sc.php', 'th-ticket-sc.php', 'th-tickets-sc.php', 'th-profile-sc.php']);

    // Define custom capabilities for submitting and commenting on tickets.
    $capabilities = array(
        'submit_tickets' => true,
        'comment_tickets' => true,
    );

    register_post_status('archive', array(
        'label'                     => 'Archived',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>')
    ));
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('ticket-hub-style', PLUGIN_ROOT . 'css/ticket-hub.css', array(), '1.0', 'all');
});

add_filter('single_template', function ($template) {
    global $post;

    // Check if the current post is of type 'th_ticket'
    if (is_singular('th_ticket')) {
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
    add_role('th_user', 'TicketHub User', ['submit_tickets' => true, 'comment_tickets' => true]);
});

register_deactivation_hook(__FILE__, function () {
    $users = get_users(array('role' => 'th_user'));
    foreach ($users as $user) {
        $user->set_role('subscriber');  // Change 'subscriber' to whatever default you consider appropriate
    }

    remove_role('th_user');
});

add_action('after_setup_theme', function () {
    if (in_array('th_user', (array) wp_get_current_user()->roles)) {
        show_admin_bar(false);
    }
});

function enqueue_admin_post_status_script()
{
    global $post;
    if ($post->post_type == 'th_ticket') {
?>
        <script>
            jQuery(document).ready(function($) {
                // Append the new status to the status selector in the edit post and quick edit screens
                $("select[name='post_status']").append("<option value='archive'>Archived</option>");

                // Check if the current post status is 'archive' and update the selector
                <?php if ('archive' == $post->post_status) : ?>
                    $("select[name='post_status']").val('archive');
                    $('#post-status-display').text('Archived');
                <?php endif; ?>

                // Add the status to the quick edit
                $(".editinline").click(function() {
                    var $row = $(this).closest('tr');
                    var $status = $row.find('.status').text();
                    if ('archive' === $status) {
                        $('select[name="_status"]', '.inline-edit-row').val('archive');
                    }
                });
            });
        </script>
<?php
    }
}
add_action('admin_footer-post.php', 'enqueue_admin_post_status_script');
add_action('admin_footer-edit.php', 'enqueue_admin_post_status_script');
