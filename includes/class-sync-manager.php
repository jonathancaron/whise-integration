<?php
if (!defined('ABSPATH')) exit;

class Whise_Sync_Manager {
    /**
     * Traite et convertit une valeur selon son type défini
     */
    private function convert_value($value, $type) {
        switch ($type) {
            case 'number':
                if ($value === null || $value === '') {
                    return 0;
                }
                return is_numeric($value) ? (int)$value : 0;
            case 'float':
                if ($value === null || $value === '') {
                    return 0.0;
                }
                return is_numeric($value) ? (float)$value : 0.0;
            case 'boolean':
                return (bool)$value;
            case 'array':
                if ($value === null || $value === '') {
                    return [];
                }
                return is_array($value) ? $value : [];
            case 'string':
            default:
                if ($value === null) {
                    return '';
                }
                return (string)$value;
        }
    }

    private function get_field_type($field_name) {
        $field_types = [
            // Identifiants
            'whise_id' => 'string',
            'reference' => 'string',
            
            // Prix et conditions
            'price' => 'number',
            'price_formatted' => 'string',
            'price_type' => 'string',
            'price_supplement' => 'string',
            'charges' => 'number',
            'price_conditions' => 'string',
            'price_per_sqm' => 'number',
            
            // Surfaces
            'surface' => 'number',
            'total_area' => 'number',
            'land_area' => 'number',
            'commercial_area' => 'number',
            'built_area' => 'number',
            'min_area' => 'number',
            'max_area' => 'number',
            'ground_area' => 'number',
            
            // Pièces
            'rooms' => 'number',
            'bathrooms' => 'number',
            'floors' => 'number',
            'bedrooms' => 'number',
            
            // Type et statut
            'property_type' => 'string',
            'property_type_id' => 'string',
            'property_type_language' => 'string',
            'transaction_type' => 'string',
            'transaction_type_id' => 'string',
            'transaction_type_language' => 'string',
            'status' => 'string',
            'status_id' => 'string',
            'status_language' => 'string',
            'sub_categories' => 'array',
            'construction_year' => 'number',
            'renovation_year' => 'number',
            
            // Localisation
            'address' => 'string',
            'city' => 'string',
            'postal_code' => 'string',
            'country' => 'string',
            'latitude' => 'float',
            'longitude' => 'float',
            'box' => 'string',
            'number' => 'string',
            'zip' => 'string',
            
            // Énergie
            'energy_class' => 'string',
            'epc_value' => 'number',
            'heating_type' => 'string',
            'heating_group' => 'string',
            
            // Données cadastrales
            'cadastral_income' => 'number',
            'cadastral_data' => 'array',
            
            // Équipements
            'kitchen_type' => 'string',
            'parking' => 'boolean',
            'garage' => 'boolean',
            'terrace' => 'boolean',
            'garden' => 'boolean',
            'swimming_pool' => 'boolean',
            'elevator' => 'boolean',
            'cellar' => 'boolean',
            'attic' => 'boolean',
            'furnished' => 'boolean',
            'air_conditioning' => 'boolean',
            'double_glazing' => 'boolean',
            'alarm' => 'boolean',
            'concierge' => 'boolean',
            'telephone' => 'boolean',
            'telephone_central' => 'boolean',
            'electricity' => 'boolean',
            'oil_tank' => 'boolean',
            'insulation' => 'boolean',
            'toilets_mf' => 'boolean',
            'vta_regime' => 'boolean',
            'building_permit' => 'boolean',
            'subdivision_permit' => 'boolean',
            'ongoing_judgment' => 'boolean',
            
            // Proximité
            'proximity_school' => 'string',
            'proximity_shops' => 'string',
            'proximity_transport' => 'string',
            'proximity_hospital' => 'string',
            'proximity_city_center' => 'string',
            
            // Disponibilité
            'availability' => 'string',
            'is_immediately_available' => 'boolean',
            
            // Orientation et vues
            'orientation' => 'string',
            'view' => 'string',
            'building_orientation' => 'string',
            'environment_type' => 'string',
            
            // Bureaux spécifiques
            'office_1' => 'number',
            'office_2' => 'number',
            'office_3' => 'number',
            
            // Matériaux et finitions
            'floor_material' => 'string',
            'ground_destination' => 'string',
            
            // Dates
            'create_date' => 'string',
            'update_date' => 'string',
            'put_online_date' => 'string',
            'price_change_date' => 'string',
            
            // Informations client
            'client_id' => 'number',
            'office_id' => 'number',
            'client_name' => 'string',
            'office_name' => 'string',
            
            // Descriptions
            'short_description' => 'string',
            'sms_description' => 'string',
            
            // Images
            'images' => 'array',
            
            // Détails complets
            'details' => 'array',

            // Liens médias
            'link_3d_model' => 'string',
            'link_virtual_visit' => 'string',
            'link_video' => 'string',

            // Représentant
            'representative_id' => 'number',
            'representative_name' => 'string',
            'representative_email' => 'string',
            'representative_phone' => 'string',
            'representative_mobile' => 'string',
            'representative_picture' => 'string',
        ];
        
        return $field_types[$field_name] ?? 'string';
    }

