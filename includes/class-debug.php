<?php
if (!defined('ABSPATH')) exit;

class Whise_Debug {
    
    /**
     * Affiche les données d'une propriété pour debug
     */
    public static function debug_property($post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        
        if (!$post_id) {
            echo '<p>Aucun ID de post trouvé</p>';
            return;
        }
        
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'property') {
            echo '<p>Post non trouvé ou pas de type "property"</p>';
            return;
        }
        
        echo '<div style="background: #f0f0f0; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">';
        echo '<h3>Debug - Propriété ID: ' . $post_id . '</h3>';
        echo '<h4>Titre: ' . $post->post_title . '</h4>';
        
        // Récupération de tous les meta
        $meta = get_post_meta($post_id);
        
        echo '<h4>Métadonnées:</h4>';
        echo '<table style="width: 100%; border-collapse: collapse;">';
        echo '<tr style="background: #ddd;"><th style="border: 1px solid #999; padding: 5px;">Clé</th><th style="border: 1px solid #999; padding: 5px;">Valeur</th></tr>';
        
        foreach ($meta as $key => $values) {
            $value = is_array($values) ? $values[0] : $values;
            
            // Décodage JSON si nécessaire
            if (is_string($value) && (strpos($value, '{') === 0 || strpos($value, '[') === 0)) {
                $decoded = json_decode($value, true);
                if ($decoded !== null) {
                    $value = '<pre>' . print_r($decoded, true) . '</pre>';
                }
            }
            
            echo '<tr>';
            echo '<td style="border: 1px solid #999; padding: 5px; font-weight: bold;">' . esc_html($key) . '</td>';
            echo '<td style="border: 1px solid #999; padding: 5px;">' . (is_string($value) ? esc_html($value) : '<pre>' . print_r($value, true) . '</pre>') . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        
        // Vérification spécifique des champs importants
        echo '<h4>Vérification des champs importants:</h4>';
        $important_fields = [
            'whise_id' => 'ID Whise',
            'reference' => 'Référence',
            'price' => 'Prix',
            'price_formatted' => 'Prix formaté',
            'surface' => 'Surface',
            'address' => 'Adresse',
            'city' => 'Ville',
            'short_description' => 'Description courte',
            'images' => 'Images'
        ];
        
        echo '<ul>';
        foreach ($important_fields as $field => $label) {
            $value = get_post_meta($post_id, $field, true);
            $status = $value ? '✅' : '❌';
            echo '<li>' . $status . ' ' . $label . ': ' . (is_string($value) ? esc_html($value) : '<pre>' . print_r($value, true) . '</pre>') . '</li>';
        }
        echo '</ul>';
        
        echo '</div>';
    }
    
