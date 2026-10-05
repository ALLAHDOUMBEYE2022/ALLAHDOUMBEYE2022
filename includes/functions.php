<?php
/**
 * Fonctions utilitaires communes au site public et à l'administration.
 */

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money($amount): string
{
    return number_format((int) $amount, 0, ',', ' ') . ' ' . CURRENCY;
}

function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $text));
    return trim($text, '-') ?: 'item';
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function post(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

// ---------------------------------------------------------------------------
// Sécurité : CSRF
// ---------------------------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    if (!hash_equals(csrf_token(), (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Session expirée. Veuillez recharger la page et réessayer.');
    }
}

// ---------------------------------------------------------------------------
// Messages flash
// ---------------------------------------------------------------------------
function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function render_flash(): string
{
    $html = '';
    foreach ($_SESSION['flash'] ?? [] as $f) {
        $html .= '<div class="alert alert-' . e($f['type']) . '">' . e($f['message']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

// ---------------------------------------------------------------------------
// Produits
// ---------------------------------------------------------------------------
function product_image(array $product, string $prefix = ''): string
{
    $image = (string) ($product['image'] ?? '');
    // Photos livrées avec le site (catalogue initial).
    if (str_starts_with($image, 'assets/img/produits/') && is_file(ROOT_PATH . '/' . $image)) {
        return $prefix . 'assets/img/produits/' . rawurlencode(basename($image));
    }
    // Photos envoyées depuis l'administration.
    if ($image !== '' && is_file(UPLOAD_DIR . '/' . basename($image))) {
        return $prefix . 'uploads/' . rawurlencode(basename($image));
    }
    $icon = preg_replace('/[^a-z0-9-]/', '', $product['category_icon'] ?? '') ?: 'accessoire';
    return $prefix . 'assets/img/' . $icon . '.svg';
}

function discount_percent(array $product): int
{
    if (empty($product['old_price']) || $product['old_price'] <= $product['price']) {
        return 0;
    }
    return (int) round(100 - ($product['price'] * 100 / $product['old_price']));
}

function product_query(): string
{
    return 'SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon
            FROM products p JOIN categories c ON c.id = p.category_id';
}

function get_categories(): array
{
    return db_all('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.active = 1) AS total
                   FROM categories c ORDER BY c.sort_order, c.name');
}

/** Catégories affichées aux visiteurs : uniquement celles qui contiennent des produits. */
function public_categories(): array
{
    return array_values(array_filter(get_categories(), fn($c) => (int) $c['total'] > 0));
}

function phone_link(string $phone): string
{
    return 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
}

function stock_label(int $stock): string
{
    if ($stock <= 0) {
        return '<span class="stock out">Rupture de stock</span>';
    }
    if ($stock <= 3) {
        return '<span class="stock low">Plus que ' . $stock . ' en stock !</span>';
    }
    return '<span class="stock in">En stock</span>';
}

// ---------------------------------------------------------------------------
// Panier (stocké en session : [product_id => quantité])
// ---------------------------------------------------------------------------
function cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    return array_sum(cart());
}

function cart_set(int $productId, int $qty): void
{
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = min($qty, 99);
    }
}

function cart_clear(): void
{
    unset($_SESSION['cart'], $_SESSION['coupon']);
}

/** Lignes du panier avec les données produit à jour (prix, stock). */
function cart_lines(): array
{
    $cart = cart();
    if (!$cart) {
        return [];
    }
    $ids = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $products = db_all(product_query() . " WHERE p.id IN ($placeholders) AND p.active = 1", $ids);

    $lines = [];
    foreach ($products as $p) {
        $qty = min((int) $cart[$p['id']], max(0, (int) $p['stock']));
        if ($qty <= 0) {
            continue;
        }
        $lines[] = ['product' => $p, 'qty' => $qty, 'total' => $qty * (int) $p['price']];
    }
    return $lines;
}

function coupon_discount(?string $code, int $subtotal): array
{
    $code = strtoupper(trim((string) $code));
    if ($code === '' || !isset(COUPONS[$code])) {
        return [0, null];
    }
    [$type, $value, $min] = COUPONS[$code];
    if ($subtotal < $min) {
        return [0, null];
    }
    $discount = $type === 'percent' ? (int) round($subtotal * $value / 100) : (int) $value;
    return [min($discount, $subtotal), $code];
}

function cart_totals(array $lines): array
{
    $subtotal = array_sum(array_column($lines, 'total'));
    [$discount, $coupon] = coupon_discount($_SESSION['coupon'] ?? null, $subtotal);
    $afterDiscount = $subtotal - $discount;
    $delivery = ($subtotal === 0 || $afterDiscount >= FREE_DELIVERY_FROM) ? 0 : DELIVERY_FEE;
    return [
        'subtotal' => $subtotal,
        'discount' => $discount,
        'coupon'   => $coupon,
        'delivery' => $delivery,
        'total'    => $afterDiscount + $delivery,
    ];
}

// ---------------------------------------------------------------------------
// WhatsApp & divers
// ---------------------------------------------------------------------------
function whatsapp_link(string $text = ''): string
{
    $url = 'https://wa.me/' . SITE_WHATSAPP;
    return $text !== '' ? $url . '?text=' . rawurlencode($text) : $url;
}

function order_reference(): string
{
    return 'MT' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

const ORDER_STATUSES = [
    'nouvelle'   => 'Nouvelle',
    'confirmee'  => 'Confirmée',
    'expediee'   => 'En livraison',
    'livree'     => 'Livrée',
    'annulee'    => 'Annulée',
];

const REQUEST_STATUSES = [
    'nouvelle'  => 'Nouvelle',
    'en_cours'  => 'En cours',
    'terminee'  => 'Terminée',
    'annulee'   => 'Annulée',
];

const PAYMENT_METHODS = [
    'livraison'    => 'Paiement à la livraison (espèces)',
    'mobile_money' => 'Mobile Money (Airtel / Moov)',
    'whatsapp'     => 'Finaliser sur WhatsApp',
];

function status_badge(string $status, array $labels): string
{
    return '<span class="badge badge-' . e($status) . '">' . e($labels[$status] ?? $status) . '</span>';
}

function valid_phone(string $phone): bool
{
    return (bool) preg_match('/^\+?[0-9 ]{8,16}$/', $phone);
}

function format_date(?string $date): string
{
    return $date ? date('d/m/Y H:i', strtotime($date)) : '';
}
