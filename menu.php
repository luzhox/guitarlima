	<header id="masthead" class="site-header" role="banner">
			<div class="container">
				<div class="site-header-brand">
					<a class="site-header-brand__item" href="<?php echo esc_url(home_url('/')); ?>">
						<img id="brand" data-brand="<?php echo get_theme_mod('brand_img'); ?>" data-brandtwo="<?php echo get_theme_mod('brand_img-revert'); ?>" src="<?php echo get_theme_mod('brand_img'); ?>">
					</a>
					<div class="site-header-search">
						<?php echo do_shortcode('[wpdreams_ajaxsearchlite]'); ?>
					</div>
				</div>

				<div class="site-header-sandwich">
					<div class="menu menu-1"></div>
					<div class="menu menu-2"></div>
					<div class="menu menu-3"></div>
				</div>
				<div class="site-header-nav">

					<nav id="site-navegation" class="main-navegation" role="navegation">
						<?php wp_nav_menu(array('theme_location'=>'menu_principal')); ?>
						<?php if (is_user_logged_in()): ?>
							<a class="btn-favs" href="/mis-favoritos" >
								Mis favoritos
							</a>
						<?php endif; ?>
					</nav>


					<?php if (is_user_logged_in()): ?>
						<div class="user-actions">
							<a href="<?php echo wp_logout_url(home_url()); ?>" style="margin-left: 24px;" class="btn__primary logout-btn" title="Cerrar sesión">
								<span class="logout-text" >Cerrar sesión</span>
							</a>
						</div>
					<?php else: ?>
						<div class="user-actions">
							<a href="/mi-cuenta" class="btn__primary--border login-btn" style="margin-left: 24px;" title="Iniciar sesión">
								Iniciar sesión
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</header>