    /**
     * Affiche les statistiques d'import
     */
    public static function debug_import_stats() {
        $properties = get_posts([
            'post_type' => 'property',
            'post_status' => 'any',
            'numberposts' => -1
        ]);
        
        echo '<div style="background: #f0f0f0; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">';
        echo '<h3>Statistiques d\'import</h3>';
        echo '<p>Total propriétés: ' . count($properties) . '</p>';
        
        if (!empty($properties)) {
            $with_price = 0;
            $with_surface = 0;
            $with_address = 0;
            $with_images = 0;
            
            foreach ($properties as $property) {
                if (get_post_meta($property->ID, 'price', true)) $with_price++;
                if (get_post_meta($property->ID, 'surface', true)) $with_surface++;
                if (get_post_meta($property->ID, 'address', true)) $with_address++;
                if (get_post_meta($property->ID, 'images', true)) $with_images++;
            }
            
            echo '<ul>';
            echo '<li>Propriétés avec prix: ' . $with_price . '/' . count($properties) . '</li>';
            echo '<li>Propriétés avec surface: ' . $with_surface . '/' . count($properties) . '</li>';
            echo '<li>Propriétés avec adresse: ' . $with_address . '/' . count($properties) . '</li>';
            echo '<li>Propriétés avec images: ' . $with_images . '/' . count($properties) . '</li>';
            echo '</ul>';
        }
        
        // Logs de synchronisation
        $logs = get_option('whise_sync_logs', []);
        if (!empty($logs)) {
            echo '<h4>Derniers logs de synchronisation:</h4>';
            echo '<div style="max-height: 200px; overflow-y: auto; background: white; padding: 10px; border: 1px solid #ccc;">';
            foreach (array_slice($logs, -10) as $log) {
                echo '<div style="margin: 2px 0; font-family: monospace; font-size: 12px;">' . esc_html($log) . '</div>';
            }
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Teste la récupération des champs pour Elementor Loop Grid
     */
    public static function debug_elementor_fields() {
        echo '<div style="background: #f0f0f0; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">';
        echo '<h3>Champs disponibles pour Elementor Loop Grid</h3>';
        
        $properties = get_posts([
            'post_type' => 'property',
            'post_status' => 'publish',
            'numberposts' => 1
        ]);
        
        if (!empty($properties)) {
            $property = $properties[0];
            echo '<p>Exemple avec la propriété: ' . $property->post_title . '</p>';
            
            $fields = [
                'whise_id' => 'ID Whise',
                'reference' => 'Référence',
                'price' => 'Prix (numérique)',
                'price_formatted' => 'Prix formaté',
                'price_type' => 'Type de transaction',
                'charges' => 'Charges',
                'price_per_sqm' => 'Prix/m²',
                'surface' => 'Surface',
                'total_area' => 'Surface totale',
                'land_area' => 'Surface terrain',
                'rooms' => 'Chambres',
                'bathrooms' => 'Salles de bain',
                'floors' => 'Étages',
                'address' => 'Adresse',
                'city' => 'Ville',
                'postal_code' => 'Code postal',
                'construction_year' => 'Année construction',
                'heating_type' => 'Type chauffage',
                'furnished' => 'Meublé',
                'air_conditioning' => 'Climatisation',
                'elevator' => 'Ascenseur',
                'parking' => 'Parking',
                'garage' => 'Garage',
                'short_description' => 'Description courte'
            ];
            
            echo '<h4>Champs disponibles:</h4>';
            echo '<table style="width: 100%; border-collapse: collapse;">';
            echo '<tr style="background: #ddd;"><th style="border: 1px solid #999; padding: 5px;">Champ</th><th style="border: 1px solid #999; padding: 5px;">Valeur</th><th style="border: 1px solid #999; padding: 5px;">Pour Elementor</th></tr>';
            
            foreach ($fields as $field => $label) {
                $value = get_post_meta($property->ID, $field, true);
                $elementor_field = '{{' . $field . '}}';
                
                echo '<tr>';
                echo '<td style="border: 1px solid #999; padding: 5px;">' . esc_html($label) . '</td>';
                echo '<td style="border: 1px solid #999; padding: 5px;">' . (is_string($value) ? esc_html($value) : '<pre>' . print_r($value, true) . '</pre>') . '</td>';
                echo '<td style="border: 1px solid #999; padding: 5px; font-family: monospace;">' . esc_html($elementor_field) . '</td>';
                echo '</tr>';
            }
            
            echo '</table>';
        } else {
            echo '<p>Aucune propriété trouvée</p>';
        }
        
        echo '</div>';
    }
    
    /**
     * Ajoute un shortcode de debug
     */
    public static function init_shortcodes() {
        add_shortcode('whise_debug', [__CLASS__, 'shortcode_debug']);
        add_shortcode('whise_debug_stats', [__CLASS__, 'shortcode_debug_stats']);
        add_shortcode('whise_debug_fields', [__CLASS__, 'shortcode_debug_fields']);
        add_shortcode('whise_diagnostic', [__CLASS__, 'shortcode_diagnostic']);
    }
    
    public static function shortcode_debug($atts) {
        $atts = shortcode_atts(['id' => null], $atts);
        ob_start();
        self::debug_property($atts['id']);
        return ob_get_clean();
    }
    
    public static function shortcode_debug_stats($atts) {
        ob_start();
        self::debug_import_stats();
        return ob_get_clean();
    }
    
    public static function shortcode_debug_fields($atts) {
        ob_start();
        self::debug_elementor_fields();
        return ob_get_clean();
    }
    
    /**
     * Diagnostic complet du plugin
     */
    public static function diagnostic_plugin() {
        echo '<div style="background: #f0f0f0; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">';
        echo '<h3>Diagnostic complet du plugin Whise Integration</h3>';
        
        // Vérification du post type
        $post_type_exists = post_type_exists('property');
        echo '<h4>Post Type "property":</h4>';
        echo '<p>' . ($post_type_exists ? '✅ Enregistré' : '❌ Non enregistré') . '</p>';
        
        if ($post_type_exists) {
            $post_type_obj = get_post_type_object('property');
            echo '<ul>';
            echo '<li>Public: ' . ($post_type_obj->public ? '✅' : '❌') . '</li>';
            echo '<li>Show UI: ' . ($post_type_obj->show_ui ? '✅' : '❌') . '</li>';
            echo '<li>Show in Menu: ' . ($post_type_obj->show_in_menu ? '✅' : '❌') . '</li>';
            echo '<li>Show in REST: ' . ($post_type_obj->show_in_rest ? '✅' : '❌') . '</li>';
            echo '</ul>';
        }
        
        // Vérification des taxonomies
        echo '<h4>Taxonomies:</h4>';
        $taxonomies = ['property_type', 'transaction_type', 'property_city', 'property_status'];
        foreach ($taxonomies as $taxonomy) {
            $exists = taxonomy_exists($taxonomy);
            echo '<p>' . $taxonomy . ': ' . ($exists ? '✅ Enregistrée' : '❌ Non enregistrée') . '</p>';
            
            if ($exists) {
                $taxonomy_obj = get_taxonomy($taxonomy);
                echo '<ul>';
                echo '<li>Public: ' . ($taxonomy_obj->public ? '✅' : '❌') . '</li>';
                echo '<li>Show UI: ' . ($taxonomy_obj->show_ui ? '✅' : '❌') . '</li>';
                echo '<li>Show in Menu: ' . ($taxonomy_obj->show_in_menu ? '✅' : '❌') . '</li>';
                echo '</ul>';
            }
        }
        
        // Vérification des classes
        echo '<h4>Classes du plugin:</h4>';
        $classes = ['Whise_Property_CPT', 'Whise_Admin', 'Whise_Sync_Manager', 'Whise_API'];
        foreach ($classes as $class) {
            echo '<p>' . $class . ': ' . (class_exists($class) ? '✅ Chargée' : '❌ Non chargée') . '</p>';
        }
        
        // Vérification des constantes
        echo '<h4>Constantes:</h4>';
        echo '<p>WHISE_PLUGIN_URL: ' . (defined('WHISE_PLUGIN_URL') ? '✅ Définie' : '❌ Non définie') . '</p>';
        echo '<p>WHISE_PLUGIN_PATH: ' . (defined('WHISE_PLUGIN_PATH') ? '✅ Définie' : '❌ Non définie') . '</p>';
        
        // Vérification des règles de réécriture
        echo '<h4>Règles de réécriture:</h4>';
        $rewrite_rules = get_option('rewrite_rules');
        echo '<p>Règles de réécriture: ' . (!empty($rewrite_rules) ? '✅ Présentes' : '❌ Absentes') . '</p>';
        
        // Vérification des propriétés
        if ($post_type_exists) {
            $properties = get_posts([
                'post_type' => 'property',
                'post_status' => 'any',
                'numberposts' => -1
            ]);
            echo '<h4>Propriétés:</h4>';
            echo '<p>Total: ' . count($properties) . '</p>';
            
            if (!empty($properties)) {
                $published = 0;
                $draft = 0;
                foreach ($properties as $property) {
                    if ($property->post_status === 'publish') $published++;
                    else $draft++;
                }
                echo '<p>Publiées: ' . $published . '</p>';
                echo '<p>Brouillons: ' . $draft . '</p>';
            }
        }
        
        echo '</div>';
    }
    
    public static function shortcode_diagnostic($atts) {
        ob_start();
        self::diagnostic_plugin();
        return ob_get_clean();
    }
}

// Initialisation des shortcodes de debug
add_action('init', ['Whise_Debug', 'init_shortcodes']); 