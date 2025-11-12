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
        
        // Désactiver les colonnes automatiques des meta champs pour éviter les doublons
        add_filter('manage_edit-property_columns', [$this, 'remove_auto_columns'], 15);
        
        // Hook supplémentaire pour nettoyer les colonnes après tous les autres plugins
        add_filter('manage_property_posts_columns', [$this, 'final_column_cleanup'], 20);
        
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
        foreach(['property_type','transaction_type','property_city','property_status','purpose_status'] as $tax) {
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
        // transaction_type (avec tous les statuts : vente/location/vendu/sous_option)
        $transactions = ['vente', 'location', 'vendu', 'sous_option'];
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
        
        // purpose_status (statuts simples pour l'affichage et l'utilisation)
        $purpose_statuses = [
            'vente',
            'location', 
            'vendu',
            'sous_option'
        ];
        
        foreach ($purpose_statuses as $status) {
            if (!term_exists($status, 'purpose_status')) {
                wp_insert_term($status, 'purpose_status');
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
        // property_status (masqué de l'admin pour éviter le doublon)
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
            'public' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'show_in_rest' => true,
            'hierarchical' => true,
            'show_admin_column' => false, // Masquer la colonne admin
            'query_var' => true,
            'rewrite' => false, // Désactiver le rewrite pour éviter les conflits
            'capabilities' => [
                'manage_terms' => 'manage_categories',
                'edit_terms' => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts'
            ]
        ]);
        
        // purpose_status (masqué de l'admin - statuts maintenant dans transaction_type)
        register_taxonomy('purpose_status', 'property', [
            'label' => __('Statut de transaction', 'whise-integration'),
            'labels' => [
                'name' => __('Statuts de transaction', 'whise-integration'),
                'singular_name' => __('Statut de transaction', 'whise-integration'),
                'menu_name' => __('Statuts transaction', 'whise-integration'),
                'all_items' => __('Tous les statuts de transaction', 'whise-integration'),
                'edit_item' => __('Modifier le statut de transaction', 'whise-integration'),
                'view_item' => __('Voir le statut de transaction', 'whise-integration'),
                'update_item' => __('Mettre à jour le statut de transaction', 'whise-integration'),
                'add_new_item' => __('Ajouter un statut de transaction', 'whise-integration'),
                'new_item_name' => __('Nouveau statut de transaction', 'whise-integration'),
                'search_items' => __('Rechercher des statuts de transaction', 'whise-integration')
            ],
            'public' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'show_in_rest' => true,
            'hierarchical' => true,
            'show_admin_column' => false, // Masqué
            'query_var' => true,
            'rewrite' => false,
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
                        'transaction_type', 'status', 'state', 'state_id', 'purpose_status', 'transaction_status', 'energy_class', 'heating_type', 'kitchen_type',
                        'proximity_school', 'proximity_shops', 'proximity_transport', 'proximity_hospital',
                        'orientation', 'view', 'availability', 'available_date',
                        'link_3d_model', 'link_virtual_visit', 'link_video',
                        'representative_name', 'representative_email', 'representative_phone', 'representative_mobile', 'representative_picture', 'representative_function'],
            'number' => ['price', 'surface', 'total_area', 'land_area', 'commercial_area', 'built_area', 
                        'rooms', 'bedrooms', 'bathrooms', 'floors', 'construction_year', 'epc_value', 'cadastral_income', 'representative_id', 'purpose_status_id'],
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
            'state' => ['desc' => 'État du bâtiment', 'type' => 'string'],
            'state_id' => ['desc' => 'ID de l\'état du bâtiment', 'type' => 'string'],
            'purpose_status' => ['desc' => 'Statut de transaction Whise (texte)', 'type' => 'string'],
            'purpose_status_id' => ['desc' => 'ID du statut de transaction Whise', 'type' => 'number'],
            'transaction_status' => ['desc' => 'Statut simplifié (vente/location/vendu/sous_option)', 'type' => 'string'],
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
            
            // Liens médias
            'link_3d_model' => ['desc' => 'Lien modèle 3D (Matterport, YouTube, etc.)', 'type' => 'string'],
            'link_virtual_visit' => ['desc' => 'Lien visite virtuelle (Nodalview, etc.)', 'type' => 'string'],
            'link_video' => ['desc' => 'Lien vidéo', 'type' => 'string'],
            
            // Représentant
            'representative_id' => ['desc' => 'ID du représentant/agent', 'type' => 'number'],
            'representative_name' => ['desc' => 'Nom du représentant/agent', 'type' => 'string'],
            'representative_email' => ['desc' => 'Email du représentant/agent', 'type' => 'string'],
            'representative_phone' => ['desc' => 'Téléphone du représentant/agent', 'type' => 'string'],
            'representative_mobile' => ['desc' => 'Mobile du représentant/agent', 'type' => 'string'],
            'representative_picture' => ['desc' => 'Photo/Avatar du représentant/agent', 'type' => 'string'],
            'representative_function' => ['desc' => 'Fonction (rôle) du représentant/agent', 'type' => 'string'],
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
        // Approche simple : reconstruire complètement les colonnes
        $new_columns = array();
        
        // Colonnes de base WordPress qu'on garde
        if (isset($columns['cb'])) $new_columns['cb'] = $columns['cb'];
        if (isset($columns['title'])) $new_columns['title'] = $columns['title'];
        
        // Nos colonnes personnalisées
        $new_columns['reference'] = __('Référence', 'whise-integration');
        $new_columns['price'] = __('Prix', 'whise-integration');
        
        // Colonnes de taxonomies (WordPress les gère automatiquement si show_admin_column = true)
        $new_columns['taxonomy-property_type'] = __('Type', 'whise-integration');
        $new_columns['taxonomy-transaction_type'] = __('Statut', 'whise-integration'); // Renommé de "Transaction" vers "Statut"
        $new_columns['taxonomy-property_city'] = __('Ville', 'whise-integration');
        // $new_columns['taxonomy-property_status'] = __('Statut', 'whise-integration'); // Masqué pour éviter doublon
        // $new_columns['taxonomy-purpose_status'] = __('Statut', 'whise-integration'); // Masqué - statuts maintenant dans transaction_type
        
        // Colonne date à la fin
        if (isset($columns['date'])) $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }

    /**
     * Supprime les colonnes automatiques indésirables pour éviter les doublons
     */
    public function remove_auto_columns($columns) {
        // Supprimer toutes les colonnes de meta champs automatiques qui pourraient créer des doublons
        $meta_columns_to_remove = [];
        
        foreach ($columns as $key => $title) {
            // Supprimer les colonnes qui correspondent à nos meta champs
            if (in_array($key, ['reference', 'price', 'whise_id', 'price_formatted'])) {
                $meta_columns_to_remove[] = $key;
            }
            // Supprimer aussi les colonnes générées automatiquement par WordPress pour les custom fields
            if (strpos($key, 'meta-') === 0) {
                $meta_columns_to_remove[] = $key;
            }
        }
        
        foreach ($meta_columns_to_remove as $column) {
            unset($columns[$column]);
        }
        
        return $columns;
    }

    /**
     * Nettoyage final des colonnes pour garantir qu'il n'y a pas de doublons
     */
    public function final_column_cleanup($columns) {
        // Liste des colonnes que nous voulons garder dans l'ordre
        $desired_columns = [
            'cb' => $columns['cb'] ?? '',
            'title' => $columns['title'] ?? '',
            'reference' => __('Référence', 'whise-integration'),
            'price' => __('Prix', 'whise-integration'),
            'taxonomy-property_type' => __('Type', 'whise-integration'),
            'taxonomy-transaction_type' => __('Statut', 'whise-integration'), // Maintenant les statuts
            'taxonomy-property_city' => __('Ville', 'whise-integration'),
            // 'taxonomy-property_status' => __('Statut', 'whise-integration'), // Supprimé pour éviter doublon
            // 'taxonomy-purpose_status' => __('Statut', 'whise-integration'), // Masqué - statuts dans transaction_type
            'date' => $columns['date'] ?? ''
        ];
        
        // Supprimer les entrées vides
        return array_filter($desired_columns);
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
                'fields' => ['whise_id', 'reference', 'client_id', 'client_name', 'office_id', 'office_name']
            ],
            'type_categorie' => [
                'title' => 'Type et catégorie',
                'fields' => ['property_type', 'property_type_id', 'sub_category', 'sub_category_id', 'transaction_type', 'transaction_type_id']
            ],
            'etat_statut' => [
                'title' => 'État et statut',
                'fields' => ['state', 'state_id', 'status', 'status_id', 'purpose_status', 'purpose_status_id', 'transaction_status', 'construction_year', 'renovation_year']
            ],
            'prix' => [
                'title' => 'Prix et conditions',
                'fields' => ['price', 'price_formatted', 'price_type', 'price_supplement', 'charges', 'price_conditions', 'price_per_sqm']
            ],
            'surfaces' => [
                'title' => 'Surfaces',
                'fields' => ['surface', 'total_area', 'land_area', 'commercial_area', 'built_area', 'min_area', 'max_area', 'ground_area']
            ],
            'pieces' => [
                'title' => 'Pièces et espaces',
                'fields' => ['rooms', 'bedrooms', 'bathrooms', 'floors', 'number_of_floors', 'number_of_toilets', 'fronts']
            ],
            'localisation' => [
                'title' => 'Localisation',
                'fields' => ['address', 'number', 'box', 'zip', 'city', 'postal_code', 'country', 'latitude', 'longitude']
            ],
            'energie' => [
                'title' => 'Énergie et chauffage',
                'fields' => ['energy_class', 'epc_value', 'heating_type', 'heating_group', 'electricity', 'oil_tank', 'insulation']
            ],
            'cadastre' => [
                'title' => 'Données cadastrales',
                'fields' => ['cadastral_income']
            ],
            'equipements_base' => [
                'title' => 'Équipements de base',
                'fields' => ['kitchen_type', 'parking', 'garage', 'terrace', 'garden', 'swimming_pool', 'elevator', 'cellar', 'attic', 'furnished']
            ],
            'equipements_confort' => [
                'title' => 'Équipements de confort',
                'fields' => ['air_conditioning', 'double_glazing', 'alarm', 'concierge', 'telephone', 'telephone_central']
            ],
            'equipements_reglementaire' => [
                'title' => 'Équipements réglementaires',
                'fields' => ['toilets_mf', 'vta_regime', 'building_permit', 'subdivision_permit', 'ongoing_judgment']
            ],
            'proximite' => [
                'title' => 'Proximité',
                'fields' => ['proximity_school', 'proximity_shops', 'proximity_transport', 'proximity_hospital', 'proximity_city_center']
            ],
            'orientation_environnement' => [
                'title' => 'Orientation et environnement',
                'fields' => ['orientation', 'view', 'building_orientation', 'environment_type']
            ],
            'disponibilite' => [
                'title' => 'Disponibilité',
                'fields' => ['availability', 'is_immediately_available', 'available_date']
            ],
            'dimensions' => [
                'title' => 'Dimensions détaillées',
                'fields' => ['width_of_facade', 'depth_of_land', 'width_of_street_front', 'built_area_detail']
            ],
            'dates' => [
                'title' => 'Dates importantes',
                'fields' => ['create_date', 'update_date', 'put_online_date', 'price_change_date']
            ],
            'representant' => [
                'title' => 'Représentant',
                'fields' => ['representative_id', 'representative_name', 'representative_email', 'representative_phone', 'representative_mobile', 'representative_function']
            ],
            'descriptions_multilingues' => [
                'title' => 'Descriptions multilingues',
                'fields' => [
                    'short_description_fr', 'short_description_nl', 'short_description_en',
                    'sms_description_fr', 'sms_description_nl', 'sms_description_en',
                    'description_fr', 'description_nl', 'description_en'
                ]
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
                
                // Label avec meta key
                $field_label = ucfirst(str_replace('_', ' ', $field));
                $field_label_with_key = $field_label . ' <span style="color: #999; font-size: 0.85em; font-weight: normal;">(' . $field . ')</span>';
                
                echo '<div class="whise-field-row">';
                echo '<div class="whise-field-label" title="' . esc_attr($field_desc) . '">' 
                     . $field_label_with_key . '</div>';
                echo '<div class="whise-field-value">';
                
                // Formatage spécial pour les descriptions (respecter les sauts de ligne)
                if (strpos($field, 'description') !== false || strpos($field, 'sms') !== false) {
                    if (!empty($value)) {
                        echo '<div style="white-space: pre-wrap; max-height: 300px; overflow-y: auto; padding: 8px; background: #f5f5f5; border-radius: 4px;">';
                        echo esc_html($value);
                        echo '</div>';
                    } else {
                        echo '—';
                    }
                }
                // Formatage spécial pour les booléens
                elseif (is_bool($value) || in_array($field, ['parking', 'garage', 'terrace', 'garden', 'swimming_pool', 'elevator', 'cellar', 'attic', 'furnished', 'air_conditioning', 'double_glazing', 'alarm', 'concierge', 'telephone', 'telephone_central', 'electricity', 'oil_tank', 'insulation', 'toilets_mf', 'vta_regime', 'building_permit', 'subdivision_permit', 'ongoing_judgment', 'is_immediately_available'])) {
                    $is_true = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    echo '<span class="whise-boolean-' . ($is_true ? 'true' : 'false') . '">';
                    echo $is_true ? '✓ Oui' : '✗ Non';
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
        $taxonomies = ['property_type', 'transaction_type', 'property_city', 'property_status', 'purpose_status'];
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
        $taxonomies = ['property_type', 'transaction_type', 'property_city', 'property_status', 'purpose_status'];
        
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

    /**
     * Fonction utilitaire pour obtenir la galerie d'images WordPress d'une propriété
     */
    public static function get_property_gallery($post_id) {
        $gallery_ids = get_post_meta($post_id, '_whise_gallery_images', true);
        
        if (!empty($gallery_ids) && is_array($gallery_ids)) {
            $gallery = [];
            foreach ($gallery_ids as $attachment_id) {
                if (wp_attachment_is_image($attachment_id)) {
                    $order = get_post_meta($attachment_id, '_whise_image_order', true);
                    $order = $order !== '' ? (int)$order : 999;
                    
                    $gallery[] = [
                        'id' => $attachment_id,
                        'url' => wp_get_attachment_url($attachment_id),
                        'thumbnail' => wp_get_attachment_image_url($attachment_id, 'thumbnail'),
                        'medium' => wp_get_attachment_image_url($attachment_id, 'medium'),
                        'large' => wp_get_attachment_image_url($attachment_id, 'large'),
                        'title' => get_the_title($attachment_id),
                        'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
                        'order' => $order
                    ];
                }
            }
            
            // Trier la galerie par ordre avant de la retourner
            usort($gallery, function($a, $b) {
                return $a['order'] <=> $b['order'];
            });
            
            return $gallery;
        }
        
        return [];
    }

    /**
     * Fonction utilitaire pour obtenir une description dans une langue spécifique
     */
    public static function get_property_description($post_id, $field = 'shortDescription', $language = null) {
        // Utiliser la méthode du sync manager si disponible
        $sync_manager = new Whise_Sync_Manager();
        if (method_exists($sync_manager, 'get_description_by_language')) {
            return $sync_manager->get_description_by_language($post_id, $field, $language);
        }
        
        // Fallback vers les métadonnées
        $multilingual_data = get_post_meta($post_id, 'descriptions_multilingual', true);
        
        if (!$language) {
            $language = 'fr-BE'; // Langue par défaut
        }
        
        if (is_array($multilingual_data) && isset($multilingual_data[$field][$language])) {
            return $multilingual_data[$field][$language];
        }
        
        // Fallback vers les champs simples
        $fallback_field = $field === 'shortDescription' ? 'short_description' : 
                         ($field === 'sms' ? 'sms_description' : $field);
        return get_post_meta($post_id, $fallback_field, true);
    }
}

/**
 * Fonctions globales pour faciliter l'utilisation dans les templates
 */

/**
 * Obtient la galerie d'images d'une propriété
 */
function whise_get_property_gallery($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    return Whise_Property_CPT::get_property_gallery($post_id);
}

/**
 * Obtient une description d'une propriété dans une langue spécifique
 */
function whise_get_property_description($field = 'shortDescription', $language = null, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    return Whise_Property_CPT::get_property_description($post_id, $field, $language);
}

/**
 * Affiche la galerie d'images d'une propriété (simple)
 */
function whise_display_property_gallery($post_id = null, $size = 'medium') {
    $gallery = whise_get_property_gallery($post_id);
    
    if (!empty($gallery)) {
        echo '<div class="whise-simple-gallery">';
        foreach ($gallery as $image) {
            echo '<div class="whise-gallery-item">';
            echo '<img src="' . esc_url($image[$size]) . '" alt="' . esc_attr($image['alt']) . '" class="whise-gallery-image">';
            echo '</div>';
        }
        echo '</div>';
    }
}

/**
 * Obtient l'image mise en avant d'une propriété (featured image)
 */
function whise_get_property_featured_image($post_id = null, $size = 'medium') {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    if (has_post_thumbnail($post_id)) {
        return get_the_post_thumbnail_url($post_id, $size);
    }
    
    // Fallback vers la première image de la galerie
    $gallery = whise_get_property_gallery($post_id);
    if (!empty($gallery)) {
        return $gallery[0][$size] ?? '';
    }
    
    return '';
}

/**
 * Mappe un ID de statut Whise vers un statut simplifié
 * @param int $whise_purpose_status_id L'ID Whise du statut
 * @return string Le statut simplifié (vente/location/vendu/sous_option)
 */
function whise_map_status_to_simple($whise_purpose_status_id, $purpose_id = 1) {
    $mapping = [
        // Statuts de vente actifs
        1 => 'vente',    // à vendre
        19 => 'vente',   // prospection
        20 => 'vente',   // préparation
        24 => 'vente',   // estimation v.
        
        // Statuts sous option/réservé
        5 => 'sous_option',   // option v.
        12 => 'sous_option',  // option prop. v.
        21 => 'sous_option',  // réservé
        22 => 'sous_option',  // compromis
        14 => 'sous_option',  // vendu avec cond. suspensive
        
        // Statuts vendus/finalisés
        3 => 'vendu',    // vendu
        8 => 'vendu',    // retiré v.
        10 => 'vendu',   // suspendu v.
    ];
    
    $base_status = $mapping[$whise_purpose_status_id] ?? null;
    
    // Si c'est une location (purpose_id = 2), adapter les statuts
    if ($purpose_id == 2) {
        if ($base_status === 'vente') {
            return 'location';
        } elseif ($base_status === 'vendu') {
            return 'location'; // ou 'loué' si vous préférez
        }
    }
    
    return $base_status ?? ($purpose_id == 2 ? 'location' : 'vente');
}

/**
 * Met à jour le statut simplifié basé sur l'ID Whise
 * @param int $post_id ID du post
 * @param int $whise_purpose_status_id ID du statut Whise
 */
function whise_update_simple_transaction_status($post_id, $whise_purpose_status_id, $purpose_id = 1) {
    $simple_status = whise_map_status_to_simple($whise_purpose_status_id, $purpose_id);
    update_post_meta($post_id, 'transaction_status', $simple_status);
    
    // Assigner le terme dans la taxonomie transaction_type (qui contient maintenant tous les statuts)
    wp_set_object_terms($post_id, $simple_status, 'transaction_type');
    
    return $simple_status;
}

/**
 * Récupère le statut de transaction simplifié d'une propriété
 * @param int $post_id ID du post (optionnel, utilise get_the_ID() par défaut)
 * @return string Le statut simplifié
 */
function whise_get_transaction_status($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    return get_post_meta($post_id, 'transaction_status', true) ?: 'vente';
}

/**
 * Fonction de debug pour tester les mappings de statuts
 */
function whise_debug_status_mappings() {
    $test_cases = [
        ['purpose_status_id' => 1, 'purpose_id' => 1, 'expected' => 'vente'], // à vendre
        ['purpose_status_id' => 3, 'purpose_id' => 1, 'expected' => 'vendu'], // vendu
        ['purpose_status_id' => 5, 'purpose_id' => 1, 'expected' => 'sous_option'], // option v.
        ['purpose_status_id' => 1, 'purpose_id' => 2, 'expected' => 'location'], // à louer
        ['purpose_status_id' => 3, 'purpose_id' => 2, 'expected' => 'location'], // loué
        ['purpose_status_id' => 22, 'purpose_id' => 1, 'expected' => 'sous_option'], // compromis
    ];
    
    echo "<h3>Debug des mappings de statuts Whise</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Purpose Status ID</th><th>Purpose ID</th><th>Résultat</th><th>Attendu</th><th>Status</th></tr>";
    
    foreach ($test_cases as $test) {
        $result = whise_map_status_to_simple($test['purpose_status_id'], $test['purpose_id']);
        $status = ($result === $test['expected']) ? '✅ OK' : '❌ ERREUR';
        echo "<tr>";
        echo "<td>{$test['purpose_status_id']}</td>";
        echo "<td>{$test['purpose_id']}</td>";
        echo "<td><strong>{$result}</strong></td>";
        echo "<td>{$test['expected']}</td>";
        echo "<td>{$status}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

/**
 * Fonction de debug pour vérifier les termes assignés à un bien
 */
function whise_debug_property_terms($post_id) {
    if (!$post_id) {
        echo "<p>Aucun post_id fourni</p>";
        return;
    }
    
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'property') {
        echo "<p>Post non trouvé ou pas un bien immobilier</p>";
        return;
    }
    
    echo "<h3>Debug des termes pour le bien #{$post_id} : " . esc_html($post->post_title) . "</h3>";
    
    $taxonomies = ['property_type', 'transaction_type', 'property_city', 'property_status', 'purpose_status'];
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Taxonomie</th><th>Termes assignés</th><th>Méta correspondante</th></tr>";
    
    foreach ($taxonomies as $taxonomy) {
        $terms = wp_get_object_terms($post_id, $taxonomy, ['fields' => 'names']);
        $meta_value = '';
        
        // Récupérer la méta correspondante
        switch($taxonomy) {
            case 'property_type':
                $meta_value = get_post_meta($post_id, 'property_type', true);
                break;
            case 'transaction_type':
                $meta_value = get_post_meta($post_id, 'transaction_type', true) . ' | transaction_status: ' . get_post_meta($post_id, 'transaction_status', true);
                break;
            case 'property_city':
                $meta_value = get_post_meta($post_id, 'city', true);
                break;
            case 'property_status':
                $meta_value = get_post_meta($post_id, 'status', true);
                break;
            case 'purpose_status':
                $meta_value = get_post_meta($post_id, 'purpose_status', true) . ' (ID: ' . get_post_meta($post_id, 'purpose_status_id', true) . ')';
                break;
        }
        
        echo "<tr>";
        echo "<td><strong>{$taxonomy}</strong></td>";
        echo "<td>" . (empty($terms) ? '<em>Aucun</em>' : implode(', ', $terms)) . "</td>";
        echo "<td><small>{$meta_value}</small></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Autres métadonnées utiles
    echo "<h4>Métadonnées Whise</h4>";
    echo "<p><strong>Whise ID:</strong> " . get_post_meta($post_id, 'whise_id', true) . "</p>";
    echo "<p><strong>Rooms:</strong> " . get_post_meta($post_id, 'rooms', true) . "</p>";
    echo "<p><strong>Sub categories:</strong> " . print_r(get_post_meta($post_id, 'sub_categories', true), true) . "</p>";
}

/**
 * Fonction pour tester manuellement l'assignation de Studio
 */
function whise_force_studio_assignment($post_id) {
    if (!$post_id) {
        echo "<p>Aucun post_id fourni</p>";
        return;
    }
    
    // Nettoyer tous les termes actuels
    wp_set_object_terms($post_id, [], 'property_type', false);
    
    // Assigner seulement Studio
    $result = wp_set_object_terms($post_id, ['Studio'], 'property_type', false);
    
    if (is_wp_error($result)) {
        echo "<p style='color: red;'>Erreur lors de l'assignation : " . $result->get_error_message() . "</p>";
    } else {
        echo "<p style='color: green;'>Studio assigné avec succès au bien #{$post_id}</p>";
        
        // Vérifier l'assignation
        $terms = wp_get_object_terms($post_id, 'property_type', ['fields' => 'names']);
        echo "<p><strong>Termes assignés après test :</strong> " . implode(', ', $terms) . "</p>";
    }
}
