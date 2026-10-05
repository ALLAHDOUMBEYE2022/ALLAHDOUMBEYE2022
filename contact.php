<?php
require __DIR__ . '/includes/bootstrap.php';

$errors = [];
if (is_post()) {
    csrf_check();
    // Piège anti-robot : ce champ invisible doit rester vide.
    if (post('website') !== '') {
        redirect('contact.php');
    }
    $name = post('name');
    $email = post('email');
    $phone = post('phone');
    $subject = post('subject');
    $message = post('message');

    if (mb_strlen($name) < 2) {
        $errors[] = 'Veuillez indiquer votre nom.';
    }
    if ($email === '' && $phone === '') {
        $errors[] = 'Indiquez au moins un e-mail ou un téléphone pour que nous puissions vous répondre.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L\'adresse e-mail n\'est pas valide.';
    }
    if (mb_strlen($message) < 5) {
        $errors[] = 'Votre message est trop court.';
    }
    if (!$errors) {
        db_exec('INSERT INTO messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)',
            [$name, $email, $phone, $subject, $message]);
        flash('Merci ' . $name . ', votre message a bien été envoyé. Nous vous répondons rapidement.');
        redirect('contact.php');
    }
}

$pageTitle = 'Contact';
$pageDescription = 'Contactez ' . SITE_NAME . ' : boutique informatique et atelier de réparation à N\'Djamena.';
$activePage = 'contact';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Contactez-nous</h1>
        <p>Une question, un devis pour votre entreprise, un produit introuvable ? Nous sommes là.</p>
    </div>
</section>

<section class="section">
    <div class="container contact-layout">
        <div class="contact-cards">
            <a class="contact-card" href="<?= e(phone_link(SITE_PHONE)) ?>"><span>📞</span><div><strong>Appel, WhatsApp, SMS</strong><?= e(SITE_PHONE) ?></div></a>
            <?php if (SITE_PHONE_2): ?><a class="contact-card" href="<?= e(phone_link(SITE_PHONE_2)) ?>"><span>📱</span><div><strong>Appel, WhatsApp, SMS</strong><?= e(SITE_PHONE_2) ?></div></a><?php endif; ?>
            <a class="contact-card" href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener"><span>💬</span><div><strong>WhatsApp</strong>Réponse rapide</div></a>
            <?php if (SITE_EMAIL): ?><a class="contact-card" href="mailto:<?= e(SITE_EMAIL) ?>"><span>✉️</span><div><strong>E-mail</strong><?= e(SITE_EMAIL) ?></div></a><?php endif; ?>
            <div class="contact-card"><span>📍</span><div><strong>Boutique & atelier</strong><?= e(SITE_ADDRESS) ?></div></div>
            <div class="contact-card"><span>🕗</span><div><strong>Horaires</strong><?= e(SITE_HOURS) ?></div></div>
            <iframe class="map" title="Plan d'accès" loading="lazy"
                    src="https://maps.google.com/maps?q=<?= e(rawurlencode(SITE_MAP_QUERY)) ?>&output=embed"></iframe>
        </div>

        <form method="post" class="form card">
            <?= csrf_field() ?>
            <h2>Envoyez-nous un message</h2>
            <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
            <div class="form-row">
                <label>Nom *<input type="text" name="name" required value="<?= e(post('name')) ?>"></label>
                <label>Téléphone<input type="tel" name="phone" value="<?= e(post('phone')) ?>"></label>
            </div>
            <label>E-mail<input type="email" name="email" value="<?= e(post('email')) ?>"></label>
            <label>Sujet
                <select name="subject">
                    <?php foreach (['Question sur un produit', 'Devis entreprise', 'Service après-vente', 'Partenariat', 'Autre'] as $s): ?>
                        <option <?= post('subject') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Message *<textarea name="message" rows="5" required><?= e(post('message')) ?></textarea></label>
            <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
            <button class="btn btn-primary btn-lg btn-block" type="submit">Envoyer</button>
        </form>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
