<?php
require __DIR__ . '/_init.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$product = $id ? db_one(product_query() . ' WHERE p.id = ?', [$id]) : null;
if ($id && !$product) {
    flash('Produit introuvable.', 'error');
    redirect('produits.php');
}
$categories = get_categories();
if (!$categories) {
    flash('Créez d\'abord une catégorie.', 'error');
    redirect('categories.php');
}

$errors = [];
$form = $product ?? [
    'category_id' => $categories[0]['id'], 'name' => '', 'brand' => '', 'short_desc' => '', 'description' => '',
    'specs' => '', 'price' => '', 'old_price' => '', 'stock' => 1, 'featured' => 0, 'is_used' => 0, 'active' => 1, 'image' => null,
];

if (is_post()) {
    csrf_check();
    $form = array_merge($form, [
        'category_id' => (int) post('category_id'),
        'name'        => post('name'),
        'brand'       => post('brand'),
        'short_desc'  => post('short_desc'),
        'description' => post('description'),
        'specs'       => post('specs'),
        'price'       => (int) preg_replace('/\D/', '', post('price')),
        'old_price'   => (int) preg_replace('/\D/', '', post('old_price')) ?: null,
        'stock'       => max(0, (int) post('stock')),
        'featured'    => isset($_POST['featured']) ? 1 : 0,
        'is_used'     => isset($_POST['is_used']) ? 1 : 0,
        'active'      => isset($_POST['active']) ? 1 : 0,
    ]);

    if (mb_strlen($form['name']) < 2) {
        $errors[] = 'Le nom du produit est obligatoire.';
    }
    if ($form['price'] <= 0) {
        $errors[] = 'Le prix doit être supérieur à 0.';
    }
    if ($form['old_price'] !== null && $form['old_price'] <= $form['price']) {
        $errors[] = 'L\'ancien prix (barré) doit être supérieur au prix de vente, ou laissé vide.';
    }
    if (!db_value('SELECT COUNT(*) FROM categories WHERE id = ?', [$form['category_id']])) {
        $errors[] = 'Catégorie invalide.';
    }

    $newImage = $errors ? null : handle_image_upload('image', $errors);

    if (!$errors) {
        $image = $form['image'];
        if ($newImage) {
            delete_upload($image);
            $image = $newImage;
        } elseif (isset($_POST['remove_image'])) {
            delete_upload($image);
            $image = null;
        }

        $slug = slugify($form['name']);
        $base = $slug;
        $n = 2;
        while (db_value('SELECT COUNT(*) FROM products WHERE slug = ? AND id <> ?', [$slug, $id])) {
            $slug = $base . '-' . $n++;
        }

        $values = [$form['category_id'], $form['name'], $slug, $form['brand'], $form['short_desc'], $form['description'],
            $form['specs'], $form['price'], $form['old_price'], $form['stock'], $image, $form['featured'], $form['is_used'], $form['active']];
        if ($id) {
            db_exec('UPDATE products SET category_id = ?, name = ?, slug = ?, brand = ?, short_desc = ?, description = ?, specs = ?,
                     price = ?, old_price = ?, stock = ?, image = ?, featured = ?, is_used = ?, active = ? WHERE id = ?', [...$values, $id]);
            flash('Produit mis à jour.');
        } else {
            db_exec('INSERT INTO products (category_id, name, slug, brand, short_desc, description, specs, price, old_price, stock, image, featured, is_used, active)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $values);
            $id = (int) db()->lastInsertId();
            flash('Produit créé.');
        }
        redirect('produit-edit.php?id=' . $id);
    }
}

admin_header($id ? 'Modifier le produit' : 'Nouveau produit', 'produits');
?>
<p><a href="produits.php">← Retour aux produits</a><?php if ($product): ?> · <a href="../produit.php?slug=<?= e(rawurlencode($product['slug'])) ?>" target="_blank">Voir sur le site ↗</a><?php endif; ?></p>
<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="form admin-form">
    <?= csrf_field() ?>
    <div class="panel">
        <label>Nom du produit *<input type="text" name="name" required value="<?= e($form['name']) ?>"></label>
        <div class="form-row">
            <label>Catégorie *
                <select name="category_id">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (int) $form['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Marque<input type="text" name="brand" value="<?= e($form['brand']) ?>"></label>
        </div>
        <label>Accroche courte (affichée sous le titre)<input type="text" name="short_desc" maxlength="255" value="<?= e($form['short_desc']) ?>"></label>
        <label>Description<textarea name="description" rows="5"><?= e($form['description']) ?></textarea></label>
        <label>Caractéristiques techniques <small class="muted">(une par ligne, format « Nom : valeur »)</small>
            <textarea name="specs" rows="6" placeholder="Processeur : Intel Core i5&#10;Mémoire : 8 Go"><?= e($form['specs']) ?></textarea></label>
    </div>

    <div class="panel">
        <div class="form-row form-row-3">
            <label>Prix de vente (<?= e(CURRENCY) ?>) *<input type="number" name="price" min="0" step="500" required value="<?= e($form['price']) ?>"></label>
            <label>Ancien prix barré (promo)<input type="number" name="old_price" min="0" step="500" value="<?= e($form['old_price']) ?>"></label>
            <label>Stock<input type="number" name="stock" min="0" value="<?= e($form['stock']) ?>"></label>
        </div>
        <label class="checkbox"><input type="checkbox" name="featured" <?= $form['featured'] ? 'checked' : '' ?>> ⭐ Mettre en avant sur la page d'accueil</label>
        <label class="checkbox"><input type="checkbox" name="is_used" <?= $form['is_used'] ? 'checked' : '' ?>> Produit reconditionné</label>
        <label class="checkbox"><input type="checkbox" name="active" <?= $form['active'] ? 'checked' : '' ?>> Visible sur le site</label>
    </div>

    <div class="panel">
        <label>Photo du produit <small class="muted">(JPG, PNG ou WEBP — 3 Mo max, fond blanc conseillé)</small>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>
        <?php if ($product): ?>
            <div class="current-image">
                <img src="<?= e(product_image($product, '../')) ?>" alt="">
                <?php if ($product['image']): ?><label class="checkbox"><input type="checkbox" name="remove_image"> Supprimer la photo</label>
                <?php else: ?><small class="muted">Illustration par défaut de la catégorie.</small><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <button class="btn btn-accent btn-lg" type="submit"><?= $id ? 'Enregistrer les modifications' : 'Créer le produit' ?></button>
</form>
<?php admin_footer(); ?>
