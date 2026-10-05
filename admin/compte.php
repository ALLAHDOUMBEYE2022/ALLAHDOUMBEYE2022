<?php
require __DIR__ . '/_init.php';
$user = require_admin();

$errors = [];
if (is_post()) {
    csrf_check();
    $action = post('action');
    if ($action === 'password') {
        $hash = db_value('SELECT password_hash FROM admins WHERE id = ?', [$user['id']]);
        if (!password_verify(post('current'), (string) $hash)) {
            $errors[] = 'Mot de passe actuel incorrect.';
        } elseif (strlen(post('new')) < 8) {
            $errors[] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        } elseif (post('new') !== post('confirm')) {
            $errors[] = 'Les deux mots de passe ne correspondent pas.';
        } else {
            db_exec('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash(post('new'), PASSWORD_DEFAULT), $user['id']]);
            flash('Mot de passe modifié.');
            redirect('compte.php');
        }
    } elseif ($action === 'add') {
        $username = post('username');
        if (!preg_match('/^[A-Za-z0-9_.-]{3,60}$/', $username)) {
            $errors[] = 'Identifiant invalide (3 à 60 caractères : lettres, chiffres, . _ -).';
        } elseif (db_value('SELECT COUNT(*) FROM admins WHERE username = ?', [$username])) {
            $errors[] = 'Cet identifiant existe déjà.';
        } elseif (strlen(post('password')) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        } else {
            db_exec('INSERT INTO admins (username, password_hash) VALUES (?, ?)', [$username, password_hash(post('password'), PASSWORD_DEFAULT)]);
            flash('Compte « ' . $username . ' » créé.');
            redirect('compte.php');
        }
    } elseif ($action === 'delete') {
        $id = (int) post('id');
        if ($id === (int) $user['id']) {
            $errors[] = 'Vous ne pouvez pas supprimer votre propre compte.';
        } else {
            db_exec('DELETE FROM admins WHERE id = ?', [$id]);
            flash('Compte supprimé.');
            redirect('compte.php');
        }
    }
}

$admins = db_all('SELECT id, username, created_at FROM admins ORDER BY id');

admin_header('Mon compte', 'compte');
?>
<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
<div class="admin-grid admin-grid-even">
    <form method="post" class="panel form">
        <h2>Changer mon mot de passe</h2>
        <?= csrf_field() ?><input type="hidden" name="action" value="password">
        <label>Mot de passe actuel<input type="password" name="current" required autocomplete="current-password"></label>
        <label>Nouveau mot de passe<input type="password" name="new" required minlength="8" autocomplete="new-password"></label>
        <label>Confirmer<input type="password" name="confirm" required minlength="8" autocomplete="new-password"></label>
        <button class="btn btn-primary" type="submit">Modifier</button>
    </form>

    <div class="panel">
        <h2>Comptes administrateurs</h2>
        <ul class="plain-list">
            <?php foreach ($admins as $a): ?>
                <li><span>👤 <?= e($a['username']) ?> <?= (int) $a['id'] === (int) $user['id'] ? '<small class="muted">(vous)</small>' : '' ?></span>
                    <?php if ((int) $a['id'] !== (int) $user['id']): ?>
                        <form method="post" data-confirm="Supprimer ce compte ?">
                            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                            <button class="btn btn-danger btn-xs" type="submit">Suppr.</button>
                        </form>
                    <?php endif; ?></li>
            <?php endforeach; ?>
        </ul>
        <form method="post" class="form">
            <h3>Ajouter un employé</h3>
            <?= csrf_field() ?><input type="hidden" name="action" value="add">
            <label>Identifiant<input type="text" name="username" required></label>
            <label>Mot de passe (8 caractères min.)<input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
            <button class="btn btn-accent" type="submit">Créer le compte</button>
        </form>
    </div>
</div>
<?php admin_footer(); ?>
