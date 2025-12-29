<?php
/**
 * "Order received" message.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/order-received.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.8.0
 *
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;
?>
<style>
	.woocommerce-thankyou-order-received{
		display: none!important;
	}
</style>
<div class="thank-your-details">
	<!-- <img class="thank-your-details-img" src="https://paula.wayke.com.pe/wp-content/uploads/2025/07/arrangement1-1-1.png" alt="Gracias por tu compra"> -->
	<div class="thank-your-details__text">
		<h2>Gracias por tu compra</h2>
		<p>Te compartimos un correo con el detalle de tu compra.</p>
		<div class="thank-your-details__text__buttons">
		<a href="<?php echo home_url(); ?>/tienda" class="btn__primary">Realizar otra compra</a>
		<a href="<?php echo home_url(); ?>" class="btn__secondary">Volver al inicio</a>
		</div>
	</div>
	<div class="thank-your-details__image">
		<img src="<?php echo get_template_directory_uri(); ?>/images/thank-you-image.jpg" alt="Thank you">
	</div>
</div>
<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
	<?php
	/**
	 * Filter the message shown after a checkout is complete.
	 *
	 * @since 2.2.0
	 *
	 * @param string         $message The message.
	 * @param WC_Order|false $order   The order created during checkout, or false if order data is not available.
	 */
	$message = apply_filters(
		'woocommerce_thankyou_order_received_text',
		esc_html( __( 'Thank you. Your order has been received.', 'woocommerce' ) ),
		$order
	);

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $message;
	?>
</p>


