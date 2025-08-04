<?php
if (!defined('ABSPATH')) exit;

class Whise_Shortcodes {
    
    public function __construct() {
        add_action('init', [$this, 'register_shortcodes']);
    }
    
    public function register_shortcodes() {
        add_shortcode('whise_properties', [$this, 'properties_list']);
        add_shortcode('whise_property', [$this, 'single_property']);
        add_shortcode('whise_search', [$this, 'search_form']);
    }
    
    /**
     * Affiche une liste de propriétés
     */
    public function properties_list($atts) {
        $atts = shortcode_atts([
            'limit' => 10,
            'type' => '',
            'city' => '',
            'status' => 'publish'
        ], $atts);
        
        $args = [
            'post_type' => 'property',
            'post_status' => $atts['status'],
            'posts_per_page' => intval($atts['limit']),
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        // Filtres par meta
        $meta_query = [];
        
        if (!empty($atts['type'])) {
            $meta_query[] = [
                'key' => 'property_type',
                'value' => $atts['type'],
                'compare' => '='
            ];
        }
        
        if (!empty($atts['city'])) {
            $meta_query[] = [
                'key' => 'city',
                'value' => $atts['city'],
                'compare' => '='
            ];
        }
        
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }
        
        $properties = get_posts($args);
        
        if (empty($properties)) {
            return '<p>Aucune propriété trouvée.</p>';
        }
        
        ob_start();
        ?>
        <div class="whise-properties-list">
            <?php foreach ($properties as $property) : ?>
                <div class="whise-property-item">
                    <h3><a href="<?php echo get_permalink($property->ID); ?>"><?php echo esc_html($property->post_title); ?></a></h3>
                    <?php
                    $price = get_post_meta($property->ID, 'price_formatted', true);
                    $surface = get_post_meta($property->ID, 'surface', true);
                    $city = get_post_meta($property->ID, 'city', true);
                    ?>
                    <?php if ($price) : ?>
                        <p class="price"><?php echo esc_html($price); ?></p>
                    <?php endif; ?>
                    <?php if ($surface) : ?>
                        <p class="surface"><?php echo esc_html($surface); ?> m²</p>
                    <?php endif; ?>
                    <?php if ($city) : ?>
                        <p class="city"><?php echo esc_html($city); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Affiche une propriété spécifique
     */
    public function single_property($atts) {
        $atts = shortcode_atts([
            'id' => 0
        ], $atts);
        
        if (!$atts['id']) {
            return '<p>ID de propriété manquant.</p>';
        }
        
        $property = get_post($atts['id']);
        
        if (!$property || $property->post_type !== 'property') {
            return '<p>Propriété non trouvée.</p>';
        }
        
        ob_start();
        ?>
        <div class="whise-single-property">
            <h2><?php echo esc_html($property->post_title); ?></h2>
            <?php
            $price = get_post_meta($property->ID, 'price_formatted', true);
            $surface = get_post_meta($property->ID, 'surface', true);
            $city = get_post_meta($property->ID, 'city', true);
            $address = get_post_meta($property->ID, 'address', true);
            $description = get_post_meta($property->ID, 'short_description', true);
            ?>
            
            <?php if ($price) : ?>
                <p class="price"><?php echo esc_html($price); ?></p>
            <?php endif; ?>
            
            <?php if ($surface) : ?>
                <p class="surface">Surface: <?php echo esc_html($surface); ?> m²</p>
            <?php endif; ?>
            
            <?php if ($city || $address) : ?>
                <p class="location">
                    <?php if ($address) echo esc_html($address); ?>
                    <?php if ($city) echo ', ' . esc_html($city); ?>
                </p>
            <?php endif; ?>
            
            <?php if ($description) : ?>
                <div class="description"><?php echo wp_kses_post($description); ?></div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Formulaire de recherche de propriétés
     */
    public function search_form($atts) {
        $atts = shortcode_atts([
            'action' => '',
            'method' => 'get'
        ], $atts);
        
        ob_start();
        ?>
        <form class="whise-search-form" method="<?php echo esc_attr($atts['method']); ?>" action="<?php echo esc_url($atts['action']); ?>">
            <div class="search-field">
                <label for="whise_search_city">Ville</label>
                <input type="text" id="whise_search_city" name="city" value="<?php echo esc_attr($_GET['city'] ?? ''); ?>">
            </div>
            
            <div class="search-field">
                <label for="whise_search_type">Type</label>
                <select id="whise_search_type" name="type">
                    <option value="">Tous les types</option>
                    <option value="appartement" <?php selected($_GET['type'] ?? '', 'appartement'); ?>>Appartement</option>
                    <option value="maison" <?php selected($_GET['type'] ?? '', 'maison'); ?>>Maison</option>
                    <option value="bureau" <?php selected($_GET['type'] ?? '', 'bureau'); ?>>Bureau</option>
                </select>
            </div>
            
            <div class="search-field">
                <label for="whise_search_min_price">Prix minimum</label>
                <input type="number" id="whise_search_min_price" name="min_price" value="<?php echo esc_attr($_GET['min_price'] ?? ''); ?>">
            </div>
            
            <div class="search-field">
                <label for="whise_search_max_price">Prix maximum</label>
                <input type="number" id="whise_search_max_price" name="max_price" value="<?php echo esc_attr($_GET['max_price'] ?? ''); ?>">
            </div>
            
            <div class="search-field">
                <label for="whise_search_surface">Surface minimum (m²)</label>
                <input type="number" id="whise_search_surface" name="surface" value="<?php echo esc_attr($_GET['surface'] ?? ''); ?>">
            </div>
            
            <button type="submit" class="search-submit">Rechercher</button>
        </form>
        <?php
        return ob_get_clean();
    }
}
