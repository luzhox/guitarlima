<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * GL Music — Admin UI Futurista
 * Transforma el wp-admin completo con la paleta de guitarlima:
 *   #0a0b12 (fondo profundo)  |  #00aeff (azul neón)  |  #FFC600 (dorado)
 */

/* ══════════════════════════════════════════
   1. ENQUEUE CSS ADMIN GLOBAL
══════════════════════════════════════════ */
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_style(
        'gl-admin-ui',
        get_template_directory_uri() . '/admin/admin.css',
        [],
        wp_get_theme()->get( 'Version' )
    );
} );

/* ══════════════════════════════════════════
   2. BODY CLASS — agrega clase base
══════════════════════════════════════════ */
add_filter( 'admin_body_class', function( $classes ) {
    return $classes . ' gl-admin';
} );

/* ══════════════════════════════════════════
   3. QUITAR LOGO WP DE LA ADMIN BAR
      y reemplazar con logo de GL Music
══════════════════════════════════════════ */
add_action( 'admin_bar_menu', function( WP_Admin_Bar $bar ) {
    $bar->remove_node( 'wp-logo' );

    $logo_url = get_template_directory_uri() . '/images/logo.svg';
    $bar->add_node( [
        'id'    => 'gl-logo',
        'title' => '<img src="' . esc_url( 'https://glmusic.pe/wp-content/uploads/2025/07/logo-white.svg' ) . '" style="height:22px;width:auto;vertical-align:middle;filter:brightness(0) invert(1);opacity:.85;margin-top:-2px" alt="GL Music">',
        'href'  => home_url(),
        'meta'  => [ 'title' => 'GL Music — Ir al sitio' ],
    ] );
}, 99 );

/* ══════════════════════════════════════════
   4. FOOTER PERSONALIZADO
══════════════════════════════════════════ */
add_filter( 'admin_footer_text', function() {
    return '<span style="color:#3a3f5c;font-size:.78rem">⚡ GL Music Admin — Diseñado con ♥</span>';
} );
add_filter( 'update_footer', '__return_empty_string', 99 );

/* ══════════════════════════════════════════
   5. ESQUEMA DE COLOR WP — fuerza el oscuro
      para usuarios nuevos
══════════════════════════════════════════ */
add_action( 'user_register', function( $user_id ) {
    update_user_meta( $user_id, 'admin_color', 'midnight' );
} );

/* ══════════════════════════════════════════
   6. OCULTAR ELEMENTOS INNECESARIOS
══════════════════════════════════════════ */
add_action( 'admin_head', function() {
    // Ocultar avisos de "Bienvenido a WordPress" del dashboard
    remove_action( 'welcome_panel', 'wp_welcome_panel' );

    echo '<style>
        /* Ocultar botón mostrar panel bienvenida */
        #wp-admin-bar-wp-logo { display: none !important; }
        .welcome-panel-close { display: none !important; }
        /* Ocultar notas al pie de WP */
        #wpfooter .update-nag { display: none !important; }
    </style>';
} );

/* ══════════════════════════════════════════
   7. DASHBOARD — Quitar widgets por defecto
      que no aportan valor
══════════════════════════════════════════ */
add_action( 'wp_dashboard_setup', function() {
    // Solo para no-admins ocultamos todo
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    // Desactivar el widget "Noticias de WordPress"
    remove_meta_box( 'dashboard_primary',   'dashboard', 'side' );
    remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
} );

/* ══════════════════════════════════════════
   8. PANTALLA DE LOGIN PERSONALIZADA
══════════════════════════════════════════ */
// CSS del login
add_action( 'login_enqueue_scripts', function() {
    wp_enqueue_style(
        'gl-login-ui',
        get_template_directory_uri() . '/admin/login.css',
        [],
        wp_get_theme()->get( 'Version' )
    );
} );

// Logo del login → redirige al home
add_filter( 'login_headerurl',  fn() => home_url() );
add_filter( 'login_headertext', fn() => get_bloginfo( 'name' ) );

// Título personalizado en <title>
add_filter( 'login_title', function( $title ) {
    return get_bloginfo( 'name' ) . ' — Acceso';
} );
