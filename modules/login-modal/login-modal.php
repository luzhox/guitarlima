<?php
/**
 * Login Modal Component
 *
 * A modal login form that can be triggered from anywhere on the site
 * Uses AJAX for seamless login experience
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Login Modal Trigger Button -->
<button id="login-modal-trigger" class="login-modal-trigger">
    <span class="login-icon">🔐</span>
    <span class="login-text">Iniciar Sesión</span>
</button>

<!-- Login Modal Overlay -->
<div id="login-modal-overlay" class="login-modal-overlay">
    <div class="login-modal-container">
        <div class="login-modal-header">
            <h2>Iniciar Sesión</h2>
            <button id="login-modal-close" class="login-modal-close">
                <span>✕</span>
            </button>
        </div>

        <div class="login-modal-body">
            <form id="login-modal-form" class="login-modal-form">
                <div class="form-group">
                    <label for="modal_user_login">Usuario o Email</label>
                    <input type="text" id="modal_user_login" name="log" required>
                </div>

                <div class="form-group">
                    <label for="modal_user_pass">Contraseña</label>
                    <div class="password-field-wrapper">
                        <input type="password" id="modal_user_pass" name="pwd" class="password-input" required>
                        <button type="button" class="password-toggle" data-target="modal_user_pass">
                            <span class="eye-icon">👁</span>
                        </button>
                    </div>
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="modal_rememberme" name="rememberme">
                        <span class="checkmark"></span>
                        Recordarme
                    </label>
                </div>

                <div class="form-group">
                    <button type="submit" id="modal_wp_submit" class="login-submit-btn">
                        Iniciar Sesión
                    </button>
                </div>

                <div class="form-links">
                    <a href="<?php echo wp_lostpassword_url(); ?>" class="forgot-password">
                        ¿Olvidaste tu contraseña?
                    </a>
                    <a href="<?php echo home_url('/register'); ?>" class="register-link">
                        ¿No tienes cuenta? Regístrate
                    </a>
                </div>
            </form>

            <div id="modal-login-message" class="modal-message" style="display: none;"></div>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.login-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(8px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.login-modal-overlay.active {
    display: flex;
    opacity: 1;
}

.login-modal-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 0;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    transform: scale(0.9);
    transition: transform 0.3s ease;
    overflow: hidden;
}

.login-modal-overlay.active .login-modal-container {
    transform: scale(1);
}

.login-modal-header {
    background: rgba(255, 255, 255, 0.1);
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.login-modal-header h2 {
    color: white;
    margin: 0;
    font-size: 24px;
    font-weight: 600;
}

.login-modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    padding: 5px;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s ease;
}

.login-modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.login-modal-body {
    padding: 30px;
}

.login-modal-form .form-group {
    margin-bottom: 20px;
}

.login-modal-form label {
    display: block;
    color: white;
    margin-bottom: 8px;
    font-weight: 500;
    font-size: 14px;
}

.login-modal-form input[type="text"],
.login-modal-form input[type="password"],
.login-modal-form input[type="email"] {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-size: 16px;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.login-modal-form input::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.login-modal-form input:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.8);
    background: rgba(255, 255, 255, 0.2);
}

/* Password Toggle Styles */
.password-field-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.7);
    cursor: pointer;
    padding: 5px;
    font-size: 16px;
    transition: color 0.3s ease;
}

.password-toggle:hover {
    color: white;
}

.password-toggle.showing {
    color: #4CAF50;
}

/* Checkbox Styles */
.checkbox-group {
    display: flex;
    align-items: center;
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    color: white;
    font-size: 14px;
    margin: 0;
}

.checkbox-label input[type="checkbox"] {
    display: none;
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-radius: 4px;
    margin-right: 10px;
    position: relative;
    transition: all 0.3s ease;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark {
    background: #4CAF50;
    border-color: #4CAF50;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 12px;
    font-weight: bold;
}

/* Submit Button */
.login-submit-btn {
    width: 100%;
    padding: 14px 20px;
    background: linear-gradient(45deg, #4CAF50, #45a049);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.login-submit-btn:hover {
    background: linear-gradient(45deg, #45a049, #4CAF50);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
}

.login-submit-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Form Links */
.form-links {
    margin-top: 20px;
    text-align: center;
}

.form-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    font-size: 14px;
    margin: 0 10px;
    transition: color 0.3s ease;
}

.form-links a:hover {
    color: white;
    text-decoration: underline;
}

/* Modal Trigger Button */
.login-modal-trigger {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
}

.login-modal-trigger:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.login-icon {
    font-size: 16px;
}

/* Message Styles */
.modal-message {
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
    font-weight: 500;
}

.modal-message.success {
    background: rgba(76, 175, 80, 0.2);
    color: #4CAF50;
    border: 1px solid rgba(76, 175, 80, 0.3);
}

.modal-message.error {
    background: rgba(244, 67, 54, 0.2);
    color: #f44336;
    border: 1px solid rgba(244, 67, 54, 0.3);
}

/* Responsive Design */
@media (max-width: 480px) {
    .login-modal-container {
        width: 95%;
        margin: 20px;
    }

    .login-modal-header {
        padding: 15px 20px;
    }

    .login-modal-body {
        padding: 20px;
    }

    .login-modal-header h2 {
        font-size: 20px;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .login-modal-container {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Modal functionality
    const modalOverlay = $('#login-modal-overlay');
    const modalTrigger = $('#login-modal-trigger');
    const modalClose = $('#login-modal-close');

    // Open modal
    modalTrigger.on('click', function(e) {
        e.preventDefault();
        modalOverlay.addClass('active');
        $('body').css('overflow', 'hidden');
        $('#modal_user_login').focus();
    });

    // Close modal
    function closeModal() {
        modalOverlay.removeClass('active');
        $('body').css('overflow', '');
        $('#modal-login-message').hide();
    }

    modalClose.on('click', closeModal);

    // Close on overlay click
    modalOverlay.on('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Close on ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && modalOverlay.hasClass('active')) {
            closeModal();
        }
    });

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
    $('#login-modal-form').on('submit', function(e) {
        e.preventDefault();

        const username = $('#modal_user_login').val();
        const password = $('#modal_user_pass').val();
        const rememberme = $('#modal_rememberme').is(':checked');

        // Show loading state
        $('#modal_wp_submit').prop('disabled', true).text('Iniciando sesión...');

        // Clear previous messages
        $('#modal-login-message').hide();

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
                    $('#modal-login-message')
                        .removeClass('error')
                        .addClass('success')
                        .html('<p>¡Inicio de sesión exitoso! Redirigiendo...</p>')
                        .show();

                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1500);
                } else {
                    $('#modal-login-message')
                        .removeClass('success')
                        .addClass('error')
                        .html('<p>' + response.message + '</p>')
                        .show();
                    $('#modal_wp_submit').prop('disabled', false).text('Iniciar Sesión');
                }
            },
            error: function() {
                $('#modal-login-message')
                    .removeClass('success')
                    .addClass('error')
                    .html('<p>Error de conexión. Por favor, intenta de nuevo.</p>')
                    .show();
                $('#modal_wp_submit').prop('disabled', false).text('Iniciar Sesión');
            }
        });
    });
});
</script>