<?php get_header(); ?>
<?php include('menu.php') ?>
<?php while (have_posts()) {
  the_post();
  if (function_exists('get_field') && get_field('modules') !== null) {
    the_modules_loop();
  } else {
    ?>
    <div class="container">
      <?php the_module('post'); ?>
    </div>
    <?php
  }
}
?>
<?php get_footer(); ?>