    private function findDetailValue($details, $label) {
        foreach ($details as $detail) {
            if ($detail['label'] === $label) {
                return $detail['value'];
            }
        }
        return null;
    }

    private function findDetailValueById($details, $id) {
        foreach ($details as $detail) {
            if ($detail['id'] == $id) {
                return $detail['value'];
            }
        }
        return null;
    }

    private function findDetailValueByGroup($details, $group, $label) {
        foreach ($details as $detail) {
            if ($detail['group'] === $group && $detail['label'] === $label) {
                return $detail['value'];
            }
        }
        return null;
    }

    private function extract_representative_info($property) {
        $representative = [
            'id' => 0,
            'name' => '',
            'email' => '',
            'phone' => '',
            'mobile' => '',
            'picture' => ''
        ];

        // Cherche des structures possibles dans la réponse de l'API
        $candidates = [];
        if (!empty($property['employees']) && is_array($property['employees'])) {
            $candidates = $property['employees'];
        } elseif (!empty($property['employee'])) {
            $candidates = [ $property['employee'] ];
        } elseif (!empty($property['negotiator'])) {
            $candidates = [ $property['negotiator'] ];
        } elseif (!empty($property['representative'])) {
            $candidates = [ $property['representative'] ];
        }

        foreach ($candidates as $emp) {
            $role = $emp['role'] ?? ($emp['type'] ?? '');
            $is_candidate = true;
            if (!empty($role) && is_string($role)) {
                $is_candidate = (bool)preg_match('/respons|negoti|agent|broker/i', $role);
            }
            if ($is_candidate) {
                $representative['id'] = (int)($emp['id'] ?? 0);
                $representative['name'] = trim(($emp['firstName'] ?? '') . ' ' . ($emp['lastName'] ?? ''));
                if (!$representative['name']) {
                    $representative['name'] = $emp['displayName'] ?? '';
                }
                $contacts = $emp['contacts'] ?? [];
                $representative['email'] = $emp['email'] ?? ($contacts['email'] ?? '');
                $representative['phone'] = $emp['phone'] ?? ($contacts['phone'] ?? '');
                $representative['mobile'] = $emp['mobile'] ?? ($contacts['mobile'] ?? '');
                $representative['picture'] = $emp['picture'] ?? ($emp['photo'] ?? ($emp['avatar'] ?? ''));
                return $representative;
            }
        }

        return $representative;
    }

    /**
     * Récupère et stocke la liste complète des types, statuts et transactions depuis l'API Whise
     */
    public function fetch_and_store_whise_taxonomies() {
        $endpoint = get_option('whise_api_endpoint', 'https://api.whise.eu/');
        $api = new Whise_API($endpoint);
        
        // Récupération des différentes taxonomies
        $taxonomies = [
            'categories' => 'v1/estates/categories',
            'purposes' => 'v1/estates/purposes',
            'statuses' => 'v1/estates/statuses',
        ];
        
        $results = [];
        foreach ($taxonomies as $key => $url) {
            $data = $api->post($url, []);
            if (!empty($data[$key])) {
                $this->log('Taxonomie ' . $key . ' : ' . count($data[$key]) . ' éléments.');
                $results[$key] = $data[$key];
            }
        }
        
        // Stockage en base
        update_option('whise_taxonomies_full', $results);
        return $results;
    }

    /**
     * Trouve le nom d'une taxonomie Whise à partir de son ID dans la liste complète
     */
    private function find_whise_taxonomy_name($id, $list) {
        if (!$id || !$list) return '';
        foreach ($list as $item) {
            if ((string)$item['id'] === (string)$id) {
                return $item['displayName'] ?? $item['name'] ?? '';
            }
        }
        return '';
    }

