<?php
// ── Fetch user's wishlist product IDs ──
$wishlisted = [];
if (isLoggedIn()) {
    $ws = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $ws->execute([currentUser()['id']]);
    $wishlisted = $ws->fetchAll(PDO::FETCH_COLUMN);
}

/*
 * ── CATEGORY TREE ──────────────────────────────────────────────────────────
 * Expects a `categories` table: id, name, parent_id
 * Replace the query below with your actual table/column names if different.
 * The tree supports 3 levels: Top → Sub → Leaf (e.g. Men → Clothing → T-Shirts)
 */
$catTree = [];
if (isset($pdo)) {
    $cstmt = $pdo->query("SELECT id, name, parent_id FROM categories ORDER BY parent_id ASC, name ASC");
    $rawCats = $cstmt->fetchAll(PDO::FETCH_ASSOC);

    $catById = [];
    foreach ($rawCats as $c) {
        $catById[$c['id']] = $c + ['children' => []];
    }
    foreach ($catById as $id => &$node) {
        if ($node['parent_id'] && isset($catById[$node['parent_id']])) {
            $catById[$node['parent_id']]['children'][] = &$node;
        } else {
            $catTree[] = &$node;
        }
    }
    unset($node);
}

// Ceiling price for slider
$maxProductPrice = 0;
foreach ($products as $p) {
    if (($p['price'] ?? 0) > $maxProductPrice)
        $maxProductPrice = (float) $p['price'];
}
$sliderMax = (int) (ceil(max($maxProductPrice, 1000) / 1000) * 1000);

