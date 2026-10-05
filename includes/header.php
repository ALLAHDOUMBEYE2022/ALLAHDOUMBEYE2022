<?php
/** @var string $pageTitle  @var string $pageDescription  @var string $activePage */
$pageTitle = $pageTitle ?? SITE_NAME;
$pageDescription = $pageDescription ?? SITE_TAGLINE;
$activePage = $activePage ?? '';
$navItems = [
    'accueil'  => ['index.php', 'Accueil'],
    'boutique' => ['boutique.php', 'Boutique'],
    'promos'   => ['boutique.php?promo=1', 'Promos'],
    'services' => ['services.php', 'Services'],
    'apropos'  => ['apropos.php', 'À propos'],
    'contact'  => ['contact.php', 'Contact'],
];
$headerCategories = get_categories();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle === SITE_NAME ? SITE_NAME . ' — ' . SITE_TAGLINE : $pageTitle . ' | ' . SITE_NAME) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#0a1f44">
    <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php if (PROMO_BANNER !== ''): ?>
<div class="promo-bar">
    <div class="container">
        <span><?= e(PROMO_BANNER) ?></span>
        <?php if (PROMO_END && strtotime(PROMO_END) > time()): ?>
            <span class="countdown" data-end="<?= e(date('c', strtotime(PROMO_END))) ?>"></span>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="topbar">
    <div class="container">
        <span>📞 <a href="tel:<?= e(str_replace(' ', '', SITE_PHONE)) ?>"><?= e(SITE_PHONE) ?></a></span>
        <span class="hide-sm">📍 <?= e(SITE_ADDRESS) ?></span>
        <span class="hide-sm">🕗 <?= e(SITE_HOURS) ?></span>
    </div>
</div>

<header class="site-header">
    <div class="container header-inner">
        <a href="index.php" class="logo"><span class="logo-mark">M</span><?= e(SITE_NAME) ?></a>

        <form class="search" action="boutique.php" method="get" role="search">
            <input type="search" name="q" placeholder="Rechercher un ordinateur, une imprimante, un accessoire…"
                   value="<?= e($_GET['q'] ?? '') ?>" aria-label="Rechercher">
            <button type="submit" aria-label="Lancer la recherche">🔍</button>
        </form>

        <div class="header-actions">
            <a href="<?= e(whatsapp_link('Bonjour ' . SITE_NAME . ', j\'ai une question.')) ?>" class="header-wa hide-sm" target="_blank" rel="noopener">
                💬 <span>WhatsApp</span>
            </a>
            <a href="panier.php" class="cart-link" aria-label="Panier">
                🛒 <span class="hide-sm">Panier</span>
                <span class="cart-count"><?= cart_count() ?></span>
            </a>
            <button class="menu-toggle" aria-label="Menu" aria-expanded="false">☰</button>
        </div>
    </div>

    <nav class="main-nav">
        <div class="container nav-inner">
            <div class="cat-dropdown">
                <button class="cat-toggle" type="button">☰ Catégories</button>
                <ul class="cat-menu">
                    <?php foreach ($headerCategories as $cat): ?>
                        <li><a href="boutique.php?cat=<?= e($cat['slug']) ?>">
                            <img src="assets/img/<?= e($cat['icon']) ?>.svg" alt="" width="24" height="24">
                            <?= e($cat['name']) ?> <small>(<?= (int) $cat['total'] ?>)</small></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <ul class="nav-links">
                <?php foreach ($navItems as $key => [$href, $label]): ?>
                    <li><a href="<?= e($href) ?>" class="<?= $activePage === $key ? 'active' : '' ?>"><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
            <a href="services.php#demande" class="btn btn-accent btn-sm hide-sm">Demander un dépannage</a>
        </div>
    </nav>
</header>

<main>
<?php $flashHtml = render_flash(); if ($flashHtml): ?>
    <div class="container flash-zone"><?= $flashHtml ?></div>
<?php endif; ?>
