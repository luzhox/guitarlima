<?php
/**
 * Template Name: Login Page
 *
 * Template específico para la página de login
 * con estilos personalizados similares a mi-cuenta
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Si el usuario ya está logueado, redirigir
if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

get_header();
include('menu.php');
?>

<?php
while (have_posts()) {
    the_post();

    $page_modules = function_exists('get_field') ? get_field('modules') : null;

    if (!empty($page_modules)) {
        the_modules_loop();
        continue;
    }
    ?>

    <div class="user-zone user-zone--login">
        <div class="container">
            <?php if (get_the_title()): ?>
                <h1 class="login-page-title"><?php the_title(); ?></h1>
            <?php endif; ?>

            <div class="login-page-form-wrapper">
                <?php
                // Mostrar contenido de la página si existe
                $content = get_the_content();
                if (!empty(trim($content))) {
                    echo '<div class="page-content">';
                    the_content();
                    echo '</div>';
                } elseif (function_exists('pms_get_login_form')) {
                    echo do_shortcode('[pms-login]');
                } else {
                    // Fallback: mostrar formulario de login básico de WordPress
                    wp_login_form(array(
                        'redirect' => home_url(),
                        'form_id' => 'loginform',
                        'label_username' => 'Usuario o Email',
                        'label_password' => 'Contraseña',
                        'label_remember' => 'Recordarme',
                        'label_log_in' => 'Iniciar Sesión',
                        'id_username' => 'user_login',
                        'id_password' => 'user_pass',
                        'id_remember' => 'rememberme',
                        'id_submit' => 'wp-submit',
                        'remember' => true,
                        'value_username' => '',
                        'value_remember' => false
                    ));
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}
?>

<script>
    jQuery(document).ready(function ($) {
        // Traducir textos del formulario pms-login al español
        // Labels
        $('.pms-form label[for*="user_login"], .pms-form .login-username label').each(function () {
            var text = $(this).text().trim();
            if (text.includes('Username') || text.includes('Email')) {
                $(this).text('USUARIO O EMAIL');
            }
        });

        $('.pms-form label[for*="user_pass"], .pms-form .login-password label').each(function () {
            var text = $(this).text().trim();
            if (text.includes('Password')) {
                $(this).text('CONTRASEÑA');
            }
        });

        // Remember me
        $('.pms-form .login-remember label').each(function () {
            var text = $(this).text().trim();
            if (text.includes('Remember')) {
                $(this).html($(this).html().replace(/Remember\s*Me/gi, 'Recordarme'));
            }
        });

        // Botón de login
        $('.pms-form .login-submit input[type="submit"], .pms-form .login-submit button').each(function () {
            var text = $(this).val() || $(this).text();
            if (text.includes('Log In') || text.includes('Login')) {
                if ($(this).is('input')) {
                    $(this).val('Iniciar Sesión');
                } else {
                    $(this).text('Iniciar Sesión');
                }
            }
        });

        // Links de registro y password
        $('.pms-form .login-extra a').each(function () {
            var text = $(this).text().trim();
            if (text.includes('Register')) {
                $(this).text('Registrarse');
            }
            if (text.includes('Lost') || text.includes('password')) {
                $(this).text('¿Olvidaste tu contraseña?');
            }
        });

        // Placeholders
        $('.pms-form input[type="text"], .pms-form input[type="email"]').each(function () {
            var placeholder = $(this).attr('placeholder');
            if (placeholder && (placeholder.includes('Username') || placeholder.includes('Email'))) {
                $(this).attr('placeholder', 'Usuario o Email');
            }
        });

        $('.pms-form input[type="password"]').each(function () {
            var placeholder = $(this).attr('placeholder');
            if (placeholder && placeholder.includes('Password')) {
                $(this).attr('placeholder', 'Contraseña');
            }
        });
    });
</script>

<?php get_footer(); ?>
