<div class="course-percat <?php the_sub_field('type') ?>" data-module="course-percat">
  <div class="container">
    <div class="course-percat__text">
      <h2><?php the_sub_field('title'); ?></h2>
      <p><?php the_sub_field('text'); ?></p>
    </div>

    <!-- Category Filters -->
    <?php
    // Get categories from ACF field (returns term objects)
    $catfilters = get_sub_field('catfilters');

    // Only show filters if catfilters has data
    if (!empty($catfilters)) {
      ?>
      <div class="course-percat__filters">
        <?php
        // Always show "Todos" button first
        echo '<button class="course-percat__filter-btn active" data-category="all">';
        echo 'Todos';
        echo '</button>';

        // Use the ACF term objects directly
        $categories = $catfilters;

        // Generate filter buttons
        if (!empty($categories)) {
          foreach ($categories as $category) {
            if (is_object($category) && isset($category->slug) && isset($category->name)) {
              echo '<button class="course-percat__filter-btn" data-category="' . esc_attr($category->slug) . '">';
              echo esc_html($category->name);
              echo '</button>';
            }
          }
        }

        // Debug: Print available filter categories
        echo '<!-- Debug: Available filter categories: ';
        if (!empty($categories)) {
          foreach ($categories as $cat) {
            if (is_object($cat) && isset($cat->slug) && isset($cat->name)) {
              echo $cat->slug . ' (' . $cat->name . '), ';
            }
          }
        }
        echo ' -->';
        ?>
      </div>
      <?php
    }
    ?>

    <div class="course-percat__items">
      <?php
      // Build query based on catfilters
      $args = array(
        'posts_per_page' => get_sub_field('limit'),
        'orderby' => 'date',
        'post_type' => get_sub_field('postType'),
        'order' => 'ASC'
      );

      // If catfilters has data, filter by those categories
      if (!empty($catfilters)) {
        $category_ids = array();
        foreach ($catfilters as $category) {
          if (is_object($category) && isset($category->term_id)) {
            $category_ids[] = $category->term_id;
          }
        }

        if (!empty($category_ids)) {
          $args['tax_query'] = array(
            array(
              'taxonomy' => 'category',
              'field' => 'term_id',
              'terms' => $category_ids,
              'operator' => 'IN'
            )
          );
        }
      } else {
        // If no catfilters, use the original category filter
        $category_name = get_sub_field('category');
        if (!empty($category_name)) {
          $args['category_name'] = $category_name;
        }
      }
      ?>
      <?php $family = new WP_Query($args); ?>
      <?php
      $total_posts = $family->found_posts;
      $current_post = 0;
      echo '<!-- Debug: Total posts found: ' . $total_posts . ' -->';
      ?>
      <?php while ($family->have_posts()):
        $family->the_post();
        $current_post++;
        echo '<!-- Debug: Processing post ' . $current_post . ' of ' . $total_posts . ': ' . get_the_title() . ' -->';
        ?>
        <?php
        $url = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
        $categories = get_the_category();
        $category_classes = '';
        $category_names = array();

        foreach ($categories as $category) {
          $category_classes .= ' category-' . $category->slug;
          $category_names[] = $category->name;
        }

        // Debug: Print item categories
        echo '<!-- Debug: Item "' . get_the_title() . '" has categories: ' . implode(', ', $category_names) . ' (classes: ' . $category_classes . ') -->';

        // Extra debug for 6th item
        if ($current_post === 6) {
          echo '<!-- Debug: 6TH ITEM - Title: ' . get_the_title() . ' -->';
          echo '<!-- Debug: 6TH ITEM - Categories: ' . implode(', ', $category_names) . ' -->';
          echo '<!-- Debug: 6TH ITEM - Classes: ' . $category_classes . ' -->';
        }
        ?>
        <div class="course-percat__item<?php echo $category_classes; ?>"
          data-categories="<?php echo esc_attr($category_classes); ?>">
          <div class="course-percat__item-image">
            <a href="<?php the_permalink(); ?>">
              <?php $poster = get_field('poster'); ?>
              <img src="<?php echo $url; ?>" alt="<?php echo $poster['alt']; ?>">
            </a>
          </div>
          <div class="course-percat__item-title">
            <h2><?php the_title(); ?></h2>
            <p>GL Music</p>

            <?php if (is_user_logged_in()): ?>
              <div class="heart-container">
                <?php echo do_shortcode('[gl_favorite_heart]'); ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile;
      echo '<!-- Debug: Loop finished, processed ' . $current_post . ' posts -->';
      wp_reset_postdata(); ?>
    </div>
  </div>
</div>