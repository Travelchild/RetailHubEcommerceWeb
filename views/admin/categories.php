<?php
$section = 'categories';
$db = Database::connection();

function catRedirect(string $msg, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $msg, 'type' => $type];
    echo '<script>window.location.href = "index.php?page=admin-categories";</script>';
    exit;
}

// ── handle POST actions ───────────────────────────
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'add') {
        $stmt = $db->prepare("
            INSERT INTO categories (name, parent_id, sort_order, is_active)
            VALUES (:name, :parent_id, :sort_order, :is_active)
        ");
        $stmt->execute([
            ':name' => trim($_POST['name']),
            ':parent_id' => !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null,
            ':sort_order' => (int) ($_POST['sort_order'] ?? 0),
            ':is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        catRedirect('Category added successfully.');
    }

    if ($action === 'edit') {
        $id = (int) $_POST['id'];
        $stmt = $db->prepare("
            UPDATE categories SET
                name       = :name,
                parent_id  = :parent_id,
                sort_order = :sort_order,
                is_active  = :is_active
            WHERE id = :id
        ");
        $stmt->execute([
            ':name' => trim($_POST['name']),
            ':parent_id' => !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null,
            ':sort_order' => (int) ($_POST['sort_order'] ?? 0),
            ':is_active' => isset($_POST['is_active']) ? 1 : 0,
            ':id' => $id,
        ]);
        catRedirect('Category updated successfully.');
    }

    if ($action === 'toggle') {
        $id = (int) $_POST['id'];
        $db->prepare("UPDATE categories SET is_active = NOT is_active WHERE id = :id")
            ->execute([':id' => $id]);
        catRedirect('Category status updated.');
    }

    if ($action === 'delete') {
        $id = (int) $_POST['id'];
        $childCount = $db->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = :id");
        $childCount->execute([':id' => $id]);
        if ($childCount->fetchColumn() > 0) {
            catRedirect('Cannot delete: this category has sub-categories. Remove them first.', 'danger');
        }
        $db->prepare("DELETE FROM categories WHERE id = :id")->execute([':id' => $id]);
        catRedirect('Category deleted.', 'danger');
    }
}

$categories = $db->query("
    SELECT c.id, c.name, c.parent_id, c.sort_order, c.is_active, c.created_at,
           p.name AS parent_name,
           (SELECT COUNT(*) FROM categories sub WHERE sub.parent_id = c.id) AS child_count
    FROM categories c
    LEFT JOIN categories p ON p.id = c.parent_id
    ORDER BY c.sort_order ASC, c.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

$parentOptions = $db->query("
    SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$editCat = null;
if (isset($_GET['edit_id'])) {
    $stmt = $db->prepare("SELECT id, name, parent_id, sort_order, is_active, created_at FROM categories WHERE id = :id");
    $stmt->execute([':id' => (int) $_GET['edit_id']]);
    $editCat = $stmt->fetch(PDO::FETCH_ASSOC);
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<style>
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 42px;
        height: 24px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background: #cbd5e1;
        border-radius: 999px;
        transition: .3s;
    }

    .toggle-slider::before {
        content: '';
        position: absolute;
        width: 18px;
        height: 18px;
        left: 3px;
        bottom: 3px;
        background: #fff;
        border-radius: 50%;
        transition: .3s;
    }

    .toggle-switch input:checked+.toggle-slider {
        background: #22c55e;
    }

    .toggle-switch input:checked+.toggle-slider::before {
        transform: translateX(18px);
    }

    .text-indigo-500 {
        color: rgb(230 126 0 / var(--tw-text-opacity, 1));
    }

    .cat-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .15rem .6rem;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 600;
    }

    .badge-root {
        background: #f1f5f9;
        color: #64748b;
    }

    .badge-sub {
        background: #ede9fe;
        color: #6d28d9;
    }

    .badge-children {
        background: #ecfdf5;
        color: #059669;
    }

    .cat-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .45);
        z-index: 9000;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .cat-modal-overlay.open {
        display: flex;
    }

    .cat-modal {
        background: #fff;
        border-radius: 1.25rem;
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
    }

    .cat-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .cat-modal-header h2 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .cat-modal-close {
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        color: #64748b;
        padding: .25rem .5rem;
        border-radius: .5rem;
    }

    .cat-modal-close:hover {
        background: #f1f5f9;
    }

    .cat-modal-body {
        padding: 1.5rem;
        display: grid;
        gap: 1rem;
    }

    .cat-modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: .75rem;
    }

    .form-row {
        display: grid;
        gap: 1rem;
        grid-template-columns: 1fr 1fr;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: .4rem;
    }

    .form-group label {
        font-size: .8rem;
        font-weight: 600;
        color: #374151;
    }

    .form-group input,
    .form-group select {
        padding: .5rem .75rem;
        border: 1px solid #d1d5db;
        border-radius: .6rem;
        font-size: .875rem;
        outline: none;
        transition: .2s;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .15);
    }

    .form-group .hint {
        font-size: .7rem;
        color: #94a3b8;
    }

    .form-section-title {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #94a3b8;
        padding: .25rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .btn-submit {
        background: #4f46e5;
        color: #fff;
        padding: .55rem 1.3rem;
        border: none;
        border-radius: .75rem;
        font-weight: 600;
        cursor: pointer;
        font-size: .875rem;
    }

    .btn-submit:hover {
        background: #4338ca;
    }

    .btn-cancel-modal {
        background: #f1f5f9;
        color: #374151;
        padding: .55rem 1.1rem;
        border: none;
        border-radius: .75rem;
        font-weight: 600;
        cursor: pointer;
        font-size: .875rem;
    }

    .btn-cancel-modal:hover {
        background: #e2e8f0;
    }

    @media(max-width:640px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>


<div class="grid gap-8 lg:grid-cols-12 my-8 mx-12">

    <?php include __DIR__ . '/partials/nav.php'; ?>

    <div class="lg:col-span-9 xl:col-span-9">

        <!-- Page header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="inline-flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900">
                    <i class="fa-solid fa-layer-group text-indigo-500" aria-hidden="true"></i>Manage Categories
                </h1>
                <p class="mt-1 text-sm text-slate-600">Create and organise product categories and sub-categories.</p>
            </div>
            <button onclick="openAddModal()"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-700 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-700 hover:to-indigo-800">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>Add Category
            </button>
        </div>

        <!-- Flash message -->
        <?php if (!empty($flash)): ?>
            <div class="mt-6 rounded-xl border px-4 py-3 text-sm <?= flashBoxClass($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Stats bar -->
        <?php
        $totalCats = count($categories);
        $activeCats = count(array_filter($categories, fn($c) => $c['is_active']));
        $rootCats = count(array_filter($categories, fn($c) => !$c['parent_id']));
        $subCats = $totalCats - $rootCats;
        ?>
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <?php foreach ([
                ['Total', $totalCats, 'fa-layer-group', 'bg-indigo-50 text-indigo-600'],
                ['Active', $activeCats, 'fa-circle-check', 'bg-green-50  text-green-600'],
                ['Top-Level', $rootCats, 'fa-folder', 'bg-sky-50    text-sky-600'],
                ['Sub', $subCats, 'fa-folder-open', 'bg-violet-50 text-violet-600'],
            ] as [$label, $count, $icon, $cls]): ?>
                <div class="rounded-xl border border-slate-100 bg-white px-4 py-3 shadow-soft">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg <?= $cls ?>">
                            <i class="fa-solid <?= $icon ?> text-sm"></i>
                        </span>
                        <div>
                            <p class="text-xs text-slate-500"><?= $label ?></p>
                            <p class="text-lg font-bold text-slate-900"><?= $count ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Table -->
        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
            <div class="overflow-x-auto">
                <?php if (empty($categories)): ?>
                    <div class="flex flex-col items-center justify-center gap-3 py-16 text-slate-400">
                        <i class="fa-solid fa-folder-open text-4xl"></i>
                        <p class="text-sm">No categories yet. Click <strong>Add Category</strong> to create your first one.
                        </p>
                    </div>
                <?php else: ?>
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Parent</th>
                                <th class="px-4 py-3">Children</th>
                                <th class="px-4 py-3">Order</th>
                                <th class="px-4 py-3">Created</th>
                                <th class="px-4 py-3">Active</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($categories as $cat): ?>
                                <tr class="align-middle <?= $cat['parent_id'] ? 'bg-slate-50/40' : '' ?>">

                                    <td class="px-4 py-3 text-xs text-slate-400">#<?= (int) $cat['id'] ?></td>

                                    <!-- Name -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <?php if ($cat['parent_id']): ?>
                                                <i class="fa-solid fa-corner-down-right text-slate-300 ml-2"
                                                    style="font-size:.7rem;"></i>
                                            <?php else: ?>
                                                <i class="fa-solid fa-folder text-indigo-400" style="font-size:.85rem;"></i>
                                            <?php endif; ?>
                                            <span
                                                class="font-semibold text-slate-900"><?= htmlspecialchars($cat['name']) ?></span>
                                        </div>
                                    </td>

                                    <!-- Parent -->
                                    <td class="px-4 py-3">
                                        <?php if ($cat['parent_name']): ?>
                                            <span class="cat-badge badge-sub"><?= htmlspecialchars($cat['parent_name']) ?></span>
                                        <?php else: ?>
                                            <span class="cat-badge badge-root">Root</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Children -->
                                    <td class="px-4 py-3">
                                        <?php if ($cat['child_count'] > 0): ?>
                                            <span class="cat-badge badge-children"><?= (int) $cat['child_count'] ?> sub</span>
                                        <?php else: ?>
                                            <span class="text-slate-300">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Sort order -->
                                    <td class="px-4 py-3 text-center text-slate-600"><?= (int) $cat['sort_order'] ?></td>

                                    <!-- Created -->
                                    <td class="px-4 py-3 text-xs text-slate-400">
                                        <?= !empty($cat['created_at']) ? date('M j, Y', strtotime($cat['created_at'])) : '—' ?>
                                    </td>

                                    <!-- Toggle active -->
                                    <td class="px-4 py-3">
                                        <form method="POST" action="index.php?page=admin-categories" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                            <label class="toggle-switch" title="Toggle active">
                                                <input type="checkbox" <?= $cat['is_active'] ? 'checked' : '' ?>
                                                    onchange="this.form.submit()">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </form>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            <a href="#" onclick="openEditModal(<?= htmlspecialchars(json_encode([
                                                'id' => $cat['id'],
                                                'name' => $cat['name'],
                                                'parent_id' => $cat['parent_id'],
                                                'sort_order' => $cat['sort_order'],
                                                'is_active' => $cat['is_active'],
                                            ])) ?>); return false;"
                                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-800 hover:border-brand-300 hover:bg-brand-50">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>Edit
                                            </a>
                                            <form method="POST" action="index.php?page=admin-categories" class="inline"
                                                onsubmit="return confirm('Delete this category? This cannot be undone.');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /col -->
