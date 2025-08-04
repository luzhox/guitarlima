<?php
// Check if user is logged in
if (!is_user_logged_in()) {
    echo '<div class="mis-favoritos">';
    echo '<h1>Mis favoritos</h1>';
    echo '<div class="mis-favoritos__container">';
    // echo '<p class="login-required">Debes <a href="' . wp_login_url(get_permalink()) . '">iniciar sesión</a> para ver tus favoritos.</p>';
    echo '</div>';
    echo '</div>';
    return;
}

$user_id = get_current_user_id();
$favorites = gl_get_user_favorites($user_id);
?>
<style>
    #masthead{
        background: linear-gradient(105.9deg, #140f2f 24.4%, #0b0a10 80.5%);
        border-bottom: 1px solid rgba(255, 255, 255, 0.38);
    }
	.course-reproduction{
		display:none!important;
	}
</style>
<div class="mis-favoritos">
    <h1>Mis favoritos</h1>

    <?php if (!empty($favorites)): ?>
        <div class="favorites-filters">
            <button class="filter-btn active" data-type="">Todos</button>
            <button class="filter-btn" data-type="cursos">Canciones</button>
            <button class="filter-btn" data-type="cursos-wp">Cursos</button>
            <button class="filter-btn" data-type="libreria">Librerias</button>

        </div>
    <?php endif; ?>

    <div class="mis-favoritos__container">
        <?php if (empty($favorites)): ?>
            <p class="no-favorites">No tienes favoritos guardados.</p>
        <?php else: ?>
            <div class="favorites-grid">
                <?php foreach ($favorites as $favorite):
                    $post = get_post($favorite->post_id);
                    if ($post):
                        $thumbnail = get_the_post_thumbnail_url($favorite->post_id, 'medium');
                ?>
                    <div class="favorite-item" data-post-id="<?php echo $favorite->post_id; ?>">
                        <div class="favorite-item__image">
                            <?php if ($thumbnail): ?>
                                <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr(get_the_title($favorite->post_id)); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="favorite-item__content">
                            <h3><a href="<?php echo get_permalink($favorite->post_id); ?>"><?php echo get_the_title($favorite->post_id); ?></a></h3>
                            <p><?php echo wp_trim_words(get_the_excerpt($favorite->post_id), 20); ?></p>
                            <div class="favorite-item__actions">
                                <a href="<?php echo get_permalink($favorite->post_id); ?>" class="btn__primary--border">Reproducir</a>
                                <button class="btn__primary--border remove-favorite-btn" data-post-id="<?php echo $favorite->post_id; ?>">Eliminar de favoritos</button>
                            </div>
                        </div>
                    </div>
                <?php
                    endif;
                endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>