// Prepare JS data
$jsProducts = [];
$fashionCats = [
    'tops',
    'dresses',
    'bottoms',
    'jeans',
    'women',
    "women's wear",
    'fashion',
    'clothing',
    'men',
    "men's wear",
    'shirts',
    't-shirts',
    'frocks',
    'blouses'
];
foreach ($products as $p) {
    $hasSizes = in_array(strtolower($p['category_name'] ?? ''), $fashionCats);
    $jsProducts[] = [
        'id' => (int) $p['id'],
        'name' => $p['name'],
        'brand' => $p['brand'],
        'category' => $p['category_name'] ?? 'General',
        'catId' => (int) ($p['category_id'] ?? 0),
        'price' => (float) $p['price'],
        'stock' => (int) ($p['stock_qty'] ?? 0),
        'image' => assetImageUrl($p['image_url'] ?? null),
        'wishlisted' => in_array($p['id'], $wishlisted),
        'loggedIn' => isLoggedIn(),
        'detailUrl' => 'index.php?page=product&id=' . $p['id'],
        'cartUrl' => 'index.php?page=cart-add',
        'sizes' => $hasSizes ? ['XS', 'S', 'M', 'L', 'XL'] : [],
    ];
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Nunito+Sans:wght@300;400;600;700&display=swap');

    :root {
        --bg: #f8f7f4;
        --surface: #ffffff;
        --border: #e9e4dc;
        --muted: #a09a90;
        --text: #1e1c18;
        --accent: #c9a84c;
        --accent-dk: #a0832a;
        --danger: #c0392b;
        --brand: #28231c;
        --green: #2e7d50;
        --radius: 10px;
        --shadow: 0 2px 14px rgba(0, 0, 0, .06);
        --shadow-lg: 0 8px 32px rgba(0, 0, 0, .13);
        --sidebar: 252px;
        --t: .2s ease;
        --fh: 'Playfair Display', Georgia, serif;
        --fb: 'Nunito Sans', sans-serif;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0
    }

    /* ── Wrapper ── */
    .sw {
        display: flex;
        min-height: 100vh;
        background: var(--bg);
        font-family: var(--fb);
        color: var(--text)
    }

    /* ══ SIDEBAR ══ */
    .sw-sb {
        width: var(--sidebar);
        flex-shrink: 0;
        background: var(--surface);
        border-right: 1px solid var(--border);
        padding: 22px 16px 40px;
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--border) transparent;
    }

    .sb-h {
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--muted);
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border);
        margin-bottom: 10px;
    }

    .sb-hr {
        border: none;
        border-top: 1px solid var(--border);
        margin: 16px 0
    }

    /* Category scroll box — max 10rem = ~5 items visible */
    .cat-scroll {
        max-height: 10rem;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--border) transparent
    }

    /* Cat rows */
    .cat-list,
    .sub-list,
    .leaf-list {
        list-style: none
    }

    .cat-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 7px 5px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        cursor: pointer;
        border-radius: 6px;
        user-select: none;
        transition: background var(--t), color var(--t);
    }

    .cat-row:hover {
        background: #f4f0e8;
        color: var(--accent-dk)
    }

    .cat-row.active {
        color: var(--accent-dk)
    }

    .chev {
        font-size: 9px;
        color: var(--muted);
        transition: transform var(--t);
        flex-shrink: 0
    }

    .cat-row.open .chev {
        transform: rotate(90deg)
    }

    /* Sub level 1 */
    .sub-list {
        display: none;
        padding-left: 10px;
        border-left: 2px solid var(--border);
        margin: 2px 0 4px 8px;
    }

    .sub-list.open {
        display: block
    }

    .sub-list>.cat-item>.cat-row {
        font-size: 12.5px;
        font-weight: 500;
        color: #4a4540
    }

    /* Leaf level 2 */
    .leaf-list {
        display: none;
        padding-left: 10px;
        border-left: 2px solid #f0ebe0;
        margin: 2px 0 4px 8px;
    }

    .leaf-list.open {
        display: block
    }

    .leaf-item {
        padding: 5px 6px;
        font-size: 12px;
        font-weight: 400;
        color: var(--muted);
        cursor: pointer;
        border-radius: 5px;
        user-select: none;
        transition: background var(--t), color var(--t);
    }

    .leaf-item:hover {
        background: #f4f0e8;
        color: var(--accent-dk)
    }

    .leaf-item.active {
        color: var(--accent-dk);
        font-weight: 600
    }

    .cat-count {
        font-size: 10px;
        color: var(--muted);
        background: var(--bg);
        padding: 1px 6px;
        border-radius: 20px;
        flex-shrink: 0
    }

    /* Price */
    .price-slider {
        width: 100%;
        accent-color: var(--accent);
        cursor: pointer;
        margin-top: 6px;
        display: block
    }

    .price-disp {
        display: flex;
        justify-content: space-between;
        font-size: 11.5px;
        color: var(--muted);
        margin-top: 6px
    }

    .price-disp strong {
        color: var(--text);
        font-weight: 600
    }

    /* Availability */
    .avail-row {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 6px 5px;
        cursor: pointer;
        border-radius: 6px;
        font-size: 12.5px;
        font-weight: 500;
        color: var(--text);
        transition: background var(--t);
        user-select: none;
    }

    .avail-row:hover {
        background: #f4f0e8
    }

    .avail-row input[type=checkbox] {
        width: 14px;
        height: 14px;
        accent-color: var(--accent);
        cursor: pointer;
        flex-shrink: 0
    }

    /* Brand chips */
    .chip-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 5px
    }

    .chip {
        padding: 3px 11px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .02em;
        border-radius: 20px;
        border: 1px solid var(--border);
        background: var(--surface);
        cursor: pointer;
        user-select: none;
        color: var(--text);
        transition: all var(--t);
    }

    .chip:hover,
    .chip.active {
        border-color: var(--accent);
        background: #fdf8ee;
        color: var(--accent-dk)
    }

    /* ══ MAIN ══ */
    .sw-main {
        flex: 1;
        min-width: 0;
        padding: 28px 28px 56px
    }

    .pg-title {
        font-family: var(--fh);
        font-size: 32px;
        font-weight: 700;
        color: var(--brand);
        line-height: 1
    }

    .pg-sub {
        font-size: 12px;
        color: var(--muted);
        margin-top: 4px;
        margin-bottom: 18px
    }

    /* Toolbar */
    .toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 14px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border)
    }

    .tb-l {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
        min-width: 0
    }

    .tb-r {
        display: flex;
        align-items: center;
        gap: 8px
    }

    .search-box {
        display: flex;
        align-items: center;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
        flex: 1;
        max-width: 320px;
        transition: border-color var(--t);
    }

    .search-box:focus-within {
        border-color: var(--accent)
    }

    .search-box input {
        flex: 1;
        border: none;
        outline: none;
        padding: 9px 13px;
        font-family: var(--fb);
        font-size: 13px;
        background: transparent;
        color: var(--text);
    }

    .search-box button {
        padding: 9px 12px;
        background: none;
        border: none;
        border-left: 1px solid var(--border);
        cursor: pointer;
        color: var(--muted);
        transition: color var(--t);
    }

    .search-box button:hover {
        color: var(--accent-dk)
    }

    .item-count {
        font-size: 12px;
        color: var(--muted);
        white-space: nowrap
    }

    .sort-sel {
        padding: 8px 26px 8px 11px;
        font-family: var(--fb);
        font-size: 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface);
        color: var(--text);
        cursor: pointer;
        appearance: none;
        outline: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23a09a90' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
    }

    .vbtns {
        display: flex;
        gap: 2px
    }

    .vbtn {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        border-radius: 6px;
        background: var(--surface);
        cursor: pointer;
        color: var(--muted);
        font-size: 12px;
        transition: all var(--t);
    }

    .vbtn.active,
    .vbtn:hover {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff
    }

    /* Active filter pills */
    .af-wrap {
        display: none;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 14px
    }

    .af-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px 3px 12px;
        font-size: 11px;
        font-weight: 600;
        background: #fdf8ee;
        border: 1px solid var(--accent);
        border-radius: 20px;
        color: var(--accent-dk);
    }

    .af-pill button {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--accent);
        font-size: 12px;
        transition: color var(--t);
        padding: 0 2px
    }

    .af-pill button:hover {
        color: var(--danger)
    }

    /* Grid */
    .pgrid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px
    }

    @media(max-width:1100px) {
        .pgrid {
            grid-template-columns: repeat(2, 1fr)
        }
    }

    @media(max-width:700px) {
        .pgrid {
            grid-template-columns: 1fr
        }

        .sw-sb {
            display: none
        }

        .sw-main {
            padding: 14px
        }
    }

    /* Card */
    .pcard {
        background: var(--surface);
        border-radius: var(--radius);
        overflow: hidden;
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        display: flex;
        flex-direction: column;
        position: relative;
        transition: box-shadow var(--t), transform var(--t);
    }

    .pcard:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-4px)
    }

    .pc-img {
        position: relative;
        aspect-ratio: 4/4;
        overflow: hidden;
    }

    .pc-img img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;

    }

    .pcard:hover .pc-img img {
        transform: scale(1.05)
    }

    .p-brand {
        position: absolute;
        top: 9px;
        left: 9px;
        background: rgba(255, 255, 255, .92);
        backdrop-filter: blur(4px);
        border-radius: 4px;
        padding: 3px 8px;
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--brand);
        border: 1px solid rgba(0, 0, 0, .05);
    }

    .p-heart {
        position: absolute;
        top: 9px;
        right: 9px;
        width: 33px;
        height: 33px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .9);
        border: 1px solid rgba(0, 0, 0, .07);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 13px;
        color: var(--muted);
        transition: all var(--t);
        backdrop-filter: blur(4px);
    }

    .p-heart:hover {
        background: #fff2f2;
        color: var(--danger);
        border-color: #ffccc8
    }

    .p-heart.on {
        color: var(--danger);
        background: #fff0ef;
        border-color: #ffbab5
    }

    .p-oos,
    .p-low {
        position: absolute;
        bottom: 9px;
        left: 9px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        padding: 3px 9px;
        border-radius: 4px;
        backdrop-filter: blur(4px);
        color: #fff;
    }

    .p-oos {
        background: rgba(30, 28, 24, .76)
    }

    .p-low {
        background: rgba(201, 168, 76, .88)
    }

    .pc-body {
        padding: 13px 13px 15px;
        display: flex;
        flex-direction: column;
        flex: 1
    }

    .p-cat {
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 3px
    }

    .p-name {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text);
        line-height: 1.4;
        margin-bottom: 7px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .p-price {
        font-family: var(--fh);
        font-size: 17px;
        font-weight: 700;
        color: var(--brand);
        margin-bottom: 11px
    }

    .p-sizes {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-bottom: 11px
    }

    .sz {
        width: 28px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        color: var(--text);
        cursor: pointer;
        user-select: none;
        transition: all .12s;
    }

    .sz:hover {
        border-color: var(--brand);
        color: var(--brand);
        background: #f5f0e8
    }

    .p-actions {
        display: flex;
        gap: 7px;
        margin-top: auto
    }

    .btn-info {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface);
        color: var(--muted);
        text-decoration: none;
        font-size: 12px;
        transition: all var(--t);
    }

    .btn-info:hover {
        border-color: var(--brand);
        color: var(--brand);
        background: #f5f0e8
    }

    .btn-cart {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 14px;
        background: var(--brand);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-family: var(--fb);
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        cursor: pointer;
        width: 100%;
        transition: background var(--t);
    }

    .btn-cart:hover:not(:disabled) {
        background: var(--accent-dk)
    }

    .btn-cart:disabled {
        background: var(--muted);
        cursor: not-allowed
    }

    /* Empty */
    .empty-st {
        grid-column: 1/-1;
        text-align: center;
        padding: 80px 20px;
        color: var(--muted);
    }

    .empty-st i {
        font-size: 44px;
        opacity: .25;
        display: block;
        margin-bottom: 14px
    }

    .empty-st p {
        font-size: 14px
    }

    /* Pagination */
    .pg-wrap {
        margin-top: 32px;
        display: none;
        flex-direction: column;
        align-items: center;
        gap: 12px
    }

    .pg-info {
        font-size: 12px;
        color: var(--muted)
    }

    .pg-info strong {
        color: var(--text);
        font-weight: 600
    }

    .btn-more {
        padding: 12px 36px;
        background: var(--surface);
        border: 2px solid var(--brand);
        border-radius: 8px;
        font-family: var(--fb);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--brand);
        cursor: pointer;
        transition: all var(--t);
    }

    .btn-more:hover:not(:disabled) {
        background: var(--brand);
        color: #fff
    }

    .btn-more:disabled {
        border-color: var(--border);
        color: var(--muted);
        cursor: not-allowed
    }
