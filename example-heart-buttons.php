<?php
/**
 * Ejemplo de uso de botones de favoritos con corazón
 *
 * Este archivo muestra diferentes formas de implementar
 * los botones de corazón en tu tema WordPress.
 */

// Ejemplo 1: Botón básico en un post
function ejemplo_boton_basico() {
    ?>
    <div class="post-actions">
        <h3>Botón básico:</h3>
        <?php gl_the_favorite_heart_button(); ?>
    </div>
    <?php
}

// Ejemplo 2: Botón pequeño en navegación
function ejemplo_boton_navegacion() {
    ?>
    <div class="nav-actions">
        <h3>Botón en navegación:</h3>
        <?php gl_the_favorite_heart_button_small(); ?>
    </div>
    <?php
}

// Ejemplo 3: Botón grande en hero section
function ejemplo_boton_hero() {
    ?>
    <div class="hero-actions">
        <h3>Botón en hero:</h3>
        <?php gl_the_favorite_heart_button_large(); ?>
    </div>
    <?php
}

// Ejemplo 4: Botón flotante en card
function ejemplo_boton_card() {
    ?>
    <div class="card" style="position: relative;">
        <div class="card-image">
            <?php the_post_thumbnail(); ?>
            <?php gl_the_favorite_heart_button(); ?>
        </div>
        <div class="card-content">
            <h3><?php the_title(); ?></h3>
            <p><?php the_excerpt(); ?></p>
        </div>
    </div>
    <?php
}

// Ejemplo 5: Usando shortcode
function ejemplo_shortcode() {
    ?>
    <div class="shortcode-example">
        <h3>Usando shortcode:</h3>
        <?php echo do_shortcode('[gl_favorite_heart]'); ?>

        <h3>Shortcode con parámetros:</h3>
        <?php echo do_shortcode('[gl_favorite_heart post_id="123" size="large"]'); ?>
    </div>
    <?php
}

// Ejemplo 6: Botón con HTML personalizado
function ejemplo_html_personalizado() {
    $html = gl_get_favorite_heart_button();
    ?>
    <div class="custom-html">
        <h3>HTML personalizado:</h3>
        <div class="custom-wrapper">
            <?php echo $html; ?>
        </div>
    </div>
    <?php
}

// Ejemplo 7: Múltiples botones en una lista
function ejemplo_lista_botones() {
    ?>
    <div class="favorites-list">
        <h3>Lista de botones:</h3>
        <?php
        $posts = get_posts(array('numberposts' => 5));
        foreach ($posts as $post) {
            setup_postdata($post);
            ?>
            <div class="list-item">
                <h4><?php echo get_the_title($post->ID); ?></h4>
                <?php gl_the_favorite_heart_button_small($post->ID); ?>
            </div>
            <?php
        }
        wp_reset_postdata();
        ?>
    </div>
    <?php
}

// Ejemplo 8: Botón con contador
function ejemplo_boton_con_contador() {
    $post_id = get_the_ID();
    $count = gl_get_favorite_count($post_id);
    ?>
    <div class="favorite-with-count">
        <h3>Botón con contador:</h3>
        <div class="favorite-wrapper">
            <?php gl_the_favorite_heart_button(); ?>
            <span class="favorite-count"><?php echo $count; ?> favoritos</span>
        </div>
    </div>
    <?php
}

// Ejemplo 9: Botón condicional
function ejemplo_boton_condicional() {
    $post_type = get_post_type();

    // Solo mostrar en ciertos tipos de post
    if (in_array($post_type, array('post', 'cursos'))) {
        ?>
        <div class="conditional-favorite">
            <h3>Botón condicional (solo en posts y cursos):</h3>
            <?php gl_the_favorite_heart_button(); ?>
        </div>
        <?php
    }
}

// Ejemplo 10: Botón con estilos personalizados
function ejemplo_boton_estilos_personalizados() {
    ?>
    <div class="custom-styles">
        <h3>Botón con estilos personalizados:</h3>
        <style>
            .custom-styles .favorite-heart-btn {
                background: linear-gradient(45deg, #ff6b6b, #ee5a24);
                border-radius: 25px;
                padding: 12px 20px;
            }
            .custom-styles .favorite-heart-btn:hover {
                background: linear-gradient(45deg, #ee5a24, #ff6b6b);
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(238, 90, 36, 0.3);
            }
            .custom-styles .favorite-heart-btn.favorited {
                background: linear-gradient(45deg, #e74c3c, #c0392b);
            }
        </style>
        <?php gl_the_favorite_heart_button(); ?>
    </div>
    <?php
}

// Función para mostrar todos los ejemplos
function mostrar_todos_los_ejemplos() {
    ?>
    <div class="favorites-examples">
        <h1>Ejemplos de Botones de Favoritos con Corazón</h1>

        <?php ejemplo_boton_basico(); ?>
        <hr>

        <?php ejemplo_boton_navegacion(); ?>
        <hr>

        <?php ejemplo_boton_hero(); ?>
        <hr>

        <?php ejemplo_boton_card(); ?>
        <hr>

        <?php ejemplo_shortcode(); ?>
        <hr>

        <?php ejemplo_html_personalizado(); ?>
        <hr>

        <?php ejemplo_lista_botones(); ?>
        <hr>

        <?php ejemplo_boton_con_contador(); ?>
        <hr>

        <?php ejemplo_boton_condicional(); ?>
        <hr>

        <?php ejemplo_boton_estilos_personalizados(); ?>
    </div>
    <?php
}

// Para usar en un template, simplemente llama:
// mostrar_todos_los_ejemplos();

// O para usar individualmente:
// ejemplo_boton_basico();
// ejemplo_boton_navegacion();
// etc.
?>