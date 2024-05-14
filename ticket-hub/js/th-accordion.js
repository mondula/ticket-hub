/**
 * Accordion Script
 */

jQuery(document).ready(function ($) {
    function toggleAccordion(element) {
        var $content = $(element).next('.th-accordion-content');
        if ($content.is(':visible')) {
            $content.slideUp();
            $(element).removeClass('active');
        } else {
            var $accordion = $(element).closest('.th-accordion');
            $accordion.find('.th-accordion-content').slideUp();
            $accordion.find('.th-accordion-title').removeClass('active');
            $content.slideDown();
            $(element).addClass('active');
        }
    }

    // Initialize each accordion and automatically open the first section
    $('.th-accordion').each(function () {
        var $firstAccordionTitle = $(this).find('.th-accordion-title').first();
        if ($firstAccordionTitle.length) {
            $firstAccordionTitle.addClass('active');
            $firstAccordionTitle.next('.th-accordion-content').slideDown();
        }
    });

    // Attach click event handler to all accordion titles within each .th-accordion
    $('.th-accordion .th-accordion-title').on('click', function () {
        toggleAccordion(this);
    });
});