<?php
/*
Plugin Name: TicketHub
Description: Streamline your support system with TicketHub, a powerful and user-friendly plugin for managing tickets, FAQs, and documentation efficiently.
Version:     1.0
Author:      Mondula GmbH
Author URI:  https://mondula.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: tickethub
Requires at least: 6.0
Tested up to: 6.5.3
Tags: tickets, support, faq, documentation, change log
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

mondula_require_files(
    'post-types',
    [
        'th-ticket-pt.php',
        'th-change-pt.php',
        'th-faq-pt.php',
        'th-document-pt.php'
    ]
);


add_action('init', function () {
    load_plugin_textdomain('tickethub', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    mondula_require_files('includes', ['th-page-settings.php', 'th-ticket-tag-subpage.php']);
    mondula_require_files('shortcodes', ['th-changelog-sc.php', 'th-documentation-sc.php', 'th-faqs-sc.php', 'th-form-sc.php', 'th-ticket-sc.php', 'th-tickets-sc.php', 'th-profile-sc.php']);

    // Define custom capabilities for submitting and commenting on tickets.
    $capabilities = array(
        'submit_tickets' => true,
        'comment_tickets' => true,
    );

    register_post_status('th_archive', array(
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
    add_role('th_ticket_creator', __('Ticket Creator', 'tickethub'), ['submit_tickets' => true, 'comment_tickets' => true]);
});

register_deactivation_hook(__FILE__, function () {
    $users = get_users(array('role' => 'th_ticket_creator'));
    foreach ($users as $user) {
        $user->set_role('subscriber');  // Change 'subscriber' to whatever default you consider appropriate
    }

    remove_role('th_ticket_creator');
});

add_action('after_setup_theme', function () {
    if (in_array('th_ticket_creator', (array) wp_get_current_user()->roles)) {
        show_admin_bar(false);
    }
});

function enqueue_admin_post_status_script()
{
    global $post;
    if ($post->post_type == 'th_ticket') {
        $archived_text = esc_js(__('Archived', 'tickethub'));
?>
        <script>
            jQuery(document).ready(function($) {
                // Append the new status to the status selector in the edit post and quick edit screens
                $("select[name='post_status']").append("<option value='th_archive'><?php echo $archived_text; ?></option>");

                // Check if the current post status is 'th_archive' and update the selector
                <?php if ('th_archive' == $post->post_status) : ?>
                    $("select[name='post_status']").val('archive');
                    $('#post-status-display').text('<?php echo $archived_text; ?>');
                <?php endif; ?>

                // Add the status to the quick edit
                $(".editinline").click(function() {
                    var $row = $(this).closest('tr');
                    var $status = $row.find('.status').text();
                    if ('<?php echo $archived_text; ?>' === $status) {
                        $('select[name="_status"]', '.inline-edit-row').val('th_archive');
                    }
                });
            });
        </script>
<?php
    }
}
add_action('admin_footer-post.php', 'enqueue_admin_post_status_script');
add_action('admin_footer-edit.php', 'enqueue_admin_post_status_script');

add_action('admin_enqueue_scripts', function () {
    // Use get_plugin_data() if you need versioning based on your plugin version
    $version = '1.0.0';

    // Properly form the URL to the stylesheet
    $admin_style_url = plugins_url('css/th-admin-style.css', __FILE__);

    // Enqueue the stylesheet
    wp_enqueue_style('th-admin-style', $admin_style_url, array(), $version);
});
?>