/**
 * Course PerCat Module - jQuery Version
 * Handles category filtering for course items with smooth animations
 */
(function($) {
  'use strict';

  function CoursePerCat($el) {
    this.$el = $el;
    this.$filterButtons = $el.find('.course-percat__filter-btn');
    this.$items = $el.find('.course-percat__item');
    this.animationDuration = 250;
    this.isAnimating = false;

    // Validate required elements exist
    if (this.$filterButtons.length === 0) {
      console.warn('CoursePerCat: No filter buttons found');
      return;
    }

    if (this.$items.length === 0) {
      console.warn('CoursePerCat: No course items found');
      return;
    }



    // Initialize immediately
    this.init();
  }

  CoursePerCat.prototype.init = function() {
    this.bindEvents();
    this.setupInitialState();
  };

  CoursePerCat.prototype.setupInitialState = function() {
    // Set initial active state to "all" button if it exists
    const $allButton = this.$filterButtons.filter('[data-category="all"]');
    if ($allButton.length > 0) {
      this.updateActiveButton($allButton);
    } else if (this.$filterButtons.length > 0) {
      // If no "all" button, activate the first one
      this.updateActiveButton(this.$filterButtons.first());
    }
  };

  CoursePerCat.prototype.bindEvents = function() {
    const self = this;


    this.$filterButtons.on('click', function(e) {
      e.preventDefault();



      const $button = $(this);
      const category = $button.data('category');


      if (!category) {
        console.warn('CoursePerCat: Button missing data-category attribute');
        return;
      }

      self.filterItems(category);
      self.updateActiveButton($button);
    });

    // Add keyboard navigation support
    this.$filterButtons.on('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        $(this).trigger('click');
      }
    });
  };

  CoursePerCat.prototype.filterItems = function(category) {
    const self = this;


    this.isAnimating = true;

    // Use jQuery's promise-based animation with improved effects
    const animationPromises = [];

    this.$items.each(function(index) {
      const $item = $(this);
      const hasCategory = category === 'all' || $item.hasClass('category-' + category);


      if (hasCategory) {
        // Show item with simple but effective animation
        $item
          .show()
          .css({
            'opacity': 0,
            'transform': 'scale(0.7)'
          })
          .delay(index * 50) // Stagger effect
          .animate({
            'opacity': 1
          }, {
            duration: self.animationDuration,
            step: function(now) {
              const scale = 0.7 + (now * 0.3);
              $item.css('transform', 'scale(' + scale + ')');
            },
            complete: function() {
              $item.css('transform', 'scale(1)');
            }
          });
      } else {
        // Hide item with simple animation
        const hidePromise = $item
          .animate({
            'opacity': 0
          }, {
            duration: self.animationDuration,
            step: function(now) {
              const scale = 1 - (now * 0.2);
              $item.css('transform', 'scale(' + scale + ')');
            }
          })
          .promise()
          .then(function() {
            $item.hide().css('transform', 'scale(1)');
          });

        animationPromises.push(hidePromise);
      }
    });

    // Wait for all animations to complete
    $.when.apply($, animationPromises).always(function() {
      self.isAnimating = false;
    });
  };

  CoursePerCat.prototype.updateActiveButton = function($activeButton) {
    // Remove active class from all buttons
    this.$filterButtons.removeClass('active');

    // Add active class to clicked button with simple animation
    $activeButton
      .addClass('active')
      .css({
        'transform': 'scale(1.05)',
        'transition': 'transform 0.2s ease-out'
      })
      .delay(100)
      .queue(function() {
        $(this).css('transform', 'scale(1)');
        $(this).dequeue();
      });

  };

  // Add static method for easy initialization
  CoursePerCat.init = function(selector) {
    const $elements = $(selector);
    const instances = [];

    $elements.each(function() {
      instances.push(new CoursePerCat($(this)));
    });

    return instances;
  };

  // Expose to global scope for use in other scripts
  window.CoursePerCat = CoursePerCat;

  // Auto-initialize if data attribute is present
  $(document).ready(function() {
    $('[data-course-percat]').each(function() {
      new CoursePerCat($(this));
    });
  });

})(jQuery);

// CommonJS export for Node.js environments
if (typeof module !== 'undefined' && module.exports) {
  module.exports = CoursePerCat;
}