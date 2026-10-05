<?php
require __DIR__ . '/includes/bootstrap.php';

if (is_post()) {
    csrf_check();
    $email = strtolower(post('email'));
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (!db_value('SELECT COUNT(*) FROM newsletter WHERE email = ?', [$email])) {
            db_exec('INSERT INTO newsletter (email) VALUES (?)', [$email]);
        }
        flash('Merci ! Vous recevrez nos meilleures offres en avant-première.');
    } else {
        flash('Adresse e-mail invalide.', 'error');
    }
}

$back = $_SERVER['HTTP_REFERER'] ?? 'index.php';
$host = parse_url($back, PHP_URL_HOST);
redirect(!$host || $host === ($_SERVER['HTTP_HOST'] ?? '') ? $back : 'index.php');
