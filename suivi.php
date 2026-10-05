<?php
require __DIR__ . '/includes/bootstrap.php';

$order = null;
$items = [];
$searched = false;
if (isset($_GET['ref'], $_GET['phone'])) {
    $searched = true;
    $phone = preg_replace('/\D/', '', (string) $_GET['phone']);
    $candidate = db_one('SELECT * FROM orders WHERE reference = ?', [strtoupper(trim((string) $_GET['ref']))]);
    // Le numéro doit correspondre (comparaison sur les 8 derniers chiffres).
    if ($candidate && strlen($phone) >= 8 && substr(preg_replace('/\D/', '', $candidate['phone']), -8) === substr($phone, -8)) {
        $order = $candidate;
        $items = db_all('SELECT * FROM order_items WHERE order_id = ?', [$order['id']]);
    }
}

$steps = ['nouvelle', 'confirmee', 'expediee', 'livree'];
$pageTitle = 'Suivre ma commande';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container"><h1>Suivre ma commande</h1></div>
</section>

<section class="section">
    <div class="container narrow">
        <form method="get" class="form card">
            <div class="form-row">
                <label>Référence de commande<input type="text" name="ref" required placeholder="MT261005-XXXXX" value="<?= e($_GET['ref'] ?? '') ?>"></label>
                <label>Téléphone utilisé<input type="tel" name="phone" required value="<?= e($_GET['phone'] ?? '') ?>"></label>
            </div>
            <button class="btn btn-primary" type="submit">Rechercher</button>
        </form>

        <?php if ($searched && !$order): ?>
            <div class="alert alert-error">Aucune commande trouvée avec ces informations.</div>
        <?php elseif ($order): ?>
            <div class="card">
                <h2>Commande <?= e($order['reference']) ?></h2>
                <p>Passée le <?= e(format_date($order['created_at'])) ?> — <?= status_badge($order['status'], ORDER_STATUSES) ?></p>
                <?php if ($order['status'] !== 'annulee'): $idx = array_search($order['status'], $steps, true); ?>
                    <ol class="tracker">
                        <?php foreach ($steps as $i => $s): ?>
                            <li class="<?= $i <= $idx ? 'done' : '' ?>"><?= e(ORDER_STATUSES[$s]) ?></li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
                <table class="specs">
                    <?php foreach ($items as $it): ?>
                        <tr><td><?= (int) $it['quantity'] ?> × <?= e($it['product_name']) ?></td><td class="right"><?= e(money($it['unit_price'] * $it['quantity'])) ?></td></tr>
                    <?php endforeach; ?>
                    <tr><th>Total</th><th class="right"><?= e(money($order['total'])) ?></th></tr>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
