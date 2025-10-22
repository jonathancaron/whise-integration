# 🧪 Test de l'upgrade automatique V2

## 📋 Checklist de test

### 1️⃣ Avant la synchronisation

**Vérifier l'image actuelle :**
```bash
# Via navigateur
https://ahre.test.spleen-creation.be/wp-content/uploads/2025/10/whise-7136172-9-72068404.jpg

# Via curl (pour voir la taille)
curl -I https://ahre.test.spleen-creation.be/wp-content/uploads/2025/10/whise-7136172-9-72068404.jpg | grep Content-Length
```

**Taille attendue AVANT :**
- ~20-30 KB (image 640x480)

---

### 2️⃣ Lancer la synchronisation

**Option A : Via admin WordPress**
```
Tableau de bord > Whise > Réglages > Synchroniser maintenant
```

**Option B : Via WP-CLI**
```bash
wp cron event run whise_sync_properties_event
```

**Option C : Via URL directe (admin)**
```
https://ahre.test.spleen-creation.be/wp-admin/admin-post.php?action=whise_manual_sync
```

---

### 3️⃣ Pendant la synchronisation

**Consulter les logs en temps réel :**
```bash
tail -f wp-content/whise-integration-logs/sync-$(date +%Y-%m-%d).log
```

**Ou rechercher spécifiquement la propriété 7136172 :**
```bash
grep "7136172" wp-content/whise-integration-logs/sync-$(date +%Y-%m-%d).log
```

**Logs à rechercher :**
- ✅ `Found X images by filename pattern` → Détection des anciennes images
- ✅ `Checking attachment ID` → Vérification de qualité
- ✅ `Deleting lower quality image` → Suppression de la 640px
- ✅ `Deleted physical file` → Fichier supprimé
- ✅ `Downloading image 72068404 in quality urlXXL` → Re-téléchargement

---

### 4️⃣ Après la synchronisation

**Vérifier l'image :**
```bash
# Via navigateur (CTRL+F5 pour forcer le refresh)
https://ahre.test.spleen-creation.be/wp-content/uploads/2025/10/whise-7136172-9-72068404.jpg

# Via curl
curl -I https://ahre.test.spleen-creation.be/wp-content/uploads/2025/10/whise-7136172-9-72068404.jpg | grep Content-Length
```

**Taille attendue APRÈS :**
- ~150-250 KB (image 1600px+)

**Vérifier qu'il n'y a pas de doublons :**
```bash
ls -la wp-content/uploads/2025/10/ | grep "whise-7136172-9-72068404"
```

**Résultat attendu :**
- Un seul fichier : `whise-7136172-9-72068404.jpg`
- Pas de `-1`, `-2`, etc.

---

### 5️⃣ Vérification en base de données

**Vérifier l'URL stockée :**
```sql
SELECT pm.meta_value 
FROM wp_postmeta pm
INNER JOIN wp_posts p ON pm.post_id = p.ID
WHERE p.post_type = 'attachment'
AND pm.meta_key = '_whise_original_url'
AND pm.meta_value LIKE '%72068404%';
```

**Résultat attendu :**
- URL contenant `/1600/` ou `urlXXL`
- PAS `/640/` ou `urlLarge`

---

## ✅ Critères de succès

### Images

- [ ] Image principale en 1600px+ (vérifiable dans le navigateur)
- [ ] Taille du fichier : 150-250 KB (au lieu de 20-30 KB)
- [ ] Un seul fichier physique (pas de doublons)
- [ ] URL en DB contient `/1600/`

### Logs

- [ ] Log "Found X images by filename pattern" présent
- [ ] Log "Checking attachment" avec niveaux de qualité
- [ ] Log "Deleting lower quality image (level 1 vs 0)"
- [ ] Log "Downloading image 72068404 in quality urlXXL"
- [ ] **PAS** de "skipping download" pour image 72068404

### Synchronisation

- [ ] Synchronisation complète sans erreur
- [ ] Toutes les images de la propriété 7136172 en haute qualité
- [ ] Pas de timeouts
- [ ] Pas d'erreurs PHP

---

## ❌ En cas d'échec

### Symptôme : "Skipping download" encore présent

**Vérifier :**
```bash
grep "skipping download.*72068404" wp-content/whise-integration-logs/sync-*.log
```

