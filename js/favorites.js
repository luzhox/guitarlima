jQuery(document).ready(function ($) {
  'use strict'

  // Handle favorite button clicks
  $(document).on('click', '.favorite-btn', function (e) {
    e.preventDefault()

    var $button = $(this)
    var postId = $button.data('post-id')
    var isFavorited = $button.data('favorited') === 'true'

    if (isFavorited) {
      removeFavorite($button, postId)
    } else {
      addFavorite($button, postId)
    }
  })

  // Handle heart favorite button clicks
  $(document).on('click', '.favorite-heart-btn', function (e) {
    e.preventDefault()

    var $button = $(this)
    var postId = $button.data('post-id')
    var isFavorited = $button.data('favorited') === 'true'

    if (isFavorited) {
      removeFavoriteHeart($button, postId)
    } else {
      addFavoriteHeart($button, postId)
    }
  })

  // Handle remove favorite button clicks in favorites list
  $(document).on('click', '.remove-favorite-btn', function (e) {
    e.preventDefault()

    var $button = $(this)
    var $item = $button.closest('.favorite-item')
    var postId = $button.data('post-id')

    removeFavoriteFromList($button, $item, postId)
  })

  // Add favorite function
  function addFavorite($button, postId) {
    $.ajax({
      url: gl_favorites_ajax.ajax_url,
      type: 'POST',
      data: {
        action: 'gl_add_favorite',
        post_id: postId,
        nonce: gl_favorites_ajax.nonce,
      },
      beforeSend: function () {
        $button.prop('disabled', true).addClass('loading')
      },
      success: function (response) {
        if (response.success) {
          $button.data('favorited', 'true')
          $button.addClass('favorited')
          $button.find('.favorite-text').text('Eliminar de favoritos')
          $button.find('.favorite-count').text(response.data.count)

          // Show success message
          showMessage(response.data.message || 'Added to favorites!', 'success')
        } else {
          showMessage(response.data || 'Error adding to favorites', 'error')
          console.error('Error en añadir a favoritos:', response.data)
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', status, error)
        console.error('Response:', xhr.responseText)
        showMessage('Network error. Please try again.', 'error')
      },
      complete: function () {
        $button.prop('disabled', false).removeClass('loading')
      },
    })
  }

  // Remove favorite function
  function removeFavorite($button, postId) {
    $.ajax({
      url: gl_favorites_ajax.ajax_url,
      type: 'POST',
      data: {
        action: 'gl_remove_favorite',
        post_id: postId,
        nonce: gl_favorites_ajax.nonce,
      },
      beforeSend: function () {
        $button.prop('disabled', true).addClass('loading')
      },
      success: function (response) {
        if (response.success) {
          $button.data('favorited', 'false')
          $button.removeClass('favorited')
          $button.find('.favorite-text').text('Añadir a favoritos')
          $button.find('.favorite-count').text(response.data.count)

          // Show success message
          showMessage('Eliminado de favoritos', 'success')
        } else {
          showMessage(response.data || 'Error removing from favorites', 'error')
        }
      },
      error: function () {
        showMessage('Network error. Please try again.', 'error')
      },
      complete: function () {
        $button.prop('disabled', false).removeClass('loading')
      },
    })
  }

  // Remove favorite from list function
  function removeFavoriteFromList($button, $item, postId) {
    $.ajax({
      url: gl_favorites_ajax.ajax_url,
      type: 'POST',
      data: {
        action: 'gl_remove_favorite',
        post_id: postId,
        nonce: gl_favorites_ajax.nonce,
      },
      beforeSend: function () {
        $button.prop('disabled', true).addClass('loading')
      },
      success: function (response) {
        if (response.success) {
          // Animate removal
          $item.fadeOut(300, function () {
            $(this).remove()

            // Check if list is empty
            if ($('.favorite-item').length === 0) {
              $('.favorites-list').html('<p>No favorites found.</p>')
            }
          })

          // Update any favorite buttons for this post on the page
          $('.favorite-btn[data-post-id="' + postId + '"]').each(function () {
            var $btn = $(this)
            $btn.data('favorited', 'false')
            $btn.removeClass('favorited')
            $btn.find('.favorite-text').text('Add to Favorites')
            $btn.find('.favorite-count').text(response.data.count)
          })

          showMessage('Removed from favorites!', 'success')
        } else {
          showMessage(response.data || 'Error removing from favorites', 'error')
        }
      },
      error: function () {
        showMessage('Network error. Please try again.', 'error')
      },
      complete: function () {
        $button.prop('disabled', false).removeClass('loading')
      },
    })
  }

  // Load favorites for a specific post type
  function loadFavorites(postType) {
    $.ajax({
      url: gl_favorites_ajax.ajax_url,
      type: 'POST',
      data: {
        action: 'gl_get_favorites',
        post_type: postType,
      },
      success: function (response) {
        if (response.success) {
          displayFavorites(response.data)
        } else {
          showMessage(response.data || 'Error loading favorites', 'error')
        }
      },
      error: function () {
        showMessage('Network error. Please try again.', 'error')
      },
    })
  }

  // Display favorites in the container
  function displayFavorites(favorites) {
    var $container = $('.mis-favoritos__container')

    if (favorites.length === 0) {
      $container.html('<p class="no-favorites">No tienes favoritos guardados.</p>')
      return
    }

    // Show loading state
    $container.html('<div class="favorites-loading">Cargando favoritos...</div>')

    // Create promises for all post data requests
    var promises = favorites.map(function (favorite) {
      return getPostData(favorite.post_id)
    })

    // Wait for all post data to load
    Promise.all(promises)
      .then(function (responses) {
        var html = '<div class="favorites-grid">'

        responses.forEach(function (response, index) {
          if (response.success) {
            var post = response.data
            var favorite = favorites[index]

            html += '<div class="favorite-item" data-post-id="' + favorite.post_id + '">'
            html += '<div class="favorite-item__image">'
            if (post.thumbnail) {
              html += '<img src="' + post.thumbnail + '" alt="' + post.title + '">'
            }
            html += '</div>'
            html += '<div class="favorite-item__content">'
            html += '<h3><a href="' + post.url + '">' + post.title + '</a></h3>'
            html += '<p>' + post.excerpt + '</p>'
            html += '<div class="favorite-item__actions">'
            html += '<a href="' + post.url + '" class="btn__primary">Reproducir</a>'
            html += '<button class="btn__primary--border remove-favorite-btn" data-post-id="' + favorite.post_id + '">Eliminar de favoritos</button>'
            html += '</div>'
            html += '</div>'
            html += '</div>'
          }
        })

        html += '</div>'
        $container.html(html)
      })
      .catch(function (error) {
        $container.html('<p class="favorites-error">Error al cargar los favoritos. Por favor, intenta de nuevo.</p>')
      })
  }

  // Get post data via AJAX
  function getPostData(postId) {
    return $.ajax({
      url: gl_favorites_ajax.ajax_url,
      type: 'POST',
      data: {
        action: 'gl_get_post_data',
        post_id: postId,
      },
    })
  }

  // Show message function
  function showMessage(message, type) {
    var $message = $('<div class="favorites-message favorites-message--' + type + '">' + message + '</div>')

    // Remove existing messages
    $('.favorites-message').remove()

    // Add new message
    $('body').append($message)

    // Show message
    $message.fadeIn(300)

    // Auto hide after 3 seconds
    setTimeout(function () {
      $message.fadeOut(300, function () {
        $(this).remove()
      })
    }, 3000)
  }

  // Initialize favorites page
  if ($('.mis-favoritos').length > 0) {
    // Load all favorites when page loads
    loadFavorites()

    // Handle filter clicks
    $(document).on('click', '.filter-btn', function () {
      var $btn = $(this)
      var postType = $btn.data('type')

      // Update active state
      $('.filter-btn').removeClass('active')
      $btn.addClass('active')

      // Load filtered favorites
      loadFavorites(postType)
    })
  }

  // Add favorite heart function
  function addFavoriteHeart($button, postId) {
    $.ajax({
      url: gl_favorites_ajax.ajax_url,
      type: 'POST',
      data: {
        action: 'gl_add_favorite',
        post_id: postId,
        nonce: gl_favorites_ajax.nonce,
      },
      beforeSend: function () {
        $button.prop('disabled', true).addClass('loading')
      },
      success: function (response) {
        if (response.success) {
          $button.data('favorited', 'true')
          $button.addClass('favorited')
          $button.attr('title', 'Quitar de favoritos')

          // Update heart icon to filled state
          var $svg = $button.find('svg')
          $svg.addClass('favorited')

          // Update any other favorite buttons for this post on the page
          $('.favorite-btn[data-post-id="' + postId + '"]').each(function () {
            var $btn = $(this)
            $btn.data('favorited', 'true')
            $btn.addClass('favorited')
            $btn.find('.favorite-text').text('Eliminar de favoritos')
            $btn.find('.favorite-count').text(response.data.count)
          })

          // Show success message
          showMessage('Agregado a favoritos', 'success')
        } else {
          showMessage(response.data || 'Error al agregar a favoritos', 'error')
          console.error('Error en añadir a favoritos:', response.data)
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', status, error)
        console.error('Response:', xhr.responseText)
        showMessage('Error de red. Por favor, intenta de nuevo.', 'error')
      },
      complete: function () {
        $button.prop('disabled', false).removeClass('loading')
      },
    })
  }

  // Remove favorite heart function
  function removeFavoriteHeart($button, postId) {
    $.ajax({
      url: gl_favorites_ajax.ajax_url,
      type: 'POST',
      data: {
        action: 'gl_remove_favorite',
        post_id: postId,
        nonce: gl_favorites_ajax.nonce,
      },
      beforeSend: function () {
        $button.prop('disabled', true).addClass('loading')
      },
      success: function (response) {
        if (response.success) {
          $button.data('favorited', 'false')
          $button.removeClass('favorited')
          $button.attr('title', 'Agregar a favoritos')

          // Update heart icon to outline state
          var $svg = $button.find('svg')
          $svg.removeClass('favorited')

          // Update any other favorite buttons for this post on the page
          $('.favorite-btn[data-post-id="' + postId + '"]').each(function () {
            var $btn = $(this)
            $btn.data('favorited', 'false')
            $btn.removeClass('favorited')
            $btn.find('.favorite-text').text('Añadir a favoritos')
            $btn.find('.favorite-count').text(response.data.count)
          })

          // Show success message
          showMessage('Eliminado de favoritos', 'success')
        } else {
          showMessage(response.data || 'Error al eliminar de favoritos', 'error')
        }
      },
      error: function () {
        showMessage('Error de red. Por favor, intenta de nuevo.', 'error')
      },
      complete: function () {
        $button.prop('disabled', false).removeClass('loading')
      },
    })
  }
})
