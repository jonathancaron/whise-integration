<?php
// Template fallback pour un bien unique
if (!defined('ABSPATH')) exit;
get_header();

// Récupération des données de la propriété
$whise_id = get_post_meta(get_the_ID(), 'whise_id', true);
$reference = get_post_meta(get_the_ID(), 'reference', true);
$price = get_post_meta(get_the_ID(), 'price', true);
$price_formatted = get_post_meta(get_the_ID(), 'price_formatted', true);
$price_type = get_post_meta(get_the_ID(), 'price_type', true);
$charges = get_post_meta(get_the_ID(), 'charges', true);
$price_per_sqm = get_post_meta(get_the_ID(), 'price_per_sqm', true);

// Surfaces
$surface = get_post_meta(get_the_ID(), 'surface', true);
$total_area = get_post_meta(get_the_ID(), 'total_area', true);
$land_area = get_post_meta(get_the_ID(), 'land_area', true);
$ground_area = get_post_meta(get_the_ID(), 'ground_area', true);
$min_area = get_post_meta(get_the_ID(), 'min_area', true);
$max_area = get_post_meta(get_the_ID(), 'max_area', true);

// Pièces
$rooms = get_post_meta(get_the_ID(), 'rooms', true);
$bathrooms = get_post_meta(get_the_ID(), 'bathrooms', true);
$bedrooms = get_post_meta(get_the_ID(), 'bedrooms', true);
$floors = get_post_meta(get_the_ID(), 'floors', true);

// Localisation
$address = get_post_meta(get_the_ID(), 'address', true);
$city = get_post_meta(get_the_ID(), 'city', true);
$postal_code = get_post_meta(get_the_ID(), 'postal_code', true);
$box = get_post_meta(get_the_ID(), 'box', true);
$number = get_post_meta(get_the_ID(), 'number', true);
$latitude = get_post_meta(get_the_ID(), 'latitude', true);
$longitude = get_post_meta(get_the_ID(), 'longitude', true);

// Construction
$construction_year = get_post_meta(get_the_ID(), 'construction_year', true);
$renovation_year = get_post_meta(get_the_ID(), 'renovation_year', true);

// Équipements
$furnished = get_post_meta(get_the_ID(), 'furnished', true);
$air_conditioning = get_post_meta(get_the_ID(), 'air_conditioning', true);
$double_glazing = get_post_meta(get_the_ID(), 'double_glazing', true);
$elevator = get_post_meta(get_the_ID(), 'elevator', true);
$parking = get_post_meta(get_the_ID(), 'parking', true);
$garage = get_post_meta(get_the_ID(), 'garage', true);
$alarm = get_post_meta(get_the_ID(), 'alarm', true);
$concierge = get_post_meta(get_the_ID(), 'concierge', true);

// Énergie
$heating_type = get_post_meta(get_the_ID(), 'heating_type', true);
$heating_group = get_post_meta(get_the_ID(), 'heating_group', true);

// Proximité
$proximity_transport = get_post_meta(get_the_ID(), 'proximity_transport', true);
$proximity_city_center = get_post_meta(get_the_ID(), 'proximity_city_center', true);

// Descriptions
$short_description = get_post_meta(get_the_ID(), 'short_description', true);
$sms_description = get_post_meta(get_the_ID(), 'sms_description', true);

// Images
$images = get_post_meta(get_the_ID(), 'images', true);

// Détails complets
$details = get_post_meta(get_the_ID(), 'details', true);

// Liens médias
$link_3d_model = get_post_meta(get_the_ID(), 'link_3d_model', true);
$link_virtual_visit = get_post_meta(get_the_ID(), 'link_virtual_visit', true);
$link_video = get_post_meta(get_the_ID(), 'link_video', true);

// Représentant
$rep_name = get_post_meta(get_the_ID(), 'representative_name', true);
$rep_email = get_post_meta(get_the_ID(), 'representative_email', true);
$rep_phone = get_post_meta(get_the_ID(), 'representative_phone', true);
$rep_mobile = get_post_meta(get_the_ID(), 'representative_mobile', true);
$rep_picture = get_post_meta(get_the_ID(), 'representative_picture', true);
$rep_function = get_post_meta(get_the_ID(), 'representative_function', true);
?>

