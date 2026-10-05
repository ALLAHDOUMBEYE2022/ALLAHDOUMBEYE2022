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

/**
 * Catalogue MaxiTech (repris des affiches de la boutique) et services.
 * Les stocks sont fixés à 5 par défaut : ajustez-les depuis l'administration.
 */
function seed_demo_data(PDO $pdo): void
{
    $categories = [
        'promo'   => ['PC portables en promotion', 'portable', 'Les meilleures affaires du moment, testés et prêts à l\'emploi'],
        'tactile' => ['PC tactiles & 2-en-1', 'tactile', 'Écrans tactiles et pliables : ordinateur et tablette en un'],
        'pro'     => ['PC pour professionnels', 'pro', 'Des machines puissantes et fiables pour le travail au quotidien'],
        'haut'    => ['PC haut de gamme', 'premium', 'Stations de travail et ultraportables d\'exception'],
    ];
    $catIds = [];
    $st = $pdo->prepare('INSERT INTO categories (name, slug, icon, description, sort_order) VALUES (?, ?, ?, ?, ?)');
    $i = 0;
    foreach ($categories as $key => [$name, $icon, $desc]) {
        $st->execute([$name, slugify($name), $icon, $desc, $i++]);
        $catIds[$key] = (int) $pdo->lastInsertId();
    }

    // [catégorie, nom, marque, accroche, caractéristiques, prix, ancien prix, vedette, reconditionné, photo]
    $products = [
        ['promo', 'Dell Latitude 3120 tactile 2-en-1', 'Dell', 'Compact, robuste, écran tactile qui se replie en tablette — idéal pour les élèves et étudiants.',
            "Processeur : Intel Pentium 11e génération\nMémoire : 8 Go\nStockage : SSD 256 Go\nÉcran : 13\" tactile et pliable (2-en-1)\nCouleur : gris", 150000, 180000, 1, 1, 'dell-latitude-3120.jpg'],
        ['promo', 'Dell Latitude 5401 Core i5', 'Dell', 'Le portable professionnel fiable au meilleur prix.',
            "Processeur : Intel Core i5 8e génération\nMémoire : 8 Go\nStockage : SSD 256 Go", 155000, null, 1, 1, null],
        ['promo', 'Lenovo ThinkPad X1 Carbon Core i7', 'Lenovo', 'Ultra-léger et puissant : le ThinkPad haut de gamme à prix promo.',
            "Processeur : Intel Core i7 8e génération\nMémoire : 16 Go\nStockage : SSD 256 Go\nÉcran : 14\"", 250000, null, 1, 1, null],
        ['promo', 'HP EliteBook 840 G5 tactile Core i5', 'HP', 'Élégant, solide, avec écran tactile 14 pouces.',
            "Processeur : Intel Core i5 8e génération\nMémoire : 8 Go\nStockage : SSD 256 Go\nÉcran : 14\" tactile", 190000, null, 0, 1, null],
        ['promo', 'Lenovo ThinkPad X380 Yoga tactile', 'Lenovo', 'Écran tactile pliable à 360° : ordinateur, tablette ou mode présentation.',
            "Processeur : Intel Core i5 8e génération\nMémoire : 8 Go\nStockage : SSD 256 Go\nÉcran : tactile et pliable (2-en-1)", 180000, null, 0, 1, null],
        ['tactile', 'Lenovo ThinkPad T490s tactile Core i7', 'Lenovo', 'Fin et performant avec 16 Go de mémoire et écran tactile.',
            "Processeur : Intel Core i7 8e génération\nMémoire : 16 Go\nStockage : SSD 256 Go\nÉcran : 14\" tactile", 200000, null, 1, 1, null],
        ['tactile', 'Dell Latitude 7390 2-en-1 Core i7', 'Dell', 'Le 2-en-1 professionnel : écran détachable/pliable et Core i7.',
            "Processeur : Intel Core i7 8e génération\nMémoire : 16 Go\nStockage : SSD 256 Go\nÉcran : 13,3\" tactile et pliable", 210000, null, 0, 1, null],
        ['tactile', 'HP EliteBook x360 1040 G6 Core i7', 'HP', 'Convertible premium, finition aluminium et écran tactile pliable.',
            "Processeur : Intel Core i7 8e génération\nMémoire : 16 Go\nStockage : SSD 256 Go\nÉcran : tactile et pliable (360°)", 280000, null, 0, 1, null],
        ['pro', 'Dell Latitude 5400 Core i5', 'Dell', 'Le compagnon de bureau robuste et économique.',
            "Processeur : Intel Core i5 8e génération\nMémoire : 8 Go\nStockage : SSD 256 Go", 200000, null, 0, 1, null],
        ['pro', 'Dell Latitude 5500 Core i7 15,6"', 'Dell', 'Grand écran et pavé numérique : parfait pour la comptabilité et la gestion.',
            "Processeur : Intel Core i7 8e génération\nMémoire : 8 Go\nStockage : SSD 256 Go\nÉcran : 15,6\"\nClavier : avec pavé numérique", 200000, null, 0, 1, null],
        ['pro', 'HP EliteBook 830 G7 Core i7 10e gén.', 'HP', 'Compact et rapide, 16 Go de mémoire et SSD 512 Go.',
            "Processeur : Intel Core i7 10e génération\nMémoire : 16 Go\nStockage : SSD 512 Go", 400000, null, 1, 1, null],
        ['pro', 'Lenovo ThinkPad P14s Core i7 10e gén. (neuf)', 'Lenovo', 'Station de travail mobile neuve avec carte graphique dédiée.',
            "Processeur : Intel Core i7 10e génération\nMémoire : 16 Go\nStockage : SSD 512 Go\nCarte graphique : dédiée\nÉtat : neuf", 400000, null, 0, 0, null],
        ['pro', 'Lenovo ThinkPad P14s Core i7 11e gén. (neuf)', 'Lenovo', 'Pour l\'architecture, le design et le montage : carte graphique 4 Go.',
            "Processeur : Intel Core i7 11e génération\nMémoire : 16 Go\nStockage : SSD 512 Go\nCarte graphique : 4 Go dédiée\nÉtat : neuf", 450000, null, 1, 0, null],
        ['pro', 'HP Laptop 15,6" Core i7 12e gén. (neuf)', 'HP', 'Neuf, rapide et grand écran pour le travail et le multimédia.',
            "Processeur : Intel Core i7 12e génération\nMémoire : 16 Go\nStockage : SSD 512 Go\nÉcran : 15,6\"\nÉtat : neuf", 650000, null, 0, 0, null],
        ['haut', 'HP ZBook 17 G6 station de travail', 'HP', 'Puissance extrême : processeur Xeon, 32 Go de RAM et carte NVIDIA RTX.',
            "Processeur : Intel Xeon (équivalent Core i9)\nMémoire : 32 Go\nStockage : SSD 512 Go\nCarte graphique : NVIDIA RTX 4000 6 Go dédiée", 625000, null, 1, 1, 'hp-zbook-17-g6.jpg'],
        ['haut', 'HP OmniBook Core i7 (neuf)', 'HP', 'Ultraportable dernière génération avec 1 To de stockage.',
            "Processeur : Intel Core i7 15e génération\nMémoire : 16 Go\nStockage : SSD 1 To\nÉcran : 14\"\nÉtat : neuf", 1000000, null, 1, 0, 'hp-omnibook.jpg'],
        ['haut', 'Lenovo Yoga double écran', 'Lenovo', 'Deux écrans tactiles OLED pliables : une nouvelle façon de travailler.',
            "Processeur : Intel Core i7 13e génération\nMémoire : 16 Go\nStockage : SSD 1 To\nÉcran : 2 x 13\" tactiles et pliables", 1500000, null, 1, 0, 'lenovo-yoga-double-ecran.jpg'],
    ];
    $st = $pdo->prepare('INSERT INTO products (category_id, name, slug, brand, short_desc, description, specs, price, old_price, stock, image, featured, is_used)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($products as [$cat, $name, $brand, $short, $specs, $price, $old, $featured, $used, $image]) {
        $description = $short . "\n\n" . ($used ? 'Ordinateur reconditionné, testé et configuré par nos techniciens avant la vente. ' : 'Ordinateur neuf. ')
            . 'Disponible chez ' . SITE_NAME . ' à Chagoua (N\'Djamena), avec conseils personnalisés et service après-vente.';
        $st->execute([$catIds[$cat], $name, slugify($name), $brand, $short, $description, $specs,
            $price, $old, 5, $image ? 'assets/img/produits/' . $image : null, $featured, $used]);
    }

    // [icône, nom, résumé, description, à partir de (0 = sur devis), durée]
    $services = [
        ['🛠️', 'Maintenance & réparation', 'Ordinateur lent, qui ne démarre plus, écran ou clavier cassé, surchauffe…',
            "Diagnostic de votre ordinateur portable ou de bureau.\nRéparation et remplacement de pièces (écran, clavier, batterie, disque…).\nSuppression des virus et des lenteurs.\nDevis avant toute intervention.", 0, ''],
        ['🧹', 'Nettoyage & optimisation', 'Dépoussiérage interne, pâte thermique, système plus rapide.',
            "Dépoussiérage complet, essentiel avec la poussière de N'Djamena.\nRemplacement de la pâte thermique contre la surchauffe.\nOptimisation du démarrage et mises à jour.", 0, ''],
        ['💿', 'Installation Windows & logiciels', 'Windows, suite bureautique, antivirus, logiciels métiers.',
            "Installation de Windows et des pilotes.\nSuite bureautique, antivirus, navigateurs et logiciels utiles.\nTransfert de vos fichiers de l'ancien ordinateur vers le nouveau.", 0, ''],
        ['🎓', 'Formation informatique', 'Initiation, bureautique et Internet, pour particuliers et entreprises.',
            "Initiation à l'ordinateur et à Internet.\nWord, Excel, PowerPoint du débutant au niveau avancé.\nSessions individuelles ou en groupe.", 0, ''],
        ['🤝', 'Coaching informatique', 'Un accompagnement personnalisé pour maîtriser vos outils.',
            "Prise en main de votre nouvel ordinateur.\nOrganisation de vos fichiers, sauvegardes et sécurité.\nAccompagnement adapté à votre métier et à votre rythme.", 0, ''],
        ['💡', 'Conseil avant achat', 'Nous vous aidons à choisir l\'ordinateur adapté à votre budget.',
            "Analyse de vos besoins : études, bureautique, graphisme, montage, gestion…\nComparaison des modèles disponibles.\nConseil gratuit en boutique ou sur WhatsApp.", 0, 'Gratuit'],
    ];
    $st = $pdo->prepare('INSERT INTO services (icon, name, slug, short_desc, description, price_from, duration, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($services as $i => [$icon, $name, $short, $desc, $price, $duration]) {
        $st->execute([$icon, $name, slugify($name), $short, $desc, $price, $duration, $i]);
    }
}
