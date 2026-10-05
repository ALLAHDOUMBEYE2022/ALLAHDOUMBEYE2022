/* MaxiTech — interactions du site public */
(function () {
    'use strict';

    // Menu mobile
    var toggle = document.querySelector('.menu-toggle');
    var nav = document.querySelector('.main-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    // Menu catégories (clic sur mobile / tactile)
    document.querySelectorAll('.cat-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () { btn.parentElement.classList.toggle('open'); });
    });

    // Filtres de la boutique sur mobile
    var filtersBtn = document.querySelector('.filters-toggle');
    if (filtersBtn) {
        filtersBtn.addEventListener('click', function () {
            document.querySelector('.shop-filters').classList.toggle('open');
        });
    }

    // Comptes à rebours des promotions
    var countdowns = document.querySelectorAll('.countdown[data-end]');
    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function tick() {
        countdowns.forEach(function (el) {
            var diff = Math.max(0, new Date(el.dataset.end) - new Date());
            var d = Math.floor(diff / 864e5), h = Math.floor(diff / 36e5) % 24,
                m = Math.floor(diff / 6e4) % 60, s = Math.floor(diff / 1e3) % 60;
            el.textContent = '⏳ ' + d + 'j ' + pad(h) + 'h ' + pad(m) + 'm ' + pad(s) + 's';
        });
    }
    if (countdowns.length) { tick(); setInterval(tick, 1000); }

    // Boutons +/- de quantité
    document.querySelectorAll('.qty-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.parentElement.querySelector('input');
            var max = parseInt(input.max || '99', 10);
            var v = (parseInt(input.value, 10) || 1) + parseInt(btn.dataset.step, 10);
            input.value = Math.min(max, Math.max(1, v));
        });
    });

    // Ajout au panier sans recharger la page (cartes produit)
    var toast;
    function showToast(html) {
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'toast';
            toast.setAttribute('role', 'status');
            document.body.appendChild(toast);
        }
        toast.innerHTML = html;
        toast.classList.add('show');
        clearTimeout(toast._t);
        toast._t = setTimeout(function () { toast.classList.remove('show'); }, 3500);
    }
    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    document.querySelectorAll('form.add-to-cart').forEach(function (form) {
        form.addEventListener('submit', function (ev) {
            if (!window.fetch) { return; }
            ev.preventDefault();
            // getAttribute : form.action renverrait le champ caché nommé "action".
            fetch(form.getAttribute('action'), {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'fetch' },
                credentials: 'same-origin'
            }).then(function (r) { return r.json(); }).then(function (data) {
                var count = document.querySelector('.cart-count');
                if (count) {
                    count.textContent = data.count;
                    count.classList.remove('bump'); void count.offsetWidth; count.classList.add('bump');
                }
                showToast(data.ok
                    ? '✅ ' + escapeHtml(data.name) + ' ajouté au panier <a href="panier.php">Voir le panier →</a>'
                    : '⚠️ Produit indisponible');
            }).catch(function () { form.submit(); });
        });
    });

    // Affichage des instructions selon le mode de paiement
    var payments = document.querySelectorAll('input[name="payment"]');
    function updatePayment() {
        var checked = document.querySelector('input[name="payment"]:checked');
        document.querySelectorAll('.payment-info').forEach(function (box) {
            box.classList.toggle('show', !!checked && box.dataset.for === checked.value);
        });
    }
    payments.forEach(function (r) { r.addEventListener('change', updatePayment); });
    updatePayment();

    // Ouverture automatique de WhatsApp après une commande "WhatsApp"
    var auto = document.querySelector('[data-autoopen="1"]');
    if (auto) { setTimeout(function () { window.open(auto.href, '_blank'); }, 800); }
})();
