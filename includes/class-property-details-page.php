<?php
if (!defined('ABSPATH')) exit;

class Whise_Property_Details_Page {
    private $parent_slug = 'edit.php?post_type=property';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_details_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_details_page() {
        add_submenu_page(
            $this->parent_slug,
            __('Détails complets', 'whise-integration'),
            __('Détails complets', 'whise-integration'),
            'edit_posts',
            'property-details',
            [$this, 'render_details_page']
        );
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'property_page_property-details') return;

        wp_enqueue_style(
            'whise-property-details',
            plugins_url('/assets/css/property-details.css', WHISE_PLUGIN_FILE),
            [],
            WHISE_VERSION
        );
    }

    public function render_details_page() {
        // Vérification de sécurité
        if (!current_user_can('edit_posts')) {
            wp_die(__('Vous n\'avez pas les permissions suffisantes pour accéder à cette page.'));
        }

        // Récupération du bien si ID fourni
        $property_id = isset($_GET['property_id']) ? intval($_GET['property_id']) : 0;
        $property = $property_id ? get_post($property_id) : null;

        echo '<div class="wrap whise-property-details-page">';
        echo '<h1>' . __('Détails complets du bien', 'whise-integration') . '</h1>';

        if (!$property_id || !$property) {
            // Liste des biens
            $this->render_properties_list();
        } else {
            // Détails d'un bien
            $this->render_property_details($property);
        }

        echo '</div>';
    }

    private function render_properties_list() {
        $properties = get_posts([
            'post_type' => 'property',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);

        if (empty($properties)) {
            echo '<p>' . __('Aucun bien trouvé.', 'whise-integration') . '</p>';
            return;
        }

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('Référence', 'whise-integration') . '</th>';
        echo '<th>' . __('Titre', 'whise-integration') . '</th>';
        echo '<th>' . __('Type', 'whise-integration') . '</th>';
        echo '<th>' . __('Ville', 'whise-integration') . '</th>';
        echo '<th>' . __('Prix', 'whise-integration') . '</th>';
        echo '<th>' . __('Actions', 'whise-integration') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($properties as $property) {
            $ref = get_post_meta($property->ID, 'reference', true);
            $type = get_post_meta($property->ID, 'property_type', true);
            $city = get_post_meta($property->ID, 'city', true);
            $price = get_post_meta($property->ID, 'price_formatted', true);
            
            echo '<tr>';
            echo '<td>' . esc_html($ref) . '</td>';
            echo '<td>' . esc_html($property->post_title) . '</td>';
            echo '<td>' . esc_html($type) . '</td>';
            echo '<td>' . esc_html($city) . '</td>';
            echo '<td>' . esc_html($price) . '</td>';
            echo '<td>';
            echo '<a href="' . add_query_arg('property_id', $property->ID) . '" class="button">';
            echo __('Voir les détails', 'whise-integration');
            echo '</a>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    private function render_property_details($property) {
        // Lien de retour
        echo '<p><a href="' . remove_query_arg('property_id') . '" class="button">';
        echo '&larr; ' . __('Retour à la liste', 'whise-integration');
        echo '</a></p>';

        // En-tête
        $ref = get_post_meta($property->ID, 'reference', true);
        echo '<h2>' . sprintf(
            __('Détails du bien %s (Réf: %s)', 'whise-integration'),
            esc_html($property->post_title),
            esc_html($ref)
        ) . '</h2>';

        // Organisation des champs par sections
        $sections = [
            'identification' => [
                'title' => 'Identification',
                'fields' => ['whise_id', 'reference'],
                'icon' => 'dashicons-id'
            ],
            'prix' => [
                'title' => 'Prix et conditions',
                'fields' => ['price', 'price_formatted', 'price_type', 'price_supplement', 'charges', 'price_conditions'],
                'icon' => 'dashicons-money-alt'
            ],
            'surfaces' => [
                'title' => 'Surfaces',
                'fields' => ['surface', 'total_area', 'land_area', 'commercial_area', 'built_area'],
                'icon' => 'dashicons-editor-expand'
            ],
            'pieces' => [
                'title' => 'Pièces',
                'fields' => ['rooms', 'bedrooms', 'bathrooms', 'floors'],
                'icon' => 'dashicons-layout'
            ],
            'localisation' => [
                'title' => 'Localisation',
                'fields' => ['address', 'city', 'postal_code', 'country', 'latitude', 'longitude'],
                'icon' => 'dashicons-location'
            ],
            'energie' => [
                'title' => 'Énergie',
                'fields' => ['energy_class', 'epc_value', 'heating_type'],
                'icon' => 'dashicons-energy'
            ],
            'equipements' => [
                'title' => 'Équipements',
                'fields' => [
                    'kitchen_type', 'parking', 'garage', 'terrace', 'garden', 'swimming_pool',
                    'elevator', 'cellar', 'attic'
                ],
                'icon' => 'dashicons-admin-tools'
            ],
            'proximite' => [
                'title' => 'Proximité',
                'fields' => ['proximity_school', 'proximity_shops', 'proximity_transport', 'proximity_hospital'],
                'icon' => 'dashicons-location-alt'
            ],
            'disponibilite' => [
                'title' => 'Disponibilité',
                'fields' => ['availability', 'is_immediately_available', 'available_date'],
                'icon' => 'dashicons-calendar-alt'
            ],
            'descriptions' => [
                'title' => 'Descriptions',
                'fields' => ['description', 'description_short'],
                'icon' => 'dashicons-editor-alignleft'
            ],
            'media' => [
                'title' => 'Images',
                'fields' => ['images'],
                'icon' => 'dashicons-format-gallery'
            ]
        ];

        echo '<div class="whise-property-sections">';
        
        foreach ($sections as $key => $section) {
            echo '<div class="whise-section" id="section-' . esc_attr($key) . '">';
            echo '<h3><span class="dashicons ' . esc_attr($section['icon']) . '"></span> ' . 
                 esc_html($section['title']) . '</h3>';
            echo '<div class="whise-section-content">';
            
            foreach ($section['fields'] as $field) {
                $value = get_post_meta($property->ID, $field, true);
                echo '<div class="whise-field">';
                echo '<label>' . esc_html(ucfirst(str_replace('_', ' ', $field))) . ':</label>';
                
                if ($field === 'images' && is_array($value)) {
                    echo '<div class="whise-images-grid">';
                    foreach ($value as $image) {
                        if (isset($image['medium'])) {
                            echo '<div class="whise-image">';
                            echo '<img src="' . esc_url($image['medium']) . '" alt="">';
                            echo '</div>';
                        }
                    }
                    echo '</div>';
                } elseif (is_bool($value) || in_array($value, ['0', '1', '', null])) {
                    $is_true = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    echo '<span class="whise-boolean-' . ($is_true ? 'true' : 'false') . '">';
                    echo $is_true ? '✓' : '✗';
                    echo '</span>';
                } else {
                    echo '<div class="whise-value">' . (empty($value) ? '—' : esc_html($value)) . '</div>';
                }
                
                echo '</div>';
            }
            
            echo '</div></div>';
        }
        
        echo '</div>';
    }
}