    /**
     * Lance la synchronisation batch des propriétés depuis l'API Whise
     */
    public function sync_properties_batch() {
        $this->log('--- Début synchronisation Whise : ' . date('Y-m-d H:i:s'));
        
        // Mise à jour des taxonomies avant l'import
        $this->fetch_and_store_whise_taxonomies();
        
        $endpoint = get_option('whise_api_endpoint', 'https://api.whise.eu/');
        $api = new Whise_API($endpoint);
        $page = 1;
        $per_page = 50;
        $total_imported = 0;
        
        do {
            $params = [
                'page' => $page,
                'pageSize' => $per_page
            ];
            $data = $api->post('v1/estates/list', $params);
            if (!$data || empty($data['estates'])) {
                $this->log('Aucune donnée reçue ou fin de données.');
                break;
            }
            
            // Log du premier bien pour faciliter le mapping dynamique
            if ($page === 1 && !empty($data['estates'][0])) {
                $this->log('Exemple de bien (JSON): ' . json_encode($data['estates'][0]));
            }
            
            foreach ($data['estates'] as $property) {
                $this->import_property($property);
                $total_imported++;
            }
            
            $page++;
        } while (count($data['estates']) === $per_page);
        
        $this->log('Import terminé. Total biens importés/mis à jour : ' . $total_imported);
    }

    private function log($msg) {
        $logs = get_option('whise_sync_logs', []);
        if (!is_array($logs)) $logs = [];
        $logs[] = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
        // Garde les 100 derniers logs max
        if (count($logs) > 100) $logs = array_slice($logs, -100);
        update_option('whise_sync_logs', $logs);
    }

