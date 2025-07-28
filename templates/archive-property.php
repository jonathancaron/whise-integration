<?php
// Template fallback pour la liste des biens
if (!defined('ABSPATH')) exit;
get_header();
?>
<div class="whise-property-archive">
    <h1><?php post_type_archive_title(); ?></h1>
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div class="property-item">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </div>
    <?php endwhile; endif; ?>
</div>
<?php get_footer(); ?>
