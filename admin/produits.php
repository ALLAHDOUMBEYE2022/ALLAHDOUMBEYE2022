<?php
require __DIR__ . '/_init.php';
require_admin();

if (is_post()) {
    csrf_check();
    $id = (int) post('id');
    if (post('action') === 'delete' && $id) {
        $p = db_one('SELECT image FROM products WHERE id = ?', [$id]);
        $hasOrders = (int) db_value('SELECT COUNT(*) FROM order_items WHERE product_id = ?', [$id]);
        if ($hasOrders) {
            // Conserve l'historique des commandes : le produit est simplement masqué.
            db_exec('UPDATE products SET active = 0 WHERE id = ?', [$id]);
            flash('Produit masqué (il figure dans des commandes, il n\'a donc pas été supprimé).');
        } else {
            db_exec('DELETE FROM products WHERE id = ?', [$id]);
            delete_upload($p['image'] ?? null);
            flash('Produit supprimé.');
        }
    } elseif (post('action') === 'toggle' && $id) {
        db_exec('UPDATE products SET active = 1 - active WHERE id = ?', [$id]);
        flash('Visibilité du produit mise à jour.');
    } elseif (post('action') === 'stock' && $id) {
        db_exec('UPDATE products SET stock = ? WHERE id = ?', [max(0, (int) post('stock')), $id]);
        flash('Stock mis à jour.');
    }
    redirect('produits.php?' . http_build_query(array_intersect_key($_GET, array_flip(['q', 'cat', 'stock']))));
}

$q = trim((string) ($_GET['q'] ?? ''));
$cat = (int) ($_GET['cat'] ?? 0);
$where = ['1 = 1'];
$params = [];
if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.brand LIKE ?)';
    array_push($params, "%$q%", "%$q%");
}
if ($cat) {
    $where[] = 'p.category_id = ?';
    $params[] = $cat;
}
if (($_GET['stock'] ?? '') === 'low') {
    $where[] = 'p.stock <= 3';
}
$products = db_all(product_query() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY p.id DESC', $params);
$categories = get_categories();

admin_header('Produits', 'produits');
?>
<div class="toolbar">
    <form method="get" class="toolbar-filters">
        <input type="search" name="q" placeholder="Rechercher…" value="<?= e($q) ?>">
        <select name="cat">
            <option value="">Toutes catégories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= $cat === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label class="checkbox"><input type="checkbox" name="stock" value="low" <?= ($_GET['stock'] ?? '') === 'low' ? 'checked' : '' ?>> Stock faible</label>
        <button class="btn btn-outline btn-sm" type="submit">Filtrer</button>
    </form>
    <a href="produit-edit.php" class="btn btn-accent">+ Nouveau produit</a>
</div>

<div class="panel table-wrap">
    <table class="table">
        <thead><tr><th></th><th>Produit</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Ventes</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr class="<?= $p['active'] ? '' : 'row-muted' ?>">
                <td><img src="<?= e(product_image($p, '../')) ?>" alt="" class="thumb"></td>
                <td><a href="produit-edit.php?id=<?= (int) $p['id'] ?>"><strong><?= e($p['name']) ?></strong></a><br>
                    <small class="muted"><?= e($p['brand']) ?><?= $p['featured'] ? ' · ⭐ Vedette' : '' ?><?= $p['is_used'] ? ' · Reconditionné' : '' ?></small></td>
                <td><?= e($p['category_name']) ?></td>
                <td><?= e(money($p['price'])) ?><?php if (discount_percent($p)): ?><br><small class="text-danger">-<?= discount_percent($p) ?> %</small><?php endif; ?></td>
                <td>
                    <form method="post" class="inline-form">
                        <?= csrf_field() ?><input type="hidden" name="action" value="stock"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <input type="number" name="stock" value="<?= (int) $p['stock'] ?>" min="0" class="input-xs <?= $p['stock'] <= 3 ? 'warn' : '' ?>">
                        <button class="btn btn-outline btn-xs" type="submit" title="Enregistrer le stock">✓</button>
                    </form>
                </td>
                <td><?= (int) $p['sales'] ?></td>
                <td>
                    <form method="post">
                        <?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button class="badge-btn <?= $p['active'] ? 'on' : 'off' ?>" type="submit"><?= $p['active'] ? 'En ligne' : 'Masqué' ?></button>
                    </form>
                </td>
                <td class="actions">
                    <a href="produit-edit.php?id=<?= (int) $p['id'] ?>" class="btn btn-outline btn-xs">Modifier</a>
                    <form method="post" data-confirm="Supprimer ce produit ?">
                        <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button class="btn btn-danger btn-xs" type="submit">Suppr.</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$products): ?><tr><td colspan="8" class="muted center">Aucun produit.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php admin_footer(); ?>
