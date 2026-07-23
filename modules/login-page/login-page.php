<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle login form submission
$login_error = '';
$login_success = false;
$debug_info = '';

if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    $profile_message = '';
    $profile_error = '';

    if ($_POST && isset($_POST['gl_account_profile_submit'])) {
        if (!isset($_POST['gl_account_profile_nonce']) || !wp_verify_nonce($_POST['gl_account_profile_nonce'], 'gl_account_profile')) {
            $profile_error = 'Error de seguridad. Por favor, intenta de nuevo.';
        } else {
            $first_name = sanitize_text_field(wp_unslash($_POST['first_name'] ?? ''));
            $last_name = sanitize_text_field(wp_unslash($_POST['last_name'] ?? ''));
            $display_name_post = sanitize_text_field(wp_unslash($_POST['display_name'] ?? ''));
            $email = sanitize_email(wp_unslash($_POST['user_email'] ?? ''));
            $password_current = (string) ($_POST['password_current'] ?? '');
            $password_1 = (string) ($_POST['password_1'] ?? '');
            $password_2 = (string) ($_POST['password_2'] ?? '');

            if (!$display_name_post) {
                $profile_error = 'El nombre visible es obligatorio.';
            } elseif (!$email || !is_email($email)) {
                $profile_error = 'Ingresa un correo válido.';
            } elseif ($email !== $current_user->user_email && email_exists($email)) {
                $profile_error = 'Ese correo ya está asociado a otra cuenta.';
            } elseif (($password_1 || $password_2 || $password_current) && !wp_check_password($password_current, $current_user->user_pass, $current_user->ID)) {
                $profile_error = 'La contraseña actual no es correcta.';
            } elseif (($password_1 || $password_2) && $password_1 !== $password_2) {
                $profile_error = 'La nueva contraseña y su confirmación no coinciden.';
            } else {
                $user_data = [
                    'ID' => $current_user->ID,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'display_name' => $display_name_post,
                    'user_email' => $email,
                ];

                if ($password_1) {
                    $user_data['user_pass'] = $password_1;
                }

                $updated = wp_update_user($user_data);

                if (is_wp_error($updated)) {
                    $profile_error = $updated->get_error_message();
                } else {
                    if (!empty($_FILES['gl_profile_avatar']['name'])) {
                        $avatar_file = $_FILES['gl_profile_avatar'];
                        $allowed_mimes = [
                            'jpg|jpeg|jpe' => 'image/jpeg',
                            'png' => 'image/png',
                            'webp' => 'image/webp',
                        ];

                        if (!empty($avatar_file['size']) && (int) $avatar_file['size'] > 2 * MB_IN_BYTES) {
                            $profile_error = 'La foto de perfil no debe superar los 2 MB.';
                        } else {
                            require_once ABSPATH . 'wp-admin/includes/file.php';
                            require_once ABSPATH . 'wp-admin/includes/image.php';
                            require_once ABSPATH . 'wp-admin/includes/media.php';

                            $avatar_id = media_handle_upload('gl_profile_avatar', 0, [], [
                                'test_form' => false,
                                'mimes' => $allowed_mimes,
                            ]);

                            if (is_wp_error($avatar_id)) {
                                $profile_error = 'No pudimos subir la foto. Usa una imagen JPG, PNG o WebP.';
                            } else {
                                update_user_meta($current_user->ID, 'gl_profile_avatar_id', (int) $avatar_id);
                            }
                        }
                    }

                    $profile_message = 'Perfil actualizado correctamente.';
                    $current_user = wp_get_current_user();
                }
            }
        }
    }

    $display_name = $current_user->display_name ?: $current_user->user_login;
    $member_since = $current_user->user_registered ? date_i18n('F Y', strtotime($current_user->user_registered)) : '';
    $active_subscriptions = [];
    $is_profile_edit = isset($_GET['editar-perfil']);
    $checkout_url = function_exists('guitarlima_plan_pro_checkout_url')
        ? guitarlima_plan_pro_checkout_url(function_exists('guitarlima_default_pro_plan_id') ? guitarlima_default_pro_plan_id() : 0)
        : home_url('/comprar-plan-pro/');

    if (class_exists('GLS_Subscriptions')) {
        $active_subscriptions = GLS_Subscriptions::get_active_for_user(get_current_user_id());
    }

    $primary_subscription = !empty($active_subscriptions) ? $active_subscriptions[0] : null;
    $is_admin = current_user_can('manage_options');
    $subscription_status = 'Sin plan activo';
    $subscription_label = 'Explora el Plan Pro para desbloquear cursos y librerías.';

    if ($is_admin) {
        // Los administradores tienen acceso completo; no se les ofrece el Plan Pro.
        $subscription_status = 'Admin';
        $subscription_label = 'Acceso completo';
    } elseif ($primary_subscription) {
        $plan_title = get_the_title((int) $primary_subscription->plan_id);
        $subscription_status = $primary_subscription->status === 'active' ? 'Suscripción activa' : 'Suscripción pendiente';
        $subscription_label = $plan_title ?: 'Plan GL Music';
    }

    // El botón de cancelar reutiliza el endpoint AJAX (gls_cancel_subscription)
    // y el JS (bindCancel) del plugin glmusic-subscriptions.
    $can_cancel_subscription = !$is_admin && $primary_subscription && $primary_subscription->status === 'active';
    if ($can_cancel_subscription) {
        wp_enqueue_script('gls-checkout');
        wp_enqueue_style('gls-checkout');
    }
    ?>

    <div class="login-page login-page--account">
        <div class="container">
            <div class="account-panel">
                <section class="account-panel__profile" aria-label="Perfil del usuario">
                    <div class="account-panel__avatar">
                        <?php echo get_avatar(get_current_user_id(), 112, '', $display_name); ?>
                    </div>
                    <div class="account-panel__identity">
                        <p class="account-panel__kicker">Tu cuenta</p>
                        <h1><?php echo esc_html($display_name); ?></h1>
                        <p class="account-panel__email"><?php echo esc_html($current_user->user_email); ?></p>
                        <div class="account-panel__meta">
                            <span><?php echo esc_html($subscription_status); ?></span>
                            <span><?php echo esc_html($subscription_label); ?></span>
                            <?php if ($member_since) : ?>
                                <span><?php echo esc_html('Miembro desde ' . $member_since); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="account-panel__actions">
                        <?php if (!$is_admin && !$primary_subscription) : ?>
                            <a class="account-panel__button account-panel__button--pro" href="<?php echo esc_url($checkout_url); ?>">Suscribirme al Plan Pro</a>
                        <?php endif; ?>
                        <a class="account-panel__button" href="<?php echo esc_url(home_url('/mi-cuenta/?editar-perfil=1')); ?>">Editar perfil</a>
                        <a class="account-panel__link" href="<?php echo esc_url(glmusic_logout_url()); ?>">Cerrar sesión</a>
                        <?php if ($can_cancel_subscription) : ?>
                            <button type="button" class="account-panel__cancel gls-cancel-btn" data-sub-id="<?php echo esc_attr($primary_subscription->id); ?>">Cancelar suscripción</button>
                        <?php endif; ?>
                    </div>
                </section>

                <?php if ($profile_message) : ?>
                    <div class="account-panel__notice account-panel__notice--success"><?php echo esc_html($profile_message); ?></div>
                <?php endif; ?>

                <?php if ($profile_error) : ?>
                    <div class="account-panel__notice account-panel__notice--error"><?php echo esc_html($profile_error); ?></div>
                <?php endif; ?>

                <?php if ($is_profile_edit) : ?>
                    <section class="account-panel__edit" aria-label="Editar perfil">
                        <div class="account-panel__edit-head">
                            <p class="account-panel__kicker">Perfil</p>
                            <h2>Editar perfil</h2>
                            <p>Actualiza tus datos personales y, si lo necesitas, cambia tu contraseña.</p>
                        </div>

                        <form class="account-panel__form" method="post" action="<?php echo esc_url(home_url('/mi-cuenta/?editar-perfil=1')); ?>" enctype="multipart/form-data">
                            <?php wp_nonce_field('gl_account_profile', 'gl_account_profile_nonce'); ?>
                            <div class="account-panel__photo">
                                <div class="account-panel__photo-preview">
                                    <?php echo get_avatar(get_current_user_id(), 128, '', $display_name); ?>
                                </div>
                                <div class="account-panel__photo-field">
                                    <label for="gl_profile_avatar">Foto de perfil</label>
                                    <input type="file" id="gl_profile_avatar" name="gl_profile_avatar" accept="image/jpeg,image/png,image/webp">
                                    <small>Sube una imagen JPG, PNG o WebP de máximo 2 MB.</small>
                                </div>
                            </div>
                            <div class="account-panel__form-grid">
                                <p>
                                    <label for="first_name">Nombre</label>
                                    <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($current_user->first_name); ?>">
                                </p>
                                <p>
                                    <label for="last_name">Apellido</label>
                                    <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($current_user->last_name); ?>">
                                </p>
                            </div>
                            <p>
                                <label for="display_name">Nombre visible</label>
                                <input type="text" id="display_name" name="display_name" value="<?php echo esc_attr($display_name); ?>" required>
                            </p>
                            <p>
                                <label for="user_email">Correo electrónico</label>
                                <input type="email" id="user_email" name="user_email" value="<?php echo esc_attr($current_user->user_email); ?>" required>
                            </p>

                            <div class="account-panel__password">
                                <h3>Cambiar contraseña</h3>
                                <p>Déjalo vacío si quieres conservar tu contraseña actual.</p>
                                <p>
                                    <label for="password_current">Contraseña actual</label>
                                    <input type="password" id="password_current" name="password_current" autocomplete="current-password">
                                </p>
                                <div class="account-panel__form-grid">
                                    <p>
                                        <label for="password_1">Nueva contraseña</label>
                                        <input type="password" id="password_1" name="password_1" autocomplete="new-password">
                                    </p>
                                    <p>
                                        <label for="password_2">Confirmar nueva contraseña</label>
                                        <input type="password" id="password_2" name="password_2" autocomplete="new-password">
                                    </p>
                                </div>
                            </div>

                            <div class="account-panel__form-actions">
                                <button class="account-panel__button" type="submit" name="gl_account_profile_submit" value="1">Guardar cambios</button>
                                <a class="account-panel__link" href="<?php echo esc_url(home_url('/mi-cuenta/')); ?>">Volver a mi cuenta</a>
                            </div>
                        </form>
                    </section>
                <?php else : ?>
                    <nav class="account-panel__quick" aria-label="Accesos de cuenta">
                        <a class="account-panel__card" href="<?php echo esc_url(home_url('/mis-favoritos/')); ?>">
                            <span>Mis favoritos</span>
                            <small>Canciones, cursos y materiales guardados</small>
                        </a>
                        <a class="account-panel__card" href="<?php echo esc_url(home_url('/planes/')); ?>">
                            <span>Planes</span>
                            <small>Revisa o activa tu acceso Pro</small>
                        </a>
                        <a class="account-panel__card" href="<?php echo esc_url(home_url('/comprar-plan-pro/')); ?>">
                            <span>Suscripción</span>
                            <small>Estado y compra del Plan Pro</small>
                        </a>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    return;
}

