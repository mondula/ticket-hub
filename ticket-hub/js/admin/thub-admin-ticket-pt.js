/**
 * Admin ticket post type script
 */

jQuery(document).ready(function ($) {
    // console.log('thub_admin_vars', thub_admin_vars);
    // Get the archived text from localized variables
    var archivedText = thub_admin_vars.archived_text;

    // Append the new status to the status selector in the edit post and quick edit screens
    $("select[name='post_status']").append("<option value='thub_archive'>" + archivedText + "</option>");

    // Check if the current post status is 'thub_archive' and update the selector and display
    if (thub_admin_vars.post_status === 'thub_archive') {
        $("select[name='post_status']").val('thub_archive');
        $('#post-status-display').text(archivedText);
    }
});
