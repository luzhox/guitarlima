/** Find all elements with a data-module attribute and call the corresponding module, if it exists. */
function PageModules() {
  var modules = document.querySelectorAll('[data-module]')

  console.log('PageModules: Found', modules.length, 'modules');

  for (var i = 0; i < modules.length; i++) {
    var el = modules[i]
    var name = el.getAttribute('data-module')

    console.log('PageModules: Processing module:', name);

    try {
      // Require module script
      var Module = require('./' + name + '.js')
      console.log('PageModules: Successfully loaded module:', name);
    } catch (e) {
      console.log('PageModules: Failed to load module:', name, 'Error:', e.message);
      var Module = false
    }

    if (Module) {
      // Initialize the module
      console.log('PageModules: Initializing module:', name);
      new Module(el)
    }
  }
}

module.exports = PageModules
