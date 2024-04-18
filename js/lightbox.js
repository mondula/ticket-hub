/**
 * Lightbox Script
 */

jQuery(document).ready(function($) {
  // Create the lightbox elements
  var lightboxBackdrop = $('<div/>', { 'class': 'lightbox-backdrop' }).appendTo('body');
  var lightboxContent = $('<div/>', { 'class': 'lightbox-content' }).appendTo(lightboxBackdrop);
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
  $('.lightbox-trigger').click(function(e) {
      e.preventDefault();
      openLightbox(this.href);
  });

  // Event listener for closing the lightbox when clicking outside the image
  lightboxBackdrop.click(function(e) {
      if (e.target !== lightboxImg[0]) {
          closeLightbox();
      }
  });
});
