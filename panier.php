<?php
require __DIR__ . '/includes/bootstrap.php';

if (is_post()) {
    csrf_check();
    $action = post('action');
    $productId = (int) post('product_id');

    if ($action === 'add' && $productId) {
        $product = db_one('SELECT id, name, stock FROM products WHERE id = ? AND active = 1', [$productId]);
        if ($product && $product['stock'] > 0) {
            $qty = max(1, (int) post('qty', '1'));
            $newQty = min((cart()[$productId] ?? 0) + $qty, (int) $product['stock']);
            cart_set($productId, $newQty);
            flash('« ' . $product['name'] . ' » a été ajouté à votre panier.');
        } else {
            flash('Ce produit n\'est plus disponible.', 'error');
        }
        if (post('redirect') === 'checkout') {
            redirect('commande.php');
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            unset($_SESSION['flash']);
            echo json_encode(['ok' => (bool) $product, 'count' => cart_count(), 'name' => $product['name'] ?? '']);
            exit;
        }
        $back = $_SERVER['HTTP_REFERER'] ?? 'panier.php';
        redirect(parse_url($back, PHP_URL_HOST) === ($_SERVER['HTTP_HOST'] ?? '') || !parse_url($back, PHP_URL_HOST) ? $back : 'panier.php');
    }

    if ($action === 'update') {
        foreach ((array) ($_POST['qty'] ?? []) as $id => $qty) {
            cart_set((int) $id, (int) $qty);
        }
        flash('Panier mis à jour.');
    } elseif ($action === 'remove' && $productId) {
        cart_set($productId, 0);
        flash('Article retiré du panier.');
    } elseif ($action === 'coupon') {
        $code = strtoupper(post('coupon'));
        $subtotal = array_sum(array_column(cart_lines(), 'total'));
        [$discount] = coupon_discount($code, $subtotal);
        if ($discount > 0) {
            $_SESSION['coupon'] = $code;
            flash('Code promo « ' . $code . ' » appliqué : -' . money($discount) . ' !');
        } else {
            unset($_SESSION['coupon']);
            flash(isset(COUPONS[$code])
                ? 'Ce code nécessite un panier d\'au moins ' . money(COUPONS[$code][2]) . '.'
                : 'Code promo invalide.', 'error');
        }
    } elseif ($action === 'clear') {
        cart_clear();
        flash('Panier vidé.');
    }
    redirect('panier.php');
}

$lines = cart_lines();
$totals = cart_totals($lines);
$remainingForFree = FREE_DELIVERY_FROM - ($totals['subtotal'] - $totals['discount']);

$pageTitle = 'Mon panier';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container"><h1>Mon panier</h1></div>
</section>

<section class="section">
    <div class="container">
        <?php if (!$lines): ?>
            <div class="empty-state">
                <p>🛒 Votre panier est vide.</p>
                <a href="boutique.php" class="btn btn-primary">Découvrir nos produits</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <form method="post" class="cart-table-wrap">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <table class="cart-table">
                        <thead><tr><th>Produit</th><th>Prix</th><th>Quantité</th><th>Total</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($lines as $line): $p = $line['product']; ?>
                            <tr>
                                <td class="cart-product">
                                    <img src="<?= e(product_image($p)) ?>" alt="" width="64" height="64">
                                    <a href="produit.php?slug=<?= e(rawurlencode($p['slug'])) ?>"><?= e($p['name']) ?></a>
                                </td>
                                <td data-label="Prix"><?= e(money($p['price'])) ?></td>
                                <td data-label="Quantité"><input type="number" name="qty[<?= (int) $p['id'] ?>]" value="<?= $line['qty'] ?>" min="0" max="<?= (int) $p['stock'] ?>" class="qty-input"></td>
                                <td data-label="Total"><strong><?= e(money($line['total'])) ?></strong></td>
                                <td><button class="link-danger" type="submit" form="remove-<?= (int) $p['id'] ?>" aria-label="Retirer">✕</button></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="cart-actions">
                        <a href="boutique.php" class="btn btn-outline">← Continuer mes achats</a>
                        <button class="btn btn-outline" type="submit">Mettre à jour</button>
                    </div>
                </form>
                <?php foreach ($lines as $line): ?>
                    <form method="post" id="remove-<?= (int) $line['product']['id'] ?>" hidden>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="product_id" value="<?= (int) $line['product']['id'] ?>">
                    </form>
                <?php endforeach; ?>

                <aside class="cart-summary">
                    <h2>Récapitulatif</h2>
                    <?php if ($remainingForFree > 0): ?>
                        <div class="free-delivery">
                            Plus que <strong><?= e(money($remainingForFree)) ?></strong> pour la livraison offerte !
                            <div class="progress"><span style="width: <?= min(100, (int) (($totals['subtotal'] - $totals['discount']) * 100 / FREE_DELIVERY_FROM)) ?>%"></span></div>
                        </div>
                    <?php else: ?>
                        <div class="free-delivery ok">🎉 Livraison offerte à N'Djamena !</div>
                    <?php endif; ?>

                    <dl class="totals">
                        <dt>Sous-total</dt><dd><?= e(money($totals['subtotal'])) ?></dd>
                        <?php if ($totals['discount']): ?>
                            <dt>Remise (<?= e($totals['coupon']) ?>)</dt><dd class="text-success">-<?= e(money($totals['discount'])) ?></dd>
                        <?php endif; ?>
                        <dt>Livraison</dt><dd><?= $totals['delivery'] ? e(money($totals['delivery'])) : 'Offerte' ?></dd>
                        <dt class="grand">Total</dt><dd class="grand"><?= e(money($totals['total'])) ?></dd>
                    </dl>

                    <form method="post" class="coupon-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="coupon">
                        <input type="text" name="coupon" placeholder="Code promo" value="<?= e($totals['coupon'] ?? '') ?>" aria-label="Code promo">
                        <button class="btn btn-outline" type="submit">Appliquer</button>
                    </form>

                    <a href="commande.php" class="btn btn-accent btn-lg btn-block">Passer la commande →</a>
                    <p class="muted small">Paiement à la livraison, Airtel Money ou Moov Money.</p>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
