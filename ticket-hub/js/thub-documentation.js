/**
 * Documentation Script
 */

jQuery(document).ready(function ($) {
    function filterDocuments() {
        var searchValue = $('#search').val().toUpperCase();
        var typeFilterValue = $('#thub-document-type').val();

        $('.thub-document-table tbody tr').each(function () {
            var $row = $(this);
            var nameText = $row.find('td:eq(1)>div').text().toUpperCase(); // Assuming the Name is in the second column
            console.log(nameText);
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

    $('#search').on('keyup', filterDocuments);
    $('#thub-document-type').on('change', filterDocuments);
});

