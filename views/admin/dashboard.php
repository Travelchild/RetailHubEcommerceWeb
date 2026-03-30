<div class="grid gap-8 lg:grid-cols-12 my-8 mx-12">
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <div class="lg:col-span-9 xl:col-span-9">
        <div class="mb-2 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="inline-flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900"><i class="fa-solid fa-gauge-high text-brand-600" aria-hidden="true"></i>Dashboard</h1>
                <p class="text-sm text-slate-600">Overview of users, revenue, catalog, and support load.</p>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><i class="fa-solid fa-id-badge text-slate-400" aria-hidden="true"></i><?= htmlspecialchars($adminRole ?? '') ?></span>
        </div>

        <?php if (!empty($flash)): ?>
            <div class="mt-4 rounded-xl border px-4 py-3 text-sm <?= flashBoxClass($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-brand-700 p-5 text-white shadow-soft">
                <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-white/70"><i class="fa-solid fa-users" aria-hidden="true"></i>Users</p>
                <p class="mt-2 text-3xl font-bold"><?= count($users) ?></p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-600 p-5 text-white shadow-soft">
                <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-white/70"><i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>Orders</p>
                <p class="mt-2 text-3xl font-bold"><?= count($orders) ?></p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-violet-600 to-purple-800 p-5 text-white shadow-soft sm:col-span-2 xl:col-span-1">
                <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-white/70"><i class="fa-solid fa-ticket" aria-hidden="true"></i>Tickets</p>
                <p class="mt-2 text-3xl font-bold"><?= count($tickets) ?></p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 text-white shadow-soft">
                <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-white/70"><i class="fa-solid fa-sack-dollar" aria-hidden="true"></i>Revenue</p>
                <p class="mt-2 text-2xl font-bold"><?= formatCurrency($totalRevenue) ?></p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-5 text-white shadow-soft">
                <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-white/70"><i class="fa-solid fa-box-open" aria-hidden="true"></i>Products</p>
                <p class="mt-2 text-3xl font-bold"><?= count($products) ?></p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft sm:col-span-2 xl:col-span-1">
                <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500"><i class="fa-solid fa-bolt text-brand-500" aria-hidden="true"></i>Quick action</p>
                <a href="index.php?page=admin-products" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"><i class="fa-solid fa-box-open" aria-hidden="true"></i>Open product manager</a>
            </div>
        </div>

        <div class="mt-10 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <a href="index.php?page=admin-products" class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-soft transition hover:border-brand-200 hover:shadow-lg">
                <h3 class="inline-flex items-center gap-2 font-semibold text-slate-900 group-hover:text-brand-700"><i class="fa-solid fa-box-open text-brand-600" aria-hidden="true"></i>Manage products</h3>
                <p class="mt-2 text-sm text-slate-600">CRUD, stock, images, visibility.</p>
            </a>
            <a href="index.php?page=admin-users" class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-soft transition hover:border-brand-200 hover:shadow-lg">
                <h3 class="inline-flex items-center gap-2 font-semibold text-slate-900 group-hover:text-brand-700"><i class="fa-solid fa-users text-brand-600" aria-hidden="true"></i>User management</h3>
                <p class="mt-2 text-sm text-slate-600">Roles and account status.</p>
            </a>
            <a href="index.php?page=admin-orders" class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-soft transition hover:border-brand-200 hover:shadow-lg">
                <h3 class="inline-flex items-center gap-2 font-semibold text-slate-900 group-hover:text-brand-700"><i class="fa-solid fa-truck-fast text-brand-600" aria-hidden="true"></i>Order management</h3>
                <p class="mt-2 text-sm text-slate-600">Fulfillment pipeline.</p>
            </a>
            <a href="index.php?page=admin-helpdesk" class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-soft transition hover:border-brand-200 hover:shadow-lg">
                <h3 class="inline-flex items-center gap-2 font-semibold text-slate-900 group-hover:text-brand-700"><i class="fa-solid fa-headset text-brand-600" aria-hidden="true"></i>Help desk</h3>
                <p class="mt-2 text-sm text-slate-600">Tickets and replies.</p>
            </a>
        </div>
    </div>
</div>
