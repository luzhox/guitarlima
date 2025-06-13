<?php

add_action( 'wp_enqueue_scripts', function() {
    $theme = wp_get_theme();
    $theme_ver = $theme->version;
      wp_enqueue_style( 'estilos',
        get_template_directory_uri().'/build/css/main.css',
        array(),
        $theme_ver
      );
      wp_enqueue_style( 'aos',
        get_template_directory_uri().'/styles/css/aos.css',
        array(),
        $theme_ver
      );
      wp_enqueue_style( 'owlcarousel2',
        get_template_directory_uri().'/styles/css/owl.carousel.min.css',
        array(),
        $theme_ver
      );
      wp_enqueue_style( 'owlcarouseltheme',
        get_template_directory_uri().'/styles/css/owl.theme.default.min.css',
        array(),
        $theme_ver
      );

      wp_enqueue_script("jquery");
      wp_enqueue_script( 'aos',
      get_template_directory_uri().'/vendors/aos.js',
      array( 'jquery' ),
      $theme_ver,
      false
       );
      wp_enqueue_script( 'main',
      get_template_directory_uri().'/build/js/main.js',
      array( 'jquery' ),
      $theme_ver,
      false//para que vaya en el header
    );

      } );

?>