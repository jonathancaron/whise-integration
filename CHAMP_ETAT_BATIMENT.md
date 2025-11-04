# 🏗️ Champ "État du bâtiment"

## 📌 Résumé

Le champ **"État du bâtiment"** (`state`) de l'API Whise est maintenant correctement récupéré et enregistré dans WordPress.

---

## 📊 Données enregistrées

Lors de la synchronisation, **deux champs** sont enregistrés pour chaque bien :

| Meta Key | Type | Description | Exemple |
|----------|------|-------------|---------|
| `state` | `string` | Nom de l'état du bâtiment | "Bon état", "À rénover", "Neuf" |
| `state_id` | `string` | ID Whise de l'état | "1", "2", "3", "4" |

---

## 🔧 Modifications apportées

### 1. **class-sync-manager.php**
- ✅ Ajout de l'endpoint **`v1/estates/states`** pour récupérer les taxonomies (ligne 432)
- ✅ Ajout de la méthode **`get_default_state_name()`** avec valeurs par défaut (ligne 1380-1391)
- ✅ Récupération du nom depuis la taxonomie ou valeurs par défaut (ligne 661-674)
- ✅ Utilisation du **`$state_name`** dans le mapping (ligne 798-799)
- ✅ Ajout de la **définition de type** (ligne 78-79)

```php
'state' => 'string',
'state_id' => 'string',
```

**Valeurs par défaut configurées :**
```php
'1' => 'Excellent état',
'2' => 'Bon état',
'3' => 'À rafraîchir',
'4' => 'À rénover',
'5' => 'Neuf',
'6' => 'Comme neuf'
```

### 2. **class-property-cpt.php**
- ✅ Ajout de `state` et `state_id` dans les **types de champs API REST** (ligne 298)
- ✅ Ajout des **labels descriptifs** (ligne 341-342)

```php
'state' => ['desc' => 'État du bâtiment', 'type' => 'string'],
'state_id' => ['desc' => 'ID de l\'état du bâtiment', 'type' => 'string'],
```

### 3. **class-property-details-page.php**
- ✅ Création d'une nouvelle section **"État et statut"** dans la page de détails (ligne 125-129)

Cette section affiche :
- État du bâtiment (`state`)
- Statut du bien (`status`)
- Statut de transaction (`purpose_status`, `transaction_status`)
- Année de construction (`construction_year`)

---

## 📖 Structure de l'API Whise

### Format reçu depuis l'API :

```json
{
  "id": 7136142,
  "name": "Appartement 2 chambres",
  "state": {
    "id": 1,
    "name": "Bon état",
    "displayName": "Bon état"
  }
}
```

### Mapping dans WordPress :

- `$property['state']['displayName']` → `state`
- `$property['state']['id']` → `state_id`

---

## 🎨 Utilisation dans les templates

### Afficher l'état du bâtiment :

```php
<?php 
$state = get_post_meta(get_the_ID(), 'state', true);
if ($state) : ?>
    <div class="property-state">
        <strong>État du bâtiment :</strong> <?php echo esc_html($state); ?>
    </div>
<?php endif; ?>
```

### Utilisation dans Elementor (Dynamic Tags) :

1. Ajouter un widget **Dynamic Text**
2. Source : **Post Meta**
3. Meta Key : `state`

---

## 🔍 Vérification

Pour vérifier que le champ est bien enregistré, exécutez cette requête SQL :

```sql
SELECT 
    p.ID,
    p.post_title,
    pm_state.meta_value as state,
    pm_state_id.meta_value as state_id
FROM wp_posts p
LEFT JOIN wp_postmeta pm_state ON p.ID = pm_state.post_id AND pm_state.meta_key = 'state'
LEFT JOIN wp_postmeta pm_state_id ON p.ID = pm_state_id.post_id AND pm_state_id.meta_key = 'state_id'
WHERE p.post_type = 'property'
  AND p.post_status = 'publish'
LIMIT 10;
```

---

## 📝 Valeurs configurées

Le plugin utilise **3 sources** pour récupérer le nom de l'état (par ordre de priorité) :

### **1. Taxonomie Whise** (prioritaire)
Si l'endpoint `v1/estates/states` renvoie des données, elles seront utilisées.

### **2. Valeurs par défaut** (fallback)
Si la taxonomie n'est pas disponible, ces valeurs sont utilisées :

| ID | Nom français | Visible dans Whise |
|----|--------------|-------------------|
| 1  | Excellent état | ✓ |
| 2  | Bon état | ✓ |
| 3  | À rafraîchir | ✓ |
| 4  | À rénover | ✓ |
| 5  | Neuf | ✓ |
| 6  | Comme neuf | ✓ |

### **3. displayName/name de l'API** (dernier recours)
Si présent dans la réponse `v1/estates/list`

**Note :** D'après votre screenshot Whise, le champ s'appelle **"Condition"** dans l'interface, mais **"state"** dans l'API.

---

## ✅ Prochaine synchronisation

Le champ sera **automatiquement enregistré** lors de la prochaine synchronisation pour tous les biens.

### Pour forcer la synchronisation maintenant :

1. Aller dans **Tableau de bord > Whise Integration**
2. Cliquer sur **"Lancer la synchronisation"**
3. Le champ `state` sera enregistré pour tous les biens

---

## 🎯 Résultat attendu

Après la synchronisation, vous pourrez :
- ✅ Voir l'état du bâtiment dans l'admin WordPress (page de détails du bien)
- ✅ Utiliser le champ dans vos templates PHP avec `get_post_meta($post_id, 'state', true)`
- ✅ L'afficher dans Elementor via **Dynamic Tags > Post Meta > state**
- ✅ Filtrer les biens par état (si vous créez une taxonomie personnalisée)

---

## 📚 Documentation API Whise

- Endpoint : `v1/estates/list`
- Champ : `state` (objet avec `id`, `name`, `displayName`)
- Type : Référence vers la table des états de bâtiment Whise

---

**Date de modification :** 24 octobre 2025  
**Auteur :** Assistant AI

