# Changelog - Intégration Whise

## Version 2.0.0 - 2025-08-04

### Améliorations majeures du mapping des champs

#### Nouveaux champs ajoutés

**Prix et conditions :**
- `price_per_sqm` : Prix par m²/an (ID 338)

**Surfaces :**
- `min_area` : Surface minimale
- `max_area` : Surface maximale  
- `ground_area` : Surface du terrain

**Pièces :**
- `bedrooms` : Nombre de chambres

**Type et statut :**
- `renovation_year` : Année de rénovation (ID 585)

**Localisation :**
- `box` : Boîte postale
- `number` : Numéro de rue
- `zip` : Code postal

**Énergie :**
- `heating_group` : Type de chauffage (individuel/collectif) (ID 53)

**Équipements (nouveaux) :**
- `furnished` : Meublé
- `air_conditioning` : Climatisation (ID 43)
- `double_glazing` : Double vitrage (ID 461)
- `alarm` : Alarme (ID 1752)
- `concierge` : Concierge (ID 1762)
- `telephone` : Téléphone (ID 729)
- `telephone_central` : Centrale téléphonique (ID 734)
- `electricity` : Électricité (ID 757)
- `oil_tank` : Citerne à mazout (ID 758)
- `insulation` : Isolation (ID 778)
- `toilets_mf` : Toilettes H/F (ID 380)
- `vta_regime` : Sous régime TVA (ID 574)
- `building_permit` : Permis de bâtir (ID 808)
- `subdivision_permit` : Permis de lotir (ID 812)
- `ongoing_judgment` : Jugement en cours (ID 691)

**Proximité :**
- `proximity_city_center` : Distance du centre-ville (ID 111)

**Orientation et vues :**
- `building_orientation` : Orientation du bâtiment (ID 23)
- `environment_type` : Type d'environnement (ID 795)

**Bureaux spécifiques :**
- `office_1` : Surface bureau 1 (ID 1494)
- `office_2` : Surface bureau 2 (ID 1495)
- `office_3` : Surface bureau 3 (ID 1496)

**Matériaux et finitions :**
- `floor_material` : Revêtement de sol (ID 1617)
- `ground_destination` : Affectation urbanistique (ID 1736)

**Dates :**
- `create_date` : Date de création
- `update_date` : Date de mise à jour
- `put_online_date` : Date de mise en ligne
- `price_change_date` : Date de changement de prix

**Informations client :**
- `client_id` : ID du client
- `office_id` : ID du bureau
- `client_name` : Nom du client
- `office_name` : Nom du bureau

**Descriptions :**
- `short_description` : Description courte (fr-BE)
- `sms_description` : Description SMS (fr-BE)

**Images :**
- `images` : Tableau complet des images avec URLs

**Détails complets :**
- `details` : Tableau complet des détails techniques

#### Améliorations techniques

1. **Nouvelles fonctions de recherche :**
   - `findDetailValueById()` : Recherche par ID de détail
   - `findDetailValueByGroup()` : Recherche par groupe et label

2. **Mapping amélioré des coordonnées :**
   - Latitude : ID 1849 (x de coordonnées xy)
   - Longitude : ID 1850 (y de coordonnées xy)

3. **Traitement des descriptions multilingues :**
   - Extraction automatique des descriptions en français (fr-BE)
   - Support des descriptions courtes et SMS

4. **Mapping correct des objets imbriqués :**
   - `category.id` pour le type de propriété
   - `purpose.id` pour le type de transaction
   - `status.id` pour le statut
   - `availability.id` pour la disponibilité

#### Corrections de bugs

- Correction du mapping des champs de base (area, zip, etc.)
- Amélioration de la gestion des valeurs nulles
- Correction du mapping des taxonomies

### Utilisation des nouveaux champs

Pour accéder aux nouveaux champs dans vos templates :

```php
// Exemple d'utilisation
$price_per_sqm = get_post_meta($post_id, 'price_per_sqm', true);
$construction_year = get_post_meta($post_id, 'construction_year', true);
$air_conditioning = get_post_meta($post_id, 'air_conditioning', true);
$images = get_post_meta($post_id, 'images', true);

// Pour les détails complets
$details = get_post_meta($post_id, 'details', true);
```

### Compatibilité

Cette version est compatible avec les versions précédentes. Les anciens champs restent disponibles. 