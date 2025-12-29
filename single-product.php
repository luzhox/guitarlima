<?php get_header(); ?>
<?php include('menu.php')?>
<style>
	.woocommerce a.added_to_cart{
		display: none!important;
	}



  .variations_form .price{
    display: block;
  }

</style>
  		<div class="container-single-product">
		<?php while(have_posts() ): the_post(); ?>
			<?php global $post;
			$thumbID = get_post_thumbnail_id( $post->ID );
			$imgDestacada = wp_get_attachment_url( $thumbID );?>
			<div class="product-single">
				<div class="imagen" data-aos="fade-up" data-aos-offset="100" style="background:url(<?php echo $imgDestacada;?>);" ></div>
				<div class="container">
					<div class="post">
						<div class="textos">
							<!-- <h1 data-aos="fade-up" data-aos-offset="100"><?php the_title();?></h1> -->
							<div class="texto-articulo" data-aos="fade-up" data-aos-offset="100"><?php the_content();?></div>
						</div>
					</div>

				</div>
			</div>
		<?php endwhile; ?>
		</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const evaluteLength = document.getElementsByClassName('screen-reader-text').length;
			console.log(evaluteLength);
			if(evaluteLength >= 3){
				$('.price').hide()
			}
			$('.added_to_cart').hide()
		});
	</script>

<?php get_footer(); ?>