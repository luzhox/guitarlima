<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if user is already logged in
if (is_user_logged_in()) {
    echo '<script>window.location.href = "' . esc_url(home_url()) . '";</script>';
    exit;
}

// Handle regular form submission as fallback
$register_error = '';
$register_success = false;

if ($_POST && isset($_POST['wp-submit-register'])) {
    if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
        $register_error = 'Por favor, completa todos los campos requeridos.';
    } else {
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        // Check if username exists
        if (username_exists($username)) {
            $register_error = 'Este nombre de usuario ya está en uso.';
        } elseif (email_exists($email)) {
            $register_error = 'Este email ya está registrado.';
        } else {
            // Create user
            $user_id = wp_create_user($username, $password, $email);

            if (is_wp_error($user_id)) {
                $register_error = $user_id->get_error_message();
            } else {
                // Auto login after registration
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                $register_success = true;
            }
        }
    }
}
?>

<div class="register-page">
    <div class="container">
        <div class="register-page__content">
            <div class="register-form-wrapper">
                <div class="register-form-header">
                    <div class="logo">
                        <?php if (has_custom_logo()) : ?>
                            <?php the_custom_logo(); ?>
                        <?php else : ?>
                            <h1><?php bloginfo('name'); ?></h1>
                        <?php endif; ?>
                    </div>
                    <h2>Crear Cuenta</h2>
                    <p>Regístrate para acceder a todo el contenido</p>
                </div>

                <div id="register-message" style="display: none;" class="register-message"></div>

                <?php if ($register_error) : ?>
                    <div class="register-error">
                        <p><?php echo esc_html($register_error); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($register_success) : ?>
                    <div class="register-success">
                        <p>¡Cuenta creada exitosamente! Redirigiendo...</p>
                        <script>
                            setTimeout(function() {
                                window.location.href = '<?php echo esc_url(home_url()); ?>';
                            }, 1500);
                        </script>
                    </div>
                <?php else : ?>
                    <form id="register-form" class="register-form" method="post" action="">
                        <div class="form-group">
                            <label for="user_username">Nombre de Usuario *</label>
                            <input type="text" name="username" id="user_username" class="input" required>
                            <small class="form-help">El nombre de usuario no puede ser cambiado después.</small>
                        </div>

                        <div class="form-group">
                            <label for="user_email">Email *</label>
                            <input type="email" name="email" id="user_email" class="input" required>
                            <small class="form-help">Usaremos este email para contactarte.</small>
                        </div>

                        <div class="form-group">
                            <label for="user_password">Contraseña *</label>
                            <div class="password-field-wrapper">
                                <input type="password" name="password" id="user_password" class="input password-input" required>
                                <button type="button" class="password-toggle" data-target="user_password" aria-label="Mostrar contraseña">
                                    <span class="password-toggle__show" aria-hidden="true">Ver</span>
                                    <span class="password-toggle__hide" aria-hidden="true">Ocultar</span>
                                </button>
                            </div>
                            <small class="form-help">Mínimo 6 caracteres.</small>
                        </div>

                        <div class="form-group">
                            <label for="user_password_confirm">Confirmar Contraseña *</label>
                            <div class="password-field-wrapper">
                                <input type="password" name="password_confirm" id="user_password_confirm" class="input password-input" required>
                                <button type="button" class="password-toggle" data-target="user_password_confirm" aria-label="Mostrar contraseña">
                                    <span class="password-toggle__show" aria-hidden="true">Ver</span>
                                    <span class="password-toggle__hide" aria-hidden="true">Ocultar</span>
                                </button>
                            </div>
                            <small class="form-help">Debe coincidir con la contraseña.</small>
                        </div>

                        <div class="form-group terms-group">
                            <input type="hidden" id="terms_accepted" name="terms" value="0">
                            <div class="terms-button-wrapper">
                                <button type="button" id="accept_terms_btn" class="terms-button">
                                    <span class="terms-icon" aria-hidden="true"></span>
                                    <span class="terms-text">Acepto los <a href="#" target="_blank">términos y condiciones</a></span>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" name="wp-submit-register" id="wp-submit-register" class="register-button">
                                Crear Cuenta
                            </button>
                        </div>
                    </form>
                <?php endif; ?>

                <div class="register-footer">
                    <p class="login-link">
                        ¿Ya tienes una cuenta? <a href="<?php echo esc_url(home_url('/mi-cuenta/')); ?>" data-login-modal-open>Inicia sesión aquí</a>
                    </p>
                    <p class="back-to-site">
                        <a href="<?php echo esc_url(home_url()); ?>">Volver al sitio</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Password toggle functionality
    $('.register-page .password-toggle').on('click', function() {
        const button = $(this);
        const targetId = button.data('target');
        const input = $('#' + targetId);
        const isPassword = input.attr('type') === 'password';

        input.attr('type', isPassword ? 'text' : 'password');
        button.toggleClass('showing', isPassword);
        button.attr('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });

    let termsAccepted = false;

    // Handle terms button click
    $('#accept_terms_btn').on('click', function() {
        termsAccepted = !termsAccepted;
        const button = $(this);
        const hiddenInput = $('#terms_accepted');

        if (termsAccepted) {
            button.addClass('accepted');
            hiddenInput.val('1');
        } else {
            button.removeClass('accepted');
            hiddenInput.val('0');
        }
    });

    $('#register-form').on('submit', function(e) {
        e.preventDefault();

        const username = $('#user_username').val();
        const email = $('#user_email').val();
        const password = $('#user_password').val();
        const passwordConfirm = $('#user_password_confirm').val();
        const terms = $('#terms_accepted').val() === '1';

        // Basic validation
        if (!username || !email || !password || !passwordConfirm) {
            showMessage('Por favor, completa todos los campos requeridos.', 'error');
            return;
        }

        if (password !== passwordConfirm) {
            showMessage('Las contraseñas no coinciden.', 'error');
            return;
        }

        if (password.length < 6) {
            showMessage('La contraseña debe tener al menos 6 caracteres.', 'error');
            return;
        }

        if (!terms) {
            showMessage('Debes aceptar los términos y condiciones.', 'error');
            return;
        }

        // Show loading state
        $('#wp-submit-register').prop('disabled', true).text('Creando cuenta...');

        // Clear previous messages
        $('.register-error, .register-success').remove();
        $('#register-message').hide();

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'custom_register',
                username: username,
                email: email,
                password: password,
                password_confirm: passwordConfirm,
                terms: terms ? '1' : '0'
            },
            success: function(response) {
                if (response.success) {
                    $('#register-form').html('<div class="register-success"><p>¡Cuenta creada exitosamente! Redirigiendo...</p></div>');
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1500);
                } else {
                    showMessage(response.message, 'error');
                    $('#wp-submit-register').prop('disabled', false).text('Crear Cuenta');
                }
            },
            error: function() {
                showMessage('Error de conexión. Por favor, intenta de nuevo.', 'error');
                $('#wp-submit-register').prop('disabled', false).text('Crear Cuenta');
            }
        });
    });

    function showMessage(message, type) {
        const messageClass = type === 'error' ? 'register-error' : 'register-success';
        $('#register-form').before('<div class="' + messageClass + '"><p>' + message + '</p></div>');
    }
});
</script>
