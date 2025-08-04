<?php
if (!defined('ABSPATH')) exit;

class Whise_Property_CPT {
    public function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_action('init', [$this, 'register_taxonomies']);
        add_action('init', [$this, 'register_meta_fields']);
        // Pour initialiser les termes par défaut à l'activation
        add_action('whise_init_default_terms', [$this, 'init_default_terms']);
        // Force la réinitialisation des taxonomies si nécessaire
        add_action('admin_init', [$this, 'check_taxonomies_visibility']);
        // Hook pour forcer la réinitialisation lors de l'activation
        add_action('whise_force_reset', [$this, 'force_taxonomies_reset']);
    }

    public function register_post_type() {
        $labels = [
            'name' => __('Properties', 'whise-integration'),
            'singular_name' => __('Property', 'whise-integration'),
            'menu_name' => __('Biens', 'whise-integration'),
            'add_new' => __('Ajouter un bien', 'whise-integration'),
            'add_new_item' => __('Ajouter un nouveau bien', 'whise-integration'),
            'edit_item' => __('Modifier le bien', 'whise-integration'),
            'new_item' => __('Nouveau bien', 'whise-integration'),
            'view_item' => __('Voir le bien', 'whise-integration'),
            'search_items' => __('Rechercher des biens', 'whise-integration'),
            'not_found' => __('Aucun bien trouvé', 'whise-integration'),
            'not_found_in_trash' => __('Aucun bien trouvé dans la corbeille', 'whise-integration'),
        ];
        $args = [
            'label' => __('Properties', 'whise-integration'),
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'has_archive' => true,
            'hierarchical' => false,
            'menu_icon' => 'dashicons-building',
            'menu_position' => 20,
            'capability_type' => 'post',
            'rewrite' => [
                'slug' => 'biens/%property_city%/%property_type%',
                'with_front' => false
            ],
        ];
        register_post_type('property', $args);
        // Hook pour URLs SEO-friendly
        add_filter('post_type_link', [$this, 'custom_property_permalink'], 10, 2);
    }

    /**
     * Génère une URL SEO-friendly pour chaque bien : /biens/{ville}/{type}/{postname}/
     */
    public function custom_property_permalink($url, $post) {
        if ($post->post_type == 'property') {
            $city = get_post_meta($post->ID, 'city', true);
            $type = get_post_meta($post->ID, 'property_type', true);
            $city = sanitize_title($city ?: 'ville');
            $type = sanitize_title($type ?: 'type');
            $url = str_replace('%property_city%', $city, $url);
            $url = str_replace('%property_type%', $type, $url);
        }
        return $url;
    }

    /**
     * Initialise les termes par défaut pour les taxonomies principales
     */
    public function init_default_terms() {
        // property_type
        $types = ['appartement', 'maison', 'bureau', 'terrain'];
        foreach ($types as $type) {
            if (!term_exists($type, 'property_type')) {
                wp_insert_term($type, 'property_type');
            }
        }
        // transaction_type
        $transactions = ['vente', 'location'];
        foreach ($transactions as $tr) {
            if (!term_exists($tr, 'transaction_type')) {
                wp_insert_term($tr, 'transaction_type');
            }
        }
        // property_status
        $statuses = ['disponible', 'sous_option', 'vendu_loue'];
        foreach ($statuses as $st) {
            if (!term_exists($st, 'property_status')) {
                wp_insert_term($st, 'property_status');
            }
        }
    }

    public function register_taxonomies() {
        // property_type
        register_taxonomy('property_type', 'property', [
            'label' => __('Type de bien', 'whise-integration'),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'hierarchical' => false,
            'rewrite' => ['slug' => 'type-bien'],
        ]);
        
        // transaction_type
        register_taxonomy('transaction_type', 'property', [
            'label' => __('Type de transaction', 'whise-integration'),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'hierarchical' => false,
            'rewrite' => ['slug' => 'type-transaction'],
        ]);
        
        // property_city
        register_taxonomy('property_city', 'property', [
            'label' => __('Ville', 'whise-integration'),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'hierarchical' => false,
            'rewrite' => ['slug' => 'ville'],
        ]);
        
        // property_status
        register_taxonomy('property_status', 'property', [
            'label' => __('Statut', 'whise-integration'),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'hierarchical' => false,
            'rewrite' => ['slug' => 'statut'],
        ]);
    }

    /**
     * Enregistre les meta fields obligatoires pour le CPT property et les expose à l'API REST
     */
    public function register_meta_fields() {
        // Définition des types de champs
        $field_types = [
            'string' => ['whise_id', 'reference', 'address', 'city', 'postal_code', 'country', 'description', 'description_short'],
            'number' => ['price', 'surface', 'total_area', 'land_area', 'commercial_area', 'built_area', 'rooms', 'bathrooms', 'floors', 'construction_year'],
            'boolean' => ['is_immediately_available', 'parking', 'garage', 'terrace', 'garden', 'swimming_pool', 'elevator', 'cellar', 'attic'],
            'array' => ['images', 'details'],
            'float' => ['latitude', 'longitude']
        ];
        
        $fields = [
            // Identifiants
            'whise_id' => ['desc' => 'ID Whise unique', 'type' => 'string'],
            'reference' => ['desc' => 'Référence du bien', 'type' => 'string'],
            
            // Prix et conditions
            'price' => ['desc' => 'Prix (numérique)', 'type' => 'number'],
            'price_formatted' => ['desc' => 'Prix formaté (€350.000)', 'type' => 'string'],
            'price_type' => ['desc' => 'Type de prix (vente/location)', 'type' => 'string'],
            'price_supplement' => ['desc' => 'Supplément de prix', 'type' => 'string'],
            'charges' => ['desc' => 'Charges mensuelles', 'type' => 'number'],
            'price_conditions' => ['desc' => 'Conditions de prix', 'type' => 'string'],
            
            // Surfaces
            'surface' => ['desc' => 'Surface habitable (m²)', 'type' => 'number'],
            'total_area' => ['desc' => 'Surface totale (m²)', 'type' => 'number'],
            'land_area' => ['desc' => 'Surface terrain (m²)', 'type' => 'number'],
            'commercial_area' => ['desc' => 'Surface commerciale (m²)', 'type' => 'number'],
            'built_area' => ['desc' => 'Surface bâtie (m²)', 'type' => 'number'],
            
            // Pièces
            'rooms' => ['desc' => 'Nombre de chambres', 'type' => 'number'],
            'bathrooms' => ['desc' => 'Nombre de SDB', 'type' => 'number'],
            'floors' => ['desc' => 'Nombre d\'étages', 'type' => 'number'],
            
            // Type et statut
            'property_type' => ['desc' => 'Type (appartement/maison/bureau)', 'type' => 'string'],
            'transaction_type' => ['desc' => 'Vente/Location', 'type' => 'string'],
            'status' => ['desc' => 'Statut du bien', 'type' => 'string'],
            'construction_year' => ['desc' => 'Année de construction', 'type' => 'number'],
            
            // Localisation
            'address' => ['desc' => 'Adresse complète', 'type' => 'string'],
            'city' => ['desc' => 'Ville', 'type' => 'string'],
            'postal_code' => ['desc' => 'Code postal', 'type' => 'string'],
            'country' => ['desc' => 'Pays', 'type' => 'string'],
            'latitude' => ['desc' => 'Latitude', 'type' => 'float'],
            'longitude' => ['desc' => 'Longitude', 'type' => 'float'],
            
            // Descriptions
            'description' => ['desc' => 'Description détaillée', 'type' => 'string'],
            'description_short' => ['desc' => 'Description courte', 'type' => 'string'],
            
            // Énergie
            'energy_class' => ['desc' => 'Classe énergétique', 'type' => 'string'],
            'epc_value' => ['desc' => 'Valeur PEB', 'type' => 'number'],
            'heating_type' => ['desc' => 'Type de chauffage', 'type' => 'string'],
            
            // Données cadastrales
            'cadastral_income' => ['desc' => 'Revenu cadastral', 'type' => 'number'],
            'cadastral_data' => ['desc' => 'Données cadastrales', 'type' => 'string'],
            
            // Équipements 
            'kitchen_type' => ['desc' => 'Type de cuisine', 'type' => 'string'],
            'parking' => ['desc' => 'Parking', 'type' => 'boolean'],
            'garage' => ['desc' => 'Garage', 'type' => 'boolean'],
            'terrace' => ['desc' => 'Terrasse', 'type' => 'boolean'],
            'garden' => ['desc' => 'Jardin', 'type' => 'boolean'],
            'swimming_pool' => ['desc' => 'Piscine', 'type' => 'boolean'],
            'elevator' => ['desc' => 'Ascenseur', 'type' => 'boolean'],
            'cellar' => ['desc' => 'Cave', 'type' => 'boolean'],
            'attic' => ['desc' => 'Grenier', 'type' => 'boolean'],
            
            // Proximité
            'proximity_school' => ['desc' => 'Proximité écoles', 'type' => 'string'],
            'proximity_shops' => ['desc' => 'Proximité commerces', 'type' => 'string'],
            'proximity_transport' => ['desc' => 'Proximité transports', 'type' => 'string'],
            'proximity_hospital' => ['desc' => 'Proximité hôpital', 'type' => 'string'],
            
            // Disponibilité
            'availability' => ['desc' => 'Disponibilité', 'type' => 'string'],
            'is_immediately_available' => ['desc' => 'Disponible immédiatement', 'type' => 'boolean'],
            'available_date' => ['desc' => 'Date de disponibilité', 'type' => 'string'],
            
            // Orientation et vues
            'orientation' => ['desc' => 'Orientation', 'type' => 'string'],
            'view' => ['desc' => 'Vue', 'type' => 'string'],
            'images' => 'URLs des images (sérialisé)',
            'latitude' => 'Coordonnée GPS',
            'longitude' => 'Coordonnée GPS',
        ];
        foreach ($fields as $key => $desc) {
            register_post_meta('property', $key, [
                'show_in_rest' => true,
                'single' => true,
                'type' => 'string',
                'description' => $desc,
                'auth_callback' => function() { return true; },
                'sanitize_callback' => [$this, 'sanitize_meta_value'],
            ]);
        }
    }

    /**
     * Sanitize meta value (à adapter selon le champ)
     */
    public function sanitize_meta_value($value, $post_id, $meta_key) {
        // Pour l'instant, simple sanitization. À spécialiser par champ si besoin.
        return sanitize_text_field($value);
    }

    /**
     * Vérifie et corrige la visibilité du post type et des taxonomies dans l'admin
     */
    public function check_taxonomies_visibility() {
        $problems = [];
        
        // Vérifier d'abord si le post type existe
        if (!post_type_exists('property')) {
            error_log('Whise Integration: Post type "property" n\'existe pas - tentative de réenregistrement');
            $problems[] = 'post_type';
            
            // Tenter de réenregistrer le post type
            $this->register_post_type();
            flush_rewrite_rules();
        }
        
        // Vérifier si les taxonomies sont visibles
        $taxonomies = ['property_type', 'transaction_type', 'property_city', 'property_status'];
        $missing_taxonomies = [];
        
        foreach ($taxonomies as $taxonomy) {
            if (!taxonomy_exists($taxonomy)) {
                $missing_taxonomies[] = $taxonomy;
                error_log('Whise Integration: Taxonomie manquante - ' . $taxonomy);
            }
        }
        
        // Si des taxonomies manquent, les réenregistrer
        if (!empty($missing_taxonomies)) {
            error_log('Whise Integration: Réenregistrement des taxonomies manquantes');
            $this->register_taxonomies();
            $problems[] = 'taxonomies';
        }
        
        // Si des problèmes ont été détectés, forcer le flush des règles de réécriture
        if (!empty($problems)) {
            flush_rewrite_rules();
            
            // Afficher un message d'alerte
            add_action('admin_notices', function() use ($problems, $missing_taxonomies) {
                echo '<div class="notice notice-warning is-dismissible"><p>';
                echo '<strong>Whise Integration:</strong> ';
                
                if (in_array('post_type', $problems)) {
                    echo 'Post type "property" réenregistré. ';
                }
                
                if (!empty($missing_taxonomies)) {
                    echo 'Taxonomies manquantes détectées et réinitialisées : ' . implode(', ', $missing_taxonomies) . '. ';
                }
                
                echo '<br><small>Si le problème persiste, essayez de désactiver puis réactiver le plugin.</small>';
                echo '</p></div>';
            });
        }
    }
    
    /**
     * Force la réinitialisation complète du post type et des taxonomies
     */
    public function force_taxonomies_reset() {
        // Désenregistrer le post type existant
        unregister_post_type('property');
        
        // Désenregistrer les taxonomies existantes
        unregister_taxonomy('property_type');
        unregister_taxonomy('transaction_type');
        unregister_taxonomy('property_city');
        unregister_taxonomy('property_status');
        
        // Réenregistrer le post type
        $this->register_post_type();
        
        // Réenregistrer les taxonomies
        $this->register_taxonomies();
        
        // Initialiser les termes par défaut
        $this->init_default_terms();
        
        // Flush les règles de réécriture
        flush_rewrite_rules();
        
        error_log('Whise Integration: Réinitialisation complète du post type et des taxonomies effectuée');
    }
}
