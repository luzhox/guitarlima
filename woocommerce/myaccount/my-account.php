<div class="woocommerce-menu">

<h3 class="woocommerce-menu__user">
	<span class="woocommerce-menu__user-kicker"><?php esc_html_e( 'Tu cuenta', 'woocommerce' ); ?></span>
	<span class="woocommerce-menu__user-name"><?php echo esc_html( $current_user->display_name ); ?></span>
</h3>
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
