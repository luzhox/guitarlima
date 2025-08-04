<?php
/**
 * Example: How to use the Login Modal Component
 *
 * This file shows how to include and use the login modal
 * in any WordPress page or template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ejemplo: Login Modal</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="container">
    <h1>Ejemplo de Login Modal</h1>

    <p>Este es un ejemplo de cómo usar el componente de login modal en cualquier página.</p>

    <!-- Opción 1: Botón de trigger simple -->
    <?php include get_template_directory() . '/modules/login-modal/login-modal.php'; ?>

    <!-- Opción 2: Botón personalizado que abre el modal -->
    <button id="custom-login-btn" class="custom-login-button">
        🔐 Iniciar Sesión Personalizado
    </button>

    <!-- Opción 3: Enlace que abre el modal -->
    <a href="#" id="login-link" class="login-link">
        ¿Ya tienes cuenta? Inicia sesión aquí
    </a>

    <div class="content">
        <h2>Contenido de la página</h2>
        <p>El modal se puede abrir desde cualquier parte de la página usando el botón de trigger o programáticamente.</p>

        <div class="buttons-example">
            <h3>Diferentes formas de abrir el modal:</h3>
            <ul>
                <li><strong>Botón por defecto:</strong> El botón que viene con el componente</li>
                <li><strong>Botón personalizado:</strong> Cualquier botón con ID personalizado</li>
                <li><strong>Enlace:</strong> Cualquier enlace que quieras que abra el modal</li>
                <li><strong>Programáticamente:</strong> Desde JavaScript con <code>$('#login-modal-trigger').click()</code></li>
            </ul>
        </div>
    </div>
</div>

<style>
/* Estilos para el ejemplo */
.container {
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    font-family: Arial, sans-serif;
}

.custom-login-button {
    background: linear-gradient(45deg, #ff6b6b, #ee5a24);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 25px;
    cursor: pointer;
    font-size: 16px;
    margin: 10px;
    transition: all 0.3s ease;
}

.custom-login-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
}

.login-link {
    display: inline-block;
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    margin: 10px;
    padding: 8px 16px;
    border: 2px solid #667eea;
    border-radius: 20px;
    transition: all 0.3s ease;
}

.login-link:hover {
    background: #667eea;
    color: white;
}

.content {
    margin-top: 40px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.buttons-example {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}

.buttons-example ul {
    list-style: none;
    padding: 0;
}

.buttons-example li {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.buttons-example li:last-child {
    border-bottom: none;
}

code {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Ejemplo 1: Botón personalizado que abre el modal
    $('#custom-login-btn').on('click', function(e) {
        e.preventDefault();
        $('#login-modal-trigger').click(); // Simula el click en el botón original
    });

    // Ejemplo 2: Enlace que abre el modal
    $('#login-link').on('click', function(e) {
        e.preventDefault();
        $('#login-modal-trigger').click(); // Simula el click en el botón original
    });

    // Ejemplo 3: Abrir modal programáticamente después de 3 segundos
    // setTimeout(function() {
    //     $('#login-modal-trigger').click();
    // }, 3000);

    // Ejemplo 4: Abrir modal cuando el usuario hace scroll hasta cierto punto
    // $(window).on('scroll', function() {
    //     if ($(window).scrollTop() > 500) {
    //         $('#login-modal-trigger').click();
    //         $(window).off('scroll'); // Solo una vez
    //     }
    // });
});
</script>

<?php wp_footer(); ?>
</body>
</html>