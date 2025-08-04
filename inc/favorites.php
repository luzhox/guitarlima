<?php
/**
 * WordPress Favorites System
 * Handles user favorites functionality
 */

// Create favorites table on theme activation
function gl_create_favorites_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_favorites';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        post_id bigint(20) NOT NULL,
        post_type varchar(50) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_post (user_id, post_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Hook to create table on theme activation
add_action('after_switch_theme', 'gl_create_favorites_table');

// Also ensure table exists on theme load
add_action('init', 'gl_ensure_favorites_table');

// Function to ensure favorites table exists
function gl_ensure_favorites_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_favorites';

    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        gl_create_favorites_table();
    }
}

// Add favorite
function gl_add_favorite($user_id, $post_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_favorites';

    // Check if table exists, if not create it
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        gl_create_favorites_table();
    }

    $post_type = get_post_type($post_id);

    // Check if already favorited
    if (gl_is_favorited($user_id, $post_id)) {
        return true; // Already favorited, consider it a success
    }

    $result = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'post_id' => $post_id,
            'post_type' => $post_type
        ),
        array('%d', '%d', '%s')
    );

    // Debug logging
    if ($result === false) {
        error_log('Favorites Error: Failed to insert favorite. User: ' . $user_id . ', Post: ' . $post_id . ', Error: ' . $wpdb->last_error);
    }

    return $result !== false;
}

// Remove favorite
function gl_remove_favorite($user_id, $post_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_favorites';

    $result = $wpdb->delete(
        $table_name,
        array(
            'user_id' => $user_id,
            'post_id' => $post_id
        ),
        array('%d', '%d')
    );

    return $result !== false;
}

// Check if post is favorited by user
function gl_is_favorited($user_id, $post_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_favorites';

    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE user_id = %d AND post_id = %d",
        $user_id,
        $post_id
    ));

    return $result !== null;
}

// Get user favorites
function gl_get_user_favorites($user_id, $post_type = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_favorites';

    $where_clause = "WHERE user_id = %d";
    $params = array($user_id);

    if ($post_type) {
        $where_clause .= " AND post_type = %s";
        $params[] = $post_type;
    }

    $query = $wpdb->prepare(
        "SELECT post_id, post_type FROM $table_name $where_clause ORDER BY created_at DESC",
        $params
    );

    return $wpdb->get_results($query);
}

// Get favorite count for a post
function gl_get_favorite_count($post_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_favorites';

    return $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE post_id = %d",
        $post_id
    ));
}

// AJAX handler for adding favorite
function gl_ajax_add_favorite() {
    // Check nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'gl_favorites_nonce')) {
        wp_send_json_error('Security check failed');
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }

    $user_id = get_current_user_id();
    $post_id = intval($_POST['post_id']);

    if (!$post_id || !get_post($post_id)) {
        wp_send_json_error('Invalid post ID: ' . $post_id);
    }

    // Check if already favorited
    if (gl_is_favorited($user_id, $post_id)) {
        wp_send_json_success(array(
            'message' => 'Already in favorites',
            'count' => gl_get_favorite_count($post_id)
        ));
    }

    $result = gl_add_favorite($user_id, $post_id);

    if ($result) {
        wp_send_json_success(array(
            'message' => 'Added to favorites',
            'count' => gl_get_favorite_count($post_id)
        ));
    } else {
        global $wpdb;
        $error_message = 'Failed to add to favorites';
        if ($wpdb->last_error) {
            $error_message .= ' - Database error: ' . $wpdb->last_error;
        }
        wp_send_json_error($error_message);
    }
}

// AJAX handler for removing favorite
function gl_ajax_remove_favorite() {
    // Check nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'gl_favorites_nonce')) {
        wp_die('Security check failed');
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }

    $user_id = get_current_user_id();
    $post_id = intval($_POST['post_id']);

    if (!$post_id) {
        wp_send_json_error('Invalid post ID');
    }

    $result = gl_remove_favorite($user_id, $post_id);

    if ($result) {
        wp_send_json_success(array(
            'message' => 'Removed from favorites',
            'count' => gl_get_favorite_count($post_id)
        ));
    } else {
        wp_send_json_error('Failed to remove from favorites');
    }
}

// AJAX handler for getting user favorites
function gl_ajax_get_favorites() {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }

    $user_id = get_current_user_id();
    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : null;

    $favorites = gl_get_user_favorites($user_id, $post_type);

    wp_send_json_success($favorites);
}

// AJAX handler for getting post data
function gl_ajax_get_post_data() {
    $post_id = intval($_POST['post_id']);

    if (!$post_id || !get_post($post_id)) {
        wp_send_json_error('Invalid post ID');
    }

    $post = get_post($post_id);
    $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');

    $post_data = array(
        'id' => $post_id,
        'title' => get_the_title($post_id),
        'url' => get_permalink($post_id),
        'excerpt' => wp_trim_words(get_the_excerpt($post_id), 20),
        'thumbnail' => $thumbnail ? $thumbnail : '',
        'post_type' => get_post_type($post_id)
    );

    wp_send_json_success($post_data);
}

