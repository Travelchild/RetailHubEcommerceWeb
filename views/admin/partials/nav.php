<aside class="lg:col-span-3 xl:col-span-3">

    <div class="sticky top-24 rounded-2xl border border-slate-100 bg-white p-4 shadow-soft">

        <p class="inline-flex items-center gap-2 px-2 text-xs font-bold uppercase tracking-wider text-slate-400"><i
                class="fa-solid fa-sliders text-[0.65rem]"
                aria-hidden="true"></i><?= !empty($isAdminStaff) ? 'Control center' : 'Support' ?></p>

        <nav class="mt-3 space-y-1">

            <?php if (!empty($isAdminStaff)): ?>

                <a href="index.php?page=admin"
                    class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition <?= ($section ?? '') === 'dashboard' ? 'bg-gradient-to-r from-brand-600 to-indigo-700 text-white shadow-md' : 'text-slate-700 hover:bg-slate-50' ?>">

                    <i class="fa-solid fa-gauge-high w-5 text-center text-xs opacity-90" aria-hidden="true"></i> Dashboard

                </a>

                <a href="index.php?page=admin-products"
                    class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition <?= ($section ?? '') === 'products' ? 'bg-gradient-to-r from-brand-600 to-indigo-700 text-white shadow-md' : 'text-slate-700 hover:bg-slate-50' ?>">

                    <i class="fa-solid fa-box-open w-5 text-center text-xs opacity-90" aria-hidden="true"></i> Products

                </a>

                <a href="index.php?page=admin-users"
                    class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition <?= ($section ?? '') === 'users' ? 'bg-gradient-to-r from-brand-600 to-indigo-700 text-white shadow-md' : 'text-slate-700 hover:bg-slate-50' ?>">

                    <i class="fa-solid fa-users w-5 text-center text-xs opacity-90" aria-hidden="true"></i> Users

                </a>

                <a href="index.php?page=admin-orders"
                    class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition <?= ($section ?? '') === 'orders' ? 'bg-gradient-to-r from-brand-600 to-indigo-700 text-white shadow-md' : 'text-slate-700 hover:bg-slate-50' ?>">

                    <i class="fa-solid fa-truck-fast w-5 text-center text-xs opacity-90" aria-hidden="true"></i> Orders

                </a>

                <a href="index.php?page=admin-promotions"
                    class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition <?= ($section ?? '') === 'promotions' ? 'bg-gradient-to-r from-brand-600 to-indigo-700 text-white shadow-md' : 'text-slate-700 hover:bg-slate-50' ?>">

                    <i class="fa-solid fa-fire w-5 text-center text-xs opacity-90" aria-hidden="true"></i> Promotions

                </a>

                <a href="index.php?page=admin-categories"
                    class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition <?= ($section ?? '') === 'categories' ? 'bg-gradient-to-r from-brand-600 to-indigo-700 text-white shadow-md' : 'text-slate-700 hover:bg-slate-50' ?>">

                    <i class="fa-solid fa-tags w-5 text-center text-xs opacity-90" aria-hidden="true"></i> Categories

                </a>

                <a href="index.php?page=admin-reports"
                    class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition <?= ($section ?? '') === 'reports' ? 'bg-gradient-to-r from-brand-600 to-indigo-700 text-white shadow-md' : 'text-slate-700 hover:bg-slate-50' ?>">

                    <i class="fa-solid fa-chart-bar w-5 text-center text-xs opacity-90" aria-hidden="true"></i> Reports

                </a>

            <?php endif; ?>

            <a href="index.php?page=admin-helpdesk"
                class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition <?= ($section ?? '') === 'helpdesk' ? 'bg-gradient-to-r from-brand-600 to-indigo-700 text-white shadow-md' : 'text-slate-700 hover:bg-slate-50' ?>">

                <i class="fa-solid fa-ticket w-5 text-center text-xs opacity-90" aria-hidden="true"></i> Support tickets

            </a>

        </nav>

        <a href="index.php?page=products"
            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:border-brand-300 hover:bg-brand-50"><i
                class="fa-solid fa-arrow-left" aria-hidden="true"></i>Back to store</a>

    </div>

</aside>