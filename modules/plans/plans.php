<?php
/**
 * Módulo Plans. Integrado con GL Music Subscriptions (plugin glmusic-subscriptions).
 *
 * Si la fila ACF tiene seleccionado un "gls_plan" (CPT del plugin), el botón
 * se construye automáticamente hacia la página de checkout con ?plan=ID.
 * En caso contrario, se usa el botón ACF tradicional.
 */

// URL base de la página dedicada al checkout Pro.
$gls_checkout_url = function_exists('guitarlima_plan_pro_checkout_url')
  ? guitarlima_plan_pro_checkout_url()
  : home_url('/comprar-plan-pro/');
?>
<div class="plans">
  <div class="container">
    <div class="plans__title">
      <h1><?php the_sub_field('title'); ?></h1>
    </div>
    <div class="plans__content">
      <?php if (have_rows('plans')):
        while (have_rows('plans')):
          the_row();

          $plan_card_title = (string) get_sub_field('title');

          // Nuevo campo ACF opcional: gls_plan (post_object → gls_plan).
          $gls_plan = get_sub_field('gls_plan');
          $button = get_sub_field('button');
          $gls_plan_id = 0;
          $gls_plan_post = null;
          if ($gls_plan) {
            $gls_plan_id = is_object($gls_plan) ? $gls_plan->ID : intval($gls_plan);
            $gls_plan_post = get_post($gls_plan_id);
          }

          if (!$gls_plan_id && is_array($button) && !empty($button['url'])) {
            $button_query = [];
            parse_str((string) wp_parse_url($button['url'], PHP_URL_QUERY), $button_query);
            if (!empty($button_query['subscription_plan'])) {
              $gls_plan_id = absint($button_query['subscription_plan']);
              $gls_plan_post = get_post($gls_plan_id);
            }
          }

          if ($gls_plan_id && (!$gls_plan_post || $gls_plan_post->post_type !== 'gls_plan')) {
            $gls_plan_id = 0;
            $gls_plan_post = null;
          }

          if (!$gls_plan_id && stripos($plan_card_title, 'pro') !== false && function_exists('guitarlima_default_pro_plan_id')) {
            $gls_plan_id = guitarlima_default_pro_plan_id();
            $gls_plan_post = get_post($gls_plan_id);
          }

          // Precio/intervalo auto-detectado desde el plan del plugin (útil para mostrar).
          $gls_price_label = '';
          if ($gls_plan_post && $gls_plan_post->post_type === 'gls_plan') {
            $p = number_format(floatval(get_post_meta($gls_plan_id, '_gls_price', true)), 2);
            $u = get_post_meta($gls_plan_id, '_gls_interval_unit', true) ?: 'MONTH';
            $c = intval(get_post_meta($gls_plan_id, '_gls_interval_count', true) ?: 1);
            $um = ['DAY' => 'día', 'WEEK' => 'semana', 'MONTH' => 'mes', 'YEAR' => 'año'][$u] ?? strtolower($u);
            $currency = class_exists('GLS_Settings') ? GLS_Settings::get('currency') : 'USD';
            $gls_price_label = $currency . ' ' . $p . ' / ' . ($c > 1 ? $c . ' ' : '') . $um;
          }
          ?>
          <div class="plans__content__item">
            <div class="plans__content__item__text">
              <h3><?php echo esc_html($plan_card_title); ?></h3>

              <?php if ($gls_price_label): ?>
                <p class="plans__content__item__price"><strong><?php echo esc_html($gls_price_label); ?></strong></p>
              <?php endif; ?>

              <?php the_sub_field('content'); ?>
            </div>

            <div class="plans__content__item__button">
              <?php
              if ($gls_plan_id && $gls_checkout_url):
                // Si el usuario ya está suscrito activo a este plan mostramos mensaje.
                $already = is_user_logged_in()
                  && class_exists('GLS_Subscriptions')
                  && GLS_Subscriptions::user_has_active(get_current_user_id(), [$gls_plan_id]);

                if ($already): ?>
                  <span class="btn__primary is-disabled">Ya estás suscrito</span>
                  <a href="/mi-cuenta/" class="plans__account-link">Ir a Mi cuenta</a>
                <?php else:
                  // Va a la página dedicada al checkout. Si es invitado, el
                  // shortcode [gls-checkout] muestra registro/login sin salir.
                  $url = add_query_arg('plan', $gls_plan_id, $gls_checkout_url);
                  $btn_label = (is_array($button) && !empty($button['title'])) ? $button['title'] : 'Suscribirme';
                  ?>
                  <a href="<?php echo esc_url($url); ?>" class="btn__primary"><?php echo esc_html($btn_label); ?></a>
                <?php endif;
              else:
                // Fallback: botón ACF clásico.
                if ($button): ?>
                  <a href="<?php echo esc_url($button['url']); ?>" class="btn__primary"
                    target="<?php echo esc_attr($button['target']); ?>">
                    <?php echo esc_html($button['title']); ?>
                  </a>
                <?php endif;
              endif; ?>
            </div>
          </div>
        <?php endwhile;
      endif; ?>
    </div>
  </div>
</div>