// Register AJAX actions
add_action('wp_ajax_gl_add_favorite', 'gl_ajax_add_favorite');
add_action('wp_ajax_gl_remove_favorite', 'gl_ajax_remove_favorite');
add_action('wp_ajax_gl_get_favorites', 'gl_ajax_get_favorites');
add_action('wp_ajax_gl_get_post_data', 'gl_ajax_get_post_data');
add_action('wp_ajax_nopriv_gl_get_post_data', 'gl_ajax_get_post_data');

// Enqueue scripts and styles
function gl_enqueue_favorites_scripts() {
    wp_enqueue_script('gl-favorites-js', get_template_directory_uri() . '/js/favorites.js', array('jquery'), '1.0', true);

    // Localize script with AJAX URL and nonce
    wp_localize_script('gl-favorites-js', 'gl_favorites_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('gl_favorites_nonce')
    ));
}

add_action('wp_enqueue_scripts', 'gl_enqueue_favorites_scripts');

// Shortcode to display favorite button
function gl_favorite_button_shortcode($atts) {
    $atts = shortcode_atts(array(
        'post_id' => get_the_ID(),
        'text_add' => 'Add to Favorites',
        'text_remove' => 'Remove from Favorites',
        'class' => 'favorite-btn'
    ), $atts);

    // if (!is_user_logged_in()) {
    //     return '<a href="' . wp_login_url(get_permalink()) . '" class="' . esc_attr($atts['class']) . '">Login to add favorites</a>';
    // }

    $user_id = get_current_user_id();
    $post_id = intval($atts['post_id']);
    $is_favorited = gl_is_favorited($user_id, $post_id);

    $button_text = $is_favorited ? $atts['text_remove'] : $atts['text_add'];
    $button_class = $atts['class'] . ($is_favorited ? ' favorited' : '');

    return sprintf(
        '<button class="%s" data-post-id="%d" data-favorited="%s">
            <span class="favorite-text">%s</span>
            <span class="favorite-count">%d</span>
        </button>',
        esc_attr($button_class),
        $post_id,
        $is_favorited ? 'true' : 'false',
        esc_html($button_text),
        gl_get_favorite_count($post_id)
    );
}

add_shortcode('gl_favorite_button', 'gl_favorite_button_shortcode');

// Function to display favorites list
function gl_display_user_favorites($user_id = null, $post_type = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return '<p>Please log in to view your favorites.</p>';
    }

    $favorites = gl_get_user_favorites($user_id, $post_type);

    if (empty($favorites)) {
        return '<p>No favorites found.</p>';
    }

    $output = '<div class="favorites-list">';

    foreach ($favorites as $favorite) {
        $post = get_post($favorite->post_id);
        if ($post) {
            $output .= '<div class="favorite-item">';
            $output .= '<h3><a href="' . get_permalink($post->ID) . '">' . get_the_title($post->ID) . '</a></h3>';
            $output .= '<p>' . wp_trim_words(get_the_excerpt($post->ID), 20) . '</p>';
            $output .= '<button class="remove-favorite-btn" data-post-id="' . $post->ID . '">Remove from Favorites</button>';
            $output .= '</div>';
        }
    }

    $output .= '</div>';

    return $output;
}

// Template function to display favorite button
function gl_the_favorite_button($post_id = null, $text_add = 'Agregar a favoritos', $text_remove = 'Eliminar de favoritos') {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!is_user_logged_in()) {
        echo '<a href="' . wp_login_url(get_permalink($post_id)) . '" style="display: none;"  class="favorite-btn">Inicia sesión para agregar favoritos</a>';
        return;
    }

    $user_id = get_current_user_id();
    $is_favorited = gl_is_favorited($user_id, $post_id);

    $button_text = $is_favorited ? $text_remove : $text_add;
    $button_class = 'favorite-btn' . ($is_favorited ? ' favorited' : '');

    printf(
        '<button class="%s" data-post-id="%d" data-favorited="%s">
            <span class="favorite-text">%s</span>
            <span class="favorite-count">%d</span>
        </button>',
        esc_attr($button_class),
        $post_id,
        $is_favorited ? 'true' : 'false',
        esc_html($button_text),
        gl_get_favorite_count($post_id)
    );
}

// Template function to get favorite button HTML
function gl_get_favorite_button($post_id = null, $text_add = 'Agregar a favoritos', $text_remove = 'Quitar de favoritos') {
    ob_start();
    gl_the_favorite_button($post_id, $text_add, $text_remove);
    return ob_get_clean();
}

// Debug function to check favorites system status
function gl_debug_favorites_system() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_favorites';

    $debug_info = array();

    // Check if table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    $debug_info['table_exists'] = $table_exists;

    if ($table_exists) {
        // Check table structure
        $columns = $wpdb->get_results("DESCRIBE $table_name");
        $debug_info['columns'] = $columns;

        // Check if user is logged in
        $debug_info['user_logged_in'] = is_user_logged_in();
        if (is_user_logged_in()) {
            $debug_info['user_id'] = get_current_user_id();
        }

        // Check current post
        $debug_info['current_post_id'] = get_the_ID();
        $debug_info['current_post_type'] = get_post_type();
    }

    return $debug_info;
}

