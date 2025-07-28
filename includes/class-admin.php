<?php
if (!defined('ABSPATH')) exit;

class Whise_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_post_whise_manual_sync', [$this, 'manual_sync']);
        add_action('admin_notices', [$this, 'admin_notices']);
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
    }

    public function register_settings() {
        register_setting('whise_options', 'whise_api_username');
        register_setting('whise_options', 'whise_api_password');
        register_setting('whise_options', 'whise_client_id');
        register_setting('whise_options', 'whise_office_id');
        register_setting('whise_options', 'whise_api_endpoint');
    }

    public function settings_page() {
        if (!current_user_can('manage_options')) return;
        $logs = get_option('whise_sync_logs', []);
        if (!is_array($logs)) $logs = [];
        ?>
        <div class="wrap">
            <h1><?php _e('Whise Integration - Paramètres', 'whise-integration'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('whise_options'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Username Marketplace', 'whise-integration'); ?></th>
                        <td><input type="text" name="whise_api_username" value="<?php echo esc_attr(get_option('whise_api_username', '')); ?>" size="50"></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Password Marketplace', 'whise-integration'); ?></th>
                        <td><input type="password" name="whise_api_password" value="<?php echo esc_attr(get_option('whise_api_password', '')); ?>" size="50" autocomplete="off"></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Client ID', 'whise-integration'); ?></th>
                        <td><input type="text" name="whise_client_id" value="<?php echo esc_attr(get_option('whise_client_id', '')); ?>" size="50"></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Office ID', 'whise-integration'); ?></th>
                        <td><input type="text" name="whise_office_id" value="<?php echo esc_attr(get_option('whise_office_id', '')); ?>" size="50"></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Endpoint API Whise', 'whise-integration'); ?></th>
                        <td><input type="text" name="whise_api_endpoint" value="<?php echo esc_attr(get_option('whise_api_endpoint', 'https://api.whise.eu/')); ?>" size="50"></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <hr>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('whise_manual_sync', 'whise_manual_sync_nonce'); ?>
                <input type="hidden" name="action" value="whise_manual_sync">
                <button type="submit" class="button button-primary"><?php _e('Synchroniser maintenant', 'whise-integration'); ?></button>
            </form>
            <hr>
            <h2><?php _e('Logs de synchronisation', 'whise-integration'); ?></h2>
            <div style="max-height:300px;overflow:auto;background:#fff;border:1px solid #ccc;padding:10px;">
                <?php if (empty($logs)) : ?>
                    <em><?php _e('Aucun log pour le moment.', 'whise-integration'); ?></em>
                <?php else : ?>
                    <ul style="font-family:monospace;font-size:13px;">
                        <?php foreach (array_reverse($logs) as $log) : ?>
                            <li><?php echo esc_html($log); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function manual_sync() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        check_admin_referer('whise_manual_sync', 'whise_manual_sync_nonce');
        if (class_exists('Whise_Sync_Manager')) {
            (new Whise_Sync_Manager())->sync_properties_batch();
        }
        wp_redirect(admin_url('admin.php?page=whise-integration&synced=1'));
        exit;
    }

    public function admin_notices() {
        if (isset($_GET['page']) && $_GET['page'] === 'whise-integration' && isset($_GET['synced'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Synchronisation Whise terminée.', 'whise-integration') . '</p></div>';
        }
    }
}
