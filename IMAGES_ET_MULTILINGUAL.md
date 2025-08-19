# Documentation : Améliorations Images et Multilingue

## 🎯 Améliorations Implémentées

### ✅ 1. Featured Image (Image par défaut)
**Problème résolu** : Aucune image par défaut n'était définie automatiquement.

**Solution** :
- La première image (selon l'ordre Whise) est automatiquement définie comme featured image
- Intégration complète avec `has_post_thumbnail()` et `get_the_post_thumbnail()`
- Compatible avec Elementor Pro et tous les thèmes WordPress

**Utilisation** :
```php
// Dans les templates
if (has_post_thumbnail()) {
    the_post_thumbnail('medium');
}

// Ou avec la fonction helper
$featured_url = whise_get_property_featured_image(get_the_ID(), 'large');
```

### ✅ 2. Galerie d'Images comme Attachments WordPress
**Problème résolu** : Images stockées uniquement comme URLs externes.

**Solution** :
- Téléchargement automatique des images lors de la synchronisation
- Création d'attachments WordPress pour chaque image
- Stockage local dans `/wp-content/uploads/`
- Métadonnées complètes (titre, ordre, URL originale)
- Prévention des doublons par URL

**Utilisation** :
```php
// Récupérer la galerie
$gallery = whise_get_property_gallery($post_id);

// Affichage simple
whise_display_property_gallery($post_id, 'medium');

// Affichage personnalisé
foreach ($gallery as $image) {
    echo '<img src="' . $image['medium'] . '" alt="' . $image['alt'] . '">';
}
```

### ✅ 3. Support Multilingue Complet
**Problème résolu** : Seule la langue `fr-BE` était supportée.

**Solution** :
- Support de toutes les langues disponibles dans l'API Whise
- Système de fallback intelligent (`fr-BE` → `nl-BE` → `en-BE` → première disponible)
- Compatible WPML et Polylang
- Stockage des descriptions dans toutes les langues

**Langues supportées** :
- `fr-BE` (Français Belgique)
- `nl-BE` (Néerlandais Belgique) 
- `en-BE` (Anglais Belgique)
- `de-BE` (Allemand Belgique)

**Utilisation** :
```php
// Description dans la langue courante
$description = whise_get_property_description('shortDescription');

// Description dans une langue spécifique
$description_nl = whise_get_property_description('shortDescription', 'nl-BE');

// Toutes les descriptions multilingues
$all_descriptions = get_post_meta($post_id, 'descriptions_multilingual', true);
```

## 🔧 Nouvelles Métadonnées Créées

### Propriétés
- `_whise_gallery_images` : Array des IDs d'attachments de la galerie
- `descriptions_multilingual` : Array des descriptions dans toutes les langues

### Attachments d'Images
- `_whise_original_url` : URL originale Whise (pour éviter les doublons)
- `_whise_image_id` : ID de l'image dans Whise
- `_whise_image_order` : Ordre de l'image (pour le tri)

## 🎨 Templates et Affichage

### Interface d'Administration
- **Nouvelle section** "Descriptions multilingues" dans les détails de propriété
- **Galerie améliorée** avec lightbox et compteur d'images
- **Indicateurs visuels** pour différencier nouvelles images vs anciennes URLs

### CSS Inclus
```css
.whise-gallery-grid { /* Grid responsive pour galerie */ }
.whise-gallery-item { /* Item de galerie avec hover effect */ }
.whise-multilingual-descriptions { /* Section descriptions multilingues */ }
.whise-description-lang { /* Description par langue */ }
```

## 🚀 Fonctions Helper Disponibles

```php
// Images
whise_get_property_gallery($post_id = null)
whise_get_property_featured_image($post_id = null, $size = 'medium')
whise_display_property_gallery($post_id = null, $size = 'medium')

// Descriptions multilingues
whise_get_property_description($field = 'shortDescription', $language = null, $post_id = null)
```

## 🔄 Migration et Compatibilité

### Rétrocompatibilité
- **Maintenue** : Ancien système d'URLs encore supporté en fallback
- **Indicateurs** : Messages visuels pour identifier l'ancienne vs nouvelle méthode
- **Aucune perte** : Les données existantes restent accessibles

### Migration Automatique
- **Lors de la prochaine synchronisation** : Toutes les propriétés auront leurs images téléchargées
- **Progressif** : Migration se fait propriété par propriété lors des mises à jour
- **Logs détaillés** : Suivi complet dans les logs Whise

## 📊 Performance et Optimisation

### Optimisations Images
- **Cache local** : Images stockées localement (plus rapide)
- **Prévention doublons** : Vérification par URL avant téléchargement
- **Tailles multiples** : WordPress génère automatiquement les thumbnails
- **Timeout sécurisé** : 30 secondes max par image

### Optimisations Multilingues
- **Stockage efficient** : Une seule métadonnée pour toutes les langues
- **Fallback intelligent** : Ordre de priorité optimisé
- **Cache** : Descriptions mises en cache par WordPress

## 🔧 Configuration

### Options Disponibles
```php
// Langue principale (par défaut : fr-BE)
update_option('whise_primary_language', 'nl-BE');
```

### Intégration WPML/Polylang
Le système détecte automatiquement la langue courante et adapte les descriptions.

## 🐛 Débogage

### Logs Détaillés
Rechercher dans les logs Whise :
- `DEBUG - Property {ID} - Processing X images`
- `DEBUG - Property {ID} - Set featured image: attachment ID X`
- `ERROR - Property {ID} - Failed to download image: {URL}`

### Vérifications Manuelles
```php
// Vérifier la galerie d'une propriété
$gallery = whise_get_property_gallery($post_id);
var_dump($gallery);

// Vérifier les descriptions multilingues
$descriptions = get_post_meta($post_id, 'descriptions_multilingual', true);
var_dump($descriptions);
```

---

## 📝 Notes Techniques

### Nommage des Fichiers Images
Format : `whise-{whise_id}-{order}-{image_id}.{extension}`
Exemple : `whise-6775187-5-66172521.jpg`

### Structure des Descriptions Multilingues
```php
[
    'shortDescription' => [
        'fr-BE' => 'Description française...',
        'nl-BE' => 'Nederlandse beschrijving...'
    ],
    'sms' => [
        'fr-BE' => 'SMS français...',
        'nl-BE' => 'Nederlandse SMS...'
    ],
    'description' => [
        'fr-BE' => 'Description longue française...',
        'nl-BE' => 'Lange Nederlandse beschrijving...'
    ]
]
```

Cette implémentation assure une intégration WordPress complète et professionnelle du système d'images et de descriptions multilingues de Whise.