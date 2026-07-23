<?php
/**
 * WooCommerce «Mi cuenta»: etiquetas y ajustes de UI acordes al tema.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'template_redirect', 'glmusic_account_nocache_headers', 0 );
function glmusic_account_nocache_headers() {
	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		nocache_headers();
	}
}

add_filter( 'woocommerce_account_menu_items', 'glmusic_account_menu_labels', 20 );
function glmusic_account_menu_labels( $items ) {
	$labels = array(
		'dashboard'       => 'Panel',
		'orders'          => 'Pedidos',
		'downloads'       => 'Descargas',
		'edit-address'    => 'Direcciones',
		'payment-methods' => 'Métodos de pago',
		'edit-account'    => 'Mi perfil',
		'customer-logout' => 'Cerrar sesión',
	);

	foreach ( $labels as $endpoint => $label ) {
		if ( isset( $items[ $endpoint ] ) ) {
			$items[ $endpoint ] = $label;
		}
	}

	return $items;
}

add_filter( 'woocommerce_get_endpoint_url', 'glmusic_account_logout_endpoint_url', 10, 4 );
function glmusic_account_logout_endpoint_url( $url, $endpoint, $value, $permalink ) {
	if ( 'customer-logout' !== $endpoint || ! function_exists( 'glmusic_logout_url' ) ) {
		return $url;
	}

	return glmusic_logout_url();
}
