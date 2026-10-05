<?php
/**
 * Chargé en tête de chaque page : configuration, session, base de données, fonctions.
 */
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_name('maxitech_sid');
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Redirige vers l'installateur tant que le site n'est pas installé.
if (!defined('SKIP_INSTALL_CHECK') && !is_installed()) {
    $prefix = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') ? '../' : '';
    header('Location: ' . $prefix . 'install.php');
    exit;
}
