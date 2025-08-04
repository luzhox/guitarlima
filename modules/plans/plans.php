<div class="plans">
  <div class="container">
    <div class="plans__title">
      <h1><?php the_sub_field('title') ?></h1>
    </div>
    <div class="plans__content">
    <?php if (have_rows('plans')) {
            while (have_rows('plans')) {
                the_row(); ?>
      <div class="plans__content__item">
        <h3><?php the_sub_field('title') ?></h3>
        <p><?php the_sub_field('content') ?></p>
      </div>
      <?php }
        } ?>
    </div>
  </div>
</div>