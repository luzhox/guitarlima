<?php
/**
 * Fix: guardado del meta protegido `_members_access_role` de Members (Content Permissions).
 *
 * PROBLEMA
 * --------
 * El plugin Members registra los meta `_members_access_role` y `_members_access_error`
 * (Content Permissions) en los CPT con `show_in_rest => true` y un `auth_callback`
 * roto: `auth_content_permissions_meta()` hace `if ( ! $allowed ) return false;`.
 * El filtro `auth_post_meta_{key}_for_{post_type}` que WordPress consulta en
 * `map_meta_cap()` SIEMPRE entra con `$allowed = false`, así que ese callback nunca
 * puede devolver `true` y `current_user_can( 'edit_post_meta', $post_id, '_members_access_role' )`
 * devuelve `false` para TODOS los usuarios — incluido el administrador.
 *
 * SÍNTOMA
 * -------
 * Al publicar/guardar una Canción (CPT `cursos`) desde el editor de bloques, el editor
 * envía ese meta por REST y el guardado se rechaza con:
 *   "Ha fallado la publicación. Lo siento, no tienes permisos para editar el campo
 *    personalizado _members_access_role."
 *
 * SOLUCIÓN
 * --------
 * Añadimos un filtro con prioridad 20 (después del callback de Members, prioridad 10)
 * sobre los mismos hooks `auth_post_meta_*_for_*`. Devuelve la decisión correcta:
 * puede escribir el meta quien puede editar ese contenido. Es upgrade-safe (no toca el
 * plugin) y aplica a Canciones, Cursos y Librerías además de post/página.
 */

add_action( 'init', function () {

	$post_types = array( 'post', 'page', 'cursos', 'cursos-wp', 'libreria' );
	$meta_keys  = array( '_members_access_role', '_members_access_error' );

	$auth = function ( $allowed, $meta_key, $object_id ) {
		// El editor de bloques envía este meta en cada guardado. Autorizamos su
		// escritura a quien puede editar el contenido (o crear, en un post nuevo).
		if ( $object_id ) {
			return current_user_can( 'edit_post', (int) $object_id );
		}
		return current_user_can( 'edit_posts' );
	};

	foreach ( $post_types as $post_type ) {
		foreach ( $meta_keys as $meta_key ) {
			add_filter( "auth_post_meta_{$meta_key}_for_{$post_type}", $auth, 20, 3 );
		}
	}
}, 1000 );
