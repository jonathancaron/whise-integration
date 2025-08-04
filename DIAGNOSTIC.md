# 🔧 Diagnostic Whise Integration

## ✅ **Problèmes corrigés**

### **1. Erreurs critiques identifiées et corrigées :**

#### **❌ Problème : Fichier class-shortcodes.php vide**
- **Cause :** Le fichier ne contenait qu'un commentaire
- **Solution :** Création d'une classe complète avec shortcodes fonctionnels

#### **❌ Problème : Vérifications de sécurité manquantes**
- **Cause :** Pas de vérification de l'existence des classes avant instanciation
- **Solution :** Ajout de `class_exists()` partout

#### **❌ Problème : Constantes non définies**
- **Cause :** Utilisation de `WHISE_PLUGIN_URL` sans vérification
- **Solution :** Vérification avec `defined()` avant utilisation

#### **❌ Problème : Post type non vérifié**
- **Cause :** Requêtes sur un post type qui pourrait ne pas exister
- **Solution :** Vérification avec `post_type_exists()`

#### **❌ Problème : Gestion d'erreurs insuffisante**
- **Cause :** Pas de try/catch pour les opérations critiques
- **Solution :** Ajout de gestion d'exceptions complète

#### **❌ Problème : Taxonomies non visibles dans l'admin**
- **Cause :** Options `show_ui` et `show_in_menu` manquantes
- **Solution :** Ajout des options de visibilité et diagnostic complet

## 🛠️ **Actions de correction effectuées**

### **1. Fichier principal (`whise-integration.php`)**
- ✅ Ajout de vérification d'existence des fichiers
- ✅ Gestion d'erreurs avec `error_log()`
- ✅ Vérification des classes avant instanciation
- ✅ Messages d'erreur dans l'admin

### **2. Classe Admin (`class-admin.php`)**
- ✅ Vérification des constantes avant utilisation
- ✅ Vérification du post type avant requêtes
- ✅ Gestion d'erreurs dans les méthodes
- ✅ Messages d'erreur spécifiques
- ✅ **NOUVEAU :** Diagnostic complet du plugin
- ✅ **NOUVEAU :** Bouton de réinitialisation complète

### **3. Classe Property CPT (`class-property-cpt.php`)**
- ✅ Ajout des options `show_ui` et `show_in_menu` pour les taxonomies
- ✅ Amélioration de la vérification des taxonomies
- ✅ Logs d'erreur détaillés
- ✅ Force le flush des règles de réécriture

### **4. Classe Shortcodes (`class-shortcodes.php`)**
- ✅ Création complète de la classe
- ✅ Shortcodes fonctionnels : `[whise_properties]`, `[whise_property]`, `[whise_search]`
- ✅ Gestion des erreurs et cas limites

### **5. Fichiers CSS/JS**
- ✅ Création du fichier CSS de base
- ✅ Vérification de l'existence des constantes

## 🔍 **Comment diagnostiquer les problèmes**

### **1. Vérifier les logs d'erreur WordPress**
```bash
# Dans wp-config.php, activer le debug
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### **2. Vérifier les logs du plugin**
```php
// Les erreurs du plugin sont loggées avec :
error_log('Whise Integration: Message d\'erreur');
```

### **3. Tester l'activation du plugin**
1. Désactiver le plugin
2. Réactiver le plugin
3. Vérifier les messages d'erreur dans l'admin

### **4. Vérifier les fichiers requis**
```php
// Tous ces fichiers doivent exister :
- includes/class-whise-api.php
- includes/class-sync-manager.php
- includes/class-property-cpt.php
- includes/class-admin.php
- includes/class-shortcodes.php
- includes/class-debug.php
```

## 🚀 **Test de fonctionnement**

### **1. Test de l'interface admin**
- Aller dans Admin > Whise Integration
- Vérifier que la page se charge sans erreur
- Tester les boutons d'action
- **NOUVEAU :** Vérifier la section "Diagnostic complet du plugin"

### **2. Test des shortcodes**
```php
// Dans une page ou article :
[whise_properties limit="5"]
[whise_property id="123"]
[whise_search]

// Shortcodes de diagnostic :
[whise_debug_stats]           // Statistiques d'import
[whise_debug_fields]          // Champs disponibles pour Elementor
[whise_debug id="123"]        // Debug d'une propriété spécifique
[whise_diagnostic]            // Diagnostic complet du plugin
```

### **3. Test de l'API**
- Configurer les identifiants API
- Tester la connexion depuis l'admin

## 📋 **Checklist de vérification**

- [ ] Plugin s'active sans erreur
- [ ] Interface admin accessible
- [ ] Tous les fichiers présents
- [ ] Classes chargées correctement
- [ ] Shortcodes fonctionnels
- [ ] CSS/JS chargés
- [ ] Logs d'erreur propres
- [ ] **NOUVEAU :** Post type "property" visible dans l'admin
- [ ] **NOUVEAU :** Taxonomies visibles dans Admin > Propriétés

## 🆘 **En cas de problème persistant**

### **1. Vérifier les permissions**
```bash
# Les fichiers doivent être lisibles
chmod 644 *.php
chmod 644 assets/css/*.css
chmod 644 assets/js/*.js
```

### **2. Vérifier la version PHP**
```php
// Le plugin nécessite PHP 7.4+
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    echo 'PHP 7.4+ requis';
}
```

### **3. Vérifier les extensions PHP**
```php
// Extensions requises
- curl
- json
- mbstring
```

### **4. Réinitialisation complète (NOUVEAU)**
Si les problèmes persistent :
1. Aller dans Admin > Whise Integration
2. Cliquer sur "Réinitialisation complète" (bouton rouge)
3. Désactiver le plugin
4. Réactiver le plugin
5. Vérifier le diagnostic complet

### **5. Contacter le support**
Si les problèmes persistent, fournir :
- Version WordPress
- Version PHP
- Logs d'erreur
- Messages d'erreur spécifiques
- **NOUVEAU :** Capture d'écran du diagnostic complet

## 🎯 **Prochaines étapes**

1. **Tester l'activation** du plugin
2. **Vérifier l'interface admin**
3. **Consulter le diagnostic complet**
4. **Configurer l'API Whise**
5. **Tester la synchronisation**
6. **Utiliser les shortcodes**
7. **Vérifier les taxonomies dans Admin > Propriétés**

---

**Le plugin devrait maintenant fonctionner correctement !** 🎉 