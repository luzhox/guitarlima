# 🔐 Login Modal Component

Un componente de login modal moderno y responsive para WordPress que se puede usar en cualquier parte del sitio.

## ✨ Características

- **Modal moderno** con diseño glassmorphism
- **AJAX login** sin recarga de página
- **Responsive design** para móviles y tablets
- **Animaciones suaves** de entrada y salida
- **Toggle de contraseña** para mostrar/ocultar
- **Checkbox personalizado** para "Recordarme"
- **Cierre múltiple**: X, overlay, ESC
- **Dark mode** automático
- **JavaScript moderno** con `const` y `let`

## 📁 Estructura de Archivos

```
modules/
├── login-modal/
│   └── login-modal.php          # Componente principal
styles/sass/components/
├── _login-modal.scss            # Estilos SCSS
example-login-modal-usage.php    # Ejemplo de uso
```

## 🚀 Instalación

### 1. Incluir el Componente

En cualquier template o página donde quieras usar el modal:

```php
<?php include get_template_directory() . '/modules/login-modal/login-modal.php'; ?>
```

### 2. Verificar Dependencias

Asegúrate de que los estilos estén compilados:

```bash
# Si usas webpack o similar
npm run build
# o
yarn build
```

## 💻 Uso Básico

### Opción 1: Botón por Defecto

El componente incluye un botón de trigger por defecto:

```php
<?php include get_template_directory() . '/modules/login-modal/login-modal.php'; ?>
```

### Opción 2: Botón Personalizado

Crea tu propio botón y haz que abra el modal:

```html
<button id="mi-boton-login" class="mi-clase">
    🔐 Mi Botón de Login
</button>

<script>
jQuery(document).ready(function($) {
    $('#mi-boton-login').on('click', function(e) {
        e.preventDefault();
        $('#login-modal-trigger').click();
    });
});
</script>
```

### Opción 3: Enlace Personalizado

```html
<a href="#" id="login-link">Iniciar Sesión</a>

<script>
jQuery(document).ready(function($) {
    $('#login-link').on('click', function(e) {
        e.preventDefault();
        $('#login-modal-trigger').click();
    });
});
</script>
```

### Opción 4: Programáticamente

```javascript
// Abrir modal
$('#login-modal-trigger').click();

// O directamente
$('#login-modal-overlay').addClass('active');
```

## 🎨 Personalización

### Cambiar Colores del Modal

Edita `styles/sass/components/_login-modal.scss`:

```scss
.login-modal-container {
    background: linear-gradient(135deg, #tu-color-1 0%, #tu-color-2 100%);
}
```

### Cambiar Estilo del Botón Trigger

```scss
.login-modal-trigger {
    background: linear-gradient(45deg, #tu-color-1, #tu-color-2);
    // ... más estilos
}
```

### Personalizar Animaciones

```scss
@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.9) rotate(5deg);
    }
    to {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }
}
```

## 🔧 Configuración AJAX

El modal usa el mismo endpoint AJAX que el login normal:

```php
// En functions.php (ya configurado)
add_action('wp_ajax_nopriv_custom_login', 'handle_custom_login');
add_action('wp_ajax_custom_login', 'handle_custom_login');
```

## 📱 Responsive Design

El modal se adapta automáticamente a diferentes tamaños de pantalla:

- **Desktop**: 400px máximo
- **Tablet**: 90% del ancho
- **Mobile**: 95% del ancho con márgenes

## 🎯 Eventos JavaScript

### Eventos Disponibles

```javascript
// Modal abierto
$('#login-modal-overlay').on('show.bs.modal', function() {
    console.log('Modal abierto');
});

// Modal cerrado
$('#login-modal-overlay').on('hidden.bs.modal', function() {
    console.log('Modal cerrado');
});

// Login exitoso
$('#login-modal-form').on('submit', function(e) {
    // El modal maneja automáticamente la respuesta
});
```

### Métodos Personalizados

```javascript
// Abrir modal con callback
function openLoginModal(callback) {
    $('#login-modal-trigger').click();
    if (callback) callback();
}

// Cerrar modal con callback
function closeLoginModal(callback) {
    $('#login-modal-close').click();
    if (callback) callback();
}
```

## 🎨 Temas y Variaciones

### Tema Oscuro

El modal incluye soporte automático para dark mode:

```scss
@media (prefers-color-scheme: dark) {
    .login-modal-container {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }
}
```

### Tema Personalizado

```scss
.login-modal-container {
    &.tema-personalizado {
        background: linear-gradient(135deg, #tu-color-1 0%, #tu-color-2 100%);
    }
}
```

## 🔒 Seguridad

- **Nonce verification** (si se implementa)
- **Input sanitization** en el servidor
- **AJAX endpoint** seguro
- **XSS protection** con escape de datos

## 🐛 Troubleshooting

### Modal no se abre

1. Verifica que jQuery esté cargado
2. Revisa la consola del navegador por errores
3. Asegúrate de que el componente esté incluido

### Estilos no se cargan

1. Verifica que el SCSS esté compilado
2. Revisa que `_login-modal.scss` esté importado en `style.scss`
3. Limpia la caché del navegador

### AJAX no funciona

1. Verifica que el endpoint esté configurado en `functions.php`
2. Revisa los logs de error de WordPress
3. Verifica que el usuario no esté ya logueado

## 📋 Ejemplos de Uso

### En el Header

```php
// En header.php
<div class="header-actions">
    <?php if (!is_user_logged_in()): ?>
        <?php include get_template_directory() . '/modules/login-modal/login-modal.php'; ?>
    <?php else: ?>
        <a href="<?php echo wp_logout_url(); ?>">Cerrar Sesión</a>
    <?php endif; ?>
</div>
```

### En el Footer

```php
// En footer.php
<div class="footer-login">
    <?php include get_template_directory() . '/modules/login-modal/login-modal.php'; ?>
</div>
```

### En una Página Específica

```php
// En page-login.php
<div class="login-page">
    <h1>Bienvenido</h1>
    <p>Haz clic en el botón para iniciar sesión:</p>
    <?php include get_template_directory() . '/modules/login-modal/login-modal.php'; ?>
</div>
```

## 🔄 Actualizaciones

Para actualizar el componente:

1. Reemplaza `modules/login-modal/login-modal.php`
2. Actualiza `styles/sass/components/_login-modal.scss`
3. Recompila los estilos
4. Limpia la caché

## 📞 Soporte

Si tienes problemas:

1. Revisa la consola del navegador
2. Verifica los logs de WordPress
3. Asegúrate de que todas las dependencias estén cargadas
4. Prueba en un navegador diferente

## 🎉 ¡Listo!

El componente de login modal está listo para usar. Es moderno, seguro y fácil de personalizar. ¡Disfruta de una experiencia de login fluida y profesional!