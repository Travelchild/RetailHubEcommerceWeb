<div class="flex min-h-[60vh] items-center justify-center py-8">
    <div class="w-full max-w-2xl">
        <div class="rounded-3xl border border-slate-100 bg-white p-8 shadow-soft sm:p-10">
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-100 text-brand-600">
                    <i class="fa-solid fa-user-plus text-2xl" aria-hidden="true"></i>
                </div>
                <h2 class="mt-5 text-2xl font-bold tracking-tight text-slate-900">Create your account</h2>
                <p class="mt-2 text-sm text-slate-600">Join RetailHub for a faster checkout and order history.</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <form method="post" class="mt-8 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-slate-700">Full name</label>
                    <input type="text" name="full_name" required
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" required
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" required
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-slate-700">Contact</label>
                    <input type="text" name="contact_no"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Address</label>
                    <textarea name="address" rows="3"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Payment preference</label>
                    <input type="text" name="payment_preference" placeholder="e.g. Mock Card"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-700 px-4 py-3.5 text-sm font-semibold text-white shadow-md hover:from-brand-700 hover:to-indigo-800 sm:w-auto sm:px-8"><i
                            class="fa-solid fa-user-check" aria-hidden="true"></i>Create account</button>
                </div>
            </form>
            <p class="mt-6 text-center text-sm text-slate-600">
                Already registered?
                <a href="index.php?page=login"
                    class="inline-flex items-center gap-1 font-semibold text-brand-600 hover:text-brand-700"><i
                        class="fa-solid fa-right-to-bracket text-xs" aria-hidden="true"></i>Sign in</a>
            </p>
        </div>
    </div>
</div>