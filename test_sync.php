<?php
/**
 * Script de test simple pour analyser le mapping des rooms
 */

// Charger les données de test depuis le JSON
$json_data = file_get_contents('response_api_estates_list.json');
$data = json_decode($json_data, true);

if (!$data || empty($data['estates'])) {
    die("Erreur : impossible de charger les données du JSON\n");
}

echo "=== ANALYSE DES DONNÉES ROOMS ===\n\n";

// Analyser tous les biens avec rooms
$count = 0;
foreach ($data['estates'] as $property) {
    if (isset($property['rooms']) && $property['rooms'] > 0) {
        $count++;
        echo "Bien ID: " . $property['id'] . "\n";
        echo "  - Rooms: " . $property['rooms'] . " (type: " . gettype($property['rooms']) . ")\n";
        echo "  - BathRooms: " . ($property['bathRooms'] ?? 'N/A') . "\n";
        echo "  - Name: " . ($property['name'] ?? 'N/A') . "\n";
        echo "  - Reference: " . ($property['referenceNumber'] ?? 'N/A') . "\n";
        echo "---\n";
        
        if ($count >= 5) break; // Limite à 5 exemples
    }
}

echo "\nTotal de biens avec rooms > 0: $count\n";

// Tester la conversion des types
echo "\n=== TEST CONVERSION ===\n";
require_once 'includes/class-sync-manager.php';
$sync_manager = new Whise_Sync_Manager();

// Test de conversion via reflection
$reflection = new ReflectionClass($sync_manager);
$convert_method = $reflection->getMethod('convert_value');
$convert_method->setAccessible(true);

$get_type_method = $reflection->getMethod('get_field_type');
$get_type_method->setAccessible(true);

$test_values = [4, '4', 0, '0', null, ''];
foreach ($test_values as $value) {
    $type = $get_type_method->invoke($sync_manager, 'rooms');
    $converted = $convert_method->invoke($sync_manager, $value, $type);
    echo "Valeur: " . var_export($value, true) . " -> Type: $type -> Converti: " . var_export($converted, true) . " (" . gettype($converted) . ")\n";
}
?>