<div class="course-percat">
  <div class="container">
    <div class="course-percat__text">
  <h2><?php the_sub_field('title'); ?></h2>
  <p><?php the_sub_field('text'); ?></p></div>
  <div class="course-percat__items">
  <?php $args = array(
			'posts_per_page'=> -1,
      'orderby'=> 'date',
      'post_type' => array('cursos-wp', 'libreria'),
      'category_name' => get_sub_field('category'),
			'order'=>'DESC');
		?>
		<?php $family = new WP_Query($args);?>
		<?php while($family->have_posts()): $family->the_post();?>
		<?php $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );?>
    <div class="course-percat__item">
      <div class="course-percat__item-image">
    <a href="<?php the_permalink(); ?>">
      <?php $poster = get_field('poster'); ?>
        <img src="<?php echo $url; ?>" alt="<?php echo $poster['alt']; ?>">
      </div>
      <div class="course-percat__item-title">
        <h2><?php the_title(); ?></h2>
        <p><?php the_field('author'); ?></p>

        <?php if (is_user_logged_in()): ?>
        <div class="heart-container">
        <?php echo do_shortcode('[gl_favorite_heart]'); ?>
        </div>
        <?php endif; ?>

      </div>

      </a>
    </div>
    <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</div>