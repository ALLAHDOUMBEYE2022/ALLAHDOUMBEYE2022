<?php
/**
 * Installateur MaxiTech : crée les tables, le compte administrateur et les données de démonstration.
 * Supprimez ce fichier (ou laissez le verrou data/install.lock) une fois l'installation terminée.
 */
define('SKIP_INSTALL_CHECK', true);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/schema.php';

$errors = [];
$done = false;

if (is_installed()) {
    $done = true;
} elseif (is_post()) {
    csrf_check();
    $username = post('username');
    $password = post('password');

    if (!preg_match('/^[A-Za-z0-9_.-]{3,60}$/', $username)) {
        $errors[] = 'Identifiant invalide (3 à 60 caractères : lettres, chiffres, . _ -).';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    if ($password !== post('password_confirm')) {
        $errors[] = 'Les deux mots de passe ne correspondent pas.';
    }

    if (!$errors) {
        $pdo = db();
        foreach (schema_statements(DB_DRIVER) as $sql) {
            $pdo->exec($sql);
        }
        $pdo->beginTransaction();
        db_exec('INSERT INTO admins (username, password_hash) VALUES (?, ?)', [$username, password_hash($password, PASSWORD_DEFAULT)]);
        if (!empty($_POST['demo']) && (int) db_value('SELECT COUNT(*) FROM categories') === 0) {
            seed_demo_data($pdo);
        }
        $pdo->commit();

        if (!is_dir(ROOT_PATH . '/data')) {
            mkdir(ROOT_PATH . '/data', 0775, true);
        }
        file_put_contents(ROOT_PATH . '/data/install.lock', date('c'));
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Installation — <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <div class="logo logo-dark"><span class="logo-mark">M</span><?= e(SITE_NAME) ?></div>
    <?php if ($done): ?>
        <h1>Installation terminée ✅</h1>
        <p>Votre site est prêt. Pour la sécurité, supprimez le fichier <code>install.php</code> de votre serveur.</p>
        <p><a class="btn btn-primary btn-block" href="index.php">Voir le site</a></p>
        <p><a class="btn btn-outline btn-block" href="admin/login.php">Accéder à l'administration</a></p>
    <?php else: ?>
        <h1>Installation du site</h1>
        <p class="muted">Base de données : <strong><?= e(DB_DRIVER === 'sqlite' ? 'SQLite' : 'MySQL « ' . DB_NAME . ' »') ?></strong>
            (modifiable dans <code>config.php</code>).</p>
        <?php foreach ($errors as $err): ?>
            <div class="alert alert-error"><?= e($err) ?></div>
        <?php endforeach; ?>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <label>Identifiant administrateur
                <input type="text" name="username" required value="<?= e(post('username', 'admin')) ?>">
            </label>
            <label>Mot de passe (8 caractères minimum)
                <input type="password" name="password" required minlength="8">
            </label>
            <label>Confirmer le mot de passe
                <input type="password" name="password_confirm" required minlength="8">
            </label>
            <label class="checkbox">
                <input type="checkbox" name="demo" value="1" checked>
                Ajouter les produits et services de démonstration
            </label>
            <button class="btn btn-primary btn-block" type="submit">Installer</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
