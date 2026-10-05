<?php
require __DIR__ . '/includes/bootstrap.php';

$faq = [
    'Livrez-vous partout au Tchad ?' => 'Nous livrons à N\'Djamena sous 24 h (souvent le jour même). Pour les autres villes, nous expédions par transporteur : contactez-nous sur WhatsApp pour connaître le délai et le tarif.',
    'Comment payer ma commande ?' => 'En espèces à la livraison, par Airtel Money ou Moov Money, ou directement en boutique. Pour les entreprises, le paiement par virement est possible sur facture.',
    'Les ordinateurs reconditionnés sont-ils fiables ?' => 'Oui. Chaque appareil reconditionné est testé, nettoyé et équipé d\'un SSD si nécessaire. Il est vendu avec une garantie de 6 mois.',
    'Que couvre la garantie ?' => 'La garantie couvre les pannes matérielles hors casse, oxydation et mauvaise utilisation. Le diagnostic et la réparation se font dans notre atelier à N\'Djamena.',
    'Installez-vous les logiciels ?' => 'Oui, l\'installation des logiciels essentiels (navigateur, lecteur PDF, antivirus, etc.) est offerte pour tout achat d\'ordinateur.',
    'Proposez-vous des prix pour les entreprises et écoles ?' => 'Oui, nous proposons des tarifs dégressifs pour les achats en quantité et des contrats de maintenance. Demandez un devis via la page Contact.',
];

$pageTitle = 'À propos';
$activePage = 'apropos';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header page-header-dark">
    <div class="container">
        <h1>À propos de <?= e(SITE_NAME) ?></h1>
        <p>Rendre la technologie accessible, fiable et abordable pour tous au Tchad.</p>
    </div>
</section>

<section class="section">
    <div class="container about-grid">
        <div>
            <h2>Notre histoire</h2>
            <p><?= e(SITE_NAME) ?> est une entreprise tchadienne spécialisée dans la vente de matériel informatique et les services numériques.
                Nous accompagnons les étudiants, les particuliers, les commerçants, les ONG et les entreprises dans le choix,
                l'installation et l'entretien de leurs équipements.</p>
            <p>Notre engagement : des produits authentiques, des prix justes, des conseils honnêtes et un service après-vente
                réellement disponible, ici, à N'Djamena.</p>
            <a href="boutique.php" class="btn btn-primary">Découvrir la boutique</a>
        </div>
        <div class="stats-grid">
            <div class="stat"><strong>2 000+</strong><span>clients satisfaits</span></div>
            <div class="stat"><strong>1 500+</strong><span>ordinateurs réparés</span></div>
            <div class="stat"><strong>80+</strong><span>entreprises accompagnées</span></div>
            <div class="stat"><strong>24 h</strong><span>livraison à N'Djamena</span></div>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-head"><h2>Nos valeurs</h2></div>
        <div class="why-grid">
            <div class="why-item"><span>🤝</span><h3>Confiance</h3><p>Transparence sur l'état, l'origine et le prix de chaque produit.</p></div>
            <div class="why-item"><span>⚡</span><h3>Réactivité</h3><p>Réponse rapide sur WhatsApp et interventions sous 24 à 72 h.</p></div>
            <div class="why-item"><span>🎯</span><h3>Expertise</h3><p>Des techniciens formés et passionnés par les nouvelles technologies.</p></div>
            <div class="why-item"><span>🌍</span><h3>Proximité</h3><p>Une équipe locale qui connaît les réalités et les besoins du Tchad.</p></div>
        </div>
    </div>
</section>

<section class="section" id="faq">
    <div class="container narrow">
        <div class="section-head"><h2>Questions fréquentes</h2></div>
        <div class="faq">
            <?php foreach ($faq as $q => $a): ?>
                <details>
                    <summary><?= e($q) ?></summary>
                    <p><?= e($a) ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
