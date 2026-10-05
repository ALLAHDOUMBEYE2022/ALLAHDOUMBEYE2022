<?php
require __DIR__ . '/includes/bootstrap.php';

$lines = cart_lines();
if (!$lines) {
    flash('Votre panier est vide.', 'error');
    redirect('panier.php');
}
$totals = cart_totals($lines);
$errors = [];

if (is_post()) {
    csrf_check();
    $data = [
        'name'    => post('name'),
        'phone'   => post('phone'),
        'email'   => post('email'),
        'city'    => post('city', "N'Djamena"),
        'address' => post('address'),
        'payment' => post('payment'),
        'payment_ref' => post('payment_ref'),
        'notes'   => post('notes'),
    ];

    if (mb_strlen($data['name']) < 2) {
        $errors[] = 'Veuillez indiquer votre nom complet.';
    }
    if (!valid_phone($data['phone'])) {
        $errors[] = 'Veuillez indiquer un numéro de téléphone valide.';
    }
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L\'adresse e-mail n\'est pas valide.';
    }
    if (mb_strlen($data['address']) < 3) {
        $errors[] = 'Veuillez indiquer votre quartier / adresse de livraison.';
    }
    if (!isset(PAYMENT_METHODS[$data['payment']])) {
        $errors[] = 'Veuillez choisir un mode de paiement.';
    }

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            // Revérifie le stock au moment de la validation.
            foreach ($lines as $line) {
                $stock = (int) db_value('SELECT stock FROM products WHERE id = ?', [$line['product']['id']]);
                if ($stock < $line['qty']) {
                    throw new RuntimeException('Le stock de « ' . $line['product']['name'] . ' » a changé. Merci de vérifier votre panier.');
                }
            }

            $reference = order_reference();
            db_exec('INSERT INTO orders (reference, customer_name, phone, email, city, address, payment_method, payment_ref, notes,
                                         subtotal, discount, coupon, delivery_fee, total)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $reference, $data['name'], $data['phone'], $data['email'], $data['city'], $data['address'],
                $data['payment'], $data['payment_ref'], $data['notes'],
                $totals['subtotal'], $totals['discount'], $totals['coupon'], $totals['delivery'], $totals['total'],
            ]);
            $orderId = (int) $pdo->lastInsertId();

            foreach ($lines as $line) {
                $p = $line['product'];
                db_exec('INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity) VALUES (?, ?, ?, ?, ?)',
                    [$orderId, $p['id'], $p['name'], $p['price'], $line['qty']]);
                db_exec('UPDATE products SET stock = stock - ?, sales = sales + ? WHERE id = ?', [$line['qty'], $line['qty'], $p['id']]);
            }
            $pdo->commit();
        } catch (RuntimeException $ex) {
            $pdo->rollBack();
            flash($ex->getMessage(), 'error');
            redirect('panier.php');
        }

        cart_clear();
        $_SESSION['orders'][] = $reference;
        redirect('confirmation.php?ref=' . urlencode($reference));
    }
}

$pageTitle = 'Finaliser ma commande';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <ol class="steps"><li class="done">Panier</li><li class="current">Livraison & paiement</li><li>Confirmation</li></ol>
        <h1>Finaliser ma commande</h1>
    </div>
</section>

<section class="section">
    <div class="container cart-layout">
        <form method="post" class="form checkout-form">
            <?= csrf_field() ?>
            <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

            <h2>1. Vos coordonnées</h2>
            <div class="form-row">
                <label>Nom complet *<input type="text" name="name" required value="<?= e(post('name')) ?>" autocomplete="name"></label>
                <label>Téléphone (WhatsApp) *<input type="tel" name="phone" required value="<?= e(post('phone')) ?>" placeholder="+235 6X XX XX XX" autocomplete="tel"></label>
            </div>
            <label>E-mail (facultatif)<input type="email" name="email" value="<?= e(post('email')) ?>" autocomplete="email"></label>

            <h2>2. Livraison</h2>
            <div class="form-row">
                <label>Ville *<input type="text" name="city" required value="<?= e(post('city', "N'Djamena")) ?>"></label>
                <label>Quartier / adresse précise *<input type="text" name="address" required value="<?= e(post('address')) ?>" placeholder="Ex. : Moursal, près de la pharmacie…"></label>
            </div>
            <label>Instructions (facultatif)<textarea name="notes" rows="2" placeholder="Horaire souhaité, repère…"><?= e(post('notes')) ?></textarea></label>

            <h2>3. Paiement</h2>
            <div class="payment-options">
                <?php foreach (PAYMENT_METHODS as $key => $label): ?>
                    <label class="payment-option">
                        <input type="radio" name="payment" value="<?= e($key) ?>" <?= post('payment', 'livraison') === $key ? 'checked' : '' ?>>
                        <span><?= e($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="payment-info" data-for="mobile_money">
                <p>Envoyez <strong><?= e(money($totals['total'])) ?></strong> à l'un de nos numéros, puis indiquez la référence de la transaction :</p>
                <ul>
                    <?php foreach (MOBILE_MONEY as $operator => $number): ?>
                        <li><strong><?= e($operator) ?></strong> : <?= e($number) ?> (<?= e(SITE_NAME) ?>)</li>
                    <?php endforeach; ?>
                </ul>
                <label>Référence / ID de transaction<input type="text" name="payment_ref" value="<?= e(post('payment_ref')) ?>" placeholder="Vous pouvez aussi l'envoyer plus tard"></label>
            </div>
            <div class="payment-info" data-for="whatsapp">
                <p>Après validation, un message récapitulatif s'ouvrira dans WhatsApp pour finaliser avec un conseiller.</p>
            </div>

            <button class="btn btn-accent btn-lg btn-block" type="submit">Confirmer ma commande — <?= e(money($totals['total'])) ?></button>
            <p class="muted small">Un conseiller vous appelle pour confirmer la commande avant la livraison.</p>
        </form>

        <aside class="cart-summary">
            <h2>Votre commande</h2>
            <ul class="mini-cart">
                <?php foreach ($lines as $line): ?>
                    <li><span><?= (int) $line['qty'] ?> × <?= e($line['product']['name']) ?></span><strong><?= e(money($line['total'])) ?></strong></li>
                <?php endforeach; ?>
            </ul>
            <dl class="totals">
                <dt>Sous-total</dt><dd><?= e(money($totals['subtotal'])) ?></dd>
                <?php if ($totals['discount']): ?><dt>Remise (<?= e($totals['coupon']) ?>)</dt><dd class="text-success">-<?= e(money($totals['discount'])) ?></dd><?php endif; ?>
                <dt>Livraison</dt><dd><?= $totals['delivery'] ? e(money($totals['delivery'])) : 'Offerte' ?></dd>
                <dt class="grand">Total</dt><dd class="grand"><?= e(money($totals['total'])) ?></dd>
            </dl>
            <a href="panier.php" class="btn btn-outline btn-block">Modifier le panier</a>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
