<?php
require __DIR__ . '/includes/bootstrap.php';

$services = db_all('SELECT * FROM services WHERE active = 1 ORDER BY sort_order, name');
$errors = [];

if (is_post()) {
    csrf_check();
    $serviceId = (int) post('service_id');
    $data = [
        'name' => post('name'), 'phone' => post('phone'), 'email' => post('email'),
        'device' => post('device'), 'location' => post('location') === 'domicile' ? 'domicile' : 'atelier',
        'date' => post('preferred_date'), 'description' => post('description'),
    ];
    if (!db_value('SELECT COUNT(*) FROM services WHERE id = ? AND active = 1', [$serviceId])) {
        $errors[] = 'Veuillez choisir un service.';
    }
    if (mb_strlen($data['name']) < 2) {
        $errors[] = 'Veuillez indiquer votre nom.';
    }
    if (!valid_phone($data['phone'])) {
        $errors[] = 'Veuillez indiquer un numéro de téléphone valide.';
    }
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L\'adresse e-mail n\'est pas valide.';
    }
    if ($data['date'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
        $data['date'] = '';
    }
    if (mb_strlen($data['description']) < 5) {
        $errors[] = 'Décrivez brièvement votre besoin ou la panne.';
    }

    if (!$errors) {
        $reference = 'SV' . substr(order_reference(), 2);
        db_exec('INSERT INTO service_requests (reference, service_id, name, phone, email, device, location, preferred_date, description)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $reference, $serviceId, $data['name'], $data['phone'], $data['email'], $data['device'],
            $data['location'], $data['date'] ?: null, $data['description'],
        ]);
        flash('Merci ! Votre demande ' . $reference . ' a été enregistrée. Un technicien vous rappelle très vite.');
        redirect('services.php#demande');
    }
}

$selected = (int) ($_POST['service_id'] ?? $_GET['service'] ?? 0);
$pageTitle = 'Services informatiques';
$pageDescription = 'Réparation, maintenance, installation réseau, vidéosurveillance, création de sites web et formation informatique à N\'Djamena.';
$activePage = 'services';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header page-header-dark">
    <div class="container">
        <h1>Services informatiques</h1>
        <p>Particuliers, commerces et entreprises : nos techniciens s'occupent de tout, en atelier ou sur site.</p>
        <a href="#demande" class="btn btn-accent">Demander une intervention</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="service-list">
            <?php foreach ($services as $s): ?>
                <article class="service-detail" id="<?= e($s['slug']) ?>">
                    <span class="service-icon"><?= e($s['icon']) ?></span>
                    <div>
                        <h2><?= e($s['name']) ?></h2>
                        <p class="lead"><?= e($s['short_desc']) ?></p>
                        <ul class="check-list">
                            <?php foreach (array_filter(array_map('trim', explode("\n", (string) $s['description']))) as $line): ?>
                                <li><?= e($line) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="service-meta">
                            <span class="service-price"><?= $s['price_from'] > 0 ? 'À partir de ' . e(money($s['price_from'])) : 'Devis gratuit' ?></span>
                            <?php if ($s['duration']): ?><span>⏱ <?= e($s['duration']) ?></span><?php endif; ?>
                        </div>
                        <div class="service-actions">
                            <a href="services.php?service=<?= (int) $s['id'] ?>#demande" class="btn btn-primary btn-sm">Réserver</a>
                            <a href="<?= e(whatsapp_link('Bonjour, je souhaite des informations sur le service : ' . $s['name'])) ?>" class="btn btn-wa btn-sm" target="_blank" rel="noopener">WhatsApp</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt" id="demande">
    <div class="container narrow">
        <div class="section-head center"><h2>Demander une intervention ou un devis</h2></div>
        <p class="center muted">Nous vous rappelons rapidement pour fixer un rendez-vous et vous donner un devis.</p>
        <form method="post" action="services.php#demande" class="form card">
            <?= csrf_field() ?>
            <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
            <label>Service souhaité *
                <select name="service_id" required>
                    <option value="">— Choisir —</option>
                    <?php foreach ($services as $s): ?>
                        <option value="<?= (int) $s['id'] ?>" <?= $selected === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['icon'] . ' ' . $s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="form-row">
                <label>Nom complet *<input type="text" name="name" required value="<?= e(post('name')) ?>"></label>
                <label>Téléphone *<input type="tel" name="phone" required value="<?= e(post('phone')) ?>" placeholder="+235 6X XX XX XX"></label>
            </div>
            <div class="form-row">
                <label>E-mail<input type="email" name="email" value="<?= e(post('email')) ?>"></label>
                <label>Appareil concerné<input type="text" name="device" value="<?= e(post('device')) ?>" placeholder="Ex. : HP Pavilion 15, imprimante Canon…"></label>
            </div>
            <div class="form-row">
                <label>Lieu d'intervention
                    <select name="location">
                        <option value="atelier">Dans votre atelier MaxiTech</option>
                        <option value="domicile" <?= post('location') === 'domicile' ? 'selected' : '' ?>>À domicile / en entreprise</option>
                    </select>
                </label>
                <label>Date souhaitée<input type="date" name="preferred_date" min="<?= date('Y-m-d') ?>" value="<?= e(post('preferred_date')) ?>"></label>
            </div>
            <label>Décrivez votre besoin *<textarea name="description" rows="4" required><?= e(post('description')) ?></textarea></label>
            <button class="btn btn-accent btn-lg btn-block" type="submit">Envoyer ma demande</button>
        </form>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
