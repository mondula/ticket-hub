/**
 * Admin document post type script
 */

jQuery(document).ready(function ($) {

    $('#thub-document-type').change(function () {
        if ($(this).val() === 'File') {
            $('#thub-file-upload-section').show();
            $('#thub-link-section').hide();
        } else {
            $('#thub-file-upload-section').hide();
            $('#thub-link-section').show();
        }
    });

    $('#thub-upload-file-button').click(function (e) {
        e.preventDefault();
        var fileFrame;

        if (fileFrame) {
            fileFrame.open();
            return;
        }

        fileFrame = wp.media({
            title: 'Select or Upload a File',
            button: {
                text: 'Use this file'
            },
            multiple: false
        });

        fileFrame.on('select', function () {
            var attachment = fileFrame.state().get('selection').first().toJSON();
            $('#thub-document-file-id').val(attachment.id);
            $('#thub-file-name').text(attachment.title);
        });

        fileFrame.open();
    });
});