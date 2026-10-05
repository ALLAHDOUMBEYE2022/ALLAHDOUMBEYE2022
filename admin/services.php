<?php
require __DIR__ . '/_init.php';
require_admin();

if (is_post()) {
    csrf_check();
    $id = (int) post('id');
    if (post('action') === 'delete' && $id) {
        db_exec('DELETE FROM services WHERE id = ?', [$id]);
        flash('Service supprimé.');
        redirect('services.php');
    }
    $name = post('name');
    if (mb_strlen($name) < 2) {
        flash('Le nom du service est obligatoire.', 'error');
        redirect('services.php' . ($id ? '?edit=' . $id : ''));
    }
    $slug = slugify($name);
    $base = $slug;
    $n = 2;
    while (db_value('SELECT COUNT(*) FROM services WHERE slug = ? AND id <> ?', [$slug, $id])) {
        $slug = $base . '-' . $n++;
    }
    $params = [$name, $slug, mb_substr(post('icon') ?: '🛠️', 0, 4), post('short_desc'), post('description'),
        max(0, (int) post('price_from')), post('duration'), (int) post('sort_order'), isset($_POST['active']) ? 1 : 0];
    if ($id) {
        db_exec('UPDATE services SET name = ?, slug = ?, icon = ?, short_desc = ?, description = ?, price_from = ?, duration = ?, sort_order = ?, active = ?
                 WHERE id = ?', [...$params, $id]);
        flash('Service mis à jour.');
    } else {
        db_exec('INSERT INTO services (name, slug, icon, short_desc, description, price_from, duration, sort_order, active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', $params);
        flash('Service ajouté.');
    }
    redirect('services.php');
}

$services = db_all('SELECT s.*, (SELECT COUNT(*) FROM service_requests r WHERE r.service_id = s.id) AS requests FROM services s ORDER BY sort_order, name');
$edit = isset($_GET['edit']) ? db_one('SELECT * FROM services WHERE id = ?', [(int) $_GET['edit']]) : null;
$form = $edit ?? ['id' => 0, 'name' => '', 'icon' => '🛠️', 'short_desc' => '', 'description' => '', 'price_from' => 0,
    'duration' => '', 'sort_order' => count($services), 'active' => 1];

admin_header('Services', 'services');
?>
<div class="admin-grid">
    <div class="panel table-wrap">
        <table class="table">
            <thead><tr><th></th><th>Service</th><th>Prix</th><th>Demandes</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($services as $s): ?>
                <tr class="<?= $s['active'] ? '' : 'row-muted' ?>">
                    <td class="emoji"><?= e($s['icon']) ?></td>
                    <td><strong><?= e($s['name']) ?></strong><?= $s['active'] ? '' : ' <small>(masqué)</small>' ?><br><small class="muted"><?= e($s['short_desc']) ?></small></td>
                    <td><?= $s['price_from'] > 0 ? e(money($s['price_from'])) : 'Sur devis' ?></td>
                    <td><?= (int) $s['requests'] ?></td>
                    <td class="actions">
                        <a class="btn btn-outline btn-xs" href="services.php?edit=<?= (int) $s['id'] ?>">Modifier</a>
                        <form method="post" data-confirm="Supprimer ce service ?">
                            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                            <button class="btn btn-danger btn-xs" type="submit">Suppr.</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <form method="post" class="panel form">
        <h2><?= $edit ? 'Modifier le service' : 'Nouveau service' ?></h2>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
        <div class="form-row form-row-icon">
            <label>Icône<input type="text" name="icon" value="<?= e($form['icon']) ?>" maxlength="4"></label>
            <label>Nom *<input type="text" name="name" required value="<?= e($form['name']) ?>"></label>
        </div>
        <label>Résumé<input type="text" name="short_desc" maxlength="255" value="<?= e($form['short_desc']) ?>"></label>
        <label>Détails <small class="muted">(un point par ligne)</small><textarea name="description" rows="5"><?= e($form['description']) ?></textarea></label>
        <div class="form-row">
            <label>À partir de (<?= e(CURRENCY) ?>, 0 = devis)<input type="number" name="price_from" min="0" step="500" value="<?= (int) $form['price_from'] ?>"></label>
            <label>Durée<input type="text" name="duration" value="<?= e($form['duration']) ?>" placeholder="Ex. : 24 à 72 h"></label>
        </div>
        <label>Ordre d'affichage<input type="number" name="sort_order" value="<?= (int) $form['sort_order'] ?>"></label>
        <label class="checkbox"><input type="checkbox" name="active" <?= $form['active'] ? 'checked' : '' ?>> Visible sur le site</label>
        <button class="btn btn-accent btn-block" type="submit"><?= $edit ? 'Enregistrer' : 'Ajouter' ?></button>
        <?php if ($edit): ?><a href="services.php" class="btn btn-outline btn-block">Annuler</a><?php endif; ?>
    </form>
</div>
<?php admin_footer(); ?>
