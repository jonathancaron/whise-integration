# 📋 Liste complète des champs Whise disponibles

## 🎯 Vue d'ensemble

Ce document liste **TOUS** les champs récupérés depuis l'API Whise et enregistrés dans WordPress.

**Utilisation dans les templates :**
```php
$valeur = get_post_meta(get_the_ID(), 'nom_du_champ', true);
```

---

## 📑 Organisation par sections

### 🆔 **Identification**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Whise ID | `whise_id` | string | ID unique Whise du bien |
| Référence | `reference` | string | Numéro de référence |
| Client ID | `client_id` | number | ID du client Whise |
| Client Name | `client_name` | string | Nom du client |
| Office ID | `office_id` | number | ID du bureau |
| Office Name | `office_name` | string | Nom du bureau |

---

### 🏷️ **Type et catégorie**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Type de propriété | `property_type` | string | Ex: Appartement, Maison |
| Type de propriété ID | `property_type_id` | string | ID Whise du type |
| Sous-catégorie | `sub_category` | string | Ex: Studio, Duplex |
| Sous-catégorie ID | `sub_category_id` | string | ID Whise |
| Type de transaction | `transaction_type` | string | Vente ou Location |
| Type de transaction ID | `transaction_type_id` | string | ID Whise (1=Vente, 2=Location) |

---

### 📊 **État et statut**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| État du bâtiment | `state` | string | Ex: Bon état, À rénover |
| État du bâtiment ID | `state_id` | string | ID Whise de l'état |
| Statut | `status` | string | Ex: Disponible, Vendu |
| Statut ID | `status_id` | string | ID Whise du statut |
| Purpose Status | `purpose_status` | string | Statut de transaction Whise |
| Purpose Status ID | `purpose_status_id` | number | ID du statut |
| Transaction Status | `transaction_status` | string | Statut simplifié |
| Année de construction | `construction_year` | number | Année (ex: 1995) |
| Année de rénovation | `renovation_year` | number | Année de rénovation |

---

### 💰 **Prix et conditions**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Prix | `price` | number | Prix numérique |
| Prix formaté | `price_formatted` | string | Ex: €350.000 |
| Type de prix | `price_type` | string | vente ou location |
| Supplément de prix | `price_supplement` | string | Infos supplémentaires |
| Charges | `charges` | number | Charges mensuelles/annuelles |
| Conditions de prix | `price_conditions` | string | Conditions particulières |
| Prix par m² | `price_per_sqm` | number | Prix au m² |
| Devise | `currency` | string | €, $, etc. |

---

### 📐 **Surfaces**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Surface habitable | `surface` | number | En m² |
| Surface totale | `total_area` | number | En m² |
| Surface terrain | `land_area` | number | En m² |
| Surface commerciale | `commercial_area` | number | En m² |
| Surface bâtie | `built_area` | number | En m² |
| Surface minimum | `min_area` | number | En m² |
| Surface maximum | `max_area` | number | En m² |
| Surface terrain (ground) | `ground_area` | number | En m² |
| Surface nette | `net_area` | number | En m² |
| Surface jardin | `garden_area` | number | En m² |

---

### 🚪 **Pièces et espaces**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Nombre de pièces | `rooms` | number | Total de pièces |
| Nombre de chambres | `bedrooms` | number | Chambres à coucher |
| Salles de bain | `bathrooms` | number | Nombre de SDB |
| Étages | `floors` | number | Nombre d'étages |
| Nombre d'étages (détail) | `number_of_floors` | number | Détail Whise |
| Nombre de toilettes | `number_of_toilets` | number | WC séparés |
| Façades | `fronts` | number | Nombre de façades |

---

### 📍 **Localisation**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Adresse | `address` | string | Rue complète |
| Numéro | `number` | string | Numéro de rue |
| Boîte | `box` | string | Numéro de boîte |
| Code postal | `zip` | string | Code postal |
| Ville | `city` | string | Nom de la ville |
| Code postal (alt) | `postal_code` | string | Alternative |
| Pays | `country` | string | Pays |
| Latitude | `latitude` | float | Coordonnée GPS |
| Longitude | `longitude` | float | Coordonnée GPS |

---

### ⚡ **Énergie et chauffage**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Classe énergétique | `energy_class` | string | A, B, C, D, etc. |
| Valeur PEB | `epc_value` | number | Valeur numérique |
| Type de chauffage | `heating_type` | string | Ex: Gaz, Mazout |
| Groupe de chauffage | `heating_group` | string | Catégorie |
| Électricité | `electricity` | boolean | Présence électricité |
| Citerne à mazout | `oil_tank` | boolean | Présence citerne |
| Isolation | `insulation` | boolean | Bien isolé |