// Debug: Check if any POST data exists
if (!empty($_POST)) {
    $debug_info .= 'POST data exists: ' . print_r($_POST, true) . '. ';
}

if ($_POST && isset($_POST['wp-submit'])) {
    // Debug information
    $debug_info .= 'Form submitted. ';

    // Check if nonce exists
    if (!isset($_POST['login_nonce'])) {
        $login_error = 'Error: Nonce no encontrado.';
        $debug_info .= 'Nonce not found. ';
    } else {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['login_nonce'], 'login_form')) {
            $login_error = 'Error de seguridad. Por favor, intenta de nuevo.';
            $debug_info .= 'Nonce verification failed. ';
        } else {
            $debug_info .= 'Nonce verified. ';

            // Check if username and password are provided
            if (empty($_POST['log']) || empty($_POST['pwd'])) {
                $login_error = 'Por favor, ingresa tu usuario y contraseña.';
                $debug_info .= 'Username or password empty. ';
            } else {
                $creds = array(
                    'user_login'    => sanitize_text_field($_POST['log']),
                    'user_password' => $_POST['pwd'],
                    'remember'      => isset($_POST['rememberme'])
                );

                $debug_info .= 'Attempting login with user: ' . $creds['user_login'] . '. ';

                $user = wp_signon($creds, false);

                if (is_wp_error($user)) {
                    $login_error = $user->get_error_message();
                    $debug_info .= 'Login failed: ' . $user->get_error_message() . '. ';
                } else {
                    $login_success = true;
                    $debug_info .= 'Login successful. ';
                }
            }
        }
    }
} else {
    $debug_info .= 'Form not submitted or wp-submit not found. ';
}
?>

