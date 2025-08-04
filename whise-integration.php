<?php
/*
Plugin Name: Whise Integration
Description: Intégration professionnelle de l'API Whise pour WordPress, compatible Elementor Pro.
Version: 1.0.0
Author: Jonathan Caron
Text Domain: whise-integration
Domain Path: /languages
*/

if (!defined('ABSPATH')) exit;

// Chargement automatique des classes
foreach ([
    'class-whise-api.php',
    'class-property-cpt.php',
    'class-sync-manager.php',
    'class-admin.php',
    'class-shortcodes.php',
] as $file) {
    $path = plugin_dir_path(__FILE__) . 'includes/' . $file;
    if (file_exists($path)) require_once $path;
}

// Initialisation des composants principaux
add_action('plugins_loaded', function() {
    new Whise_Property_CPT();
    new Whise_Admin();
    // Les autres classes seront instanciées ici plus tard
});

// Activation : flush rewrite rules


register_activation_hook(__FILE__, function() {
    $cpt = new Whise_Property_CPT();
    $cpt->register_post_type();
    $cpt->register_taxonomies();
    // Initialisation des termes par défaut
    do_action('whise_init_default_terms');
    // Planification du cron si non déjà planifié
    if (!wp_next_scheduled('whise_sync_properties')) {
        wp_schedule_event(time(), 'hourly', 'whise_sync_properties');
    }
    flush_rewrite_rules();
});

// Désactivation : flush rewrite rules

register_deactivation_hook(__FILE__, function() {
    // Suppression du cron
    $timestamp = wp_next_scheduled('whise_sync_properties');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'whise_sync_properties');
    }
    flush_rewrite_rules();
});

// Hook de synchronisation (callback à compléter dans la classe Whise_Sync_Manager)
add_action('whise_sync_properties', function() {
    if (class_exists('Whise_Sync_Manager')) {
        (new Whise_Sync_Manager())->sync_properties_batch();
    }
});
