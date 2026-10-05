</main>

<section class="trust-strip">
    <div class="container trust-grid">
        <div><strong>🚚 Livraison rapide</strong><span>À N'Djamena, offerte dès <?= e(money(FREE_DELIVERY_FROM)) ?></span></div>
        <div><strong>🛡️ <?= e(WARRANTY_TEXT) ?></strong><span>Neufs et reconditionnés, contrôlés par nos techniciens</span></div>
        <div><strong>💳 Paiement facile</strong><span>Espèces à la livraison ou Mobile Money</span></div>
        <div><strong>🎓 Formation & coaching</strong><span>Maintenance, formation et accompagnement</span></div>
    </div>
</section>

<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a href="index.php" class="footer-logo"><img src="assets/img/logo-complet.png" alt="<?= e(SITE_NAME) ?>" width="220" height="154"></a>
            <p><?= e(SITE_TAGLINE) ?>. Votre partenaire informatique de confiance pour les particuliers, étudiants et entreprises.</p>
            <div class="socials">
                <?php if (SITE_FACEBOOK): ?><a href="<?= e(SITE_FACEBOOK) ?>" target="_blank" rel="noopener" aria-label="Facebook">f</a><?php endif; ?>
                <?php if (SITE_INSTAGRAM): ?><a href="<?= e(SITE_INSTAGRAM) ?>" target="_blank" rel="noopener" aria-label="Instagram">◎</a><?php endif; ?>
                <?php if (SITE_TIKTOK): ?><a href="<?= e(SITE_TIKTOK) ?>" target="_blank" rel="noopener" aria-label="TikTok">♪</a><?php endif; ?>
                <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">✆</a>
            </div>
        </div>
        <div>
            <h4>Boutique</h4>
            <ul>
                <?php foreach (array_slice($headerCategories, 0, 6) as $cat): ?>
                    <li><a href="boutique.php?cat=<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h4>Informations</h4>
            <ul>
                <li><a href="services.php">Nos services</a></li>
                <li><a href="apropos.php">À propos</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="suivi.php">Suivre ma commande</a></li>
                <li><a href="apropos.php#faq">Questions fréquentes</a></li>
            </ul>
        </div>
        <div>
            <h4>Newsletter</h4>
            <p>Recevez nos promos et arrivages en avant-première.</p>
            <form action="newsletter.php" method="post" class="newsletter-form">
                <?= csrf_field() ?>
                <input type="email" name="email" placeholder="Votre e-mail" required aria-label="Votre e-mail">
                <button class="btn btn-accent" type="submit">OK</button>
            </form>
            <p class="footer-contact">📞 <?= e(SITE_PHONE) ?><?= SITE_PHONE_2 ? ' / ' . e(SITE_PHONE_2) : '' ?><br>
                <?php if (SITE_EMAIL): ?>✉️ <?= e(SITE_EMAIL) ?><br><?php endif; ?>📍 <?= e(SITE_ADDRESS) ?></p>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">© <?= date('Y') ?> <?= e(SITE_NAME) ?> — Tous droits réservés.</div>
    </div>
</footer>

<a href="<?= e(whatsapp_link('Bonjour ' . SITE_NAME . ', je souhaite avoir des informations.')) ?>"
   class="wa-float" target="_blank" rel="noopener" aria-label="Discuter sur WhatsApp">
    <svg viewBox="0 0 32 32" width="30" height="30" fill="#fff" aria-hidden="true"><path d="M16 3C9 3 3.3 8.6 3.3 15.6c0 2.2.6 4.4 1.7 6.3L3.2 29l7.3-1.9c1.8 1 3.9 1.5 6 1.5 7 0 12.7-5.7 12.7-12.7S23 3 16 3zm0 23.2c-1.9 0-3.8-.5-5.4-1.5l-.4-.2-4.3 1.1 1.2-4.2-.3-.4c-1.1-1.7-1.6-3.6-1.6-5.5C5.2 9.7 10 5 16 5s10.6 4.7 10.6 10.6S21.9 26.2 16 26.2zm5.8-7.9c-.3-.2-1.9-.9-2.2-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.2-.7.1-.3-.2-1.3-.5-2.5-1.6-.9-.8-1.6-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.5-.6c.2-.2.2-.3.3-.5.1-.2 0-.4 0-.6l-1-2.4c-.3-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.1-1.2 2.8s1.2 3.2 1.4 3.5c.2.2 2.4 3.6 5.7 5.1 2.8 1.1 3.4.9 4 .8.6-.1 1.9-.8 2.2-1.5.3-.7.3-1.4.2-1.5-.1-.1-.3-.2-.6-.3z"/></svg>
</a>

<script src="assets/js/main.js"></script>
</body>
</html>