<div class="login-page">
    <div class="container">
        <div class="login-page__content">
            <div class="login-form-wrapper">
                <div class="login-form-header">
                    <div class="logo">
                        <?php if (has_custom_logo()) : ?>
                            <?php the_custom_logo(); ?>
                        <?php else : ?>
                            <h1><?php bloginfo('name'); ?></h1>
                        <?php endif; ?>
                    </div>
                    <h2>Iniciar Sesión</h2>
                    <p>Accede a tu cuenta para continuar</p>
                </div>

                <div id="login-message" style="display: none;" class="login-message"></div>

                <?php if ($login_error) : ?>
                    <div class="login-error">
                        <p><?php echo esc_html($login_error); ?></p>
                        <?php if (current_user_can('administrator')) : ?>
                            <small style="color: #999; font-size: 0.8rem;">Debug: <?php echo esc_html($debug_info); ?></small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($login_success) : ?>
                    <div class="login-success">
                        <p>¡Inicio de sesión exitoso! Redirigiendo...</p>
                        <script>
                            setTimeout(function() {
                                window.location.href = '<?php echo esc_url(home_url('/mi-cuenta/')); ?>';
                            }, 1500);
                        </script>
                    </div>
                <?php else : ?>
                    <form id="login-form" class="login-form" method="post" action="">
                        <?php wp_nonce_field('login_form', 'login_nonce'); ?>
                        <div class="form-group">
                            <label for="user_login">Usuario o Email</label>
                            <input type="text" name="log" id="user_login" class="input" value="<?php echo esc_attr(wp_unslash($_POST['log'] ?? '')); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="user_pass">Contraseña</label>
                            <div class="password-field-wrapper">
                                <input type="password" name="pwd" id="user_pass" class="input password-input" required>
                                <button type="button" class="password-toggle" data-target="user_pass">
                                    <span class="eye-icon">👁</span>
                                </button>
                            </div>
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="rememberme" id="rememberme" value="forever">
                                <span class="checkmark"></span>
                                Recordarme
                            </label>
                        </div>

                        <div class="form-group">
                            <button type="submit" name="wp-submit" id="wp-submit" class="login-button">
                                Iniciar Sesión
                            </button>
                        </div>
                    </form>
                <?php endif; ?>

                <div class="login-footer">
                    <p class="lost-password">
                        <a href="<?php echo wp_lostpassword_url(); ?>">¿Olvidaste tu contraseña?</a>
                    </p>
                    <p class="back-to-site">
                        <a href="<?php echo home_url(); ?>">← Volver al sitio</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.password-field-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.password-input {
    padding-right: 50px !important;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    font-size: 16px;
    color: #666;
    transition: color 0.3s ease;
}

