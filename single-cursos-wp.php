<?php get_header(); ?>
<?php include('menu.php') ?>
<style>
  #masthead {
    background: linear-gradient(105.9deg, rgb(20, 15, 47) 24.4%, rgb(11, 10, 16) 80.5%) !important;

  }
</style>
<div class="course-reproduction">
  <div class="overlay"></div>
  <div class="course-reproduction__list">
    <div class="course-reproduction__list__title">
      <h3>Listado de reproducciones</h3>
    </div>
    <?php $catname = get_field('cat'); ?>
    <?php $args = array(
      'posts_per_page' => -1,
      'post_type' => 'cursos',
      'cat' => $catname,
      'order' => 'ASC',
      'orderby' => 'title'
    );

    ?>
    <div class="course-reproduction__list__content">
      <?php echo $catname ?>
      <?php $family = new WP_Query($args); ?>
      <?php while ($family->have_posts()):
        $family->the_post(); ?>
        <div class="course-reproduction__item">
          <button class="course-reproduction__item-button" data-video="<?php the_field('idVideo'); ?>">
            <h2><?php the_title(); ?></h2>
          </button>

        </div>
      <?php endwhile;
      wp_reset_postdata(); ?>
    </div>
  </div>


  <?php if (get_field('description')): ?>
    <div class="course-reproduction__description descriptions">
      <div class="course-reproduction__description__content">
        <?php $image = get_field('image'); ?>
        <?php if ($image): ?>
          <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>">
        <?php endif; ?>
        <h3>Sobre el curso</h3>
        <p><?php the_field('description'); ?></p>
      </div>
    </div>
  <?php endif; ?>

  <?php if (get_field('documents')): ?>
    <div class="course-reproduction__description documents">
      <div class="course-reproduction__description__content">
        <h3>Documentos</h3>

        <?php if (have_rows('documents')) {
          while (have_rows('documents')) {
            the_row(); ?>
            <button class="course-reproduction__item-link"
              data-file="<?php the_sub_field('file'); ?>"><?php the_sub_field('name'); ?></button>
          <?php }
        } ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="course-reproduction__bar">
    <?php if (get_field('description')): ?>
      <div class="course-reproduction__bar__item">
        <button id="description" class="course-reproduction__bar__item__button">
          <svg width="800" height="800" viewBox="0 0 800 800" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M283.663 66.6672H516.334C524.084 66.6652 530.024 66.6639 535.217 67.1719C572.144 70.7842 602.37 92.9862 615.187 122.893H184.81C197.627 92.9862 227.852 70.7842 264.779 67.1719C269.973 66.6639 275.914 66.6652 283.663 66.6672Z"
              fill="white" />
            <path
              d="M210.351 157.438C163.996 157.438 125.988 185.429 113.303 222.564C113.039 223.338 112.785 224.116 112.543 224.898C125.815 220.879 139.627 218.253 153.609 216.461C189.622 211.844 235.133 211.846 288.001 211.849H291.949H517.737C570.603 211.846 616.117 211.844 652.13 216.461C666.11 218.253 679.923 220.879 693.193 224.898C692.953 224.116 692.7 223.338 692.433 222.564C679.75 185.429 641.74 157.438 595.387 157.438H210.351Z"
              fill="white" />
            <path fill-rule="evenodd" clip-rule="evenodd"
              d="M289.08 251.401H510.92C623.414 251.401 679.66 251.401 711.257 284.296C742.85 317.19 735.417 368.01 720.55 469.653L706.45 566.037C694.79 645.747 688.964 685.6 659.057 709.467C629.15 733.333 585.04 733.333 496.82 733.333H303.179C214.961 733.333 170.851 733.333 140.945 709.467C111.039 685.6 105.209 645.747 93.5497 566.037L79.4507 469.653C64.583 368.01 57.1493 317.19 88.7443 284.296C120.339 251.401 176.586 251.401 289.08 251.401ZM266.667 600.003C266.667 586.197 279.104 575.003 294.445 575.003H505.557C520.897 575.003 533.334 586.197 533.334 600.003C533.334 613.813 520.897 625.007 505.557 625.007H294.445C279.104 625.007 266.667 613.813 266.667 600.003Z"
              fill="white" />
          </svg>
          <span>Sobre el curso</span></button>
      </div>
    <?php endif; ?>
    <div class="course-reproduction__bar__item">
      <button id="songs" class="course-reproduction__bar__item__button">
        <svg width="800" height="800" viewBox="0 0 800 800" fill="none" xmlns="http://www.w3.org/2000/svg">
          <g clip-path="url(#clip0_5895_29)">
            <path
              d="M383.333 683.383C383.333 673.928 378.025 662.225 363.121 651.58C348.281 640.98 326.07 633.383 300 633.383C273.929 633.383 251.718 640.98 236.878 651.58C221.974 662.225 216.666 673.928 216.666 683.383C216.666 692.835 221.974 704.537 236.879 715.183C251.719 725.784 273.93 733.383 300 733.383C326.07 733.382 348.281 725.784 363.121 715.183C378.025 704.537 383.333 692.835 383.333 683.383ZM450 257.405V353.799L583.333 309.355V212.961L450 257.405ZM449.996 684.238C449.674 720.42 428.977 750.07 401.87 769.432C374.486 788.993 338.363 800.048 300 800.049C261.637 800.049 225.513 788.993 198.129 769.432C170.809 749.918 150 719.953 150 683.383C150 646.811 170.809 616.845 198.13 597.331C225.515 577.771 261.638 566.716 300 566.716C330.228 566.716 359.065 573.581 383.333 586.061V252.601C383.333 248.535 382.773 237.337 386.804 227.091C389.868 219.298 394.839 212.404 401.256 207.038L401.271 207.026C409.711 199.976 420.486 196.971 424.358 195.68V195.679L571.025 146.791C576.355 145.014 582.467 142.944 587.821 141.678C593.166 140.415 601.624 138.928 611.243 141.12L612.178 141.343L612.181 141.344C624.173 144.357 634.613 151.703 641.498 161.944L642.152 162.944L642.162 162.959C647.704 171.653 649.032 180.519 649.539 186.164C650.031 191.641 650 198.093 650 203.712V314.161C650 318.227 650.558 329.412 646.534 339.654L646.535 339.655C643.471 347.455 638.497 354.353 632.079 359.721C632.073 359.726 632.068 359.731 632.062 359.736C623.622 366.786 612.845 369.791 608.975 371.081L608.974 371.082L462.308 419.97C458.422 421.266 454.12 422.716 450 423.898V683.383L449.996 684.238Z"
              fill="white" />
          </g>
          <defs>
            <clipPath id="clip0_5895_29">
              <rect width="800" height="800" fill="white" />
            </clipPath>
          </defs>
        </svg>
        <span>Canciones</span></button>
    </div>
    <?php if (have_rows('documents')): ?>
      <div class="course-reproduction__bar__item">
        <button id="documents" class="course-reproduction__bar__item__button">
          <svg width="800" height="800" viewBox="0 0 800 800" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M283.663 66.6672H516.334C524.084 66.6652 530.024 66.6639 535.217 67.1719C572.144 70.7842 602.37 92.9862 615.187 122.893H184.81C197.627 92.9862 227.852 70.7842 264.779 67.1719C269.973 66.6639 275.914 66.6652 283.663 66.6672Z"
              fill="white" />
            <path
              d="M210.351 157.438C163.996 157.438 125.988 185.429 113.303 222.564C113.039 223.338 112.785 224.116 112.543 224.898C125.815 220.879 139.627 218.253 153.609 216.461C189.622 211.844 235.133 211.846 288.001 211.849H291.949H517.737C570.603 211.846 616.117 211.844 652.13 216.461C666.11 218.253 679.923 220.879 693.193 224.898C692.953 224.116 692.7 223.338 692.433 222.564C679.75 185.429 641.74 157.438 595.387 157.438H210.351Z"
              fill="white" />
            <path fill-rule="evenodd" clip-rule="evenodd"
              d="M289.08 251.401H510.92C623.414 251.401 679.66 251.401 711.257 284.296C742.85 317.19 735.417 368.01 720.55 469.653L706.45 566.037C694.79 645.747 688.964 685.6 659.057 709.467C629.15 733.333 585.04 733.333 496.82 733.333H303.179C214.961 733.333 170.851 733.333 140.945 709.467C111.039 685.6 105.209 645.747 93.5497 566.037L79.4507 469.653C64.583 368.01 57.1493 317.19 88.7443 284.296C120.339 251.401 176.586 251.401 289.08 251.401ZM266.667 600.003C266.667 586.197 279.104 575.003 294.445 575.003H505.557C520.897 575.003 533.334 586.197 533.334 600.003C533.334 613.813 520.897 625.007 505.557 625.007H294.445C279.104 625.007 266.667 613.813 266.667 600.003Z"
              fill="white" />
          </svg>
          <span>Documentos</span>
        </button>
      </div>
    <?php endif; ?>
  </div>
  <div class="course-reproduction__content">
    <?php $args = array(
      'posts_per_page' => 1,
      'post_type' => 'cursos',
      'cat' => $catname,
      'order' => 'ASC',
      'orderby' => 'title'
    );
    ?>
    <?php $family = new WP_Query($args); ?>
    <?php while ($family->have_posts()):
      $family->the_post(); ?>
      <iframe width="560" height="315" src="https://www.soundslice.com/slices/<?php the_field('idVideo') ?>/embed/"
        title="YouTube video player" frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
    <?php endwhile;
    wp_reset_postdata(); ?>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    $('#songs').on('click', function () {
      $('.course-reproduction__list').toggleClass('active');
      $('.course-reproduction__bar').toggleClass('active');
      $('.course-reproduction__content').toggleClass('active');
      $('.course-reproduction__description').removeClass('active');
      if ($('.course-reproduction__list').hasClass('active')) {
        $('.overlay').addClass('active');
        $('body').css('overflow', 'hidden');

      } else {
        $('.overlay').removeClass('active');
        $('body').css('overflow', 'auto');
      }
    });

    $('.course-reproduction__item-link').on('click', function () {
      const video = $(this).data('file');
      $('.course-reproduction__list').removeClass('active');
      $('.course-reproduction__bar').removeClass('active');
      $('.course-reproduction__content').removeClass('active');
      $('.course-reproduction__description').removeClass('active');
      $('.overlay').removeClass('active');
      $('.course-reproduction__content iframe').attr('src', `${video}`);
    });
    $('.course-reproduction__item-button').on('click', function () {
      const video = $(this).data('video');
      $('.course-reproduction__list').removeClass('active');
      $('.course-reproduction__bar').removeClass('active');
      $('.course-reproduction__content').removeClass('active');
      $('.course-reproduction__description').removeClass('active');
      $('.overlay').removeClass('active');
      $('.course-reproduction__content iframe').attr('src', `https://www.soundslice.com/slices/${video}/embed/`);
    });
    $('#description').on('click', function () {
      $('.course-reproduction__list').removeClass('active');
      $('.course-reproduction__description.descriptions').toggleClass('active');
      if ($('.course-reproduction__description.descriptions').hasClass('active')) {
        $('body').css('overflow', 'hidden');
        $('.overlay').addClass('active');
      } else {
        $('body').css('overflow', 'auto');
        $('.overlay').removeClass('active');
      }
    });
    $('#documents').on('click', function () {
      $('.course-reproduction__description.documents').toggleClass('active');
      if ($('.course-reproduction__description.documents').hasClass('active')) {
        $('body').css('overflow', 'hidden');
        $('.overlay').addClass('active');
      }
    });
    $('.overlay').on('click', function () {
      $('.course-reproduction__list').removeClass('active');
      $('.course-reproduction__bar').removeClass('active');
      $('.course-reproduction__content').removeClass('active');
      $('.course-reproduction__description').removeClass('active');
      $('.overlay').removeClass('active');
    });
  });
</script>
<?php get_footer(); ?>