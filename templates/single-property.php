<?php
// Template fallback pour un bien unique
if (!defined('ABSPATH')) exit;
get_header();
?>
<div class="whise-property-single">
    <h1><?php the_title(); ?></h1>
    <div><?php the_content(); ?></div>
    <!-- Affichage des champs personnalisés ici -->
</div>
<?php get_footer(); ?>
