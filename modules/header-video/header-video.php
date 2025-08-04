<div class="header-video">
  <div class="overlay"></div>
  <div class="header-video__video">
    <video src="<?php the_sub_field('video'); ?>" poster="<?php the_sub_field('poster'); ?>" autoplay muted loop>
      <source src="<?php the_sub_field('video'); ?>" type="video/mp4">
    </video>
  </div>
  <div class="container">
    <div class="header-video__text">
      <?php the_sub_field('text'); ?>
      <div class="header-video__buttons">
        <?php $link = get_sub_field('button');
            if( $link ):
                $link_url = $link['url'];
                $link_title = $link['title'];
                $link_target = $link['target'] ? $link['target'] : '_self';
                ?>
                <a class="btn__primary" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?></a>
            <?php endif; ?>
            <?php $link = get_sub_field('buttontwo');
            if( $link ):
                $link_url = $link['url'];
                $link_title = $link['title'];
                $link_target = $link['target'] ? $link['target'] : '_self';
                ?>
                <a class="btn__primary--border" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?></a>
            <?php endif; ?>
      </div>
    </div>
  </div>
</div>