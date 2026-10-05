<?php
require __DIR__ . '/_init.php';
require_admin();

if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="newsletter-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM pour Excel
    fputcsv($out, ['email', 'date_inscription'], ';');
    foreach (db_all('SELECT email, created_at FROM newsletter ORDER BY id DESC') as $row) {
        fputcsv($out, [$row['email'], $row['created_at']], ';');
    }
    exit;
}

if (is_post()) {
    csrf_check();
    db_exec('DELETE FROM newsletter WHERE id = ?', [(int) post('id')]);
    flash('Abonné supprimé.');
    redirect('abonnes.php');
}

$subscribers = db_all('SELECT * FROM newsletter ORDER BY id DESC');

admin_header('Newsletter', 'abonnes');
?>
<div class="toolbar">
    <p><strong><?= count($subscribers) ?></strong> abonné(s)</p>
    <a href="abonnes.php?export=csv" class="btn btn-primary">⬇ Exporter (CSV / Excel)</a>
</div>
<div class="panel table-wrap">
    <table class="table">
        <thead><tr><th>E-mail</th><th>Inscription</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($subscribers as $s): ?>
            <tr>
                <td><a href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a></td>
                <td><?= e(format_date($s['created_at'])) ?></td>
                <td class="actions">
                    <form method="post" data-confirm="Supprimer cet abonné ?">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                        <button class="btn btn-danger btn-xs" type="submit">Suppr.</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$subscribers): ?><tr><td colspan="3" class="muted center">Aucun abonné pour le moment.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php admin_footer(); ?>
