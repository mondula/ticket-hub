/**
 * Admin post status script
 */


jQuery(document).ready(function ($) {
    // Get the archived text from localized variables
    var archivedText = tickethub_status_vars.archived_text;

    // Append the new status to the status selector in the edit post and quick edit screens
    $("select[name='post_status']").append("<option value='thub_archive'>" + archivedText + "</option>");

    // Check if the current post status is 'thub_archive' and update the selector
    if (tickethub_status_vars.post_status === 'thub_archive') {
        $("select[name='post_status']").val('archive');
        $('#post-status-display').text(archivedText);
    }

    // Add the status to the quick edit
    $(".editinline").click(function () {
        var $row = $(this).closest('tr');
        var $status = $row.find('.status').text();
        if (archivedText === $status) {
            $('select[name="_status"]', '.inline-edit-row').val('thub_archive');
        }
    });
});
