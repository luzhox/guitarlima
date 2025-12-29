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
            <div class="plans__content__item__text">
              <h3><?php the_sub_field('title') ?></h3>
              <?php the_sub_field('content') ?>
            </div>
            <div class="plans__content__item__button">
              <?php $button = get_sub_field('button'); ?>
              <?php if ($button): ?>
                <a href="<?php echo $button['url']; ?>" class="btn__primary"
                  target="<?php echo $button['target']; ?>"><?php echo $button['title']; ?></a>
              <?php endif; ?>
            </div>
          </div>
        <?php }
      } ?>
    </div>
  </div>
</div>