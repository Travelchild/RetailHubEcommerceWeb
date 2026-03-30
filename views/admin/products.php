<div class="grid gap-8 lg:grid-cols-12 my-8 mx-12">
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <div class="lg:col-span-9 xl:col-span-9">

        <!-- Page header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="inline-flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900">
                    <i class="fa-solid fa-box-open text-brand-600" aria-hidden="true"></i>Products
                </h1>
                <p class="text-sm text-slate-600">Manage catalog, pricing, stock, and imagery.</p>
            </div>
            <?php if (currentUser()['role_name'] === 'Admin'): ?>
                <a href="index.php?page=admin-product-create"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-700 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-700 hover:to-indigo-800">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>Add product
                </a>
            <?php endif; ?>
        </div>

        <!-- Flash -->
        <?php if (!empty($flash)): ?>
            <div class="mt-6 rounded-xl border px-4 py-3 text-sm <?= flashBoxClass($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- ── Search & Filter bar ── -->
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">

            <!-- Text search -->
            <div class="relative flex-1">
                <i
                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                <input id="prod-search" type="text" placeholder="Search by name, SKU, or category…"
                    oninput="filterProducts()"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-800 shadow-sm outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                <!-- clear button -->
                <button id="prod-search-clear" onclick="clearSearch()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 hidden text-slate-400 hover:text-slate-600 transition"
                    title="Clear search">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <!-- Status filter -->
            <select id="prod-status" onchange="filterProducts()"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <!-- Stock filter -->
            <select id="prod-stock" onchange="filterProducts()"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                <option value="">All stock</option>
                <option value="instock">In stock (&gt; 0)</option>
                <option value="outofstock">Out of stock</option>
            </select>

        </div>

        <!-- Result count -->
        <p id="prod-count" class="mt-3 text-xs text-slate-400"></p>

        <!-- ── Table ── -->
        <div class="mt-3 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm" id="prod-table">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Image</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">SKU</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Price</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="prod-tbody">
                        <?php foreach ($products as $p): ?>
                            <tr class="prod-row align-middle" data-name="<?= htmlspecialchars(strtolower($p['name'])) ?>"
                                data-sku="<?= htmlspecialchars(strtolower($p['sku'])) ?>"
                                data-category="<?= htmlspecialchars(strtolower($p['category_name'] ?? '')) ?>"
                                data-status="<?= (int) $p['is_active'] === 1 ? 'active' : 'inactive' ?>"
                                data-stock="<?= (int) $p['stock_qty'] ?>">

                                <td class="px-4 py-3 text-slate-500">#<?= (int) $p['id'] ?></td>

                                <td class="px-4 py-3">
                                    <img src="<?= htmlspecialchars(assetImageUrl($p['image_url'] ?? null)) ?>" alt=""
                                        class="h-11 w-14 rounded-lg object-cover ring-1 ring-slate-100">
                                </td>

                                <td class="px-4 py-3 font-medium text-slate-900">
                                    <?= htmlspecialchars($p['name']) ?>
                                </td>

                                <td class="px-4 py-3 text-slate-600">
                                    <?= htmlspecialchars($p['sku']) ?>
                                </td>

                                <td class="px-4 py-3 text-slate-600">
                                    <?= htmlspecialchars($p['category_name'] ?? 'N/A') ?>
                                </td>

                                <td class="px-4 py-3 font-medium text-slate-900">
                                    <?= formatCurrency($p['price']) ?>
                                </td>

                                <td class="px-4 py-3">
                                    <?php if ((int) $p['stock_qty'] === 0): ?>
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-600">
                                            <i class="fa-solid fa-circle-xmark"
                                                style="font-size:.6rem;"></i><?= (int) $p['stock_qty'] ?>
                                        </span>
                                    <?php elseif ((int) $p['stock_qty'] <= 5): ?>
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-600">
                                            <i class="fa-solid fa-triangle-exclamation"
                                                style="font-size:.6rem;"></i><?= (int) $p['stock_qty'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-700"><?= (int) $p['stock_qty'] ?></span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-4 py-3">
                                    <?php if ((int) $p['is_active'] === 1): ?>
                                        <span
                                            class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">Active</span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">Inactive</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-4 py-3">
                                    <?php if (currentUser()['role_name'] === 'Admin'): ?>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="index.php?page=admin-product-edit&id=<?= (int) $p['id'] ?>"
                                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-800 hover:border-brand-300 hover:bg-brand-50">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>Edit
                                            </a>
                                            <form method="post" action="index.php?page=admin-product-delete"
                                                onsubmit="return confirm('Delete this product?');">
                                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>Delete
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-500">View only</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Empty-state shown by JS when nothing matches -->
                <div id="prod-empty" class="hidden flex-col items-center justify-center gap-3 py-16 text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-4xl"></i>
                    <p class="text-sm">No products match your search. <button onclick="clearSearch()"
                            class="text-indigo-500 underline">Clear filters</button></p>
                </div>
            </div>
        </div>

    </div><!-- /col -->
</div><!-- /grid -->

<script>
    (function () {
        var searchInput = document.getElementById('prod-search');
        var clearBtn = document.getElementById('prod-search-clear');
        var statusSel = document.getElementById('prod-status');
        var stockSel = document.getElementById('prod-stock');
        var countEl = document.getElementById('prod-count');
        var emptyEl = document.getElementById('prod-empty');
        var rows = document.querySelectorAll('.prod-row');
        var totalRows = rows.length;

        function filterProducts() {
            var query = searchInput.value.toLowerCase().trim();
            var status = statusSel.value;   // '' | 'active' | 'inactive'
            var stock = stockSel.value;    // '' | 'instock' | 'outofstock'
            var visible = 0;

            // Show / hide clear button
            clearBtn.classList.toggle('hidden', query === '');

            rows.forEach(function (row) {
                var name = row.dataset.name || '';
                var sku = row.dataset.sku || '';
                var category = row.dataset.category || '';
                var rowStatus = row.dataset.status;          // 'active' | 'inactive'
                var rowStock = parseInt(row.dataset.stock, 10);

                // Text match
                var textMatch = !query ||
                    name.includes(query) ||
                    sku.includes(query) ||
                    category.includes(query);

                // Status match
                var statusMatch = !status || rowStatus === status;

                // Stock match
                var stockMatch = !stock ||
                    (stock === 'instock' && rowStock > 0) ||
                    (stock === 'outofstock' && rowStock <= 0);

                var show = textMatch && statusMatch && stockMatch;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            // Update result count label
            if (query || status || stock) {
                countEl.textContent = visible + ' of ' + totalRows + ' product' + (totalRows !== 1 ? 's' : '') + ' shown';
            } else {
                countEl.textContent = '';
            }

            // Empty state
            emptyEl.classList.toggle('hidden', visible > 0);
            emptyEl.classList.toggle('flex', visible === 0);

            // Highlight matching text in Name / SKU / Category cells
            highlightMatches(query);
        }

        function highlightMatches(query) {
            rows.forEach(function (row) {
                // Columns: name (index 2), sku (3), category (4)
                [2, 3, 4].forEach(function (colIdx) {
                    var cell = row.querySelectorAll('td')[colIdx];
                    if (!cell) return;

                    // Restore original text first
                    if (cell.dataset.original === undefined) {
                        cell.dataset.original = cell.textContent;
                    }
                    var original = cell.dataset.original;

                    if (!query) {
                        cell.textContent = original;
                        return;
                    }

                    var escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    var re = new RegExp('(' + escaped + ')', 'gi');
                    cell.innerHTML = original.replace(re,
                        '<mark style="background:#fef08a;border-radius:2px;padding:0 1px;">$1</mark>');
                });
            });
        }

        function clearSearch() {
            searchInput.value = '';
            statusSel.value = '';
            stockSel.value = '';
            filterProducts();
            searchInput.focus();
        }

        window.filterProducts = filterProducts;
        window.clearSearch = clearSearch;

        filterProducts();
    })();
</script>