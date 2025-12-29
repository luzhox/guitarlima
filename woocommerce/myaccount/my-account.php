<div class="woocommerce-menu">

<h3 class="woocommerce-menu__user">	<?php
	$allowed_html = array(
		'strong' => array(),
		'em'     => array(),
		'b'       => array(),
		'i'       => array(),
	);
	printf(
		/* translators: 1: user display name 2: logout url */
		wp_kses( __( 'Bienvenido %1$s', 'woocommerce' ), $allowed_html ),
		'<strong>' . esc_html( $current_user->display_name ) . '</strong>',
		esc_url( wc_logout_url() )
	);
	?></h3>
<?php do_action( 'woocommerce_account_navigation' ); ?>
</div>
<div class="woocommerce-MyAccount-content">
	<?php
		/**
		 * My Account content.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_account_content' );
	?>
</div>
