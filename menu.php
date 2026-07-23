<?php
$account_user = is_user_logged_in() ? wp_get_current_user() : null;
$account_name = '';

if ($account_user) {
	$account_name = trim((string) $account_user->first_name);

	if (!$account_name) {
		$display_name_parts = preg_split('/\s+/', trim((string) $account_user->display_name));
		$account_name = $display_name_parts[0] ?? '';
	}

	if (!$account_name) {
		$account_name = $account_user->user_login;
	}
}
?>
<header id="masthead" class="site-header" role="banner">
	<div class="container">
		<div class="site-header-brand">
			<a class="site-header-brand__item" href="<?php echo esc_url(home_url('/')); ?>">
				<img id="brand" data-brand="<?php echo get_theme_mod('brand_img'); ?>"
					data-brandtwo="<?php echo get_theme_mod('brand_img-revert'); ?>"
					src="<?php echo get_theme_mod('brand_img'); ?>">
			</a>
			<div class="site-header-search">
				<?php echo do_shortcode('[wpdreams_ajaxsearchlite]'); ?>
			</div>

			<?php if (!is_user_logged_in()): ?>
				<div class="user-actions hide-on-medium-onlytwo">
					<a href="<?php echo esc_url(home_url('/mi-cuenta/')); ?>" class="btn__primary--border login-btn" style="margin-left: 24px;" title="Iniciar sesión" data-login-modal-open>
						Iniciar sesión
					</a>
				</div>
			<?php endif; ?>
		</div>

		<?php if (is_user_logged_in()): ?>
			<div class="mobile-account-actions hide-on-desktop-only">
				<div class="account-menu account-menu--mobile-top">
					<button class="account-menu__trigger" type="button" aria-haspopup="true" aria-expanded="false" aria-label="Abrir cuenta">
						<span class="account-menu__avatar" aria-hidden="true">
							<?php echo get_avatar($account_user->ID, 40, '', $account_name); ?>
						</span>
					</button>
					<div class="account-menu__dropdown" role="menu">
						<a href="<?php echo esc_url(home_url('/mi-cuenta/')); ?>" role="menuitem">Ver perfil</a>
						<a href="<?php echo esc_url(home_url('/mis-favoritos/')); ?>" role="menuitem">Mis favoritos</a>
						<a href="<?php echo esc_url(glmusic_logout_url()); ?>" role="menuitem">Cerrar sesión</a>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="site-header-sandwich">
			<div class="menu menu-1"></div>
			<div class="menu menu-2"></div>
			<div class="menu menu-3"></div>
		</div>
		<div class="site-header-nav">

			<nav id="site-navegation" class="main-navegation" role="navegation">
				<?php wp_nav_menu(array('theme_location' => 'menu_principal')); ?>
				<?php if (is_user_logged_in()): ?>
					<div class="fav hide-on-desktop-only">
						<a class="btn-favs" href="<?php echo esc_url(home_url('/mi-cuenta/')); ?>">
							Ver perfil
						</a>
						<a class="btn-favs" href="<?php echo esc_url(home_url('/mis-favoritos/')); ?>">
							Mis favoritos
						</a>
					</div>
				<?php else: ?>
					<div class="fav hide-on-desktop-only">
						<a href="<?php echo esc_url(home_url('/mi-cuenta/')); ?>" class="btn__primary--border login-btn-mov" title="Iniciar sesión" data-login-modal-open>
							Iniciar sesión
						</a>
					</div>
				<?php endif; ?>
			</nav>


			<?php if (is_user_logged_in()): ?>
				<div class="user-actions">
					<div class="account-menu">
						<button class="account-menu__trigger" type="button" aria-haspopup="true" aria-expanded="false">
							<span class="account-menu__avatar" aria-hidden="true">
								<?php echo get_avatar($account_user->ID, 40, '', $account_name); ?>
							</span>
							<span class="account-menu__name"><?php echo esc_html($account_name); ?></span>
						</button>
						<div class="account-menu__dropdown" role="menu">
							<a href="<?php echo esc_url(home_url('/mi-cuenta/')); ?>" role="menuitem">Ver perfil</a>
							<a href="<?php echo esc_url(home_url('/mis-favoritos/')); ?>" role="menuitem">Mis favoritos</a>
							<a href="<?php echo esc_url(glmusic_logout_url()); ?>" role="menuitem">Cerrar sesión</a>
						</div>
					</div>
				</div>
			<?php else: ?>
				<div class="user-actions">
					<a href="<?php echo esc_url(home_url('/mi-cuenta/')); ?>" class="btn__primary--border login-btn" style="margin-left: 24px;" title="Iniciar sesión" data-login-modal-open>
						Iniciar sesión
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</header>
<?php
if (!is_user_logged_in()) {
	include get_template_directory() . '/modules/login-modal/login-modal.php';
}
?>
