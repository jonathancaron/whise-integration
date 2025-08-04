<?php
if (!defined('ABSPATH')) exit;

class Whise_Property_CPT {
    public function __construct() {
        // Enregistrement dans le bon ordre sur 'init' (taxonomies, post type, meta fields, termes)
        add_action('init', [$this, 'register_taxonomies'], 0);
        add_action('init', [$this, 'register_post_type'], 1);
        add_action('init', [$this, 'register_meta_fields'], 2);
        add_action('init', [$this, 'init_default_terms'], 3);

        // Hook d'activation pour tout forcer
        if (defined('WHISE_PLUGIN_FILE')) {
            register_activation_hook(WHISE_PLUGIN_FILE, [$this, 'on_activation']);
        }

        // Diagnostic admin si problème
        add_action('admin_notices', [$this, 'admin_diagnostic_notice']);
        
        // Ajout des colonnes personnalisées dans la liste des biens
        add_filter('manage_property_posts_columns', [$this, 'add_custom_columns']);
        add_action('manage_property_posts_custom_column', [$this, 'display_custom_columns'], 10, 2);
        
        // Ajout de la metabox détaillée
        add_action('add_meta_boxes', [$this, 'add_property_details_metabox']);
    }

    public function on_activation() {
        // Enregistre tout et flush
        $this->register_taxonomies();
        $this->register_post_type();
        $this->register_meta_fields();
        $this->init_default_terms();
        flush_rewrite_rules();
    }

