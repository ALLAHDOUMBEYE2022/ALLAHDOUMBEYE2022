<?php
/**
 * MaxiTech — Fichier de configuration principal.
 * Modifiez ici les informations de votre entreprise.
 */

// ---------------------------------------------------------------------------
// Base de données
// ---------------------------------------------------------------------------
// Les identifiants saisis dans install.php sont enregistrés dans data/config.local.php
// (fichier protégé, non versionné) et prennent le pas sur les valeurs ci-dessous.
if (is_file(__DIR__ . '/data/config.local.php')) {
    require __DIR__ . '/data/config.local.php';
}
// 'mysql' en production (hébergeur, XAMPP, WAMP…) ; 'sqlite' pour un test rapide sans MySQL.
defined('DB_DRIVER') || define('DB_DRIVER', getenv('MAXITECH_DB_DRIVER') ?: 'mysql');
defined('DB_HOST')   || define('DB_HOST', getenv('MAXITECH_DB_HOST') ?: 'localhost');
defined('DB_NAME')   || define('DB_NAME', getenv('MAXITECH_DB_NAME') ?: 'maxitech');
defined('DB_USER')   || define('DB_USER', getenv('MAXITECH_DB_USER') ?: 'root');
defined('DB_PASS')   || define('DB_PASS', getenv('MAXITECH_DB_PASS') ?: '');
define('DB_SQLITE_PATH', __DIR__ . '/data/maxitech.sqlite');

// ---------------------------------------------------------------------------
// Informations de l'entreprise
// ---------------------------------------------------------------------------
define('SITE_NAME', 'MaxiTech');
define('SITE_TAGLINE', 'Ordinateurs, accessoires & services informatiques à N\'Djamena');
define('SITE_PHONE', '+235 66 07 51 46');
define('SITE_PHONE_2', '+235 93 37 76 61');        // Laisser vide s'il n'y a qu'un numéro
define('SITE_WHATSAPP', '23566075146');            // Format international sans "+" ni espaces
define('SITE_EMAIL', '');                          // Ex. : 'contact@maxitech.td' (masqué si vide)
define('SITE_ADDRESS', 'Chagoua, axe CA7, au sein du Centre FIDETECHL FORMATION — N\'Djamena, Tchad');
define('SITE_MAP_QUERY', 'Chagoua, N\'Djamena, Tchad');
define('SITE_HOURS', 'Lun – Sam : 8h00 – 19h00');
define('SITE_FACEBOOK', 'https://www.facebook.com/');
define('SITE_TIKTOK', '');
define('SITE_INSTAGRAM', '');

// ---------------------------------------------------------------------------
// Ventes
// ---------------------------------------------------------------------------
define('CURRENCY', 'FCFA');
define('DELIVERY_FEE', 2000);                 // Livraison à N'Djamena
define('FREE_DELIVERY_FROM', 300000);         // Livraison offerte à partir de ce montant
define('WARRANTY_TEXT', 'Matériel testé avant la vente');

// Comptes Mobile Money affichés au moment de la commande
const MOBILE_MONEY = [
    'Airtel Money' => '+235 66 07 51 46',
    'Moov Money'   => '+235 93 37 76 61',
];

// Codes promo : CODE => [type ('percent' | 'fixed'), valeur, montant minimum du panier]
// Exemple : 'RENTREE10' => ['fixed', 10000, 200000]. Le champ « code promo » est masqué si la liste est vide.
const COUPONS = [];

// Bandeau promotionnel (laisser vide pour le masquer)
define('PROMO_BANNER', '🔥 En promotion : Dell Latitude 3120 tactile & pliable à 150 000 FCFA au lieu de 180 000 FCFA');
define('PROMO_END', '');  // Date de fin pour le compte à rebours, ex. '2026-10-31 23:59:59' (vide = pas de compte à rebours)

// Avis clients affichés en page d'accueil : ['Nom', 'Profil', 'Avis'].
// N'ajoutez que de vrais avis (la section est masquée tant que la liste est vide).
const TESTIMONIALS = [];

// ---------------------------------------------------------------------------
// Technique
// ---------------------------------------------------------------------------
define('ROOT_PATH', __DIR__);
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('MAX_UPLOAD_SIZE', 3 * 1024 * 1024);   // 3 Mo

date_default_timezone_set('Africa/Ndjamena');
