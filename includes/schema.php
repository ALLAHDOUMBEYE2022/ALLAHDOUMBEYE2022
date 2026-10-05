<?php
/**
 * Structure de la base de données et données de démonstration.
 * Utilisé uniquement par install.php.
 */

function schema_statements(string $driver): array
{
    $pk   = $driver === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
    $fk   = $driver === 'sqlite' ? 'INTEGER' : 'INT UNSIGNED';
    $tail = $driver === 'sqlite' ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    return [
        "CREATE TABLE IF NOT EXISTS admins (
            id $pk,
            username VARCHAR(60) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )$tail",

        "CREATE TABLE IF NOT EXISTS categories (
            id $pk,
            name VARCHAR(120) NOT NULL,
            slug VARCHAR(140) NOT NULL UNIQUE,
            icon VARCHAR(40) NOT NULL DEFAULT 'accessoire',
            description VARCHAR(255) DEFAULT '',
            sort_order INT NOT NULL DEFAULT 0
        )$tail",

        "CREATE TABLE IF NOT EXISTS products (
            id $pk,
            category_id $fk NOT NULL,
            name VARCHAR(190) NOT NULL,
            slug VARCHAR(210) NOT NULL UNIQUE,
            brand VARCHAR(80) DEFAULT '',
            short_desc VARCHAR(255) DEFAULT '',
            description TEXT,
            specs TEXT,
            price INT NOT NULL DEFAULT 0,
            old_price INT DEFAULT NULL,
            stock INT NOT NULL DEFAULT 0,
            image VARCHAR(190) DEFAULT NULL,
            featured TINYINT NOT NULL DEFAULT 0,
            is_used TINYINT NOT NULL DEFAULT 0,
            active TINYINT NOT NULL DEFAULT 1,
            sales INT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id)
        )$tail",

        "CREATE TABLE IF NOT EXISTS services (
            id $pk,
            name VARCHAR(150) NOT NULL,
            slug VARCHAR(170) NOT NULL UNIQUE,
            icon VARCHAR(10) NOT NULL DEFAULT '🛠️',
            short_desc VARCHAR(255) DEFAULT '',
            description TEXT,
            price_from INT NOT NULL DEFAULT 0,
            duration VARCHAR(80) DEFAULT '',
            sort_order INT NOT NULL DEFAULT 0,
            active TINYINT NOT NULL DEFAULT 1
        )$tail",

        "CREATE TABLE IF NOT EXISTS orders (
            id $pk,
            reference VARCHAR(30) NOT NULL UNIQUE,
            customer_name VARCHAR(120) NOT NULL,
            phone VARCHAR(30) NOT NULL,
            email VARCHAR(150) DEFAULT '',
            city VARCHAR(80) DEFAULT '',
            address VARCHAR(255) DEFAULT '',
            payment_method VARCHAR(30) NOT NULL,
            payment_ref VARCHAR(80) DEFAULT '',
            notes TEXT,
            subtotal INT NOT NULL DEFAULT 0,
            discount INT NOT NULL DEFAULT 0,
            coupon VARCHAR(40) DEFAULT NULL,
            delivery_fee INT NOT NULL DEFAULT 0,
            total INT NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'nouvelle',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )$tail",

        "CREATE TABLE IF NOT EXISTS order_items (
            id $pk,
            order_id $fk NOT NULL,
            product_id $fk,
            product_name VARCHAR(190) NOT NULL,
            unit_price INT NOT NULL,
            quantity INT NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )$tail",

        "CREATE TABLE IF NOT EXISTS service_requests (
            id $pk,
            reference VARCHAR(30) NOT NULL UNIQUE,
            service_id $fk,
            name VARCHAR(120) NOT NULL,
            phone VARCHAR(30) NOT NULL,
            email VARCHAR(150) DEFAULT '',
            device VARCHAR(150) DEFAULT '',
            location VARCHAR(20) NOT NULL DEFAULT 'atelier',
            preferred_date DATE DEFAULT NULL,
            description TEXT,
            status VARCHAR(20) NOT NULL DEFAULT 'nouvelle',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )$tail",

        "CREATE TABLE IF NOT EXISTS messages (
            id $pk,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(150) DEFAULT '',
            phone VARCHAR(30) DEFAULT '',
            subject VARCHAR(190) DEFAULT '',
            message TEXT NOT NULL,
            is_read TINYINT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )$tail",

        "CREATE TABLE IF NOT EXISTS newsletter (
            id $pk,
            email VARCHAR(150) NOT NULL UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )$tail",
    ];
}

