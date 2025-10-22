<?php
/**
 * Script de nettoyage des doublons d'images Whise
 * 
 * Ce script détecte et supprime les fichiers physiques dupliqués
 * qui ont été créés avec des suffixes (-1, -2, -3, etc.) par WordPress.
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
        <title>Nettoyage des doublons d'images Whise</title>
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
            h2 { color: #2c3338; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
            .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #0073aa; }
            .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107; }
            .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
            .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545; }
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
                font-weight: 500;
            }
            .button:hover { background: #005a87; }
            .button-danger { background: #dc3545; }
            .button-danger:hover { background: #bd2130; }
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
            .file-list { 
                max-height: 400px; 
                overflow-y: auto; 
                background: #f8f9fa; 
                padding: 15px; 
                border-radius: 4px;
                border: 1px solid #dcdcde;
            }
            .file-item {
                padding: 8px;
                margin: 4px 0;
                background: white;
                border-radius: 3px;
                font-family: monospace;
                font-size: 12px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .file-duplicate {
                background: #fff3cd;
                border-left: 3px solid #ffc107;
            }
            .file-size {
                color: #646970;
                font-size: 11px;
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
            <h1>🧹 Nettoyage des doublons d'images Whise</h1>
            
            <?php
            if (isset($_POST['action'])) {
                $action = $_POST['action'];
                
                if ($action === 'scan') {
                    echo '<h2>🔍 Analyse des doublons :</h2>';
                    
                    $upload_dir = wp_upload_dir();
                    $base_dir = $upload_dir['basedir'];
                    
                    // Scanner tous les fichiers Whise
                    $all_whise_files = [];
                    $scan_dirs = [
                        $base_dir . '/2025',
                        $base_dir . '/2024',
                        $base_dir . '/2023',
                    ];
                    
                    echo '<div class="info">';
                    echo '<p>⏳ Scan en cours des répertoires...</p>';
                    echo '</div>';
                    
                    foreach ($scan_dirs as $scan_dir) {
                        if (is_dir($scan_dir)) {
                            $iterator = new RecursiveIteratorIterator(
                                new RecursiveDirectoryIterator($scan_dir, RecursiveDirectoryIterator::SKIP_DOTS),
                                RecursiveIteratorIterator::SELF_FIRST
                            );
                            
                            foreach ($iterator as $file) {
                                if ($file->isFile() && preg_match('/^whise-\d+-\d+-\d+(-\d+)?\.(jpg|jpeg|png|gif|webp)$/i', $file->getFilename())) {
                                    $all_whise_files[] = [
                                        'path' => $file->getPathname(),
                                        'name' => $file->getFilename(),
                                        'size' => $file->getSize(),
                                    ];
                                }
                            }
                        }
                    }
                    
                    echo '<div class="stats">';
                    echo '<div class="stat-item">';
                    echo '<div class="stat-number">' . count($all_whise_files) . '</div>';
                    echo '<div class="stat-label">Fichiers Whise trouvés</div>';
                    echo '</div>';
                    echo '</div>';
                    
                    // Identifier les doublons (fichiers avec -1, -2, -3, etc.)
                    $duplicates = [];
                    $originals = [];
                    
                    foreach ($all_whise_files as $file) {
                        // Vérifier si c'est un doublon (contient -N avant l'extension)
                        if (preg_match('/^(whise-\d+-\d+-\d+)-(\d+)\.(jpg|jpeg|png|gif|webp)$/i', $file['name'], $matches)) {
                            $duplicates[] = $file;
                        } else {
                            $originals[] = $file;
                        }
                    }
                    
                    echo '<div class="stats">';
                    echo '<div class="stat-item">';
                    echo '<div class="stat-number">' . count($originals) . '</div>';
                    echo '<div class="stat-label">Images originales</div>';
                    echo '</div>';
                    echo '<div class="stat-item">';
                    echo '<div class="stat-number" style="color: #dc3545;">' . count($duplicates) . '</div>';
                    echo '<div class="stat-label">Doublons détectés</div>';
                    echo '</div>';
                    echo '</div>';
                    
                    if (empty($duplicates)) {
                        echo '<div class="success">';
                        echo '<h3>✅ Aucun doublon trouvé</h3>';
                        echo '<p>Toutes vos images Whise sont uniques, aucun nettoyage nécessaire.</p>';
                        echo '</div>';
                    } else {
                        // Calculer l'espace occupé
                        $total_size = 0;
                        foreach ($duplicates as $dup) {
                            $total_size += $dup['size'];
                        }
                        
                        echo '<div class="warning">';
                        echo '<h3>⚠️ Doublons détectés</h3>';
                        echo '<p><strong>' . count($duplicates) . '</strong> fichier(s) dupliqué(s) détecté(s).</p>';
                        echo '<p><strong>Espace disque occupé par les doublons :</strong> ' . number_format($total_size / 1024 / 1024, 2) . ' MB</p>';
                        
                        // Grouper les doublons par image originale
                        $grouped = [];
                        foreach ($duplicates as $dup) {
                            preg_match('/^(whise-\d+-\d+-\d+)-(\d+)\.(jpg|jpeg|png|gif|webp)$/i', $dup['name'], $matches);
                            $base_name = $matches[1];
                            if (!isset($grouped[$base_name])) {
                                $grouped[$base_name] = [];
                            }
                            $grouped[$base_name][] = $dup;
                        }
                        
                        echo '<p><strong>Nombre d\'images concernées :</strong> ' . count($grouped) . '</p>';
                        
                        echo '<div class="file-list">';
                        echo '<h4>Exemples de doublons (premiers 30) :</h4>';
                        
                        $count = 0;
                        foreach ($grouped as $base_name => $dups) {
                            if ($count >= 10) break;
                            
                            echo '<div style="margin-bottom: 10px; padding: 10px; background: white; border-radius: 4px;">';
                            echo '<strong>' . $base_name . '</strong>';
                            echo '<ul style="margin: 5px 0; padding-left: 20px;">';
                            foreach ($dups as $dup) {
                                echo '<li class="file-item file-duplicate">';
                                echo '<span>' . esc_html($dup['name']) . '</span>';
                                echo '<span class="file-size">' . number_format($dup['size'] / 1024, 2) . ' KB</span>';
                                echo '</li>';
                            }
                            echo '</ul>';
                            echo '</div>';
                            
                            $count++;
                        }
                        
                        if (count($grouped) > 10) {
                            echo '<p><em>... et ' . (count($grouped) - 10) . ' autres images avec doublons</em></p>';
                        }
                        
                        echo '</div>';
                        
                        echo '<form method="post" style="margin-top: 20px;">';
                        echo '<input type="hidden" name="action" value="cleanup">';
                        // Stocker les doublons dans un transient
                        set_transient('whise_duplicates', $duplicates, 3600);
                        echo '<button type="submit" class="button button-danger" onclick="return confirm(\'⚠️ ATTENTION :\\n\\nCette action va supprimer ' . count($duplicates) . ' fichier(s) dupliqué(s).\\n\\nSeules les images originales (sans suffixe -1, -2, etc.) seront conservées.\\n\\nCette action est irréversible.\\n\\nÊtes-vous sûr de vouloir continuer ?\')">🗑️ Supprimer tous les doublons (' . count($duplicates) . ' fichiers)</button>';
                        echo '</form>';
                        
                        echo '</div>';
                    }
                    
                } elseif ($action === 'cleanup') {
                    echo '<h2>🧹 Suppression des doublons :</h2>';
                    
                    $duplicates = get_transient('whise_duplicates');
                    
                    if (empty($duplicates)) {
                        echo '<div class="warning">';
                        echo '<p>❌ Aucun doublon à nettoyer. Veuillez d\'abord lancer un scan.</p>';
                        echo '</div>';
                    } else {
                        $deleted_files_count = 0;
                        $deleted_attachments_count = 0;
                        $error_count = 0;
                        $total_size_freed = 0;
                        
                        echo '<div class="info">';
                        echo '<p>⏳ Suppression en cours de ' . count($duplicates) . ' fichier(s) et leurs attachments DB...</p>';
                        echo '</div>';
                        
                        foreach ($duplicates as $duplicate) {
                            $file_deleted = false;
                            $attachment_deleted = false;
                            
                            // 1. Chercher l'attachment dans la DB par nom de fichier
                            global $wpdb;
                            $filename = basename($duplicate['path']);
                            $attachment_id = $wpdb->get_var($wpdb->prepare(
                                "SELECT post_id FROM {$wpdb->postmeta} 
                                WHERE meta_key = '_wp_attached_file' 
                                AND meta_value LIKE %s 
                                LIMIT 1",
                                '%' . $wpdb->esc_like($filename)
                            ));
                            
                            // 2. Supprimer l'attachment de la DB
                            if ($attachment_id) {
                                if (wp_delete_attachment($attachment_id, true)) {
                                    $deleted_attachments_count++;
                                    $attachment_deleted = true;
                                }
                            }
                            
                            // 3. Supprimer le fichier physique
                            if (file_exists($duplicate['path'])) {
                                $size = filesize($duplicate['path']);
                                if (@unlink($duplicate['path'])) {
                                    $deleted_files_count++;
                                    $total_size_freed += $size;
                                    $file_deleted = true;
                                }
                            }
                            
                            // 4. Compter les erreurs
                            if (!$file_deleted && !$attachment_deleted) {
                                $error_count++;
                            }
                        }
                        
                        delete_transient('whise_duplicates');
                        
                        echo '<div class="success">';
                        echo '<h3>✅ Nettoyage terminé avec succès !</h3>';
                        echo '<div class="stats">';
                        echo '<div class="stat-item">';
                        echo '<div class="stat-number" style="color: #28a745;">' . $deleted_files_count . '</div>';
                        echo '<div class="stat-label">Fichiers physiques supprimés</div>';
                        echo '</div>';
                        echo '<div class="stat-item">';
                        echo '<div class="stat-number" style="color: #0073aa;">' . $deleted_attachments_count . '</div>';
                        echo '<div class="stat-label">Attachments DB supprimés</div>';
                        echo '</div>';
                        echo '<div class="stat-item">';
                        echo '<div class="stat-number" style="color: #dc3545;">' . $error_count . '</div>';
                        echo '<div class="stat-label">Erreurs</div>';
                        echo '</div>';
                        echo '<div class="stat-item">';
                        echo '<div class="stat-number" style="color: #6c757d;">' . number_format($total_size_freed / 1024 / 1024, 2) . ' MB</div>';
                        echo '<div class="stat-label">Espace disque libéré</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        
                        if ($error_count > 0) {
                            echo '<div class="warning">';
                            echo '<p>⚠️ Certains fichiers n\'ont pas pu être supprimés. Vérifiez les permissions.</p>';
                            echo '</div>';
                        }
                        
                        // Afficher un message explicatif sur la différence entre fichiers et attachments
                        if ($deleted_files_count !== $deleted_attachments_count) {
                            echo '<div class="info">';
                            echo '<h4>ℹ️ Différence entre fichiers et attachments</h4>';
                            if ($deleted_files_count > $deleted_attachments_count) {
                                echo '<p><strong>Fichiers orphelins détectés :</strong> ' . ($deleted_files_count - $deleted_attachments_count) . ' fichier(s) physique(s) supprimé(s) sans entrée dans la base de données.</p>';
                                echo '<p>Cela peut arriver après des nettoyages partiels ou des suppressions manuelles d\'attachments.</p>';
                            } else {
                                echo '<p><strong>Attachments orphelins détectés :</strong> ' . ($deleted_attachments_count - $deleted_files_count) . ' entrée(s) dans la base de données sans fichier physique correspondant.</p>';
                                echo '<p>Cela peut arriver après des suppressions manuelles de fichiers ou des problèmes de synchronisation.</p>';
                            }
                            echo '</div>';
                        }
                    }
                }
                
            } else {
                // Afficher le formulaire de scan
                ?>
                <div class="info">
                    <h2>ℹ️ À propos</h2>
                    <p>Ce script détecte et supprime les <strong>doublons d'images</strong> créés par WordPress lors des synchronisations répétées.</p>
                    <p><strong>Qu'est-ce qu'un doublon ?</strong></p>
                    <p>WordPress ajoute automatiquement des suffixes <code>-1</code>, <code>-2</code>, <code>-3</code>, etc. aux fichiers quand un fichier avec le même nom existe déjà.</p>
                    <p><strong>Exemples :</strong></p>
                    <ul>
                        <li><code>whise-7136172-9-72068404.jpg</code> ← <strong>Original</strong> ✅</li>
                        <li><code>whise-7136172-9-72068404-1.jpg</code> ← <strong>Doublon</strong> ❌</li>
                        <li><code>whise-7136172-9-72068404-2.jpg</code> ← <strong>Doublon</strong> ❌</li>
                    </ul>
                </div>
                
                <div class="warning">
                    <h2>⚠️ Important</h2>
                    <ul>
                        <li>Cette action ne supprime que les <strong>fichiers physiques</strong> avec suffixes</li>
                        <li>Les <strong>images originales</strong> (sans suffixe) sont <strong>conservées</strong></li>
                        <li>Cette action est <strong>irréversible</strong></li>
                        <li>Après le nettoyage, les <strong>futures synchronisations</strong> écraseront automatiquement les images existantes (correction appliquée)</li>
                    </ul>
                </div>
                
                <h2>🔍 Étape 1 : Scanner les doublons</h2>
                <p>Cliquez sur le bouton ci-dessous pour analyser vos fichiers et détecter les doublons.</p>
                <form method="post">
                    <input type="hidden" name="action" value="scan">
                    <button type="submit" class="button">🔍 Scanner les doublons</button>
                </form>
                
                <h2>🔧 Autres outils disponibles :</h2>
                <a href="<?php echo admin_url('admin.php?page=whise-settings'); ?>" class="button">⚙️ Réglages Whise</a>
                <?php
            }
            ?>
            
            <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #dcdcde; color: #646970; font-size: 13px;">
                <p><strong>🔒 Sécurité :</strong> Supprimez ce fichier (<code>cleanup-duplicates.php</code>) après utilisation pour des raisons de sécurité.</p>
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
