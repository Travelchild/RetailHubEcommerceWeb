<div class="grid gap-8 lg:grid-cols-12 my-8 mx-12">
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <div class="lg:col-span-9 xl:col-span-9">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="inline-flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900"><i
                        class="fa-solid <?= !empty($user) ? 'fa-user-pen' : 'fa-user-plus' ?> text-brand-600"
                        aria-hidden="true"></i><?= htmlspecialchars($formTitle ?? 'User') ?></h1>
                <p class="text-sm text-slate-600">
                    <?= $user ? 'Update profile, role, and access.' : 'Create a new staff or customer account.' ?></p>
            </div>
            <a href="index.php?page=admin-users"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:border-brand-300 hover:bg-brand-50"><i
                    class="fa-solid fa-arrow-left" aria-hidden="true"></i>Back to users</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="mt-8 rounded-3xl border border-slate-100 bg-white p-6 shadow-soft sm:p-8">
            <?php if ($user): ?>
                <form method="post" action="index.php?page=admin-user-update" class="grid gap-5 sm:grid-cols-2">
                    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Full name *</label>
                        <input type="text" name="full_name" required value="<?= htmlspecialchars($user['full_name']) ?>"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Email *</label>
                        <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Role *</label>
                        <select name="role_id"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= (int) $r['id'] ?>" <?= (int) $user['role_id'] === (int) $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['role_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Status *</label>
                        <select name="status"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                            <option value="Active" <?= $user['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Inactive" <?= $user['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">New password</label>
                        <input type="password" name="new_password" placeholder="Leave blank to keep current password"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Contact</label>
                        <input type="text" name="contact_no" value="<?= htmlspecialchars($user['contact_no'] ?? '') ?>"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Payment preference</label>
                        <input type="text" name="payment_preference"
                            value="<?= htmlspecialchars($user['payment_preference'] ?? '') ?>"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Address</label>
                        <textarea name="address" rows="3"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-700 px-8 py-3 text-sm font-semibold text-white shadow-md hover:from-brand-700 hover:to-indigo-800"><i
                                class="fa-solid fa-floppy-disk" aria-hidden="true"></i>Save changes</button>
                    </div>
                </form>
            <?php else: ?>
                <form method="post" action="index.php?page=admin-user-store" class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Full name *</label>
                        <input type="text" name="full_name" required
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Email *</label>
                        <input type="email" name="email" required
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Password *</label>
                        <input type="password" name="password" required
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Role *</label>
                        <select name="role_id" required
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= (int) $r['id'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Status *</label>
                        <select name="status"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Contact</label>
                        <input type="text" name="contact_no"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Payment preference</label>
                        <input type="text" name="payment_preference"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Address</label>
                        <textarea name="address" rows="3"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-700 px-8 py-3 text-sm font-semibold text-white shadow-md hover:from-brand-700 hover:to-indigo-800"><i
                                class="fa-solid fa-user-plus" aria-hidden="true"></i>Create user</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>