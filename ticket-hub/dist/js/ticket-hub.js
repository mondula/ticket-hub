/**
 * Accordion Script
 */

jQuery(document).ready(function ($) {

    function toggleAccordion(element) {
        var $content = $(element).next('.thub-accordion-content');
        if ($content.is(':visible')) {
            $content.slideUp();
            $(element).removeClass('active');
        } else {
            var $accordion = $(element).closest('.thub-accordion');
            $accordion.find('.thub-accordion-content').slideUp();
            $accordion.find('.thub-accordion-title').removeClass('active');
            $content.slideDown();
            $(element).addClass('active');
        }
    }

    // Initialize each accordion and automatically open the first section
    $('.thub-accordion').each(function () {
        var $firstAccordionTitle = $(this).find('.thub-accordion-title').first();
        if ($firstAccordionTitle.length) {
            $firstAccordionTitle.addClass('active');
            $firstAccordionTitle.next('.thub-accordion-content').slideDown();
        }
    });

    // Attach click event handler to all accordion titles within each .thub-accordion
    $('.thub-accordion .thub-accordion-title').on('click', function () {
        toggleAccordion(this);
    });
});
/**
 * Documentation Script
 */

jQuery(document).ready(function ($) {

    function filterDocuments() {
        var searchValue = $('#thub-doc-search').val().toUpperCase();
        var typeFilterValue = $('#thub-document-type').val();

        $('.thub-document-table tbody tr').each(function () {
            var $row = $(this);
            var nameText = $row.find('td:eq(1)>div').text().toUpperCase(); // Assuming the Name is in the second column
            // console.log(nameText);
            var documentType = $row.data('document-type'); // Corrected to match the data attribute correctly
            var matchesName = nameText.includes(searchValue);
            var matchesType = typeFilterValue === "" || documentType === typeFilterValue;

            if (matchesName && matchesType) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    }

    $('#thub-doc-search').on('keyup', filterDocuments);
    $('#thub-document-type').on('change', filterDocuments);
});


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

/**
 * Lightbox Script
 */

jQuery(document).ready(function ($) {
    // Create the lightbox elements
    var lightboxBackdrop = $('<div/>', { 'class': 'thub-lightbox-backdrop' }).appendTo('body');
    var lightboxContent = $('<div/>', { 'class': 'thub-lightbox-content' }).appendTo(lightboxBackdrop);
    var lightboxImg = $('<img/>').appendTo(lightboxContent);

    // Function to open the lightbox
    function openLightbox(src) {
        lightboxImg.attr('src', src);
        lightboxBackdrop.show().css('display', 'flex');
        $('body').css('overflow', 'hidden'); // Prevent background scrolling
    }

    // Function to close the lightbox
    function closeLightbox() {
        lightboxBackdrop.hide();
        $('body').css('overflow', ''); // Re-enable background scrolling
    }

    // Event listener for image click
    $('.thub-lightbox-trigger').click(function (e) {
        e.preventDefault();
        openLightbox(this.href);
    });

    // Event listener for closing the lightbox when clicking outside the image
    lightboxBackdrop.click(function (e) {
        if (e.target !== lightboxImg[0]) {
            closeLightbox();
        }
    });
});

/**
 * Tickets Script
 */

jQuery(document).ready(function ($) {
    // Check if the ticket table doesn't exist
    if ($('.thub-ticket-table').length === 0) {
        return; // Exit early if the ticket table is not present
    }

    var page = 1; // Start on the first page

    function fetchTickets(shouldResetPage) {
        // console.log('fetchTickets');
        if (shouldResetPage) {
            page = 1; // Reset to the first page when filters change
        }

        var isArchive = $('#thub-toggle-archive').is(':checked');
        var searchValue = $('#thub-ticket-search').val();
        var statusValue = $('#thub-ticket-status').val();
        var typeValue = $('#thub-ticket-type').val();

        let data = {
            action: 'fetch_tickets',
            isArchive,
            page,
            user_id: $('.thub-profile-head').length === 0 ? 0 : thub_public_vars.user_id,
            searchValue,
            statusValue,
            typeValue,
            nonce: thub_public_vars.nonces.fetch_tickets
        };
        // console.log(data);

        $.ajax({
            url: thub_public_vars.ajax_url,
            type: 'POST',
            dataType: 'json',
            data,
            success: function (data) {
                // console.log(data);
                $('#thub-tickets-container').html(data.tickets);
                $('#thub-ticket-pagination').html(data.pagination);
            },
            error: function (xhr, status, error) {
                console.error("Error fetching tickets:", xhr.responseText);
            }
        });
    }

    $('#thub-ticket-search').on('keyup', function () { fetchTickets(true); });
    $('#thub-ticket-status').on('change', function () { fetchTickets(true); });
    $('#thub-ticket-type').on('change', function () { fetchTickets(true); });
    $('#thub-toggle-archive').on('change', function () { fetchTickets(true); });

    $(document).on('click', '.thub-page-number', function (e) {
        // Check if the inner HTML is '...'
        if ($(this).text().trim() === '…') return;

        e.preventDefault();
        var href = $(this).find('a').attr('href');
        var match = href.match(/page=(\d+)/);
        if (match) {
            page = parseInt(match[1], 10);
            fetchTickets(false);
        }
    });

    // Initial load
    fetchTickets(false);
});