---

### 🏛️ **Données cadastrales**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Revenu cadastral | `cadastral_income` | number | RC annuel |
| Données cadastrales | `cadastral_data` | array | Données complètes |

---

### 🛠️ **Équipements de base**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Type de cuisine | `kitchen_type` | string | Ex: Équipée, US |
| Parking | `parking` | boolean | ✓/✗ |
| Garage | `garage` | boolean | ✓/✗ |
| Terrasse | `terrace` | boolean | ✓/✗ |
| Jardin | `garden` | boolean | ✓/✗ |
| Piscine | `swimming_pool` | boolean | ✓/✗ |
| Ascenseur | `elevator` | boolean | ✓/✗ |
| Cave | `cellar` | boolean | ✓/✗ |
| Grenier | `attic` | boolean | ✓/✗ |
| Meublé | `furnished` | boolean | ✓/✗ |

---

### ⭐ **Équipements de confort**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Climatisation | `air_conditioning` | boolean | ✓/✗ |
| Double vitrage | `double_glazing` | boolean | ✓/✗ |
| Alarme | `alarm` | boolean | ✓/✗ |
| Concierge | `concierge` | boolean | ✓/✗ |
| Téléphone | `telephone` | boolean | ✓/✗ |
| Standard téléphonique | `telephone_central` | boolean | ✓/✗ |

---

### 📋 **Équipements réglementaires**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Toilettes M/F | `toilets_mf` | boolean | Séparation H/F |
| Régime TVA | `vta_regime` | boolean | Soumis à TVA |
| Permis de bâtir | `building_permit` | boolean | Obtenu |
| Permis de lotir | `subdivision_permit` | boolean | Obtenu |
| Procédure judiciaire | `ongoing_judgment` | boolean | En cours |

---

### 🏫 **Proximité**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Écoles | `proximity_school` | string | Distance/description |
| Commerces | `proximity_shops` | string | Distance/description |
| Transports | `proximity_transport` | string | Distance/description |
| Hôpital | `proximity_hospital` | string | Distance/description |
| Centre-ville | `proximity_city_center` | string | Distance/description |

---

### 🧭 **Orientation et environnement**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Orientation | `orientation` | string | Ex: Sud, Est |
| Vue | `view` | string | Ex: Dégagée, Jardin |
| Orientation bâtiment | `building_orientation` | string | Orientation générale |
| Type d'environnement | `environment_type` | string | Ex: Urbain, Rural |

---

### 📅 **Disponibilité**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Disponibilité | `availability` | string | Ex: Immédiate |
| Disponible immédiatement | `is_immediately_available` | boolean | ✓/✗ |
| Date de disponibilité | `available_date` | string | Date au format texte |

---

### 🏢 **Bureaux (spécifique)**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Bureau 1 | `office_1` | number | Nombre ou surface |
| Bureau 2 | `office_2` | number | Nombre ou surface |
| Bureau 3 | `office_3` | number | Nombre ou surface |

---

### 🔨 **Matériaux et finitions**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Matériau du sol | `floor_material` | string | Ex: Parquet, Carrelage |
| Destination du terrain | `ground_destination` | string | Usage prévu |

---

### 📏 **Dimensions détaillées**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Largeur de façade | `width_of_facade` | number | En mètres |
| Profondeur du terrain | `depth_of_land` | number | En mètres |
| Largeur front de rue | `width_of_street_front` | number | En mètres |
| Surface bâtie (détail) | `built_area_detail` | number | En m² |

---

### 📆 **Dates importantes**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Date de création | `create_date` | string | Date au format texte |
| Date de mise à jour | `update_date` | string | Dernière modif |
| Date de mise en ligne | `put_online_date` | string | Publication |
| Date changement prix | `price_change_date` | string | Dernier changement |

---

### 📝 **Descriptions**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Description | `description` | string | Description complète (HTML) |
| Description courte | `short_description` | string | Résumé |
| Description SMS | `sms_description` | string | Version très courte |

---

### 🎬 **Médias et liens**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Lien modèle 3D | `link_3d_model` | string | URL vers modèle 3D |
| Lien visite virtuelle | `link_virtual_visit` | string | URL visite 360° |
| Lien vidéo | `link_video` | string | URL vidéo YouTube/Vimeo |

---

### 🖼️ **Images**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Images | `images` | array | Tableau des images |
| Galerie Whise | `_whise_gallery_images` | array | IDs attachments WP |

