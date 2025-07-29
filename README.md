# Whise Integration

Intégration professionnelle de l'API Whise pour WordPress, compatible Elementor Pro.

## Description
Ce plugin WordPress permet de synchroniser automatiquement les biens immobiliers depuis l'API Whise (Belgique) et de les exploiter avec Elementor Pro (Loop Grid, filtres, etc.).

- Custom Post Type "property" avec champs et taxonomies adaptés
- Synchronisation automatique via cron WordPress (batch, logs, retry)
- Interface d'administration complète (API Key, endpoint, mapping, logs)
- Templates fallback pour single et archive
- Shortcodes de fallback
- Compatible Hello Elementor + Elementor Pro
- Optimisé pour la performance et la sécurité

## Prérequis
- Un compte WHISE Marketplace (voir documentation_whise.txt)
- Activation du client par WHISE (liaison Marketplace/Client)

## Installation
1. Téléchargez le plugin ou clonez le dépôt :
   `git clone https://github.com/jonathancaron/whise-integration`
2. Placez le dossier dans `wp-content/plugins/`
3. Activez le plugin via l’admin WordPress
4. Configurez vos accès API Whise dans le menu "Whise Integration"

## Configuration
- Renseignez vos identifiants API Whise Marketplace
- Renseignez le ClientID du client à synchroniser
- Choisissez la fréquence de synchronisation
- Consultez les logs et statistiques
- Lancez une synchronisation manuelle si besoin

## Utilisation
- Les biens sont accessibles via le Custom Post Type "property"
- Utilisez Elementor Pro Loop Grid pour afficher et filtrer les biens
- Utilisez les shortcodes :
  - `[whise_properties limit="12" type="appartement"]`
  - `[whise_property_search]`
  - `[whise_featured_properties]`
  - `[whise_property id="123"]`

## API Whise – Workflow d’authentification
1. Authentification Marketplace : POST `/token` (Username/Password)
2. Activation du client par WHISE (voir documentation)
3. Obtention du client token : POST `/v1/admin/clients/token` (ClientId)
4. Utilisation du client token pour toutes les requêtes métiers

## Documentation officielle
- [API Whise](https://api.whise.eu/WebsiteDesigner.html)
- [Postman Collection](https://api.whise.eu/docs/postman/WhiseAPI_wd_postman_collection.json)

## Champs récupérés depuis l'API Whise
Le plugin synchronise et expose les principaux champs immobiliers pour chaque bien :
- **ID Whise**
- **Prix** (numérique, formaté, charges, taxes)
- **Surface** (habitable, totale, terrain, bâtie, commerciale)
- **Nombre de chambres**
- **Nombre de salles de bain**
- **Nombre d’étages**
- **Type de propriété** (appartement, maison, bureau, terrain, etc.)
- **Type de transaction** (vente, location)
- **Adresse complète**
- **Ville**
- **Code postal**
- **Description détaillée**
- **Classe énergétique** (PEB, certificat, niveau E, energy label)
- **Date de disponibilité**
- **Images** (URLs des photos, plans, documents)
- **Latitude / Longitude**
- **Année de construction**
- **Orientation**
- **État du bien** (statut, condition, publication)
- **Équipements principaux** (garage, parking intérieur/extérieur, terrasse, jardin, piscine, ascenseur, cave, grenier, buanderie, cheminée, véranda, etc.)
- **Type de chauffage** (central, gaz, électrique, etc.)
- **Type de cuisine** (équipée, américaine, etc.)
- **Surface commerciale**
- **Surface terrain**
- **Surface bâtie**
- **Surface totale**
- **Proximité** (écoles, commerces, transports, hôpital, etc.)
- **Documents et plans** (PDF, images, etc.)
- **Taxe foncière, valeur cadastrale, etc.**
- **Statut de publication** (disponible, sous option, vendu, loué, publié)
- **Contact agence** (nom, téléphone, email)
- **Date de mise à jour**

La liste peut être enrichie selon les besoins métier : voir la documentation officielle pour tous les champs disponibles.

## Synchronisation automatique
- La synchronisation des biens s’effectue automatiquement via le cron WordPress (`wp_schedule_event`).
- Fréquence configurable (par défaut : toutes les heures)
- Traitement par batch (50 biens à la fois pour éviter les timeouts)
- Logs et statistiques accessibles dans l’interface admin
- Possibilité de lancer une synchronisation manuelle depuis l’admin

Pour personnaliser la fréquence ou le mapping des champs, modifiez les options dans l’interface ou le code du plugin.

---

## Développement & Contributions
- Licence MIT
- Dépôt GitHub : https://github.com/jonathancaron/whise-integration
- Contributions bienvenues via issues ou pull requests

## Support
Pour toute question ou bug, ouvrez une issue sur GitHub ou contactez l’auteur.

## Auteur
Jonathan Caron

## Licence
MIT License. Voir le fichier LICENSE.txt.
# whise-integration
