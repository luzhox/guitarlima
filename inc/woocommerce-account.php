<?php
/**
 * WooCommerce «Mi cuenta»: etiquetas y ajustes de UI acordes al tema.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
