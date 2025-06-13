<div class="container texto">
<?php 
$image = get_sub_field('imagen');
if( !empty( $image ) ): ?>
    <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
<?php endif; ?>
<h2 style="color:<?php the_sub_field("color")?>"><?php the_sub_field("texto")?></h2>
</div>
