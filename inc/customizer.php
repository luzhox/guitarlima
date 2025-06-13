<?php

function my_customize_register( $wp_customize ) {
  $wp_customize->add_panel( 'my_custom_options', array(
    'title' => __( 'Google Analytics', 'textdomain' ),
    'priority' => 160,
    'capability' => 'edit_theme_options',
  ));

  // Section para Google Analytics
  $wp_customize->add_section( 'google_analytics_section' , array(
    'title' => __( 'Google Analytics', 'textdomain' ),
    'panel' => 'my_custom_options',
    'priority' => 1,
    'capability' => 'edit_theme_options',
  ));


  //Google Analytics
  $wp_customize->add_setting( 'my_google_analytics', array(
    'type' => 'theme_mod',
    'capability' => 'edit_theme_options',
  ));

  $wp_customize->add_control('my_google_analytics', array(
    'label' => __( 'Código de Google Analytics', 'textdomain' ),
    'section' => 'google_analytics_section',
    'priority' => 1,
    'type' => 'textarea',
  ));

  $wp_customize->add_panel( 'brand', array(
    'title' => __( 'Brand', 'textdomain' ),
    'priority' => 50,
    'capability' => 'edit_theme_options',
  ));

  // Section para Google Analytics
  $wp_customize->add_section( 'brand_section' , array(
    'title' => __( 'Logo de Menu', 'textdomain' ),
    'panel' => 'brand',
    'priority' => 1,
    'capability' => 'edit_theme_options',
  ));

  //Google Analytics
  $wp_customize->add_setting( 'brand_img', array(
    'default' => '',
    'transport' => 'refresh',
    'sanitize_callback' => 'esc_url_raw'
  ));

  $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'brand_img',
  array(
     'label' => __( 'Logo principal del menu' ),
     'description' => esc_html__( 'Aca se pondrá el logo principal del menu de navegación' ),
     'section' => 'brand_section',
     'button_labels' => array( // Optional.
        'select' => __( 'Elige una imagen' ),
        'change' => __( 'Cambiar imagen' ),
        'remove' => __( 'Borrar' ),
        'default' => __( 'Default' ),
        'placeholder' => __( 'No hay una imagen elegida' ),
        'frame_title' => __( 'Elegir imagen' ),
        'frame_button' => __( 'Escoger' ),
     )
  )
) );

$wp_customize->add_setting( 'brand_img-revert', array(
    'default' => '',
    'transport' => 'refresh',
    'sanitize_callback' => 'esc_url_raw'
  ));

  $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'brand_img-revert',
  array(
    'label' => __( 'Logo alternativo del menu' ),
    'description' => esc_html__( 'Aca se pondrá el logo alternativo del menu de navegación' ),
     'section' => 'brand_section',
     'button_labels' => array( // Optional.
        'select' => __( 'Elige una imagen' ),
        'change' => __( 'Cambiar imagen' ),
        'remove' => __( 'Borrar' ),
        'default' => __( 'Default' ),
        'placeholder' => __( 'No hay una imagen elegida' ),
        'frame_title' => __( 'Elegir imagen' ),
        'frame_button' => __( 'Escoger' ),
     )
  )
) );

}
add_action( 'customize_register', 'my_customize_register' );


//Add Google Analytics Code
if (get_option('my_google_analytics')) {
  function add_google_analytics_code() {
    echo get_option('my_google_analytics');
  }
  add_action( 'wp_head', 'add_google_analytics_code' );
}