**Si trouvé :**
1. Vérifier que le code a bien été mis à jour
2. Vider le cache OPcache PHP :
   ```bash
   # Via PHP
   opcache_reset();
   
   # Ou redémarrer PHP-FPM
   sudo systemctl restart php-fpm
   ```

---

### Symptôme : Image toujours en 640px

**Vérifier :**
```bash
grep "Deleting lower quality" wp-content/whise-integration-logs/sync-*.log
```

**Si absent :**
1. Vérifier que l'image a bien `_whise_image_id` en meta :
   ```sql
   SELECT pm.meta_value 
   FROM wp_postmeta pm
   INNER JOIN wp_posts p ON pm.post_id = p.ID
   WHERE p.post_type = 'attachment'
   AND p.post_name LIKE '%72068404%'
   AND pm.meta_key = '_whise_image_id';
   ```

2. Si absent, vérifier que la recherche par nom de fichier fonctionne :
   ```bash
   grep "Found.*images by filename pattern.*72068404" wp-content/whise-integration-logs/sync-*.log
   ```

---

### Symptôme : Erreurs de suppression

**Rechercher les erreurs :**
```bash
grep "ERROR.*7136172" wp-content/whise-integration-logs/sync-*.log
```

**Causes possibles :**
- Permissions insuffisantes sur les fichiers
- Fichiers déjà supprimés
- Attachment orphelin (DB sans fichier)

**Solution :**
```bash
# Vérifier les permissions
ls -la wp-content/uploads/2025/10/ | grep "whise-7136172"

# Corriger si nécessaire
chmod 644 wp-content/uploads/2025/10/whise-7136172-*.jpg
```

---

## 📊 Résultat attendu

### Console logs (extraits)

```log
[2025-10-22 14:30:15] DEBUG - Starting synchronization...
[2025-10-22 14:30:25] DEBUG - Property 7136172 - Processing 10 images
[2025-10-22 14:30:26] DEBUG - Property 7136172 - Found 1 images by filename pattern for image 72068404
[2025-10-22 14:30:26] DEBUG - Property 7136172 - Checking attachment ID 12345 - old quality level: 1, preferred: 0
[2025-10-22 14:30:26] DEBUG - Property 7136172 - Deleting lower quality image (level 1 vs 0): attachment ID 12345 URL: https://image.whise.eu/.../640/...jpg
[2025-10-22 14:30:26] DEBUG - Property 7136172 - Deleted physical file: whise-7136172-9-72068404.jpg
[2025-10-22 14:30:27] DEBUG - Property 7136172 - Downloading image 72068404 in quality urlXXL
[2025-10-22 14:30:27] DEBUG - Property 7136172 - Using urlXXL (preferred quality) for image 72068404
[2025-10-22 14:30:27] DEBUG - Property 7136172 - Downloading image: https://image.whise.eu/.../1600/...jpg
[2025-10-22 14:30:29] DEBUG - Property 7136172 - Image uploaded successfully: whise-7136172-9-72068404.jpg
[2025-10-22 14:30:29] DEBUG - Property 7136172 - Gallery updated with 10 image attachments
[2025-10-22 14:30:29] DEBUG - Property 7136172 - Set featured image: attachment ID 54321
```

### Fichiers

**Avant :**
```bash
-rw-r--r-- 1 www-data www-data  25K Oct 20 10:15 whise-7136172-9-72068404.jpg
```

**Après :**
```bash
-rw-r--r-- 1 www-data www-data 180K Oct 22 14:30 whise-7136172-9-72068404.jpg
```

### Base de données

**Avant :**
```
_whise_original_url: https://image.whise.eu/.../640/...jpg
```

**Après :**
```
_whise_original_url: https://image.whise.eu/.../1600/...jpg
```

---

## 🎉 Confirmation finale

Si tous les critères sont remplis :

✅ **L'upgrade automatique V2 fonctionne correctement !**

Vous pouvez maintenant :
1. Supprimer le script `cleanup-duplicates.php` (plus nécessaire)
2. Laisser les synchronisations automatiques gérer la qualité
3. Profiter des images en haute qualité

---

**Date de test :** _______________  
**Testé par :** _______________  
**Résultat :** ☐ Succès ☐ Échec  
**Notes :** _______________________________________________
