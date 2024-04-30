jQuery(document).ready(function($) {
    $('#ticket-form').submit(function(e) {
        e.preventDefault(); // Stop the form from submitting normally
        var formData = new FormData(this); // Get the form data

        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                // Display the success message
                $('#ticket-form').prepend('<div class="notice notice-success">Thank you for your submission. We will get back to you soon.</div>');
            },
            error: function() {
                // Display the error message
                $('#ticket-form').prepend('<div class="notice notice-error">There was a problem with your submission. Please try again.</div>');
            }
        });
    });
});
