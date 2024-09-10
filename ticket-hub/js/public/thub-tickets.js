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
        console.log('fetchTickets');
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
            user_id: $('.thub-ticket-table tr').children().length === 5 ? 0 : thub_public_vars.user_id,
            searchValue,
            statusValue,
            typeValue,
            nonce: thub_public_vars.nonce
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
