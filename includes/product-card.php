<?php
/** Carte produit réutilisable. Attend une variable $p (ligne produit + catégorie). */
$discount = discount_percent($p);
$productUrl = 'produit.php?slug=' . rawurlencode($p['slug']);
?>
<article class="product-card">
    <div class="product-badges">
        <?php if ($discount): ?><span class="tag tag-promo">-<?= $discount ?> %</span><?php endif; ?>
        <?php if (!empty($p['is_used'])): ?><span class="tag tag-used">Reconditionné</span><?php endif; ?>
        <?php if ((int) $p['stock'] > 0 && (int) $p['stock'] <= 3): ?><span class="tag tag-hot">Stock limité</span><?php endif; ?>
    </div>
    <a href="<?= e($productUrl) ?>" class="product-thumb">
        <img src="<?= e(product_image($p)) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
    </a>
    <div class="product-body">
        <span class="product-cat"><?= e($p['category_name']) ?><?= $p['brand'] ? ' · ' . e($p['brand']) : '' ?></span>
        <h3><a href="<?= e($productUrl) ?>"><?= e($p['name']) ?></a></h3>
        <div class="price">
            <strong><?= e(money($p['price'])) ?></strong>
            <?php if ($discount): ?><del><?= e(money($p['old_price'])) ?></del><?php endif; ?>
        </div>
        <?= stock_label((int) $p['stock']) ?>
        <div class="product-actions">
            <?php if ((int) $p['stock'] > 0): ?>
                <form action="panier.php" method="post" class="add-to-cart">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                    <button class="btn btn-primary btn-sm" type="submit">🛒 Ajouter</button>
                </form>
            <?php endif; ?>
            <a class="btn btn-wa btn-sm" target="_blank" rel="noopener"
               href="<?= e(whatsapp_link('Bonjour, je suis intéressé(e) par : ' . $p['name'] . ' (' . money($p['price']) . '). Est-il disponible ?')) ?>">WhatsApp</a>
        </div>
    </div>
</article>
