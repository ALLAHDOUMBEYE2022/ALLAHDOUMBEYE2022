<?php
require __DIR__ . '/includes/bootstrap.php';

$activePage = !empty($_GET['promo']) ? 'promos' : 'boutique';
$categories = public_categories();

$catSlug = (string) ($_GET['cat'] ?? '');
$q       = trim((string) ($_GET['q'] ?? ''));
$promo   = !empty($_GET['promo']);
$used    = !empty($_GET['used']);
$inStock = !empty($_GET['stock']);
$maxPrice = (int) ($_GET['max'] ?? 0);
$sort    = (string) ($_GET['sort'] ?? 'pertinence');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;

$where = ['p.active = 1'];
$params = [];
$currentCat = null;

if ($catSlug !== '') {
    foreach ($categories as $c) {
        if ($c['slug'] === $catSlug) {
            $currentCat = $c;
        }
    }
    if ($currentCat) {
        $where[] = 'p.category_id = ?';
        $params[] = $currentCat['id'];
    }
}
if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.brand LIKE ? OR p.short_desc LIKE ? OR p.specs LIKE ?)';
    array_push($params, "%$q%", "%$q%", "%$q%", "%$q%");
}
if ($promo) {
    $where[] = 'p.old_price > p.price';
}
if ($used) {
    $where[] = 'p.is_used = 1';
}
if ($inStock) {
    $where[] = 'p.stock > 0';
}
if ($maxPrice > 0) {
    $where[] = 'p.price <= ?';
    $params[] = $maxPrice;
}

$orders = [
    'pertinence' => 'p.featured DESC, p.sales DESC, p.id DESC',
    'prix_asc'   => 'p.price ASC',
    'prix_desc'  => 'p.price DESC',
    'nouveautes' => 'p.created_at DESC, p.id DESC',
    'ventes'     => 'p.sales DESC',
];
$orderBy = $orders[$sort] ?? $orders['pertinence'];

$whereSql = ' WHERE ' . implode(' AND ', $where);
$total = (int) db_value('SELECT COUNT(*) FROM products p' . $whereSql, $params);
$pages = max(1, (int) ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;
$products = db_all(product_query() . $whereSql . " ORDER BY $orderBy LIMIT $perPage OFFSET $offset", $params);

$title = $currentCat['name'] ?? ($promo ? 'Promotions' : ($q !== '' ? 'Résultats pour « ' . $q . ' »' : 'Boutique'));
$pageTitle = $title;
$pageDescription = $currentCat['description'] ?? 'Achetez ordinateurs, imprimantes et accessoires informatiques au meilleur prix au Tchad.';

function shop_url(array $changes): string
{
    $query = array_merge($_GET, $changes);
    $query = array_filter($query, fn($v) => $v !== '' && $v !== null && $v !== 0 && $v !== '0');
    return 'boutique.php' . ($query ? '?' . http_build_query($query) : '');
}

require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <nav class="breadcrumb"><a href="index.php">Accueil</a> › <a href="boutique.php">Boutique</a><?= $currentCat ? ' › ' . e($currentCat['name']) : '' ?></nav>
        <h1><?= e($title) ?></h1>
        <p><?= $total ?> produit<?= $total > 1 ? 's' : '' ?> trouvé<?= $total > 1 ? 's' : '' ?></p>
    </div>
</section>

<section class="section">
    <div class="container shop-layout">
        <aside class="shop-filters">
            <form method="get" action="boutique.php" id="filters">
                <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
                <h3>Catégories</h3>
                <ul class="filter-cats">
                    <li><a href="<?= e(shop_url(['cat' => '', 'page' => ''])) ?>" class="<?= !$currentCat ? 'active' : '' ?>">Toutes les catégories</a></li>
                    <?php foreach ($categories as $c): ?>
                        <li><a href="<?= e(shop_url(['cat' => $c['slug'], 'page' => ''])) ?>" class="<?= $currentCat && $currentCat['id'] == $c['id'] ? 'active' : '' ?>">
                            <?= e($c['name']) ?> <small>(<?= (int) $c['total'] ?>)</small></a></li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($currentCat): ?><input type="hidden" name="cat" value="<?= e($currentCat['slug']) ?>"><?php endif; ?>

                <h3>Filtres</h3>
                <label class="checkbox"><input type="checkbox" name="promo" value="1" <?= $promo ? 'checked' : '' ?>> En promotion</label>
                <label class="checkbox"><input type="checkbox" name="used" value="1" <?= $used ? 'checked' : '' ?>> Reconditionné</label>
                <label class="checkbox"><input type="checkbox" name="stock" value="1" <?= $inStock ? 'checked' : '' ?>> En stock uniquement</label>

                <h3>Budget maximum</h3>
                <select name="max">
                    <option value="">Tous les prix</option>
                    <?php foreach ([25000, 50000, 150000, 300000, 500000, 1000000] as $b): ?>
                        <option value="<?= $b ?>" <?= $maxPrice === $b ? 'selected' : '' ?>>Jusqu'à <?= e(money($b)) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="sort" value="<?= e($sort) ?>">
                <button class="btn btn-primary btn-block" type="submit">Appliquer</button>
                <a href="boutique.php" class="btn btn-outline btn-block">Réinitialiser</a>
            </form>
        </aside>

        <div class="shop-results">
            <div class="shop-toolbar">
                <button class="btn btn-outline btn-sm filters-toggle" type="button">⚙ Filtres</button>
                <form method="get" action="boutique.php" class="sort-form">
                    <?php foreach ($_GET as $k => $v): if ($k === 'sort' || $k === 'page' || is_array($v)) continue; ?>
                        <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
                    <?php endforeach; ?>
                    <label>Trier par
                        <select name="sort" onchange="this.form.submit()">
                            <?php foreach (['pertinence' => 'Pertinence', 'prix_asc' => 'Prix croissant', 'prix_desc' => 'Prix décroissant', 'nouveautes' => 'Nouveautés', 'ventes' => 'Meilleures ventes'] as $k => $label): ?>
                                <option value="<?= e($k) ?>" <?= $sort === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </form>
            </div>

            <?php if ($products): ?>
                <div class="product-grid">
                    <?php foreach ($products as $p) { include __DIR__ . '/includes/product-card.php'; } ?>
                </div>
                <?php if ($pages > 1): ?>
                    <nav class="pagination">
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <a href="<?= e(shop_url(['page' => $i])) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>😕 Aucun produit ne correspond à votre recherche.</p>
                    <p>Nous pouvons probablement vous le trouver !</p>
                    <a class="btn btn-wa" target="_blank" rel="noopener"
                       href="<?= e(whatsapp_link('Bonjour, je recherche : ' . ($q ?: 'un produit') . '. Pouvez-vous me le trouver ?')) ?>">Demander sur WhatsApp</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
