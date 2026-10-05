<?php
require __DIR__ . '/_init.php';
require_admin();

if (is_post()) {
    csrf_check();
    $id = (int) post('id');
    if (post('action') === 'delete') {
        db_exec('DELETE FROM service_requests WHERE id = ?', [$id]);
        flash('Demande supprimée.');
    } elseif (isset(REQUEST_STATUSES[post('status')])) {
        db_exec('UPDATE service_requests SET status = ? WHERE id = ?', [post('status'), $id]);
        flash('Statut mis à jour.');
    }
    redirect('demandes.php' . (isset($_GET['status']) ? '?status=' . urlencode((string) $_GET['status']) : ''));
}

$status = (string) ($_GET['status'] ?? '');
$params = [];
$sql = 'SELECT r.*, s.name AS service_name, s.icon FROM service_requests r LEFT JOIN services s ON s.id = r.service_id';
if (isset(REQUEST_STATUSES[$status])) {
    $sql .= ' WHERE r.status = ?';
    $params[] = $status;
}
$requests = db_all($sql . ' ORDER BY r.id DESC LIMIT 300', $params);

admin_header('Demandes de service', 'demandes');
?>
<div class="tabs">
    <a href="demandes.php" class="<?= $status === '' ? 'active' : '' ?>">Toutes</a>
    <?php foreach (REQUEST_STATUSES as $k => $label): ?>
        <a href="demandes.php?status=<?= e($k) ?>" class="<?= $status === $k ? 'active' : '' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>

<div class="cards-list">
    <?php foreach ($requests as $r): ?>
        <article class="panel request">
            <div class="panel-head">
                <h2><?= e(($r['icon'] ?? '') . ' ' . ($r['service_name'] ?? 'Service supprimé')) ?> <small class="muted">— <?= e($r['reference']) ?></small></h2>
                <?= status_badge($r['status'], REQUEST_STATUSES) ?>
            </div>
            <div class="request-grid">
                <div>
                    <p><strong><?= e($r['name']) ?></strong><br>
                        📞 <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $r['phone'])) ?>"><?= e($r['phone']) ?></a>
                        <?php if ($r['email']): ?> · ✉️ <a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a><?php endif; ?></p>
                    <p class="small">
                        <?php if ($r['device']): ?>💻 <?= e($r['device']) ?><br><?php endif; ?>
                        📍 <?= $r['location'] === 'domicile' ? 'À domicile / en entreprise' : 'En atelier' ?><br>
                        <?php if ($r['preferred_date']): ?>📅 Souhaité le <?= e(date('d/m/Y', strtotime($r['preferred_date']))) ?><br><?php endif; ?>
                        🕒 Reçue le <?= e(format_date($r['created_at'])) ?>
                    </p>
                    <blockquote><?= nl2br(e($r['description'])) ?></blockquote>
                </div>
                <div class="request-actions">
                    <form method="post" class="inline-form">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                        <select name="status">
                            <?php foreach (REQUEST_STATUSES as $k => $label): ?>
                                <option value="<?= e($k) ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary btn-sm" type="submit">OK</button>
                    </form>
                    <a class="btn btn-wa btn-sm" target="_blank" rel="noopener"
                       href="https://wa.me/<?= e(preg_replace('/\D/', '', $r['phone'])) ?>?text=<?= e(rawurlencode('Bonjour ' . $r['name'] . ', ici ' . SITE_NAME . ' au sujet de votre demande ' . $r['reference'] . '. ')) ?>">WhatsApp</a>
                    <form method="post" data-confirm="Supprimer cette demande ?">
                        <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                        <button class="btn btn-danger btn-sm" type="submit">Supprimer</button>
                    </form>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$requests): ?><p class="muted">Aucune demande.</p><?php endif; ?>
</div>
<?php admin_footer(); ?>
