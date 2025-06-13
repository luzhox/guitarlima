<?php get_header(); ?>
  <body <?php body_class(); ?>>
	<main class="main-404">
		<div class="container main-404__content">
				<img src="<?php echo get_theme_mod('brand_img'); ?>" alt="Logo Marca">
				<h2>La página que esta buscando no existe</h1>
				<a class="btn__primary" href="<?php echo home_url(); ?>"> Regresar a home </a>
		</div>
	</main>
  </body>
</html>