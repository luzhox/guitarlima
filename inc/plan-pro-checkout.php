<?php
/**
 * Virtual checkout page for the Pro subscription flow.
 */

if (!defined('ABSPATH')) {
    exit;
}

function guitarlima_plan_pro_checkout_slug() {
    return 'comprar-plan-pro';
}

function guitarlima_plan_pro_checkout_url($plan_id = 0) {
    $url = home_url('/' . guitarlima_plan_pro_checkout_slug() . '/');

    if ($plan_id) {
        $url = add_query_arg('plan', absint($plan_id), $url);
    }

    return $url;
}

function guitarlima_default_pro_plan_id() {
    /**
     * Default local GLSubs Pro plan id.
     *
     * Keep filterable so production/staging can override this without editing
     * the checkout template.
     */
    return (int) apply_filters('guitarlima_default_pro_plan_id', 781);
}

function guitarlima_is_plan_pro_checkout_request() {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $path = trim((string) $path, '/');

    return $path === guitarlima_plan_pro_checkout_slug();
}

add_action('wp_enqueue_scripts', function () {
    if (!guitarlima_is_plan_pro_checkout_request()) {
        return;
    }

    if (wp_script_is('gls-checkout', 'registered')) {
        wp_enqueue_script('gls-checkout');
    }

    if (wp_style_is('gls-checkout', 'registered')) {
        wp_enqueue_style('gls-checkout');
    }
}, 20);

add_filter('wp_title', function ($title) {
    if (guitarlima_is_plan_pro_checkout_request()) {
        return 'Compra Plan Pro';
    }

    return $title;
}, 20);

add_filter('body_class', function ($classes) {
    if (guitarlima_is_plan_pro_checkout_request()) {
        $classes[] = 'plan-pro-checkout-page';
    }

    return $classes;
});

add_action('template_redirect', function () {
    if (!guitarlima_is_plan_pro_checkout_request()) {
        return;
    }

    global $wp_query;
    if ($wp_query) {
        $wp_query->is_404 = false;
    }

    status_header(200);
    nocache_headers();

    locate_template('templates/plan-pro-checkout.php', true, true);
    exit;
}, 0);
