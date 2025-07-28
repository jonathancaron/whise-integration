<?php
if (!defined('ABSPATH')) exit;

class Whise_Sync_Manager {
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
     * Importe ou met à jour un bien (à compléter selon mapping)
     */
    private function import_property($property) {
        if (empty($property['id'])) return;
        $whise_id = $property['id'];
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
            'post_title' => $property['title'] ?? ($property['reference'] ?? ''),
            'post_content' => $property['description'] ?? '',
            'post_status' => 'publish',
        ];
        if ($existing) {
            $postarr['ID'] = $existing[0]->ID;
            $post_id = wp_update_post($postarr);
        } else {
            $post_id = wp_insert_post($postarr);
        }
        if (is_wp_error($post_id) || !$post_id) return;

        // Mapping dynamique : tous les champs du bien sont stockés en custom fields
        foreach ($property as $key => $val) {
            if (is_array($val) || is_object($val)) {
                update_post_meta($post_id, $key, maybe_serialize($val));
            } else {
                update_post_meta($post_id, $key, $val);
            }
        }

        // Taxonomies (si présentes)
        if (!empty($property['type'])) {
            wp_set_object_terms($post_id, $property['type'], 'property_type', false);
        }
        if (!empty($property['transactionType'])) {
            wp_set_object_terms($post_id, $property['transactionType'], 'transaction_type', false);
        }
        if (!empty($property['city'])) {
            wp_set_object_terms($post_id, $property['city'], 'property_city', false);
        }
        if (!empty($property['status'])) {
            wp_set_object_terms($post_id, $property['status'], 'property_status', false);
        }
    }
}