// AJAX handler for debugging
function gl_ajax_debug_favorites() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    $debug_info = gl_debug_favorites_system();
    wp_send_json_success($debug_info);
}

add_action('wp_ajax_gl_debug_favorites', 'gl_ajax_debug_favorites');

// Template function to display favorite button with heart icon only
function gl_the_favorite_heart_button($post_id = null, $size = 'medium') {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!is_user_logged_in()) {
        echo '<a href="' . wp_login_url(get_permalink($post_id)) . '" class="favorite-heart-btn favorite-heart-btn--login" title="Inicia sesión para agregar favoritos">
                <svg class="favorite-heart-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor"/>
                </svg>
              </a>';
        return;
    }

    $user_id = get_current_user_id();
    $is_favorited = gl_is_favorited($user_id, $post_id);

    $button_class = 'favorite-heart-btn' . ($is_favorited ? ' favorited' : '');
    $icon_class = 'favorite-heart-icon' . ($is_favorited ? ' favorited' : '');

    // SVG paths for different states
    $heart_outline = '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor"/>';
    $heart_filled = '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor"/>';

    $svg_content = $is_favorited ? $heart_filled : $heart_outline;

    printf(
        '<button class="%s" data-post-id="%d" data-favorited="%s" title="%s">
            <svg class="%s" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                %s
            </svg>
        </button>',
        esc_attr($button_class),
        $post_id,
        $is_favorited ? 'true' : 'false',
        $is_favorited ? 'Quitar de favoritos' : 'Agregar a favoritos',
        esc_attr($icon_class),
        $svg_content
    );
}

// Template function to get favorite heart button HTML
function gl_get_favorite_heart_button($post_id = null, $size = 'medium') {
    ob_start();
    gl_the_favorite_heart_button($post_id, $size);
    return ob_get_clean();
}

// Shortcode for heart icon favorite button
function gl_favorite_heart_shortcode($atts) {
    $atts = shortcode_atts(array(
        'post_id' => get_the_ID(),
        'size' => 'medium',
        'class' => 'favorite-heart-btn'
    ), $atts);

    return gl_get_favorite_heart_button(intval($atts['post_id']), $atts['size']);
}

add_shortcode('gl_favorite_heart', 'gl_favorite_heart_shortcode');

// Alternative heart button with different sizes
function gl_the_favorite_heart_button_small($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!is_user_logged_in()) {
        echo '<a href="' . wp_login_url(get_permalink($post_id)) . '" class="favorite-heart-btn favorite-heart-btn--small favorite-heart-btn--login" title="Inicia sesión para agregar favoritos">
                <svg class="favorite-heart-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor"/>
                </svg>
              </a>';
        return;
    }

    $user_id = get_current_user_id();
    $is_favorited = gl_is_favorited($user_id, $post_id);

    $button_class = 'favorite-heart-btn favorite-heart-btn--small' . ($is_favorited ? ' favorited' : '');
    $icon_class = 'favorite-heart-icon' . ($is_favorited ? ' favorited' : '');

    $svg_content = '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor"/>';

    printf(
        '<button class="%s" data-post-id="%d" data-favorited="%s" title="%s">
            <svg class="%s" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                %s
            </svg>
        </button>',
        esc_attr($button_class),
        $post_id,
        $is_favorited ? 'true' : 'false',
        $is_favorited ? 'Quitar de favoritos' : 'Agregar a favoritos',
        esc_attr($icon_class),
        $svg_content
    );
}

function gl_the_favorite_heart_button_large($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!is_user_logged_in()) {
        echo '<a href="' . wp_login_url(get_permalink($post_id)) . '" class="favorite-heart-btn favorite-heart-btn--large favorite-heart-btn--login" title="Inicia sesión para agregar favoritos">
                <svg class="favorite-heart-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor"/>
                </svg>
              </a>';
        return;
    }

    $user_id = get_current_user_id();
    $is_favorited = gl_is_favorited($user_id, $post_id);

    $button_class = 'favorite-heart-btn favorite-heart-btn--large' . ($is_favorited ? ' favorited' : '');
    $icon_class = 'favorite-heart-icon' . ($is_favorited ? ' favorited' : '');

    $svg_content = '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor"/>';

    printf(
        '<button class="%s" data-post-id="%d" data-favorited="%s" title="%s">
            <svg class="%s" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                %s
            </svg>
        </button>',
        esc_attr($button_class),
        $post_id,
        $is_favorited ? 'true' : 'false',
        $is_favorited ? 'Quitar de favoritos' : 'Agregar a favoritos',
        esc_attr($icon_class),
        $svg_content
    );
}

// Helper functions to get HTML for different sizes
function gl_get_favorite_heart_button_small($post_id = null) {
    ob_start();
    gl_the_favorite_heart_button_small($post_id);
    return ob_get_clean();
}

function gl_get_favorite_heart_button_large($post_id = null) {
    ob_start();
    gl_the_favorite_heart_button_large($post_id);
    return ob_get_clean();
}

?>