function seed_demo_data(PDO $pdo): void
{
    $categories = [
        ['Ordinateurs portables', 'portable', 'Laptops neufs et reconditionnés pour études, bureau et gaming'],
        ['Ordinateurs de bureau', 'bureau', 'Unités centrales, tout-en-un et postes complets'],
        ['Imprimantes & scanners', 'imprimante', 'Laser, jet d\'encre, réservoirs d\'encre et consommables'],
        ['Accessoires', 'accessoire', 'Souris, claviers, casques, sacoches, onduleurs…'],
        ['Stockage & composants', 'stockage', 'SSD, disques durs, clés USB, mémoire RAM'],
        ['Réseau & sécurité', 'reseau', 'Routeurs, switches, Wi-Fi et vidéosurveillance'],
    ];
    $catIds = [];
    $st = $pdo->prepare('INSERT INTO categories (name, slug, icon, description, sort_order) VALUES (?, ?, ?, ?, ?)');
    foreach ($categories as $i => [$name, $icon, $desc]) {
        $st->execute([$name, slugify($name), $icon, $desc, $i]);
        $catIds[$icon] = (int) $pdo->lastInsertId();
    }

    // [catégorie, nom, marque, résumé, caractéristiques, prix, ancien prix, stock, vedette, reconditionné]
    $products = [
        ['portable', 'HP 15s Intel Core i5 12e génération', 'HP', 'Le portable polyvalent idéal pour le bureau et les études.',
            "Processeur : Intel Core i5-1235U\nMémoire : 8 Go DDR4\nStockage : SSD 512 Go\nÉcran : 15,6\" Full HD\nSystème : Windows 11", 385000, 420000, 8, 1, 0],
        ['portable', 'Lenovo IdeaPad 3 Core i3', 'Lenovo', 'Léger et économique, parfait pour les étudiants.',
            "Processeur : Intel Core i3-1115G4\nMémoire : 8 Go\nStockage : SSD 256 Go\nÉcran : 15,6\" HD\nSystème : Windows 11", 275000, null, 12, 1, 0],
        ['portable', 'Dell Latitude 5420 reconditionné', 'Dell', 'Robuste ordinateur professionnel, testé et garanti 6 mois.',
            "Processeur : Intel Core i5-1145G7\nMémoire : 16 Go\nStockage : SSD 256 Go\nÉcran : 14\" Full HD\nGarantie : 6 mois", 245000, 290000, 5, 1, 1],
        ['portable', 'ASUS Vivobook 15 Ryzen 5', 'ASUS', 'Performances AMD et grand écran pour le multitâche.',
            "Processeur : AMD Ryzen 5 7520U\nMémoire : 16 Go\nStockage : SSD 512 Go\nÉcran : 15,6\" Full HD\nSystème : Windows 11", 365000, null, 6, 0, 0],
        ['portable', 'Apple MacBook Air M2', 'Apple', 'Ultra-fin, silencieux, jusqu\'à 18 h d\'autonomie.',
            "Puce : Apple M2\nMémoire : 8 Go\nStockage : SSD 256 Go\nÉcran : 13,6\" Liquid Retina\nAutonomie : 18 h", 850000, 920000, 3, 1, 0],
        ['portable', 'Acer Nitro 5 Gaming RTX 3050', 'Acer', 'Le PC gamer pour jouer et faire du montage vidéo.',
            "Processeur : Intel Core i5-12500H\nCarte graphique : NVIDIA RTX 3050\nMémoire : 16 Go\nStockage : SSD 512 Go\nÉcran : 15,6\" 144 Hz", 695000, 750000, 2, 0, 0],
        ['bureau', 'Pack HP ProDesk 400 G7 + écran 22"', 'HP', 'Poste de travail complet prêt à l\'emploi pour votre entreprise.',
            "Processeur : Intel Core i5-10500\nMémoire : 8 Go\nStockage : SSD 256 Go\nÉcran : HP 22\" Full HD\nInclus : clavier + souris", 425000, null, 4, 1, 0],
        ['bureau', 'Dell OptiPlex 3080 reconditionné', 'Dell', 'Unité centrale compacte et fiable, garantie 6 mois.',
            "Processeur : Intel Core i5-10500T\nMémoire : 8 Go\nStockage : SSD 256 Go\nFormat : Micro\nGarantie : 6 mois", 230000, 260000, 7, 0, 1],
        ['bureau', 'Lenovo IdeaCentre AIO 24"', 'Lenovo', 'Tout-en-un élégant, sans câbles encombrants.',
            "Processeur : Intel Core i5-1235U\nMémoire : 8 Go\nStockage : SSD 512 Go\nÉcran : 23,8\" Full HD\nWebcam intégrée", 480000, null, 3, 0, 0],
        ['imprimante', 'Epson EcoTank L3250 Wi-Fi', 'Epson', 'Imprimante multifonction à réservoirs : impression à très bas coût.',
            "Fonctions : impression, copie, scan\nConnexion : Wi-Fi, USB\nCouleur : oui\nRendement : 4 500 pages noir", 135000, 150000, 9, 1, 0],
        ['imprimante', 'HP LaserJet M111w', 'HP', 'Laser monochrome compacte et rapide pour le bureau.',
            "Type : laser noir et blanc\nVitesse : 20 pages/min\nConnexion : Wi-Fi, USB", 115000, null, 5, 0, 0],
        ['imprimante', 'Canon PIXMA G3410', 'Canon', 'Multifonction couleur à réservoirs rechargeables.',
            "Fonctions : impression, copie, scan\nConnexion : Wi-Fi\nRendement : 6 000 pages noir", 120000, null, 4, 0, 0],
        ['accessoire', 'Combo clavier + souris Logitech MK270', 'Logitech', 'Sans fil, fiable, autonomie de plusieurs mois.',
            "Connexion : récepteur USB 2,4 GHz\nDisposition : AZERTY\nAutonomie : 24 mois (clavier)", 18000, 22000, 25, 1, 0],
        ['accessoire', 'Souris sans fil Logitech M185', 'Logitech', 'Compacte et précise, idéale pour portable.',
            "Connexion : USB 2,4 GHz\nAutonomie : 12 mois", 9000, null, 40, 0, 0],
        ['accessoire', 'Casque USB Logitech H390', 'Logitech', 'Pour vos réunions Zoom, Teams et appels WhatsApp.',
            "Connexion : USB\nMicro antibruit\nCommandes intégrées", 25000, null, 15, 0, 0],
        ['accessoire', 'Onduleur APC Back-UPS 650VA', 'APC', 'Protégez votre matériel des coupures et surtensions.',
            "Puissance : 650 VA / 360 W\nPrises : 4\nAutonomie : 5–10 min pour un PC", 55000, 62000, 10, 1, 0],
        ['accessoire', 'Sac à dos pour ordinateur 15,6"', 'MaxiTech', 'Rembourré, imperméable, avec port USB.',
            "Compatibilité : jusqu'à 15,6\"\nMatière : imperméable\nPort USB de charge", 15000, null, 30, 0, 0],
        ['stockage', 'SSD Samsung 870 EVO 500 Go', 'Samsung', 'Rendez votre ancien PC jusqu\'à 5x plus rapide.',
            "Format : 2,5\" SATA\nLecture : 560 Mo/s\nÉcriture : 530 Mo/s", 45000, 50000, 14, 1, 0],
        ['stockage', 'Disque dur externe Seagate 1 To', 'Seagate', 'Sauvegardez vos documents, photos et vidéos.',
            "Capacité : 1 To\nConnexion : USB 3.0\nFormat : 2,5\" portable", 40000, null, 11, 0, 0],
        ['stockage', 'Clé USB SanDisk Ultra 64 Go', 'SanDisk', 'Rapide et fiable pour tous vos transferts.',
            "Capacité : 64 Go\nConnexion : USB 3.0\nVitesse : jusqu'à 130 Mo/s", 7000, null, 60, 0, 0],
        ['stockage', 'Barrette RAM DDR4 8 Go', 'Kingston', 'Plus de mémoire pour un PC plus fluide.',
            "Type : DDR4 3200 MHz\nFormat : SO-DIMM (portable)\nInstallation offerte en boutique", 22000, null, 20, 0, 0],
        ['reseau', 'Routeur Wi-Fi TP-Link Archer C6', 'TP-Link', 'Wi-Fi double bande rapide pour maison et bureau.',
            "Norme : AC1200\nBandes : 2,4 GHz + 5 GHz\nPorts : 4 LAN Gigabit", 30000, 35000, 10, 1, 0],
        ['reseau', 'Switch TP-Link 8 ports Gigabit', 'TP-Link', 'Connectez tous les postes de votre bureau.',
            "Ports : 8 x Gigabit\nPlug & play\nBoîtier métal", 15000, null, 12, 0, 0],
        ['reseau', 'Kit vidéosurveillance 4 caméras', 'Hikvision', 'Surveillez votre commerce depuis votre téléphone.',
            "Caméras : 4 x 2 MP\nEnregistreur : DVR 4 voies + 1 To\nVision nocturne\nInstallation sur devis", 260000, 295000, 3, 1, 0],
    ];
    $st = $pdo->prepare('INSERT INTO products (category_id, name, slug, brand, short_desc, description, specs, price, old_price, stock, featured, is_used, sales)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($products as $i => [$cat, $name, $brand, $short, $specs, $price, $old, $stock, $featured, $used]) {
        $description = $short . ' Disponible chez ' . SITE_NAME . ' avec conseils personnalisés, '
            . 'installation des logiciels essentiels offerte et service après-vente à N\'Djamena.';
        $st->execute([$catIds[$cat], $name, slugify($name), $brand, $short, $description, $specs,
            $price, $old, $stock, $featured, $used, ($i * 7) % 23]);
    }

    // [icône, nom, résumé, description, à partir de (0 = sur devis), durée]
    $services = [
        ['🛠️', 'Réparation & dépannage', 'Ordinateur lent, qui ne démarre plus, écran cassé, surchauffe…',
            "Diagnostic complet de votre ordinateur portable ou de bureau.\nRemplacement d'écran, clavier, batterie, charnières, connecteur de charge.\nSuppression des virus et lenteurs.\nDevis gratuit avant toute intervention.", 5000, '24 à 72 h'],
        ['🧹', 'Maintenance & nettoyage', 'Nettoyage interne, pâte thermique, optimisation du système.',
            "Dépoussiérage complet (essentiel avec la poussière du Sahel).\nRemplacement de la pâte thermique.\nOptimisation du démarrage et mises à jour.", 10000, '2 à 4 h'],
        ['💿', 'Installation Windows & logiciels', 'Windows, Microsoft Office, antivirus, logiciels métiers.',
            "Installation et activation de Windows 10/11.\nSuite bureautique, antivirus, navigateurs, pilotes.\nTransfert de vos fichiers de l'ancien PC vers le nouveau.", 10000, '2 à 3 h'],
        ['💾', 'Récupération de données', 'Disque endommagé, fichiers supprimés, clé USB illisible.',
            "Récupération de documents, photos et vidéos.\nDisques durs, SSD, clés USB et cartes mémoire.\nConfidentialité garantie.", 25000, '1 à 5 jours'],
        ['📶', 'Installation réseau & Wi-Fi', 'Câblage, Wi-Fi, partage d\'imprimante et de fichiers.',
            "Étude de votre local et installation du câblage.\nConfiguration routeurs, points d'accès et switches.\nPartage d'imprimantes et de dossiers.", 30000, '1 jour'],
        ['📹', 'Vidéosurveillance', 'Caméras pour maison, boutique, entrepôt, visibles sur téléphone.',
            "Étude de besoin et conseil d'emplacement.\nInstallation de caméras HD avec vision nocturne.\nConsultation à distance sur smartphone.", 0, 'Sur devis'],
        ['🌐', 'Création de sites web', 'Site vitrine, boutique en ligne, page Facebook professionnelle.',
            "Conception de sites modernes adaptés au mobile.\nNom de domaine, hébergement et adresses e-mail professionnelles.\nCréation et animation de pages Facebook.", 150000, '1 à 3 semaines'],
        ['🎓', 'Formation informatique', 'Bureautique, Internet, Excel, initiation pour adultes et entreprises.',
            "Initiation à l'ordinateur et à Internet.\nWord, Excel, PowerPoint du débutant à l'avancé.\nFormations en groupe pour les entreprises.", 25000, 'Par mois'],
        ['🏢', 'Contrat de maintenance entreprise', 'Un technicien dédié pour tout votre parc informatique.',
            "Visites préventives mensuelles.\nIntervention prioritaire en cas de panne.\nGestion des sauvegardes et de la sécurité.", 0, 'Mensuel'],
    ];
    $st = $pdo->prepare('INSERT INTO services (icon, name, slug, short_desc, description, price_from, duration, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($services as $i => [$icon, $name, $short, $desc, $price, $duration]) {
        $st->execute([$icon, $name, slugify($name), $short, $desc, $price, $duration, $i]);
    }
}
