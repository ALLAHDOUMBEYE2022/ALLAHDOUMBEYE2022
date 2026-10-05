<?php
require __DIR__ . '/_init.php';
require_admin();

if (is_post()) {
    csrf_check();
    $id = (int) post('id');
    if (post('action') === 'delete') {
        db_exec('DELETE FROM messages WHERE id = ?', [$id]);
        flash('Message supprimé.');
    } elseif (post('action') === 'read') {
        db_exec('UPDATE messages SET is_read = 1 - is_read WHERE id = ?', [$id]);
    }
    redirect('messages.php');
}

$messages = db_all('SELECT * FROM messages ORDER BY is_read ASC, id DESC LIMIT 300');

admin_header('Messages', 'messages');
?>
<div class="cards-list">
    <?php foreach ($messages as $m): ?>
        <article class="panel <?= $m['is_read'] ? 'row-muted' : 'unread' ?>">
            <div class="panel-head">
                <h2><?= e($m['subject'] ?: 'Sans sujet') ?></h2>
                <small class="muted"><?= e(format_date($m['created_at'])) ?></small>
            </div>
            <p><strong><?= e($m['name']) ?></strong>
                <?php if ($m['phone']): ?> · 📞 <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $m['phone'])) ?>"><?= e($m['phone']) ?></a><?php endif; ?>
                <?php if ($m['email']): ?> · ✉️ <a href="mailto:<?= e($m['email']) ?>?subject=<?= e(rawurlencode('Re: ' . $m['subject'])) ?>"><?= e($m['email']) ?></a><?php endif; ?></p>
            <blockquote><?= nl2br(e($m['message'])) ?></blockquote>
            <div class="actions">
                <form method="post">
                    <?= csrf_field() ?><input type="hidden" name="action" value="read"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                    <button class="btn btn-outline btn-sm" type="submit"><?= $m['is_read'] ? 'Marquer non lu' : 'Marquer comme lu' ?></button>
                </form>
                <?php if ($m['phone']): ?>
                    <a class="btn btn-wa btn-sm" target="_blank" rel="noopener" href="https://wa.me/<?= e(preg_replace('/\D/', '', $m['phone'])) ?>">WhatsApp</a>
                <?php endif; ?>
                <form method="post" data-confirm="Supprimer ce message ?">
                    <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit">Supprimer</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$messages): ?><p class="muted">Aucun message.</p><?php endif; ?>
</div>
<?php admin_footer(); ?>
