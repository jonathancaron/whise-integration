<?php
if (!defined('ABSPATH')) exit;

class Whise_Integration {
    private $sync_manager;
    private $property_cpt;
    private $property_details;
    
    public function __construct() {
        // Définition des constantes
        if (!defined('WHISE_VERSION')) {
            define('WHISE_VERSION', '1.0.0');
        }
        if (!defined('WHISE_PLUGIN_FILE')) {
            define('WHISE_PLUGIN_FILE', plugin_dir_path(__FILE__) . 'whise-integration.php');
        }

        // Chargement des dépendances
        $this->load_dependencies();
        
        // Initialisation des composants
        $this->init_components();
        
        // Actions d'activation/désactivation
        register_activation_hook(WHISE_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(WHISE_PLUGIN_FILE, [$this, 'deactivate']);
    }
    
    private function load_dependencies() {
        require_once plugin_dir_path(__FILE__) . 'includes/class-sync-manager.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-property-cpt.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-property-details-page.php';
    }
    
    private function init_components() {
        $this->sync_manager = new Whise_Sync_Manager();
        $this->property_cpt = new Whise_Property_CPT();
        $this->property_details = new Whise_Property_Details_Page();
    }
    
    public function activate() {
        // Force la réinitialisation des taxonomies
        if ($this->property_cpt) {
            $this->property_cpt->force_taxonomies_reset();
        }
        
        // Flush les règles de réécriture
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Nettoyage si nécessaire
        flush_rewrite_rules();
    }
}
