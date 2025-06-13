
	<header id="masthead" class="site-header" role="banner">
			<div class="container">
				<div class="site-header-brand">
					<a class="site-header-brand__item" href="<?php echo esc_url(home_url('/')); ?>">
						<img id="brand" data-brand="<?php echo get_theme_mod('brand_img'); ?>" data-brandtwo="<?php echo get_theme_mod('brand_img-revert'); ?>" src="<?php echo get_theme_mod('brand_img'); ?>">
					</a>
				</div>
				<div class="site-header-sandwich">
					<div class="menu menu-1"></div>
					<div class="menu menu-2"></div>
					<div class="menu menu-3"></div>
				</div>
				<div class="site-header-nav">
					<nav id="site-navegation" class="main-navegation" role="navegation">
						<?php wp_nav_menu(array('theme_location'=>'menu_principal')); ?>
					</nav>
				</div>
			</div>
		</header>