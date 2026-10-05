<?php
require __DIR__ . '/_init.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$order = db_one('SELECT * FROM orders WHERE id = ?', [$id]);
if (!$order) {
    flash('Commande introuvable.', 'error');
    redirect('commandes.php');
}

if (is_post()) {
    csrf_check();
    $new = post('status');
    if (isset(ORDER_STATUSES[$new]) && $new !== $order['status']) {
        $pdo = db();
        $pdo->beginTransaction();
        $items = db_all('SELECT product_id, quantity FROM order_items WHERE order_id = ?', [$id]);
        // Annulation : on remet les articles en stock. Réactivation : on les retire à nouveau.
        if ($new === 'annulee' || $order['status'] === 'annulee') {
            $sign = $new === 'annulee' ? 1 : -1;
            foreach ($items as $it) {
                $q = $sign * (int) $it['quantity'];
                db_exec('UPDATE products SET stock = CASE WHEN stock + ? < 0 THEN 0 ELSE stock + ? END,
                                             sales = CASE WHEN sales - ? < 0 THEN 0 ELSE sales - ? END WHERE id = ?',
                    [$q, $q, $q, $q, $it['product_id']]);
            }
        }
        db_exec('UPDATE orders SET status = ? WHERE id = ?', [$new, $id]);
        $pdo->commit();
        flash('Statut mis à jour : ' . ORDER_STATUSES[$new] . '.');
    }
    if (isset($_POST['payment_ref'])) {
        db_exec('UPDATE orders SET payment_ref = ? WHERE id = ?', [post('payment_ref'), $id]);
    }
    redirect('commande.php?id=' . $id);
}

$items = db_all('SELECT * FROM order_items WHERE order_id = ?', [$id]);
$waText = 'Bonjour ' . $order['customer_name'] . ', ici ' . SITE_NAME . '. Merci pour votre commande ' . $order['reference']
    . ' (' . money($order['total']) . '). ';

admin_header('Commande ' . $order['reference'], 'commandes');
?>
<p><a href="commandes.php">← Retour aux commandes</a></p>
<div class="admin-grid">
    <div>
        <section class="panel">
            <div class="panel-head"><h2>Articles</h2><?= status_badge($order['status'], ORDER_STATUSES) ?></div>
            <table class="table">
                <thead><tr><th>Produit</th><th>Prix unitaire</th><th>Qté</th><th class="right">Total</th></tr></thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= $it['product_id'] ? '<a href="produit-edit.php?id=' . (int) $it['product_id'] . '">' . e($it['product_name']) . '</a>' : e($it['product_name']) ?></td>
                        <td><?= e(money($it['unit_price'])) ?></td>
                        <td><?= (int) $it['quantity'] ?></td>
                        <td class="right"><?= e(money($it['unit_price'] * $it['quantity'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="3">Sous-total</td><td class="right"><?= e(money($order['subtotal'])) ?></td></tr>
                    <?php if ($order['discount']): ?><tr><td colspan="3">Remise (<?= e($order['coupon']) ?>)</td><td class="right">-<?= e(money($order['discount'])) ?></td></tr><?php endif; ?>
                    <tr><td colspan="3">Livraison</td><td class="right"><?= e(money($order['delivery_fee'])) ?></td></tr>
                    <tr><th colspan="3">Total</th><th class="right"><?= e(money($order['total'])) ?></th></tr>
                </tfoot>
            </table>
        </section>
        <?php if ($order['notes']): ?>
            <section class="panel"><h2>Instructions du client</h2><p><?= nl2br(e($order['notes'])) ?></p></section>
        <?php endif; ?>
    </div>

    <div>
        <section class="panel">
            <h2>Client</h2>
            <p><strong><?= e($order['customer_name']) ?></strong><br>
                📞 <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $order['phone'])) ?>"><?= e($order['phone']) ?></a><br>
                <?php if ($order['email']): ?>✉️ <a href="mailto:<?= e($order['email']) ?>"><?= e($order['email']) ?></a><br><?php endif; ?>
                📍 <?= e($order['address']) ?>, <?= e($order['city']) ?></p>
            <a class="btn btn-wa btn-block" target="_blank" rel="noopener"
               href="https://wa.me/<?= e(preg_replace('/\D/', '', $order['phone'])) ?>?text=<?= e(rawurlencode($waText)) ?>">💬 Contacter sur WhatsApp</a>
            <p class="muted small">Passée le <?= e(format_date($order['created_at'])) ?></p>
        </section>

        <form method="post" class="panel form">
            <?= csrf_field() ?>
            <h2>Traitement</h2>
            <p>Paiement : <strong><?= e(PAYMENT_METHODS[$order['payment_method']] ?? $order['payment_method']) ?></strong></p>
            <label>Référence de paiement<input type="text" name="payment_ref" value="<?= e($order['payment_ref']) ?>"></label>
            <label>Statut
                <select name="status">
                    <?php foreach (ORDER_STATUSES as $k => $label): ?>
                        <option value="<?= e($k) ?>" <?= $order['status'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <p class="muted small">Une commande annulée remet automatiquement les articles en stock.</p>
            <button class="btn btn-primary btn-block" type="submit">Enregistrer</button>
        </form>
    </div>
</div>
<?php admin_footer(); ?>
