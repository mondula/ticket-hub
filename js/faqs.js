/**
 * FAQs Script
 */

jQuery(document).ready(function($) {
    function toggleAccordion(element) {
        var $content = $(element).next('.accordion-content');
        if ($content.css('display') === 'block') {
            $content.hide();
            $(element).removeClass('active');
        } else {
            // $('.accordion-content').hide();
            // $('.accordion-title').removeClass('active');
            $content.show();
            $(element).addClass('active');
        }
    }

    // Automatically open the first accordion section when the page loads
    var $firstAccordionTitle = $('.accordion-title').first();
    if ($firstAccordionTitle.length) {
        $firstAccordionTitle.addClass('active');
        $firstAccordionTitle.next('.accordion-content').show();
    }

    // Attach click event handler to all accordion titles
    $('.accordion-title').on('click', function() {
        toggleAccordion(this);
    });
});