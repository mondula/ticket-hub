/**
 * Tickets Script
 */

jQuery(document).ready(function($) {
    function filterTickets() {
        var searchValue = $('#search').val().toUpperCase();
        var statusValue = $('#ticket_status').val();
        var typeValue = $('#ticket_type').val();

        $('.ticket-table tbody tr').each(function() {
            var row = $(this);
            var idText = row.find('td:eq(0)').text().toUpperCase();
            var statusText = row.find('td:eq(1)').text().toUpperCase();
            var typeText = row.find('td:eq(2)').text().toUpperCase();

            var matchesID = idText.indexOf(searchValue) > -1 || searchValue === "";
            var matchesStatus = statusValue === "" || statusText === statusValue.toUpperCase();
            var matchesType = typeValue === "" || typeText === typeValue.toUpperCase();

            if (matchesID && matchesStatus && matchesType) {
                row.show();
            } else {
                row.hide();
            }
        });
    }

    $('#search').on('keyup', filterTickets);
    $('#ticket_status').on('change', filterTickets);
    $('#ticket_type').on('change', filterTickets);
});
