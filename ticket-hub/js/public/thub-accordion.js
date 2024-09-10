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