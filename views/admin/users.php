<div class="grid gap-8 lg:grid-cols-12 my-8 mx-12">
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <div class="lg:col-span-9 xl:col-span-9">

        <!-- Page header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="inline-flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900">
                    <i class="fa-solid fa-users text-brand-600" aria-hidden="true"></i>User management
                </h1>
                <p class="mt-1 text-sm text-slate-600">Full CRUD: create accounts, edit profiles, assign roles, remove
                    users.</p>
            </div>
            <?php if (currentUser()['role_name'] === 'Admin'): ?>
                <a href="index.php?page=admin-user-create"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-700 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-700 hover:to-indigo-800">
                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i>Add user
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
                <input id="user-search" type="text" placeholder="Search by name or email…" oninput="filterUsers()"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-9 text-sm text-slate-800 shadow-sm outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                <button id="user-search-clear" onclick="clearSearch()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 hidden text-slate-400 hover:text-slate-600 transition"
                    title="Clear">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <!-- Role filter -->
            <select id="user-role" onchange="filterUsers()"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                <option value="">All roles</option>
                <?php
                // Collect unique roles from $users for dynamic options
                $uniqueRoles = array_unique(array_column($users, 'role_name'));
                sort($uniqueRoles);
                foreach ($uniqueRoles as $role):
                    ?>
                    <option value="<?= htmlspecialchars(strtolower($role)) ?>"><?= htmlspecialchars($role) ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Status filter -->
            <select id="user-status" onchange="filterUsers()"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

        </div>

        <!-- Result count -->
        <p id="user-count" class="mt-3 text-xs text-slate-400"></p>

        <!-- ── Table ── -->
        <div class="mt-3 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Created</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="user-tbody">
                        <?php foreach ($users as $u): ?>
                            <tr class="user-row align-middle"
                                data-name="<?= htmlspecialchars(strtolower($u['full_name'])) ?>"
                                data-email="<?= htmlspecialchars(strtolower($u['email'])) ?>"
                                data-role="<?= htmlspecialchars(strtolower($u['role_name'])) ?>"
                                data-status="<?= strtolower($u['status']) ?>">

                                <td class="px-4 py-3 text-slate-500">#<?= (int) $u['id'] ?></td>

                                <td class="px-4 py-3 font-medium text-slate-900" data-col="name">
                                    <?= htmlspecialchars($u['full_name']) ?>
                                </td>

                                <td class="px-4 py-3 text-slate-600" data-col="email">
                                    <?= htmlspecialchars($u['email']) ?>
                                </td>

                                <td class="px-4 py-3">
                                    <?php
                                    $roleColors = [
                                        'admin' => 'bg-indigo-100 text-indigo-700',
                                        'manager' => 'bg-sky-100 text-sky-700',
                                        'staff' => 'bg-amber-100 text-amber-700',
                                    ];
                                    $roleLower = strtolower($u['role_name']);
                                    $roleClass = $roleColors[$roleLower] ?? 'bg-slate-100 text-slate-700';
                                    ?>
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold <?= $roleClass ?>">
                                        <?= htmlspecialchars($u['role_name']) ?>
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                        <?= $u['status'] === 'Active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' ?>">
                                        <?= htmlspecialchars($u['status']) ?>
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-slate-500 text-xs">
                                    <?= htmlspecialchars($u['created_at']) ?>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <?php if (currentUser()['role_name'] === 'Admin' && (int) $u['id'] !== (int) currentUser()['id']): ?>
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            <a href="index.php?page=admin-user-edit&id=<?= (int) $u['id'] ?>"
                                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-800 hover:border-brand-300 hover:bg-brand-50">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>Edit
                                            </a>
                                            <form method="post" action="index.php?page=admin-user-delete" class="inline"
                                                onsubmit="return confirm('Delete this user? Users with orders may only be deactivated.');">
                                                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>Delete
                                                </button>
                                            </form>
                                        </div>
                                    <?php elseif ((int) $u['id'] === (int) currentUser()['id']): ?>
                                        <span class="inline-flex items-center gap-1 text-xs text-slate-500">
                                            <i class="fa-solid fa-circle-user text-indigo-400" style="font-size:.75rem;"></i>
                                            You
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-500">View only</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Empty state (shown by JS) -->
                <div id="user-empty" class="hidden flex-col items-center justify-center gap-3 py-16 text-slate-400">
                    <i class="fa-solid fa-user-slash text-4xl"></i>
                    <p class="text-sm">No users match your search.
                        <button onclick="clearSearch()" class="text-indigo-500 underline">Clear filters</button>
                    </p>
                </div>
            </div>
        </div>

    </div><!-- /col -->
</div><!-- /grid -->

<script>
    (function () {
        var searchInput = document.getElementById('user-search');
        var clearBtn = document.getElementById('user-search-clear');
        var roleSel = document.getElementById('user-role');
        var statusSel = document.getElementById('user-status');
        var countEl = document.getElementById('user-count');
        var emptyEl = document.getElementById('user-empty');
        var rows = document.querySelectorAll('.user-row');
        var totalRows = rows.length;

        // Store original text for highlight restore
        rows.forEach(function (row) {
            ['name', 'email'].forEach(function (col) {
                var cell = row.querySelector('[data-col="' + col + '"]');
                if (cell) cell.dataset.original = cell.textContent.trim();
            });
        });

        function filterUsers() {
            var query = searchInput.value.toLowerCase().trim();
            var role = roleSel.value;
            var status = statusSel.value;
            var visible = 0;

            // Show / hide clear button
            clearBtn.classList.toggle('hidden', query === '');

            rows.forEach(function (row) {
                var name = row.dataset.name || '';
                var email = row.dataset.email || '';
                var rowRole = row.dataset.role;
                var rowStatus = row.dataset.status;

                var textMatch = !query || name.includes(query) || email.includes(query);
                var roleMatch = !role || rowRole === role;
                var statusMatch = !status || rowStatus === status;

                var show = textMatch && roleMatch && statusMatch;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            // Result count label
            if (query || role || status) {
                countEl.textContent = visible + ' of ' + totalRows + ' user' + (totalRows !== 1 ? 's' : '') + ' shown';
            } else {
                countEl.textContent = '';
            }

            // Empty state
            emptyEl.classList.toggle('hidden', visible > 0);
            emptyEl.classList.toggle('flex', visible === 0);

            // Highlight matching text in Name & Email columns
            highlightMatches(query);
        }

        function highlightMatches(query) {
            rows.forEach(function (row) {
                ['name', 'email'].forEach(function (col) {
                    var cell = row.querySelector('[data-col="' + col + '"]');
                    if (!cell) return;
                    var original = cell.dataset.original || '';

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
            roleSel.value = '';
            statusSel.value = '';
            filterUsers();
            searchInput.focus();
        }

        // Expose globally for inline onclick
        window.filterUsers = filterUsers;
        window.clearSearch = clearSearch;

        // Init
        filterUsers();
    })();
</script>