<div class="whise-property-single">
    <h1><?php the_title(); ?></h1>
    
    <!-- En-tête du bien -->
    <div class="property-header">
        <div class="property-ref">Réf: <?php echo esc_html($reference); ?></div>
        <?php if ($whise_id): ?>
            <div class="property-whise-id">ID Whise: <?php echo esc_html($whise_id); ?></div>
        <?php endif; ?>
        <div class="property-price"><?php echo esc_html($price_formatted); ?></div>
        <?php if ($price_type): ?>
            <div class="property-type"><?php echo esc_html($price_type); ?></div>
        <?php endif; ?>
    </div>

    <!-- Caractéristiques principales -->
    <div class="property-main-features">
        <?php if ($surface): ?>
            <div class="surface"><?php echo esc_html($surface); ?> m²</div>
        <?php endif; ?>
        <?php if ($rooms): ?>
            <div class="rooms"><?php echo esc_html($rooms); ?> chambres</div>
        <?php endif; ?>
        <?php if ($bathrooms): ?>
            <div class="bathrooms"><?php echo esc_html($bathrooms); ?> SDB</div>
        <?php endif; ?>
        <?php if ($floors): ?>
            <div class="floors"><?php echo esc_html($floors); ?> étages</div>
        <?php endif; ?>
    </div>

    <!-- Description -->
    <div class="property-description">
        <?php if ($short_description): ?>
            <?php echo wp_kses_post($short_description); ?>
        <?php else: ?>
            <?php the_content(); ?>
        <?php endif; ?>
    </div>

    <!-- Images -->
    <?php if ($images && is_array($images)): ?>
        <div class="property-images">
            <h3>Images</h3>
            <div class="images-gallery">
                <?php foreach ($images as $image): ?>
                    <div class="image-item">
                        <img src="<?php echo esc_url($image['medium']); ?>" 
                             alt="Image propriété"
                             loading="lazy">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Médias avancés -->
    <?php if ($link_3d_model || $link_virtual_visit || $link_video): ?>
        <div class="property-media-advanced">
            <h3>Médias</h3>
            <ul class="media-links">
                <?php if ($link_virtual_visit): ?>
                    <li><a href="<?php echo esc_url($link_virtual_visit); ?>" target="_blank" rel="noopener">Visite virtuelle</a></li>
                <?php endif; ?>
                <?php if ($link_3d_model): ?>
                    <li><a href="<?php echo esc_url($link_3d_model); ?>" target="_blank" rel="noopener">Modèle 3D</a></li>
                <?php endif; ?>
                <?php if ($link_video): ?>
                    <li><a href="<?php echo esc_url($link_video); ?>" target="_blank" rel="noopener">Vidéo</a></li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Représentant / Agent -->
    <?php if ($rep_name || $rep_email || $rep_phone || $rep_mobile || $rep_function): ?>
        <div class="property-representative">
            <h3>Votre contact</h3>
            <div class="rep-card">
                <?php if ($rep_picture): ?>
                    <img class="rep-avatar" src="<?php echo esc_url($rep_picture); ?>" alt="<?php echo esc_attr($rep_name ?: 'Agent'); ?>" />
                <?php endif; ?>
                <div class="rep-infos">
                    <?php if ($rep_name): ?><div class="rep-name"><?php echo esc_html($rep_name); ?></div><?php endif; ?>
                    <?php if ($rep_function): ?><div class="rep-function"><?php echo esc_html($rep_function); ?></div><?php endif; ?>
                    <ul>
                        <?php if ($rep_email): ?><li><a href="mailto:<?php echo esc_attr($rep_email); ?>"><?php echo esc_html($rep_email); ?></a></li><?php endif; ?>
                        <?php if ($rep_phone): ?><li><a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $rep_phone)); ?>"><?php echo esc_html($rep_phone); ?></a></li><?php endif; ?>
                        <?php if ($rep_mobile): ?><li><a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $rep_mobile)); ?>"><?php echo esc_html($rep_mobile); ?></a></li><?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Détails techniques -->
    <div class="property-details">
        <h3>Informations détaillées</h3>
        
        <!-- Surface et dimensions -->
        <div class="details-section">
            <h4>Surfaces</h4>
            <ul>
                <?php if ($surface): ?>
                    <li>Surface habitable: <?php echo esc_html($surface); ?> m²</li>
                <?php endif; ?>
                <?php if ($total_area): ?>
                    <li>Surface totale: <?php echo esc_html($total_area); ?> m²</li>
                <?php endif; ?>
                <?php if ($land_area): ?>
                    <li>Surface terrain: <?php echo esc_html($land_area); ?> m²</li>
                <?php endif; ?>
                <?php if ($ground_area): ?>
                    <li>Surface au sol: <?php echo esc_html($ground_area); ?> m²</li>
                <?php endif; ?>
                <?php if ($min_area && $max_area): ?>
                    <li>Surface: <?php echo esc_html($min_area); ?> - <?php echo esc_html($max_area); ?> m²</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Pièces -->
        <div class="details-section">
            <h4>Pièces</h4>
            <ul>
                <?php if ($rooms): ?>
                    <li>Chambres: <?php echo esc_html($rooms); ?></li>
                <?php endif; ?>
                <?php if ($bathrooms): ?>
                    <li>Salles de bain: <?php echo esc_html($bathrooms); ?></li>
                <?php endif; ?>
                <?php if ($bedrooms): ?>
                    <li>Chambres à coucher: <?php echo esc_html($bedrooms); ?></li>
                <?php endif; ?>
                <?php if ($floors): ?>
                    <li>Étages: <?php echo esc_html($floors); ?></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Construction -->
        <div class="details-section">
            <h4>Construction</h4>
            <ul>
                <?php if ($construction_year): ?>
                    <li>Année de construction: <?php echo esc_html($construction_year); ?></li>
                <?php endif; ?>
                <?php if ($renovation_year): ?>
                    <li>Année de rénovation: <?php echo esc_html($renovation_year); ?></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Énergie -->
        <div class="details-section">
            <h4>Énergie</h4>
            <ul>
                <?php if (get_post_meta(get_the_ID(), 'energy_class', true)): ?>
                    <li>Classe énergétique: <?php echo get_post_meta(get_the_ID(), 'energy_class', true); ?></li>
                <?php endif; ?>
                <?php if (get_post_meta(get_the_ID(), 'epc_value', true)): ?>
                    <li>Valeur PEB: <?php echo get_post_meta(get_the_ID(), 'epc_value', true); ?></li>
                <?php endif; ?>
                <?php if ($heating_type): ?>
                    <li>Chauffage: <?php echo esc_html($heating_type); ?></li>
                <?php endif; ?>
                <?php if ($heating_group): ?>
                    <li>Type chauffage: <?php echo esc_html($heating_group); ?></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Équipements -->
        <div class="details-section">
            <h4>Équipements</h4>
            <ul>
                <?php if ($furnished): ?>
                    <li>✓ Meublé</li>
                <?php endif; ?>
                <?php if ($air_conditioning): ?>
                    <li>✓ Climatisation</li>
                <?php endif; ?>
                <?php if ($double_glazing): ?>
                    <li>✓ Double vitrage</li>
                <?php endif; ?>
                <?php if ($elevator): ?>
                    <li>✓ Ascenseur</li>
                <?php endif; ?>
                <?php if ($parking): ?>
                    <li>✓ Parking</li>
                <?php endif; ?>
                <?php if ($garage): ?>
                    <li>✓ Garage</li>
                <?php endif; ?>
                <?php if ($alarm): ?>
                    <li>✓ Alarme</li>
                <?php endif; ?>
                <?php if ($concierge): ?>
                    <li>✓ Concierge</li>
                <?php endif; ?>
                <?php if (get_post_meta(get_the_ID(), 'terrace', true)): ?>
                    <li>✓ Terrasse</li>
                <?php endif; ?>
                <?php if (get_post_meta(get_the_ID(), 'garden', true)): ?>
                    <li>✓ Jardin</li>
                <?php endif; ?>
                <?php if (get_post_meta(get_the_ID(), 'swimming_pool', true)): ?>
                    <li>✓ Piscine</li>
                <?php endif; ?>
                <?php if (get_post_meta(get_the_ID(), 'cellar', true)): ?>
                    <li>✓ Cave</li>
                <?php endif; ?>
                <?php if (get_post_meta(get_the_ID(), 'attic', true)): ?>
                    <li>✓ Grenier</li>
                <?php endif; ?>
                <?php if (get_post_meta(get_the_ID(), 'kitchen_type', true)): ?>
                    <li>Cuisine: <?php echo get_post_meta(get_the_ID(), 'kitchen_type', true); ?></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Localisation -->
        <div class="details-section">
            <h4>Localisation</h4>
            <address>
                <?php if ($number && $address): ?>
                    <?php echo esc_html($number . ' ' . $address); ?><br>
                <?php endif; ?>
                <?php if ($box): ?>
                    Boîte <?php echo esc_html($box); ?><br>
                <?php endif; ?>
                <?php if ($postal_code && $city): ?>
                    <?php echo esc_html($postal_code . ' ' . $city); ?>
                <?php endif; ?>
            </address>
            <?php if ($latitude && $longitude): ?>
                <p class="coordinates">
                    Coordonnées: <?php echo esc_html($latitude); ?>, <?php echo esc_html($longitude); ?>
                </p>
            <?php endif; ?>
            <ul>
                <?php if (get_post_meta(get_the_ID(), 'proximity_school', true)): ?>
                    <li>École: <?php echo get_post_meta(get_the_ID(), 'proximity_school', true); ?></li>
                <?php endif; ?>
                <?php if (get_post_meta(get_the_ID(), 'proximity_shops', true)): ?>
                    <li>Commerces: <?php echo get_post_meta(get_the_ID(), 'proximity_shops', true); ?></li>
                <?php endif; ?>
                <?php if ($proximity_transport): ?>
                    <li>Transports en commun: <?php echo esc_html($proximity_transport); ?> m</li>
                <?php endif; ?>
                <?php if ($proximity_city_center): ?>
                    <li>Centre-ville: <?php echo esc_html($proximity_city_center); ?> m</li>
                <?php endif; ?>
                <?php if (get_post_meta(get_the_ID(), 'proximity_hospital', true)): ?>
                    <li>Hôpital: <?php echo get_post_meta(get_the_ID(), 'proximity_hospital', true); ?></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Informations financières -->
    <div class="property-financial">
        <h3>Informations financières</h3>
        <ul>
            <li>Prix: <?php echo esc_html($price_formatted); ?></li>
            <?php if ($charges): ?>
                <li>Charges: <?php echo esc_html($charges); ?> €/m²/an</li>
            <?php endif; ?>
            <?php if ($price_per_sqm): ?>
                <li>Prix/m²: <?php echo esc_html($price_per_sqm); ?> €/m²/an</li>
            <?php endif; ?>
            <?php if (get_post_meta(get_the_ID(), 'cadastral_income', true)): ?>
                <li>Revenu cadastral: <?php echo get_post_meta(get_the_ID(), 'cadastral_income', true); ?></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Détails techniques complets -->
    <?php if ($details && is_array($details)): ?>
        <div class="property-technical-details">
            <h3>Détails techniques</h3>
            <div class="details-grid">
                <?php foreach ($details as $detail): ?>
                    <div class="detail-item">
                        <strong><?php echo esc_html($detail['label']); ?>:</strong>
                        <span><?php echo esc_html($detail['value']); ?></span>
                        <?php if ($detail['group']): ?>
                            <small>(<?php echo esc_html($detail['group']); ?>)</small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.whise-property-single {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: Arial, sans-serif;
}

.property-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

.property-main-features {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.property-main-features > div {
    background: #e9ecef;
    padding: 10px 15px;
    border-radius: 5px;
    font-weight: bold;
}

.property-description {
    margin-bottom: 30px;
    line-height: 1.6;
}

.property-images {
    margin-bottom: 30px;
}

.images-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.image-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
}

.property-details {
    margin-bottom: 30px;
}

.details-section {
    margin-bottom: 25px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.details-section h4 {
    color: #333;
    border-bottom: 2px solid #007cba;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.details-section ul {
    list-style: none;
    padding: 0;
}

.details-section li {
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}

.property-financial {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.property-technical-details {
    margin-bottom: 30px;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.detail-item {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
}

.detail-item small {
    color: #666;
    font-style: italic;
}

h3 {
    color: #333;
    border-bottom: 2px solid #007cba;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

address {
    font-style: normal;
    margin-bottom: 15px;
}

.coordinates {
    font-family: monospace;
    background: #f8f9fa;
    padding: 5px 10px;
    border-radius: 3px;
    display: inline-block;
}
</style>

<?php get_footer(); ?>
