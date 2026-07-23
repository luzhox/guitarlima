<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Canciones (CPT cursos) — la asignación de categorías se hace con el campo
 * ACF "categorias" (autocomplete multi-select con save/load terms). Se oculta
 * el panel nativo de Categorías del editor de bloques para que exista una sola
 * vía de edición: un cambio hecho en el panel nativo sería pisado por el valor
 * del campo ACF al guardar.
 */
add_action( 'enqueue_block_editor_assets', function () {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || 'cursos' !== $screen->post_type ) {
        return;
    }

    $js = "wp.domReady( function () {
        var store = wp.data.dispatch( 'core/editor' );
        if ( ! store || typeof store.removeEditorPanel !== 'function' ) {
            store = wp.data.dispatch( 'core/edit-post' );
        }
        if ( store && typeof store.removeEditorPanel === 'function' ) {
            store.removeEditorPanel( 'taxonomy-panel-category' );
        }
    } );";

    wp_add_inline_script( 'wp-edit-post', $js );
} );