    public function admin_diagnostic_notice() {
        $missing = [];
        if (!post_type_exists('property')) $missing[] = 'Post type "property"';
        foreach(['property_type','transaction_type','property_city','property_status'] as $tax) {
            if (!taxonomy_exists($tax)) $missing[] = 'Taxonomie "'.$tax.'"';
        }
        if (!empty($missing)) {
            echo '<div class="notice notice-error"><p><strong>Whise Integration:</strong> Problème d\'enregistrement : '.implode(', ', $missing).'.<br>Essayez de désactiver/réactiver le plugin et de sauvegarder les permaliens.</p></div>';
        }
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
            'show_in_menu' => false,
            'show_in_rest' => true,
            'rest_base' => 'properties',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'],
            'has_archive' => true,
            'hierarchical' => false,
            'menu_icon' => 'dashicons-building',
            'menu_position' => 20,
            'capability_type' => 'post',
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
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
            'labels' => [
                'name' => __('Types de bien', 'whise-integration'),
                'singular_name' => __('Type de bien', 'whise-integration'),
                'menu_name' => __('Types de bien', 'whise-integration'),
                'all_items' => __('Tous les types', 'whise-integration'),
                'edit_item' => __('Modifier le type', 'whise-integration'),
                'view_item' => __('Voir le type', 'whise-integration'),
                'update_item' => __('Mettre à jour le type', 'whise-integration'),
                'add_new_item' => __('Ajouter un type', 'whise-integration'),
                'new_item_name' => __('Nouveau type', 'whise-integration'),
                'search_items' => __('Rechercher des types', 'whise-integration')
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'show_admin_column' => false,
            'hierarchical' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'type-bien', 'with_front' => false],
            'capabilities' => [
                'manage_terms' => 'manage_categories',
                'edit_terms' => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts'
            ]
        ]);
        // transaction_type
        register_taxonomy('transaction_type', 'property', [
            'label' => __('Type de transaction', 'whise-integration'),
            'labels' => [
                'name' => __('Types de transaction', 'whise-integration'),
                'singular_name' => __('Type de transaction', 'whise-integration'),
                'menu_name' => __('Transactions', 'whise-integration'),
                'all_items' => __('Toutes les transactions', 'whise-integration'),
                'edit_item' => __('Modifier la transaction', 'whise-integration'),
                'view_item' => __('Voir la transaction', 'whise-integration'),
                'update_item' => __('Mettre à jour la transaction', 'whise-integration'),
                'add_new_item' => __('Ajouter une transaction', 'whise-integration'),
                'new_item_name' => __('Nouvelle transaction', 'whise-integration'),
                'search_items' => __('Rechercher des transactions', 'whise-integration')
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'hierarchical' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'type-transaction', 'with_front' => false],
            'capabilities' => [
                'manage_terms' => 'manage_categories',
                'edit_terms' => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts'
            ]
        ]);
        // property_city
        register_taxonomy('property_city', 'property', [
            'label' => __('Ville', 'whise-integration'),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'hierarchical' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'ville', 'with_front' => false],
        ]);
        // property_status
        register_taxonomy('property_status', 'property', [
            'label' => __('Statut', 'whise-integration'),
            'labels' => [
                'name' => __('Statuts', 'whise-integration'),
                'singular_name' => __('Statut', 'whise-integration'),
                'menu_name' => __('Statuts', 'whise-integration'),
                'all_items' => __('Tous les statuts', 'whise-integration'),
                'edit_item' => __('Modifier le statut', 'whise-integration'),
                'view_item' => __('Voir le statut', 'whise-integration'),
                'update_item' => __('Mettre à jour le statut', 'whise-integration'),
                'add_new_item' => __('Ajouter un statut', 'whise-integration'),
                'new_item_name' => __('Nouveau statut', 'whise-integration'),
                'search_items' => __('Rechercher des statuts', 'whise-integration')
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'hierarchical' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'statut', 'with_front' => false],
            'capabilities' => [
                'manage_terms' => 'manage_categories',
                'edit_terms' => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts'
            ]
        ]);
    }

    /**
     * Enregistre les meta fields obligatoires pour le CPT property et les expose à l'API REST
     */
    public function register_meta_fields() {
        // Définition des types de champs avec leur type réel pour l'API REST
        $field_types = [
            'string' => ['whise_id', 'reference', 'address', 'city', 'postal_code', 'country', 'description', 'description_short',
                        'price_formatted', 'price_type', 'price_supplement', 'price_conditions', 'property_type', 
                        'transaction_type', 'status', 'energy_class', 'heating_type', 'kitchen_type',
                        'proximity_school', 'proximity_shops', 'proximity_transport', 'proximity_hospital',
                        'orientation', 'view', 'availability', 'available_date'],
            'number' => ['price', 'surface', 'total_area', 'land_area', 'commercial_area', 'built_area', 
                        'rooms', 'bedrooms', 'bathrooms', 'floors', 'construction_year', 'epc_value', 'cadastral_income'],
            'boolean' => ['is_immediately_available', 'parking', 'garage', 'terrace', 'garden', 
                         'swimming_pool', 'elevator', 'cellar', 'attic'],
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
            'rooms' => ['desc' => 'Nombre de pièces', 'type' => 'number'],
            'bedrooms' => ['desc' => 'Nombre de chambres', 'type' => 'number'],
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
        foreach ($fields as $key => $field_info) {
            // Déterminer le type réel pour l'API REST
            $type = 'string'; // Type par défaut
            foreach ($field_types as $field_type => $fields) {
                if (in_array($key, $fields)) {
                    $type = $field_type;
                    break;
                }
            }
            
            // Configuration de base pour tous les champs
            $args = [
                'show_in_rest' => true,
                'single' => true,
                'type' => $type,
                'description' => $field_info['desc'] ?? '',
                'auth_callback' => function() { return true; },
                'sanitize_callback' => [$this, 'sanitize_meta_value'],
            ];

            // Ajustements spécifiques par type
            if ($type === 'array') {
                $args['type'] = 'string'; // Les arrays sont stockés comme strings sérialisés
                $args['show_in_rest'] = [
                    'schema' => [
                        'type' => 'array',
                        'items' => ['type' => 'string']
                    ]
                ];
            }

            register_post_meta('property', $key, $args);
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
     * Ajoute des colonnes personnalisées à la liste des biens
     */
    public function add_custom_columns($columns) {
        $new_columns = array();
        
        // Réorganise les colonnes avec nos ajouts
        foreach($columns as $key => $value) {
            if ($key === 'title') {
                $new_columns[$key] = $value;
                $new_columns['reference'] = __('Référence', 'whise-integration');
                $new_columns['price'] = __('Prix', 'whise-integration');
                // On utilise les colonnes de taxonomies automatiques de WordPress
                $new_columns['taxonomy-property_type'] = __('Type', 'whise-integration');
                $new_columns['taxonomy-transaction_type'] = __('Transaction', 'whise-integration');
                $new_columns['taxonomy-property_city'] = __('Ville', 'whise-integration');
                $new_columns['taxonomy-property_status'] = __('Statut', 'whise-integration');
            } else if (!in_array($key, ['taxonomy-property_type', 'taxonomy-transaction_type', 'taxonomy-property_city', 'taxonomy-property_status'])) {
                // Éviter les doublons des taxonomies
                $new_columns[$key] = $value;
            }
        }
        
        return $new_columns;
    }

    /**
     * Affiche le contenu des colonnes personnalisées
     */
     public function display_custom_columns($column, $post_id) {
         switch ($column) {
             case 'reference':
                 $reference = get_post_meta($post_id, 'reference', true);
                 echo $reference ?: '—';
                 break;
             case 'price':
                 $price_formatted = get_post_meta($post_id, 'price_formatted', true);
                 if (!$price_formatted) {
                     $price = get_post_meta($post_id, 'price', true);
                     $currency = get_post_meta($post_id, 'currency', true) ?: '€';
                     $price_formatted = $price ? $currency . number_format($price, 0, ',', ' ') : '—';
                 }
                 echo $price_formatted;
                 break;
         }
     }

    /**
     * Ajoute la metabox de détails du bien
     */
    public function add_property_details_metabox() {
        add_meta_box(
            'property_details',
            __('Détails du bien', 'whise-integration'),
            [$this, 'render_property_details_metabox'],
            'property',
            'normal',
            'high'
        );
    }

    /**
     * Affiche la metabox de détails du bien
     */
    public function render_property_details_metabox($post) {
        $field_groups = [
            'identification' => [
                'title' => 'Identification',
                'fields' => ['whise_id', 'reference']
            ],
            'prix' => [
                'title' => 'Prix et conditions',
                'fields' => ['price', 'price_formatted', 'price_type', 'price_supplement', 'charges', 'price_conditions']
            ],
            'surfaces' => [
                'title' => 'Surfaces',
                'fields' => ['surface', 'total_area', 'land_area', 'commercial_area', 'built_area']
            ],
            'pieces' => [
                'title' => 'Pièces',
                'fields' => ['rooms', 'bedrooms', 'bathrooms', 'floors']
            ],
            'localisation' => [
                'title' => 'Localisation',
                'fields' => ['address', 'city', 'postal_code', 'country', 'latitude', 'longitude']
            ],
            'energie' => [
                'title' => 'Énergie',
                'fields' => ['energy_class', 'epc_value', 'heating_type']
            ],
            'equipements' => [
                'title' => 'Équipements',
                'fields' => ['kitchen_type', 'parking', 'garage', 'terrace', 'garden', 'swimming_pool', 
                           'elevator', 'cellar', 'attic']
            ],
            'proximite' => [
                'title' => 'Proximité',
                'fields' => ['proximity_school', 'proximity_shops', 'proximity_transport', 'proximity_hospital']
            ]
        ];

        wp_nonce_field('whise_property_details', 'whise_property_details_nonce');

        echo '<div class="whise-property-details">';
        
        // Style inline pour la démo
        echo '<style>
            .whise-property-details { padding: 15px; }
            .whise-field-group { margin-bottom: 20px; background: #f9f9f9; padding: 15px; border: 1px solid #e5e5e5; }
            .whise-field-group h3 { margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #e5e5e5; }
            .whise-field-row { display: flex; margin-bottom: 8px; }
            .whise-field-label { width: 200px; font-weight: bold; }
            .whise-field-value { flex: 1; }
            .whise-boolean-true { color: green; }
            .whise-boolean-false { color: #999; }
        </style>';

        echo '<p class="description">' . __('Ces champs sont synchronisés automatiquement depuis Whise. Les modifications manuelles seront écrasées lors de la prochaine synchronisation.', 'whise-integration') . '</p>';

        foreach ($field_groups as $group => $data) {
            echo '<div class="whise-field-group">';
            echo '<h3>' . esc_html($data['title']) . '</h3>';
            
            foreach ($data['fields'] as $field) {
                $value = get_post_meta($post->ID, $field, true);
                $field_desc = isset($fields[$field]['desc']) ? $fields[$field]['desc'] : '';
                
                echo '<div class="whise-field-row">';
                echo '<div class="whise-field-label" title="' . esc_attr($field_desc) . '">' 
                     . esc_html(ucfirst(str_replace('_', ' ', $field))) . '</div>';
                echo '<div class="whise-field-value">';
                
                // Formatage spécial pour les booléens
                if (is_bool($value) || in_array($value, ['0', '1', '', null])) {
                    $is_true = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    echo '<span class="whise-boolean-' . ($is_true ? 'true' : 'false') . '">';
                    echo $is_true ? '✓' : '✗';
                    echo '</span>';
                } else {
                    echo esc_html($value ?: '—');
                }
                
                echo '</div></div>';
            }
            echo '</div>';
        }
        
        echo '</div>';
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
     * Force la réinitialisation complète des taxonomies
     */
    public function force_taxonomies_reset() {
        global $wpdb;
        
        // Supprime d'abord toutes les données des taxonomies
        $taxonomies = ['property_type', 'transaction_type', 'property_city', 'property_status'];
        
        // Supprime les termes et relations
        foreach($taxonomies as $taxonomy) {
            $term_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT t.term_id FROM {$wpdb->terms} AS t 
                INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id 
                WHERE tt.taxonomy = %s",
                $taxonomy
            ));
            
            if (!empty($term_ids)) {
                $wpdb->query("DELETE FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN (SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = '{$taxonomy}')");
                $wpdb->query("DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy = '{$taxonomy}'");
                $wpdb->query("DELETE FROM {$wpdb->terms} WHERE term_id IN (" . implode(',', $term_ids) . ")");
            }
            
            // Désenregistre la taxonomie
            unregister_taxonomy($taxonomy);
        }
        
        // Vide le cache
        clean_term_cache($term_ids, '', false);
        delete_option($taxonomy . '_children');
        
        // Réenregistre tout
        $this->register_taxonomies();
        $this->init_default_terms();
        
        // Force la mise à jour des règles de réécriture
        flush_rewrite_rules(true);
        
        return true;
    }
}
