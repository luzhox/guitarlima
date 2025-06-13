<section class="hero-blog" style="background:url(<?php the_sub_field('bg')?>)">
    <div class="overlay" style="background-color:<?php the_sub_field('overlay')?>"></div>
    <div class="container">
        <div class="hero-blog__text">
            <?php the_sub_field('text')?>

        </div>
        <div class="hero-blog__button">
        <?php $link = get_sub_field('button');
                        if ($link) :
                            $link_url = $link['url'];
                            $link_title = $link['title'];
                            $link_target = $link['target'] ? $link['target'] : '_self';
                        ?>
                            <a class="btn__primary" href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>"><?php echo esc_html($link_title); ?></a>
                        <?php endif; ?>
        </div>
    </div>
</section>