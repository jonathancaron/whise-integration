<?php
/**
 * Plugin Name: Whise Integration
 * Description: Intégration professionnelle de l'API Whise pour WordPress, compatible Elementor Pro.
 * Version: 2.0.0
 * Author: Jonathan Caron
 * License: MIT
 */

if (!defined('ABSPATH')) exit;

// Définition des constantes
define('WHISE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WHISE_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Vérification et inclusion des classes avec gestion d'erreurs
function whise_include_class($class_file) {
    $file_path = WHISE_PLUGIN_PATH . 'includes/' . $class_file;
    if (file_exists($file_path)) {
        require_once $file_path;
        return true;
    } else {
        error_log('Whise Integration: Fichier manquant - ' . $file_path);
        return false;
    }
}

// Inclusion des classes avec vérification
$classes_loaded = [
    'class-whise-api.php' => whise_include_class('class-whise-api.php'),
    'class-sync-manager.php' => whise_include_class('class-sync-manager.php'),
    'class-property-cpt.php' => whise_include_class('class-property-cpt.php'),
    'class-admin.php' => whise_include_class('class-admin.php'),
    'class-shortcodes.php' => whise_include_class('class-shortcodes.php'),
    'class-debug.php' => whise_include_class('class-debug.php')
];

// Vérifier si tous les fichiers essentiels sont présents
$missing_files = array_filter($classes_loaded, function($loaded) { return !$loaded; });
if (!empty($missing_files)) {
    add_action('admin_notices', function() use ($missing_files) {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>Whise Integration:</strong> Fichiers manquants: ' . implode(', ', array_keys($missing_files));
        echo '</p></div>';
    });
    return; // Arrêter l'initialisation si des fichiers manquent
}

// Initialisation du plugin
class Whise_Integration {
    
    public function __construct() {
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }
    
    public function init() {
        // Initialisation des classes avec vérification
        if (class_exists('Whise_Property_CPT')) {
            new Whise_Property_CPT();
        }
        
        if (class_exists('Whise_Admin')) {
            new Whise_Admin();
        }
        
        if (class_exists('Whise_Shortcodes')) {
            new Whise_Shortcodes();
        }
        
        // Ajout d'un hook pour la synchronisation manuelle
        add_action('wp_ajax_whise_sync_manual', [$this, 'manual_sync']);
    }
    
    public function enqueue_scripts() {
        if (defined('WHISE_PLUGIN_URL')) {
            wp_enqueue_style('whise-integration', WHISE_PLUGIN_URL . 'assets/css/style.css', [], '2.0.0');
        }
    }
    
    public function manual_sync() {
        if (!current_user_can('manage_options')) {
            wp_die('Accès refusé');
        }
        
        if (class_exists('Whise_Sync_Manager')) {
            $sync_manager = new Whise_Sync_Manager();
            $sync_manager->sync_properties_batch();
        }
        
        wp_redirect(admin_url('admin.php?page=whise-integration&sync=success'));
        exit;
    }
}

// Initialisation du plugin seulement si les classes sont disponibles
if (class_exists('Whise_Property_CPT') && class_exists('Whise_Admin')) {
    new Whise_Integration();
}

// Activation du plugin
register_activation_hook(__FILE__, function() {
    // Création des tables et options nécessaires
    add_option('whise_api_endpoint', 'https://api.whise.eu/');
    add_option('whise_sync_frequency', 'hourly');
    
    // Planification de la synchronisation automatique
    if (!wp_next_scheduled('whise_sync_event')) {
        wp_schedule_event(time(), 'hourly', 'whise_sync_event');
    }
    
    // Forcer la réinitialisation du post type et des taxonomies
    if (class_exists('Whise_Property_CPT')) {
        do_action('whise_force_reset');
    }
    
    // Flush des règles de réécriture
    flush_rewrite_rules();
    
    // Log d'activation
    error_log('Whise Integration: Plugin activé avec réinitialisation forcée');
});

// Désactivation du plugin
register_deactivation_hook(__FILE__, function() {
    // Nettoyage des tâches planifiées
    wp_clear_scheduled_hook('whise_sync_event');
    
    // Log de désactivation
    error_log('Whise Integration: Plugin désactivé');
});

// Hook pour la synchronisation automatique
add_action('whise_sync_event', function() {
    if (class_exists('Whise_Sync_Manager')) {
        try {
            $sync_manager = new Whise_Sync_Manager();
            $sync_manager->sync_properties_batch();
        } catch (Exception $e) {
            error_log('Whise Integration: Erreur lors de la synchronisation automatique - ' . $e->getMessage());
        }
    }
});
