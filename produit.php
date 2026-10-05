<?php
require __DIR__ . '/includes/bootstrap.php';

$product = db_one(product_query() . ' WHERE p.slug = ? AND p.active = 1', [(string) ($_GET['slug'] ?? '')]);
if (!$product) {
    http_response_code(404);
    $pageTitle = 'Produit introuvable';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container empty-state"><h1>Produit introuvable</h1>'
        . '<p>Ce produit n\'existe plus ou a été retiré.</p><a href="boutique.php" class="btn btn-primary">Retour à la boutique</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$related = db_all(product_query() . ' WHERE p.active = 1 AND p.category_id = ? AND p.id <> ? ORDER BY p.featured DESC, p.sales DESC LIMIT 4',
    [$product['category_id'], $product['id']]);
$accessories = db_all(product_query() . " WHERE p.active = 1 AND p.stock > 0 AND c.icon IN ('accessoire', 'stockage') AND p.category_id <> ?
                                         ORDER BY p.sales DESC LIMIT 4", [$product['category_id']]);

$discount = discount_percent($product);
$specs = array_filter(array_map('trim', explode("\n", (string) $product['specs'])));
$stock = (int) $product['stock'];

$pageTitle = $product['name'];
$pageDescription = $product['short_desc'] . ' — ' . money($product['price']) . ' chez ' . SITE_NAME . '.';
$activePage = 'boutique';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="index.php">Accueil</a> › <a href="boutique.php?cat=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a> › <?= e($product['name']) ?>
        </nav>

        <div class="product-detail">
            <div class="product-gallery">
                <?php if ($discount): ?><span class="tag tag-promo tag-lg">-<?= $discount ?> %</span><?php endif; ?>
                <img src="<?= e(product_image($product)) ?>" alt="<?= e($product['name']) ?>">
            </div>

            <div class="product-info">
                <span class="product-cat"><?= e($product['brand']) ?><?= $product['is_used'] ? ' · Reconditionné & garanti' : ' · Neuf' ?></span>
                <h1><?= e($product['name']) ?></h1>
                <p class="lead"><?= e($product['short_desc']) ?></p>

                <div class="price price-lg">
                    <strong><?= e(money($product['price'])) ?></strong>
                    <?php if ($discount): ?>
                        <del><?= e(money($product['old_price'])) ?></del>
                        <span class="saving">Vous économisez <?= e(money($product['old_price'] - $product['price'])) ?></span>
                    <?php endif; ?>
                </div>

                <?= stock_label($stock) ?>

                <?php if ($stock > 0): ?>
                    <form action="panier.php" method="post" class="buy-box">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                        <div class="qty">
                            <button type="button" class="qty-btn" data-step="-1" aria-label="Diminuer">−</button>
                            <input type="number" name="qty" value="1" min="1" max="<?= $stock ?>" aria-label="Quantité">
                            <button type="button" class="qty-btn" data-step="1" aria-label="Augmenter">+</button>
                        </div>
                        <button class="btn btn-primary btn-lg" type="submit" name="redirect" value="product">🛒 Ajouter au panier</button>
                        <button class="btn btn-accent btn-lg" type="submit" name="redirect" value="checkout">⚡ Acheter maintenant</button>
                    </form>
                <?php endif; ?>

                <a class="btn btn-wa btn-block" target="_blank" rel="noopener"
                   href="<?= e(whatsapp_link('Bonjour ' . SITE_NAME . ', je souhaite commander : ' . $product['name'] . ' à ' . money($product['price']) . '.')) ?>">
                    💬 Commander directement sur WhatsApp
                </a>

                <ul class="product-perks">
                    <li>🚚 Livraison à N'Djamena <?= $product['price'] >= FREE_DELIVERY_FROM ? '<strong>offerte</strong>' : 'à ' . e(money(DELIVERY_FEE)) ?></li>
                    <li>💳 Paiement à la livraison ou par Mobile Money</li>
                    <li>🛡️ <?= e(WARRANTY_TEXT) ?> — SAV dans notre atelier</li>
                    <li>💿 Installation des logiciels essentiels offerte</li>
                </ul>
            </div>
        </div>

        <div class="product-tabs">
            <div class="product-tab">
                <h2>Description</h2>
                <p><?= nl2br(e($product['description'])) ?></p>
            </div>
            <?php if ($specs): ?>
                <div class="product-tab">
                    <h2>Caractéristiques techniques</h2>
                    <table class="specs">
                        <?php foreach ($specs as $line):
                            [$k, $v] = array_pad(array_map('trim', explode(':', $line, 2)), 2, ''); ?>
                            <tr><?= $v !== '' ? '<th>' . e($k) . '</th><td>' . e($v) . '</td>' : '<td colspan="2">' . e($k) . '</td>' ?></tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($accessories && in_array($product['category_icon'], ['portable', 'bureau'], true)): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-head"><h2>Complétez votre équipement</h2></div>
        <div class="product-grid">
            <?php foreach ($accessories as $p) { include __DIR__ . '/includes/product-card.php'; } ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($related): ?>
<section class="section">
    <div class="container">
        <div class="section-head"><h2>Produits similaires</h2></div>
        <div class="product-grid">
            <?php foreach ($related as $p) { include __DIR__ . '/includes/product-card.php'; } ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
