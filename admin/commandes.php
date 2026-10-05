<?php
require __DIR__ . '/_init.php';
require_admin();

$status = (string) ($_GET['status'] ?? '');
$q = trim((string) ($_GET['q'] ?? ''));
$where = ['1 = 1'];
$params = [];
if (isset(ORDER_STATUSES[$status])) {
    $where[] = 'status = ?';
    $params[] = $status;
}
if ($q !== '') {
    $where[] = '(reference LIKE ? OR customer_name LIKE ? OR phone LIKE ?)';
    array_push($params, "%$q%", "%$q%", "%$q%");
}
$orders = db_all('SELECT o.*, (SELECT SUM(quantity) FROM order_items i WHERE i.order_id = o.id) AS items
                  FROM orders o WHERE ' . implode(' AND ', $where) . ' ORDER BY o.id DESC LIMIT 300', $params);
$counts = [];
foreach (db_all('SELECT status, COUNT(*) AS n FROM orders GROUP BY status') as $row) {
    $counts[$row['status']] = (int) $row['n'];
}

admin_header('Commandes', 'commandes');
?>
<div class="tabs">
    <a href="commandes.php" class="<?= $status === '' ? 'active' : '' ?>">Toutes (<?= array_sum($counts) ?>)</a>
    <?php foreach (ORDER_STATUSES as $k => $label): ?>
        <a href="commandes.php?status=<?= e($k) ?>" class="<?= $status === $k ? 'active' : '' ?>"><?= e($label) ?> (<?= $counts[$k] ?? 0 ?>)</a>
    <?php endforeach; ?>
</div>

<form method="get" class="toolbar-filters">
    <?php if ($status): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
    <input type="search" name="q" placeholder="Référence, nom ou téléphone…" value="<?= e($q) ?>">
    <button class="btn btn-outline btn-sm" type="submit">Rechercher</button>
</form>

<div class="panel table-wrap">
    <table class="table">
        <thead><tr><th>Réf.</th><th>Client</th><th>Articles</th><th>Total</th><th>Paiement</th><th>Statut</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><a href="commande.php?id=<?= (int) $o['id'] ?>"><strong><?= e($o['reference']) ?></strong></a></td>
                <td><?= e($o['customer_name']) ?><br><small class="muted"><?= e($o['phone']) ?> · <?= e($o['city']) ?></small></td>
                <td><?= (int) $o['items'] ?></td>
                <td><strong><?= e(money($o['total'])) ?></strong></td>
                <td><small><?= e(PAYMENT_METHODS[$o['payment_method']] ?? $o['payment_method']) ?></small></td>
                <td><?= status_badge($o['status'], ORDER_STATUSES) ?></td>
                <td><small><?= e(format_date($o['created_at'])) ?></small></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?><tr><td colspan="7" class="muted center">Aucune commande.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php admin_footer(); ?>
