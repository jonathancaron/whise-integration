<?php
/**
 * Script de vérification du champ "État du bâtiment"
 * 
 * Affiche les valeurs du champ 'state' pour tous les biens
 * URL: https://votre-site.com/wp-content/plugins/whise-integration/check-state-field.php
 */

// Chargement de WordPress
$paths_to_try = [
    __DIR__ . '/../../../wp-load.php',
    __DIR__ . '/../../../wp-config.php',
    __DIR__ . '/../../../../wp-load.php',
    dirname(dirname(dirname(__DIR__))) . '/wp-load.php',
];

$loaded = false;
foreach ($paths_to_try as $path) {
    if (file_exists($path)) {
        require_once $path;
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    die('❌ Impossible de charger WordPress. Vérifiez le chemin.');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification État du Bâtiment</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        thead {
            background: #f8f9fa;
        }
        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #dee2e6;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .state-distribution {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .state-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .state-item:last-child {
            border-bottom: none;
        }
        .state-name {
            font-weight: 600;
            color: #2c3e50;
        }
        .state-count {
            color: #667eea;
            font-weight: 600;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏗️ Vérification du champ "État du bâtiment"</h1>
        <div class="subtitle">Analyse des valeurs du champ 'state' pour tous les biens</div>

        <?php
        // Récupération de tous les biens
        $properties = get_posts([
            'post_type' => 'property',
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);

        $total = count($properties);
        $with_state = 0;
        $without_state = 0;
        $state_distribution = [];

        $data = [];
        foreach ($properties as $property) {
            $state = get_post_meta($property->ID, 'state', true);
            $state_id = get_post_meta($property->ID, 'state_id', true);
            $whise_id = get_post_meta($property->ID, 'whise_id', true);

            if (!empty($state)) {
                $with_state++;
                if (!isset($state_distribution[$state])) {
                    $state_distribution[$state] = 0;
                }
                $state_distribution[$state]++;
            } else {
                $without_state++;
            }

            $data[] = [
                'id' => $property->ID,
                'title' => $property->post_title,
                'whise_id' => $whise_id,
                'state' => $state,
                'state_id' => $state_id
            ];
        }

        // Tri par état
        arsort($state_distribution);
        ?>

        <!-- Statistiques -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total; ?></div>
                <div class="stat-label">Total de biens</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="stat-number"><?php echo $with_state; ?></div>
                <div class="stat-label">Avec état défini</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);">
                <div class="stat-number"><?php echo $without_state; ?></div>
                <div class="stat-label">Sans état défini</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stat-number"><?php echo count($state_distribution); ?></div>
                <div class="stat-label">États différents</div>
            </div>
        </div>

        <?php if ($without_state > 0) : ?>
            <div class="warning-box">
                <strong>⚠️ Attention :</strong> <?php echo $without_state; ?> bien(s) n'ont pas d'état défini. 
                Lancez une synchronisation pour récupérer ces données.
            </div>
        <?php else : ?>
            <div class="info-box">
                <strong>✅ Parfait :</strong> Tous les biens ont un état défini !
            </div>
        <?php endif; ?>

        <!-- Distribution des états -->
        <?php if (!empty($state_distribution)) : ?>
            <div class="state-distribution">
                <h3 style="margin-bottom: 15px; color: #2c3e50;">📊 Distribution des états</h3>
                <?php foreach ($state_distribution as $state => $count) : ?>
                    <div class="state-item">
                        <span class="state-name"><?php echo esc_html($state); ?></span>
                        <span class="state-count"><?php echo $count; ?> bien(s)</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Tableau détaillé -->
        <h3 style="margin-top: 40px; margin-bottom: 15px; color: #2c3e50;">📋 Liste détaillée des biens</h3>
        <table>
            <thead>
                <tr>
                    <th>ID WP</th>
                    <th>Titre</th>
                    <th>ID Whise</th>
                    <th>État du bâtiment</th>
                    <th>ID État</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row) : ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo esc_html($row['title']); ?></td>
                        <td><?php echo esc_html($row['whise_id']); ?></td>
                        <td>
                            <?php if ($row['state']) : ?>
                                <span class="badge badge-success"><?php echo esc_html($row['state']); ?></span>
                            <?php else : ?>
                                <span class="badge badge-warning">Non défini</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['state_id']) : ?>
                                <span class="badge badge-info"><?php echo esc_html($row['state_id']); ?></span>
                            <?php else : ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['state']) : ?>
                                <span class="badge badge-success">✓</span>
                            <?php else : ?>
                                <span class="badge badge-danger">✗</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="info-box" style="margin-top: 40px;">
            <strong>💡 Utilisation dans les templates :</strong><br><br>
            <code style="background: white; padding: 10px; display: block; border-radius: 4px; font-family: monospace;">
                $state = get_post_meta(get_the_ID(), 'state', true);<br>
                echo esc_html($state);
            </code>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #dcdcde; color: #646970; font-size: 13px; text-align: center;">
            <p>Script de vérification - Whise Integration Plugin</p>
            <p>Pour plus d'informations, consultez <strong>CHAMP_ETAT_BATIMENT.md</strong></p>
        </div>
    </div>
</body>
</html>

