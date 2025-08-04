<?php get_header(); ?>
<?php include('menu.php')?>

<div class="single-curso">
<?php while(have_posts() ): the_post(); ?>
			<?php global $post;
			$thumbID = get_post_thumbnail_id( $post->ID );
			$imgDestacada = wp_get_attachment_url( $thumbID );?>
  <div class="single-curso__header">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <h1><?php the_title(); ?></h1>

        </div>
      </div>
    </div>
    <?php $poster = get_field('poster'); ?>
    <?php if($poster): ?>
      <img src="<?php echo $poster['url']; ?>" alt="<?php echo $poster['alt']; ?>">
    <?php endif; ?>

  </div>
    <div class="container">
        <div class="row">

            <div class="video">
              <iframe width="560" height="315" src="https://www.soundslice.com/slices/<?php the_field('idVideo'); ?>/embed/" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            <?php gl_the_favorite_button(); ?>
        </div>
        <?php endwhile; wp_reset_postdata(); ?>

    </div>


</div>

<script>
function debugFavorites() {
    jQuery.ajax({
        url: gl_favorites_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'gl_debug_favorites',
            nonce: gl_favorites_ajax.nonce
        },
        success: function(response) {
            console.log('Debug Response:', response);
            alert('Check console for debug info');
        },
        error: function(xhr, status, error) {
            console.error('Debug Error:', error);
            alert('Debug error: ' + error);
        }
    });
}
</script>

<?php get_footer(); ?>