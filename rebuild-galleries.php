<?php
/**
 * Script de reconstruction des galeries d'images Whise
 * 
 * Reconstruit les galeries (_whise_gallery_images) à partir des attachments existants
 * 
 * IMPORTANT : Supprimez ce fichier après utilisation !
 */

// Désactiver l'affichage des erreurs
error_reporting(0);
ini_set('display_errors', 0);

try {
    // Charger WordPress
    if (!defined('ABSPATH')) {
        $possible_paths = [
            dirname(__FILE__) . '/../../../../wp-load.php',
            dirname(__FILE__) . '/../../../wp-load.php',
            dirname(__FILE__) . '/../../wp-load.php',
            dirname(__FILE__) . '/../wp-load.php',
            dirname(__FILE__) . '/wp-load.php',
        ];
        
        $wp_loaded = false;
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                require_once($path);
                $wp_loaded = true;
                break;
            }
        }
        
        if (!$wp_loaded) {
            die('Erreur: Impossible de charger WordPress.');
        }
    }

    // Vérification de sécurité
    if (!function_exists('current_user_can') || !current_user_can('manage_options')) {
        wp_die('Accès refusé. Vous devez être administrateur.');
    }

    // Configuration
    set_time_limit(0);
    ini_set('memory_limit', '512M');

    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Reconstruction des galeries Whise</title>
        <style>
            body { 
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                margin: 20px;
                background: #f0f0f1;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            h1 { color: #1d2327; margin-top: 0; }
            .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #0073aa; }
            .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107; }
            .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
            .button { 
                background: #0073aa; 
                color: white; 
                padding: 12px 24px; 
                text-decoration: none; 
                border-radius: 4px; 
                margin: 5px; 
                display: inline-block; 
                border: none; 
                cursor: pointer;
                font-size: 14px;
            }
            .button:hover { background: #005a87; }
            .stats { 
                background: #f8f9fa; 
                padding: 20px; 
                border-radius: 5px; 
                margin: 20px 0;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
            }
            .stat-item {
                background: white;
                padding: 15px;
                border-radius: 4px;
                text-align: center;
                box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            }
            .stat-number {
                font-size: 32px;
                font-weight: bold;
                color: #0073aa;
            }
            .stat-label {
                color: #646970;
                font-size: 13px;
                margin-top: 5px;
            }
            .progress {
                background: #f0f0f1;
                border-radius: 4px;
                height: 30px;
                overflow: hidden;
                margin: 20px 0;
            }
            .progress-bar {
                background: #0073aa;
                height: 100%;
                text-align: center;
                line-height: 30px;
                color: white;
                font-weight: bold;
                transition: width 0.3s;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔧 Reconstruction des galeries Whise</h1>
            
            <?php
            if (isset($_POST['action'])) {
                $action = $_POST['action'];
                
                if ($action === 'rebuild') {
                    echo '<h2>🔧 Reconstruction en cours :</h2>';
                    
                    // Récupérer tous les biens Whise
                    $properties = get_posts([
                        'post_type' => 'property',
                        'numberposts' => -1,
                        'post_status' => 'any'
                    ]);
                    
                    echo '<div class="info">';
                    echo '<p>⏳ Traitement de ' . count($properties) . ' bien(s)...</p>';
                    echo '</div>';
                    
                    $rebuilt = 0;
                    $skipped = 0;
                    $errors = 0;
                    $total_images = 0;
                    
                    foreach ($properties as $property) {
                        $post_id = $property->ID;
                        $whise_id = get_post_meta($post_id, 'whise_id', true);
                        
                        if (empty($whise_id)) {
                            $skipped++;
                            continue;
                        }
                        
                        // Chercher tous les attachments pour ce bien
                        global $wpdb;
                        $attachment_ids = $wpdb->get_col($wpdb->prepare(
                            "SELECT p.ID 
                            FROM {$wpdb->posts} p
                            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                            WHERE p.post_type = 'attachment'
                            AND pm.meta_key = '_wp_attached_file'
                            AND pm.meta_value LIKE %s
                            ORDER BY pm.meta_value",
                            '%whise-' . $whise_id . '%'
                        ));
                        
                        if (!empty($attachment_ids)) {
                            // Mettre à jour la galerie
                            update_post_meta($post_id, '_whise_gallery_images', $attachment_ids);
                            
                            // Définir la première image comme featured image si absente
                            if (!has_post_thumbnail($post_id)) {
                                set_post_thumbnail($post_id, $attachment_ids[0]);
                            }
                            
                            $rebuilt++;
                            $total_images += count($attachment_ids);
                        } else {
                            $errors++;
                        }
                    }
                    
                    echo '<div class="success">';
                    echo '<h3>✅ Reconstruction terminée !</h3>';
                    echo '<div class="stats">';
                    echo '<div class="stat-item">';
                    echo '<div class="stat-number" style="color: #28a745;">' . $rebuilt . '</div>';
                    echo '<div class="stat-label">Galeries reconstruites</div>';
                    echo '</div>';
                    echo '<div class="stat-item">';
                    echo '<div class="stat-number" style="color: #0073aa;">' . $total_images . '</div>';
                    echo '<div class="stat-label">Images associées</div>';
                    echo '</div>';
                    echo '<div class="stat-item">';
                    echo '<div class="stat-number" style="color: #6c757d;">' . $skipped . '</div>';
                    echo '<div class="stat-label">Biens ignorés</div>';
                    echo '</div>';
                    echo '<div class="stat-item">';
                    echo '<div class="stat-number" style="color: #dc3545;">' . $errors . '</div>';
                    echo '<div class="stat-label">Biens sans images</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    
                    if ($errors > 0) {
                        echo '<div class="warning">';
                        echo '<p>⚠️ ' . $errors . ' bien(s) n\'ont aucune image associée. Lancez une synchronisation pour les télécharger.</p>';
                        echo '</div>';
                    }
                }
                
            } else {
                // Afficher le formulaire
                ?>
                <div class="info">
                    <h2>ℹ️ À propos</h2>
                    <p>Ce script reconstruit les galeries d'images pour tous les biens Whise à partir des attachments existants.</p>
                    <p><strong>Cas d'usage :</strong></p>
                    <ul>
                        <li>Galeries vides ou corrompues</li>
                        <li>Images présentes mais non associées aux biens</li>
                        <li>Après un nettoyage qui a affecté les méta</li>
                    </ul>
                </div>
                
                <div class="warning">
                    <h2>⚠️ Important</h2>
                    <p>Ce script :</p>
                    <ul>
                        <li>✅ Reconstruit les galeries à partir des images existantes</li>
                        <li>✅ Définit l'image principale si absente</li>
                        <li>❌ Ne télécharge PAS de nouvelles images (utilisez la synchro pour ça)</li>
                    </ul>
                </div>
                
                <?php
                // Statistiques actuelles
                global $wpdb;
                
                $total_properties = $wpdb->get_var(
                    "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'property' AND post_status != 'trash'"
                );
                
                $properties_with_gallery = $wpdb->get_var(
                    "SELECT COUNT(*) FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_whise_gallery_images' 
                    AND meta_value != '' 
                    AND meta_value != 'a:0:{}'"
                );
                
                $properties_without_gallery = $total_properties - $properties_with_gallery;
                
                $total_attachments = $wpdb->get_var(
                    "SELECT COUNT(*) FROM {$wpdb->posts} p
                    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                    WHERE p.post_type = 'attachment'
                    AND pm.meta_key = '_wp_attached_file'
                    AND pm.meta_value LIKE '%whise-%'"
                );
                
                echo '<h2>📊 État actuel :</h2>';
                echo '<div class="stats">';
                echo '<div class="stat-item">';
                echo '<div class="stat-number">' . $total_properties . '</div>';
                echo '<div class="stat-label">Biens totaux</div>';
                echo '</div>';
                echo '<div class="stat-item">';
                echo '<div class="stat-number" style="color: #28a745;">' . $properties_with_gallery . '</div>';
                echo '<div class="stat-label">Biens avec galerie</div>';
                echo '</div>';
                echo '<div class="stat-item">';
                echo '<div class="stat-number" style="color: #dc3545;">' . $properties_without_gallery . '</div>';
                echo '<div class="stat-label">Biens sans galerie</div>';
                echo '</div>';
                echo '<div class="stat-item">';
                echo '<div class="stat-number" style="color: #0073aa;">' . $total_attachments . '</div>';
                echo '<div class="stat-label">Images disponibles</div>';
                echo '</div>';
                echo '</div>';
                
                if ($properties_without_gallery > 0) {
                    echo '<div class="warning">';
                    echo '<p>⚠️ <strong>' . $properties_without_gallery . '</strong> bien(s) n\'ont pas de galerie d\'images.</p>';
                    echo '<p>La reconstruction va tenter de retrouver leurs images existantes.</p>';
                    echo '</div>';
                }
                ?>
                
                <h2>🔧 Lancer la reconstruction :</h2>
                <form method="post">
                    <input type="hidden" name="action" value="rebuild">
                    <button type="submit" class="button" onclick="return confirm('Êtes-vous sûr de vouloir reconstruire toutes les galeries ?')">🔧 Reconstruire les galeries</button>
                </form>
                <?php
            }
            ?>
            
            <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #dcdcde; color: #646970; font-size: 13px;">
                <p><strong>🔒 Sécurité :</strong> Supprimez ce fichier (<code>rebuild-galleries.php</code>) après utilisation.</p>
            </div>
        </div>
    </body>
    </html>
    
<?php
} catch (Exception $e) {
    echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; margin: 20px;">';
    echo '<h2>❌ Erreur critique</h2>';
    echo '<p><strong>Message d\'erreur :</strong> ' . esc_html($e->getMessage()) . '</p>';
    echo '</div>';
}
?>

