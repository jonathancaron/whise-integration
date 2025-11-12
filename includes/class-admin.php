<?php
if (!defined('ABSPATH')) exit;

class Whise_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_post_whise_manual_sync', [$this, 'manual_sync']);
        add_action('admin_post_whise_test_connection', [$this, 'test_connection']);
        add_action('admin_post_whise_clear_logs', [$this, 'clear_logs']);
        add_action('admin_post_whise_reset_taxonomies', [$this, 'reset_taxonomies']);
        add_action('admin_post_whise_force_reset', [$this, 'force_reset']);
        add_action('admin_post_whise_cleanup_images', [$this, 'cleanup_images']);
        add_action('admin_post_whise_delete_property_images', [$this, 'delete_property_images']);
        add_action('admin_notices', [$this, 'admin_notices']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function add_menu() {
        add_menu_page(
            __('Whise Integration', 'whise-integration'),
            __('Whise Integration', 'whise-integration'),
            'manage_options',
            'whise-integration',
            [$this, 'settings_page'],
            'dashicons-admin-generic'
        );
        
        add_submenu_page(
            'whise-integration',
            __('Paramètres', 'whise-integration'),
            __('Paramètres', 'whise-integration'),
            'manage_options',
            'whise-integration',
            [$this, 'settings_page']
        );
        
        add_submenu_page(
            'whise-integration',
            __('Statistiques', 'whise-integration'),
            __('Statistiques', 'whise-integration'),
            'manage_options',
            'whise-integration-stats',
            [$this, 'stats_page']
        );
        
        // Ajouter un lien vers les propriétés
        add_submenu_page(
            'whise-integration',
            __('Propriétés', 'whise-integration'),
            __('Propriétés', 'whise-integration'),
            'manage_options',
            'edit.php?post_type=property',
            null
        );
        
        // Ajouter un lien vers l'ajout d'une nouvelle propriété
       /* add_submenu_page(
            'whise-integration',
            __('Ajouter une propriété', 'whise-integration'),
            __('Ajouter une propriété', 'whise-integration'),
            'manage_options',
            'post-new.php?post_type=property',
            null
        );*/
    }

    public function register_settings() {
        register_setting('whise_options', 'whise_api_username');
        register_setting('whise_options', 'whise_api_password');
        register_setting('whise_options', 'whise_client_id');
        register_setting('whise_options', 'whise_office_id');
        register_setting('whise_options', 'whise_api_endpoint');
        register_setting('whise_options', 'whise_sync_frequency', ['default' => 'hourly']);
        register_setting('whise_options', 'whise_sync_enabled', ['default' => true]);
        register_setting('whise_options', 'whise_debug_mode', ['default' => false]);
        register_setting('whise_options', 'whise_image_quality', ['default' => 'urlXXL']);
        register_setting('whise_options', 'whise_cleanup_obsolete', ['default' => true]);
        register_setting('whise_options', 'whise_batch_size', ['default' => 25]);
        register_setting('whise_options', 'whise_skip_image_download', ['default' => false]);
    }

    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'whise-integration') !== false) {
            // Vérifier que les constantes sont définies
            if (defined('WHISE_PLUGIN_URL')) {
                wp_enqueue_script('whise-admin', WHISE_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], '2.0.0', true);
                wp_enqueue_style('whise-admin', WHISE_PLUGIN_URL . 'assets/css/admin.css', [], '2.0.0');
            }
        }
    }

    public function settings_page() {
        if (!current_user_can('manage_options')) return;
        
        $logs = get_option('whise_sync_logs', []);
        if (!is_array($logs)) $logs = [];
        
        // Vérifier que le post type existe avant de faire des requêtes
        if (post_type_exists('property')) {
            $properties = get_posts([
                'post_type' => 'property',
                'post_status' => 'any',
                'numberposts' => -1
            ]);
            
            $total_properties = count($properties);
            $published_properties = count(get_posts([
                'post_type' => 'property',
                'post_status' => 'publish',
                'numberposts' => -1
            ]));
        } else {
            $total_properties = 0;
            $published_properties = 0;
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Whise Integration - Paramètres', 'whise-integration'); ?></h1>
            
            <!-- Statistiques rapides -->
            <div class="whise-stats-overview">
                <div class="stat-box">
                    <h3><?php echo $total_properties; ?></h3>
                    <p><?php _e('Total propriétés', 'whise-integration'); ?></p>
                </div>
                <div class="stat-box">
                    <h3><?php echo $published_properties; ?></h3>
                    <p><?php _e('Propriétés publiées', 'whise-integration'); ?></p>
                </div>
                <div class="stat-box">
                    <h3><?php echo count(array_slice($logs, -1)); ?></h3>
                    <p><?php _e('Dernière sync', 'whise-integration'); ?></p>
                </div>
            </div>
            
            <!-- Configuration API -->
            <div class="whise-section">
                <h2><?php _e('Configuration API', 'whise-integration'); ?></h2>
                <form method="post" action="options.php">
                    <?php settings_fields('whise_options'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Username Marketplace', 'whise-integration'); ?></th>
                            <td><input type="text" name="whise_api_username" value="<?php echo esc_attr(get_option('whise_api_username', '')); ?>" size="50" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Password Marketplace', 'whise-integration'); ?></th>
                            <td><input type="password" name="whise_api_password" value="<?php echo esc_attr(get_option('whise_api_password', '')); ?>" size="50" class="regular-text" autocomplete="off"></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Client ID', 'whise-integration'); ?></th>
                            <td><input type="text" name="whise_client_id" value="<?php echo esc_attr(get_option('whise_client_id', '')); ?>" size="50" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Office ID', 'whise-integration'); ?></th>
                            <td><input type="text" name="whise_office_id" value="<?php echo esc_attr(get_option('whise_office_id', '')); ?>" size="50" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Endpoint API Whise', 'whise-integration'); ?></th>
                            <td><input type="text" name="whise_api_endpoint" value="<?php echo esc_attr(get_option('whise_api_endpoint', 'https://api.whise.eu/')); ?>" size="50" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Fréquence de synchronisation', 'whise-integration'); ?></th>
                            <td>
                                <select name="whise_sync_frequency">
                                    <option value="hourly" <?php selected(get_option('whise_sync_frequency', 'hourly'), 'hourly'); ?>><?php _e('Toutes les heures', 'whise-integration'); ?></option>
                                    <option value="twicedaily" <?php selected(get_option('whise_sync_frequency', 'hourly'), 'twicedaily'); ?>><?php _e('Deux fois par jour', 'whise-integration'); ?></option>
                                    <option value="daily" <?php selected(get_option('whise_sync_frequency', 'hourly'), 'daily'); ?>><?php _e('Une fois par jour', 'whise-integration'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Mode debug', 'whise-integration'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="whise_debug_mode" value="1" <?php checked(get_option('whise_debug_mode', false), true); ?>>
                                    <?php _e('Activer le mode debug (plus de logs)', 'whise-integration'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Qualité des images', 'whise-integration'); ?></th>
                            <td>
                                <select name="whise_image_quality">
                                    <option value="urlXXL" <?php selected(get_option('whise_image_quality', 'urlXXL'), 'urlXXL'); ?>><?php _e('Haute qualité (1600px+)', 'whise-integration'); ?></option>
                                    <option value="urlLarge" <?php selected(get_option('whise_image_quality', 'urlXXL'), 'urlLarge'); ?>><?php _e('Qualité moyenne (640px)', 'whise-integration'); ?></option>
                                    <option value="urlSmall" <?php selected(get_option('whise_image_quality', 'urlXXL'), 'urlSmall'); ?>><?php _e('Qualité réduite (200px)', 'whise-integration'); ?></option>
                                </select>
                                <p class="description"><?php _e('Qualité des images téléchargées depuis Whise. La haute qualité est recommandée pour un affichage optimal.', 'whise-integration'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Nettoyage automatique', 'whise-integration'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="whise_cleanup_obsolete" value="1" <?php checked(get_option('whise_cleanup_obsolete', true), true); ?>>
                                    <?php _e('Supprimer les biens qui ne sont plus dans l\'API Whise', 'whise-integration'); ?>
                                </label>
                                <p class="description"><?php _e('Lors de la synchronisation, supprime automatiquement les biens qui ne sont plus présents dans l\'API Whise. Désactivez cette option si vous voulez conserver tous les biens même s\'ils sont retirés de Whise.', 'whise-integration'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Taille des batches', 'whise-integration'); ?></th>
                            <td>
                                <input type="number" name="whise_batch_size" value="<?php echo esc_attr(get_option('whise_batch_size', 25)); ?>" min="5" max="100" step="5">
                                <p class="description"><?php _e('Nombre de biens traités par page lors de la synchronisation. Réduisez cette valeur si vous rencontrez des timeouts (recommandé: 25).', 'whise-integration'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Téléchargement d\'images', 'whise-integration'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="whise_skip_image_download" value="1" <?php checked(get_option('whise_skip_image_download', false), true); ?>>
                                    <?php _e('Désactiver le téléchargement d\'images (pour éviter les timeouts)', 'whise-integration'); ?>
                                </label>
                                <p class="description"><?php _e('Si activé, les images ne seront pas téléchargées lors de la synchronisation. Utile pour éviter les timeouts sur les serveurs lents. Les images existantes seront conservées.', 'whise-integration'); ?></p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
            
            <!-- Actions -->
            <div class="whise-section">
                <h2><?php _e('Actions', 'whise-integration'); ?></h2>
                <div class="whise-actions">
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;">
                        <?php wp_nonce_field('whise_manual_sync', 'whise_manual_sync_nonce'); ?>
                        <input type="hidden" name="action" value="whise_manual_sync">
                        <button type="submit" class="button button-primary"><?php _e('Synchroniser maintenant', 'whise-integration'); ?></button>
                    </form>
                    
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;">
                        <?php wp_nonce_field('whise_test_connection', 'whise_test_connection_nonce'); ?>
                        <input type="hidden" name="action" value="whise_test_connection">
                        <button type="submit" class="button button-secondary"><?php _e('Tester la connexion', 'whise-integration'); ?></button>
                    </form>
                    
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;">
                        <?php wp_nonce_field('whise_clear_logs', 'whise_clear_logs_nonce'); ?>
                        <input type="hidden" name="action" value="whise_clear_logs">
                        <button type="submit" class="button button-secondary" onclick="return confirm('<?php _e('Êtes-vous sûr de vouloir effacer tous les logs ?', 'whise-integration'); ?>')"><?php _e('Effacer les logs', 'whise-integration'); ?></button>
                    </form>
                    
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;">
                        <?php wp_nonce_field('whise_reset_taxonomies', 'whise_reset_taxonomies_nonce'); ?>
                        <input type="hidden" name="action" value="whise_reset_taxonomies">
                        <button type="submit" class="button button-secondary" onclick="return confirm('<?php _e('Êtes-vous sûr de vouloir réinitialiser les taxonomies ? Cela peut affecter les propriétés existantes.', 'whise-integration'); ?>')"><?php _e('Réinitialiser les taxonomies', 'whise-integration'); ?></button>
                    </form>
                    
                    <a href="<?php echo plugins_url('cleanup-duplicates.php', dirname(__FILE__)); ?>" class="button button-secondary" style="background-color: #ff9800; border-color: #ff9800; color: white;" target="_blank"><?php _e('🧹 Nettoyer les doublons d\'images', 'whise-integration'); ?></a>
                    
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;">
                        <?php wp_nonce_field('whise_force_reset', 'whise_force_reset_nonce'); ?>
                        <input type="hidden" name="action" value="whise_force_reset">
                        <button type="submit" class="button button-secondary" style="background-color: #dc3232; border-color: #dc3232; color: white;" onclick="return confirm('<?php _e('ATTENTION : Cette action va supprimer toutes les données du plugin et forcer une réinitialisation complète. Êtes-vous absolument sûr ?', 'whise-integration'); ?>')"><?php _e('Réinitialisation complète', 'whise-integration'); ?></button>
                    </form>
                    
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline; margin-left: 10px;">
                        <?php wp_nonce_field('whise_cleanup_images', 'whise_cleanup_images_nonce'); ?>
                        <input type="hidden" name="action" value="whise_cleanup_images">
                        <button type="submit" class="button button-secondary" style="background-color: #ff6b35; border-color: #ff6b35; color: white;" onclick="return confirm('<?php _e('ATTENTION : Cette action va supprimer TOUTES les images Whise téléchargées et remettre à zéro les galeries. Êtes-vous sûr de vouloir continuer ?', 'whise-integration'); ?>')"><?php _e('🗑️ Nettoyer toutes les images', 'whise-integration'); ?></button>
                    </form>
                </div>
            </div>
            
            <!-- Supprimer les images d'un bien spécifique -->
            <div class="whise-section">
                <h2><?php _e('Supprimer les images d\'un bien', 'whise-integration'); ?></h2>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="max-width: 500px;">
                    <?php wp_nonce_field('whise_delete_property_images', 'whise_delete_property_images_nonce'); ?>
                    <input type="hidden" name="action" value="whise_delete_property_images">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="whise_property_id"><?php _e('Référence Whise', 'whise-integration'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="whise_property_id" name="whise_property_id" value="" class="regular-text" placeholder="Ex: 7136195" required>
                                <p class="description"><?php _e('Entrez la référence Whise du bien (ex: 7136195)', 'whise-integration'); ?></p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-secondary" style="background-color: #dc3232; border-color: #dc3232; color: white;" onclick="return confirm('<?php _e('ATTENTION : Cette action va supprimer TOUTES les images de ce bien. Êtes-vous sûr de vouloir continuer ?', 'whise-integration'); ?>')">
                            <?php _e('🗑️ Supprimer toutes les images de ce bien', 'whise-integration'); ?>
                        </button>
                    </p>
                </form>
            </div>
            
            <!-- Logs -->
            <div class="whise-section">
                <h2><?php _e('Logs de synchronisation', 'whise-integration'); ?></h2>
                <div class="whise-logs">
                    <?php if (empty($logs)) : ?>
                        <p><em><?php _e('Aucun log pour le moment.', 'whise-integration'); ?></em></p>
                    <?php else : ?>
                        <div class="log-controls">
                            <button class="button" onclick="document.getElementById('whise-logs').style.display = document.getElementById('whise-logs').style.display === 'none' ? 'block' : 'none'">
                                <?php _e('Masquer/Afficher les logs', 'whise-integration'); ?>
                            </button>
                        </div>
                        <div id="whise-logs" style="max-height:400px;overflow:auto;background:#fff;border:1px solid #ccc;padding:15px;margin-top:10px;">
                            <ul style="font-family:monospace;font-size:12px;margin:0;padding:0;list-style:none;">
                                <?php foreach (array_reverse($logs) as $log) : ?>
                                    <li style="margin:2px 0;padding:2px 0;border-bottom:1px solid #eee;"><?php echo esc_html($log); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Informations système -->
            <div class="whise-section">
                <h2><?php _e('Informations système', 'whise-integration'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Prochaine synchronisation', 'whise-integration'); ?></th>
                        <td>
                            <?php 
                            $next_sync = wp_next_scheduled('whise_sync_event');
                            if ($next_sync) {
                                echo date('d/m/Y H:i:s', $next_sync);
                            } else {
                                echo '<em>' . __('Aucune planifiée', 'whise-integration') . '</em>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Dernière synchronisation', 'whise-integration'); ?></th>
                        <td>
                            <?php 
                            $last_sync = get_option('whise_last_sync', '');
                            if ($last_sync) {
                                echo date('d/m/Y H:i:s', strtotime($last_sync));
                            } else {
                                echo '<em>' . __('Jamais', 'whise-integration') . '</em>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Version du plugin', 'whise-integration'); ?></th>
                        <td>2.0.0</td>
                    </tr>
                </table>
            </div>
            
            <!-- Diagnostic des taxonomies -->
            <div class="whise-section">
                <h2><?php _e('Diagnostic des taxonomies', 'whise-integration'); ?></h2>
                <?php
                $taxonomies = [
                    'property_type' => 'Type de bien',
                    'transaction_type' => 'Type de transaction',
                    'property_city' => 'Ville',
                    'property_status' => 'Statut'
                ];
                
                $taxonomies_status = [];
                foreach ($taxonomies as $taxonomy => $label) {
                    $exists = taxonomy_exists($taxonomy);
                    $terms_count = $exists ? wp_count_terms($taxonomy) : 0;
                    $taxonomies_status[$taxonomy] = [
                        'exists' => $exists,
                        'label' => $label,
                        'terms_count' => $terms_count
                    ];
                }
                ?>
                <table class="form-table">
                    <?php foreach ($taxonomies_status as $taxonomy => $status) : ?>
                    <tr>
                        <th scope="row"><?php echo esc_html($status['label']); ?></th>
                        <td>
                            <?php if ($status['exists']) : ?>
                                <span style="color: #46b450;">✅ <?php _e('Enregistrée', 'whise-integration'); ?></span>
                                (<?php echo $status['terms_count']; ?> <?php _e('termes', 'whise-integration'); ?>)
                            <?php else : ?>
                                <span style="color: #dc3232;">❌ <?php _e('Non enregistrée', 'whise-integration'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                
                <?php
                $missing_taxonomies = array_filter($taxonomies_status, function($status) {
                    return !$status['exists'];
                });
                
                if (!empty($missing_taxonomies)) : ?>
                    <div class="notice notice-warning">
                        <p><strong><?php _e('Attention :', 'whise-integration'); ?></strong> <?php _e('Certaines taxonomies ne sont pas enregistrées. Utilisez le bouton "Réinitialiser les taxonomies" ci-dessus.', 'whise-integration'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Diagnostic complet du plugin -->
            <div class="whise-section">
                <h2><?php _e('Diagnostic complet du plugin', 'whise-integration'); ?></h2>
                <?php
                $diagnostic = [
                    'post_type_property' => [
                        'label' => 'Post type "property"',
                        'check' => post_type_exists('property'),
                        'description' => 'Le post type principal pour les propriétés'
                    ],
                    'class_whise_property_cpt' => [
                        'label' => 'Classe Whise_Property_CPT',
                        'check' => class_exists('Whise_Property_CPT'),
                        'description' => 'Classe de gestion du post type'
                    ],
                    'class_whise_admin' => [
                        'label' => 'Classe Whise_Admin',
                        'check' => class_exists('Whise_Admin'),
                        'description' => 'Classe d\'administration'
                    ],
                    'class_whise_sync_manager' => [
                        'label' => 'Classe Whise_Sync_Manager',
                        'check' => class_exists('Whise_Sync_Manager'),
                        'description' => 'Classe de synchronisation'
                    ],
                    'class_whise_api' => [
                        'label' => 'Classe Whise_API',
                        'check' => class_exists('Whise_API'),
                        'description' => 'Classe d\'API'
                    ],
                    'wp_rewrite' => [
                        'label' => 'Règles de réécriture',
                        'check' => !empty(get_option('rewrite_rules')),
                        'description' => 'Règles de réécriture WordPress'
                    ],
                    'plugin_constants' => [
                        'label' => 'Constantes du plugin',
                        'check' => defined('WHISE_PLUGIN_URL') && defined('WHISE_PLUGIN_PATH'),
                        'description' => 'Constantes WHISE_PLUGIN_URL et WHISE_PLUGIN_PATH'
                    ]
                ];
                ?>
                <table class="form-table">
                    <?php foreach ($diagnostic as $key => $item) : ?>
                    <tr>
                        <th scope="row"><?php echo esc_html($item['label']); ?></th>
                        <td>
                            <?php if ($item['check']) : ?>
                                <span style="color: #46b450;">✅ <?php _e('OK', 'whise-integration'); ?></span>
                            <?php else : ?>
                                <span style="color: #dc3232;">❌ <?php _e('Problème', 'whise-integration'); ?></span>
                            <?php endif; ?>
                            <br><small><?php echo esc_html($item['description']); ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                
                <?php
                $problems = array_filter($diagnostic, function($item) {
                    return !$item['check'];
                });
                
                if (!empty($problems)) : ?>
                    <div class="notice notice-error">
                        <p><strong><?php _e('Problèmes détectés :', 'whise-integration'); ?></strong></p>
                        <ul>
                            <?php foreach ($problems as $key => $item) : ?>
                                <li><?php echo esc_html($item['label']); ?> : <?php echo esc_html($item['description']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <p><strong><?php _e('Solutions recommandées :', 'whise-integration'); ?></strong></p>
                        <ol>
                            <li><?php _e('1. Utilisez le bouton "Réinitialiser les taxonomies" ci-dessus', 'whise-integration'); ?></li>
                            <li><?php _e('2. Si cela ne fonctionne pas, utilisez "Réinitialisation complète"', 'whise-integration'); ?></li>
                            <li><?php _e('3. Désactivez puis réactivez le plugin', 'whise-integration'); ?></li>
                            <li><?php _e('4. Vérifiez les logs d\'erreur WordPress', 'whise-integration'); ?></li>
                        </ol>
                    </div>
                <?php else : ?>
                    <div class="notice notice-success">
                        <p><strong><?php _e('✅ Diagnostic complet :', 'whise-integration'); ?></strong> <?php _e('Tous les composants du plugin sont correctement initialisés.', 'whise-integration'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function stats_page() {
        if (!current_user_can('manage_options')) return;
        
        // Vérifier que le post type existe
        if (!post_type_exists('property')) {
            echo '<div class="wrap"><h1>' . __('Statistiques Whise', 'whise-integration') . '</h1>';
            echo '<div class="notice notice-error"><p>' . __('Le post type "property" n\'est pas enregistré.', 'whise-integration') . '</p></div></div>';
            return;
        }
        
        $properties = get_posts([
            'post_type' => 'property',
            'post_status' => 'any',
            'numberposts' => -1
        ]);
        
        $stats = [
            'total' => count($properties),
            'published' => 0,
            'draft' => 0,
            'with_price' => 0,
            'with_surface' => 0,
            'with_address' => 0,
            'with_images' => 0,
            'by_type' => [],
            'by_city' => []
        ];
        
        foreach ($properties as $property) {
            if ($property->post_status === 'publish') $stats['published']++;
            if ($property->post_status === 'draft') $stats['draft']++;
            
            if (get_post_meta($property->ID, 'price', true)) $stats['with_price']++;
            if (get_post_meta($property->ID, 'surface', true)) $stats['with_surface']++;
            if (get_post_meta($property->ID, 'address', true)) $stats['with_address']++;
            if (get_post_meta($property->ID, 'images', true)) $stats['with_images']++;
            
            $city = get_post_meta($property->ID, 'city', true);
            if ($city) {
                $stats['by_city'][$city] = ($stats['by_city'][$city] ?? 0) + 1;
            }
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Statistiques Whise', 'whise-integration'); ?></h1>
            
            <div class="whise-stats-grid">
                <div class="stat-card">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p><?php _e('Total propriétés', 'whise-integration'); ?></p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['published']; ?></h3>
                    <p><?php _e('Publiées', 'whise-integration'); ?></p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['draft']; ?></h3>
                    <p><?php _e('Brouillons', 'whise-integration'); ?></p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['with_price']; ?></h3>
                    <p><?php _e('Avec prix', 'whise-integration'); ?></p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['with_surface']; ?></h3>
                    <p><?php _e('Avec surface', 'whise-integration'); ?></p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['with_images']; ?></h3>
                    <p><?php _e('Avec images', 'whise-integration'); ?></p>
                </div>
            </div>
            
            <?php if (!empty($stats['by_city'])) : ?>
            <div class="whise-section">
                <h2><?php _e('Répartition par ville', 'whise-integration'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Ville', 'whise-integration'); ?></th>
                            <th><?php _e('Nombre de propriétés', 'whise-integration'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['by_city'] as $city => $count) : ?>
                        <tr>
                            <td><?php echo esc_html($city); ?></td>
                            <td><?php echo $count; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function manual_sync() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        check_admin_referer('whise_manual_sync', 'whise_manual_sync_nonce');
        
        try {
            if (class_exists('Whise_Sync_Manager')) {
                $sync_manager = new Whise_Sync_Manager();
                $sync_manager->sync_properties_batch();
                update_option('whise_last_sync', current_time('mysql'));
                wp_redirect(admin_url('admin.php?page=whise-integration&synced=1'));
            } else {
                wp_redirect(admin_url('admin.php?page=whise-integration&error=class_not_found'));
            }
        } catch (Exception $e) {
            wp_redirect(admin_url('admin.php?page=whise-integration&error=sync_failed&message=' . urlencode($e->getMessage())));
        }
        exit;
    }

    public function test_connection() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        check_admin_referer('whise_test_connection', 'whise_test_connection_nonce');
        
        try {
            if (class_exists('Whise_API')) {
                $api = new Whise_API();
                $token = $api->get_client_token();
                
                if ($token) {
                    wp_redirect(admin_url('admin.php?page=whise-integration&test=success'));
                } else {
                    wp_redirect(admin_url('admin.php?page=whise-integration&test=failed'));
                }
            } else {
                wp_redirect(admin_url('admin.php?page=whise-integration&test=error&message=' . urlencode('Classe Whise_API non trouvée')));
            }
        } catch (Exception $e) {
            wp_redirect(admin_url('admin.php?page=whise-integration&test=error&message=' . urlencode($e->getMessage())));
        }
        exit;
    }

    public function clear_logs() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        check_admin_referer('whise_clear_logs', 'whise_clear_logs_nonce');
        
        delete_option('whise_sync_logs');
        wp_redirect(admin_url('admin.php?page=whise-integration&logs_cleared=1'));
        exit;
    }

    public function reset_taxonomies() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        check_admin_referer('whise_reset_taxonomies', 'whise_reset_taxonomies_nonce');
        
        try {
            if (class_exists('Whise_Property_CPT')) {
                $property_cpt = new Whise_Property_CPT();
                $property_cpt->force_taxonomies_reset();
                wp_redirect(admin_url('admin.php?page=whise-integration&taxonomies_reset=1'));
            } else {
                wp_redirect(admin_url('admin.php?page=whise-integration&error=class_not_found'));
            }
        } catch (Exception $e) {
            wp_redirect(admin_url('admin.php?page=whise-integration&error=taxonomies_failed&message=' . urlencode($e->getMessage())));
        }
        exit;
    }

    public function force_reset() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        check_admin_referer('whise_force_reset', 'whise_force_reset_nonce');

        try {
            // Supprimer toutes les options du plugin
            delete_option('whise_api_username');
            delete_option('whise_api_password');
            delete_option('whise_client_id');
            delete_option('whise_office_id');
            delete_option('whise_api_endpoint');
            delete_option('whise_sync_frequency');
            delete_option('whise_sync_enabled');
            delete_option('whise_debug_mode');
            delete_option('whise_last_sync');
            delete_option('whise_sync_logs');
            delete_option('whise_last_visibility_check');

            // Supprimer les taxonomies personnalisées
            $taxonomies_to_delete = [
                'property_type',
                'transaction_type',
                'property_city',
                'property_status'
            ];
            foreach ($taxonomies_to_delete as $taxonomy) {
                if (taxonomy_exists($taxonomy)) {
                    unregister_taxonomy($taxonomy);
                }
            }

            // Supprimer les post types personnalisés
            $post_types_to_delete = [
                'property'
            ];
            foreach ($post_types_to_delete as $post_type) {
                if (post_type_exists($post_type)) {
                    unregister_post_type($post_type);
                }
            }

            // Rediriger vers la page de paramètres avec un message de succès
            wp_redirect(admin_url('admin.php?page=whise-integration&force_reset=1'));
        } catch (Exception $e) {
            wp_redirect(admin_url('admin.php?page=whise-integration&error=force_reset_failed&message=' . urlencode($e->getMessage())));
        }
        exit;
    }

    public function admin_notices() {
        if (isset($_GET['page']) && $_GET['page'] === 'whise-integration') {
            if (isset($_GET['synced'])) {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Synchronisation Whise terminée avec succès.', 'whise-integration') . '</p></div>';
            }
            if (isset($_GET['images_deleted']) && $_GET['images_deleted'] === 'success') {
                $whise_id = isset($_GET['whise_id']) ? esc_html($_GET['whise_id']) : '';
                $deleted = isset($_GET['deleted']) ? (int)$_GET['deleted'] : 0;
                $errors = isset($_GET['errors']) ? (int)$_GET['errors'] : 0;
                echo '<div class="notice notice-success is-dismissible"><p>' . 
                     sprintf(__('Images supprimées avec succès pour le bien %s. Images supprimées: %d, Erreurs: %d', 'whise-integration'), $whise_id, $deleted, $errors) . 
                     '</p></div>';
            }
            if (isset($_GET['error']) && $_GET['error'] === 'property_not_found') {
                $whise_id = isset($_GET['whise_id']) ? esc_html($_GET['whise_id']) : '';
                echo '<div class="notice notice-error is-dismissible"><p>' . 
                     sprintf(__('Erreur : Aucun bien trouvé avec la référence Whise %s', 'whise-integration'), $whise_id) . 
                     '</p></div>';
            }
            if (isset($_GET['error']) && $_GET['error'] === 'no_images') {
                $whise_id = isset($_GET['whise_id']) ? esc_html($_GET['whise_id']) : '';
                echo '<div class="notice notice-warning is-dismissible"><p>' . 
                     sprintf(__('Aucune image trouvée pour le bien %s', 'whise-integration'), $whise_id) . 
                     '</p></div>';
            }
            if (isset($_GET['error']) && $_GET['error'] === 'no_property_id') {
                echo '<div class="notice notice-error is-dismissible"><p>' . 
                     __('Erreur : Veuillez entrer une référence Whise', 'whise-integration') . 
                     '</p></div>';
            }
            
            if (isset($_GET['test']) && $_GET['test'] === 'success') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Test de connexion réussi ! L\'API Whise est accessible.', 'whise-integration') . '</p></div>';
            }
            
            if (isset($_GET['test']) && $_GET['test'] === 'failed') {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Test de connexion échoué. Vérifiez vos identifiants API.', 'whise-integration') . '</p></div>';
            }
            
            if (isset($_GET['test']) && $_GET['test'] === 'error') {
                $message = isset($_GET['message']) ? urldecode($_GET['message']) : 'Erreur inconnue';
                echo '<div class="notice notice-error is-dismissible"><p>' . sprintf(__('Erreur lors du test de connexion : %s', 'whise-integration'), esc_html($message)) . '</p></div>';
            }
            
            if (isset($_GET['logs_cleared'])) {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Logs effacés avec succès.', 'whise-integration') . '</p></div>';
            }
            
            if (isset($_GET['error']) && $_GET['error'] === 'sync_failed') {
                $message = isset($_GET['message']) ? urldecode($_GET['message']) : 'Erreur inconnue';
                echo '<div class="notice notice-error is-dismissible"><p>' . sprintf(__('Échec de la synchronisation : %s', 'whise-integration'), esc_html($message)) . '</p></div>';
            }
            
            if (isset($_GET['error']) && $_GET['error'] === 'class_not_found') {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Erreur : Classe Whise_Sync_Manager non trouvée.', 'whise-integration') . '</p></div>';
            }

            if (isset($_GET['taxonomies_reset'])) {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Réinitialisation des taxonomies Whise terminée avec succès.', 'whise-integration') . '</p></div>';
            }

            if (isset($_GET['error']) && $_GET['error'] === 'taxonomies_failed') {
                $message = isset($_GET['message']) ? urldecode($_GET['message']) : 'Erreur inconnue';
                echo '<div class="notice notice-error is-dismissible"><p>' . sprintf(__('Échec de la réinitialisation des taxonomies : %s', 'whise-integration'), esc_html($message)) . '</p></div>';
            }

            if (isset($_GET['force_reset'])) {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Réinitialisation complète du plugin Whise terminée avec succès.', 'whise-integration') . '</p></div>';
            }

            if (isset($_GET['error']) && $_GET['error'] === 'force_reset_failed') {
                $message = isset($_GET['message']) ? urldecode($_GET['message']) : 'Erreur inconnue';
                echo '<div class="notice notice-error is-dismissible"><p>' . sprintf(__('Échec de la réinitialisation complète du plugin : %s', 'whise-integration'), esc_html($message)) . '</p></div>';
            }
            
            if (isset($_GET['cleanup']) && $_GET['cleanup'] === 'success') {
                $deleted = isset($_GET['deleted']) ? intval($_GET['deleted']) : 0;
                $errors = isset($_GET['errors']) ? intval($_GET['errors']) : 0;
                $galleries = isset($_GET['galleries']) ? intval($_GET['galleries']) : 0;
                $total = isset($_GET['total']) ? intval($_GET['total']) : 0;
                echo '<div class="notice notice-success is-dismissible"><p>';
                echo sprintf(__('Nettoyage des images terminé ! Images supprimées : %d, Erreurs : %d, Galeries nettoyées : %d, Total traité : %d', 'whise-integration'), $deleted, $errors, $galleries, $total);
                echo '</p></div>';
            }
        }
    }

    /**
     * Nettoie toutes les images Whise et remet à zéro les galeries
     * Version optimisée pour éviter les timeouts
     */
    public function cleanup_images() {
        if (!current_user_can('manage_options')) {
            wp_die('Accès refusé');
        }

        // Vérification de sécurité
        if (!isset($_POST['whise_cleanup_images_nonce']) || !wp_verify_nonce($_POST['whise_cleanup_images_nonce'], 'whise_cleanup_images')) {
            wp_die('Erreur de sécurité');
        }

        // Augmenter les limites pour éviter les timeouts
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        ini_set('max_input_time', -1);

        $this->log('--- Début nettoyage des images Whise : ' . date('Y-m-d H:i:s'));
        
        // Traitement par lots pour éviter les timeouts
        $batch_size = 20; // Traiter 20 images à la fois
        $offset = 0;
        $deleted_count = 0;
        $error_count = 0;
        $total_processed = 0;

        do {
            // Récupérer les attachments par lots
            $whise_attachments = get_posts([
                'post_type' => 'attachment',
                'meta_key' => '_whise_original_url',
                'numberposts' => $batch_size,
                'offset' => $offset,
                'post_status' => 'any'
            ]);

            if (empty($whise_attachments)) {
                break; // Plus d'attachments à traiter
            }

            foreach ($whise_attachments as $attachment) {
                $attachment_id = $attachment->ID;
                $file_path = get_attached_file($attachment_id);
                
                // Supprimer le fichier physique
                if ($file_path && file_exists($file_path)) {
                    wp_delete_file($file_path);
                }
                
                // Supprimer l'attachment de la base de données
                $deleted = wp_delete_attachment($attachment_id, true);
                
                if ($deleted) {
                    $deleted_count++;
                    $this->log('SUCCESS - Image supprimée: ' . $attachment->post_title . ' (ID: ' . $attachment_id . ')');
                } else {
                    $error_count++;
                    $this->log('ERROR - Échec suppression image: ' . $attachment->post_title . ' (ID: ' . $attachment_id . ')');
                }
                
                $total_processed++;
            }

            $offset += $batch_size;
            
            // Pause pour éviter la surcharge
            usleep(100000); // 0.1 seconde
            
            // Log de progression
            if ($total_processed % 100 == 0) {
                $this->log('INFO - Progression: ' . $total_processed . ' images traitées, ' . $deleted_count . ' supprimées');
            }

        } while (count($whise_attachments) == $batch_size);

        // Nettoyer les métadonnées de galerie par lots aussi
        $properties_offset = 0;
        $gallery_cleaned = 0;
        $properties_batch_size = 50;

        do {
            $properties = get_posts([
                'post_type' => 'property',
                'numberposts' => $properties_batch_size,
                'offset' => $properties_offset,
                'post_status' => 'any'
            ]);

            if (empty($properties)) {
                break;
            }

            foreach ($properties as $property) {
                // Supprimer la métadonnée de galerie
                delete_post_meta($property->ID, '_whise_gallery_images');
                
                // Supprimer l'image mise en avant si c'était une image Whise
                $thumbnail_id = get_post_thumbnail_id($property->ID);
                if ($thumbnail_id) {
                    $is_whise_image = get_post_meta($thumbnail_id, '_whise_original_url', true);
                    if ($is_whise_image) {
                        delete_post_thumbnail($property->ID);
                        $this->log('DEBUG - Featured image supprimée pour: ' . $property->post_title);
                    }
                }
                
                $gallery_cleaned++;
            }

            $properties_offset += $properties_batch_size;
            usleep(50000); // 0.05 seconde

        } while (count($properties) == $properties_batch_size);

        $this->log('INFO - Nettoyage terminé. Images supprimées: ' . $deleted_count . ', Erreurs: ' . $error_count . ', Galeries nettoyées: ' . $gallery_cleaned . ', Total traité: ' . $total_processed);

        // Redirection avec message de succès
        wp_redirect(admin_url('admin.php?page=whise-integration&cleanup=success&deleted=' . $deleted_count . '&errors=' . $error_count . '&galleries=' . $gallery_cleaned . '&total=' . $total_processed));
        exit;
    }

    public function delete_property_images() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        check_admin_referer('whise_delete_property_images', 'whise_delete_property_images_nonce');
        
        $whise_id = isset($_POST['whise_property_id']) ? sanitize_text_field($_POST['whise_property_id']) : '';
        
        if (empty($whise_id)) {
            wp_redirect(admin_url('admin.php?page=whise-integration&error=no_property_id'));
            exit;
        }
        
        // Trouver le bien par sa référence Whise
        $properties = get_posts([
            'post_type' => 'property',
            'meta_key' => 'whise_id',
            'meta_value' => $whise_id,
            'post_status' => 'any',
            'numberposts' => 1
        ]);
        
        if (empty($properties)) {
            wp_redirect(admin_url('admin.php?page=whise-integration&error=property_not_found&whise_id=' . urlencode($whise_id)));
            exit;
        }
        
        $property = $properties[0];
        $post_id = $property->ID;
        
        // Récupérer toutes les images attachées au post (pas seulement celles dans _whise_gallery_images)
        $gallery_ids = get_post_meta($post_id, '_whise_gallery_images', true);
        if (!is_array($gallery_ids)) {
            $gallery_ids = [];
        }
        
        // Récupérer aussi toutes les images attachées directement au post
        $all_attached_images = get_posts([
            'post_type' => 'attachment',
            'post_parent' => $post_id,
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        
        // Fusionner les deux listes pour avoir toutes les images
        $all_images = array_unique(array_merge($gallery_ids, $all_attached_images));
        
        if (empty($all_images)) {
            wp_redirect(admin_url('admin.php?page=whise-integration&error=no_images&whise_id=' . urlencode($whise_id)));
            exit;
        }
        
        $deleted_count = 0;
        $error_count = 0;
        
        $this->log('INFO - Suppression des images du bien ' . $whise_id . ' (Post ID: ' . $post_id . ') - ' . count($all_images) . ' image(s) trouvée(s)');
        
        // Supprimer toutes les images
        foreach ($all_images as $attachment_id) {
            if (empty($attachment_id)) continue;
            
            $file_path = get_attached_file($attachment_id);
            $deleted = wp_delete_attachment($attachment_id, true);
            
            if ($deleted) {
                $deleted_count++;
                $this->log('INFO - Image supprimée: attachment ID ' . $attachment_id);
                
                // Supprimer aussi le fichier physique et ses variantes
                if ($file_path && file_exists($file_path)) {
                    @unlink($file_path);
                    $path_info = pathinfo($file_path);
                    if (isset($path_info['filename']) && isset($path_info['dirname']) && isset($path_info['extension'])) {
                        $filename_without_ext = $path_info['filename'];
                        for ($i = 1; $i <= 10; $i++) {
                            $variant_path = $path_info['dirname'] . '/' . $filename_without_ext . '-' . $i . '.' . $path_info['extension'];
                            if (file_exists($variant_path)) {
                                @unlink($variant_path);
                            }
                        }
                    }
                }
            } else {
                $error_count++;
                $this->log('ERROR - Échec suppression image: attachment ID ' . $attachment_id);
            }
        }
        
        // Supprimer la métadonnée de galerie
        delete_post_meta($post_id, '_whise_gallery_images');
        
        // Supprimer l'image mise en avant
        delete_post_thumbnail($post_id);
        
        $this->log('INFO - Suppression terminée pour le bien ' . $whise_id . '. Images supprimées: ' . $deleted_count . ', Erreurs: ' . $error_count);
        
        // Redirection avec message de succès
        wp_redirect(admin_url('admin.php?page=whise-integration&images_deleted=success&whise_id=' . urlencode($whise_id) . '&deleted=' . $deleted_count . '&errors=' . $error_count));
        exit;
    }

    private function log($msg) {
        $logs = get_option('whise_sync_logs', []);
        if (!is_array($logs)) $logs = [];
        $logs[] = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
        // Garde les 100 derniers logs max
        if (count($logs) > 100) $logs = array_slice($logs, -100);
        update_option('whise_sync_logs', $logs);
    }
}
