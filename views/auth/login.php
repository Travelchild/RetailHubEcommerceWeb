<div class="flex min-h-[60vh] items-center justify-center py-8">
    <div class="w-full max-w-md">
        <div class="rounded-3xl border border-slate-100 bg-white p-8 shadow-soft sm:p-10">
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-100 text-brand-600">
                    <i class="fa-solid fa-right-to-bracket text-2xl" aria-hidden="true"></i>
                </div>
                <h2 class="mt-5 text-2xl font-bold tracking-tight text-slate-900">Welcome back</h2>
                <p class="mt-2 text-sm text-slate-600">Sign in to continue shopping and track orders.</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" class="mt-8 space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" id="email" name="email" required
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" id="password" name="password" required
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-700 px-4 py-3.5 text-sm font-semibold text-white shadow-md transition hover:from-brand-700 hover:to-indigo-800"><i
                        class="fa-solid fa-lock" aria-hidden="true"></i>Sign in</button>
            </form>
            <p class="mt-6 text-center text-sm text-slate-600">
                New customer?
                <a href="index.php?page=register"
                    class="font-semibold text-brand-600 hover:text-brand-700 inline-flex items-center gap-1"><i
                        class="fa-solid fa-user-plus text-xs" aria-hidden="true"></i>Create an account</a>
            </p>
        </div>
    </div>
</div>