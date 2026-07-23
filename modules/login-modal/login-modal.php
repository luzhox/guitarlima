<?php
/**
 * Global login modal.
 *
 * Header links with [data-login-modal-open] open this form. Their href remains
 * a normal account URL so login still works if JavaScript does not load.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="login-modal-overlay" class="login-modal-overlay" aria-hidden="true">
    <div class="login-modal-container" role="dialog" aria-modal="true" aria-labelledby="login-modal-title">
        <div class="login-modal-header">
            <div>
                <span class="login-modal-kicker">GL Music</span>
                <h2 id="login-modal-title">Iniciar sesión</h2>
            </div>
            <button id="login-modal-close" class="login-modal-close" type="button" aria-label="Cerrar inicio de sesión">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="login-modal-body">
            <form id="login-modal-form" class="login-modal-form" autocomplete="on">
                <div class="form-group">
                    <label for="modal_user_login">Usuario o email</label>
                    <input type="text" id="modal_user_login" name="log" autocomplete="username" required>
                </div>

                <div class="form-group">
                    <label for="modal_user_pass">Contraseña</label>
                    <div class="password-field-wrapper">
                        <input type="password" id="modal_user_pass" name="pwd" class="password-input" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" data-target="modal_user_pass" aria-label="Mostrar contraseña">
                            <span class="password-toggle__show" aria-hidden="true">Ver</span>
                            <span class="password-toggle__hide" aria-hidden="true">Ocultar</span>
                        </button>
                    </div>
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-label" for="modal_rememberme">
                        <input type="checkbox" id="modal_rememberme" name="rememberme">
                        <span class="checkmark" aria-hidden="true"></span>
                        <span>Recordarme</span>
                    </label>
                </div>

                <div id="modal-login-message" class="modal-message" role="status" aria-live="polite" style="display: none;"></div>

                <button type="submit" id="modal_wp_submit" class="login-submit-btn">
                    Iniciar sesión
                </button>

                <div class="form-links">
                    <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="forgot-password">
                        ¿Olvidaste tu contraseña?
                    </a>
                    <a href="<?php echo esc_url(home_url('/registro/')); ?>" class="register-link">
                        Crear cuenta
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const modalOverlay = $('#login-modal-overlay');
    const modalClose = $('#login-modal-close');
    const modalForm = $('#login-modal-form');
    const modalMessage = $('#modal-login-message');
    const loginInput = $('#modal_user_login');
    const submitButton = $('#modal_wp_submit');

    function openModal() {
        modalOverlay.addClass('active').attr('aria-hidden', 'false');
        $('body').addClass('login-modal-open');
        $('.site-header-sandwich, .site-header-nav').removeClass('active');
        $('#masthead').removeClass('active');
        $('body').removeClass('gl-menu-open');

        window.setTimeout(function() {
            loginInput.trigger('focus');
        }, 80);
    }

    function closeModal() {
        modalOverlay.removeClass('active').attr('aria-hidden', 'true');
        $('body').removeClass('login-modal-open');
        modalMessage.hide().removeClass('success error').empty();
    }

    $('[data-login-modal-open]').on('click', function(e) {
        e.preventDefault();
        openModal();
    });

    modalClose.on('click', closeModal);

    modalOverlay.on('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && modalOverlay.hasClass('active')) {
            closeModal();
        }
    });

    modalOverlay.find('.password-toggle').on('click', function() {
        const button = $(this);
        const input = $('#' + button.data('target'));
        const isPassword = input.attr('type') === 'password';

        input.attr('type', isPassword ? 'text' : 'password');
        button.toggleClass('showing', isPassword);
        button.attr('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });

    modalForm.on('submit', function(e) {
        e.preventDefault();

        submitButton.prop('disabled', true).text('Iniciando...');
        modalMessage.hide().removeClass('success error').empty();

        $.ajax({
            url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
            type: 'POST',
            data: {
                action: 'custom_login',
                username: $('#modal_user_login').val(),
                password: $('#modal_user_pass').val(),
                rememberme: $('#modal_rememberme').is(':checked') ? '1' : ''
            },
            success: function(response) {
                if (response.success) {
                    modalMessage
                        .removeClass('error')
                        .addClass('success')
                        .html('<p>Inicio de sesión correcto. Redirigiendo...</p>')
                        .show();

                    window.setTimeout(function() {
                        const redirectUrl = response.redirect || '<?php echo esc_url(home_url('/mi-cuenta/')); ?>';
                        window.location.replace(redirectUrl);
                    }, 700);
                } else {
                    modalMessage
                        .removeClass('success')
                        .addClass('error')
                        .html('<p>' + (response.message || 'No pudimos iniciar sesión.') + '</p>')
                        .show();
                    submitButton.prop('disabled', false).text('Iniciar sesión');
                }
            },
            error: function() {
                modalMessage
                    .removeClass('success')
                    .addClass('error')
                    .html('<p>Error de conexión. Intenta nuevamente.</p>')
                    .show();
                submitButton.prop('disabled', false).text('Iniciar sesión');
            }
        });
    });
});
</script>
