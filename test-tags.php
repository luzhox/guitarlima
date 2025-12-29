<?php
/**
 * Test script to check for level tags (principiante, avanzado, intermedio)
 * Place this file in your theme directory and access it via browser
 */

// Include WordPress
require_once('../../../wp-load.php');

// Check if user is logged in and has admin privileges
if (!current_user_can('manage_options')) {
  wp_die('Access denied. Admin privileges required.');
}

$level_tags = array('principiante', 'avanzado', 'intermedio');
$found_posts = array();

echo '<h1>Evaluación de Etiquetas de Nivel</h1>';
echo '<h2>Buscando etiquetas: principiante, avanzado, intermedio</h2>';

// Get all posts
$args = array(
  'post_type' => 'post',
  'posts_per_page' => -1,
  'post_status' => 'publish'
);

$query = new WP_Query($args);

if ($query->have_posts()) {
  echo '<h3>Resultados:</h3>';
  echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
  echo '<tr><th>Post ID</th><th>Título</th><th>Etiquetas</th><th>Etiquetas de Nivel</th></tr>';

  while ($query->have_posts()) {
    $query->the_post();
    $post_id = get_the_ID();
    $post_title = get_the_title();
    $post_tags = get_the_tags();

    $all_tags = array();
    $level_tags_found = array();

    if ($post_tags) {
      foreach ($post_tags as $tag) {
        $all_tags[] = $tag->name;
        if (in_array(strtolower($tag->name), $level_tags)) {
          $level_tags_found[] = $tag->name;
        }
      }
    }

    echo '<tr>';
    echo '<td>' . $post_id . '</td>';
    echo '<td>' . esc_html($post_title) . '</td>';
    echo '<td>' . (empty($all_tags) ? 'Sin etiquetas' : implode(', ', $all_tags)) . '</td>';
    echo '<td>' . (empty($level_tags_found) ? 'Ninguna' : implode(', ', $level_tags_found)) . '</td>';
    echo '</tr>';

    if (!empty($level_tags_found)) {
      $found_posts[] = array(
        'id' => $post_id,
        'title' => $post_title,
        'level_tags' => $level_tags_found
      );
    }
  }

  echo '</table>';

  wp_reset_postdata();

  // Summary
  echo '<h3>Resumen:</h3>';
  echo '<p><strong>Total de posts revisados:</strong> ' . $query->found_posts . '</p>';
  echo '<p><strong>Posts con etiquetas de nivel:</strong> ' . count($found_posts) . '</p>';

  if (!empty($found_posts)) {
    echo '<h4>Posts con etiquetas de nivel encontradas:</h4>';
    echo '<ul>';
    foreach ($found_posts as $post) {
      echo '<li><strong>ID ' . $post['id'] . ':</strong> ' . esc_html($post['title']) . ' - Etiquetas: ' . implode(', ', $post['level_tags']) . '</li>';
    }
    echo '</ul>';
  } else {
    echo '<p><em>No se encontraron posts con las etiquetas de nivel especificadas.</em></p>';
    echo '<p>Para agregar etiquetas de nivel a los posts:</p>';
    echo '<ol>';
    echo '<li>Ve al panel de administración de WordPress</li>';
    echo '<li>Edita cualquier post</li>';
    echo '<li>En la sección "Etiquetas", agrega: principiante, avanzado, o intermedio</li>';
    echo '<li>Guarda el post</li>';
    echo '</ol>';
  }

} else {
  echo '<p>No se encontraron posts.</p>';
}

echo '<hr>';
echo '<p><a href="' . admin_url() . '">Volver al panel de administración</a></p>';
?>