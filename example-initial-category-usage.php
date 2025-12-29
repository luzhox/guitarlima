<?php
/**
 * Ejemplo de uso del módulo course-percat con categoría inicial
 *
 * Este archivo muestra cómo configurar el módulo para que muestre
 * una categoría específica al cargar la página.
 */

// Ejemplo 1: Mostrar solo cursos de guitarra inicialmente
$args = array(
  'post_type' => 'cursos-wp',
  'posts_per_page' => 6,
  'orderby' => 'date',
  'order' => 'ASC'
);

// Configuración ACF simulada
$acf_fields = array(
  'title' => 'Cursos de Música',
  'text' => 'Explora nuestros cursos por categoría',
  'limit' => 6,
  'postType' => 'cursos-wp',
  'type' => 'full',
  'catfilters' => array(
    (object) array('slug' => 'guitarra', 'name' => 'Guitarra', 'term_id' => 1),
    (object) array('slug' => 'piano', 'name' => 'Piano', 'term_id' => 2),
    (object) array('slug' => 'bateria', 'name' => 'Batería', 'term_id' => 3)
  ),
  'initial_category' => (object) array('slug' => 'guitarra', 'name' => 'Guitarra', 'term_id' => 1)
);

?>

<!-- Ejemplo 1: Con categoría inicial "Guitarra" -->
<div class="course-percat <?php echo $acf_fields['type']; ?>" data-module="course-percat">
  <div class="container">
    <div class="course-percat__text">
      <h2><?php echo $acf_fields['title']; ?></h2>
      <p><?php echo $acf_fields['text']; ?></p>
    </div>

    <!-- Category Filters -->
    <div class="course-percat__filters" data-initial-category="guitarra">
      <!-- Botón "Todos" (no activo) -->
      <button class="course-percat__filter-btn" data-category="all">Todos</button>

      <!-- Botón "Guitarra" (activo por ser la categoría inicial) -->
      <button class="course-percat__filter-btn active" data-category="guitarra">Guitarra</button>

      <!-- Otros botones -->
      <button class="course-percat__filter-btn" data-category="piano">Piano</button>
      <button class="course-percat__filter-btn" data-category="bateria">Batería</button>
    </div>

    <div class="course-percat__items">
      <!-- Los elementos se cargarán dinámicamente -->
      <!-- Solo se mostrarán inicialmente los elementos con categoría "guitarra" -->
    </div>
  </div>
</div>

<?php
// Ejemplo 2: Sin categoría inicial (por defecto "Todos")
$acf_fields_2 = array(
  'title' => 'Todos los Cursos',
  'text' => 'Explora todos nuestros cursos',
  'limit' => 6,
  'postType' => 'cursos-wp',
  'type' => 'full',
  'catfilters' => array(
    (object) array('slug' => 'guitarra', 'name' => 'Guitarra', 'term_id' => 1),
    (object) array('slug' => 'piano', 'name' => 'Piano', 'term_id' => 2),
    (object) array('slug' => 'bateria', 'name' => 'Batería', 'term_id' => 3)
  ),
  'initial_category' => null // Sin categoría inicial
);
?>

<!-- Ejemplo 2: Sin categoría inicial (por defecto "Todos") -->
<div class="course-percat <?php echo $acf_fields_2['type']; ?>" data-module="course-percat">
  <div class="container">
    <div class="course-percat__text">
      <h2><?php echo $acf_fields_2['title']; ?></h2>
      <p><?php echo $acf_fields_2['text']; ?></p>
    </div>

    <!-- Category Filters -->
    <div class="course-percat__filters" data-initial-category="all">
      <!-- Botón "Todos" (activo por defecto) -->
      <button class="course-percat__filter-btn active" data-category="all">Todos</button>

      <!-- Otros botones -->
      <button class="course-percat__filter-btn" data-category="guitarra">Guitarra</button>
      <button class="course-percat__filter-btn" data-category="piano">Piano</button>
      <button class="course-percat__filter-btn" data-category="bateria">Batería</button>
    </div>

    <div class="course-percat__items">
      <!-- Los elementos se cargarán dinámicamente -->
      <!-- Se mostrarán todos los elementos inicialmente -->
    </div>
  </div>
</div>

<script>
  // Ejemplo de cómo funciona el JavaScript
  document.addEventListener('DOMContentLoaded', function () {
    // El módulo se inicializa automáticamente
    // Para el primer ejemplo: mostrará solo cursos de guitarra
    // Para el segundo ejemplo: mostrará todos los cursos

    console.log('Módulos course-percat inicializados');

    // Puedes acceder a las instancias si necesitas control programático
    const modules = document.querySelectorAll('[data-module="course-percat"]');
    modules.forEach(function (module, index) {
      const initialCategory = module.querySelector('.course-percat__filters').dataset.initialCategory;
      console.log(`Módulo ${index + 1}: Categoría inicial = ${initialCategory}`);
    });
  });
</script>

<style>
  /* Estilos de ejemplo */
  .course-percat {
    padding: 2rem 0;
  }

  .course-percat__filters {
    margin-bottom: 2rem;
    text-align: center;
  }

  .course-percat__filter-btn {
    margin: 0 0.5rem;
    padding: 0.5rem 1rem;
    border: 2px solid #ddd;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .course-percat__filter-btn.active {
    background: #007cba;
    color: white;
    border-color: #007cba;
  }

  .course-percat__filter-btn:hover {
    background: #f0f0f0;
  }

  .course-percat__filter-btn.active:hover {
    background: #005a87;
  }
</style>