<?php
require __DIR__ . '/includes/bootstrap.php';

$activePage = 'accueil';
$categories = get_categories();
$featured = db_all(product_query() . ' WHERE p.active = 1 AND p.featured = 1 ORDER BY p.created_at DESC, p.id DESC LIMIT 8');
$promos = db_all(product_query() . ' WHERE p.active = 1 AND p.old_price > p.price AND p.stock > 0
                                     ORDER BY (p.old_price - p.price) * 1.0 / p.old_price DESC LIMIT 4');
$bestSellers = db_all(product_query() . ' WHERE p.active = 1 AND p.stock > 0 ORDER BY p.sales DESC LIMIT 4');
$services = db_all('SELECT * FROM services WHERE active = 1 ORDER BY sort_order LIMIT 6');

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero-grid">
        <div class="hero-text">
            <span class="eyebrow">N°1 de l'informatique à N'Djamena</span>
            <h1>Le bon ordinateur, <span class="hl">au meilleur prix</span>, livré chez vous.</h1>
            <p>Portables, PC de bureau, imprimantes et accessoires neufs ou reconditionnés — avec installation,
                garantie et un atelier de réparation à votre service.</p>
            <div class="hero-cta">
                <a href="boutique.php" class="btn btn-accent btn-lg">Voir la boutique</a>
                <a href="services.php" class="btn btn-ghost btn-lg">Nos services</a>
            </div>
            <ul class="hero-points">
                <li>✔ Paiement à la livraison</li>
                <li>✔ Airtel & Moov Money</li>
                <li>✔ <?= e(WARRANTY_TEXT) ?></li>
            </ul>
        </div>
        <div class="hero-visual">
            <img src="assets/img/hero.svg" alt="Ordinateurs et accessoires MaxiTech" width="520" height="420">
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Acheter par catégorie</h2>
            <a href="boutique.php" class="link-more">Tout voir →</a>
        </div>
        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="boutique.php?cat=<?= e($cat['slug']) ?>" class="category-card">
                    <img src="assets/img/<?= e($cat['icon']) ?>.svg" alt="" width="72" height="72">
                    <strong><?= e($cat['name']) ?></strong>
                    <span><?= (int) $cat['total'] ?> produit<?= $cat['total'] > 1 ? 's' : '' ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($promos): ?>
<section class="section section-promo">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>🔥 Offres du moment</h2>
                <?php if (PROMO_END && strtotime(PROMO_END) > time()): ?>
                    <p class="muted">Fin de l'offre dans <span class="countdown" data-end="<?= e(date('c', strtotime(PROMO_END))) ?>"></span></p>
                <?php endif; ?>
            </div>
            <a href="boutique.php?promo=1" class="link-more">Toutes les promos →</a>
        </div>
        <div class="product-grid">
            <?php foreach ($promos as $p) { include __DIR__ . '/includes/product-card.php'; } ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Produits phares</h2>
            <a href="boutique.php" class="link-more">Voir le catalogue →</a>
        </div>
        <div class="product-grid">
            <?php foreach ($featured as $p) { include __DIR__ . '/includes/product-card.php'; } ?>
        </div>
    </div>
</section>

<section class="section cta-band">
    <div class="container cta-band-inner">
        <div>
            <h2>Votre ordinateur est en panne ?</h2>
            <p>Diagnostic gratuit, devis avant intervention, réparation rapide en atelier ou à domicile.</p>
        </div>
        <div class="cta-band-actions">
            <a href="services.php#demande" class="btn btn-accent btn-lg">Demander une intervention</a>
            <a href="<?= e(whatsapp_link('Bonjour, mon ordinateur a un problème : ')) ?>" class="btn btn-wa btn-lg" target="_blank" rel="noopener">Écrire sur WhatsApp</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Nos services informatiques</h2>
            <a href="services.php" class="link-more">Tous les services →</a>
        </div>
        <div class="service-grid">
            <?php foreach ($services as $s): ?>
                <a href="services.php#<?= e($s['slug']) ?>" class="service-card">
                    <span class="service-icon"><?= e($s['icon']) ?></span>
                    <h3><?= e($s['name']) ?></h3>
                    <p><?= e($s['short_desc']) ?></p>
                    <span class="service-price"><?= $s['price_from'] > 0 ? 'À partir de ' . e(money($s['price_from'])) : 'Sur devis gratuit' ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($bestSellers): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-head"><h2>Les plus vendus</h2></div>
        <div class="product-grid">
            <?php foreach ($bestSellers as $p) { include __DIR__ . '/includes/product-card.php'; } ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <div class="section-head"><h2>Pourquoi choisir <?= e(SITE_NAME) ?> ?</h2></div>
        <div class="why-grid">
            <div class="why-item"><span>💰</span><h3>Prix compétitifs</h3><p>Des prix négociés directement auprès de nos fournisseurs, et des promos chaque mois.</p></div>
            <div class="why-item"><span>✅</span><h3>Matériel testé</h3><p>Chaque appareil est contrôlé, configuré et prêt à l'emploi avant la livraison.</p></div>
            <div class="why-item"><span>🤝</span><h3>Conseil honnête</h3><p>Nous vous orientons vers l'appareil adapté à votre budget et à vos besoins réels.</p></div>
            <div class="why-item"><span>🔧</span><h3>Après-vente sur place</h3><p>Un atelier et des techniciens à N'Djamena pour vous accompagner dans la durée.</p></div>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-head"><h2>Ils nous font confiance</h2></div>
        <div class="testimonial-grid">
            <?php foreach (TESTIMONIALS as [$name, $role, $text]): ?>
                <figure class="testimonial">
                    <div class="stars" aria-label="5 étoiles sur 5">★★★★★</div>
                    <blockquote>« <?= e($text) ?> »</blockquote>
                    <figcaption><strong><?= e($name) ?></strong> — <?= e($role) ?></figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
