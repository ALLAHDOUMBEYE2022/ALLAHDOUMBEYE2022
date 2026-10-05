<?php
require __DIR__ . '/includes/bootstrap.php';

$ref = (string) ($_GET['ref'] ?? '');
// Seul le client qui vient de commander (même session) peut voir cette page.
if (!in_array($ref, $_SESSION['orders'] ?? [], true)) {
    redirect('suivi.php');
}
$order = db_one('SELECT * FROM orders WHERE reference = ?', [$ref]);
if (!$order) {
    redirect('suivi.php');
}
$items = db_all('SELECT * FROM order_items WHERE order_id = ?', [$order['id']]);

$summary = "Bonjour " . SITE_NAME . ", je viens de passer la commande " . $order['reference'] . " :\n";
foreach ($items as $it) {
    $summary .= '- ' . $it['quantity'] . ' x ' . $it['product_name'] . ' (' . money($it['unit_price']) . ")\n";
}
$summary .= 'Total : ' . money($order['total']) . "\n"
    . 'Paiement : ' . PAYMENT_METHODS[$order['payment_method']] . "\n"
    . 'Nom : ' . $order['customer_name'] . "\nLivraison : " . $order['address'] . ', ' . $order['city'];

$pageTitle = 'Commande confirmée';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <ol class="steps"><li class="done">Panier</li><li class="done">Livraison & paiement</li><li class="current">Confirmation</li></ol>
    </div>
</section>

<section class="section">
    <div class="container narrow">
        <div class="confirm-box">
            <div class="confirm-icon">✅</div>
            <h1>Merci <?= e($order['customer_name']) ?> !</h1>
            <p>Votre commande <strong><?= e($order['reference']) ?></strong> a bien été enregistrée.
                Un conseiller vous contactera au <strong><?= e($order['phone']) ?></strong> pour confirmer la livraison.</p>

            <?php if ($order['payment_method'] === 'mobile_money'): ?>
                <div class="alert alert-info">
                    <strong>Paiement Mobile Money :</strong> envoyez <?= e(money($order['total'])) ?> à
                    <?php $mm = []; foreach (MOBILE_MONEY as $op => $num) { $mm[] = e($op) . ' ' . e($num); } echo implode(' ou ', $mm); ?>
                    en indiquant la référence <strong><?= e($order['reference']) ?></strong>.
                </div>
            <?php endif; ?>

            <table class="specs">
                <?php foreach ($items as $it): ?>
                    <tr><td><?= (int) $it['quantity'] ?> × <?= e($it['product_name']) ?></td><td class="right"><?= e(money($it['unit_price'] * $it['quantity'])) ?></td></tr>
                <?php endforeach; ?>
                <?php if ($order['discount']): ?><tr><td>Remise (<?= e($order['coupon']) ?>)</td><td class="right">-<?= e(money($order['discount'])) ?></td></tr><?php endif; ?>
                <tr><td>Livraison</td><td class="right"><?= $order['delivery_fee'] ? e(money($order['delivery_fee'])) : 'Offerte' ?></td></tr>
                <tr><th>Total</th><th class="right"><?= e(money($order['total'])) ?></th></tr>
            </table>

            <a class="btn btn-wa btn-lg btn-block" target="_blank" rel="noopener" href="<?= e(whatsapp_link($summary)) ?>"
               <?= $order['payment_method'] === 'whatsapp' ? 'data-autoopen="1"' : '' ?>>💬 Envoyer le récapitulatif sur WhatsApp</a>
            <a class="btn btn-outline btn-block" href="boutique.php">Continuer mes achats</a>
            <p class="muted small">Conservez votre référence pour <a href="suivi.php">suivre votre commande</a>.</p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