---

### 👔 **Représentant**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Représentant ID | `representative_id` | number | ID Whise |
| Nom | `representative_name` | string | Nom complet |
| Email | `representative_email` | string | Adresse email |
| Téléphone | `representative_phone` | string | Numéro fixe |
| Mobile | `representative_mobile` | string | Numéro mobile |
| Fonction | `representative_function` | string | Titre/poste |
| Photo | `representative_picture` | string | URL de la photo |

---

### 🌍 **Multilingue**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Descriptions multilingues | `descriptions_multilingual` | array | Contenu par langue |

---

### 🔧 **Détails avancés**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Surface nette | `net_area` | number | En m² |
| Surface jardin | `garden_area` | number | En m² |
| Charges locataire | `tenant_charges` | number | Charges mensuelles |
| Profession libérale | `professional_liberal_possibility` | number | Possibilité |
| Salle de fitness | `fitness_room_area` | number | Surface en m² |
| Langue type propriété | `property_type_language` | string | Code langue |
| Langue transaction | `transaction_type_language` | string | Code langue |
| Langue statut | `status_language` | string | Code langue |

---

### 🔍 **Données techniques (debug)**
| Nom du champ | Meta Key | Type | Description |
|--------------|----------|------|-------------|
| Détails bruts Whise | `details` | array | Tous les détails de l'API |

---

## 📊 Total des champs

**Total : ~120+ champs disponibles** répartis en :
- ✅ Champs de base : ~40
- ✅ Champs équipements : ~20
- ✅ Champs surfaces : ~10
- ✅ Champs localisation : ~10
- ✅ Champs représentant : ~7
- ✅ Champs avancés : ~40+

---

## 💡 Exemples d'utilisation

### **Afficher le prix formaté :**
```php
<?php 
$prix = get_post_meta(get_the_ID(), 'price_formatted', true);
echo $prix; // Affiche : €350.000
?>
```

### **Vérifier si le bien a un garage :**
```php
<?php 
$garage = get_post_meta(get_the_ID(), 'garage', true);
if ($garage) {
    echo '✓ Garage disponible';
}
?>
```

### **Afficher l'état du bâtiment :**
```php
<?php 
$etat = get_post_meta(get_the_ID(), 'state', true);
echo 'État : ' . $etat; // Affiche : État : Bon état
?>
```

### **Récupérer toutes les surfaces :**
```php
<?php 
$surface = get_post_meta(get_the_ID(), 'surface', true);
$total_area = get_post_meta(get_the_ID(), 'total_area', true);
$land_area = get_post_meta(get_the_ID(), 'land_area', true);

echo "Surface habitable : {$surface} m²<br>";
echo "Surface totale : {$total_area} m²<br>";
echo "Terrain : {$land_area} m²";
?>
```

### **Afficher le représentant :**
```php
<?php 
$nom = get_post_meta(get_the_ID(), 'representative_name', true);
$email = get_post_meta(get_the_ID(), 'representative_email', true);
$phone = get_post_meta(get_the_ID(), 'representative_phone', true);

echo "<div class='agent'>";
echo "<h3>{$nom}</h3>";
echo "<a href='mailto:{$email}'>{$email}</a><br>";
echo "<a href='tel:{$phone}'>{$phone}</a>";
echo "</div>";
?>
```

---

## 🎨 Utilisation dans Elementor

### **Dynamic Tags disponibles :**

1. Créer un widget **Dynamic Text** ou **Dynamic Number**
2. Source : **Post Meta**
3. Meta Key : Choisir parmi la liste ci-dessus

**Exemples :**
- Prix : `price_formatted`
- Surface : `surface`
- Chambres : `bedrooms`
- État : `state`
- Ville : `city`

---

## 📖 Documentation complémentaire

- **`CHAMP_ETAT_BATIMENT.md`** : Documentation du champ `state`
- **`CORRECTION_STATE_V2.md`** : Fix récent pour le champ état
- **Admin WordPress** : Voir tous les champs dans "Détails du bien"

---

## 🔄 Mise à jour

Ces champs sont **synchronisés automatiquement** depuis Whise lors de :
- La synchronisation manuelle (admin Whise Integration)
- La synchronisation automatique (cron quotidien)

Pour forcer une synchronisation :
```
Tableau de bord > Whise Integration > Lancer la synchronisation
```

---

**Date de création :** 24 octobre 2025  
**Dernière mise à jour :** 24 octobre 2025  
**Version du plugin :** 1.2.0


