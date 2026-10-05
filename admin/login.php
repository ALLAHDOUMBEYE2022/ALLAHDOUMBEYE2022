<?php
require __DIR__ . '/_init.php';

if (admin_user()) {
    redirect('index.php');
}

$error = '';
if (is_post()) {
    csrf_check();
    // Ralentit les tentatives répétées.
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] > 5) {
        sleep(min(10, $_SESSION['login_attempts'] - 5));
    }
    $admin = db_one('SELECT * FROM admins WHERE username = ?', [post('username')]);
    if ($admin && password_verify(post('password'), $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        unset($_SESSION['login_attempts']);
        redirect('index.php');
    }
    $error = 'Identifiant ou mot de passe incorrect.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Connexion — Admin <?= e(SITE_NAME) ?></title>
    <link rel="icon" href="../assets/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <img src="../assets/img/logo-complet.png" alt="<?= e(SITE_NAME) ?>" class="auth-logo">
    <h1>Espace administration</h1>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form">
        <?= csrf_field() ?>
        <label>Identifiant<input type="text" name="username" required autofocus autocomplete="username" value="<?= e(post('username')) ?>"></label>
        <label>Mot de passe<input type="password" name="password" required autocomplete="current-password"></label>
        <button class="btn btn-primary btn-block btn-lg" type="submit">Se connecter</button>
    </form>
    <p class="center small"><a href="../index.php">← Retour au site</a></p>
</div>
</body>
</html>
