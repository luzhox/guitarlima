<?php

function my_acf_init() {acf_update_setting('google_api_key', 'AIzaSyB2opgncJgWQkvibe4bVFb0AWAI2I7MSXw');}
add_action('acf/init', 'my_acf_init');

add_action( 'after_setup_theme', function() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption' ) );
    add_theme_support( 'post-thumbnails' );
} );
    add_filter('show_admin_bar','__return_false');

?>