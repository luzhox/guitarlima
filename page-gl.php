<?php
/**
 * Template Name: Page GL
 */

get_header(); ?>
<?php include('menu.php') ?>

<div class="container">
  <h1>
    <?php the_title(); ?>
  </h1>
  <?php the_content(); ?>
</div>

<?php get_footer(); ?>