.password-toggle:hover {
    color: #667eea;
}

.password-toggle.showing .eye-icon {
    opacity: 0.7;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Password toggle functionality
    $('.password-toggle').on('click', function() {
        const button = $(this);
        const targetId = button.data('target');
        const input = $('#' + targetId);
        const icon = button.find('.eye-icon');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.text('🙈');
            button.addClass('showing');
        } else {
            input.attr('type', 'password');
            icon.text('👁');
            button.removeClass('showing');
        }
    });

    // Handle login form submission
    $('#login-form').on('submit', function(e) {
        e.preventDefault();

        const username = $('#user_login').val();
        const password = $('#user_pass').val();
        const rememberme = $('#rememberme').is(':checked');

        // Show loading state
        $('#wp-submit').prop('disabled', true).text('Iniciando sesión...');

        // Clear previous messages
        $('.login-error, .login-success').remove();
        $('#login-message').hide();

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'custom_login',
                username: username,
                password: password,
                rememberme: rememberme
            },
            success: function(response) {
                if (response.success) {
                    $('#login-form').html('<div class="login-success"><p>¡Inicio de sesión exitoso! Redirigiendo...</p></div>');
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1500);
                } else {
                    $('#login-form').before('<div class="login-error"><p>' + response.message + '</p></div>');
                    $('#wp-submit').prop('disabled', false).text('Iniciar Sesión');
                }
            },
            error: function() {
                $('#login-form').before('<div class="login-error"><p>Error de conexión. Por favor, intenta de nuevo.</p></div>');
                $('#wp-submit').prop('disabled', false).text('Iniciar Sesión');
            }
        });
    });
});
</script>

<?php if (current_user_can('administrator')) : ?>
    <div style="background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc; font-family: monospace; font-size: 12px;">
        <strong>Debug Info (Admin Only):</strong><br>
        POST Data: <?php echo !empty($_POST) ? 'Yes' : 'No'; ?><br>
        wp-submit exists: <?php echo isset($_POST['wp-submit']) ? 'Yes' : 'No'; ?><br>
        Nonce exists: <?php echo isset($_POST['login_nonce']) ? 'Yes' : 'No'; ?><br>
        Username provided: <?php echo !empty($_POST['log']) ? 'Yes' : 'No'; ?><br>
        Password provided: <?php echo !empty($_POST['pwd']) ? 'Yes' : 'No'; ?><br>
        Debug: <?php echo esc_html($debug_info); ?>
    </div>
<?php endif; ?>
