/**
 * Tickets Script
 */

jQuery(document).ready(function($) {

    var page = 1; // Start on the first page

    function fetchTickets(shouldResetPage) {
        if (shouldResetPage) {
            page = 1; // Reset to the first page when filters change
        }

        var archive = $('#toggleArchived').is(':checked');
        var searchValue = $('#search').val();
        var statusValue = $('#ticket_status').val();
        var typeValue = $('#ticket_type').val();

        $.ajax({
            url: ajax_params.ajax_url,
            type: 'POST',
            dataType: 'json', // Ensuring we handle JSON correctly
            data: {
                action: 'fetch_tickets',
                archive: archive,
                page: page,
                user_id: ajax_params.user_id,
                search: searchValue,
                status: statusValue,
                type: typeValue
            },
            success: function(data) {
                $('#tickets-container').html(data.tickets);
                $('#ticket-pagination').html(data.pagination);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching tickets:", xhr.responseText);
            }
        });
    }

    $('#search').on('keyup', function() { fetchTickets(true); });
    $('#ticket_status').on('change', function() { fetchTickets(true); });
    $('#ticket_type').on('change', function() { fetchTickets(true); });
    $('#toggleArchived').on('change', function() { fetchTickets(true); });

    $(document).on('click', '.page-number', function(e) {
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
