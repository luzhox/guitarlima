<?php
/**
 * My Account Dashboard
 *
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user();
$display_name = $current_user->display_name ?: $current_user->user_login;
$member_since = $current_user->user_registered ? date_i18n( 'F Y', strtotime( $current_user->user_registered ) ) : '';
$active_subscriptions = [];

if ( class_exists( 'GLS_Subscriptions' ) ) {
	$active_subscriptions = GLS_Subscriptions::get_active_for_user( get_current_user_id() );
}

$primary_subscription = ! empty( $active_subscriptions ) ? $active_subscriptions[0] : null;
$subscription_status = __( 'Sin plan activo', 'woocommerce' );
$subscription_label = __( 'Explora el Plan Pro para desbloquear cursos y librerías.', 'woocommerce' );

if ( $primary_subscription ) {
	$plan_title = get_the_title( (int) $primary_subscription->plan_id );
	$subscription_status = $primary_subscription->status === 'active'
		? __( 'Suscripción activa', 'woocommerce' )
		: __( 'Suscripción pendiente', 'woocommerce' );
	$subscription_label = $plan_title ?: __( 'Plan GL Music', 'woocommerce' );
}

$allowed_html = array(
	'a' => array(
		'href' => array(),
	),
);
?>
<div class="account-dashboard">
	<section class="account-dashboard__profile" aria-label="<?php esc_attr_e( 'Perfil del usuario', 'woocommerce' ); ?>">
		<div class="account-dashboard__avatar">
			<?php echo get_avatar( get_current_user_id(), 96, '', $display_name ); ?>
		</div>
		<div class="account-dashboard__identity">
			<p class="account-dashboard__kicker"><?php esc_html_e( 'Tu perfil', 'woocommerce' ); ?></p>
			<h2 class="account-dashboard__profile-name"><?php echo esc_html( $display_name ); ?></h2>
			<p class="account-dashboard__profile-email"><?php echo esc_html( $current_user->user_email ); ?></p>
			<div class="account-dashboard__meta">
				<span><?php echo esc_html( $subscription_status ); ?></span>
				<span><?php echo esc_html( $subscription_label ); ?></span>
				<?php if ( $member_since ) : ?>
					<span><?php printf( esc_html__( 'Miembro desde %s', 'woocommerce' ), esc_html( $member_since ) ); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<div class="account-dashboard__profile-actions">
			<a class="account-dashboard__profile-button" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-account' ) ); ?>">
				<?php esc_html_e( 'Editar perfil', 'woocommerce' ); ?>
			</a>
			<a class="account-dashboard__profile-link" href="<?php echo esc_url( wc_logout_url() ); ?>">
				<?php esc_html_e( 'Cerrar sesión', 'woocommerce' ); ?>
			</a>
		</div>
	</section>

	<header class="account-dashboard__intro">
		<p class="account-dashboard__kicker"><?php esc_html_e( 'Panel', 'woocommerce' ); ?></p>
		<h2 class="account-dashboard__title"><?php esc_html_e( 'Gestiona tu cuenta', 'woocommerce' ); ?></h2>
		<p class="account-dashboard__session">
			<?php
			printf(
				wp_kses( __( '¿No eres %1$s? <a href="%2$s">Cerrar sesión</a>', 'woocommerce' ), $allowed_html ),
				'<strong>' . esc_html( $current_user->display_name ) . '</strong>',
				esc_url( wc_logout_url() )
			);
			?>
		</p>
		<p class="account-dashboard__desc">
			<?php
			if ( wc_shipping_enabled() ) {
				printf(
					wp_kses(
						__( 'Desde aquí puedes revisar tus <a href="%1$s">pedidos</a>, administrar tus <a href="%2$s">direcciones de envío y facturación</a> y actualizar los <a href="%3$s">datos de tu cuenta</a>.', 'woocommerce' ),
						$allowed_html
					),
					esc_url( wc_get_endpoint_url( 'orders' ) ),
					esc_url( wc_get_endpoint_url( 'edit-address' ) ),
					esc_url( wc_get_endpoint_url( 'edit-account' ) )
				);
			} else {
				printf(
					wp_kses(
						__( 'Desde aquí puedes revisar tus <a href="%1$s">pedidos</a>, gestionar tu <a href="%2$s">dirección de facturación</a> y actualizar los <a href="%3$s">datos de tu cuenta</a>.', 'woocommerce' ),
						$allowed_html
					),
					esc_url( wc_get_endpoint_url( 'orders' ) ),
					esc_url( wc_get_endpoint_url( 'edit-address' ) ),
					esc_url( wc_get_endpoint_url( 'edit-account' ) )
				);
			}
			?>
		</p>
	</header>

	<nav class="account-dashboard__quick" aria-label="<?php esc_attr_e( 'Accesos rápidos', 'woocommerce' ); ?>">
		<a class="account-dashboard__card" href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>">
			<span class="account-dashboard__card-label"><?php esc_html_e( 'Pedidos', 'woocommerce' ); ?></span>
			<span class="account-dashboard__card-hint"><?php esc_html_e( 'Historial y seguimiento', 'woocommerce' ); ?></span>
		</a>
		<a class="account-dashboard__card" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address' ) ); ?>">
			<span class="account-dashboard__card-label"><?php esc_html_e( 'Direcciones', 'woocommerce' ); ?></span>
			<span class="account-dashboard__card-hint"><?php esc_html_e( 'Envío y facturación', 'woocommerce' ); ?></span>
		</a>
		<a class="account-dashboard__card" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-account' ) ); ?>">
			<span class="account-dashboard__card-label"><?php esc_html_e( 'Mi perfil', 'woocommerce' ); ?></span>
			<span class="account-dashboard__card-hint"><?php esc_html_e( 'Nombre, correo y contraseña', 'woocommerce' ); ?></span>
		</a>
	</nav>
</div>

<?php
	do_action( 'woocommerce_account_dashboard' );
	do_action( 'woocommerce_before_my_account' );
	do_action( 'woocommerce_after_my_account' );
