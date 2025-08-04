// Test file for course-percat filtering
document.addEventListener('DOMContentLoaded', function() {
  console.log('Test filter script loaded');

  // Find course-percat elements
  var coursePerCatElements = document.querySelectorAll('[data-module="course-percat"]');
  console.log('Found course-percat elements:', coursePerCatElements.length);

  coursePerCatElements.forEach(function(element, index) {
    console.log('Processing element', index);

    var filterButtons = element.querySelectorAll('.course-percat__filter-btn');
    var items = element.querySelectorAll('.course-percat__item');

    console.log('Element', index, 'has', filterButtons.length, 'filter buttons and', items.length, 'items');

    // Add click handlers
    filterButtons.forEach(function(button) {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        var category = this.dataset.category;
        console.log('Button clicked:', category);

        // Update active button
        filterButtons.forEach(function(btn) {
          btn.classList.remove('active');
        });
        this.classList.add('active');

        // Filter items
        items.forEach(function(item) {
          if (category === 'all') {
            item.style.display = 'block';
            setTimeout(function() {
              item.style.opacity = '1';
              item.style.transform = 'scale(1)';
            }, 10);
          } else {
            var hasCategory = item.classList.contains('category-' + category);
            console.log('Item has category', category, ':', hasCategory);

            if (hasCategory) {
              item.style.display = 'block';
              setTimeout(function() {
                item.style.opacity = '1';
                item.style.transform = 'scale(1)';
              }, 10);
            } else {
              item.style.opacity = '0';
              item.style.transform = 'scale(0.8)';
              setTimeout(function() {
                item.style.display = 'none';
              }, 300);
            }
          }
        });
      });
    });
  });
});