<?php
if (!defined('ABSPATH')) exit;

class Whise_Property_CPT {
    public function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_action('init', [$this, 'register_taxonomies']);
        add_action('init', [$this, 'register_meta_fields']);
        // Pour initialiser les termes par défaut à l'activation
        add_action('whise_init_default_terms', [$this, 'init_default_terms']);
    }

    public function register_post_type() {
        $labels = [
            'name' => __('Properties', 'whise-integration'),
            'singular_name' => __('Property', 'whise-integration'),
        ];
        $args = [
            'label' => __('Properties', 'whise-integration'),
            'labels' => $labels,
            'public' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'has_archive' => true,
            'hierarchical' => false,
            'menu_icon' => 'dashicons-building',
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
            'show_in_rest' => true,
            'hierarchical' => false,
        ]);
        // transaction_type
        register_taxonomy('transaction_type', 'property', [
            'label' => __('Type de transaction', 'whise-integration'),
            'public' => true,
            'show_in_rest' => true,
            'hierarchical' => false,
        ]);
        // property_city
        register_taxonomy('property_city', 'property', [
            'label' => __('Ville', 'whise-integration'),
            'public' => true,
            'show_in_rest' => true,
            'hierarchical' => false,
        ]);
        // property_status
        register_taxonomy('property_status', 'property', [
            'label' => __('Statut', 'whise-integration'),
            'public' => true,
            'show_in_rest' => true,
            'hierarchical' => false,
        ]);
    }

    /**
     * Enregistre les meta fields obligatoires pour le CPT property et les expose à l'API REST
     */
    public function register_meta_fields() {
        $fields = [
            'whise_id' => 'ID Whise unique',
            'price' => 'Prix (numérique)',
            'price_formatted' => 'Prix formaté (€350.000)',
            'surface' => 'Surface (m²)',
            'rooms' => 'Nombre de chambres',
            'bathrooms' => 'Nombre de SDB',
            'property_type' => 'Type (appartement/maison/bureau)',
            'transaction_type' => 'Vente/Location',
            'address' => 'Adresse complète',
            'city' => 'Ville',
            'postal_code' => 'Code postal',
            'description' => 'Description détaillée',
            'energy_class' => 'Classe énergétique',
            'available_date' => 'Date de disponibilité',
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
}
