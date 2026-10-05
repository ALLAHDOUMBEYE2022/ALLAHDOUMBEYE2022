<?php
require __DIR__ . '/_init.php';
require_admin();

$stats = [
    'ca_mois'    => (int) db_value("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'annulee' AND created_at >= ?", [date('Y-m-01 00:00:00')]),
    'ca_total'   => (int) db_value("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'livree'"),
    'commandes'  => (int) db_value("SELECT COUNT(*) FROM orders WHERE status = 'nouvelle'"),
    'demandes'   => (int) db_value("SELECT COUNT(*) FROM service_requests WHERE status IN ('nouvelle', 'en_cours')"),
    'produits'   => (int) db_value('SELECT COUNT(*) FROM products WHERE active = 1'),
    'messages'   => (int) db_value('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
    'abonnes'    => (int) db_value('SELECT COUNT(*) FROM newsletter'),
];
$recentOrders = db_all('SELECT * FROM orders ORDER BY id DESC LIMIT 8');
$recentRequests = db_all('SELECT r.*, s.name AS service_name FROM service_requests r LEFT JOIN services s ON s.id = r.service_id ORDER BY r.id DESC LIMIT 5');
$lowStock = db_all('SELECT id, name, stock FROM products WHERE active = 1 AND stock <= 3 ORDER BY stock ASC LIMIT 8');
$topProducts = db_all('SELECT id, name, sales, price FROM products ORDER BY sales DESC LIMIT 5');

admin_header('Tableau de bord', 'index');
?>
<div class="kpi-grid">
    <div class="kpi"><span>Chiffre d'affaires du mois</span><strong><?= e(money($stats['ca_mois'])) ?></strong><small>Commandes non annulées</small></div>
    <div class="kpi"><span>CA encaissé (livré)</span><strong><?= e(money($stats['ca_total'])) ?></strong><small>Depuis l'ouverture</small></div>
    <a class="kpi kpi-alert" href="commandes.php?status=nouvelle"><span>Nouvelles commandes</span><strong><?= $stats['commandes'] ?></strong><small>À confirmer</small></a>
    <a class="kpi" href="demandes.php"><span>Demandes de service</span><strong><?= $stats['demandes'] ?></strong><small>Nouvelles ou en cours</small></a>
    <a class="kpi" href="messages.php"><span>Messages non lus</span><strong><?= $stats['messages'] ?></strong><small>Formulaire de contact</small></a>
    <a class="kpi" href="produits.php"><span>Produits en ligne</span><strong><?= $stats['produits'] ?></strong><small><?= $stats['abonnes'] ?> abonnés newsletter</small></a>
</div>

<div class="admin-grid">
    <section class="panel">
        <div class="panel-head"><h2>Dernières commandes</h2><a href="commandes.php">Tout voir →</a></div>
        <?php if ($recentOrders): ?>
            <table class="table">
                <thead><tr><th>Réf.</th><th>Client</th><th>Total</th><th>Statut</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($recentOrders as $o): ?>
                    <tr>
                        <td><a href="commande.php?id=<?= (int) $o['id'] ?>"><?= e($o['reference']) ?></a></td>
                        <td><?= e($o['customer_name']) ?><br><small class="muted"><?= e($o['phone']) ?></small></td>
                        <td><?= e(money($o['total'])) ?></td>
                        <td><?= status_badge($o['status'], ORDER_STATUSES) ?></td>
                        <td><small><?= e(format_date($o['created_at'])) ?></small></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?><p class="muted">Aucune commande pour le moment.</p><?php endif; ?>
    </section>

    <div>
        <section class="panel">
            <div class="panel-head"><h2>⚠️ Stock faible</h2><a href="produits.php?stock=low">Gérer →</a></div>
            <?php if ($lowStock): ?>
                <ul class="plain-list">
                    <?php foreach ($lowStock as $p): ?>
                        <li><a href="produit-edit.php?id=<?= (int) $p['id'] ?>"><?= e($p['name']) ?></a>
                            <span class="badge <?= $p['stock'] <= 0 ? 'badge-annulee' : 'badge-nouvelle' ?>"><?= (int) $p['stock'] ?></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?><p class="muted">Tous les stocks sont corrects.</p><?php endif; ?>
        </section>

        <section class="panel">
            <div class="panel-head"><h2>🏆 Meilleures ventes</h2></div>
            <ul class="plain-list">
                <?php foreach ($topProducts as $p): ?>
                    <li><span><?= e($p['name']) ?></span><strong><?= (int) $p['sales'] ?></strong></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="panel">
            <div class="panel-head"><h2>Demandes récentes</h2><a href="demandes.php">Tout voir →</a></div>
            <?php if ($recentRequests): ?>
                <ul class="plain-list">
                    <?php foreach ($recentRequests as $r): ?>
                        <li><span><?= e($r['name']) ?> — <small class="muted"><?= e($r['service_name'] ?? '') ?></small></span><?= status_badge($r['status'], REQUEST_STATUSES) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?><p class="muted">Aucune demande.</p><?php endif; ?>
        </section>
    </div>
</div>
<?php admin_footer(); ?>
