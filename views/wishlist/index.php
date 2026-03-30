<?php
if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

$userId = currentUser()['id'];

// ── Handle POST (toggle/remove/add-to-cart) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = (int) $_POST['product_id'];
    $action = $_POST['action'] ?? 'toggle';

    if ($productId > 0) {
        if ($action === 'remove') {
            $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")
                ->execute([$userId, $productId]);

        } elseif ($action === 'add_to_cart') {
            $check = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
            $check->execute([$userId, $productId]);
            if ($check->fetch()) {
                $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?")
                    ->execute([$userId, $productId]);
            } else {
                $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)")
                    ->execute([$userId, $productId]);
            }
            $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")
                ->execute([$userId, $productId]);

        } else {
            $check = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $check->execute([$userId, $productId]);
            if ($check->fetch()) {
                $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")
                    ->execute([$userId, $productId]);
            } else {
                $pdo->prepare("INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())")
                    ->execute([$userId, $productId]);
            }
        }
    }

    $redirect = $_POST['redirect'] ?? 'index.php?page=wishlist';
    header('Location: ' . $redirect);
    exit;
}

// ── GET: fetch wishlist items ──
$stmt = $pdo->prepare("
    SELECT w.id AS wish_id, w.created_at AS added_at,
           p.id AS product_id, p.name, p.brand, p.price,
           p.image_url, p.stock_qty,
           c.name AS category_name
    FROM wishlist w
    JOIN products p ON p.id = w.product_id
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->execute([$userId]);
$wishlistItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    :root {
        --navy: #131921;
        --navy2: #232f3e;
        --gold: #ff9900;
        --gold2: #e68900;
    }

    /* ── Hero banner ── */
    .wl-hero {
        background: linear-gradient(135deg, var(--navy) 0%, var(--navy2) 60%, #37475a 100%);
        padding: 36px 40px;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        position: relative;
        overflow: hidden;
    }

    .wl-hero::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255, 153, 0, .1);
        filter: blur(48px);
        pointer-events: none;
    }

    .wl-hero::after {
        content: '';
        position: absolute;
        bottom: -40px;
        left: 80px;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .04);
        filter: blur(32px);
        pointer-events: none;
    }

    .wl-hero-left {
        position: relative;
        z-index: 1;
    }

    .wl-hero-breadcrumb {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 12.5px;
        color: rgba(255, 255, 255, .45);
        margin-bottom: 10px;
    }

    .wl-hero-breadcrumb a {
        color: rgba(255, 255, 255, .45);
        text-decoration: none;
        transition: color .15s;
    }

    .wl-hero-breadcrumb a:hover {
        color: var(--gold);
    }

    .wl-hero-breadcrumb i {
        font-size: 9px;
    }

    .wl-hero-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: clamp(1.8rem, 3.5vw, 2.6rem);
        color: white;
        line-height: 1.1;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .wl-hero-title i {
        color: var(--gold);
        font-size: .85em;
    }

    .wl-hero-sub {
        font-size: 14px;
        color: rgba(255, 255, 255, .55);
        margin-top: 6px;
    }

    .wl-hero-badge {
        position: relative;
        z-index: 1;
        background: rgba(255, 153, 0, .15);
        border: 1px solid rgba(255, 153, 0, .3);
        border-radius: 16px;
        padding: 16px 24px;
        text-align: center;
        flex-shrink: 0;
    }

    .wl-hero-badge-num {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: 36px;
        color: var(--gold);
        line-height: 1;
    }

    .wl-hero-badge-lbl {
        font-size: 12px;
        color: rgba(255, 255, 255, .5);
        margin-top: 3px;
    }

    /* ── Table card ── */
    .wl-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 16px rgba(0, 0, 0, .06);
        overflow: hidden;
    }

    /* ── Table ── */
    .wl-table {
        width: 100%;
        border-collapse: collapse;
    }

    .wl-table thead tr {
        background: linear-gradient(135deg, var(--navy), var(--navy2));
    }

    .wl-table thead th {
        padding: 16px 20px;
        text-align: left;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 13px;
        color: rgba(255, 255, 255, .85);
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .wl-table thead th:first-child {
        padding-left: 24px;
    }

    .wl-table thead th.center {
        text-align: center;
    }

    .wl-table thead th.right {
        text-align: right;
        padding-right: 24px;
    }

    .wl-table thead th i {
        color: var(--gold);
        margin-right: 7px;
        font-size: 11px;
    }

    .wl-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s;
        animation: rowIn .4s ease both;
    }

    .wl-table tbody tr:last-child {
        border-bottom: none;
    }

    .wl-table tbody tr:hover {
        background: #fafcff;
    }

    @keyframes rowIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .wl-table tbody tr:nth-child(1) {
        animation-delay: .05s;
    }

    .wl-table tbody tr:nth-child(2) {
        animation-delay: .10s;
    }

    .wl-table tbody tr:nth-child(3) {
        animation-delay: .15s;
    }

    .wl-table tbody tr:nth-child(4) {
        animation-delay: .20s;
    }

    .wl-table tbody tr:nth-child(5) {
        animation-delay: .25s;
    }

    .wl-table tbody tr:nth-child(6) {
        animation-delay: .30s;
    }

    .wl-table td {
        padding: 18px 20px;
        vertical-align: middle;
        font-size: 14px;
        color: #374151;
    }

    .wl-table td:first-child {
        padding-left: 24px;
    }

    .wl-table td.center {
        text-align: center;
    }

    .wl-table td.right {
        text-align: right;
        padding-right: 24px;
    }

    /* Remove button */
    .wl-remove-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1.5px solid #e5e7eb;
        background: white;
        color: #9ca3af;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all .15s;
    }

    .wl-remove-btn:hover {
        border-color: #fca5a5;
        background: #fef2f2;
        color: #ef4444;
    }

    /* Product cell */
    .wl-product-cell {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .wl-product-img {
        width: 72px;
        height: 72px;
        border-radius: 12px;
        object-fit: cover;
        background: #f1f5f9;
        flex-shrink: 0;
        border: 1px solid #f1f5f9;
        transition: transform .2s;
    }

    .wl-table tbody tr:hover .wl-product-img {
        transform: scale(1.04);
    }

    .wl-product-name {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 14.5px;
        color: #111827;
        text-decoration: none;
        transition: color .15s;
        display: block;
    }

    .wl-product-name:hover {
        color: var(--gold);
    }

    .wl-product-meta {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 3px;
    }

    /* Price */
    .wl-price {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 15px;
        color: var(--navy);
    }

    /* Date */
    .wl-date {
        font-size: 13px;
        color: #6b7280;
        white-space: nowrap;
    }

    /* Stock badge */
    .wl-stock {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12.5px;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 20px;
    }

    .wl-stock.instock {
        background: #f0fdf4;
        color: #16a34a;
    }

    .wl-stock.lowstock {
        background: #fff7ed;
        color: #c2410c;
    }

    .wl-stock.outstock {
        background: #fef2f2;
        color: #dc2626;
    }

    .wl-stock i {
        font-size: 9px;
    }

    /* Add to cart button */
    .wl-cart-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: linear-gradient(135deg, var(--navy), var(--navy2));
        color: white;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 13px;
        padding: 10px 20px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        white-space: nowrap;
        transition: transform .15s, box-shadow .15s;
        box-shadow: 0 3px 10px rgba(19, 25, 33, .2);
    }

    .wl-cart-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(19, 25, 33, .28);
    }

    .wl-cart-btn:active {
        transform: translateY(0);
    }

    .wl-cart-btn i {
        color: var(--gold);
    }

    .wl-cart-btn.out {
        background: #e5e7eb;
        color: #9ca3af;
        box-shadow: none;
        cursor: not-allowed;
        transform: none;
    }

    .wl-cart-btn.out i {
        color: #9ca3af;
    }

    /* ── Empty state ── */
    .wl-empty {
        padding: 72px 24px;
        text-align: center;
    }

    .wl-empty-icon {
        font-size: 64px;
        margin-bottom: 16px;
        animation: heartbeat 2s ease-in-out infinite;
    }

    @keyframes heartbeat {

        0%,
        100% {
            transform: scale(1);
        }

        14% {
            transform: scale(1.15);
        }

        28% {
            transform: scale(1);
        }

        42% {
            transform: scale(1.08);
        }
    }

    .wl-empty h3 {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 20px;
        color: var(--navy);
        margin-bottom: 8px;
    }

    .wl-empty p {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 24px;
    }

    .wl-empty-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--navy), var(--navy2));
        color: white;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 14px;
        padding: 13px 28px;
        border-radius: 12px;
        text-decoration: none;
        transition: transform .15s, box-shadow .15s;
        box-shadow: 0 4px 14px rgba(19, 25, 33, .2);
    }

    .wl-empty-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(19, 25, 33, .28);
    }

    .wl-empty-btn i {
        color: var(--gold);
    }

    /* ── Footer bar ── */
    .wl-footer-bar {
        padding: 18px 24px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        background: #fafcff;
    }

    .wl-footer-info {
        font-size: 13px;
        color: #6b7280;
    }

    .wl-footer-info strong {
        color: var(--navy);
        font-weight: 700;
    }

    .wl-add-all-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--gold);
        color: var(--navy);
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 13.5px;
        padding: 11px 22px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        transition: background .15s, transform .15s;
        box-shadow: 0 3px 12px rgba(255, 153, 0, .3);
        text-decoration: none;
    }

    .wl-add-all-btn:hover {
        background: var(--gold2);
        transform: translateY(-1px);
    }

    /* ── Responsive ── */
    @media (max-width: 900px) {

        .wl-table thead th.hide-md,
        .wl-table td.hide-md {
            display: none;
        }
    }

    @media (max-width: 640px) {

        .wl-table thead th.hide-sm,
        .wl-table td.hide-sm {
            display: none;
        }

        .wl-hero {
            padding: 24px 20px;
        }

        .wl-hero-badge {
            display: none;
        }

        .wl-product-img {
            width: 54px;
            height: 54px;
        }

        .wl-table td,
        .wl-table th {
            padding: 14px 12px;
        }

        .wl-table td:first-child,
        .wl-table th:first-child {
            padding-left: 16px;
        }
    }
