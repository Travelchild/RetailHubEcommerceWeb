<div class="grid gap-8 lg:grid-cols-12 mb-6 mt-8 ml-12 mr-12">
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <div class="lg:col-span-9 xl:col-span-9">

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="inline-flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900">
                    <i class="fa-solid fa-truck-fast text-brand-600" aria-hidden="true"></i>Order management
                </h1>
                <p class="mt-1 text-sm text-slate-600">Update fulfillment status for every order.</p>
            </div>
            <span
                class="inline-flex w-fit items-center gap-2 rounded-full bg-slate-100 px-4 py-1.5 text-sm font-semibold text-slate-700">
                <i class="fa-solid fa-box text-xs opacity-70"></i><?= count($orders) ?> orders
            </span>
        </div>

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
                <input id="ord-search" type="text" placeholder="Search by order ID or customer name…"
                    oninput="filterOrders()"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-9 text-sm text-slate-800 shadow-sm outline-none placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition">
                <button id="ord-search-clear" onclick="clearOrdSearch()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 hidden text-slate-400 hover:text-slate-600 transition"
                    title="Clear">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <!-- Status filter -->
            <select id="ord-status" onchange="filterOrders()"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition">
                <option value="">All statuses</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <!-- Payment status filter -->
            <select id="ord-payment" onchange="filterOrders()"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition">
                <option value="">All payments</option>
                <option value="paid">Paid</option>
                <option value="unpaid">Unpaid</option>
                <option value="pending">Pending</option>
                <option value="refunded">Refunded</option>
            </select>

        </div>

        <!-- Result count -->
        <p id="ord-count" class="mt-3 text-xs text-slate-400"></p>

        <!-- ── Table ── -->
        <div class="mt-3 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Order</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Gateway</th>
                            <th class="px-4 py-3">Pay Status</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="ord-tbody">
                        <?php foreach ($orders as $o): ?>
                            <tr class="ord-row align-middle" data-id="<?= (int) $o['id'] ?>"
                                data-customer="<?= htmlspecialchars(strtolower($o['full_name'])) ?>"
                                data-status="<?= htmlspecialchars(strtolower($o['order_status'])) ?>"
                                data-payment="<?= htmlspecialchars(strtolower($o['payment_status'] ?? '')) ?>">

                                <td class="px-4 py-3 font-medium text-slate-900" data-col="id">
                                    #<?= (int) $o['id'] ?>
                                </td>
                                <td class="px-4 py-3 text-slate-600" data-col="customer">
                                    <?= htmlspecialchars($o['full_name']) ?>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-800">
                                    <?= formatCurrency($o['total_amount']) ?>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">
                                    <?= htmlspecialchars($o['payment_gateway'] ?? '—') ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php
                                    $ps = strtolower($o['payment_status'] ?? '');
                                    $psClass = match ($ps) {
                                        'paid' => 'bg-emerald-100 text-emerald-800',
                                        'refunded' => 'bg-purple-100 text-purple-800',
                                        'pending' => 'bg-amber-100 text-amber-800',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                    ?>
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold <?= $psClass ?>">
                                        <?= htmlspecialchars($o['payment_status'] ?? '—') ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <?php
                                    $os = strtolower($o['order_status']);
                                    $osClass = match ($os) {
                                        'delivered' => 'bg-emerald-100 text-emerald-800',
                                        'shipped' => 'bg-sky-100 text-sky-800',
                                        'processing' => 'bg-amber-100 text-amber-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                    ?>
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold <?= $osClass ?>">
                                        <?= htmlspecialchars($o['order_status']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500 text-xs">
                                    <?= date('d M Y', strtotime($o['created_at'])) ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <!-- Update status form -->
                                        <form method="post" action="index.php?page=admin-order-update"
                                            class="flex items-center gap-2">
                                            <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                                            <select name="status"
                                                class="rounded-lg border border-slate-200 px-2 py-1.5 text-xs outline-none focus:border-brand-500">
                                                <option value="Pending" <?= $o['order_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Processing" <?= $o['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                                <option value="Shipped" <?= $o['order_status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                                <option value="Delivered" <?= $o['order_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                                <option value="Cancelled" <?= $o['order_status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700"
                                                title="Update status">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                        <!-- Delete -->
                                        <form method="post" action="index.php?page=admin-order-delete"
                                            onsubmit="return confirm('Delete order #<?= (int) $o['id'] ?>?')">
                                            <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                                            <button type="submit"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div id="ord-empty" class="hidden flex-col items-center justify-center gap-3 py-16 text-slate-400">
                    <i class="fa-solid fa-box-open text-4xl"></i>
                    <p class="text-sm">No orders match your search.
                        <button onclick="clearOrdSearch()" class="text-brand-600 underline">Clear filters</button>
                    </p>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
    (function () {
        var searchInput = document.getElementById('ord-search');
        var clearBtn = document.getElementById('ord-search-clear');
        var statusSel = document.getElementById('ord-status');
        var paymentSel = document.getElementById('ord-payment');
        var countEl = document.getElementById('ord-count');
        var emptyEl = document.getElementById('ord-empty');
        var rows = document.querySelectorAll('.ord-row');
        var totalRows = rows.length;

        rows.forEach(function (row) {
            ['id', 'customer'].forEach(function (col) {
                var cell = row.querySelector('[data-col="' + col + '"]');
                if (cell) cell.dataset.original = cell.textContent.trim();
            });
        });

        function filterOrders() {
            var query = searchInput.value.toLowerCase().trim();
            var status = statusSel.value.toLowerCase();
            var payment = paymentSel.value.toLowerCase();
            var visible = 0;

            clearBtn.classList.toggle('hidden', query === '');

            rows.forEach(function (row) {
                var id = String(row.dataset.id);
                var customer = row.dataset.customer || '';
                var rowSt = row.dataset.status || '';
                var rowPay = row.dataset.payment || '';

                var textMatch = !query || id.includes(query) || customer.includes(query);
                var statusMatch = !status || rowSt.includes(status);
                var paymentMatch = !payment || rowPay.includes(payment);

                var show = textMatch && statusMatch && paymentMatch;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (query || status || payment) {
                countEl.textContent = visible + ' of ' + totalRows + ' order' + (totalRows !== 1 ? 's' : '') + ' shown';
            } else {
                countEl.textContent = '';
            }

            emptyEl.classList.toggle('hidden', visible > 0);
            emptyEl.classList.toggle('flex', visible === 0);

            highlightMatches(query);
        }

        function highlightMatches(query) {
            rows.forEach(function (row) {
                ['id', 'customer'].forEach(function (col) {
                    var cell = row.querySelector('[data-col="' + col + '"]');
                    if (!cell) return;
                    var original = cell.dataset.original || '';
                    if (!query) { cell.textContent = original; return; }
                    var escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    var re = new RegExp('(' + escaped + ')', 'gi');
                    cell.innerHTML = original.replace(re,
                        '<mark style="background:#fef08a;border-radius:2px;padding:0 1px;">$1</mark>');
                });
            });
        }

        function clearOrdSearch() {
            searchInput.value = '';
            statusSel.value = '';
            paymentSel.value = '';
            filterOrders();
            searchInput.focus();
        }

        window.filterOrders = filterOrders;
        window.clearOrdSearch = clearOrdSearch;

        filterOrders();
    })();
</script>