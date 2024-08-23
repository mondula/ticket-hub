/**
 * Tickets Script
 */

jQuery(document).ready(function ($) {

    var page = 1; // Start on the first page

    function fetchTickets(shouldResetPage) {
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
            user_id: ajax_params.user_id,
            searchValue,
            statusValue,
            typeValue,
            nonce: ajax_params.nonce // Add the nonce here
        };
        console.log(data);

        $.ajax({
            url: ajax_params.ajax_url,
            type: 'POST',
            dataType: 'json', // Ensuring we handle JSON correctly
            data,
            success: function (data) {
                console.log(data);
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
