<?php
if (!defined('ABSPATH')) exit;

class Whise_Sync_Manager {
    /**
     * Traite et convertit une valeur selon son type défini
     */
    private function convert_value($value, $type) {
        if ($value === null || $value === '') {
            return '';
        }
        
        switch ($type) {
            case 'number':
                return is_numeric($value) ? (int)$value : 0;
            case 'float':
                return is_numeric($value) ? (float)$value : 0.0;
            case 'boolean':
                return (bool)$value;
            case 'array':
                return is_array($value) ? $value : [];
            case 'string':
            default:
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
            'transaction_type' => 'string',
            'status' => 'string',
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

    /**
     * Lance la synchronisation batch des propriétés depuis l'API Whise
     */
    public function sync_properties_batch() {
        $this->log('--- Début synchronisation Whise : ' . date('Y-m-d H:i:s'));
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

        // Mapping complet des champs Whise avec conversion des types
        $mapped_data = [
            // Identifiants de base
            'whise_id' => $property['id'],
            'reference' => $property['referenceNumber'] ?? '',
            
            // Prix et conditions
            'price' => $property['price'] ?? 0,
            'price_formatted' => ($property['currency'] ?? '€') . number_format($property['price'] ?? 0, 0, ',', ' '),
            'price_type' => $property['purpose']['id'] == 1 ? 'vente' : 'location',
            'charges' => $this->findDetailValueById($details, 344) ?? 0, // charges (€/m²/a)
            'price_per_sqm' => $this->findDetailValueById($details, 338) ?? 0, // prix/m²/a
            
            // Surfaces
            'surface' => $property['area'] ?? 0,
            'total_area' => $property['totalArea'] ?? 0,
            'land_area' => $property['landArea'] ?? 0,
            'commercial_area' => $property['commercialArea'] ?? 0,
            'built_area' => $property['builtArea'] ?? 0,
            'min_area' => $property['minArea'] ?? 0,
            'max_area' => $property['maxArea'] ?? 0,
            'ground_area' => $property['groundArea'] ?? 0,
            
            // Pièces
            'rooms' => $property['bedrooms'] ?? 0,
            'bathrooms' => $property['bathrooms'] ?? 0,
            'floors' => $property['floors'] ?? 0,
            'bedrooms' => $property['bedrooms'] ?? 0,
            
            // Type et statut
            'property_type' => $property['category']['id'] ?? '',
            'transaction_type' => $property['purpose']['id'] ?? '',
            'status' => $property['status']['id'] ?? '',
            'construction_year' => $this->findDetailValueById($details, 14) ?? 0, // Année de construction
            'renovation_year' => $this->findDetailValueById($details, 585) ?? 0, // Année de rénovation
            
            // Localisation
            'address' => $property['address'] ?? '',
            'city' => $property['city'] ?? '',
            'postal_code' => $property['zip'] ?? '',
            'country' => $property['country']['id'] ?? '',
            'latitude' => $this->findDetailValueById($details, 1849) ?? 0.0, // x de coordonnées xy
            'longitude' => $this->findDetailValueById($details, 1850) ?? 0.0, // y de coordonnées xy
            'box' => $property['box'] ?? '',
            'number' => $property['number'] ?? '',
            'zip' => $property['zip'] ?? '',
            
            // Énergie
            'energy_class' => $property['energyClass'] ?? '',
            'epc_value' => $property['epcValue'] ?? 0,
            'heating_type' => $this->findDetailValueById($details, 1020) ?? '', // chauffage
            'heating_group' => $this->findDetailValueById($details, 53) ?? '', // chauffage (ind/coll)
            
            // Données cadastrales
            'cadastral_income' => $property['cadastralIncome'] ?? 0,
            'cadastral_data' => $property['cadastralData'] ?? [],
            
            // Équipements
            'kitchen_type' => $this->findDetailValueById($details, 1595) ?? '', // type de cuisine
            'parking' => (bool)($property['parking'] ?? false),
            'garage' => (bool)($property['garage'] ?? false),
            'terrace' => (bool)($property['terrace'] ?? false),
            'garden' => (bool)($property['garden'] ?? false),
            'swimming_pool' => (bool)($property['swimmingPool'] ?? false),
            'elevator' => (bool)($this->findDetailValueById($details, 372) ?? false), // ascenseur
            'cellar' => (bool)($property['cellar'] ?? false),
            'attic' => (bool)($property['attic'] ?? false),
            'furnished' => (bool)($property['furnished'] ?? false),
            'air_conditioning' => (bool)($this->findDetailValueById($details, 43) ?? false), // air conditionné
            'double_glazing' => (bool)($this->findDetailValueById($details, 461) ?? false), // double vitrage
            'alarm' => (bool)($this->findDetailValueById($details, 1752) ?? false), // Alarme
            'concierge' => (bool)($this->findDetailValueById($details, 1762) ?? false), // concierge
            'telephone' => (bool)($this->findDetailValueById($details, 729) ?? false), // téléphone
            'telephone_central' => (bool)($this->findDetailValueById($details, 734) ?? false), // centrale tél.
            'electricity' => (bool)($this->findDetailValueById($details, 757) ?? false), // électricité
            'oil_tank' => (bool)($this->findDetailValueById($details, 758) ?? false), // citerne à mazout
            'insulation' => (bool)($this->findDetailValueById($details, 778) ?? false), // isolation
            'toilets_mf' => (bool)($this->findDetailValueById($details, 380) ?? false), // toilettes H/F
            'vta_regime' => (bool)($this->findDetailValueById($details, 574) ?? false), // Sous régime TVA
            'building_permit' => (bool)($this->findDetailValueById($details, 808) ?? false), // Permis de bâtir
            'subdivision_permit' => (bool)($this->findDetailValueById($details, 812) ?? false), // Permis de lotir
            'ongoing_judgment' => (bool)($this->findDetailValueById($details, 691) ?? false), // Jugement en cours
            
            // Proximité
            'proximity_school' => $property['proximitySchool'] ?? '',
            'proximity_shops' => $property['proximityShops'] ?? '',
            'proximity_transport' => $this->findDetailValueById($details, 110) ?? '', // transports en commun
            'proximity_hospital' => $property['proximityHospital'] ?? '',
            'proximity_city_center' => $this->findDetailValueById($details, 111) ?? '', // Centre-ville
            
            // Disponibilité
            'availability' => $property['availability']['id'] ?? '',
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
        ];
        
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
            'post_content' => $short_description,
            'post_status' => 'publish',
        ];
        
        if ($existing) {
            $postarr['ID'] = $existing[0]->ID;
            $post_id = wp_update_post($postarr);
        } else {
            $post_id = wp_insert_post($postarr);
        }
        
        if (is_wp_error($post_id) || !$post_id) return;

        // Mise à jour des meta avec conversion des types
        foreach ($mapped_data as $key => $value) {
            $type = $this->get_field_type($key);
            $converted_value = $this->convert_value($value, $type);
            update_post_meta($post_id, $key, $converted_value);
        }

        // Mise à jour des taxonomies
        if (!empty($property['category']['id'])) {
            wp_set_object_terms($post_id, $property['category']['id'], 'property_type', false);
        }
        if (!empty($property['purpose']['id'])) {
            wp_set_object_terms($post_id, $property['purpose']['id'], 'transaction_type', false);
        }
        if (!empty($property['city'])) {
            wp_set_object_terms($post_id, $property['city'], 'property_city', false);
        }
        if (!empty($property['status']['id'])) {
            wp_set_object_terms($post_id, $property['status']['id'], 'property_status', false);
        }
    }
}
