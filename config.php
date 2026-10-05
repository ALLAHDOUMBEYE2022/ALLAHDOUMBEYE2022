<?php
/**
 * MaxiTech — Fichier de configuration principal.
 * Modifiez ici les informations de votre entreprise et de votre base de données.
 */

// ---------------------------------------------------------------------------
// Base de données
// ---------------------------------------------------------------------------
// 'mysql' en production (hébergeur, XAMPP, WAMP…) ; 'sqlite' pour un test rapide sans MySQL.
define('DB_DRIVER', getenv('MAXITECH_DB_DRIVER') ?: 'mysql');
define('DB_HOST', getenv('MAXITECH_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('MAXITECH_DB_NAME') ?: 'maxitech');
define('DB_USER', getenv('MAXITECH_DB_USER') ?: 'root');
define('DB_PASS', getenv('MAXITECH_DB_PASS') ?: '');
define('DB_SQLITE_PATH', __DIR__ . '/data/maxitech.sqlite');

// ---------------------------------------------------------------------------
// Informations de l'entreprise
// ---------------------------------------------------------------------------
define('SITE_NAME', 'MaxiTech');
define('SITE_TAGLINE', 'Ordinateurs, accessoires & services informatiques au Tchad');
define('SITE_PHONE', '+235 66 00 00 00');
define('SITE_WHATSAPP', '23566000000');          // Format international sans "+" ni espaces
define('SITE_EMAIL', 'contact@maxitech.td');
define('SITE_ADDRESS', "Avenue Charles de Gaulle, N'Djamena, Tchad");
define('SITE_HOURS', 'Lun – Sam : 8h00 – 19h00');
define('SITE_FACEBOOK', 'https://www.facebook.com/');
define('SITE_TIKTOK', 'https://www.tiktok.com/');
define('SITE_INSTAGRAM', 'https://www.instagram.com/');

// ---------------------------------------------------------------------------
// Ventes
// ---------------------------------------------------------------------------
define('CURRENCY', 'FCFA');
define('DELIVERY_FEE', 2000);                 // Livraison à N'Djamena
define('FREE_DELIVERY_FROM', 300000);         // Livraison offerte à partir de ce montant
define('WARRANTY_TEXT', 'Garantie jusqu\'à 12 mois');

// Comptes Mobile Money affichés au moment de la commande
const MOBILE_MONEY = [
    'Airtel Money' => '+235 66 00 00 00',
    'Moov Money'   => '+235 99 00 00 00',
];

// Codes promo : CODE => [type ('percent' | 'fixed'), valeur, montant minimum du panier]
const COUPONS = [
    'BIENVENUE5' => ['percent', 5, 50000],
    'MAXI10000'  => ['fixed', 10000, 200000],
];

// Bandeau promotionnel (laisser vide pour le masquer)
define('PROMO_BANNER', '🔥 Rentrée 2026 : jusqu\'à -15 % sur les ordinateurs portables — code BIENVENUE5 pour 5 % de plus !');
define('PROMO_END', '2026-10-31 23:59:59');  // Date de fin affichée dans le compte à rebours

// Avis clients affichés en page d'accueil
const TESTIMONIALS = [
    ['Mahamat A.', 'Étudiant', 'J\'ai acheté mon HP chez MaxiTech, livré le jour même à Moursal. Très bon prix et appareil impeccable.'],
    ['Achta D.', 'Gérante de boutique', 'Ils ont installé le Wi-Fi et les caméras de mon magasin en une journée. Travail propre et sérieux.'],
    ['Dr. Ngarlem K.', 'Clinique privée', 'Contrat de maintenance depuis 1 an : nos ordinateurs ne tombent plus en panne. Je recommande.'],
];

// ---------------------------------------------------------------------------
// Technique
// ---------------------------------------------------------------------------
define('ROOT_PATH', __DIR__);
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('MAX_UPLOAD_SIZE', 3 * 1024 * 1024);   // 3 Mo

date_default_timezone_set('Africa/Ndjamena');
