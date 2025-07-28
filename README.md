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

## Installation
1. Téléchargez le plugin ou clonez le dépôt :
   `git clone https://github.com/jonathancaron/whise-integration`
2. Placez le dossier dans `wp-content/plugins/`
3. Activez le plugin via l’admin WordPress
4. Configurez vos accès API Whise dans le menu "Whise Integration"

## Configuration
- Renseignez vos identifiants API Whise
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
