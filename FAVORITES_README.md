# Sistema de Favoritos - Botones de Corazón

Este documento describe cómo usar los nuevos botones de favoritos con ícono de corazón.

## Funciones Disponibles

### 1. Botón de Corazón Básico

```php
// Mostrar botón de corazón
gl_the_favorite_heart_button();

// Obtener HTML del botón
echo gl_get_favorite_heart_button();

// Con post ID específico
gl_the_favorite_heart_button(123);
```

### 2. Botones de Corazón con Diferentes Tamaños

```php
// Botón pequeño
gl_the_favorite_heart_button_small();
echo gl_get_favorite_heart_button_small();

// Botón grande
gl_the_favorite_heart_button_large();
echo gl_get_favorite_heart_button_large();
```

### 3. Shortcode

```php
// Shortcode básico
[gl_favorite_heart]

// Con parámetros
[gl_favorite_heart post_id="123" size="large"]
```

## Uso en Templates

### En single.php o single-cursos.php
```php
<div class="post-actions">
    <?php gl_the_favorite_heart_button(); ?>
</div>
```

### En cards o listas
```php
<div class="card">
    <div class="card-image">
        <?php the_post_thumbnail(); ?>
        <?php gl_the_favorite_heart_button(); ?>
    </div>
    <div class="card-content">
        <h3><?php the_title(); ?></h3>
    </div>
</div>
```

### En navegación
```php
<div class="nav-actions">
    <?php gl_the_favorite_heart_button_small(); ?>
</div>
```

## Clases CSS Disponibles

### Tamaños
- `.favorite-heart-btn` - Tamaño medio (24px)
- `.favorite-heart-btn--small` - Tamaño pequeño (18px)
- `.favorite-heart-btn--large` - Tamaño grande (32px)

### Estados
- `.favorite-heart-btn--login` - Estado cuando no está logueado
- `.favorite-heart-btn--floating` - Para usar en cards flotantes
- `.favorite-heart-btn--nav` - Para usar en navegación

### Estados Dinámicos
- `.favorited` - Cuando el post está en favoritos
- `.loading` - Durante la carga AJAX

## Características

- ✅ Ícono SVG de corazón
- ✅ Animación de latido al agregar/quitar
- ✅ Estados visuales claros
- ✅ Responsive design
- ✅ Accesibilidad con títulos
- ✅ Sincronización con botones de texto existentes
- ✅ Diferentes tamaños disponibles
- ✅ Estilos personalizables

## Personalización

### Cambiar colores
```scss
.favorite-heart-btn {
  &.favorited {
    .favorite-heart-icon {
      color: #tu-color;
      fill: #tu-color;
    }
  }
}
```

### Cambiar animación
```scss
@keyframes heartBeat {
  // Personalizar animación aquí
}
```

## Compatibilidad

Los nuevos botones de corazón son completamente compatibles con el sistema de favoritos existente:

- Usan las mismas funciones AJAX
- Se sincronizan con los botones de texto
- Mantienen la misma base de datos
- Funcionan con el mismo sistema de autenticación
