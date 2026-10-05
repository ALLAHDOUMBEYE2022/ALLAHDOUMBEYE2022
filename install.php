<?php
/**
 * Installateur MaxiTech, en deux étapes :
 *   1. connexion à la base MySQL (identifiants fournis par l'hébergeur) ;
 *   2. création des tables, du compte administrateur et du catalogue.
 * Supprimez ce fichier une fois l'installation terminée (le verrou data/install.lock le neutralise de toute façon).
 */
define('SKIP_INSTALL_CHECK', true);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/schema.php';

$errors = [];
$done = is_installed();
$step = 2;
$dbError = '';

if (!$done) {
    try {
        db_connect(DB_DRIVER, DB_HOST, DB_NAME, DB_USER, DB_PASS);
    } catch (PDOException $e) {
        $step = 1;
        $dbError = $e->getMessage();
    }
}

if (!$done && is_post()) {
    csrf_check();

    if (post('step') === '1') {
        $cfg = ['host' => post('db_host'), 'name' => post('db_name'), 'user' => post('db_user'), 'pass' => (string) ($_POST['db_pass'] ?? '')];
        try {
            db_connect('mysql', $cfg['host'], $cfg['name'], $cfg['user'], $cfg['pass']);
            if (!is_dir(ROOT_PATH . '/data')) {
                mkdir(ROOT_PATH . '/data', 0775, true);
            }
            $php = "<?php\n// Généré par install.php le " . date('d/m/Y H:i') . "\n"
                . "define('DB_DRIVER', 'mysql');\n"
                . 'define(\'DB_HOST\', ' . var_export($cfg['host'], true) . ");\n"
                . 'define(\'DB_NAME\', ' . var_export($cfg['name'], true) . ");\n"
                . 'define(\'DB_USER\', ' . var_export($cfg['user'], true) . ");\n"
                . 'define(\'DB_PASS\', ' . var_export($cfg['pass'], true) . ");\n";
            if (file_put_contents(ROOT_PATH . '/data/config.local.php', $php) === false) {
                $errors[] = 'Impossible d\'écrire data/config.local.php : vérifiez que le dossier data/ est accessible en écriture.';
            } else {
                redirect('install.php');
            }
        } catch (PDOException $e) {
            $errors[] = 'Connexion refusée : ' . $e->getMessage();
        }
    } elseif ($step === 2) {
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
            if (!db_value('SELECT COUNT(*) FROM admins WHERE username = ?', [$username])) {
                db_exec('INSERT INTO admins (username, password_hash) VALUES (?, ?)', [$username, password_hash($password, PASSWORD_DEFAULT)]);
            }
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
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Installation — <?= e(SITE_NAME) ?></title>
    <link rel="icon" href="assets/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <img src="assets/img/logo-complet.png" alt="<?= e(SITE_NAME) ?>" class="auth-logo">
    <?php if ($done): ?>
        <h1>Installation terminée ✅</h1>
        <p>Votre site est prêt. Pour la sécurité, <strong>supprimez le fichier <code>install.php</code></strong> de votre serveur.</p>
        <p><a class="btn btn-primary btn-block" href="index.php">Voir le site</a></p>
        <p><a class="btn btn-outline btn-block" href="admin/login.php">Accéder à l'administration</a></p>
    <?php elseif ($step === 1): ?>
        <h1>Étape 1/2 — Base de données</h1>
        <p class="muted small">Indiquez les informations MySQL fournies par votre hébergeur
            (sur InfinityFree : <em>Control Panel › MySQL Databases</em>).</p>
        <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="step" value="1">
            <label>Serveur MySQL (hostname)<input type="text" name="db_host" required value="<?= e(post('db_host', DB_HOST)) ?>" placeholder="ex. sql123.infinityfree.com"></label>
            <label>Nom de la base<input type="text" name="db_name" required value="<?= e(post('db_name', DB_NAME)) ?>" placeholder="ex. if0_12345678_maxitech"></label>
            <label>Utilisateur<input type="text" name="db_user" required value="<?= e(post('db_user', DB_USER)) ?>" placeholder="ex. if0_12345678"></label>
            <label>Mot de passe<input type="password" name="db_pass" autocomplete="off"></label>
            <button class="btn btn-primary btn-block" type="submit">Tester et enregistrer</button>
        </form>
    <?php else: ?>
        <h1>Étape 2/2 — Compte administrateur</h1>
        <p class="muted small">✔ Connexion à la base <strong><?= e(DB_DRIVER === 'sqlite' ? 'SQLite' : DB_NAME) ?></strong> réussie.</p>
        <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="step" value="2">
            <label>Identifiant administrateur<input type="text" name="username" required value="<?= e(post('username', 'admin')) ?>"></label>
            <label>Mot de passe (8 caractères minimum)<input type="password" name="password" required minlength="8"></label>
            <label>Confirmer le mot de passe<input type="password" name="password_confirm" required minlength="8"></label>
            <label class="checkbox"><input type="checkbox" name="demo" value="1" checked> Importer le catalogue MaxiTech (ordinateurs et services)</label>
            <button class="btn btn-primary btn-block" type="submit">Installer</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
