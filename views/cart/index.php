<div class="mb-8 mt-8 ml-12 mr-12 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <h1 class="inline-flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900"><i
            class="fa-solid fa-cart-shopping text-brand-600" aria-hidden="true"></i>Your cart</h1>
    <a href="index.php?page=products"
        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm hover:border-brand-300 hover:bg-brand-50"><i
            class="fa-solid fa-arrow-left" aria-hidden="true"></i>Continue shopping</a>
</div>

<?php if (empty($items)): ?>
    <div class="rounded-2xl border border-slate-100 bg-white py-16 text-center shadow-soft">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
            <i class="fa-solid fa-cart-arrow-down text-2xl" aria-hidden="true"></i>
        </div>
        <p class="mt-4 text-lg font-semibold text-slate-900">Your cart is empty</p>
        <p class="mt-2 text-sm text-slate-600">Add items from the shop to see them here.</p>
        <a href="index.php?page=products"
            class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-700"><i
                class="fa-solid fa-bag-shopping" aria-hidden="true"></i>Browse products</a>
    </div>
<?php else: ?>
    <div class="ml-12 mr-12 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3">Price</th>
                        <th class="px-5 py-3">Qty</th>
                        <th class="px-5 py-3">Subtotal</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($items as $item): ?>
                        <tr class="align-middle">
                            <td class="px-5 py-4 font-medium text-slate-900"><?= htmlspecialchars($item['name']) ?></td>
                            <td class="px-5 py-4 text-slate-600"><?= formatCurrency($item['price']) ?></td>
                            <td class="px-5 py-4">
                                <form method="post" action="index.php?page=cart-update"
                                    class="flex flex-wrap items-center gap-2">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <input type="number" name="qty" min="0" value="<?= (int) $item['qty'] ?>"
                                        class="w-20 rounded-lg border border-slate-200 px-2 py-2 text-center outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                    <button type="submit"
                                        class="inline-flex items-center justify-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"><i
                                            class="fa-solid fa-rotate" aria-hidden="true"></i>Update</button>
                                </form>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-900"><?= formatCurrency($item['subtotal']) ?></td>
                            <td class="px-5 py-4 text-xs text-slate-500">Set qty 0 to remove</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div
        class="ml-12 mr-12 mt-6 flex flex-col gap-4 rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-50/80 to-white px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xl font-bold text-slate-900">Total <span class="text-brand-600"><?= formatCurrency($total) ?></span>
        </p>
        <a href="index.php?page=checkout"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-700 px-8 py-3.5 text-sm font-semibold text-white shadow-md hover:from-brand-700 hover:to-indigo-800"><i
                class="fa-solid fa-truck-fast" aria-hidden="true"></i>Proceed to checkout</a>
    </div>
<?php endif; ?>