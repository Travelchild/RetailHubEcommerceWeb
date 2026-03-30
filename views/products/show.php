<?php if (!$product): ?>
    <div class="inline-flex items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>Product not found.
    </div>
<?php else: ?>

<?php
// ── Fetch related products from same category ─────────────────────────────
$relatedProducts = [];
if (!empty($product['category_id'])) {
    try {
        $relStmt = $pdo->prepare("
            SELECT p.id, p.name, p.brand, p.price, p.image_url, p.stock_qty,
                   c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.is_active = 1
              AND p.category_id = ?
              AND p.id != ?
            ORDER BY p.created_at DESC
            LIMIT 4
        ");
        $relStmt->execute([$product['category_id'], $product['id']]);
        $relatedProducts = $relStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $relatedProducts = []; }
}

// ── Recently viewed ───────────────────────────────────────────────────────
if (!isset($_SESSION['recently_viewed'])) $_SESSION['recently_viewed'] = [];
$pid = (int)$product['id'];
$_SESSION['recently_viewed'] = array_filter($_SESSION['recently_viewed'], fn($x) => $x !== $pid);
array_unshift($_SESSION['recently_viewed'], $pid);
$_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 10);

$recentlyViewedProducts = [];
$rvIds = array_filter($_SESSION['recently_viewed'], fn($x) => $x !== $pid);
if (!empty($rvIds)) {
    try {
        $placeholders = implode(',', array_fill(0, count($rvIds), '?'));
        $rvStmt = $pdo->prepare("
            SELECT id, name, brand, price, image_url, stock_qty
            FROM products WHERE id IN ($placeholders) AND is_active = 1 LIMIT 4
        ");
        $rvStmt->execute(array_values($rvIds));
        $recentlyViewedProducts = $rvStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $recentlyViewedProducts = []; }
}

// ── Determine if this product needs size selection ────────────────────────
// Categories that require a size to be picked before adding to cart
$sizeCategoryKeywords = [
    'fashion','clothing','clothes','tops','shirts','t-shirts','dresses','frocks',
    'bottoms','jeans','skirt','skirts','women','men','activewear','footwear',
    'shoes','sneakers','shoue','boots','sandals','heels','sport','sports'
];
$catName       = strtolower($product['category_name'] ?? '');
$requiresSize  = false;
foreach ($sizeCategoryKeywords as $kw) {
    if (str_contains($catName, $kw)) { $requiresSize = true; break; }
}

// Available sizes — pulled from product field OR default fashion sizes
$sizes = [];
if (!empty($product['sizes'])) {
    $decoded = json_decode($product['sizes'], true);
    $sizes   = is_array($decoded) ? $decoded : array_map('trim', explode(',', $product['sizes']));
}
if ($requiresSize && empty($sizes)) {
    // Default sizes for clothing/shoes if none stored
    $shoeCats = ['shoe','shoes','sneaker','sneakers','shoue','boots','sandals','heels','footwear'];
    $isShoe   = false;
    foreach ($shoeCats as $kw) { if (str_contains($catName, $kw)) { $isShoe = true; break; } }
    $sizes = $isShoe
        ? ['36','37','38','39','40','41','42','43','44']
        : ['XS','S','M','L','XL','XXL'];
}
?>

<style>
    .thumb-list { display:flex; gap:.6rem; margin-top:.75rem; }
    .thumb-item { width:80px; height:80px; border-radius:.75rem; overflow:hidden; cursor:pointer; border:2px solid transparent; transition:.2s; flex-shrink:0; }
    .thumb-item img { width:100%; height:100%; object-fit:cover; }
    .thumb-item.active, .thumb-item:hover { border-color:#4f46e5; }

    /* ── Size buttons ── */
    .size-btn {
        display:inline-flex; align-items:center; justify-content:center;
        min-width:42px; height:42px; padding:0 10px;
        border-radius:.65rem; border:1.5px solid #e2e8f0;
        font-size:.8rem; font-weight:700; color:#374151;
        cursor:pointer; transition:.2s; background:#fff;
        white-space:nowrap;
    }
    .size-btn:hover  { border-color:#6366f1; color:#6366f1; background:#f5f3ff; }
    .size-btn.selected { border-color:#4f46e5; background:#4f46e5; color:#fff; box-shadow:0 2px 8px rgba(79,70,229,.3); }
    .size-btn.unavailable { opacity:.35; cursor:not-allowed; text-decoration:line-through; }

    /* ── Size error shake ── */
    @keyframes shake {
        0%,100%{transform:translateX(0)} 20%{transform:translateX(-6px)} 40%{transform:translateX(6px)}
        60%{transform:translateX(-4px)} 80%{transform:translateX(4px)}
    }
    .size-error { animation:shake .4s ease; }
    .size-error-msg {
        display:none; margin-top:.5rem;
        font-size:.75rem; font-weight:700; color:#dc2626;
        display:flex; align-items:center; gap:.35rem;
    }
    .size-error-msg.show { display:flex; }

    .qty-stepper { display:inline-flex; align-items:center; border:1.5px solid #e2e8f0; border-radius:.75rem; overflow:hidden; }
    .qty-stepper button { width:38px; height:42px; background:none; border:none; font-size:1.1rem; color:#374151; cursor:pointer; transition:.15s; }
    .qty-stepper button:hover { background:#f1f5f9; }
    .qty-stepper input { width:48px; height:42px; border:none; border-left:1.5px solid #e2e8f0; border-right:1.5px solid #e2e8f0; text-align:center; font-size:.9rem; font-weight:700; color:#0f172a; outline:none; }

    .delivery-row { display:flex; align-items:flex-start; gap:.75rem; padding:.75rem 0; border-bottom:1px solid #f1f5f9; }
    .delivery-row:last-child { border-bottom:none; }
    .delivery-icon { flex-shrink:0; width:34px; height:34px; border-radius:.6rem; display:flex; align-items:center; justify-content:center; font-size:.85rem; }

    .tab-btn { padding:.55rem 1.1rem; font-size:.85rem; font-weight:600; border-bottom:2px solid transparent; color:#64748b; cursor:pointer; transition:.15s; background:none; border-top:none; border-left:none; border-right:none; }
    .tab-btn.active { color:#4f46e5; border-bottom-color:#4f46e5; }
    .tab-panel { display:none; }
    .tab-panel.active { display:block; }

    .rel-card { border-radius:1rem; overflow:hidden; background:#fff; border:1px solid #f1f5f9; transition:.2s; display:block; text-decoration:none; }
    .rel-card:hover { box-shadow:0 8px 24px rgba(0,0,0,.08); transform:translateY(-2px); }
    .rel-card img { width:100%; aspect-ratio:1/1; object-fit:cover; }
    .rel-card-body { padding:.75rem; }
    .icon-btn { width:40px; height:40px; border-radius:.75rem; border:1.5px solid #e2e8f0; background:#fff; display:inline-flex; align-items:center; justify-content:center; color:#64748b; cursor:pointer; transition:.2s; font-size:.9rem; }
    .icon-btn:hover { border-color:#6366f1; color:#6366f1; }
    .stock-pill { display:inline-flex; align-items:center; gap:.35rem; padding:.25rem .7rem; border-radius:999px; font-size:.75rem; font-weight:700; }

    .rel-section-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:.75rem; }
    .rel-section-title { font-size:1.2rem; font-weight:800; color:#0f172a; letter-spacing:-.02em; display:flex; align-items:center; gap:.6rem; }
    .rel-section-title .rel-cat-badge { display:inline-flex; align-items:center; gap:.3rem; background:#ede9fe; color:#6d28d9; font-size:.7rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; padding:.2rem .65rem; border-radius:999px; }
    .rel-view-all { display:inline-flex; align-items:center; gap:6px; font-size:.8rem; font-weight:700; color:#4f46e5; text-decoration:none; border:1.5px solid #e0e7ff; padding:.35rem .9rem; border-radius:.6rem; transition:.15s; }
    .rel-view-all:hover { background:#4f46e5; color:#fff; border-color:#4f46e5; }
    .rel-view-all i { font-size:10px; transition:transform .15s; }
    .rel-view-all:hover i { transform:translateX(3px); }
    .rel-stock-dot { width:7px; height:7px; border-radius:50%; display:inline-block; flex-shrink:0; }
</style>

<!-- Breadcrumb -->
<nav class="mb-6 flex items-center gap-2 text-xs text-slate-500 my-8 mx-12">
    <a href="index.php" class="hover:text-indigo-600 transition">Home</a>
    <i class="fa-solid fa-chevron-right text-[10px]"></i>
    <a href="index.php?page=products" class="hover:text-indigo-600 transition">Products</a>
    <?php if (!empty($product['category_name'])): ?>
    <i class="fa-solid fa-chevron-right text-[10px]"></i>
    <a href="index.php?page=products&category_id=<?= (int)($product['category_id'] ?? 0) ?>"
       class="hover:text-indigo-600 transition"><?= htmlspecialchars($product['category_name']) ?></a>
    <?php endif; ?>
    <i class="fa-solid fa-chevron-right text-[10px]"></i>
    <span class="text-slate-800 font-medium"><?= htmlspecialchars($product['name']) ?></span>
</nav>

<!-- ══ MAIN PRODUCT GRID ══ -->
<div class="grid gap-10 lg:grid-cols-2 lg:gap-14 lg:items-start my-8 mx-12">

    <!-- LEFT: Image gallery -->
    <div>
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft relative">
            <img id="main-img"
                 src="<?= htmlspecialchars(assetImageUrl($product['image_url'] ?? null)) ?>"
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 class="w-full aspect-square object-contain transition duration-300">
            <?php
            $availStock = (int)($product['available_stock'] ?? $product['stock_qty'] ?? 0);
            $inCart     = (int)($product['stock_qty'] ?? 0) - $availStock;
            ?>
            <?php if ($availStock > 0): ?>
                <span class="absolute top-3 left-3 stock-pill bg-emerald-100 text-emerald-700">
                    <i class="fa-solid fa-circle-check" style="font-size:.6rem;"></i> In Stock
                </span>
            <?php elseif ($inCart > 0): ?>
                <span class="absolute top-3 left-3 stock-pill bg-amber-100 text-amber-700">
                    <i class="fa-solid fa-cart-shopping" style="font-size:.6rem;"></i> In Your Cart
                </span>
            <?php else: ?>
                <span class="absolute top-3 left-3 stock-pill bg-red-100 text-red-600">
                    <i class="fa-solid fa-circle-xmark" style="font-size:.6rem;"></i> Out of Stock
                </span>
            <?php endif; ?>
        </div>

        <!-- Thumbnails -->
        <?php
        $thumbs = [];
        if (!empty($product['image_url']))   $thumbs[] = assetImageUrl($product['image_url']);
        if (!empty($product['image_url_2'])) $thumbs[] = assetImageUrl($product['image_url_2']);
        if (!empty($product['image_url_3'])) $thumbs[] = assetImageUrl($product['image_url_3']);
        if (!empty($product['image_url_4'])) $thumbs[] = assetImageUrl($product['image_url_4']);
        ?>
        <?php if (count($thumbs) > 0): ?>
        <div class="thumb-list overflow-x-auto pb-1">
            <?php foreach ($thumbs as $i => $thumb): ?>
            <div class="thumb-item <?= $i===0?'active':'' ?>"
                 onclick="switchImage(this, '<?= htmlspecialchars($thumb) ?>')">
                <img src="<?= htmlspecialchars($thumb) ?>" alt="View <?= $i+1 ?>" class="object-contain">
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- RIGHT: Product info -->
    <div class="flex flex-col gap-0">

        <div class="flex items-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                <i class="fa-solid fa-tag text-[9px]"></i>
                <?= htmlspecialchars($product['category_name'] ?? 'General') ?>
            </span>
            <?php if (!empty($product['brand'])): ?>
                <span class="text-xs text-slate-500">by <span class="font-semibold text-slate-700"><?= htmlspecialchars($product['brand']) ?></span></span>
            <?php endif; ?>
            <?php if (!empty($product['sku'])): ?>
                <span class="ml-auto text-xs text-slate-400">SKU: <span class="font-mono"><?= htmlspecialchars($product['sku']) ?></span></span>
            <?php endif; ?>
        </div>

        <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 leading-snug">
            <?= htmlspecialchars($product['name']) ?>
        </h1>

        <div class="mt-4 flex items-baseline gap-3">
            <span class="text-3xl font-extrabold text-slate-900"><?= formatCurrency($product['price']) ?></span>
            <?php if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']): ?>
                <span class="text-base text-slate-400 line-through"><?= formatCurrency($product['compare_price']) ?></span>
                <?php $disc = round((1 - $product['price'] / $product['compare_price']) * 100); ?>
                <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-600">-<?= $disc ?>%</span>
            <?php endif; ?>
        </div>

        <hr class="my-5 border-slate-100">

        <!-- ══ SIZE SELECTOR ══ -->
        <?php if (!empty($sizes)): ?>
        <div class="mb-5" id="size-section">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500 flex items-center gap-1.5">
                    <i class="fa-solid fa-ruler-horizontal text-indigo-400" style="font-size:.7rem;"></i>
                    Select Size
                    <?php if ($requiresSize): ?>
                        <span class="text-rose-500">*</span>
                    <?php endif; ?>
                </p>
                <a href="#" class="text-xs text-indigo-500 underline hover:text-indigo-700">Size Guide</a>
            </div>

            <div class="flex flex-wrap gap-2" id="size-group">
                <?php foreach ($sizes as $sz): ?>
                <button type="button" class="size-btn"
                        onclick="selectSize(this, '<?= htmlspecialchars($sz) ?>')">
                    <?= htmlspecialchars($sz) ?>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Error message shown when user tries to add without selecting -->
            <div class="size-error-msg" id="size-error-msg" style="display:none;">
                <i class="fa-solid fa-circle-exclamation"></i>
                Please select a size before adding to cart.
            </div>

            <!-- Selected size display -->
            <div id="selected-size-display" class="mt-2 hidden">
                <span class="text-xs text-slate-500">Selected: </span>
                <span id="selected-size-label" class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full"></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Qty + Add to cart -->
        <form method="post" action="index.php?page=cart-add" id="add-to-cart-form"
              onsubmit="return validateBeforeAdd(event)">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="hidden" name="size" id="form-size" value="">

            <?php
            $availStock = (int)($product['available_stock'] ?? $product['stock_qty'] ?? 0);
            $totalStock = (int)($product['stock_qty'] ?? 0);
            $inCart     = $totalStock - $availStock;
            $outOfStock = $availStock <= 0;
            ?>

            <?php if (!$outOfStock): ?>
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500 w-full">Quantity</p>
                <div class="qty-stepper">
                    <button type="button" onclick="changeQty(-1)">−</button>
                    <input type="number" id="qty" name="qty" min="1" max="<?= $availStock ?>" value="1" readonly>
                    <button type="button" onclick="changeQty(1)">+</button>
                </div>
                <span class="text-xs text-slate-400">
                    <?= $availStock ?> available
                    <?php if ($inCart > 0): ?>
                        <span class="ml-1 text-amber-600 font-semibold">(<?= $inCart ?> already in your cart)</span>
                    <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>

            <div class="flex flex-wrap gap-3">
                <?php if ($outOfStock): ?>
                <div class="flex-1 flex flex-col gap-2">
                    <div class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-100 px-6 py-3.5 text-sm font-bold text-slate-400 cursor-not-allowed border border-slate-200">
                        <i class="fa-solid fa-ban"></i>
                        <?= $inCart > 0 ? 'You\'ve added all available stock to your cart' : 'Out of Stock' ?>
                    </div>
                    <?php if ($inCart > 0): ?>
                    <a href="index.php?page=cart" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-700 px-6 py-3 text-sm font-bold text-white hover:from-brand-700 hover:to-indigo-800 transition">
                        <i class="fa-solid fa-cart-shopping"></i> View Cart
                    </a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <button type="submit" id="add-to-cart-btn"
                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-700 px-6 py-3.5 text-sm font-bold text-white shadow-md hover:from-brand-700 hover:to-indigo-800 transition">
                    <i class="fa-solid fa-cart-plus" aria-hidden="true"></i>
                    <?php if ($requiresSize && !empty($sizes)): ?>
                        Add to Cart — Select Size First
                    <?php else: ?>
                        Add to Cart
                    <?php endif; ?>
                </button>
                <?php endif; ?>
                <button type="button" class="icon-btn" title="Add to wishlist">
                    <i class="fa-regular fa-heart"></i>
                </button>
            </div>
        </form>

        <hr class="my-5 border-slate-100">

        <!-- Delivery info -->
        <div>
            <div class="delivery-row">
                <div class="delivery-icon bg-indigo-50 text-indigo-600"><i class="fa-solid fa-truck-fast"></i></div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Same Day Delivery</p>
                    <p class="text-xs text-slate-500 mt-0.5">Available for selected areas. Orders placed before 2 PM qualify.</p>
                </div>
            </div>
            <div class="delivery-row">
                <div class="delivery-icon bg-emerald-50 text-emerald-600"><i class="fa-solid fa-money-bill-wave"></i></div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Cash On Delivery</p>
                    <p class="text-xs text-slate-500 mt-0.5">Island-wide cash on delivery available.</p>
                </div>
            </div>
            <div class="delivery-row">
                <div class="delivery-icon bg-amber-50 text-amber-600"><i class="fa-solid fa-arrow-right-arrow-left"></i></div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Exchange From Physical Outlets</p>
                    <p class="text-xs text-slate-500 mt-0.5"><a href="#" class="text-indigo-500 underline">Learn more</a></p>
                </div>
            </div>
            <div class="delivery-row">
                <div class="delivery-icon bg-sky-50 text-sky-600"><i class="fa-solid fa-box"></i></div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Delivery Within 2–3 Business Days</p>
                    <p class="text-xs text-slate-500 mt-0.5">Island-wide delivery.</p>
                </div>
            </div>
        </div>

    </div><!-- /right col -->
</div><!-- /main grid -->

<!-- ══ TABS ══ -->
<div class="my-12 mx-12">
    <div class="flex gap-0 border-b border-slate-200">
        <button class="tab-btn active" onclick="switchTab(this,'tab-description')">Description</button>
        <button class="tab-btn" onclick="switchTab(this,'tab-shipping')">Shipping &amp; Return</button>
    </div>

    <div id="tab-description" class="tab-panel active mt-6">
        <?php if (!empty($product['description'])): ?>
            <div class="prose prose-sm max-w-none text-slate-600 leading-relaxed">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </div>
        <?php else: ?>
            <p class="text-sm text-slate-400 italic">No description available.</p>
        <?php endif; ?>
    </div>

    <div id="tab-shipping" class="tab-panel mt-6">
        <div class="space-y-3 text-sm text-slate-600">
            <p><strong class="text-slate-800">Standard Delivery:</strong> 2–3 business days island-wide.</p>
            <p><strong class="text-slate-800">Same Day Delivery:</strong> Available for orders placed before 2 PM in selected areas.</p>
            <p><strong class="text-slate-800">Returns:</strong> Exchange within 7 days from physical outlets with original receipt and tags intact.</p>
            <p><strong class="text-slate-800">Cash On Delivery:</strong> Available island-wide. Payment collected upon delivery.</p>
        </div>
    </div>
</div>

<!-- ══ RELATED PRODUCTS ══ -->
<?php if (!empty($relatedProducts)): ?>
<div class="mt-10 mb-12 mx-12">
    <div class="rel-section-header">
        <div class="rel-section-title">
            <i class="fa-solid fa-layer-group text-indigo-400" style="font-size:1rem;"></i>
            More in
            <span class="rel-cat-badge">
                <i class="fa-solid fa-tag" style="font-size:.55rem;"></i>
                <?= htmlspecialchars($product['category_name'] ?? 'This Category') ?>
            </span>
        </div>
        <a href="index.php?page=products&category_id=<?= (int)($product['category_id'] ?? 0) ?>" class="rel-view-all">
            View All <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <?php foreach ($relatedProducts as $rp):
            $rpStock = (int)($rp['stock_qty'] ?? 0); ?>
        <a href="index.php?page=product&id=<?= (int)$rp['id'] ?>" class="rel-card">
            <div style="position:relative;overflow:hidden;">
                <img src="<?= htmlspecialchars(assetImageUrl($rp['image_url'] ?? null)) ?>" alt="<?= htmlspecialchars($rp['name']) ?>">
                <span style="position:absolute;top:8px;right:8px;display:inline-flex;align-items:center;gap:4px;background:rgba(255,255,255,.92);border-radius:999px;padding:3px 8px;font-size:10px;font-weight:700;color:<?= $rpStock===0?'#dc2626':($rpStock<=5?'#c2410c':'#16a34a') ?>;">
                    <span class="rel-stock-dot" style="background:<?= $rpStock===0?'#dc2626':($rpStock<=5?'#f97316':'#22c55e') ?>;"></span>
                    <?= $rpStock===0?'Out of Stock':($rpStock<=5?'Only '.$rpStock.' left':'In Stock') ?>
                </span>
            </div>
            <div class="rel-card-body">
                <p class="text-[11px] font-semibold text-indigo-500 mb-1"><?= htmlspecialchars($rp['brand'] ?? '') ?></p>
                <p class="text-xs font-semibold text-slate-800 leading-snug line-clamp-2"><?= htmlspecialchars($rp['name']) ?></p>
                <p class="mt-1.5 text-sm font-bold text-slate-900"><?= formatCurrency($rp['price']) ?></p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ══ RECENTLY VIEWED ══ -->
<?php if (!empty($recentlyViewedProducts)): ?>
<div class="mt-4 mb-16 mx-12">
    <div class="rel-section-header">
        <div class="rel-section-title">
            <i class="fa-solid fa-clock-rotate-left text-slate-400" style="font-size:1rem;"></i>
            Recently Viewed
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <?php foreach ($recentlyViewedProducts as $rv): ?>
        <a href="index.php?page=product&id=<?= (int)$rv['id'] ?>" class="rel-card">
            <img src="<?= htmlspecialchars(assetImageUrl($rv['image_url'] ?? null)) ?>" alt="<?= htmlspecialchars($rv['name']) ?>">
            <div class="rel-card-body">
                <p class="text-xs font-semibold text-slate-800 leading-snug line-clamp-2"><?= htmlspecialchars($rv['name']) ?></p>
                <p class="mt-1 text-sm font-bold text-slate-900"><?= formatCurrency($rv['price']) ?></p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php endif; // end product check ?>

<script>
// ── Config from PHP ───────────────────────────────────────────────────────
const REQUIRES_SIZE = <?= $requiresSize ? 'true' : 'false' ?>;
const HAS_SIZES     = <?= !empty($sizes) ? 'true' : 'false' ?>;

// ── Size selection ────────────────────────────────────────────────────────
function selectSize(btn, size) {
    if (btn.classList.contains('unavailable')) return;

    // Deselect all, select clicked
    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');

    // Set hidden input
    document.getElementById('form-size').value = size;

    // Show selected size label
    const display = document.getElementById('selected-size-display');
    const label   = document.getElementById('selected-size-label');
    if (display && label) {
        label.textContent = size;
        display.classList.remove('hidden');
    }

    // Update button text to "Add to Cart"
    const addBtn = document.getElementById('add-to-cart-btn');
    if (addBtn) {
        addBtn.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Add to Cart';
    }

    // Hide error if visible
    hideSizeError();
}

function showSizeError() {
    const errMsg   = document.getElementById('size-error-msg');
    const sizeGrp  = document.getElementById('size-group');
    if (errMsg)  { errMsg.style.display = 'flex'; }
    if (sizeGrp) {
        sizeGrp.classList.add('size-error');
        // Add red border briefly to size buttons
        document.querySelectorAll('.size-btn').forEach(b => {
            b.style.borderColor = '#dc2626';
            b.style.background  = '#fef2f2';
        });
        setTimeout(() => {
            sizeGrp.classList.remove('size-error');
            document.querySelectorAll('.size-btn:not(.selected)').forEach(b => {
                b.style.borderColor = '';
                b.style.background  = '';
            });
        }, 600);
    }
    // Scroll to size section
    document.getElementById('size-section')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideSizeError() {
    const errMsg = document.getElementById('size-error-msg');
    if (errMsg) errMsg.style.display = 'none';
}

// ── Form submit validation ────────────────────────────────────────────────
function validateBeforeAdd(e) {
    if (REQUIRES_SIZE && HAS_SIZES) {
        const selectedSize = document.getElementById('form-size').value;
        if (!selectedSize) {
            e.preventDefault();
            showSizeError();
            return false;
        }
    }
    return true;
}

// ── Image gallery ─────────────────────────────────────────────────────────
function switchImage(thumb, src) {
    document.getElementById('main-img').src = src;
    document.querySelectorAll('.thumb-item').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}

// ── Quantity stepper ──────────────────────────────────────────────────────
function changeQty(delta) {
    const input = document.getElementById('qty');
    let val = parseInt(input.value, 10) + delta;
    const max = parseInt(input.max, 10) || 99;
    if (val < 1) val = 1;
    if (val > max) val = max;
    input.value = val;
}

// ── Tabs ──────────────────────────────────────────────────────────────────
function switchTab(btn, panelId) {
    document.querySelectorAll('.tab-btn').forEach(b  => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(panelId).classList.add('active');
}
</script>