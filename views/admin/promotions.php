<?php

$section = 'promotions';
$db = Database::connection();

function promoRedirect(string $msg, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $msg, 'type' => $type];
    echo '<script>window.location.href = "index.php?page=admin-promotions";</script>';
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'add') {
        $stmt = $db->prepare("
            INSERT INTO promotions
                (title, tag_label, tag_icon, emoji, discount_text,
                 btn_label, btn_icon, link_url, slide_theme, sort_order, is_active, starts_at, ends_at)
            VALUES
                (:title, :tag_label, :tag_icon, :emoji, :discount_text,
                 :btn_label, :btn_icon, :link_url, :slide_theme, :sort_order, :is_active, :starts_at, :ends_at)
        ");
        $stmt->execute([
            ':title' => trim($_POST['title']),
            ':tag_label' => trim($_POST['tag_label']),
            ':tag_icon' => trim($_POST['tag_icon']),
            ':emoji' => trim($_POST['emoji']),
            ':discount_text' => trim($_POST['discount_text']),
            ':btn_label' => trim($_POST['btn_label']),
            ':btn_icon' => trim($_POST['btn_icon']),
            ':link_url' => trim($_POST['link_url']),
            ':slide_theme' => trim($_POST['slide_theme']),
            ':sort_order' => (int) ($_POST['sort_order'] ?? 0),
            ':is_active' => isset($_POST['is_active']) ? 1 : 0,
            ':starts_at' => !empty($_POST['starts_at']) ? $_POST['starts_at'] : null,
            ':ends_at' => !empty($_POST['ends_at']) ? $_POST['ends_at'] : null,
        ]);
        promoRedirect('Promotion added successfully.');
    }

    if ($action === 'edit') {
        $id = (int) $_POST['id'];
        $stmt = $db->prepare("
            UPDATE promotions SET
                title         = :title,
                tag_label     = :tag_label,
                tag_icon      = :tag_icon,
                emoji         = :emoji,
                discount_text = :discount_text,
                btn_label     = :btn_label,
                btn_icon      = :btn_icon,
                link_url      = :link_url,
                slide_theme   = :slide_theme,
                sort_order    = :sort_order,
                is_active     = :is_active,
                starts_at     = :starts_at,
                ends_at       = :ends_at
            WHERE id = :id
        ");
        $stmt->execute([
            ':title' => trim($_POST['title']),
            ':tag_label' => trim($_POST['tag_label']),
            ':tag_icon' => trim($_POST['tag_icon']),
            ':emoji' => trim($_POST['emoji']),
            ':discount_text' => trim($_POST['discount_text']),
            ':btn_label' => trim($_POST['btn_label']),
            ':btn_icon' => trim($_POST['btn_icon']),
            ':link_url' => trim($_POST['link_url']),
            ':slide_theme' => trim($_POST['slide_theme']),
            ':sort_order' => (int) ($_POST['sort_order'] ?? 0),
            ':is_active' => isset($_POST['is_active']) ? 1 : 0,
            ':starts_at' => !empty($_POST['starts_at']) ? $_POST['starts_at'] : null,
            ':ends_at' => !empty($_POST['ends_at']) ? $_POST['ends_at'] : null,
            ':id' => $id,
        ]);
        promoRedirect('Promotion updated successfully.');
    }

    if ($action === 'toggle') {
        $id = (int) $_POST['id'];
        $db->prepare("UPDATE promotions SET is_active = NOT is_active WHERE id = :id")
            ->execute([':id' => $id]);
        promoRedirect('Promotion status updated.');
    }

    if ($action === 'delete') {
        $id = (int) $_POST['id'];
        $db->prepare("DELETE FROM promotions WHERE id = :id")->execute([':id' => $id]);
        promoRedirect('Promotion deleted.', 'danger');
    }
}

$promotions = $db->query("SELECT * FROM promotions ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

$editPromo = null;
if (isset($_GET['edit_id'])) {
    $stmt = $db->prepare("SELECT * FROM promotions WHERE id = :id");
    $stmt->execute([':id' => (int) $_GET['edit_id']]);
    $editPromo = $stmt->fetch(PDO::FETCH_ASSOC);
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$themes = [
    'theme-1' => 'Indigo / Brand',
    'theme-2' => 'Sky / Cyan',
    'theme-3' => 'Violet / Purple',
    'theme-4' => 'Emerald / Teal',
    'theme-5' => 'Amber / Orange',
];

$iconPresets = [
    'fa-solid fa-bolt' => '⚡ Bolt',
    'fa-solid fa-star' => '⭐ Star',
    'fa-solid fa-fire' => '🔥 Fire',
    'fa-solid fa-tag' => '🏷️ Tag',
    'fa-solid fa-house' => '🏠 House',
    'fa-solid fa-truck-fast' => '🚚 Truck',
    'fa-solid fa-gift' => '🎁 Gift',
    'fa-solid fa-percent' => '% Percent',
];
?>

<style>
    /* toggle switch */
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

    /* slide theme pills */
    .slide-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .2rem .65rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 600;
        color: #fff;
    }

    .slide-pill.theme-1 {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
    }

    .slide-pill.theme-2 {
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
    }

    .slide-pill.theme-3 {
        background: linear-gradient(135deg, #7c3aed, #6d28d9);
    }

    .slide-pill.theme-4 {
        background: linear-gradient(135deg, #10b981, #0d9488);
    }

    .slide-pill.theme-5 {
        background: linear-gradient(135deg, #f59e0b, #ea580c);
    }

    /* modal */
    .promo-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .45);
        z-index: 9000;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .promo-modal-overlay.open {
        display: flex;
    }

    .promo-modal {
        background: #fff;
        border-radius: 1.25rem;
        width: 100%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
    }

    .promo-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .promo-modal-header h2 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .promo-modal-close {
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        color: #64748b;
        padding: .25rem .5rem;
        border-radius: .5rem;
    }

    .promo-modal-close:hover {
        background: #f1f5f9;
    }

    .promo-modal-body {
        padding: 1.5rem;
        display: grid;
        gap: 1rem;
    }

    .promo-modal-footer {
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
                    <i class="fa-solid fa-fire text-orange-500" aria-hidden="true"></i>Manage Promotions
                </h1>
                <p class="mt-1 text-sm text-slate-600">Add, edit, delete and schedule promotion slides shown to
                    customers.</p>
            </div>
            <button onclick="openAddModal()"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-700 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-700 hover:to-indigo-800">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>Add Promotion
            </button>
        </div>

        <!-- Flash message -->
        <?php if (!empty($flash)): ?>
            <div class="mt-6 rounded-xl border px-4 py-3 text-sm <?= flashBoxClass($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Promotions table -->
        <div class="mt-8 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
            <div class="overflow-x-auto">
                <?php if (empty($promotions)): ?>
                    <div class="flex flex-col items-center justify-center gap-3 py-16 text-slate-400">
                        <i class="fa-solid fa-fire-flame-simple text-4xl"></i>
                        <p class="text-sm">No promotions yet. Click <strong>Add Promotion</strong> to create your first
                            slide.</p>
                    </div>
                <?php else: ?>
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">Slide</th>
                                <th class="px-4 py-3">Title</th>
                                <th class="px-4 py-3">Tag</th>
                                <th class="px-4 py-3">Theme</th>
                                <th class="px-4 py-3">Order</th>
                                <th class="px-4 py-3">Schedule</th>
                                <th class="px-4 py-3">Active</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($promotions as $p): ?>
                                <tr class="align-middle">
                                    <td class="px-4 py-3 text-slate-500">#<?= (int) $p['id'] ?></td>
                                    <td class="px-4 py-3 text-2xl"><?= htmlspecialchars($p['emoji']) ?></td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-slate-900"><?= htmlspecialchars($p['title']) ?></span><br>
                                        <span class="text-xs text-slate-400"><?= htmlspecialchars($p['discount_text']) ?></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                                            <i class="<?= htmlspecialchars($p['tag_icon']) ?>" style="font-size:9px;"></i>
                                            <?= htmlspecialchars($p['tag_label']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="slide-pill <?= htmlspecialchars($p['slide_theme']) ?>">
                                            <?= htmlspecialchars($themes[$p['slide_theme']] ?? $p['slide_theme']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600"><?= (int) $p['sort_order'] ?></td>
                                    <td class="px-4 py-3 text-xs text-slate-500">
                                        <?php if ($p['starts_at'] || $p['ends_at']): ?>
                                            <?= $p['starts_at'] ? date('M j, Y', strtotime($p['starts_at'])) : '—' ?>
                                            &rarr; <?= $p['ends_at'] ? date('M j, Y', strtotime($p['ends_at'])) : '∞' ?>
                                        <?php else: ?>
                                            <span class="text-slate-300">Always</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <form method="POST" action="index.php?page=admin-promotions" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <label class="toggle-switch" title="Toggle active">
                                                <input type="checkbox" <?= $p['is_active'] ? 'checked' : '' ?>
                                                    onchange="this.form.submit()">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            <a href="#"
                                                onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>); return false;"
                                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-800 hover:border-brand-300 hover:bg-brand-50">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>Edit
                                            </a>
                                            <form method="POST" action="index.php?page=admin-promotions" class="inline"
                                                onsubmit="return confirm('Delete this promotion?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
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
        </div><!-- /table card -->

    </div><!-- /lg:col-span-9 -->
</div><!-- /grid -->


<!-- ══════════════════════════════════════
     ADD MODAL
══════════════════════════════════════ -->
<div class="promo-modal-overlay" id="addModal">
    <div class="promo-modal">
        <div class="promo-modal-header">
            <h2><i class="fa-solid fa-plus-circle text-indigo-500"></i> Add Promotion</h2>
            <button class="promo-modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST" action="index.php?page=admin-promotions">
            <input type="hidden" name="action" value="add">
            <div class="promo-modal-body" id="addModalBody">
                <!-- filled by JS on open -->
            </div>
            <div class="promo-modal-footer">
                <button type="button" class="btn-cancel-modal" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-submit"><i class="fa-solid fa-plus"></i> Add Promotion</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════
     EDIT MODAL
══════════════════════════════════════ -->
<div class="promo-modal-overlay" id="editModal">
    <div class="promo-modal">
        <div class="promo-modal-header">
            <h2><i class="fa-solid fa-pen-to-square text-blue-500"></i> Edit Promotion</h2>
            <button class="promo-modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST" action="index.php?page=admin-promotions" id="editForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="promo-modal-body" id="editModalBody">
                <!-- filled by JS -->
            </div>
            <div class="promo-modal-footer">
                <button type="button" class="btn-cancel-modal" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    const THEMES = <?= json_encode($themes) ?>;
    const ICONS = <?= json_encode($iconPresets) ?>;

    function formFieldsHTML(p) {
        p = p || {};
        var themeOpts = Object.entries(THEMES).map(function ([v, l]) {
            return '<option value="' + v + '"' + (p.slide_theme === v ? ' selected' : '') + '>' + l + '</option>';
        }).join('');
        var iconOpts = Object.entries(ICONS).map(function ([v, l]) {
            return '<option value="' + v + '"' + (p.tag_icon === v ? ' selected' : '') + '>' + l + '</option>';
        }).join('');
        var btnIconOpts = Object.entries(ICONS).map(function ([v, l]) {
            return '<option value="' + v + '"' + (p.btn_icon === v ? ' selected' : '') + '>' + l + '</option>';
        }).join('');
        var isActiveChecked = (p.is_active === 1 || p.is_active === undefined) ? 'checked' : '';

        return '<div class="form-row">'
            + '<div class="form-group" style="grid-column:1/-1"><label>Slide Title *</label>'
            + '<input name="title" required value="' + esc(p.title) + '" placeholder="e.g. Electronics Up to 40% Off"></div></div>'

            + '<div class="form-row">'
            + '<div class="form-group"><label>Tag Label *</label><input name="tag_label" required value="' + esc(p.tag_label) + '" placeholder="Flash Sale"></div>'
            + '<div class="form-group"><label>Tag Icon</label><select name="tag_icon">' + iconOpts + '</select></div></div>'

            + '<div class="form-row">'
            + '<div class="form-group"><label>Discount Text *</label><input name="discount_text" required value="' + esc(p.discount_text) + '" placeholder="Up to 40% Off"></div>'
            + '<div class="form-group"><label>Emoji</label><input name="emoji" value="' + esc(p.emoji || '🛍️') + '" placeholder="📱" maxlength="4"><span class="hint">Paste any emoji</span></div></div>'

            + '<div class="form-row">'
            + '<div class="form-group"><label>Button Label</label><input name="btn_label" value="' + esc(p.btn_label || 'Shop Now') + '" placeholder="Shop Now"></div>'
            + '<div class="form-group"><label>Button Icon</label><select name="btn_icon">' + btnIconOpts + '</select></div></div>'

            + '<div class="form-group"><label>Link URL</label>'
            + '<input name="link_url" value="' + esc(p.link_url || '#') + '" placeholder="index.php?page=products&category=..."></div>'

            + '<div class="form-row">'
            + '<div class="form-group"><label>Slide Theme</label><select name="slide_theme">' + themeOpts + '</select></div>'
            + '<div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="' + (p.sort_order || 0) + '" min="0" max="99"><span class="hint">Lower = shown first</span></div></div>'

            + '<div class="form-row">'
            + '<div class="form-group"><label>Start Date (optional)</label><input type="datetime-local" name="starts_at" value="' + (p.starts_at || '').replace(' ', 'T').slice(0, 16) + '"></div>'
            + '<div class="form-group"><label>End Date (optional)</label><input type="datetime-local" name="ends_at" value="' + (p.ends_at || '').replace(' ', 'T').slice(0, 16) + '"></div></div>'

            + '<div class="form-group" style="flex-direction:row;align-items:center;gap:.75rem;">'
            + '<label class="toggle-switch"><input type="checkbox" name="is_active" value="1" ' + isActiveChecked + '>'
            + '<span class="toggle-slider"></span></label>'
            + '<span style="font-size:.875rem;font-weight:600;color:#374151;">Active (visible to customers)</span></div>';
    }

    function esc(s) {
        return s ? String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;') : '';
    }

    function openAddModal() {
        document.getElementById('addModalBody').innerHTML = formFieldsHTML();
        document.getElementById('addModal').classList.add('open');
    }

    function openEditModal(p) {
        document.getElementById('edit_id').value = p.id;
        document.getElementById('editModalBody').innerHTML = formFieldsHTML(p);
        document.getElementById('editModal').classList.add('open');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }

    document.querySelectorAll('.promo-modal-overlay').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (e.target === el) el.classList.remove('open');
        });
    });

    <?php if ($editPromo): ?>
        openEditModal(<?= json_encode($editPromo) ?>);
    <?php endif; ?>
</script>