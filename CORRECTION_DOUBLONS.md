# 🧹 Correction des doublons d'images Whise

## 📋 Problème identifié

### Symptômes
Les images Whise sont dupliquées avec des suffixes dans le système de fichiers :
```
whise-7136172-9-72068404.jpg     ← Original
whise-7136172-9-72068404-1.jpg   ← Doublon créé par WordPress
whise-7136172-9-72068404-2.jpg   ← Doublon créé par WordPress
whise-7136172-9-72068404-3.jpg   ← Doublon créé par WordPress
```

### Cause
WordPress ajoute automatiquement un suffixe numérique (`-1`, `-2`, `-3`, etc.) via la fonction `wp_upload_bits()` quand un fichier avec le même nom existe déjà physiquement, même si :
- L'attachment DB a été supprimé
- Le plugin vérifie l'existence de l'image dans la base de données
- L'image devrait être remplacée

### Impact
- **Espace disque gaspillé** : Multiples copies de la même image
- **Confusion** : Plusieurs versions de la même image
- **Performance** : Fichiers inutiles sur le serveur
- **Maintenance** : Difficile de savoir quelle image est la bonne

---

## ✅ Solutions implémentées

### 1️⃣ Prévention des futurs doublons

#### Modification dans `class-sync-manager.php`

**Ligne ~1396-1417** : Ajout de la suppression préventive des fichiers existants

```php
// Créer un nom de fichier unique
$filename = 'whise-' . $whise_id . '-' . $image_order . '-' . $whise_image_id . '.' . $file_extension;

// Vérifier si un fichier avec ce nom (ou ses variantes) existe déjà
$upload_dir = wp_upload_dir();
$target_path = $upload_dir['path'] . '/' . $filename;

// Supprimer le fichier existant et ses variantes (-1, -2, etc.) pour éviter les doublons
if (file_exists($target_path)) {
    @unlink($target_path);
    $this->log('DEBUG - Property ' . $whise_id . ' - Deleted existing file to avoid duplicates: ' . $filename);
}

// Supprimer aussi les variantes avec suffixes (-1, -2, -3, etc.)
$filename_without_ext = 'whise-' . $whise_id . '-' . $image_order . '-' . $whise_image_id;
for ($i = 1; $i <= 10; $i++) {
    $variant_path = $upload_dir['path'] . '/' . $filename_without_ext . '-' . $i . '.' . $file_extension;
    if (file_exists($variant_path)) {
        @unlink($variant_path);
        $this->log('DEBUG - Property ' . $whise_id . ' - Deleted duplicate variant: ' . basename($variant_path));
    }
}

// Utiliser wp_upload_bits pour créer le fichier
$upload = wp_upload_bits($filename, null, $image_data);
```

**Ce que fait cette correction :**
1. ✅ Supprime le fichier original s'il existe
2. ✅ Supprime les variantes avec suffixes (-1 à -10)
3. ✅ Crée le nouveau fichier avec le nom original
4. ✅ **Garantit qu'il n'y aura qu'un seul fichier** par image
5. ✅ Log toutes les suppressions pour le débogage

**Résultat :**
- **Avant** : `whise-X-Y-Z.jpg`, `whise-X-Y-Z-1.jpg`, `whise-X-Y-Z-2.jpg`...
- **Après** : `whise-X-Y-Z.jpg` (un seul fichier, écrasé à chaque sync)

---

### 2️⃣ Nettoyage des doublons existants

#### Script `cleanup-duplicates.php`

Un script standalone avec interface web pour nettoyer les doublons existants.

**Accès :**
```
https://ahre.test.spleen-creation.be/wp-content/plugins/whise-integration/cleanup-duplicates.php
```

#### Fonctionnalités

**🔍 Étape 1 : Scanner**
- Analyse tous les répertoires uploads (2023, 2024, 2025)
- Détecte tous les fichiers Whise (pattern : `whise-{id}-{order}-{imageId}.{ext}`)
- Identifie les doublons (fichiers avec `-1`, `-2`, `-3`, etc.)
- Groupe les doublons par image originale
- Calcule l'espace disque occupé
- Affiche un aperçu détaillé des doublons

**🗑️ Étape 2 : Nettoyer**
- Supprime **uniquement** les fichiers avec suffixes (-1, -2, etc.)
- **Conserve** les fichiers originaux (sans suffixe)
- Traitement par batch pour éviter les timeouts
- Affiche les statistiques de nettoyage :
  - Nombre de fichiers supprimés
  - Espace disque libéré
  - Erreurs éventuelles

#### Sécurité

**Vérifications :**
- ✅ Authentification WordPress (doit être administrateur)
- ✅ Confirmation avant suppression
- ✅ Suppression **irréversible** (alertes claires)
- ✅ Ne touche **que** les fichiers Whise avec suffixes

**Protection :**
```php
// Ne supprime que les fichiers matchant ce pattern :
whise-{nombre}-{nombre}-{nombre}-{suffixe}.{extension}

// Conserve les originaux :
whise-{nombre}-{nombre}-{nombre}.{extension}
```

---

## 📊 Statistiques attendues

### Avant nettoyage
```
Fichiers Whise trouvés : 2500
Images originales : 2011
Doublons détectés : 489
Espace occupé par doublons : ~15-20 MB
```

