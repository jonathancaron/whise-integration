<?php
/**
 * Script de reconstruction des images principales (featured images)
 * 
 * Définit la première image de la galerie comme image principale pour tous les biens
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
        <title>Reconstruction des images principales Whise</title>
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
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🖼️ Reconstruction des images principales</h1>
            
            <?php
            if (isset($_POST['action'])) {
                $action = $_POST['action'];
                
                if ($action === 'rebuild' || $action === 'force_rebuild') {
                    $force_mode = ($action === 'force_rebuild');
                    
                    echo '<h2>🔧 Reconstruction en cours' . ($force_mode ? ' (mode forcé)' : '') . ' :</h2>';
                    
                    // Récupérer tous les biens
                    $properties = get_posts([
                        'post_type' => 'property',
                        'numberposts' => -1,
                        'post_status' => 'publish'
                    ]);
                    
                    echo '<div class="info">';
                    echo '<p>⏳ Traitement de ' . count($properties) . ' bien(s)...</p>';
                    if ($force_mode) {
                        echo '<p><strong>Mode forcé :</strong> Toutes les images principales seront redéfinies.</p>';
                    }
                    echo '</div>';
                    
                    $set_thumbnail = 0;
                    $already_has = 0;
                    $no_gallery = 0;
                    $replaced = 0;
                    
                    foreach ($properties as $property) {
                        $post_id = $property->ID;
                        
                        // Vérifier si a déjà une image principale
                        $has_thumbnail = has_post_thumbnail($post_id);
                        
                        // En mode normal, skip si a déjà une image
                        if ($has_thumbnail && !$force_mode) {
                            $already_has++;
                            continue;
                        }
                        
                        // Récupérer la galerie
                        $gallery = get_post_meta($post_id, '_whise_gallery_images', true);
                        
                        if (empty($gallery) || !is_array($gallery)) {
                            $no_gallery++;
                            continue;
                        }
                        
                        // Définir la première image comme image principale
                        $first_image_id = $gallery[0];
                        if ($first_image_id && get_post($first_image_id)) {
                            // Supprimer l'ancienne image principale si en mode forcé
                            if ($has_thumbnail && $force_mode) {
                                delete_post_thumbnail($post_id);
                                $replaced++;
                            }
                            
                            set_post_thumbnail($post_id, $first_image_id);
                            $set_thumbnail++;
                        }
                    }
                    
                    echo '<div class="success">';
                    echo '<h3>✅ Reconstruction terminée !</h3>';
                    echo '<div class="stats">';
                    echo '<div class="stat-item">';
                    echo '<div class="stat-number" style="color: #28a745;">' . $set_thumbnail . '</div>';
                    echo '<div class="stat-label">Images principales définies</div>';
                    echo '</div>';
                    if ($force_mode && $replaced > 0) {
                        echo '<div class="stat-item">';
                        echo '<div class="stat-number" style="color: #ff9800;">' . $replaced . '</div>';
                        echo '<div class="stat-label">Images remplacées</div>';
                        echo '</div>';
                    }
                    if (!$force_mode) {
                        echo '<div class="stat-item">';
                        echo '<div class="stat-number" style="color: #0073aa;">' . $already_has . '</div>';
                        echo '<div class="stat-label">Déjà configurés</div>';
                        echo '</div>';
                    }
                    echo '<div class="stat-item">';
                    echo '<div class="stat-number" style="color: #dc3545;">' . $no_gallery . '</div>';
                    echo '<div class="stat-label">Sans galerie</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    
                    if ($no_gallery > 0) {
                        echo '<div class="warning">';
                        echo '<p>⚠️ ' . $no_gallery . ' bien(s) n\'ont aucune galerie. Lancez une synchronisation pour télécharger leurs images.</p>';
                        echo '</div>';
                    }
                }
                
            } else {
                // Diagnostiquer la situation actuelle
                global $wpdb;
                
                $total_properties = $wpdb->get_var(
                    "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'property' AND post_status = 'publish'"
                );
                
                $with_thumbnail = $wpdb->get_var(
                    "SELECT COUNT(DISTINCT p.ID) 
                    FROM {$wpdb->posts} p
                    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                    WHERE p.post_type = 'property' 
                    AND p.post_status = 'publish'
                    AND pm.meta_key = '_thumbnail_id'"
                );
                
                $with_gallery = $wpdb->get_var(
                    "SELECT COUNT(DISTINCT p.ID) 
                    FROM {$wpdb->posts} p
                    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                    WHERE p.post_type = 'property' 
                    AND p.post_status = 'publish'
                    AND pm.meta_key = '_whise_gallery_images' 
                    AND pm.meta_value != '' 
                    AND pm.meta_value != 'a:0:{}'"
                );
                
                $without_thumbnail = $total_properties - $with_thumbnail;
                
                ?>
                <div class="info">
                    <h2>ℹ️ À propos</h2>
                    <p>Ce script définit la <strong>première image de la galerie</strong> comme <strong>image principale</strong> (featured image) pour tous les biens qui n'en ont pas.</p>
                    <p><strong>Cas d'usage :</strong></p>
                    <ul>
                        <li>Images principales manquantes après un nettoyage</li>
                        <li>Galeries OK mais pas d'image à la une</li>
                        <li>Après une migration ou restauration</li>
                    </ul>
                </div>
                
                <h2>📊 État actuel :</h2>
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $total_properties; ?></div>
                        <div class="stat-label">Biens totaux</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" style="color: #28a745;"><?php echo $with_thumbnail; ?></div>
                        <div class="stat-label">Avec image principale</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" style="color: #dc3545;"><?php echo $without_thumbnail; ?></div>
                        <div class="stat-label">Sans image principale</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" style="color: #0073aa;"><?php echo $with_gallery; ?></div>
                        <div class="stat-label">Avec galerie</div>
                    </div>
                </div>
                
                <?php if ($without_thumbnail > 0) : ?>
                    <div class="warning">
                        <p>⚠️ <strong><?php echo $without_thumbnail; ?></strong> bien(s) n'ont pas d'image principale.</p>
                        <p>La reconstruction va définir automatiquement la première image de leur galerie.</p>
                    </div>
                    
                    <h2>🔧 Lancer la reconstruction :</h2>
                    <form method="post" style="display: inline-block; margin-right: 10px;">
                        <input type="hidden" name="action" value="rebuild">
                        <button type="submit" class="button">🖼️ Définir les images principales</button>
                    </form>
                <?php else : ?>
                    <div class="success">
                        <p>✅ Tous les biens ont déjà une image principale !</p>
                    </div>
                <?php endif; ?>
                
                <?php if ($with_thumbnail > 0) : ?>
                    <div class="warning">
                        <h3>⚡ Mode forcé</h3>
                        <p><strong>Utilisez cette option si :</strong></p>
                        <ul>
                            <li>Les images principales sont définies mais ne s'affichent pas</li>
                            <li>Vous voulez remplacer toutes les images principales par la première de chaque galerie</li>
                            <li>Les images principales sont corrompues</li>
                        </ul>
                        <p><strong>⚠️ Attention :</strong> Cette action va redéfinir l'image principale de <strong>TOUS</strong> les <?php echo $with_thumbnail; ?> bien(s).</p>
                    </div>
                    
                    <form method="post" style="display: inline-block;">
                        <input type="hidden" name="action" value="force_rebuild">
                        <button type="submit" class="button" style="background-color: #ff9800; border-color: #ff9800;" onclick="return confirm('⚠️ Êtes-vous sûr de vouloir FORCER la redéfinition de toutes les images principales ?\n\nCette action va remplacer les images principales existantes par la première image de chaque galerie.')">⚡ Mode forcé : Tout redéfinir</button>
                    </form>
                <?php endif; ?>
                <?php
            }
            ?>
            
            <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #dcdcde; color: #646970; font-size: 13px;">
                <p><strong>🔒 Sécurité :</strong> Supprimez ce fichier (<code>rebuild-thumbnails.php</code>) après utilisation.</p>
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
git s