</style>

<!-- Hero -->
<div class="wl-hero mt-8 mx-12">
    <div class="wl-hero-left">
        <div class="wl-hero-breadcrumb">
            <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span style="color:rgba(255,255,255,.75);">Wishlist</span>
        </div>
        <div class="wl-hero-title">
            <i class="fa-solid fa-heart"></i> My Wishlist
        </div>
        <p class="wl-hero-sub">Your saved items — add them to your cart whenever you're ready.</p>
    </div>
    <div class="wl-hero-badge">
        <div class="wl-hero-badge-num"><?= count($wishlistItems) ?></div>
        <div class="wl-hero-badge-lbl">Saved Items</div>
    </div>
</div>

<!-- Table card -->
<div class="wl-card mt-0 mx-12 mb-12">

    <?php if (empty($wishlistItems)): ?>
        <div class="wl-empty">
            <div class="wl-empty-icon">🤍</div>
            <h3>Your wishlist is empty</h3>
            <p>Browse our products and save the ones you love!</p>
            <a href="index.php?page=products" class="wl-empty-btn">
                <i class="fa-solid fa-bag-shopping"></i> Start Shopping
            </a>
        </div>

    <?php else: ?>

        <div style="overflow-x:auto;">
            <table class="wl-table">
                <thead>
                    <tr>
                        <th style="width:36px;"></th>
                        <th><i class="fa-solid fa-box-open"></i>Product</th>
                        <th><i class="fa-solid fa-tag"></i>Price</th>
                        <th class="hide-sm"><i class="fa-regular fa-calendar"></i>Date Added</th>
                        <th class="center hide-md"><i class="fa-solid fa-circle-check"></i>Stock Status</th>
                        <th class="right"><i class="fa-solid fa-cart-plus"></i>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($wishlistItems as $item):
                        $stock = (int) ($item['stock_qty'] ?? 0);
                        $inStock = $stock > 0;
                        $lowStock = $stock > 0 && $stock <= 5;
                        $imgSrc = !empty($item['image_url'])
                            ? htmlspecialchars(assetImageUrl($item['image_url']))
                            : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="72" height="72"%3E%3Crect width="72" height="72" fill="%23f1f5f9"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" font-size="28"%3E📦%3C/text%3E%3C/svg%3E';
                        ?>
                        <tr>
                            <!-- Remove -->
                            <td style="padding-left:20px;">
                                <form method="post" action="index.php?page=wishlist">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="redirect" value="index.php?page=wishlist">
                                    <button type="submit" class="wl-remove-btn" title="Remove from wishlist">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </td>

                            <!-- Product -->
                            <td>
                                <div class="wl-product-cell">
                                    <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                                        class="wl-product-img">
                                    <div>
                                        <a href="index.php?page=product&id=<?= $item['product_id'] ?>" class="wl-product-name">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </a>
                                        <div class="wl-product-meta">
                                            <?= htmlspecialchars($item['brand'] ?? '') ?>
                                            <?php if (!empty($item['category_name'])): ?>
                                                · <?= htmlspecialchars($item['category_name']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Price -->
                            <td>
                                <span class="wl-price"><?= formatCurrency($item['price']) ?></span>
                            </td>

                            <!-- Date Added -->
                            <td class="hide-sm">
                                <span class="wl-date">
                                    <i class="fa-regular fa-clock" style="margin-right:5px;color:#d1d5db;"></i>
                                    <?= date('d F Y', strtotime($item['added_at'])) ?>
                                </span>
                            </td>

                            <!-- Stock Status -->
                            <td class="center hide-md">
                                <?php if ($stock === 0): ?>
                                    <span class="wl-stock outstock">
                                        <i class="fa-solid fa-circle"></i> Out of Stock
                                    </span>
                                <?php elseif ($lowStock): ?>
                                    <span class="wl-stock lowstock">
                                        <i class="fa-solid fa-circle"></i> Low Stock (<?= $stock ?> left)
                                    </span>
                                <?php else: ?>
                                    <span class="wl-stock instock">
                                        <i class="fa-solid fa-circle"></i> In Stock
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Action -->
                            <td class="right">
                                <?php if ($inStock): ?>
                                    <form method="post" action="index.php?page=wishlist">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                        <input type="hidden" name="action" value="add_to_cart">
                                        <input type="hidden" name="redirect" value="index.php?page=wishlist">
                                        <button type="submit" class="wl-cart-btn">
                                            <i class="fa-solid fa-cart-plus"></i> Add to Cart
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="wl-cart-btn out" disabled>
                                        <i class="fa-solid fa-ban"></i> Out of Stock
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer bar -->
        <?php
        $inStockCount = count(array_filter($wishlistItems, fn($i) => (int) ($i['stock_qty'] ?? 0) > 0));
        ?>
        <div class="wl-footer-bar">
            <div class="wl-footer-info">
                <strong><?= count($wishlistItems) ?></strong> item<?= count($wishlistItems) !== 1 ? 's' : '' ?> saved
                &nbsp;·&nbsp;
                <strong><?= $inStockCount ?></strong> in stock
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <a href="index.php?page=products"
                    style="display:inline-flex;align-items:center;gap:7px;padding:10px 18px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;font-weight:600;color:#374151;text-decoration:none;transition:all .15s;"
                    onmouseover="this.style.borderColor='var(--gold)';this.style.color='var(--gold)'"
                    onmouseout="this.style.borderColor='#e5e7eb';this.style.color='#374151'">
                    <i class="fa-solid fa-bag-shopping" style="color:var(--gold);"></i> Continue Shopping
                </a>
                <?php if ($inStockCount > 0): ?>
                    <form method="post" action="index.php?page=wishlist-add-all">
                        <input type="hidden" name="redirect" value="index.php?page=cart">
                        <button type="submit" class="wl-add-all-btn">
                            <i class="fa-solid fa-cart-arrow-down"></i>
                            Add All to Cart (<?= $inStockCount ?>)
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    <?php endif; ?>
</div>