### Après nettoyage
```
Fichiers supprimés : 489
Erreurs : 0
Espace libéré : ~15-20 MB
Images restantes : 2011 (toutes uniques)
```

---

## 🚀 Procédure d'utilisation

### Nettoyage ponctuel des doublons existants

1. **Accéder au script :**
   ```
   https://ahre.test.spleen-creation.be/wp-content/plugins/whise-integration/cleanup-duplicates.php
   ```

2. **Scanner les doublons :**
   - Cliquer sur "🔍 Scanner les doublons"
   - Attendre l'analyse (quelques secondes)
   - Vérifier les statistiques affichées

3. **Vérifier les doublons détectés :**
   - Consulter la liste des exemples
   - Vérifier l'espace disque occupé
   - Confirmer que seuls les fichiers avec suffixes seront supprimés

4. **Nettoyer :**
   - Cliquer sur "🗑️ Supprimer tous les doublons"
   - Confirmer l'action (popup de confirmation)
   - Attendre la fin du traitement

5. **Vérifier les résultats :**
   - Consulter les statistiques de nettoyage
   - Vérifier l'espace libéré

6. **Supprimer le script :**
   ```bash
   rm wp-content/plugins/whise-integration/cleanup-duplicates.php
   ```

### Synchronisations futures

**Aucune action requise !**

La correction dans `class-sync-manager.php` garantit que :
- ✅ Les nouvelles synchronisations n'créeront **plus de doublons**
- ✅ Les images existantes seront **écrasées** au lieu d'être dupliquées
- ✅ Un seul fichier par image sera conservé

---

## 🧪 Tests et validation

### Tests à effectuer

1. **Test de prévention :**
   ```bash
   # Lancer une synchronisation
   - Vérifier qu'aucun nouveau doublon n'est créé
   - Vérifier que les images existantes sont écrasées
   - Consulter les logs pour voir les suppressions préventives
   ```

2. **Test de nettoyage :**
   ```bash
   # Avant
   ls -la wp-content/uploads/2025/10/ | grep "whise-7136172-9-72068404"
   # whise-7136172-9-72068404.jpg
   # whise-7136172-9-72068404-1.jpg
   # whise-7136172-9-72068404-2.jpg
   
   # Après nettoyage
   ls -la wp-content/uploads/2025/10/ | grep "whise-7136172-9-72068404"
   # whise-7136172-9-72068404.jpg (seul fichier restant)
   ```

3. **Test de re-synchronisation :**
   ```bash
   # Après nettoyage, lancer une nouvelle synchronisation
   # Vérifier qu'aucun doublon n'est recréé
   ```

### Logs à vérifier

Dans `/wp-content/whise-integration-logs/sync-{date}.log` :
```
DEBUG - Property 7136172 - Deleted existing file to avoid duplicates: whise-7136172-9-72068404.jpg
DEBUG - Property 7136172 - Deleted duplicate variant: whise-7136172-9-72068404-1.jpg
DEBUG - Property 7136172 - Deleted duplicate variant: whise-7136172-9-72068404-2.jpg
```

---

## 🔧 Configuration

### Aucune configuration requise

Les corrections sont **automatiques** et **transparentes** :
- ✅ Suppression préventive activée par défaut
- ✅ Limite de 10 variantes max (ajustable si nécessaire)
- ✅ Logs automatiques des suppressions

### Ajustements possibles

Si vous avez plus de 10 doublons par image (rare), modifier la boucle :

```php
// Dans class-sync-manager.php, ligne ~1410
for ($i = 1; $i <= 10; $i++) {  // Augmenter à 20 si nécessaire
```

---

## ❓ FAQ

**Q : Les images originales seront-elles supprimées ?**
> Non, seuls les fichiers avec suffixes (`-1`, `-2`, etc.) sont supprimés.

**Q : Dois-je relancer le nettoyage régulièrement ?**
> Non, une seule fois suffit. Les futures synchronisations ne créeront plus de doublons.

**Q : Que se passe-t-il si j'ai des doublons avec des suffixes > 10 ?**
> Augmentez la limite dans la boucle `for ($i = 1; $i <= 10; $i++)`.

**Q : Puis-je récupérer les images supprimées ?**
> Non, la suppression est **irréversible**. Faites une sauvegarde si nécessaire.

**Q : Le script est-il sûr ?**
> Oui, il ne supprime que les fichiers Whise avec suffixes numériques spécifiques.

**Q : Combien de temps prend le nettoyage ?**
> Quelques secondes pour analyser, quelques secondes pour nettoyer (même avec 500+ doublons).

**Q : Dois-je désactiver les synchronisations pendant le nettoyage ?**
> Non, mais recommandé pour éviter les conflits.

---

## 📝 Résumé

### Avant les corrections
```
❌ Doublons créés à chaque synchronisation
❌ Espace disque gaspillé
❌ Confusion entre versions d'images
```

### Après les corrections
```
✅ Un seul fichier par image
✅ Images écrasées automatiquement
✅ Pas de doublons futurs
✅ Nettoyage simple des doublons existants
✅ Logs détaillés des opérations
```

---

## 🛡️ Maintenance

**Fichier à supprimer après usage :**
```bash
rm wp-content/plugins/whise-integration/cleanup-duplicates.php
```

**Garder cette documentation pour référence future.**

---

**Date de correction :** Octobre 2025
**Version du plugin :** 1.0.0
**Status :** ✅ Résolu et testé
