<?php
$plan_id = absint($_GET['plan'] ?? 0);

if (!$plan_id) {
    $plan_id = guitarlima_default_pro_plan_id();
}

$plan = get_post($plan_id);
$is_valid_gls_plan = $plan && $plan->post_type === 'gls_plan';

$plan_title = $is_valid_gls_plan ? get_the_title($plan_id) : 'Plan Pro';
$price = $is_valid_gls_plan ? floatval(get_post_meta($plan_id, '_gls_price', true)) : 15;
$currency = class_exists('GLS_Settings') ? GLS_Settings::get('currency') : 'USD';
?>
<?php get_header(); ?>
<?php include(get_template_directory() . '/menu.php'); ?>

<main class="plan-pro-checkout" role="main">
    <div class="container">
        <a class="plan-pro-checkout__back" href="<?php echo esc_url(home_url('/planes/')); ?>">Volver a planes</a>

        <div class="plan-pro-checkout__grid">
            <section class="plan-pro-checkout__summary" aria-labelledby="plan-pro-title">
                <p class="plan-pro-checkout__eyebrow">Membresia GL Music</p>
                <h1 id="plan-pro-title">Completa tu compra del <?php echo esc_html($plan_title); ?></h1>
                <p class="plan-pro-checkout__lead">
                    Accede a todo el catalogo de cursos, canciones y librerias disponibles en GL Music.
                </p>

                <div class="plan-pro-checkout__price">
                    <span><?php echo esc_html($currency); ?></span>
                    <strong><?php echo esc_html(number_format($price, 2)); ?></strong>
                    <small>/ mes</small>
                </div>

                <ul class="plan-pro-checkout__features">
                    <li>Catalogo completo de cursos y librerias.</li>
                    <li>Favoritos para guardar canciones, cursos y materiales.</li>
                    <li>Pago seguro conectado a PayPal por GLSubs.</li>
                    <li>Gestion de suscripcion desde tu cuenta.</li>
                </ul>
            </section>

            <section class="plan-pro-checkout__form" aria-label="Checkout Plan Pro">
                <?php if ($is_valid_gls_plan && shortcode_exists('gls-checkout')): ?>
                    <?php echo do_shortcode('[gls-checkout plan_id="' . esc_attr($plan_id) . '"]'); ?>
                <?php else: ?>
                    <div class="plan-pro-checkout__notice">
                        <h2>Plan no disponible</h2>
                        <p>No pudimos encontrar el plan seleccionado. Vuelve a planes e intentalo nuevamente.</p>
                        <a class="btn__primary" href="<?php echo esc_url(home_url('/planes/')); ?>">Ver planes</a>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>

<?php get_footer(); ?>
