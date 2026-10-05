<?php
/**
 * Socle de l'administration : chargement, contrôle de connexion et mise en page.
 */
require_once __DIR__ . '/../includes/bootstrap.php';

function admin_user(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    return db_one('SELECT id, username FROM admins WHERE id = ?', [$_SESSION['admin_id']]);
}

function require_admin(): array
{
    $user = admin_user();
    if (!$user) {
        redirect('login.php');
    }
    return $user;
}

function admin_header(string $title, string $active = ''): void
{
    $user = admin_user();
    $counts = [
        'commandes' => (int) db_value("SELECT COUNT(*) FROM orders WHERE status = 'nouvelle'"),
        'demandes'  => (int) db_value("SELECT COUNT(*) FROM service_requests WHERE status = 'nouvelle'"),
        'messages'  => (int) db_value('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
    ];
    $menu = [
        'index'      => ['📊', 'Tableau de bord'],
        'commandes'  => ['🧾', 'Commandes'],
        'produits'   => ['💻', 'Produits'],
        'categories' => ['🗂️', 'Catégories'],
        'demandes'   => ['🛠️', 'Demandes de service'],
        'services'   => ['🧰', 'Services'],
        'messages'   => ['✉️', 'Messages'],
        'abonnes'    => ['📣', 'Newsletter'],
        'compte'     => ['🔑', 'Mon compte'],
    ];
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> — Admin <?= e(SITE_NAME) ?></title>
    <link rel="icon" href="../assets/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin">
<aside class="admin-sidebar">
    <a href="index.php" class="logo logo-admin"><img src="../assets/img/logo-emblem.png" alt="" width="44" height="38"><?= e(SITE_NAME) ?></a>
    <nav>
        <?php foreach ($menu as $key => [$icon, $label]): ?>
            <a href="<?= e($key) ?>.php" class="<?= $active === $key ? 'active' : '' ?>">
                <span><?= $icon ?></span><?= e($label) ?>
                <?php if (!empty($counts[$key])): ?><em><?= $counts[$key] ?></em><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar-foot">
        <a href="../index.php" target="_blank">🌐 Voir le site</a>
        <a href="logout.php">⏻ Déconnexion</a>
    </div>
</aside>
<div class="admin-main">
    <header class="admin-topbar">
        <button class="admin-menu-toggle" type="button" aria-label="Menu">☰</button>
        <h1><?= e($title) ?></h1>
        <span class="muted">Connecté : <strong><?= e($user['username'] ?? '') ?></strong></span>
    </header>
    <div class="admin-content">
        <?= render_flash() ?>
    <?php
}

function admin_footer(): void
{
    ?>
    </div>
</div>
<script>
document.querySelector('.admin-menu-toggle').addEventListener('click', function () {
    document.body.classList.toggle('sidebar-open');
});
document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('submit', function (ev) { if (!confirm(el.dataset.confirm)) { ev.preventDefault(); } });
});
</script>
</body>
</html>
    <?php
}

/** Enregistre une image envoyée et renvoie son nom de fichier, ou null. */
function handle_image_upload(string $field, array &$errors): ?string
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Échec de l\'envoi de l\'image (code ' . (int) $file['error'] . ').';
        return null;
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        $errors[] = 'Image trop lourde (3 Mo maximum).';
        return null;
    }
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $info = @getimagesize($file['tmp_name']);
    $mime = $info['mime'] ?? '';
    if (!isset($types[$mime])) {
        $errors[] = 'Format d\'image non accepté (JPG, PNG, WEBP ou GIF).';
        return null;
    }
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }
    $name = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . '/' . $name)) {
        $errors[] = 'Impossible d\'enregistrer l\'image sur le serveur (droits du dossier uploads/).';
        return null;
    }
    return $name;
}

function delete_upload(?string $name): void
{
    if ($name && !str_starts_with($name, 'assets/') && is_file(UPLOAD_DIR . '/' . basename($name))) {
        @unlink(UPLOAD_DIR . '/' . basename($name));
    }
}