</div><!-- /grid -->



<div class="cat-modal-overlay" id="addModal">
    <div class="cat-modal">
        <div class="cat-modal-header">
            <h2><i class="fa-solid fa-plus-circle text-indigo-500"></i> Add Category</h2>
            <button class="cat-modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST" action="index.php?page=admin-categories">
            <input type="hidden" name="action" value="add">
            <div class="cat-modal-body" id="addModalBody"></div>
            <div class="cat-modal-footer">
                <button type="button" class="btn-cancel-modal" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-submit"><i class="fa-solid fa-plus"></i> Add Category</button>
            </div>
        </form>
    </div>
</div>


<div class="cat-modal-overlay" id="editModal">
    <div class="cat-modal">
        <div class="cat-modal-header">
            <h2><i class="fa-solid fa-pen-to-square text-blue-500"></i> Edit Category</h2>
            <button class="cat-modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST" action="index.php?page=admin-categories" id="editForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="cat-modal-body" id="editModalBody"></div>
            <div class="cat-modal-footer">
                <button type="button" class="btn-cancel-modal" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    const CAT_PARENTS = <?= json_encode($parentOptions) ?>;

    function catFormHTML(c) {
        c = c || {};

        var parentOpts = '<option value="">— None (top-level) —</option>';
        CAT_PARENTS.forEach(function (p) {
            if (c.id && parseInt(p.id) === parseInt(c.id)) return;
            var sel = (c.parent_id != null && parseInt(c.parent_id) === parseInt(p.id)) ? ' selected' : '';
            parentOpts += '<option value="' + p.id + '"' + sel + '>' + escH(p.name) + '</option>';
        });

        var isActiveChecked = (c.is_active == 1 || c.is_active === undefined) ? 'checked' : '';

        return `
    <div class="form-section-title">Category Details</div>

    <div class="form-group">
        <label>Category Name *</label>
        <input name="name" required value="${escH(c.name)}" placeholder="e.g. Smartphones">
    </div>

    <div class="form-section-title">Structure &amp; Settings</div>

    <div class="form-row">
        <div class="form-group">
            <label>Parent Category</label>
            <select name="parent_id">${parentOpts}</select>
            <span class="hint">Leave blank for a top-level category.</span>
        </div>
        <div class="form-group">
            <label>Sort Order</label>
            <input type="number" name="sort_order" value="${c.sort_order !== undefined ? parseInt(c.sort_order) : 0}" min="0" max="999">
            <span class="hint">Lower = shown first.</span>
        </div>
    </div>

    <div class="form-group" style="flex-direction:row;align-items:center;gap:.75rem;margin-top:.25rem;">
        <label class="toggle-switch">
            <input type="checkbox" name="is_active" value="1" ${isActiveChecked}>
            <span class="toggle-slider"></span>
        </label>
        <span style="font-size:.875rem;font-weight:600;color:#374151;">Active (visible to customers)</span>
    </div>`;
    }

    function escH(s) {
        return s ? String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;') : '';
    }

    function openAddModal() {
        document.getElementById('addModalBody').innerHTML = catFormHTML();
        document.getElementById('addModal').classList.add('open');
    }
    function openEditModal(c) {
        document.getElementById('edit_id').value = c.id;
        document.getElementById('editModalBody').innerHTML = catFormHTML(c);
        document.getElementById('editModal').classList.add('open');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }

    document.querySelectorAll('.cat-modal-overlay').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (e.target === el) el.classList.remove('open');
        });
    });

    <?php if ($editCat): ?>
        openEditModal(<?= json_encode([
            'id' => $editCat['id'],
            'name' => $editCat['name'],
            'parent_id' => $editCat['parent_id'],
            'sort_order' => $editCat['sort_order'],
            'is_active' => $editCat['is_active'],
        ]) ?>);
    <?php endif; ?>
</script>