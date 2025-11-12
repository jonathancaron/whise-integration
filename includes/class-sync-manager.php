<?php
if (!defined('ABSPATH')) exit;

class Whise_Sync_Manager {

    /**
     * Retourne la liste des statuts à exclure (non utiles pour l'affichage public)
     * @return array Tableau associatif [id => nom]
     */
    private function get_excluded_status_ids() {
        return [
            8 => 'retiré v.',
            10 => 'suspendu v.',
            19 => 'prospection',
            20 => 'préparation',
            24 => 'estimation v.'
        ];
    }

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
            'state' => 'string',
            'state_id' => 'string',
            'purpose_status' => 'string',
            'purpose_status_id' => 'number',
            'transaction_status' => 'string',
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
            
            // Descriptions multilingues - Short Description
            'short_description_fr' => 'string',
            'short_description_nl' => 'string',
            'short_description_en' => 'string',
            
            // Descriptions multilingues - SMS
            'sms_description_fr' => 'string',
            'sms_description_nl' => 'string',
            'sms_description_en' => 'string',
            
            // Descriptions multilingues - Long Description
            'description_fr' => 'string',
            'description_nl' => 'string',
            'description_en' => 'string',
            
            // Structure multilingue complète
            'descriptions_multilingual' => 'array',
            
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
            'representative_function' => 'string',
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
        } elseif (!empty($property['assignedEmployees']) && is_array($property['assignedEmployees'])) {
            $candidates = $property['assignedEmployees'];
        } elseif (!empty($property['estateEmployees']) && is_array($property['estateEmployees'])) {
            $candidates = $property['estateEmployees'];
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
                $lastName = $emp['lastName'] ?? ($emp['name'] ?? '');
                $representative['name'] = trim(($emp['firstName'] ?? '') . ' ' . $lastName);
                if (!$representative['name']) {
                    $representative['name'] = $emp['displayName'] ?? '';
                }
                $contacts = $emp['contacts'] ?? [];
                $representative['email'] = $emp['email'] ?? ($contacts['email'] ?? '');
                // directLine (WebsiteDesigner list) prioritaire si présent
                $phoneCandidates = [
                    $emp['directLine'] ?? null,
                    $emp['phone'] ?? null,
                    $contacts['phone'] ?? null,
                    $emp['phoneNumber'] ?? null,
                ];
                foreach ($phoneCandidates as $pc) { if (!empty($pc)) { $representative['phone'] = $pc; break; } }
                $mobileCandidates = [
                    $emp['mobile'] ?? null,
                    $contacts['mobile'] ?? null,
                    $emp['mobilePhone'] ?? null,
                    $emp['gsm'] ?? null,
                    $emp['cellphone'] ?? null,
                ];
                foreach ($mobileCandidates as $mc) { if (!empty($mc)) { $representative['mobile'] = $mc; break; } }
                $representative['picture'] = $emp['picture'] ?? ($emp['photo'] ?? ($emp['avatar'] ?? ($emp['pictureUrl'] ?? (is_array($emp['pictures'] ?? null) ? ($emp['pictures']['profile'] ?? '') : ''))));
                $representative['function'] = $role ?: ($emp['function'] ?? ($emp['jobTitle'] ?? ($emp['representativeTypeInEx'] ?? '')));
                return $representative;
            }
        }

        return $representative;
    }

    private function fetch_estate_representative($estateId) {
        $endpoint = get_option('whise_api_endpoint', 'https://api.whise.eu/');
        $api = new Whise_API($endpoint);
        $this->log('DEBUG - Fetch representative for estate ' . $estateId);

        // 1) Détail du bien (réponse la plus riche). On tente plusieurs variantes de paramètres
        $getVariants = [
            [ 'id' => $estateId ],
            [ 'Id' => $estateId ],
            [ 'id' => $estateId, 'include' => 'employees' ],
            [ 'Id' => $estateId, 'include' => 'employees' ],
            [ 'id' => $estateId, 'include' => 'Employees' ],
            [ 'Id' => $estateId, 'include' => 'Employees' ],
        ];
        foreach ($getVariants as $variant) {
            $detail = $api->post('v1/estates/get', $variant);
            if (!empty($detail['estate'])) {
                $estate = $detail['estate'];
                $this->log('DEBUG - Estate get keys: ' . implode(',', array_keys($estate)) . ' (params: ' . json_encode($variant) . ')');
                $rep = $this->extract_representative_info($estate);
                if (!empty($rep['name']) || !empty($rep['email']) || !empty($rep['phone']) || !empty($rep['mobile'])) {
                    return $rep;
                }
                // Si pas trouvé, essayer d'identifier des IDs d'employés dans le payload
                $employeeIds = $this->find_employee_ids_in_estate($estate);
                if (!empty($employeeIds)) {
                    foreach ($employeeIds as $empId) {
                        $emp = $this->fetch_employee_by_id($empId);
                        if (!empty($emp['name']) || !empty($emp['email']) || !empty($emp['phone']) || !empty($emp['mobile'])) {
                            return $emp;
                        }
                    }
                }
            }
        }

        // 2) Tenter un endpoint dédié aux employés du bien (selon WebsiteDesigner)
        $employeesList = $api->post('v1/estates/employees', [ 'estateId' => $estateId ]);
        if (empty($employeesList['employees'])) {
            $employeesList = $api->post('v1/estates/employees', [ 'EstateId' => $estateId ]);
        }
        if (!empty($employeesList['employees']) && is_array($employeesList['employees'])) {
            $this->log('DEBUG - Estate employees count: ' . count($employeesList['employees']));
            $rep = $this->extract_representative_info([ 'employees' => $employeesList['employees'] ]);
            if (!empty($rep['name']) || !empty($rep['email']) || !empty($rep['phone']) || !empty($rep['mobile'])) {
                return $rep;
            }
        }

        // 3) Tenter un autre détail générique
        $detail2 = $api->post('v1/estates/details', [ 'id' => $estateId ]);
        if (empty($detail2['estate'])) {
            $detail2 = $api->post('v1/estates/details', [ 'Id' => $estateId ]);
        }
        if (!empty($detail2['estate'])) {
            $estate = $detail2['estate'];
            $this->log('DEBUG - Estate details keys: ' . implode(',', array_keys($estate)));
            $rep = $this->extract_representative_info($estate);
            if (!empty($rep['name']) || !empty($rep['email']) || !empty($rep['phone']) || !empty($rep['mobile'])) {
                return $rep;
            }
        }

        return [
            'id' => 0,
            'name' => '',
            'email' => '',
            'phone' => '',
            'mobile' => '',
            'picture' => ''
        ];
    }

    private function find_employee_ids_in_estate($estate) {
        $ids = [];
        $walk = function($node) use (&$ids, &$walk) {
            if (is_array($node)) {
                foreach ($node as $key => $value) {
                    if (is_string($key) && preg_match('/(^|_)employee(Id)?$|negotiatorId|responsible(Id)?|representative(Id)?|userId/i', $key)) {
                        if (is_numeric($value)) {
                            $ids[] = (int)$value;
                        }
                    }
                    if (is_array($value)) {
                        $walk($value);
                    }
                }
            }
        };
        $walk($estate);
        $ids = array_values(array_unique(array_filter($ids)));
        $this->log('DEBUG - Found possible employee IDs in estate: ' . json_encode($ids));
        return $ids;
    }

    private function fetch_employee_by_id($employeeId) {
        if (!$employeeId) return [ 'id' => 0, 'name' => '', 'email' => '', 'phone' => '', 'mobile' => '', 'picture' => '' ];
        $endpoint = get_option('whise_api_endpoint', 'https://api.whise.eu/');
        $api = new Whise_API($endpoint);
        $this->log('DEBUG - Fetch employee by id: ' . $employeeId);
        $res = $api->post('v1/employees/get', [ 'id' => $employeeId ]);
        if (!empty($res['employee'])) {
            $e = $res['employee'];
            return [
                'id' => (int)($e['id'] ?? $employeeId),
                'name' => trim(($e['firstName'] ?? '') . ' ' . ($e['lastName'] ?? '')) ?: ($e['displayName'] ?? ''),
                'email' => $e['email'] ?? ($e['contacts']['email'] ?? ''),
                'phone' => $e['phone'] ?? ($e['contacts']['phone'] ?? ''),
                'mobile' => $e['mobile'] ?? ($e['contacts']['mobile'] ?? ''),
                'picture' => $e['picture'] ?? ($e['photo'] ?? ($e['avatar'] ?? ''))
            ];
        }
        return [ 'id' => 0, 'name' => '', 'email' => '', 'phone' => '', 'mobile' => '', 'picture' => '' ];
    }

    /**
     * Retourne une description multilingue depuis les métadonnées, utilisée par le template
     */
    public function get_description_by_language($post_id, $field = 'shortDescription', $language = 'fr-BE') {
        $multilingual_data = get_post_meta($post_id, 'descriptions_multilingual', true);
        if (is_array($multilingual_data) && isset($multilingual_data[$field][$language])) {
            return $multilingual_data[$field][$language];
        }
        $fallback_field = $field === 'shortDescription' ? 'short_description' : ($field === 'sms' ? 'sms_description' : $field);
        return get_post_meta($post_id, $fallback_field, true);
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
            'states' => 'v1/estates/states',
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
        
        // Augmenter le temps d'exécution pour éviter les timeouts
        set_time_limit(0); // Pas de limite de temps
        ini_set('max_execution_time', 0);
        
        // Augmenter la mémoire disponible
        if (function_exists('ini_set')) {
            ini_set('memory_limit', '512M');
        }
        
        $this->log('DEBUG - Configuration: max_execution_time=' . ini_get('max_execution_time') . ', memory_limit=' . ini_get('memory_limit'));
        
        // Mise à jour des taxonomies avant l'import
        $this->fetch_and_store_whise_taxonomies();
        
        $endpoint = get_option('whise_api_endpoint', 'https://api.whise.eu/');
        $api = new Whise_API($endpoint);
        $page = 1;
        $per_page = get_option('whise_batch_size', 50); // Augmenté à 50 par défaut pour plus de performance
        $total_imported = 0;
        $whise_ids_from_api = []; // Pour tracker les IDs présents dans l'API
        
        $this->log('DEBUG - Configuration batch: ' . $per_page . ' biens par page');
        
        do {
            // Essayer avec filtres pour récupérer les représentants et toutes les langues
            $baseParams = [ 'page' => $page, 'pageSize' => $per_page ];
            $variants = [
                $baseParams + [ 'Filter' => [ 'ShowRepresentatives' => true, 'LanguageIds' => ['fr-BE', 'nl-BE', 'en-GB'] ] ],
                $baseParams + [ 'filter' => [ 'ShowRepresentatives' => true, 'LanguageIds' => ['fr-BE', 'nl-BE', 'en-GB'] ] ],
                $baseParams + [ 'Filter' => [ 'ShowRepresentatives' => true, 'languageIds' => ['fr-BE', 'nl-BE', 'en-GB'] ] ],
                $baseParams + [ 'filter' => [ 'ShowRepresentatives' => true, 'languageIds' => ['fr-BE', 'nl-BE', 'en-GB'] ] ],
                $baseParams + [ 'Filter' => [ 'ShowRepresentatives' => true ] ],
                $baseParams + [ 'filter' => [ 'ShowRepresentatives' => true ] ],
                $baseParams + [ 'Filter' => [ 'showRepresentatives' => true ] ],
                $baseParams + [ 'filter' => [ 'showRepresentatives' => true ] ],
                $baseParams,
            ];
            $data = null;
            foreach ($variants as $variant) {
                $trial = $api->post('v1/estates/list', $variant);
                if (!empty($trial['estates'])) {
                    $data = $trial;
                    if ($page === 1) {
                        $this->log('DEBUG - estates/list variant accepted (params): ' . json_encode(array_keys($variant)));
                    }
                    break;
                }
            }
            if (!$data || empty($data['estates'])) {
                $this->log('Aucune donnée reçue ou fin de données.');
                break;
            }
            
            // Log du premier bien pour faciliter le mapping dynamique
            if ($page === 1 && !empty($data['estates'][0])) {
                $this->log('Exemple de bien (JSON): ' . json_encode($data['estates'][0]));
            }
            
            foreach ($data['estates'] as $property) {
                // Log uniquement pour la première page et les propriétés de debug
                $debug_property_ids = ['7136195'];
                $is_debug_property = $page === 1 && isset($property['representatives']) && in_array((string)($property['id'] ?? ''), $debug_property_ids, true);
                if ($is_debug_property) {
                    $this->log('DEBUG - representatives in list for estate ' . ($property['id'] ?? 'n/a') . ' count: ' . (is_array($property['representatives']) ? count($property['representatives']) : 0));
                }
                
                // Exclure les biens avec des statuts non utiles
                $purpose_status_id = $property['purposeStatus']['id'] ?? 0;
                $excluded_status_ids = $this->get_excluded_status_ids();
                
                if (isset($excluded_status_ids[$purpose_status_id])) {
                    $status_name = $excluded_status_ids[$purpose_status_id];
                    $this->log('INFO - Bien ' . ($property['id'] ?? 'inconnu') . ' ignoré : statut "' . $status_name . '" (purpose_status_id=' . $purpose_status_id . ')');
                    // Ne pas ajouter à whise_ids_from_api pour que le bien soit supprimé lors du nettoyage s'il existe déjà
                    continue; // Passer au bien suivant
                }
                
                // Collecter l'ID Whise pour la vérification des biens obsolètes (seulement pour les biens non retirés)
                if (!empty($property['id'])) {
                    $whise_ids_from_api[] = (string)$property['id'];
                }
                
                try {
                    $this->import_property($property);
                    $total_imported++;
                    
                    // Pause réduite (seulement si nécessaire pour éviter de surcharger l'API)
                    // Supprimée pour améliorer les performances
                    
                } catch (Exception $e) {
                    $this->log('ERROR - Échec import propriété ' . ($property['id'] ?? 'inconnue') . ': ' . $e->getMessage());
                    continue; // Continuer avec la propriété suivante
                }
            }
            
            $page++;
            
            // Pause réduite entre les pages (seulement si nécessaire)
            // Supprimée pour améliorer les performances - l'API Whise peut gérer les requêtes rapides
        } while (count($data['estates']) === $per_page);
        
        $this->log('Import terminé. Total biens importés/mis à jour : ' . $total_imported);
        
        // Supprimer les biens qui ne sont plus dans l'API Whise
        $cleanup_enabled = get_option('whise_cleanup_obsolete', true);
        if ($cleanup_enabled) {
            $this->cleanup_obsolete_properties($whise_ids_from_api);
        } else {
            $this->log('INFO - Nettoyage automatique désactivé, conservation de tous les biens');
        }
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
     * Essaie de récupérer les coordonnées depuis les détails WHISE (1849/1850) si non présentes
     */
    private function enrich_coordinates_from_whise($post_id, $estateId) {
        $lat = (float)get_post_meta($post_id, 'latitude', true);
        $lng = (float)get_post_meta($post_id, 'longitude', true);
        if ($lat !== 0.0 && $lng !== 0.0) {
            return; // déjà présents
        }
        $endpoint = get_option('whise_api_endpoint', 'https://api.whise.eu/');
        $api = new Whise_API($endpoint);
        $this->log('DEBUG - Enrich coordinates for estate ' . $estateId);
        // Essayer plusieurs variantes pour forcer le retour des détails
        $details_variants = [
            ['id' => $estateId, 'include' => 'details'],
            ['Id' => $estateId, 'include' => 'details'],
            ['id' => $estateId, 'Include' => 'details'],
            ['Id' => $estateId, 'Include' => 'details'],
            ['id' => $estateId],
            ['Id' => $estateId],
        ];
        $estate_detail = null;
        foreach ($details_variants as $variant) {
            $trial = $api->post('v1/estates/get', $variant);
            if (!empty($trial['estate'])) { 
                $estate_detail = $trial['estate']; 
                $this->log('DEBUG - estates/get accepted with params: ' . json_encode(array_keys($variant)) . ' keys: ' . implode(',', array_keys($estate_detail)));
                break; 
            }
        }
        if (!$estate_detail) {
            $trial = $api->post('v1/estates/details', ['id' => $estateId]);
            if (!empty($trial['estate'])) { 
                $estate_detail = $trial['estate'];
                $this->log('DEBUG - estates/details (id) returned keys: ' . implode(',', array_keys($estate_detail)));
            } else {
                // parfois le payload est directement l'objet du bien
                if (!empty($trial) && is_array($trial)) {
                    $this->log('DEBUG - estates/details raw keys: ' . implode(',', array_keys($trial)));
                }
            }
        }
        if (!$estate_detail) {
            $trial = $api->post('v1/estates/details', ['Id' => $estateId]);
            if (!empty($trial['estate'])) { 
                $estate_detail = $trial['estate'];
                $this->log('DEBUG - estates/details (Id) returned keys: ' . implode(',', array_keys($estate_detail)));
            }
        }
        $details_array = [];
        if (!empty($estate_detail['details']) && is_array($estate_detail['details'])) {
            $this->log('DEBUG - details count: ' . count($estate_detail['details']));
            foreach ($estate_detail['details'] as $d) {
                if (!isset($d['id'])) { continue; }
                $details_array[(string)$d['id']] = $d['value'] ?? null;
            }
        }
        // keys peuvent être string
        $lat2 = isset($details_array['1849']) ? (float)$details_array['1849'] : (isset($details_array[1849]) ? (float)$details_array[1849] : 0.0);
        $lng2 = isset($details_array['1850']) ? (float)$details_array['1850'] : (isset($details_array[1850]) ? (float)$details_array[1850] : 0.0);
        if ($lat2 !== 0.0 || $lng2 !== 0.0) {
            update_post_meta($post_id, 'latitude', $lat2);
            update_post_meta($post_id, 'longitude', $lng2);
            $this->log('INFO - Coordinates enriched from WHISE details: lat=' . $lat2 . ' lng=' . $lng2 . ' (post ' . $post_id . ')');
        } else {
            $this->log('WARN - No coordinates found in WHISE details for estate ' . $estateId);
        }
    }

    /**
     * Importe ou met à jour un bien avec conversion des types
     */
    private function import_property($property) {
        if (empty($property['id'])) return;
        $whise_id = $property['id'];
        
        // Activer les logs détaillés uniquement pour certaines propriétés (pour debug)
        $debug_property_ids = ['7136195'];
        $is_debug_property = in_array((string)$whise_id, $debug_property_ids, true);
        
        // Debug amélioré pour le champ rooms
        if (isset($property['rooms'])) {
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - rooms value from API: ' . var_export($property['rooms'], true) . ' (type: ' . gettype($property['rooms']) . ')');
            }
        } else {
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - rooms field not found in API response');
            }
        }
        
        // Récupération des taxonomies stockées
        $whise_taxonomies = get_option('whise_taxonomies_full', []);
        if ($is_debug_property) {
            $this->log('DEBUG - Property ' . $whise_id . ' - available taxonomies: ' . json_encode(array_keys($whise_taxonomies)));
        }
        
        // Récupération du nom de l'état depuis la taxonomie
        $state_name = '';
        $state_id = $property['state']['id'] ?? '';
        if ($state_id && !empty($whise_taxonomies['states'])) {
            $state_name = $this->find_whise_taxonomy_name($state_id, $whise_taxonomies['states']);
        }
        // Si pas trouvé dans la taxonomie, utiliser les valeurs par défaut
        if (empty($state_name)) {
            $state_name = $this->get_default_state_name($state_id);
        }
        // Si toujours vide, essayer displayName/name de l'API
        if (empty($state_name)) {
            $state_name = $property['state']['displayName'] ?? $property['state']['name'] ?? '';
        }
        
        // Traitement des détails
        $details = [];
        if (!empty($property['details'])) {
            foreach ($property['details'] as $detail) {
                // Conserver la structure attendue par findDetailValueById (avec la clé 'id')
                $details[] = [
                    'id' => $detail['id'],
                    'value' => $detail['value'],
                    'label' => $detail['label'] ?? '',
                    'group' => $detail['group'] ?? '',
                    'type' => $detail['type'] ?? ''
                ];
            }
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - details count from list: ' . count($details));
            }
            // Vérifier la présence des IDs de coordonnées dans le payload liste
            $has1849 = false; $has1850 = false;
            foreach ($details as $dchk) {
                if ((string)$dchk['id'] === '1849') { $has1849 = true; }
                if ((string)$dchk['id'] === '1850') { $has1850 = true; }
            }
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - details contains 1849=' . ($has1849 ? 'yes' : 'no') . ' 1850=' . ($has1850 ? 'yes' : 'no'));
            }
        }

        // Traitement des images (URLs seulement pour le moment)
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
        // Extraction des descriptions multilingues
        $short_description = '';
        $sms_description = '';
        $long_description = '';
        
        // Dictionnaires pour stocker toutes les langues
        $short_descriptions = ['fr' => '', 'nl' => '', 'en' => ''];
        $sms_descriptions = ['fr' => '', 'nl' => '', 'en' => ''];
        $long_descriptions = ['fr' => '', 'nl' => '', 'en' => ''];
        
        // Structure multilingue pour compatibilité
        $descriptions_multilingual = [
            'shortDescription' => [],
            'sms' => [],
            'description' => []
        ];
        
        // Extraction des descriptions courtes
        if (!empty($property['shortDescription'])) {
            foreach ($property['shortDescription'] as $desc) {
                $lang_id = $desc['languageId'] ?? '';
                $content = $desc['content'] ?? '';
                
                // Mapping des codes de langue
                $lang_code = '';
                if (strpos($lang_id, 'fr') !== false) {
                    $lang_code = 'fr';
                } elseif (strpos($lang_id, 'nl') !== false) {
                    $lang_code = 'nl';
                } elseif (strpos($lang_id, 'en') !== false) {
                    $lang_code = 'en';
                }
                
                if ($lang_code && !empty($content)) {
                    $short_descriptions[$lang_code] = $content;
                    $descriptions_multilingual['shortDescription'][$lang_id] = $content;
                    
                    // Garder fr-BE comme valeur par défaut pour rétrocompatibilité
                    if ($lang_id === 'fr-BE') {
                        $short_description = $content;
                    }
                }
            }
        }
        
        // Extraction des descriptions SMS
        if (!empty($property['sms'])) {
            foreach ($property['sms'] as $sms) {
                $lang_id = $sms['languageId'] ?? '';
                $content = $sms['content'] ?? '';
                
                // Mapping des codes de langue
                $lang_code = '';
                if (strpos($lang_id, 'fr') !== false) {
                    $lang_code = 'fr';
                } elseif (strpos($lang_id, 'nl') !== false) {
                    $lang_code = 'nl';
                } elseif (strpos($lang_id, 'en') !== false) {
                    $lang_code = 'en';
                }
                
                if ($lang_code && !empty($content)) {
                    $sms_descriptions[$lang_code] = $content;
                    $descriptions_multilingual['sms'][$lang_id] = $content;
                    
                    // Garder fr-BE comme valeur par défaut pour rétrocompatibilité
                    if ($lang_id === 'fr-BE') {
                        $sms_description = $content;
                    }
                }
            }
        }
        
        // Extraction des descriptions longues
        if (!empty($property['description'])) {
            foreach ($property['description'] as $desc) {
                $lang_id = $desc['languageId'] ?? '';
                $content = $desc['content'] ?? '';
                
                // Mapping des codes de langue
                $lang_code = '';
                if (strpos($lang_id, 'fr') !== false) {
                    $lang_code = 'fr';
                } elseif (strpos($lang_id, 'nl') !== false) {
                    $lang_code = 'nl';
                } elseif (strpos($lang_id, 'en') !== false) {
                    $lang_code = 'en';
                }
                
                if ($lang_code && !empty($content)) {
                    $long_descriptions[$lang_code] = $content;
                    $descriptions_multilingual['description'][$lang_id] = $content;
                    
                    // Garder fr-BE comme valeur par défaut pour rétrocompatibilité
                    if ($lang_id === 'fr-BE') {
                        $long_description = $content;
                    }
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
            'state' => $state_name,
            'state_id' => $state_id,
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
            
            // Descriptions (FR par défaut pour rétrocompatibilité)
            'short_description' => $short_description,
            'sms_description' => $sms_description,
            
            // Descriptions multilingues - Short Description
            'short_description_fr' => $short_descriptions['fr'],
            'short_description_nl' => $short_descriptions['nl'],
            'short_description_en' => $short_descriptions['en'],
            
            // Descriptions multilingues - SMS
            'sms_description_fr' => $sms_descriptions['fr'],
            'sms_description_nl' => $sms_descriptions['nl'],
            'sms_description_en' => $sms_descriptions['en'],
            
            // Descriptions multilingues - Long Description
            'description_fr' => $long_descriptions['fr'],
            'description_nl' => $long_descriptions['nl'],
            'description_en' => $long_descriptions['en'],
            
            // Structure multilingue complète (pour compatibilité avec get_description_by_language)
            'descriptions_multilingual' => $descriptions_multilingual,
            
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
        if ($is_debug_property) {
            $this->log('DEBUG - Property ' . $whise_id . ' - mapped rooms value: ' . var_export($mapped_data['rooms'], true));
            // Debug mapping latitude/longitude
            $this->log('DEBUG - Property ' . $whise_id . ' - mapped latitude: ' . var_export($mapped_data['latitude'], true) . ' longitude: ' . var_export($mapped_data['longitude'], true));
        }
        
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

        // Traitement optimisé des images - éviter les re-téléchargements
        $gallery_attachment_ids = [];
        $existing_gallery = get_post_meta($post_id, '_whise_gallery_images', true);
        $skip_image_download = get_option('whise_skip_image_download', false);
        
        // Collecter tous les whise_image_id de l'API pour détecter les images supprimées
        $whise_image_ids_from_api = [];
        if (!empty($property['pictures'])) {
            foreach ($property['pictures'] as $picture) {
                if (!empty($picture['id'])) {
                    $whise_image_ids_from_api[] = (string)$picture['id'];
                }
            }
        }
        
        // IMPORTANT : Récupérer TOUTES les images attachées au post, pas seulement celles dans $existing_gallery
        // Cela permet de détecter les images qui ont été ajoutées manuellement ou qui ne sont pas dans la méta
        $all_attached_images = get_posts([
            'post_type' => 'attachment',
            'post_parent' => $post_id,
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        
        // Fusionner $existing_gallery et $all_attached_images pour avoir toutes les images à vérifier
        $all_images_to_check = array_unique(array_merge(
            is_array($existing_gallery) ? $existing_gallery : [],
            $all_attached_images
        ));
        
        // Supprimer les images qui ne sont plus dans l'API
        // Optimisation : charger toutes les métadonnées en une fois pour éviter les requêtes multiples
        if (!empty($all_images_to_check)) {
            $images_to_delete = [];
            $images_to_keep = [];
            
            // Précharger toutes les métadonnées des images en une seule requête
            $attachment_metadata = [];
            foreach ($all_images_to_check as $attachment_id) {
                $attachment_metadata[$attachment_id] = [
                    'whise_image_id' => get_post_meta($attachment_id, '_whise_image_id', true),
                    'original_url' => get_post_meta($attachment_id, '_whise_original_url', true)
                ];
            }
            
            // Créer un index des URLs de l'API pour recherche rapide
            $api_urls_index = [];
            if (!empty($property['pictures'])) {
                foreach ($property['pictures'] as $api_picture) {
                    $api_urls_index[$api_picture['urlXXL'] ?? ''] = $api_picture['id'];
                    $api_urls_index[$api_picture['urlLarge'] ?? ''] = $api_picture['id'];
                    $api_urls_index[$api_picture['urlSmall'] ?? ''] = $api_picture['id'];
                }
            }
            
            foreach ($all_images_to_check as $attachment_id) {
                // Vérifier que l'attachment existe toujours
                if (!get_post($attachment_id)) {
                    continue;
                }
                
                $meta = $attachment_metadata[$attachment_id];
                $attachment_whise_id = $meta['whise_image_id'];
                
                if (!empty($attachment_whise_id)) {
                    // Si l'image n'est plus dans l'API, la marquer pour suppression
                    if (!in_array((string)$attachment_whise_id, $whise_image_ids_from_api, true)) {
                        $images_to_delete[] = $attachment_id;
                    } else {
                        $images_to_keep[] = $attachment_id;
                    }
                } else {
                    // Image sans whise_image_id : vérifier si elle correspond à une image de l'API par URL
                    $attachment_url = $meta['original_url'];
                    $found_in_api = false;
                    
                    if (!empty($attachment_url) && isset($api_urls_index[$attachment_url])) {
                        $found_in_api = true;
                        $api_image_id = $api_urls_index[$attachment_url];
                        // Mettre à jour le whise_image_id manquant
                        update_post_meta($attachment_id, '_whise_image_id', $api_image_id);
                        $images_to_keep[] = $attachment_id;
                    }
                    
                    // Si pas trouvée dans l'API, la supprimer (image orpheline)
                    if (!$found_in_api) {
                        $images_to_delete[] = $attachment_id;
                    }
                }
            }
            
            $this->log('INFO - Property ' . $whise_id . ' - Images à garder: ' . count($images_to_keep) . ', Images à supprimer: ' . count($images_to_delete) . ' (total images vérifiées: ' . count($all_images_to_check) . ')');
            
            // Supprimer les images obsolètes
            if (!empty($images_to_delete)) {
                $this->log('INFO - Property ' . $whise_id . ' - Suppression de ' . count($images_to_delete) . ' image(s) supprimée(s) dans Whise');
                foreach ($images_to_delete as $attachment_id_to_delete) {
                    $file_path = get_attached_file($attachment_id_to_delete);
                    $deleted = wp_delete_attachment($attachment_id_to_delete, true);
                    if ($deleted) {
                        $this->log('INFO - Property ' . $whise_id . ' - Image supprimée: attachment ID ' . $attachment_id_to_delete);
                        // Supprimer aussi le fichier physique et ses variantes
                        if ($file_path && file_exists($file_path)) {
                            @unlink($file_path);
                            $path_info = pathinfo($file_path);
                            $filename_without_ext = $path_info['filename'];
                            for ($i = 1; $i <= 10; $i++) {
                                $variant_path = $path_info['dirname'] . '/' . $filename_without_ext . '-' . $i . '.' . $path_info['extension'];
                                if (file_exists($variant_path)) {
                                    @unlink($variant_path);
                                }
                            }
                        }
                    } else {
                        $this->log('ERROR - Property ' . $whise_id . ' - Échec suppression image: attachment ID ' . $attachment_id_to_delete);
                    }
                }
                
                // Mettre à jour la galerie existante pour retirer les images supprimées
                $existing_gallery = is_array($existing_gallery) ? array_values(array_diff($existing_gallery, $images_to_delete)) : [];
                if ($is_debug_property) {
                    $this->log('DEBUG - Property ' . $whise_id . ' - Gallery updated after deletions: ' . count($existing_gallery) . ' images remaining');
                }
            }
        }
        
        if (!empty($property['pictures']) && !$skip_image_download) {
            $this->log('INFO - Property ' . $whise_id . ' - Processing ' . count($property['pictures']) . ' images from API');
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - Existing gallery count: ' . (is_array($existing_gallery) ? count($existing_gallery) : 0));
            }
            
            // Optimisation : Précharger toutes les images existantes du bien en une seule requête
            $existing_attachments_cache = [];
            if (!empty($existing_gallery) && is_array($existing_gallery)) {
                // Précharger tous les posts en une fois pour éviter les requêtes multiples
                $attachment_posts = [];
                foreach ($existing_gallery as $att_id) {
                    $attachment_posts[$att_id] = get_post($att_id);
                }
                
                // Charger toutes les métadonnées des images existantes en une fois
                foreach ($existing_gallery as $att_id) {
                    $att_whise_id = get_post_meta($att_id, '_whise_image_id', true);
                    $att_url = get_post_meta($att_id, '_whise_original_url', true);
                    $att_order = get_post_meta($att_id, '_whise_image_order', true);
                    $att_post = $attachment_posts[$att_id] ?? null;
                    $att_parent = $att_post ? $att_post->post_parent : 0;
                    
                    if (!empty($att_whise_id)) {
                        $existing_attachments_cache['by_id'][(string)$att_whise_id] = [
                            'id' => $att_id,
                            'url' => $att_url,
                            'order' => $att_order,
                            'parent' => $att_parent
                        ];
                    }
                    if (!empty($att_url)) {
                        $existing_attachments_cache['by_url'][$att_url] = [
                            'id' => $att_id,
                            'whise_id' => $att_whise_id,
                            'order' => $att_order,
                            'parent' => $att_parent
                        ];
                    }
                }
                if ($is_debug_property) {
                    $this->log('DEBUG - Property ' . $whise_id . ' - Cache built: ' . count($existing_attachments_cache['by_id'] ?? []) . ' by ID, ' . count($existing_attachments_cache['by_url'] ?? []) . ' by URL');
                }
            }
            
            // Trier les images par ordre pour maintenir l'ordre de la galerie
            $pictures_sorted = $property['pictures'];
            usort($pictures_sorted, function($a, $b) {
                $order_a = $a['order'] ?? 999;
                $order_b = $b['order'] ?? 999;
                return $order_a <=> $order_b;
            });
            
            foreach ($pictures_sorted as $picture) {
                // Déterminer l'URL à utiliser selon la qualité configurée
                $preferred_quality = get_option('whise_image_quality', 'urlXXL');
                $image_url_to_check = '';
                
                // Utiliser la même logique que dans download_and_create_attachment
                if (!empty($picture[$preferred_quality])) {
                    $image_url_to_check = $picture[$preferred_quality];
                } else {
                    // Fallback : utiliser la meilleure qualité disponible
                    if (!empty($picture['urlXXL'])) {
                        $image_url_to_check = $picture['urlXXL'];
                    } elseif (!empty($picture['urlLarge'])) {
                        $image_url_to_check = $picture['urlLarge'];
                    } elseif (!empty($picture['urlSmall'])) {
                        $image_url_to_check = $picture['urlSmall'];
                    }
                }
                
                // ÉTAPE 1 : Détecter et supprimer les images de mauvaise qualité
                $whise_image_id = $picture['id'];
                $image_order = $picture['order'] ?? 0;
                
                if ($is_debug_property) {
                    $this->log('DEBUG - Property ' . $whise_id . ' - Processing image whise_id=' . $whise_image_id . ', order=' . $image_order . ', URL=' . substr($image_url_to_check, 0, 80) . '...');
                }
                
                // Chercher les images existantes par whise_image_id
                $all_quality_attachments = get_posts([
                    'post_type' => 'attachment',
                    'meta_query' => [
                        [
                            'key' => '_whise_image_id',
                            'value' => $whise_image_id,
                            'compare' => '='
                        ]
                    ],
                    'numberposts' => -1
                ]);
                
                // Si pas trouvé par whise_image_id, chercher par pattern de nom de fichier
                if (empty($all_quality_attachments)) {
                    // Utiliser le même format avec zéros pour la recherche
                    $image_order_padded = str_pad((string)$image_order, 3, '0', STR_PAD_LEFT);
                    $filename_pattern = 'whise-' . $whise_id . '-' . $image_order_padded . '-' . $whise_image_id;
                    global $wpdb;
                    $attachment_ids = $wpdb->get_col($wpdb->prepare(
                        "SELECT p.ID FROM {$wpdb->posts} p 
                        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                        WHERE p.post_type = 'attachment' 
                        AND pm.meta_key = '_wp_attached_file'
                        AND pm.meta_value LIKE %s",
                        '%' . $wpdb->esc_like($filename_pattern) . '%'
                    ));
                    
                    if (!empty($attachment_ids)) {
                        foreach ($attachment_ids as $att_id) {
                            $all_quality_attachments[] = get_post($att_id);
                        }
                        if ($is_debug_property) {
                            $this->log('DEBUG - Property ' . $whise_id . ' - Found ' . count($attachment_ids) . ' images by filename pattern for image ' . $whise_image_id);
                        }
                    }
                }
                
                // Hiérarchie de qualité
                $quality_hierarchy = ['urlXXL', 'urlLarge', 'urlSmall'];
                $preferred_quality_level = array_search($preferred_quality, $quality_hierarchy);
                $force_redownload = false;
                
                // Vérifier et supprimer les images de qualité inférieure
                foreach ($all_quality_attachments as $old_attachment) {
                    if (!$old_attachment || !isset($old_attachment->ID)) continue;
                    
                    $old_url = get_post_meta($old_attachment->ID, '_whise_original_url', true);
                    
                    // Détecter la qualité de l'ancienne image
                    $old_quality_level = -1;
                    if (strpos($old_url, '/1600/') !== false || strpos($old_url, 'urlXXL') !== false) {
                        $old_quality_level = 0; // urlXXL
                    } elseif (strpos($old_url, '/640/') !== false || strpos($old_url, 'urlLarge') !== false) {
                        $old_quality_level = 1; // urlLarge
                    } elseif (strpos($old_url, '/200/') !== false || strpos($old_url, 'urlSmall') !== false) {
                        $old_quality_level = 2; // urlSmall
                    }
                    
                        if ($is_debug_property) {
                            $this->log('DEBUG - Property ' . $whise_id . ' - Checking attachment ID ' . $old_attachment->ID . ' - old quality level: ' . $old_quality_level . ', preferred: ' . $preferred_quality_level);
                        }
                    
                    // Si l'ancienne image est de qualité inférieure, la supprimer
                    if ($old_quality_level > $preferred_quality_level && $old_quality_level !== -1) {
                        if ($is_debug_property) {
                            $this->log('DEBUG - Property ' . $whise_id . ' - Deleting lower quality image (level ' . $old_quality_level . ' vs ' . $preferred_quality_level . '): attachment ID ' . $old_attachment->ID . ' URL: ' . $old_url);
                        }
                        
                        // Récupérer le chemin du fichier AVANT de supprimer l'attachment
                        $file_path = get_attached_file($old_attachment->ID);
                        
                        // Supprimer l'attachment de la DB
                        wp_delete_attachment($old_attachment->ID, true);
                        
                        // Supprimer le fichier physique et ses variantes
                        if ($file_path && file_exists($file_path)) {
                            @unlink($file_path);
                            if ($is_debug_property) {
                                $this->log('DEBUG - Property ' . $whise_id . ' - Deleted physical file: ' . basename($file_path));
                            }
                            
                            // Supprimer les variantes (-1, -2, etc.)
                            $path_info = pathinfo($file_path);
                            $filename_without_ext = $path_info['filename'];
                            for ($i = 1; $i <= 10; $i++) {
                                $variant_path = $path_info['dirname'] . '/' . $filename_without_ext . '-' . $i . '.' . $path_info['extension'];
                                if (file_exists($variant_path)) {
                                    @unlink($variant_path);
                                    if ($is_debug_property) {
                                        $this->log('DEBUG - Property ' . $whise_id . ' - Deleted variant file: ' . basename($variant_path));
                                    }
                                }
                            }
                        }
                        
                        $force_redownload = true;
                    }
                }
                
                // ÉTAPE 2 : Vérifier si l'image existe déjà (utiliser le cache au lieu de requêtes DB)
                if (!$force_redownload) {
                    $existing_att_data = null;
                    
                    // Chercher d'abord par whise_image_id dans le cache
                    if (isset($existing_attachments_cache['by_id'][(string)$whise_image_id])) {
                        $existing_att_data = $existing_attachments_cache['by_id'][(string)$whise_image_id];
                        if ($is_debug_property) {
                            $this->log('DEBUG - Property ' . $whise_id . ' - Image ' . $whise_image_id . ' found in cache by ID');
                        }
                    } elseif (isset($existing_attachments_cache['by_url'][$image_url_to_check])) {
                        // Si pas trouvé par ID, chercher par URL dans le cache
                        $existing_att_data = $existing_attachments_cache['by_url'][$image_url_to_check];
                        if ($is_debug_property) {
                            $this->log('DEBUG - Property ' . $whise_id . ' - Image ' . $whise_image_id . ' found in cache by URL');
                        }
                    } else {
                        if ($is_debug_property) {
                            $this->log('DEBUG - Property ' . $whise_id . ' - Image ' . $whise_image_id . ' NOT found in cache, will download');
                        }
                    }
                    
                    if ($existing_att_data) {
                        $existing_att_id = $existing_att_data['id'];
                        $existing_url = $existing_att_data['url'] ?? '';
                        $existing_order = $existing_att_data['order'] ?? '';
                        $existing_parent = $existing_att_data['parent'] ?? 0;
                        
                        // Vérifier si l'URL a changé ou si l'ordre a changé (mise à jour nécessaire)
                        $needs_update = false;
                        $update_reason = [];
                        
                        if ($existing_url !== $image_url_to_check) {
                            $needs_update = true;
                            $update_reason[] = 'URL changée (' . substr($existing_url, 0, 50) . ' vs ' . substr($image_url_to_check, 0, 50) . ')';
                        }
                        
                        if ((string)$existing_order !== (string)$image_order) {
                            $needs_update = true;
                            $update_reason[] = 'ordre changé (' . $existing_order . ' -> ' . $image_order . ')';
                        }
                        
                        // Vérifier si l'attachment est lié au bon post
                        if ($existing_parent != $post_id) {
                            $needs_update = true;
                            $update_reason[] = 'parent incorrect (' . $existing_parent . ' vs ' . $post_id . ')';
                        }
                        
                        if ($needs_update) {
                            $this->log('INFO - Property ' . $whise_id . ' - Image ' . $whise_image_id . ' needs update: ' . implode(', ', $update_reason));
                            // Supprimer l'ancienne pour télécharger la nouvelle
                            wp_delete_attachment($existing_att_id, true);
                            $force_redownload = true;
                        } else {
                            if ($is_debug_property) {
                                $this->log('DEBUG - Property ' . $whise_id . ' - Image ' . $whise_image_id . ' is up to date, adding to gallery');
                            }
                            // L'image existe déjà et est à jour, l'ajouter à la galerie
                            // Mettre à jour les métadonnées si nécessaire (sans requête DB supplémentaire)
                            if ($existing_parent != $post_id) {
                                wp_update_post([
                                    'ID' => $existing_att_id,
                                    'post_parent' => $post_id
                                ]);
                            }
                            // Mettre à jour whise_image_id si manquant (pour images trouvées par URL)
                            if (empty($existing_att_data['whise_id'])) {
                                update_post_meta($existing_att_id, '_whise_image_id', $whise_image_id);
                            }
                            // Mettre à jour l'ordre si différent (même si on ne force pas le re-téléchargement)
                            if ((string)$existing_order !== (string)$image_order) {
                                update_post_meta($existing_att_id, '_whise_image_order', $image_order);
                                // Mettre à jour aussi le menu_order pour WordPress/Elementor
                                wp_update_post([
                                    'ID' => $existing_att_id,
                                    'menu_order' => (int)$image_order
                                ]);
                            }
                            $gallery_attachment_ids[] = $existing_att_id;
                            continue; // Passer à l'image suivante
                        }
                    }
                }
                
                // ÉTAPE 3 : Télécharger l'image
                if ($is_debug_property) {
                    $this->log('DEBUG - Property ' . $whise_id . ' - Downloading image ' . $picture['id'] . ' in quality ' . $preferred_quality);
                }
                $attachment_id = $this->download_and_create_attachment($picture, $whise_id, $post_id);
                if ($attachment_id) {
                    $gallery_attachment_ids[] = $attachment_id;
                }
            }
            
            // Nettoyer les IDs invalides (attachments qui n'existent plus)
            $gallery_attachment_ids = array_filter($gallery_attachment_ids, function($att_id) {
                return get_post($att_id) !== null;
            });
            
            // Trier la galerie par ordre (basé sur _whise_image_order)
            // Créer un tableau temporaire avec les ordres pour le tri
            $gallery_with_orders = [];
            foreach ($gallery_attachment_ids as $attachment_id) {
                $order = get_post_meta($attachment_id, '_whise_image_order', true);
                $order = $order !== '' ? (int)$order : 999;
                $gallery_with_orders[] = ['id' => $attachment_id, 'order' => $order];
            }
            usort($gallery_with_orders, function($a, $b) {
                return $a['order'] <=> $b['order'];
            });
            // Réextraire les IDs triés
            $gallery_attachment_ids = array_values(array_column($gallery_with_orders, 'id'));
            
            // Mettre à jour le menu_order de tous les attachments pour WordPress/Elementor
            foreach ($gallery_with_orders as $index => $item) {
                $current_menu_order = get_post_field('menu_order', $item['id']);
                if ($current_menu_order != $item['order']) {
                    wp_update_post([
                        'ID' => $item['id'],
                        'menu_order' => (int)$item['order']
                    ]);
                }
            }
            
            // IMPORTANT : S'assurer que la galerie finale ne contient QUE les images de l'API
            // Vérifier que toutes les images dans la galerie ont un whise_image_id qui est dans l'API
            $final_gallery_clean = [];
            foreach ($gallery_attachment_ids as $att_id) {
                $att_whise_id = get_post_meta($att_id, '_whise_image_id', true);
                if (!empty($att_whise_id) && in_array((string)$att_whise_id, $whise_image_ids_from_api, true)) {
                    $final_gallery_clean[] = $att_id;
                } elseif (empty($att_whise_id)) {
                    // Image sans whise_image_id : vérifier par URL si elle est dans l'API
                    $att_url = get_post_meta($att_id, '_whise_original_url', true);
                    $found_in_api = false;
                    if (!empty($att_url) && !empty($property['pictures'])) {
                        foreach ($property['pictures'] as $api_pic) {
                            if ($att_url === ($api_pic['urlXXL'] ?? '') || 
                                $att_url === ($api_pic['urlLarge'] ?? '') || 
                                $att_url === ($api_pic['urlSmall'] ?? '')) {
                                $found_in_api = true;
                                // Mettre à jour le whise_image_id
                                update_post_meta($att_id, '_whise_image_id', $api_pic['id']);
                                break;
                            }
                        }
                    }
                    if ($found_in_api) {
                        $final_gallery_clean[] = $att_id;
                    } else {
                        $this->log('WARN - Property ' . $whise_id . ' - Image ' . $att_id . ' sans whise_image_id et non trouvée dans API, retirée de la galerie');
                    }
                } else {
                    $this->log('WARN - Property ' . $whise_id . ' - Image ' . $att_id . ' (whise_id=' . $att_whise_id . ') n\'est plus dans l\'API, retirée de la galerie');
                }
            }
            
            // Stocker les IDs des attachments de la galerie (même si vide, pour supprimer les anciennes)
            $this->log('INFO - Property ' . $whise_id . ' - Final gallery: ' . count($final_gallery_clean) . ' images (was: ' . (is_array($existing_gallery) ? count($existing_gallery) : 0) . ', before cleanup: ' . count($gallery_attachment_ids) . ')');
            update_post_meta($post_id, '_whise_gallery_images', $final_gallery_clean);
            $gallery_attachment_ids = $final_gallery_clean;
            
            if (!empty($gallery_attachment_ids)) {
                if ($is_debug_property) {
                    $this->log('DEBUG - Property ' . $whise_id . ' - Gallery updated with ' . count($gallery_attachment_ids) . ' image attachments');
                }
                
                // Définir la première image comme featured image
                if (!has_post_thumbnail($post_id)) {
                    set_post_thumbnail($post_id, $gallery_attachment_ids[0]);
                    if ($is_debug_property) {
                        $this->log('DEBUG - Property ' . $whise_id . ' - Set featured image: attachment ID ' . $gallery_attachment_ids[0]);
                    }
                }
            } else {
                $this->log('INFO - Property ' . $whise_id . ' - No images in gallery (all removed or none in API)');
                // Supprimer la featured image si plus d'images
                delete_post_thumbnail($post_id);
            }
        } elseif ($skip_image_download) {
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - Image download disabled, keeping existing gallery');
            }
            $gallery_attachment_ids = $existing_gallery ?: [];
        } else {
            // Cas où property['pictures'] est vide : supprimer toutes les images
            if (empty($property['pictures']) && !empty($existing_gallery) && is_array($existing_gallery)) {
                $this->log('INFO - Property ' . $whise_id . ' - No images in API, removing all existing images');
                foreach ($existing_gallery as $attachment_id) {
                    wp_delete_attachment($attachment_id, true);
                }
                update_post_meta($post_id, '_whise_gallery_images', []);
                delete_post_thumbnail($post_id);
            }
        }

        // Compléter avec le représentant si disponible
        // 1) Depuis la liste: champ representatives
        if (!empty($property['representatives']) && is_array($property['representatives'])) {
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - representatives from list: ' . count($property['representatives']));
            }
            $rep = $this->extract_representative_info([ 'employees' => $property['representatives'] ]);
        } else {
            $rep = $this->extract_representative_info($property);
        }
        if (empty($rep['name']) && empty($rep['email']) && empty($rep['phone']) && empty($rep['mobile'])) {
            // Si rien dans la liste, tenter les endpoints de détails/employés
            $rep = $this->fetch_estate_representative($whise_id);
        }
        if (!empty($rep)) {
            $mapped_data['representative_id'] = $rep['id'];
            $mapped_data['representative_name'] = $rep['name'];
            $mapped_data['representative_email'] = $rep['email'];
            $mapped_data['representative_phone'] = $rep['phone'];
            $mapped_data['representative_mobile'] = $rep['mobile'];
            $mapped_data['representative_picture'] = $rep['picture'];
            $mapped_data['representative_function'] = $rep['function'] ?? '';
        }

        // Mise à jour optimisée des meta avec conversion des types
        $meta_to_update = [];
        foreach ($mapped_data as $key => $value) {
            $type = $this->get_field_type($key);
            $converted_value = $this->convert_value($value, $type);
            $meta_to_update[$key] = $converted_value;
            
            // Debug spécifique pour le champ rooms
            if ($key === 'rooms' && $is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - rooms conversion: original=' . var_export($value, true) . ', type=' . $type . ', converted=' . var_export($converted_value, true));
            }
        }
        
        // Mise à jour optimisée des métadonnées (une seule transaction DB)
        // Utiliser update_post_meta en batch pour réduire les requêtes DB
        if (!empty($meta_to_update)) {
            // Désactiver temporairement l'autoload pour les meta non critiques
            foreach ($meta_to_update as $key => $value) {
                // Utiliser update_post_meta directement (WordPress optimise déjà en interne)
                update_post_meta($post_id, $key, $value);
            }
        }
        
        // Traitement spécial pour les coordonnées
        if (isset($meta_to_update['latitude']) || isset($meta_to_update['longitude'])) {
            $saved_lat = $meta_to_update['latitude'] ?? get_post_meta($post_id, 'latitude', true);
            $saved_lng = $meta_to_update['longitude'] ?? get_post_meta($post_id, 'longitude', true);
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - saved lat/lng in DB: ' . var_export($saved_lat, true) . '/' . var_export($saved_lng, true));
            }
            
            // Champs dédiés supplémentaires pour usage front/ACF
            update_post_meta($post_id, 'geo_lat', (float)$saved_lat);
            update_post_meta($post_id, 'geo_lng', (float)$saved_lng);
            
            // Champ compatible ACF Google Map
            $address = $meta_to_update['address'] ?? get_post_meta($post_id, 'address', true);
            $zip = $meta_to_update['zip'] ?? get_post_meta($post_id, 'zip', true);
            $city = $meta_to_update['city'] ?? get_post_meta($post_id, 'city', true);
            $acf_location = [
                'address' => $address . ', ' . $zip . ' ' . $city,
                'lat' => (float)$saved_lat,
                'lng' => (float)$saved_lng,
            ];
            update_post_meta($post_id, 'immo_location', $acf_location);
        }

        // Mise à jour des taxonomies - PRIORITÉ AUX SOUS-CATÉGORIES
        $assigned_categories = [];
        
        // Debug : Logger toutes les données de catégories reçues
        if ($is_debug_property) {
            $this->log('DEBUG - Property ' . $whise_id . ' - RAW category: ' . json_encode($property['category'] ?? null));
            $this->log('DEBUG - Property ' . $whise_id . ' - RAW subCategories: ' . json_encode($property['subCategories'] ?? null));  
            $this->log('DEBUG - Property ' . $whise_id . ' - RAW subCategory: ' . json_encode($property['subCategory'] ?? null));
        }
        
        // 1. D'abord traiter les sous-catégories (prioritaires)
        // Gérer subCategories (pluriel - array)
        if (!empty($property['subCategories'])) {
            foreach ($property['subCategories'] as $subCategory) {
                $sub_name = $subCategory['displayName'] ?? $subCategory['name'] ?? '';
                if ($sub_name) {
                    $assigned_categories[] = $sub_name;
                    if ($is_debug_property) {
                        $this->log('DEBUG - Property ' . $whise_id . ' - subcategory from subCategories: ' . $sub_name);
                    }
                }
            }
        }
        
        // Gérer subCategory (singulier - objet)
        if (!empty($property['subCategory']) && !empty($property['subCategory']['id'])) {
            $subcategory_id = $property['subCategory']['id'];
            $sub_name = $property['subCategory']['displayName'] ?? $property['subCategory']['name'] ?? $this->get_subcategory_name($subcategory_id);
            if ($sub_name && !in_array($sub_name, $assigned_categories)) {
                $assigned_categories[] = $sub_name;
                if ($is_debug_property) {
                    $this->log('DEBUG - Property ' . $whise_id . ' - subcategory from subCategory: ' . $sub_name . ' (ID: ' . $subcategory_id . ')');
                }
            } else {
                if ($is_debug_property) {
                    $this->log('DEBUG - Property ' . $whise_id . ' - subCategory ID ' . $subcategory_id . ' not found in mapping');
                }
            }
        }
        
        // 2. Si pas de sous-catégorie, utiliser la catégorie principale
        if (empty($assigned_categories)) {
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
                if ($category_name && $is_debug_property) {
                    $this->log('DEBUG - Property ' . $whise_id . ' - using default category name: ' . $category_name . ' for ID: ' . $category_id);
                }
            }
            
            if ($category_name) {
                $assigned_categories[] = $category_name;
                if ($is_debug_property) {
                    $this->log('DEBUG - Property ' . $whise_id . ' - main category used: ' . $category_name);
                }
            }
        }
        
        // 3. Assigner les catégories finales
        if (!empty($assigned_categories)) {
            // Log des données complètes pour debug
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - category data: ' . json_encode($property['category'] ?? []));
                $this->log('DEBUG - Property ' . $whise_id . ' - subCategories data: ' . json_encode($property['subCategories'] ?? []));
                $this->log('DEBUG - Property ' . $whise_id . ' - final assigned_categories: ' . json_encode($assigned_categories));
            }
            
            wp_set_object_terms($post_id, $assigned_categories, 'property_type', false);
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - assigned to property_type: ' . implode(', ', $assigned_categories));
            }
            
            // Vérification après assignation
            $check_terms = wp_get_object_terms($post_id, 'property_type', ['fields' => 'names']);
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - verified terms after assignment: ' . implode(', ', $check_terms));
            }
        } else {
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - NO category found');
            }
        }
        
        // ANCIEN CODE : purpose assigné séparément - maintenant géré par whise_update_simple_transaction_status()
        // $purpose = $property['purpose'] ?? [];
        // $purpose_name = ...
        // wp_set_object_terms($post_id, $purpose_name, 'transaction_type', false);
        // Note: Les statuts de transaction sont maintenant assignés par whise_update_simple_transaction_status() plus bas
        
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
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - assigned to property_status: ' . $status_name);
            }
        }
        
        // Normalisation taxonomique: Studio si 0 chambre ET pas déjà de sous-catégorie
        $rooms_saved = (int)get_post_meta($post_id, 'rooms', true);
        $existing_terms = wp_get_object_terms($post_id, 'property_type', ['fields' => 'names']);
        if ($rooms_saved === 0 && empty($existing_terms)) {
            // Seulement si aucun type n'a été assigné par les sous-catégories
            wp_set_object_terms($post_id, 'Studio', 'property_type', false); // false = remplace
            $this->log('INFO - Property ' . $whise_id . ' - assigned Studio as fallback (rooms=0, no subcategory)');
        } elseif ($rooms_saved === 0 && !empty($existing_terms)) {
            $this->log('INFO - Property ' . $whise_id . ' - rooms=0 but subcategory already assigned: ' . implode(', ', $existing_terms));
        }

        // Enrichir coordonnées si manquantes: récupérer les détails WHISE 1849/1850
        $this->enrich_coordinates_from_whise($post_id, $whise_id);

        // Mettre à jour le statut de transaction simplifié
        $purpose_status_id = $mapped_data['purpose_status_id'] ?? 0;
        $purpose_id = $mapped_data['transaction_type_id'] ?? 1;
        if ($purpose_status_id && function_exists('whise_update_simple_transaction_status')) {
            $simple_status = whise_update_simple_transaction_status($post_id, $purpose_status_id, $purpose_id);
            $this->log('INFO - Property ' . $whise_id . ' - transaction_status set to: ' . $simple_status . ' (purpose_status_id=' . $purpose_status_id . ', purpose_id=' . $purpose_id . ')');
        }

        // Après enrichissement, projeter systématiquement vers les champs dédiés
        $final_lat = get_post_meta($post_id, 'latitude', true);
        $final_lng = get_post_meta($post_id, 'longitude', true);
        if ($final_lat !== '' && $final_lng !== '') {
            update_post_meta($post_id, 'geo_lat', (float)$final_lat);
            update_post_meta($post_id, 'geo_lng', (float)$final_lng);
            $acf_location = [
                'address' => get_post_meta($post_id, 'address', true) . ', ' . get_post_meta($post_id, 'zip', true) . ' ' . get_post_meta($post_id, 'city', true),
                'lat' => (float)$final_lat,
                'lng' => (float)$final_lng,
            ];
            update_post_meta($post_id, 'immo_location', $acf_location);
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - projected geo_lat/geo_lng: ' . var_export($final_lat, true) . '/' . var_export($final_lng, true));
            }
        } else {
            $this->log('WARN - Property ' . $whise_id . ' - missing lat/lng after enrichment; geo_* not updated');
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

    /**
     * Retourne un nom d'état du bâtiment par défaut basé sur l'ID Whise
     */
    private function get_default_state_name($state_id) {
        $default_states = [
            '1' => 'Excellent état',
            '2' => 'Bon état',
            '3' => 'À rafraîchir',
            '4' => 'À rénover',
            '5' => 'Neuf',
            '6' => 'Comme neuf'
        ];
        
        return $default_states[(string)$state_id] ?? null;
    }

    /**
     * Retourne un nom de sous-catégorie basé sur l'ID Whise
     */
    private function get_subcategory_name($subcategory_id) {
        $subcategories = [
            '17' => 'Appartement',
            '18' => 'Duplex', 
            '19' => 'Penthouse',
            '20' => 'Flat',
            '21' => 'Studio',
            '22' => 'Triplex',
            '23' => 'Loft',
            '25' => 'Rez-de-chaussée',
            '62' => 'Autres',
            '69' => 'Appartements à usage multiple',
            '72' => 'App. dans maison de caractère',
            '73' => 'Rez-de-ch. avec jardin',
            '74' => 'Appartement avec jardin',
            '93' => 'Chambre étudiant',
            '94' => 'App. sous toit',
            '95' => 'Service flats',
            '97' => 'Appartement vente sur plan',
            '156' => 'Appartement exceptionnel',
            '160' => 'Chambre',
            '165' => 'Résidences-services'
        ];
        
        return $subcategories[(string)$subcategory_id] ?? null;
    }

    /**
     * Télécharge une image depuis Whise et crée un attachment WordPress
     */
    private function download_and_create_attachment($picture, $whise_id, $post_id) {
        if (empty($picture['id'])) {
            return false;
        }

        // Activer les logs détaillés uniquement pour certaines propriétés (pour debug)
        $debug_property_ids = ['7136195'];
        $is_debug_property = in_array((string)$whise_id, $debug_property_ids, true);

        // Utiliser la qualité configurée ou la meilleure disponible
        $preferred_quality = get_option('whise_image_quality', 'urlXXL'); // Par défaut : haute qualité
        $image_url = '';
        
        // Essayer d'abord la qualité préférée
        if (!empty($picture[$preferred_quality])) {
            $image_url = $picture[$preferred_quality];
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - Using ' . $preferred_quality . ' (preferred quality) for image ' . $picture['id']);
            }
        } else {
            // Fallback : utiliser la meilleure qualité disponible (urlXXL > urlLarge > urlSmall)
            if (!empty($picture['urlXXL'])) {
                $image_url = $picture['urlXXL'];
                if ($is_debug_property) {
                    $this->log('DEBUG - Property ' . $whise_id . ' - Using urlXXL (high quality fallback) for image ' . $picture['id']);
                }
            } elseif (!empty($picture['urlLarge'])) {
                $image_url = $picture['urlLarge'];
                if ($is_debug_property) {
                    $this->log('DEBUG - Property ' . $whise_id . ' - Using urlLarge (medium quality fallback) for image ' . $picture['id']);
                }
            } elseif (!empty($picture['urlSmall'])) {
                $image_url = $picture['urlSmall'];
                if ($is_debug_property) {
                    $this->log('DEBUG - Property ' . $whise_id . ' - Using urlSmall (low quality fallback) for image ' . $picture['id']);
                }
            }
        }
        
        if (empty($image_url)) {
            $this->log('ERROR - Property ' . $whise_id . ' - No image URL available for image ' . $picture['id']);
            return false;
        }
        $whise_image_id = $picture['id'];
        $image_order = $picture['order'] ?? 0;

        // Note: La vérification d'existence est déjà faite dans import_property()
        // Cette fonction est appelée uniquement si l'image doit être téléchargée

        // Télécharger l'image
        if ($is_debug_property) {
            $this->log('DEBUG - Property ' . $whise_id . ' - Downloading image: ' . $image_url);
        }
        
        $response = wp_remote_get($image_url, [
            'timeout' => 120, // Augmenté à 2 minutes pour les images
            'headers' => [
                'User-Agent' => 'WordPress/Whise-Integration'
            ]
        ]);

        if (is_wp_error($response)) {
            $this->log('ERROR - Property ' . $whise_id . ' - Failed to download image: ' . $image_url . ' - ' . $response->get_error_message());
            return false;
        }

        $image_data = wp_remote_retrieve_body($response);
        if (empty($image_data)) {
            $this->log('ERROR - Property ' . $whise_id . ' - Empty image data for: ' . $image_url);
            return false;
        }

        // Déterminer l'extension du fichier
        $file_extension = 'jpg'; // Par défaut
        $content_type = wp_remote_retrieve_header($response, 'content-type');
        if ($content_type) {
            switch ($content_type) {
                case 'image/png':
                    $file_extension = 'png';
                    break;
                case 'image/gif':
                    $file_extension = 'gif';
                    break;
                case 'image/webp':
                    $file_extension = 'webp';
                    break;
            }
        }

        // Créer un nom de fichier unique avec zéros pour le tri lexical (01, 02, 03, etc.)
        $image_order_padded = str_pad((string)$image_order, 3, '0', STR_PAD_LEFT);
        $filename = 'whise-' . $whise_id . '-' . $image_order_padded . '-' . $whise_image_id . '.' . $file_extension;
        
        // Vérifier si un fichier avec ce nom (ou ses variantes) existe déjà
        $upload_dir = wp_upload_dir();
        $target_path = $upload_dir['path'] . '/' . $filename;
        
        // Supprimer le fichier existant et ses variantes (-1, -2, etc.) pour éviter les doublons
        if (file_exists($target_path)) {
            @unlink($target_path);
            if ($is_debug_property) {
                $this->log('DEBUG - Property ' . $whise_id . ' - Deleted existing file to avoid duplicates: ' . $filename);
            }
        }
        
        // Supprimer aussi les variantes avec suffixes (-1, -2, -3, etc.)
        $image_order_padded = str_pad((string)$image_order, 3, '0', STR_PAD_LEFT);
        $filename_without_ext = 'whise-' . $whise_id . '-' . $image_order_padded . '-' . $whise_image_id;
        for ($i = 1; $i <= 10; $i++) {
            $variant_path = $upload_dir['path'] . '/' . $filename_without_ext . '-' . $i . '.' . $file_extension;
            if (file_exists($variant_path)) {
                @unlink($variant_path);
                if ($is_debug_property) {
                    $this->log('DEBUG - Property ' . $whise_id . ' - Deleted duplicate variant: ' . basename($variant_path));
                }
            }
        }

        // Utiliser wp_upload_bits pour créer le fichier
        $upload = wp_upload_bits($filename, null, $image_data);
        
        if ($upload['error']) {
            $this->log('ERROR - Property ' . $whise_id . ' - Upload failed: ' . $upload['error']);
            return false;
        }

        // Créer l'attachment
        $attachment = [
            'post_mime_type' => $content_type ?: 'image/jpeg',
            'post_title' => 'Image propriété ' . $whise_id . ' - ' . $image_order,
            'post_content' => '',
            'post_status' => 'inherit',
            'post_parent' => $post_id,
            'menu_order' => (int)$image_order // Définir l'ordre pour WordPress/Elementor
        ];

        $attachment_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
        
        if (is_wp_error($attachment_id)) {
            $this->log('ERROR - Property ' . $whise_id . ' - Failed to create attachment: ' . $attachment_id->get_error_message());
            return false;
        }

        // Générer les métadonnées de l'image
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);

        // Ajouter les métadonnées spécifiques Whise
        update_post_meta($attachment_id, '_whise_original_url', $image_url);
        update_post_meta($attachment_id, '_whise_image_id', $whise_image_id);
        update_post_meta($attachment_id, '_whise_image_order', $image_order);
        update_post_meta($attachment_id, '_wp_attachment_image_alt', 'Image propriété ' . $whise_id);

        if ($is_debug_property) {
            $this->log('DEBUG - Property ' . $whise_id . ' - Created attachment ID ' . $attachment_id . ' for image ' . $whise_image_id);
        }
        
        return $attachment_id;
    }

    /**
     * Supprime les biens qui ne sont plus présents dans l'API Whise
     */
    private function cleanup_obsolete_properties($whise_ids_from_api) {
        if (empty($whise_ids_from_api)) {
            $this->log('WARN - Aucun ID Whise reçu de l\'API, impossible de nettoyer les biens obsolètes');
            return;
        }

        $this->log('DEBUG - Nettoyage des biens obsolètes. IDs présents dans l\'API: ' . count($whise_ids_from_api));
        
        // Récupérer tous les biens WordPress avec leur whise_id
        $all_properties = get_posts([
            'post_type' => 'property',
            'post_status' => 'any',
            'numberposts' => -1,
            'meta_query' => [
                [
                    'key' => 'whise_id',
                    'compare' => 'EXISTS'
                ]
            ]
        ]);

        $deleted_count = 0;
        $kept_count = 0;

        foreach ($all_properties as $property) {
            $whise_id = get_post_meta($property->ID, 'whise_id', true);
            
            if (empty($whise_id)) {
                $this->log('WARN - Property ' . $property->ID . ' n\'a pas de whise_id, ignoré');
                continue;
            }

            // Vérifier si le bien a un statut à exclure et le supprimer explicitement
            $purpose_status_id = get_post_meta($property->ID, 'purpose_status_id', true);
            $excluded_status_ids = $this->get_excluded_status_ids();
            
            if (isset($excluded_status_ids[$purpose_status_id])) {
                $status_name = $excluded_status_ids[$purpose_status_id];
                $this->log('INFO - Suppression du bien avec statut exclu "' . $status_name . '": ' . $property->post_title . ' (ID: ' . $property->ID . ', Whise ID: ' . $whise_id . ')');
                
                // Supprimer définitivement le bien et ses métadonnées
                $deleted = wp_delete_post($property->ID, true);
                
                if ($deleted) {
                    $deleted_count++;
                    $this->log('SUCCESS - Bien avec statut exclu "' . $status_name . '" supprimé: ' . $property->post_title . ' (ID: ' . $property->ID . ')');
                } else {
                    $this->log('ERROR - Échec de suppression du bien avec statut exclu "' . $status_name . '": ' . $property->post_title . ' (ID: ' . $property->ID . ')');
                }
                continue; // Passer au bien suivant
            }

            // Vérifier si ce whise_id est encore présent dans l'API
            if (!in_array((string)$whise_id, $whise_ids_from_api, true)) {
                // Le bien n'est plus dans l'API, le supprimer
                $this->log('INFO - Suppression du bien obsolète: ' . $property->post_title . ' (ID: ' . $property->ID . ', Whise ID: ' . $whise_id . ')');
                
                // Supprimer définitivement le bien et ses métadonnées
                $deleted = wp_delete_post($property->ID, true);
                
                if ($deleted) {
                    $deleted_count++;
                    $this->log('SUCCESS - Bien supprimé: ' . $property->post_title . ' (ID: ' . $property->ID . ')');
                } else {
                    $this->log('ERROR - Échec de suppression du bien: ' . $property->post_title . ' (ID: ' . $property->ID . ')');
                }
            } else {
                $kept_count++;
            }
        }

        $this->log('INFO - Nettoyage terminé. Biens supprimés: ' . $deleted_count . ', Biens conservés: ' . $kept_count);
        
        // Mettre à jour le timestamp de la dernière synchronisation
        update_option('whise_last_sync', current_time('mysql'));
    }
}
