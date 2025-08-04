<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if user is already logged in
if (is_user_logged_in()) {
    // Use JavaScript redirect instead of PHP redirect to avoid headers issue
    echo '<script>window.location.href = "' . esc_url(home_url()) . '";</script>';
    exit;
}

// Handle login form submission
$login_error = '';
$login_success = false;
$debug_info = '';

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
                                window.location.href = '<?php echo esc_url(home_url()); ?>';
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