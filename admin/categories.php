<?php
require __DIR__ . '/_init.php';
require_admin();

const CATEGORY_ICONS = ['portable' => 'Ordinateur portable', 'bureau' => 'Ordinateur de bureau', 'imprimante' => 'Imprimante',
    'accessoire' => 'Accessoires', 'stockage' => 'Stockage', 'reseau' => 'Réseau'];

if (is_post()) {
    csrf_check();
    $id = (int) post('id');
    if (post('action') === 'delete' && $id) {
        if (db_value('SELECT COUNT(*) FROM products WHERE category_id = ?', [$id])) {
            flash('Impossible : cette catégorie contient encore des produits.', 'error');
        } else {
            db_exec('DELETE FROM categories WHERE id = ?', [$id]);
            flash('Catégorie supprimée.');
        }
    } else {
        $name = post('name');
        $icon = array_key_exists(post('icon'), CATEGORY_ICONS) ? post('icon') : 'accessoire';
        if (mb_strlen($name) < 2) {
            flash('Le nom de la catégorie est obligatoire.', 'error');
        } else {
            $slug = slugify($name);
            $base = $slug;
            $n = 2;
            while (db_value('SELECT COUNT(*) FROM categories WHERE slug = ? AND id <> ?', [$slug, $id])) {
                $slug = $base . '-' . $n++;
            }
            $params = [$name, $slug, $icon, post('description'), (int) post('sort_order')];
            if ($id) {
                db_exec('UPDATE categories SET name = ?, slug = ?, icon = ?, description = ?, sort_order = ? WHERE id = ?', [...$params, $id]);
                flash('Catégorie mise à jour.');
            } else {
                db_exec('INSERT INTO categories (name, slug, icon, description, sort_order) VALUES (?, ?, ?, ?, ?)', $params);
                flash('Catégorie ajoutée.');
            }
        }
    }
    redirect('categories.php');
}

$categories = get_categories();
$edit = isset($_GET['edit']) ? db_one('SELECT * FROM categories WHERE id = ?', [(int) $_GET['edit']]) : null;
$form = $edit ?? ['id' => 0, 'name' => '', 'icon' => 'accessoire', 'description' => '', 'sort_order' => count($categories)];

admin_header('Catégories', 'categories');
?>
<div class="admin-grid">
    <div class="panel table-wrap">
        <table class="table">
            <thead><tr><th></th><th>Nom</th><th>Produits</th><th>Ordre</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td><img src="../assets/img/<?= e($c['icon']) ?>.svg" alt="" class="thumb"></td>
                    <td><strong><?= e($c['name']) ?></strong><br><small class="muted"><?= e($c['description']) ?></small></td>
                    <td><?= (int) $c['total'] ?></td>
                    <td><?= (int) $c['sort_order'] ?></td>
                    <td class="actions">
                        <a class="btn btn-outline btn-xs" href="categories.php?edit=<?= (int) $c['id'] ?>">Modifier</a>
                        <form method="post" data-confirm="Supprimer cette catégorie ?">
                            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                            <button class="btn btn-danger btn-xs" type="submit">Suppr.</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <form method="post" class="panel form">
        <h2><?= $edit ? 'Modifier la catégorie' : 'Nouvelle catégorie' ?></h2>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
        <label>Nom *<input type="text" name="name" required value="<?= e($form['name']) ?>"></label>
        <label>Illustration
            <select name="icon">
                <?php foreach (CATEGORY_ICONS as $k => $label): ?>
                    <option value="<?= e($k) ?>" <?= $form['icon'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Description<input type="text" name="description" maxlength="255" value="<?= e($form['description']) ?>"></label>
        <label>Ordre d'affichage<input type="number" name="sort_order" value="<?= (int) $form['sort_order'] ?>"></label>
        <button class="btn btn-accent btn-block" type="submit"><?= $edit ? 'Enregistrer' : 'Ajouter' ?></button>
        <?php if ($edit): ?><a href="categories.php" class="btn btn-outline btn-block">Annuler</a><?php endif; ?>
    </form>
</div>
<?php admin_footer(); ?>