</style>

<div class="sw">

    <!-- ═══ SIDEBAR ═══ -->
    <aside class="sw-sb">

        <div class="sb-h">Categories</div>
        <div class="cat-scroll">
            <ul class="cat-list" id="catTree"></ul>
        </div>

        <hr class="sb-hr">

        <div class="sb-h">Price Range</div>
        <input type="range" class="price-slider" id="priceSlider" min="0" max="<?= $sliderMax ?>"
            value="<?= $sliderMax ?>" step="100">
        <div class="price-disp">
            <span>Rs 0</span>
            <span>Max: <strong id="priceVal">Rs <?= number_format($sliderMax) ?></strong></span>
        </div>

        <hr class="sb-hr">

        <div class="sb-h">Availability</div>
        <label class="avail-row"><input type="checkbox" id="chkIn"> In Stock Only</label>
        <label class="avail-row"><input type="checkbox" id="chkOut"> Out of Stock</label>

        <hr class="sb-hr">

        <div class="sb-h">Brand</div>
        <div class="chip-wrap" id="brandChips"></div>

    </aside>

    <!-- ═══ MAIN ═══ -->
    <main class="sw-main my-8 mx-12">

        <h1 class="pg-title">Shop</h1>
        <p class="pg-sub">Discover our latest collections</p>

        <div class="toolbar">
            <div class="tb-l">
                <form class="search-box" onsubmit="handleSearch(event)">
                    <input type="search" id="searchInput" placeholder="Search by name or brand…"
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
                <span class="item-count" id="itemCount">– items</span>
            </div>
            <div class="tb-r">
                <div class="vbtns">
                    <button class="vbtn" title="4 columns" onclick="setGrid(this,4)"><i
                            class="fa-solid fa-grip"></i></button>
                    <button class="vbtn active" title="3 columns" onclick="setGrid(this,3)"><i
                            class="fa-solid fa-table-cells-large"></i></button>
                    <button class="vbtn" title="2 columns" onclick="setGrid(this,2)"><i
                            class="fa-solid fa-th-large"></i></button>
                    <button class="vbtn" title="List" onclick="setGrid(this,1)"><i
                            class="fa-solid fa-bars"></i></button>
                </div>
                <select class="sort-sel" id="sortSel">
                    <option value="default">Date: New</option>
                    <option value="price_asc">Price: Low → High</option>
                    <option value="price_desc">Price: High → Low</option>
                    <option value="name_az">Name A–Z</option>
                    <option value="stock_d">Most Stock</option>
                </select>
            </div>
        </div>

        <div class="af-wrap" id="afWrap"></div>

        <div class="pgrid" id="pgrid">
            <div class="empty-st"><i class="fa-solid fa-spinner fa-spin"></i>
                <p>Loading…</p>
            </div>
        </div>

        <div class="pg-wrap" id="pgWrap">
            <p class="pg-info" id="pgInfo"></p>
            <button class="btn-more" id="btnMore" onclick="loadMore()">
                <i class="fa-solid fa-chevron-down" style="margin-right:6px"></i>Show More Products
            </button>
        </div>

    </main>
