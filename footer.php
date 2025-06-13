</div>
<footer class="site-footer"  role="contentinfo">
        <div class="container">
                <div class="site-footer__brand">
                        <img id="logito" src="<?php echo get_theme_mod('brand_img-revert'); ?>">
                </div>
                <div class="site-footer__links">
                        <p class="title">Otros Links:</p>
                        <?php wp_nav_menu(array('theme_location'=>'footer')); ?>
                </div>
                <div class="site-footer__social">
                        <p class="title">Síguenos en:</p>
                        <div class="group">
                        <?php $args = array(
                                'theme_location'=> 'redes',
                                'container' => 'nav',
                                'container_class' => 'site-footer__socials',
                                'link_before'=> '<span class="sr-text">',
                                'link_after'=>'</span>'
                        );  wp_nav_menu($args);?>
                        </div>
                        <p class="title call">Llámanos:</p>
                        <a class="number" href="tel:+51<?php echo esc_html(get_option('numero_page')); ?>">
                                <svg width='32' height='30' viewBox='0 0 32 30' fill='none' xmlns='http://www.w3.org/2000/svg'>
                                <path d='M27.465 23.7045L22.6325 19.2516C22.0514 18.7161 21.1092 18.7161 20.528 19.2516L18.5258 21.0966C14.6001 19.0562 11.3195 16.0332 9.10525 12.4158L11.2434 10.4455C11.8246 9.91006 11.8246 9.04179 11.2434 8.5063L6.41093 4.05348C5.82974 3.51798 4.88755 3.51798 4.30642 4.05348L1.22054 6.89705L1.23248 6.90805C0.139972 7.73041 -0.307842 9.15329 0.223347 10.5557C3.51504 19.2478 11.1117 26.2478 20.5443 29.2811C21.9981 29.7485 23.4677 29.394 24.3759 28.4844L24.379 28.4873L27.4649 25.6437C28.0462 25.1082 28.0462 24.24 27.465 23.7045Z' fill='#FFCC00'/><path d='M16.3092 7.82959C15.7298 7.82959 15.26 8.26245 15.26 8.79639C15.26 9.33032 15.7297 9.76319 16.3092 9.76319C19.1188 9.76319 21.4047 11.8695 21.4047 14.4585C21.4047 14.9924 21.8745 15.4253 22.4539 15.4253C23.0334 15.4253 23.5031 14.9924 23.5031 14.4585C23.5031 10.8034 20.2759 7.82959 16.3092 7.82959Z' fill='#FFCC00'/><path d='M16.3092 3.89258C15.7298 3.89258 15.26 4.32544 15.26 4.85938C15.26 5.39331 15.7297 5.82618 16.3092 5.82618C21.4747 5.82618 25.6772 9.69861 25.6772 14.4584C25.6772 14.9923 26.1469 15.4252 26.7264 15.4252C27.3058 15.4252 27.7756 14.9923 27.7756 14.4584C27.7756 8.63235 22.6318 3.89258 16.3092 3.89258Z' fill='#FFCC00'/><path d='M27.4042 4.23482C24.4405 1.50397 20.5003 0 16.3092 0C15.7298 0 15.26 0.432864 15.26 0.966802C15.26 1.50074 15.7298 1.9336 16.3092 1.9336C23.804 1.9336 29.9016 7.55225 29.9016 14.4586C29.9016 14.9925 30.3713 15.4254 30.9507 15.4254C31.5302 15.4254 31.9999 14.9925 31.9999 14.4586C31.9999 10.5965 30.3678 6.96568 27.4042 4.23482Z' fill='#FFCC00'/>
                                </svg>
                                <?php echo esc_html(get_option('numero_page')); ?></a>
                </div>
                <div class="site-footer__address">
                        <?php dynamic_sidebar('location')?>
                </div>
        </div>
</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>