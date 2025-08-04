require('./page-modules')
require('./owl.carousel')
require('./template')

// Direct initialization for course-percat module
document.addEventListener('DOMContentLoaded', function() {
  const coursePerCatElements = document.querySelectorAll('[data-module="course-percat"]');
  console.log('Direct init: Found', coursePerCatElements.length, 'course-percat elements');

  if (coursePerCatElements.length > 0) {
    try {
      const CoursePerCat = require('./course-percat.js');
      coursePerCatElements.forEach(function(element) {
        // Convert DOM element to jQuery object
        new CoursePerCat($(element));
      });
    } catch (e) {
      console.log('Failed to load course-percat module:', e.message);
    }
  }
});

import '../styles/sass/style.scss'
