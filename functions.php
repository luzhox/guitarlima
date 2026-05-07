<?php
  require('inc/opciones.php');
  require('inc/customizer.php');
  require('inc/widgets.php');
  require('inc/login.php');
  require('inc/menus.php');
  require('inc/formats.php');
  require('inc/libraries.php');
  require('lib/helpers.php');
  require('inc/etc.php');
  require('inc/favorites.php');
  require('inc/admin-style.php'); // GL Music Admin UI Futurista
  require('inc/woocommerce-account.php');
  require('inc/plan-pro-checkout.php');

    // Custom AJAX login handler
  add_action('wp_ajax_nopriv_custom_login', 'handle_custom_login');
  add_action('wp_ajax_custom_login', 'handle_custom_login');

  function handle_custom_login() {
    if (isset($_POST['action']) && $_POST['action'] === 'custom_login') {
        $response = array('success' => false, 'message' => '');

        if (empty($_POST['username']) || empty($_POST['password'])) {
            $response['message'] = 'Por favor, ingresa tu usuario y contraseña.';
        } else {
            $creds = array(
                'user_login'    => sanitize_text_field($_POST['username']),
                'user_password' => $_POST['password'],
                'remember'      => isset($_POST['rememberme'])
            );

            $user = wp_signon($creds, false);

            if (is_wp_error($user)) {
                $response['message'] = $user->get_error_message();
            } else {
                $response['success'] = true;
                $response['message'] = 'Login exitoso';
                $response['redirect'] = home_url('/mi-cuenta/');
            }
        }

        wp_send_json($response);
    }
  }

  // Custom AJAX register handler
  add_action('wp_ajax_nopriv_custom_register', 'handle_custom_register');
  add_action('wp_ajax_custom_register', 'handle_custom_register');

  function handle_custom_register() {
    if (isset($_POST['action']) && $_POST['action'] === 'custom_register') {
        $response = array('success' => false, 'message' => '');

        // Debug: Log what we're receiving
        error_log('Register AJAX - Terms value: ' . (isset($_POST['terms']) ? $_POST['terms'] : 'NOT SET'));
        error_log('Register AJAX - All POST data: ' . print_r($_POST, true));

        // Validate required fields
        if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
            $response['message'] = 'Por favor, completa todos los campos requeridos.';
        } elseif ($_POST['password'] !== $_POST['password_confirm']) {
            $response['message'] = 'Las contraseñas no coinciden.';
        } elseif (strlen($_POST['password']) < 6) {
            $response['message'] = 'La contraseña debe tener al menos 6 caracteres.';
        } elseif (!isset($_POST['terms']) || $_POST['terms'] !== '1') {
            $response['message'] = 'Debes aceptar los términos y condiciones.';
            error_log('Register AJAX - Terms validation failed. Received: ' . (isset($_POST['terms']) ? $_POST['terms'] : 'NOT SET'));
        } else {
            $username = sanitize_user($_POST['username']);
            $email = sanitize_email($_POST['email']);
            $password = $_POST['password'];

            // Check if username exists
            if (username_exists($username)) {
                $response['message'] = 'Este nombre de usuario ya está en uso.';
            } elseif (email_exists($email)) {
                $response['message'] = 'Este email ya está registrado.';
            } else {
                // Create user
                $user_id = wp_create_user($username, $password, $email);

                if (is_wp_error($user_id)) {
                    $response['message'] = $user_id->get_error_message();
                } else {
                    // Auto login after registration
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id);

                    $response['success'] = true;
                    $response['message'] = 'Cuenta creada exitosamente';
                    $response['redirect'] = home_url('/mi-cuenta/');
                }
            }
        }

        wp_send_json($response);
    }
  }

  add_filter('get_avatar_data', 'guitarlima_user_profile_avatar_data', 20, 2);

  function guitarlima_user_profile_avatar_data($args, $id_or_email) {
    $user_id = 0;

    if (is_numeric($id_or_email)) {
        $user_id = (int) $id_or_email;
    } elseif ($id_or_email instanceof WP_User) {
        $user_id = (int) $id_or_email->ID;
    } elseif ($id_or_email instanceof WP_Comment) {
        $user_id = (int) $id_or_email->user_id;
    } elseif ($id_or_email instanceof WP_Post) {
        $user_id = (int) $id_or_email->post_author;
    }

    if (!$user_id) {
        return $args;
    }

    $avatar_id = (int) get_user_meta($user_id, 'gl_profile_avatar_id', true);

    if (!$avatar_id) {
        return $args;
    }

    $size = isset($args['size']) ? (int) $args['size'] : 96;
    $image = wp_get_attachment_image_src($avatar_id, [$size, $size]);

    if (!$image) {
        return $args;
    }

    $args['url'] = $image[0];
    $args['found_avatar'] = true;

    return $args;
  }
  ?>
