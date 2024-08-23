jQuery(document).ready(function ($) {
    $('#thub-form').submit(function (e) {
        e.preventDefault(); // Stop the form from submitting normally
        var formData = new FormData(this); // Get the form data

        // Disable the submit button to prevent multiple submissions
        $(this).find('input[type="submit"]').prop('disabled', true);

        // Remove any existing notices
        $('.notice').remove();

        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                var noticeHtml = '';
                if (response.success) {
                    // Create the success message
                    noticeHtml = '<div class="notice notice-success">Thank you for your submission. We will get back to you soon.</div>';
                    // Reset the form to clear all input fields
                    document.getElementById('thub-form').reset();
                } else {
                    // Create the error message
                    noticeHtml = '<div class="notice notice-error">' + response.data + '</div>';
                }
                // Prepend the notice to the form
                $('#thub-form').prepend(noticeHtml);

                // Scroll to the notice
                $('html, body').animate({
                    scrollTop: $('.notice').offset().top - 50
                }, 500);

                // Re-enable the submit button
                $('#thub-form').find('input[type="submit"]').prop('disabled', false);
            },
            error: function (xhr, status, error) {
                // Create the error message
                var errorMsg = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data : 'There was a problem with your submission. Please try again.';
                var noticeHtml = '<div class="notice notice-error">' + errorMsg + '</div>';

                // Prepend the notice to the form
                $('#thub-form').prepend(noticeHtml);

                // Scroll to the notice
                $('html, body').animate({
                    scrollTop: $('.notice').offset().top - 50
                }, 500);

                // Re-enable the submit button
                $('#thub-form').find('input[type="submit"]').prop('disabled', false);
            }
        });
    });
});
