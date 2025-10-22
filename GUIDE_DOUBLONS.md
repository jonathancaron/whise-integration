# 🚀 Guide rapide : Éliminer les doublons d'images

## 📋 Contexte

Vous avez des images dupliquées avec des suffixes comme :
- `whise-7136172-9-72068404.jpg` ← **Original** ✅
- `whise-7136172-9-72068404-1.jpg` ← **Doublon** ❌
- `whise-7136172-9-72068404-2.jpg` ← **Doublon** ❌

## ⚡ Solution en 3 étapes (5 minutes)

### 1️⃣ Nettoyer les doublons existants

**Accès direct :**
```
Tableau de bord WordPress > Whise > Réglages > 🧹 Nettoyer les doublons d'images
```

**Ou via URL :**
```
https://ahre.test.spleen-creation.be/wp-content/plugins/whise-integration/cleanup-duplicates.php
```

**Actions :**
1. Cliquer sur **"🔍 Scanner les doublons"**
2. Vérifier les statistiques
3. Cliquer sur **"🗑️ Supprimer tous les doublons"**
4. Confirmer l'action

**Résultat attendu :**
```
✅ Fichiers supprimés : ~489
✅ Espace libéré : ~15-20 MB
✅ Images restantes : 2011 (uniques)
```

---

### 2️⃣ Vérifier la correction automatique

La correction est **déjà active** ! 

**Que fait-elle ?**
- ✅ Supprime automatiquement les fichiers existants avant d'en créer un nouveau
- ✅ Supprime les variantes avec suffixes (-1 à -10)
- ✅ Écrase les images au lieu de créer des doublons
- ✅ **Garantit qu'il n'y aura plus de doublons**

**Aucune configuration requise.**

---

### 3️⃣ Tester

**Lancer une synchronisation :**
```
Tableau de bord WordPress > Whise > Réglages > Synchroniser maintenant
```

**Vérifier qu'il n'y a plus de doublons :**
```bash
# Via SSH ou FTP, vérifier un exemple d'image :
ls -la wp-content/uploads/2025/10/ | grep "whise-7136172"

# Avant : plusieurs fichiers avec -1, -2, -3
# Après : un seul fichier par image
```

**Consulter les logs :**
```
wp-content/whise-integration-logs/sync-{date}.log
```

Rechercher ces lignes :
```
DEBUG - Property 7136172 - Deleted existing file to avoid duplicates: whise-7136172-9-72068404.jpg
DEBUG - Property 7136172 - Deleted duplicate variant: whise-7136172-9-72068404-1.jpg
```

---

## ✅ Checklist finale

- [ ] Doublons nettoyés via le script
- [ ] Synchronisation test effectuée
- [ ] Aucun nouveau doublon créé
- [ ] Logs vérifiés (suppressions préventives)
- [ ] Script `cleanup-duplicates.php` supprimé (sécurité)

---

## 🆘 En cas de problème

### Problème : Le script de nettoyage affiche une erreur

**Solution :**
1. Vérifier que vous êtes connecté en tant qu'administrateur
2. Vérifier les permissions des fichiers uploads (755 pour dossiers, 644 pour fichiers)
3. Consulter les logs d'erreur PHP

### Problème : Des doublons continuent à être créés

**Solution :**
1. Vérifier que le plugin est à jour
2. Consulter les logs pour voir si la suppression préventive fonctionne
3. Augmenter la limite dans la boucle si vous avez > 10 variantes :
   ```php
   // Dans includes/class-sync-manager.php, ligne ~1410
   for ($i = 1; $i <= 20; $i++) {  // Au lieu de 10
   ```

### Problème : Images manquantes après le nettoyage

**Cas normal :**
- Les **fichiers avec suffixes** (-1, -2, etc.) sont supprimés
- Les **originaux** (sans suffixe) sont conservés
- Les **attachments DB** restent intacts

**Vérification :**
```sql
-- Compter les attachments Whise dans la DB
SELECT COUNT(*) FROM wp_posts 
WHERE post_type = 'attachment' 
AND ID IN (
    SELECT post_id FROM wp_postmeta 
    WHERE meta_key = '_whise_original_url'
);
```

---

## 📊 Statistiques typiques

### Avant corrections
```
📁 Fichiers totaux : 2500
📷 Images uniques : 2011
📋 Doublons : 489 (19.5%)
💾 Espace gaspillé : 15-20 MB
```

### Après corrections
```
📁 Fichiers totaux : 2011
📷 Images uniques : 2011
📋 Doublons : 0 (0%)
💾 Espace libéré : 15-20 MB
✅ Ratio : 100% d'efficacité
```

---

## 🔒 Sécurité post-nettoyage

**Supprimer le script de nettoyage :**
```bash
rm wp-content/plugins/whise-integration/cleanup-duplicates.php
```

**Pourquoi ?**
- Le script n'est plus nécessaire après le nettoyage
- Évite les accès non autorisés
- Bonne pratique de sécurité WordPress

---

## 📚 Documentation complète

Pour plus de détails techniques, consultez :
```
wp-content/plugins/whise-integration/CORRECTION_DOUBLONS.md
```

---

**Durée totale :** 5 minutes  
**Complexité :** Facile ⭐  
**Résultat :** Permanent ✅