</div>

<script>
    (function () {

        /* ═══ DATA ═══ */
        const AP = <?= json_encode($jsProducts, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?>;
        const CT = <?= json_encode($catTree, JSON_UNESCAPED_UNICODE) ?>;
        const MXP = <?= $sliderMax ?>;
        const PPG = 9;

        /* ═══ STATE ═══ */
        const S = {
            search: '<?= addslashes(htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES)) ?>',
            catId: null,
            catName: null,
            maxPrice: MXP,
            inStock: false,
            outStock: false,
            brands: new Set(),
            sort: 'default',
            page: 1,
        };

        /* ═══════════════════════════
           CATEGORY TREE
        ═══════════════════════════ */
        function buildCatTree() {
            const ul = document.getElementById('catTree');
            ul.innerHTML = '';

            // "All" row
            const allLi = ce('li');
            allLi.innerHTML = `<div class="cat-row${!S.catId && !S.catName ? ' active' : ''}"
        style="cursor:pointer" onclick="selAll()">
        All Products <span class="cat-count">${AP.length}</span></div>`;
            ul.appendChild(allLi);

            if (!CT || CT.length === 0) {
                // Fallback: derive flat list from products
                const seen = {};
                AP.forEach(p => { seen[p.category] = (seen[p.category] || 0) + 1; });
                Object.entries(seen).forEach(([name, cnt]) => {
                    const li = ce('li'); li.className = 'cat-item';
                    li.innerHTML = `<div class="cat-row${S.catName === name ? ' active' : ''}"
                onclick="selCatName('${esc(name)}',this)">
                ${h(name)}<span class="cat-count">${cnt}</span></div>`;
                    ul.appendChild(li);
                });
                return;
            }

            // Build from tree
            function render(nodes, container, depth) {
                nodes.forEach(node => {
                    const hasSub = node.children && node.children.length > 0;
                    const li = ce('li'); li.className = 'cat-item';
                    const chevron = hasSub ? `<i class="fa-solid fa-chevron-right chev"></i>` : `<span class="cat-count">${cntForId(node.id)}</span>`;
                    const isActive = S.catId === node.id;
                    const rowDiv = ce('div');
                    rowDiv.className = 'cat-row' + (isActive ? ' active' : '');
                    rowDiv.dataset.id = node.id;
                    rowDiv.innerHTML = `${h(node.name)}<span>${chevron}</span>`;
                    rowDiv.addEventListener('click', function (e) { e.stopPropagation(); selId(node.id, this, hasSub ? 'sub-' + node.id : null); });
                    li.appendChild(rowDiv);

                    if (hasSub) {
                        const subUl = ce('ul');
                        subUl.className = depth === 0 ? 'sub-list' : 'leaf-list';
                        subUl.id = 'sub-' + node.id;
                        if (isActive) subUl.classList.add('open');
                        render(node.children, subUl, depth + 1);
                        li.appendChild(subUl);
                    }
                    container.appendChild(li);
                });
            }
            render(CT, ul, 0);
        }

        function cntForId(id) { return AP.filter(p => p.catId === id).length; }

        function selAll() {
            S.catId = null; S.catName = null; S.page = 1;
            document.querySelectorAll('.cat-row,.leaf-item').forEach(r => r.classList.remove('active'));
            document.querySelector('#catTree .cat-row')?.classList.add('active');
            go();
        }
        window.selAll = selAll;

        function selId(id, el, subId) {
            // Toggle sub-list
            if (subId) {
                const sub = document.getElementById(subId);
                if (sub) {
                    const open = sub.classList.toggle('open');
                    el.classList.toggle('open', open);
                }
            }
            // Toggle selection
            S.catId = S.catId === id ? null : id;
            S.catName = null; S.page = 1;
            document.querySelectorAll('.cat-row').forEach(r => r.classList.remove('active'));
            if (S.catId) el.classList.add('active');
            else document.querySelector('#catTree .cat-row')?.classList.add('active');
            go();
        }

        function selCatName(name, el) {
            S.catName = name; S.catId = null; S.page = 1;
            document.querySelectorAll('.cat-row,.leaf-item').forEach(r => r.classList.remove('active'));
            el.classList.add('active');
            go();
        }
        window.selCatName = selCatName;

        /* ═══════════════════════════
           BRAND CHIPS
        ═══════════════════════════ */
        function buildBrands() {
            const wrap = document.getElementById('brandChips');
            const brands = [...new Set(AP.map(p => p.brand))].sort();
            wrap.innerHTML = brands.map(b => `<span class="chip${S.brands.has(b) ? ' active' : ''}"
        onclick="togBrand('${esc(b)}',this)">${h(b)}</span>`).join('');
        }
        function togBrand(b, el) {
            el.classList.toggle('active');
            S.brands.has(b) ? S.brands.delete(b) : S.brands.add(b);
            S.page = 1; go();
        }
        window.togBrand = togBrand;

        function removeBrand(b) {
            S.brands.delete(b); S.page = 1;
            buildBrands(); go();
        }
        window.removeBrand = removeBrand;

        /* ═══════════════════════════
           FILTERING + SORTING
        ═══════════════════════════ */
        function filtered() {
            let list = [...AP];

            if (S.catId) list = list.filter(p => p.catId === S.catId);
            else if (S.catName) list = list.filter(p => p.category === S.catName);

            if (S.search.trim()) {
                const q = S.search.toLowerCase();
                list = list.filter(p => p.name.toLowerCase().includes(q) || p.brand.toLowerCase().includes(q) || p.category.toLowerCase().includes(q));
            }

            list = list.filter(p => p.price <= S.maxPrice);

            if (S.inStock && !S.outStock) list = list.filter(p => p.stock > 0);
            if (S.outStock && !S.inStock) list = list.filter(p => p.stock <= 0);

            if (S.brands.size > 0) list = list.filter(p => S.brands.has(p.brand));

            switch (S.sort) {
                case 'price_asc': list.sort((a, b) => a.price - b.price); break;
                case 'price_desc': list.sort((a, b) => b.price - a.price); break;
                case 'name_az': list.sort((a, b) => a.name.localeCompare(b.name)); break;
                case 'stock_d': list.sort((a, b) => b.stock - a.stock); break;
            }
            return list;
        }

        /* ═══════════════════════════
           RENDER
        ═══════════════════════════ */
        function go() {
            S.page = 1; render();
        }

        function render() {
            const list = filtered();
            const total = list.length;
            const showing = Math.min(S.page * PPG, total);
            const slice = list.slice(0, showing);

            document.getElementById('itemCount').textContent = total + ' item' + (total !== 1 ? 's' : '');

            const grid = document.getElementById('pgrid');

            if (total === 0) {
                grid.innerHTML = `<div class="empty-st"><i class="fa-solid fa-box-open"></i><p>No products match your filters.</p></div>`;
            } else {
                grid.innerHTML = slice.map(cardHTML).join('');
            }

            // Pagination
            const pgWrap = document.getElementById('pgWrap');
            const pgInfo = document.getElementById('pgInfo');
            const btnMore = document.getElementById('btnMore');

            if (total > PPG) {
                pgWrap.style.display = 'flex';
                pgInfo.innerHTML = `Showing <strong>${showing}</strong> of <strong>${total}</strong> products`;
                btnMore.disabled = (showing >= total);
                btnMore.innerHTML = showing >= total
                    ? `<i class="fa-solid fa-check" style="margin-right:6px"></i>All Products Loaded`
                    : `<i class="fa-solid fa-chevron-down" style="margin-right:6px"></i>Show More (${total - showing} remaining)`;
            } else {
                pgWrap.style.display = 'none';
            }

            renderPills();
        }

        function loadMore() {
            S.page++;
            const prevCount = (S.page - 1) * PPG;
            render();
            // Scroll to first newly revealed card
            const cards = document.querySelectorAll('#pgrid .pcard');
            if (cards[prevCount]) cards[prevCount].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        window.loadMore = loadMore;

        /* ═══════════════════════════
           CARD HTML
        ═══════════════════════════ */
        function cardHTML(p) {
            const inStock = p.stock > 0;
            const lowStock = p.stock > 0 && p.stock <= 5;
            const fmt = v => 'Rs ' + parseFloat(v).toLocaleString('en-LK', { minimumFractionDigits: 2 });

            const badge = !inStock ? `<span class="p-oos">Out of Stock</span>`
                : lowStock ? `<span class="p-low">Only ${p.stock} left</span>` : '';

            const heart = p.loggedIn ? `
        <form method="post" action="index.php?page=wishlist" style="display:contents">
            <input type="hidden" name="product_id" value="${p.id}">
            <input type="hidden" name="redirect" value="index.php?page=products">
            <button type="submit" class="p-heart${p.wishlisted ? ' on' : ''}"
                title="${p.wishlisted ? 'Remove from wishlist' : 'Add to wishlist'}">
                <i class="fa-${p.wishlisted ? 'solid' : 'regular'} fa-heart"></i>
            </button>
        </form>` : '';

            const sizes = p.sizes.length
                ? `<div class="p-sizes">${p.sizes.map(s => `<span class="sz">${s}</span>`).join('')}</div>` : '';

            return `
    <article class="pcard">
        <div class="pc-img">
            <img src="${a(p.image)}" alt="${a(p.name)}" loading="lazy">
            <span class="p-brand">${h(p.brand)}</span>
            ${heart}
            ${badge}
        </div>
        <div class="pc-body">
            <p class="p-cat">${h(p.category)}</p>
            <h2 class="p-name">${h(p.name)}</h2>
            <p class="p-price">${fmt(p.price)}</p>
            ${sizes}
            <div class="p-actions">
                <a href="${a(p.detailUrl)}" class="btn-info" title="View details"><i class="fa-solid fa-circle-info"></i></a>
                <form method="post" action="${a(p.cartUrl)}" style="flex:1;display:flex">
                    <input type="hidden" name="product_id" value="${p.id}">
                    <button type="submit" class="btn-cart"${!inStock ? ' disabled' : ''}>
                        <i class="fa-solid fa-cart-plus"></i>${inStock ? 'Add to Cart' : 'Unavailable'}
                    </button>
                </form>
            </div>
        </div>
    </article>`;
        }

        /* ═══════════════════════════
           ACTIVE FILTER PILLS
        ═══════════════════════════ */
        function renderPills() {
            const wrap = document.getElementById('afWrap');
            let pills = '';

            if (S.search) pills += pill(`"${S.search}"`, `clearSearch()`);
            if (S.catId || S.catName) pills += pill(S.catName || 'Category', `clearCat()`);
            if (S.maxPrice < MXP) pills += pill(`Max Rs ${S.maxPrice.toLocaleString()}`, `clearPrice()`);
            if (S.inStock) pills += pill('In Stock', `clearIn()`);
            if (S.outStock) pills += pill('Out of Stock', `clearOut()`);
            S.brands.forEach(b => { pills += pill(`Brand: ${b}`, `removeBrand('${esc(b)}')`); });

            wrap.innerHTML = pills;
            wrap.style.display = pills ? 'flex' : 'none';
        }

        function pill(label, fn) {
            return `<span class="af-pill">${h(label)}<button onclick="${fn}" title="Remove">✕</button></span>`;
        }

        /* Clear helpers */
        window.clearSearch = function () { S.search = ''; document.getElementById('searchInput').value = ''; go(); };
        window.clearCat = function () { S.catId = null; S.catName = null; buildCatTree(); go(); };
        window.clearPrice = function () { S.maxPrice = MXP; document.getElementById('priceSlider').value = MXP; document.getElementById('priceVal').textContent = 'Rs ' + MXP.toLocaleString(); go(); };
        window.clearIn = function () { S.inStock = false; document.getElementById('chkIn').checked = false; go(); };
        window.clearOut = function () { S.outStock = false; document.getElementById('chkOut').checked = false; go(); };

        /* ═══════════════════════════
           EVENT LISTENERS
        ═══════════════════════════ */
        // Price slider — live, no Apply button
        document.getElementById('priceSlider').addEventListener('input', function () {
            S.maxPrice = parseInt(this.value);
            document.getElementById('priceVal').textContent = 'Rs ' + S.maxPrice.toLocaleString();
            S.page = 1; go();
        });

        document.getElementById('chkIn').addEventListener('change', function () { S.inStock = this.checked; S.page = 1; go(); });
        document.getElementById('chkOut').addEventListener('change', function () { S.outStock = this.checked; S.page = 1; go(); });

        document.getElementById('sortSel').addEventListener('change', function () { S.sort = this.value; S.page = 1; go(); });

        // Live search with debounce
        document.getElementById('searchInput').addEventListener('input', debounce(function () {
            S.search = this.value; S.page = 1; go();
        }, 300));

        function handleSearch(e) { e.preventDefault(); S.search = document.getElementById('searchInput').value; S.page = 1; go(); }
        window.handleSearch = handleSearch;

        /* ═══════════════════════════
           VIEW GRID
        ═══════════════════════════ */
        function setGrid(btn, cols) {
            document.querySelectorAll('.vbtn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('pgrid').style.gridTemplateColumns =
                { 1: '1fr', 2: 'repeat(2,1fr)', 3: 'repeat(3,1fr)', 4: 'repeat(4,1fr)' }[cols] || 'repeat(3,1fr)';
        }
        window.setGrid = setGrid;

        /* ═══════════════════════════
           UTILS
        ═══════════════════════════ */
        function ce(t) { return document.createElement(t); }
        function h(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
        function a(s) { return (s || '').replace(/"/g, '&quot;'); }
        function esc(s) { return (s || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'"); }
        function debounce(fn, ms) { let t; return function (...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), ms); }; }

        /* ═══════════════════════════
           INIT
        ═══════════════════════════ */
        buildCatTree();
        buildBrands();
        render();

    })();
</script>