    /**
     * Importe ou met à jour un bien avec conversion des types
     */
    private function import_property($property) {
        if (empty($property['id'])) return;
        $whise_id = $property['id'];
        
        // Debug amélioré pour le champ rooms
        if (isset($property['rooms'])) {
            $this->log('DEBUG - Property ' . $whise_id . ' - rooms value from API: ' . var_export($property['rooms'], true) . ' (type: ' . gettype($property['rooms']) . ')');
        } else {
            $this->log('DEBUG - Property ' . $whise_id . ' - rooms field not found in API response');
        }
        
        // Récupération des taxonomies stockées
        $whise_taxonomies = get_option('whise_taxonomies_full', []);
        $this->log('DEBUG - Property ' . $whise_id . ' - available taxonomies: ' . json_encode(array_keys($whise_taxonomies)));
        
        // Traitement des détails
        $details = [];
        if (!empty($property['details'])) {
            foreach ($property['details'] as $detail) {
                $details[$detail['id']] = [
                    'value' => $detail['value'],
                    'label' => $detail['label'],
                    'group' => $detail['group'],
                    'type' => $detail['type']
                ];
            }
        }

        // Traitement des images
        $images = [];
        if (!empty($property['pictures'])) {
            foreach ($property['pictures'] as $picture) {
                $images[] = [
                    'id' => $picture['id'],
                    'order' => $picture['order'],
                    'small' => $picture['urlSmall'],
                    'medium' => $picture['urlLarge'],
                    'large' => $picture['urlXXL'],
                    'orientation' => $picture['orientation']
                ];
            }
        }

        // Traitement des descriptions
        $short_description = '';
        $sms_description = '';
        $long_description = '';
        if (!empty($property['shortDescription'])) {
            foreach ($property['shortDescription'] as $desc) {
                if ($desc['languageId'] === 'fr-BE') {
                    $short_description = $desc['content'];
                    break;
                }
            }
        }
        if (!empty($property['sms'])) {
            foreach ($property['sms'] as $sms) {
                if ($sms['languageId'] === 'fr-BE') {
                    $sms_description = $sms['content'];
                    break;
                }
            }
        }
        if (!empty($property['description'])) {
            foreach ($property['description'] as $desc) {
                if ($desc['languageId'] === 'fr-BE') {
                    $long_description = $desc['content'];
                    break;
                }
            }
        }

        // Mapping complet des champs Whise avec conversion des types
        $mapped_data = [
            // Identifiants de base
            'whise_id' => $property['id'],
            'reference' => $property['referenceNumber'] ?? '',
            'name' => $property['name'] ?? '',
            
            // Prix et conditions
            'price' => $property['price'] ?? 0,
            'price_formatted' => ($property['currency'] ?? '€') . number_format($property['price'] ?? 0, 0, ',', ' '),
            'currency' => $property['currency'] ?? '€',
            'price_type' => $property['purpose']['id'] == 1 ? 'vente' : 'location',
            'charges' => $this->findDetailValueById($details, 344) ?? 0, // charges (€/m²/a)
            'tenant_charges' => $this->findDetailValueById($details, 301) ?? 0, // Charges locataire
            'professional_liberal_possibility' => $this->findDetailValueById($details, 1024) ?? 0, // possibilité profession libérale
            'fitness_room_area' => $this->findDetailValueById($details, 1067) ?? 0, // salle de fitness
            'price_per_sqm' => $this->findDetailValueById($details, 338) ?? 0, // prix/m²/a
            
            // Surfaces
            'surface' => $property['area'] ?? 0,
            'total_area' => $property['totalArea'] ?? 0,
            'land_area' => $property['landArea'] ?? 0,
            'commercial_area' => $property['commercialArea'] ?? 0,
            'built_area' => $property['builtArea'] ?? 0,
            'net_area' => $this->findDetailValueById($details, 407) ?? 0, // surface nette
            'min_area' => $property['minArea'] ?? 0,
            'max_area' => $property['maxArea'] ?? 0,
            'ground_area' => $property['groundArea'] ?? 0,
            'garden_area' => $property['gardenArea'] ?? 0,
            
            // Pièces
            'rooms' => $property['rooms'] ?? 0,
            'bathrooms' => $property['bathRooms'] ?? 0,
            'floors' => $property['floors'] ?? 0,
            'number_of_floors' => $this->findDetailValueById($details, 15) ?? 0, // nombre d'étages
            'number_of_toilets' => $this->findDetailValueById($details, 55) ?? 0, // Nbre de toilette(s)
            'width_of_facade' => $this->findDetailValueById($details, 713) ?? 0, // largeur de façade
            'depth_of_land' => $this->findDetailValueById($details, 718) ?? 0, // profondeur terrain
            'width_of_street_front' => $this->findDetailValueById($details, 26) ?? 0, // largeur front de rue
            'built_area_detail' => $this->findDetailValueById($details, 27) ?? 0, // surface bâtie
            'bedrooms' => $property['bedrooms'] ?? 0,
            'fronts' => $property['fronts'] ?? 0,
            
            // Type et statut
            'property_type' => $property['category']['displayName'] ?? $property['category']['name'] ?? '',
            'property_type_id' => $property['category']['id'] ?? '',
            'property_type_language' => $property['category']['languageId'] ?? '',
            'transaction_type' => $property['purpose']['displayName'] ?? $property['purpose']['name'] ?? '',
            'transaction_type_id' => $property['purpose']['id'] ?? '',
            'transaction_type_language' => $property['purpose']['languageId'] ?? '',
            'purpose_status' => $property['purposeStatus']['displayName'] ?? $property['purposeStatus']['name'] ?? '',
            'purpose_status_id' => $property['purposeStatus']['id'] ?? '',
            'status' => $property['status']['displayName'] ?? $property['status']['name'] ?? '',
            'status_id' => $property['status']['id'] ?? '',
            'status_language' => $property['status']['languageId'] ?? '',
            'state' => $property['state']['displayName'] ?? $property['state']['name'] ?? '',
            'state_id' => $property['state']['id'] ?? '',
            'sub_categories' => $property['subCategories'] ?? [],
            'sub_category' => $property['subCategory']['displayName'] ?? $property['subCategory']['name'] ?? '',
            'sub_category_id' => $property['subCategory']['id'] ?? '',
            'construction_year' => $this->findDetailValueById($details, 14) ?? 0, // Année de construction
            'renovation_year' => $this->findDetailValueById($details, 585) ?? 0, // Année de rénovation
            
            // Localisation
            'address' => $property['address'] ?? '',
            'city' => $property['city'] ?? '',
            'postal_code' => $property['zip'] ?? '',
            'country' => $property['country']['id'] ?? '',
            'country_name' => $property['country']['displayName'] ?? $property['country']['name'] ?? '',
            'latitude' => $this->findDetailValueById($details, 1849) ?? 0.0, // x de coordonnées xy
            'longitude' => $this->findDetailValueById($details, 1850) ?? 0.0, // y de coordonnées xy
            'box' => $property['box'] ?? '',
            'number' => $property['number'] ?? '',
            'zip' => $property['zip'] ?? '',
            
            // Énergie
            'energy_class' => $property['energyClass'] ?? '',
            'energy_value' => $property['energyValue'] ?? '',
            'epc_value' => $property['epcValue'] ?? 0,
            'energy_class_detail' => $this->findDetailValueById($details, 2056) ?? '', // Label PEB
            'epc_value_detail' => $this->findDetailValueById($details, 2089) ?? 0, // PEB E-SPEC (kwh/m²/an)
            'co2_emission' => $this->findDetailValueById($details, 2090) ?? 0, // Emission CO2
            'flood_risk' => $this->findDetailValueById($details, 2222) ?? '', // risque d'inondation
            'flood_area_type' => $this->findDetailValueById($details, 2223) ?? '', // Type de zone inondable
            'heating_type' => $this->findDetailValueById($details, 1020) ?? '', // chauffage
            'heating_group' => $this->findDetailValueById($details, 53) ?? '', // chauffage (ind/coll)
            
            // Données cadastrales
            'cadastral_income' => $property['cadastralIncome'] ?? 0,
            'cadastral_income_indexed' => $this->findDetailValueById($details, 496) ?? 0, // Revenu cad. indexé
            'cadastral_income_euro' => $this->findDetailValueById($details, 1733) ?? 0, // Revenu cadastral (€)
            'cadastral_data' => $property['cadastralData'] ?? [],
            
            // Équipements
            'kitchen_type' => $this->findDetailValueById($details, 1595) ?? '', // type de cuisine
            'bathroom_type' => $this->findDetailValueById($details, 1596) ?? '', // sdb
            'parking' => (bool)($property['parking'] ?? false),
            'garage' => (bool)($property['garage'] ?? false),
            'terrace' => (bool)($property['terrace'] ?? false),
            'terrace_area' => $this->findDetailValueById($details, 874) ?? 0, // surface de terrasse 1
            'living_room_area' => $this->findDetailValueById($details, 1009) ?? 0, // Salle de séjour
            'bedroom_1_area' => $this->findDetailValueById($details, 78) ?? 0, // chambre 1
            'bedroom_2_area' => $this->findDetailValueById($details, 79) ?? 0, // chambre 2
            'bedroom_3_area' => $this->findDetailValueById($details, 80) ?? 0, // chambre 3
            'garden' => (bool)($property['garden'] ?? false),
            'swimming_pool' => (bool)($property['swimmingPool'] ?? false),
            'swimming_pool_detail' => (bool)($this->findDetailValueById($details, 322) ?? false), // piscine
            'elevator' => (bool)($this->findDetailValueById($details, 372) ?? false), // ascenseur
            'cellar' => (bool)($property['cellar'] ?? false),
            'attic' => (bool)($property['attic'] ?? false),
            'furnished' => (bool)($property['furnished'] ?? false),
            'air_conditioning' => (bool)($this->findDetailValueById($details, 43) ?? false), // air conditionné
            'double_glazing' => (bool)($this->findDetailValueById($details, 461) ?? false), // double vitrage
            'alarm' => (bool)($this->findDetailValueById($details, 1752) ?? false), // Alarme
            'concierge' => (bool)($this->findDetailValueById($details, 1762) ?? false), // concierge
            'intercom' => (bool)($this->findDetailValueById($details, 1763) ?? false), // parlophone
            'videophone' => (bool)($this->findDetailValueById($details, 1771) ?? false), // Videophone
            'water_tank' => (bool)($this->findDetailValueById($details, 1773) ?? false), // Citerne d'eau
            'telephone' => (bool)($this->findDetailValueById($details, 729) ?? false), // téléphone
            'telephone_central' => (bool)($this->findDetailValueById($details, 734) ?? false), // centrale tél.
            'electricity' => (bool)($this->findDetailValueById($details, 757) ?? false), // électricité
            'cable_tv' => (bool)($this->findDetailValueById($details, 1757) ?? false), // télévision par cable
            'gas' => (bool)($this->findDetailValueById($details, 1760) ?? false), // gaz
            'water' => (bool)($this->findDetailValueById($details, 1772) ?? false), // eau
            'oil_tank' => (bool)($this->findDetailValueById($details, 758) ?? false), // citerne à mazout
            'tank_certificate' => (bool)($this->findDetailValueById($details, 763) ?? false), // Certificat citerne
            'sewers' => (bool)($this->findDetailValueById($details, 724) ?? false), // égouts
            'veranda' => (bool)($this->findDetailValueById($details, 56) ?? false), // véranda
            'office' => (bool)($this->findDetailValueById($details, 67) ?? false), // bureau
            'cellar' => (bool)($this->findDetailValueById($details, 134) ?? false), // débarras
            'fitness_room' => (bool)($this->findDetailValueById($details, 142) ?? false), // salle de fitness
            'kitchen' => (bool)($this->findDetailValueById($details, 38) ?? false), // cuisine
            'handicap_access' => (bool)($this->findDetailValueById($details, 22) ?? false), // accès handicapés
            'professional_liberal_possibility_bool' => (bool)($this->findDetailValueById($details, 59) ?? false), // Profession libérale poss.
            'insulation' => (bool)($this->findDetailValueById($details, 778) ?? false), // isolation
            'toilets_mf' => (bool)($this->findDetailValueById($details, 380) ?? false), // toilettes H/F
            'vta_regime' => (bool)($this->findDetailValueById($details, 574) ?? false), // Sous régime TVA
            'building_permit' => (bool)($this->findDetailValueById($details, 808) ?? false), // Permis de bâtir
            'subdivision_permit' => (bool)($this->findDetailValueById($details, 812) ?? false), // Permis de lotir
            'ongoing_judgment' => (bool)($this->findDetailValueById($details, 691) ?? false), // Jugement en cours
            'right_of_preemption' => (bool)($this->findDetailValueById($details, 1734) ?? false), // Droit de préemption
            'rented' => (bool)($this->findDetailValueById($details, 824) ?? false), // Loué
            'soil_certificate' => (bool)($this->findDetailValueById($details, 820) ?? false), // attestation du sol
            'investment_estate' => (bool)($property['investmentEstate'] ?? false),
            'display_address' => (bool)($property['displayAddress'] ?? true),
            'display_price' => (bool)($property['displayPrice'] ?? true),
            'display_status_id' => $property['displayStatusId'] ?? 0,
            
            // Proximité
            'proximity_school' => $property['proximitySchool'] ?? '',
            'proximity_shops' => $property['proximityShops'] ?? '',
            'proximity_transport' => $this->findDetailValueById($details, 110) ?? '', // transports en commun
            'proximity_hospital' => $property['proximityHospital'] ?? '',
            'proximity_city_center' => $this->findDetailValueById($details, 111) ?? '', // Centre-ville
            'proximity_shops_detail' => $this->findDetailValueById($details, 108) ?? '', // magasins
            'proximity_schools_detail' => $this->findDetailValueById($details, 109) ?? '', // écoles
            'proximity_beach' => $this->findDetailValueById($details, 115) ?? '', // plage
            'proximity_shops_bool' => (bool)($this->findDetailValueById($details, 1781) ?? false), // Magasins
            'proximity_schools_bool' => (bool)($this->findDetailValueById($details, 1782) ?? false), // Ecoles
            'proximity_transport_bool' => (bool)($this->findDetailValueById($details, 1783) ?? false), // Transports en commun
            'proximity_city_center_bool' => (bool)($this->findDetailValueById($details, 1784) ?? false), // Centre-ville
            'proximity_beach_bool' => (bool)($this->findDetailValueById($details, 1788) ?? false), // Plage
            
            // Disponibilité
            'availability' => $property['availability']['id'] ?? '',
            'availability_date' => $property['availabilityDateTime'] ?? '',
            'is_immediately_available' => (bool)($property['isImmediatelyAvailable'] ?? false),
            
            // Orientation et vues
            'orientation' => $property['orientation'] ?? '',
            'view' => $property['view'] ?? '',
            'building_orientation' => $this->findDetailValueById($details, 23) ?? '', // orientation du bâtiment
            'environment_type' => $this->findDetailValueById($details, 795) ?? '', // Type d'environnement
            
            // Bureaux spécifiques
            'office_1' => $this->findDetailValueById($details, 1494) ?? 0, // bureau 1
            'office_2' => $this->findDetailValueById($details, 1495) ?? 0, // bureau 2
            'office_3' => $this->findDetailValueById($details, 1496) ?? 0, // bureau 3
            
            // Matériaux et finitions
            'floor_material' => $this->findDetailValueById($details, 1617) ?? '', // revêtement de sol de bureaux général
            'floor_material_general' => $this->findDetailValueById($details, 1183) ?? '', // type de revêtement de sol
            'ground_destination' => $this->findDetailValueById($details, 1736) ?? '', // affectation urbanistique
            
            // Dates
            'create_date' => $property['createDateTime'] ?? '',
            'update_date' => $property['updateDateTime'] ?? '',
            'put_online_date' => $property['putOnlineDateTime'] ?? '',
            'price_change_date' => $property['priceChangeDateTime'] ?? '',
            
            // Informations client
            'client_id' => $property['clientId'] ?? 0,
            'office_id' => $property['officeId'] ?? 0,
            'client_name' => $property['client'] ?? '',
            'office_name' => $property['office'] ?? '',
            
            // Descriptions
            'short_description' => $short_description,
            'sms_description' => $sms_description,
            
            // Images
            'images' => $images,
            
            // Détails complets
            'details' => $details,

            // Liens médias (WebsiteDesigner)
            // Docs: https://api.whise.eu/WebsiteDesigner.html
            'link_3d_model' => $property['link3DModel'] ?? ($property['links']['link3DModel'] ?? ''),
            'link_virtual_visit' => $property['linkVirtualVisit'] ?? ($property['links']['linkVirtualVisit'] ?? ''),
            'link_video' => $property['linkVideo'] ?? ($property['links']['linkVideo'] ?? ($property['videoUrl'] ?? '')),
        ];
        
        // Debug final pour vérifier le mapping des rooms
        $this->log('DEBUG - Property ' . $whise_id . ' - mapped rooms value: ' . var_export($mapped_data['rooms'], true));
        
        // Recherche d'un post existant avec ce whise_id
        $existing = get_posts([
            'post_type' => 'property',
            'meta_key' => 'whise_id',
            'meta_value' => $whise_id,
            'post_status' => 'any',
            'numberposts' => 1
        ]);
        
        $postarr = [
            'post_type' => 'property',
            'post_title' => $property['name'] ?? ($property['referenceNumber'] ?? ''),
            'post_content' => $long_description ?: $short_description,
            'post_excerpt' => $short_description,
            'post_status' => 'publish',
        ];
        
        if ($existing) {
            $postarr['ID'] = $existing[0]->ID;
            $post_id = wp_update_post($postarr);
        } else {
            $post_id = wp_insert_post($postarr);
        }
        
        if (is_wp_error($post_id) || !$post_id) return;

        // Compléter avec le représentant si disponible
        $rep = $this->extract_representative_info($property);
        if (empty($rep['name'])) {
            // Fallback: tenter de récupérer les détails du bien pour y trouver l'agent
            try {
                $endpoint = get_option('whise_api_endpoint', 'https://api.whise.eu/');
                $api = new Whise_API($endpoint);
                $estate_detail = $api->post('v1/estates/get', [ 'id' => $whise_id ]);
                if (!empty($estate_detail['estate'])) {
                    $rep2 = $this->extract_representative_info($estate_detail['estate']);
                    if (!empty($rep2['name'])) {
                        $rep = $rep2;
                    }
                }
            } catch (Exception $e) {
                // ignore
            }
        }
        if (!empty($rep)) {
            $mapped_data['representative_id'] = $rep['id'];
            $mapped_data['representative_name'] = $rep['name'];
            $mapped_data['representative_email'] = $rep['email'];
            $mapped_data['representative_phone'] = $rep['phone'];
            $mapped_data['representative_mobile'] = $rep['mobile'];
            $mapped_data['representative_picture'] = $rep['picture'];
        }

        // Mise à jour des meta avec conversion des types
        foreach ($mapped_data as $key => $value) {
            $type = $this->get_field_type($key);
            $converted_value = $this->convert_value($value, $type);
            update_post_meta($post_id, $key, $converted_value);
            
            // Debug spécifique pour le champ rooms
            if ($key === 'rooms') {
                $this->log('DEBUG - Property ' . $whise_id . ' - rooms conversion: original=' . var_export($value, true) . ', type=' . $type . ', converted=' . var_export($converted_value, true));
                $saved_value = get_post_meta($post_id, 'rooms', true);
                $this->log('DEBUG - Property ' . $whise_id . ' - rooms saved in DB: ' . var_export($saved_value, true) . ' (type: ' . gettype($saved_value) . ')');
            }
        }

        // Mise à jour des taxonomies (on utilise displayName en priorité)
        $category = $property['category'] ?? [];
        $category_id = $category['id'] ?? '';
        $category_name = $category['displayName'] ?? '';
        if (empty($category_name)) {
            $category_name = $this->find_whise_taxonomy_name($category_id, $whise_taxonomies['categories'] ?? []);
        }
        if (empty($category_name)) {
            $category_name = $category['name'] ?? '';
        }
        if (empty($category_name)) {
            $category_name = $this->get_default_category_name($category_id);
            if ($category_name) {
                $this->log('DEBUG - Property ' . $whise_id . ' - using default category name: ' . $category_name . ' for ID: ' . $category_id);
            }
        }
        
        $this->log('DEBUG - Property ' . $whise_id . ' - category data: ' . json_encode($category));
        $this->log('DEBUG - Property ' . $whise_id . ' - category_name resolved: ' . $category_name);
        
        if ($category_name) {
            wp_set_object_terms($post_id, $category_name, 'property_type', false);
            $this->log('DEBUG - Property ' . $whise_id . ' - assigned to property_type: ' . $category_name);
        } else {
            $this->log('DEBUG - Property ' . $whise_id . ' - NO category name found');
        }
        
        $purpose = $property['purpose'] ?? [];
        $purpose_id = $purpose['id'] ?? '';
        $purpose_name = $purpose['displayName'] ?? '';
        if (empty($purpose_name)) {
            $purpose_name = $this->find_whise_taxonomy_name($purpose_id, $whise_taxonomies['purposes'] ?? []);
        }
        if (empty($purpose_name)) {
            $purpose_name = $purpose['name'] ?? '';
        }
        if (empty($purpose_name)) {
            $purpose_name = $this->get_default_purpose_name($purpose_id);
        }
        if ($purpose_name) {
            wp_set_object_terms($post_id, $purpose_name, 'transaction_type', false);
            $this->log('DEBUG - Property ' . $whise_id . ' - assigned to transaction_type: ' . $purpose_name);
        }
        
        if (!empty($property['city'])) {
            wp_set_object_terms($post_id, $property['city'], 'property_city', false);
        }
        
        $status = $property['status'] ?? [];
        $status_id = $status['id'] ?? '';
        $status_name = $status['displayName'] ?? '';
        if (empty($status_name)) {
            $status_name = $this->find_whise_taxonomy_name($status_id, $whise_taxonomies['statuses'] ?? []);
        }
        if (empty($status_name)) {
            $status_name = $status['name'] ?? '';
        }
        if (empty($status_name)) {
            $status_name = $this->get_default_status_name($status_id);
        }
        if ($status_name) {
            wp_set_object_terms($post_id, $status_name, 'property_status', false);
            $this->log('DEBUG - Property ' . $whise_id . ' - assigned to property_status: ' . $status_name);
        }
        
        // Gestion des sous-catégories
        if (!empty($property['subCategories'])) {
            foreach ($property['subCategories'] as $subCategory) {
                $sub_name = $subCategory['displayName'] ?? $subCategory['name'] ?? '';
                if ($sub_name) {
                    wp_set_object_terms($post_id, $sub_name, 'property_type', true); // true pour ajouter, ne pas remplacer
                }
            }
        }
    }

    /**
     * Retourne un nom de catégorie par défaut basé sur l'ID Whise
     */
    private function get_default_category_name($category_id) {
        $default_categories = [
            '1' => 'Maison',
            '2' => 'Appartement', 
            '3' => 'Terrain',
            '4' => 'Bureau',
            '5' => 'Commerce',
            '6' => 'Immeuble',
            '7' => 'Garage',
            '8' => 'Autre'
        ];
        
        return $default_categories[(string)$category_id] ?? null;
    }

    /**
     * Retourne un nom de transaction par défaut basé sur l'ID Whise
     */
    private function get_default_purpose_name($purpose_id) {
        $default_purposes = [
            '1' => 'Vente',
            '2' => 'Location'
        ];
        
        return $default_purposes[(string)$purpose_id] ?? null;
    }

    /**
     * Retourne un nom de statut par défaut basé sur l'ID Whise
     */
    private function get_default_status_name($status_id) {
        $default_statuses = [
            '1' => 'Disponible',
            '2' => 'Sous option',
            '3' => 'Vendu/Loué',
            '4' => 'Retiré'
        ];
        
        return $default_statuses[(string)$status_id] ?? null;
    }
}
