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
                'fields' => ['whise_id', 'reference', 'client_id', 'client_name', 'office_id', 'office_name'],
                'icon' => 'dashicons-id'
            ],
            'type_categorie' => [
                'title' => 'Type et catégorie',
                'fields' => ['property_type', 'property_type_id', 'sub_category', 'sub_category_id', 'transaction_type', 'transaction_type_id'],
                'icon' => 'dashicons-category'
            ],
            'etat_statut' => [
                'title' => 'État et statut',
                'fields' => ['state', 'state_id', 'status', 'status_id', 'purpose_status', 'purpose_status_id', 'transaction_status', 'construction_year', 'renovation_year'],
                'icon' => 'dashicons-info'
            ],
            'prix' => [
                'title' => 'Prix et conditions',
                'fields' => ['price', 'price_formatted', 'price_type', 'price_supplement', 'charges', 'price_conditions', 'price_per_sqm'],
                'icon' => 'dashicons-money-alt'
            ],
            'surfaces' => [
                'title' => 'Surfaces',
                'fields' => ['surface', 'total_area', 'land_area', 'commercial_area', 'built_area', 'min_area', 'max_area', 'ground_area'],
                'icon' => 'dashicons-editor-expand'
            ],
            'pieces' => [
                'title' => 'Pièces et espaces',
                'fields' => ['rooms', 'bedrooms', 'bathrooms', 'floors', 'number_of_floors', 'number_of_toilets', 'fronts'],
                'icon' => 'dashicons-layout'
            ],
            'localisation' => [
                'title' => 'Localisation',
                'fields' => ['address', 'number', 'box', 'zip', 'city', 'postal_code', 'country', 'latitude', 'longitude'],
                'icon' => 'dashicons-location'
            ],
            'energie' => [
                'title' => 'Énergie et chauffage',
                'fields' => ['energy_class', 'epc_value', 'heating_type', 'heating_group', 'electricity', 'oil_tank', 'insulation'],
                'icon' => 'dashicons-energy'
            ],
            'cadastre' => [
                'title' => 'Données cadastrales',
                'fields' => ['cadastral_income', 'cadastral_data'],
                'icon' => 'dashicons-admin-site-alt3'
            ],
            'equipements_base' => [
                'title' => 'Équipements de base',
                'fields' => ['kitchen_type', 'parking', 'garage', 'terrace', 'garden', 'swimming_pool', 'elevator', 'cellar', 'attic', 'furnished'],
                'icon' => 'dashicons-admin-tools'
            ],
            'equipements_confort' => [
                'title' => 'Équipements de confort',
                'fields' => ['air_conditioning', 'double_glazing', 'alarm', 'concierge', 'telephone', 'telephone_central'],
                'icon' => 'dashicons-star-filled'
            ],
            'equipements_reglementaire' => [
                'title' => 'Équipements réglementaires',
                'fields' => ['toilets_mf', 'vta_regime', 'building_permit', 'subdivision_permit', 'ongoing_judgment'],
                'icon' => 'dashicons-clipboard'
            ],
            'proximite' => [
                'title' => 'Proximité',
                'fields' => ['proximity_school', 'proximity_shops', 'proximity_transport', 'proximity_hospital', 'proximity_city_center'],
                'icon' => 'dashicons-location-alt'
            ],
            'orientation_environnement' => [
                'title' => 'Orientation et environnement',
                'fields' => ['orientation', 'view', 'building_orientation', 'environment_type'],
                'icon' => 'dashicons-visibility'
            ],
            'disponibilite' => [
                'title' => 'Disponibilité',
                'fields' => ['availability', 'is_immediately_available', 'available_date'],
                'icon' => 'dashicons-calendar-alt'
            ],
            'bureaux' => [
                'title' => 'Bureaux (spécifique)',
                'fields' => ['office_1', 'office_2', 'office_3'],
                'icon' => 'dashicons-building'
            ],
            'materiaux' => [
                'title' => 'Matériaux et finitions',
                'fields' => ['floor_material', 'ground_destination'],
                'icon' => 'dashicons-hammer'
            ],
            'dimensions' => [
                'title' => 'Dimensions détaillées',
                'fields' => ['width_of_facade', 'depth_of_land', 'width_of_street_front', 'built_area_detail'],
                'icon' => 'dashicons-editor-justify'
            ],
            'dates' => [
                'title' => 'Dates importantes',
                'fields' => ['create_date', 'update_date', 'put_online_date', 'price_change_date'],
                'icon' => 'dashicons-clock'
            ],
            'descriptions' => [
                'title' => 'Descriptions',
                'fields' => ['description', 'short_description', 'sms_description'],
                'icon' => 'dashicons-editor-alignleft'
            ],
            'media_liens' => [
                'title' => 'Médias et liens',
                'fields' => ['link_3d_model', 'link_virtual_visit', 'link_video'],
                'icon' => 'dashicons-video-alt3'
            ],
            'images' => [
                'title' => 'Galerie d\'images',
                'fields' => ['images'],
                'icon' => 'dashicons-format-gallery'
            ],
            'representant' => [
                'title' => 'Représentant',
                'fields' => ['representative_id', 'representative_name', 'representative_email', 'representative_phone', 'representative_mobile', 'representative_function', 'representative_picture'],
                'icon' => 'dashicons-businessman'
            ],
            'details_avances' => [
                'title' => 'Détails avancés',
                'fields' => ['net_area', 'garden_area', 'tenant_charges', 'professional_liberal_possibility', 'fitness_room_area', 'currency', 'sub_category_id', 'property_type_language', 'transaction_type_language', 'status_language'],
                'icon' => 'dashicons-admin-generic'
            ],
            'multilingual' => [
                'title' => 'Descriptions multilingues',
                'fields' => ['descriptions_multilingual'],
                'icon' => 'dashicons-translation'
            ],
            'technique' => [
                'title' => 'Données techniques (debug)',
                'fields' => ['details'],
                'icon' => 'dashicons-code-standards'
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
                
                // Créer un label lisible avec le nom du champ en parenthèses
                $field_label = ucfirst(str_replace('_', ' ', $field));
                $field_label_with_key = $field_label . ' <span style="color: #999; font-size: 0.85em; font-weight: normal;">(' . $field . ')</span>';
                
                echo '<div class="whise-field">';
                echo '<label>' . $field_label_with_key . ':</label>';
                
                if ($field === 'images') {
                    $this->display_property_gallery($property->ID);
                } elseif ($field === 'representative_picture' && !empty($value)) {
                    echo '<div class="whise-representative-picture">';
                    echo '<img src="' . esc_url($value) . '" alt="Photo représentant" style="max-width: 150px; border-radius: 4px;">';
                    echo '</div>';
                } elseif ($field === 'descriptions_multilingual' && is_array($value)) {
                    echo '<div class="whise-multilingual-descriptions">';
                    foreach ($value as $desc_type => $languages) {
                        if (!empty($languages)) {
                            echo '<h4>' . esc_html(ucfirst(str_replace('_', ' ', $desc_type))) . '</h4>';
                            foreach ($languages as $lang => $content) {
                                if (!empty($content)) {
                                    echo '<div class="whise-description-lang" data-lang="' . esc_attr($lang) . '">';
                                    echo '<span class="whise-lang-label">' . esc_html($lang) . ':</span>';
                                    echo '<div class="whise-description-content">' . wp_kses_post($content) . '</div>';
                                    echo '</div>';
                                }
                            }
                        }
                    }
                    echo '</div>';
                } elseif ($field === 'details' && is_array($value)) {
                    // Affichage spécial pour les détails Whise (format tableau)
                    echo '<div class="whise-details-table">';
                    echo '<table style="width: 100%; border-collapse: collapse; font-size: 0.9em;">';
                    echo '<thead><tr style="background: #f0f0f0;">';
                    echo '<th style="padding: 8px; text-align: left; border: 1px solid #ddd;">ID</th>';
                    echo '<th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Label</th>';
                    echo '<th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Valeur</th>';
                    echo '<th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Groupe</th>';
                    echo '</tr></thead><tbody>';
                    
                    foreach ($value as $detail) {
                        echo '<tr>';
                        echo '<td style="padding: 6px; border: 1px solid #ddd;">' . esc_html($detail['id'] ?? '') . '</td>';
                        echo '<td style="padding: 6px; border: 1px solid #ddd;">' . esc_html($detail['label'] ?? '') . '</td>';
                        echo '<td style="padding: 6px; border: 1px solid #ddd; font-weight: bold;">' . esc_html($detail['value'] ?? '') . '</td>';
                        echo '<td style="padding: 6px; border: 1px solid #ddd; color: #666;">' . esc_html($detail['group'] ?? '') . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                    echo '<p style="margin-top: 10px; font-size: 0.85em; color: #666;">Total : ' . count($value) . ' détails</p>';
                    echo '</div>';
                } elseif (is_array($value)) {
                    // Affichage des autres arrays (comme cadastral_data)
                    echo '<div class="whise-array-value">';
                    echo '<pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; font-size: 0.9em; overflow-x: auto;">';
                    echo esc_html(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    echo '</pre>';
                    echo '</div>';
                } elseif (is_bool($value) || in_array($field, ['parking', 'garage', 'terrace', 'garden', 'swimming_pool', 'elevator', 'cellar', 'attic', 'furnished', 'air_conditioning', 'double_glazing', 'alarm', 'concierge', 'telephone', 'telephone_central', 'electricity', 'oil_tank', 'insulation', 'toilets_mf', 'vta_regime', 'building_permit', 'subdivision_permit', 'ongoing_judgment', 'is_immediately_available'])) {
                    $is_true = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    echo '<span class="whise-boolean-' . ($is_true ? 'true' : 'false') . '">';
                    echo $is_true ? '✓ Oui' : '✗ Non';
                    echo '</span>';
                } elseif (in_array($field, ['link_3d_model', 'link_virtual_visit', 'link_video']) && !empty($value)) {
                    echo '<div class="whise-link">';
                    echo '<a href="' . esc_url($value) . '" target="_blank" rel="noopener noreferrer">';
                    echo '<span class="dashicons dashicons-external"></span> ' . esc_html($value);
                    echo '</a>';
                    echo '</div>';
                } elseif (in_array($field, ['representative_email']) && !empty($value)) {
                    echo '<div class="whise-email">';
                    echo '<a href="mailto:' . esc_attr($value) . '">' . esc_html($value) . '</a>';
                    echo '</div>';
                } elseif (in_array($field, ['representative_phone', 'representative_mobile']) && !empty($value)) {
                    echo '<div class="whise-phone">';
                    echo '<a href="tel:' . esc_attr($value) . '">' . esc_html($value) . '</a>';
                    echo '</div>';
                } elseif (in_array($field, ['description', 'short_description', 'sms_description']) && !empty($value)) {
                    echo '<div class="whise-description-text" style="background: #f9f9f9; padding: 15px; border-left: 3px solid #2271b1; border-radius: 4px;">';
                    echo wp_kses_post($value);
                    echo '</div>';
                } else {
                    echo '<div class="whise-value">' . (empty($value) && $value !== '0' && $value !== 0 ? '<span style="color: #ccc;">—</span>' : esc_html($value)) . '</div>';
                }
                
                echo '</div>';
            }
            
            echo '</div></div>';
        }
        
        echo '</div>';
    }

    /**
     * Affiche la galerie d'images d'une propriété avec attachments WordPress
     */
    private function display_property_gallery($post_id) {
        // Récupérer les IDs des attachments de la galerie
        $gallery_ids = get_post_meta($post_id, '_whise_gallery_images', true);
        
        if (!empty($gallery_ids) && is_array($gallery_ids)) {
            echo '<div class="whise-gallery-container">';
            echo '<div class="whise-gallery-grid">';
            
            foreach ($gallery_ids as $attachment_id) {
                if (wp_attachment_is_image($attachment_id)) {
                    $image_data = wp_get_attachment_metadata($attachment_id);
                    $image_url = wp_get_attachment_url($attachment_id);
                    $thumbnail_url = wp_get_attachment_image_url($attachment_id, 'medium');
                    $title = get_the_title($attachment_id);
                    $whise_order = get_post_meta($attachment_id, '_whise_image_order', true);
                    
                    echo '<div class="whise-gallery-item" data-order="' . esc_attr($whise_order) . '">';
                    echo '<a href="' . esc_url($image_url) . '" class="whise-gallery-link" data-lightbox="property-gallery" data-title="' . esc_attr($title) . '">';
                    echo '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr($title) . '" class="whise-gallery-thumbnail">';
                    echo '<div class="whise-gallery-overlay">';
                    echo '<span class="whise-gallery-icon">🔍</span>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
            }
            
            echo '</div>';
            echo '<p class="whise-gallery-count">' . sprintf(__('%d images disponibles', 'whise-integration'), count($gallery_ids)) . '</p>';
            echo '</div>';
            
            // Ajouter le script lightbox si pas déjà inclus
            $this->enqueue_lightbox_scripts();
        } else {
            // Fallback vers l'ancien système d'URLs
            $images = get_post_meta($post_id, 'images', true);
            if (!empty($images) && is_array($images)) {
                echo '<div class="whise-images-grid whise-fallback-gallery">';
                foreach ($images as $image) {
                    if (isset($image['medium'])) {
                        echo '<div class="whise-image">';
                        echo '<img src="' . esc_url($image['medium']) . '" alt="" class="whise-fallback-image">';
                        echo '</div>';
                    }
                }
                echo '</div>';
                echo '<p class="whise-gallery-notice"><em>' . __('Images depuis URLs externes (ancienne méthode)', 'whise-integration') . '</em></p>';
            } else {
                echo '<p class="whise-no-images">' . __('Aucune image disponible', 'whise-integration') . '</p>';
            }
        }
    }

    /**
     * Inclut les scripts nécessaires pour la lightbox
     */
    private function enqueue_lightbox_scripts() {
        static $scripts_enqueued = false;
        
        if (!$scripts_enqueued) {
            // CSS pour la galerie
            echo '<style>
                .whise-gallery-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                    gap: 15px;
                    margin: 15px 0;
                }
                .whise-gallery-item {
                    position: relative;
                    border-radius: 8px;
                    overflow: hidden;
                    transition: transform 0.3s ease;
                }
                .whise-gallery-item:hover {
                    transform: scale(1.05);
                }
                .whise-gallery-link {
                    display: block;
                    position: relative;
                }
                .whise-gallery-thumbnail {
                    width: 100%;
                    height: 200px;
                    object-fit: cover;
                    border-radius: 8px;
                }
                .whise-gallery-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.7);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                .whise-gallery-item:hover .whise-gallery-overlay {
                    opacity: 1;
                }
                .whise-gallery-icon {
                    font-size: 24px;
                    color: white;
                }
                .whise-gallery-count {
                    text-align: center;
                    color: #666;
                    font-style: italic;
                    margin-top: 10px;
                }
                .whise-multilingual-descriptions {
                    border-left: 3px solid #0073aa;
                    padding-left: 15px;
                    margin: 10px 0;
                }
                .whise-description-lang {
                    margin-bottom: 15px;
                    padding: 10px;
                    background: #f9f9f9;
                    border-radius: 5px;
                }
                .whise-lang-label {
                    font-weight: bold;
                    color: #0073aa;
                    text-transform: uppercase;
                    font-size: 12px;
                    display: block;
                    margin-bottom: 5px;
                }
                .whise-description-content {
                    line-height: 1.6;
                }
                .whise-fallback-gallery .whise-image {
                    border: 2px dashed #ccc;
                    padding: 10px;
                }
                .whise-gallery-notice {
                    color: #d63384;
                    font-size: 12px;
                    margin-top: 10px;
                }
            </style>';
            
            // JavaScript simple pour lightbox (peut être remplacé par une bibliothèque)
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    const galleryLinks = document.querySelectorAll(".whise-gallery-link");
                    galleryLinks.forEach(link => {
                        link.addEventListener("click", function(e) {
                            e.preventDefault();
                            // Simple lightbox - peut être amélioré avec une vraie bibliothèque
                            const imageUrl = this.href;
                            const title = this.dataset.title;
                            window.open(imageUrl, "_blank");
                        });
                    });
                });
            </script>';
            
            $scripts_enqueued = true;
        }
    }
}
