/**
 * Documentation Script
 */

jQuery(document).ready(function ($) {
    function filterDocuments() {
        var searchValue = $('#search').val().toUpperCase();
        var typeFilterValue = $('#document_type').val();

        $('.th-document-table tbody tr').each(function () {
            var $row = $(this);
            var nameText = $row.find('td:eq(1)').text().toUpperCase(); // Assuming the Name is in the second column
            var documentType = $row.data('documentType');
            var matchesName = nameText.includes(searchValue) || searchValue === "";
            var matchesType = typeFilterValue === "" || documentType === typeFilterValue;

            if (matchesName && matchesType) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    }

    $('#search').on('keyup', filterDocuments);
    $('#document_type').on('change', filterDocuments);
});
