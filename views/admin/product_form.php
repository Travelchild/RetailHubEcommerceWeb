<div class="grid gap-8 lg:grid-cols-12 my-8 mx-12">
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <div class="lg:col-span-9 xl:col-span-9">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="inline-flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900">
                <i class="fa-solid fa-box text-brand-600" aria-hidden="true"></i><?= htmlspecialchars($title) ?>
            </h1>
            <a href="index.php?page=admin-products"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm hover:border-brand-300 hover:bg-brand-50">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Back to list
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="mt-8 rounded-3xl border border-slate-100 bg-white p-6 shadow-soft sm:p-8">
            <form method="post" enctype="multipart/form-data" class="grid gap-6 sm:grid-cols-2">

                <!-- Product Name -->
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-slate-700">Product name *</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($product['name'] ?? '') ?>"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>

                <!-- SKU -->
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-slate-700">SKU *</label>
                    <input type="text" name="sku" required value="<?= htmlspecialchars($product['sku'] ?? '') ?>"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>

                <!-- Brand -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Brand</label>
                    <input type="text" name="brand" value="<?= htmlspecialchars($product['brand'] ?? '') ?>"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>

                <?php
                // ── Build category tree ──────────────────────────────────
                $catById    = [];
                $parentCats = [];
                foreach ($categories as $c) {
                    $catById[(int)$c['id']] = array_merge($c, [
                        'id'        => (int)$c['id'],
                        'parent_id' => isset($c['parent_id']) ? (int)$c['parent_id'] : null,
                        'children'  => [],
                    ]);
                }
                foreach ($catById as $id => &$node) {
                    if (!empty($node['parent_id']) && isset($catById[$node['parent_id']])) {
                        $catById[$node['parent_id']]['children'][] = &$node;
                    } elseif (empty($node['parent_id'])) {
                        $parentCats[] = &$node;
                    }
                }
                unset($node);

                // ── Determine pre-selected parent + sub ──────────────────
                $currentCatId   = (int)($product['category_id'] ?? 0);
                $selectedParent = 0;
                $selectedSub    = 0;

                if ($currentCatId && isset($catById[$currentCatId])) {
                    $currentNode = $catById[$currentCatId];
                    if (!empty($currentNode['parent_id'])) {
                        // It IS a sub-category
                        $selectedParent = (int)$currentNode['parent_id'];
                        $selectedSub    = $currentCatId;
                    } else {
                        // It is a top-level category
                        $selectedParent = $currentCatId;
                        $selectedSub    = 0;
                    }
                }

                // Build JS data for the sub-category map
                $subMapData = [];
                foreach ($parentCats as $pc) {
                    if (!empty($pc['children'])) {
                        foreach ($pc['children'] as $sc) {
                            $subMapData[$pc['id']][] = [
                                'value' => $sc['id'],
                                'text'  => $sc['name'],
                            ];
                        }
                    }
                }
                ?>

                <!-- Main Category -->
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-slate-700">Main Category</label>
                    <select name="parent_category_id" id="parentCatSel"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                        <option value="">— Select main category —</option>
                        <?php foreach ($parentCats as $pc): ?>
                            <option value="<?= $pc['id'] ?>"
                                <?= $selectedParent === $pc['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pc['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sub Category -->
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-slate-700">
                        Sub Category
                        <span class="text-slate-400 font-normal">(optional)</span>
                    </label>
                    <select name="category_id" id="subCatSel"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                        <option value="">— None / use main category —</option>
                        <?php
                        // On edit, pre-render the sub-options for the selected parent
                        if ($selectedParent && isset($catById[$selectedParent])) {
                            foreach ($catById[$selectedParent]['children'] as $sc): ?>
                            <option value="<?= $sc['id'] ?>"
                                <?= $selectedSub === $sc['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sc['name']) ?>
                            </option>
                            <?php endforeach;
                        }
                        ?>
                    </select>
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-sm font-medium text-slate-700">Price (Rs.) *</label>
                    <input type="number" step="0.01" min="0" name="price" required
                        value="<?= htmlspecialchars((string)($product['price'] ?? '0')) ?>"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>

                <!-- Stock -->
                <div>
                    <label class="block text-sm font-medium text-slate-700">Stock *</label>
                    <input type="number" min="0" name="stock_qty" required
                        value="<?= htmlspecialchars((string)($product['stock_qty'] ?? '0')) ?>"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>

                <!-- ── MULTIPLE IMAGES ── -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Product Images
                        <span class="text-slate-400 font-normal ml-1">— first image is shown as the main image</span>
                    </label>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4" id="imageSlots">
                        <?php
                        $imageFields = [
                            ['field' => 'image_file',   'label' => 'Main Image', 'existing' => $product['image_url']   ?? ''],
                            ['field' => 'image_file_2', 'label' => 'Image 2',    'existing' => $product['image_url_2'] ?? ''],
                            ['field' => 'image_file_3', 'label' => 'Image 3',    'existing' => $product['image_url_3'] ?? ''],
                            ['field' => 'image_file_4', 'label' => 'Image 4',    'existing' => $product['image_url_4'] ?? ''],
                        ];
                        foreach ($imageFields as $idx => $imgF):
                            $hasImg = !empty($imgF['existing']);
                        ?>
                        <div class="image-slot flex flex-col gap-2">
                            <div class="flex items-center gap-1.5 text-xs font-semibold <?= $idx===0?'text-indigo-600':'text-slate-500' ?>">
                                <?php if ($idx===0): ?><i class="fa-solid fa-star text-yellow-400" style="font-size:9px"></i><?php endif; ?>
                                <?= $imgF['label'] ?>
                            </div>
                            <label for="<?= $imgF['field'] ?>"
                                class="image-drop-zone cursor-pointer rounded-xl border-2 border-dashed transition
                                       <?= $hasImg ? 'border-indigo-300 bg-indigo-50/40' : 'border-slate-200 bg-slate-50' ?>
                                       flex flex-col items-center justify-center gap-1 p-3 hover:border-indigo-400"
                                style="min-height:120px" id="zone_<?= $imgF['field'] ?>">
                                <div id="preview_wrap_<?= $imgF['field'] ?>" class="<?= $hasImg?'':'hidden' ?> w-full flex justify-center">
                                    <img id="preview_<?= $imgF['field'] ?>"
                                        src="<?= $hasImg ? htmlspecialchars(assetImageUrl($imgF['existing'])) : '' ?>"
                                        alt="Preview"
                                        class="max-h-24 rounded-lg object-contain border border-slate-100 shadow-sm w-full">
                                </div>
                                <div id="prompt_<?= $imgF['field'] ?>" class="text-center <?= $hasImg?'hidden':'' ?>">
                                    <i class="fa-solid fa-cloud-arrow-up text-slate-300 text-2xl mb-1 block"></i>
                                    <p class="text-xs text-slate-400">Click to upload</p>
                                    <p class="text-[10px] text-slate-300 mt-0.5">JPG, PNG, WEBP</p>
                                </div>
                                <input type="file" id="<?= $imgF['field'] ?>" name="<?= $imgF['field'] ?>"
                                    accept="image/*" class="hidden"
                                    onchange="previewImage(this, '<?= $imgF['field'] ?>')">
                            </label>
                            <?php if ($hasImg): ?>
                            <div class="flex items-center gap-1.5">
                                <input type="checkbox" name="clear_<?= $imgF['field'] ?>"
                                    id="clear_<?= $imgF['field'] ?>" value="1" class="h-3.5 w-3.5 accent-red-500">
                                <label for="clear_<?= $imgF['field'] ?>" class="text-[11px] text-red-500 font-medium cursor-pointer">Remove image</label>
                            </div>
                            <?php endif; ?>
                            <p id="fname_<?= $imgF['field'] ?>" class="text-[10px] text-slate-400 text-center truncate">
                                <?= $hasImg ? basename($imgF['existing']) : 'No file' ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Description</label>
                    <textarea name="description" rows="4"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>

                <!-- Active -->
                <div class="sm:col-span-2 flex items-center gap-3">
                    <input class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                        type="checkbox" value="1" id="is_active" name="is_active"
                        <?= (!isset($product['is_active']) || (int)$product['is_active'] === 1) ? 'checked' : '' ?>>
                    <label for="is_active" class="text-sm font-medium text-slate-700">Active product</label>
                </div>

                <!-- Submit -->
                <div class="sm:col-span-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-700 px-8 py-3 text-sm font-semibold text-white shadow-md hover:from-brand-700 hover:to-indigo-800">
                        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i><?= htmlspecialchars($submitLabel) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ── Sub-category map built from PHP ──────────────────────────────────────
const SUB_MAP = <?= json_encode($subMapData, JSON_HEX_TAG) ?>;

// ── Update sub-category dropdown when parent changes ─────────────────────
function updateSubCats(parentId, selectVal) {
    const sel = document.getElementById('subCatSel');
    // Remove all except placeholder
    while (sel.options.length > 1) sel.remove(1);

    const subs = SUB_MAP[parentId];
    if (!parentId || !subs || subs.length === 0) return;

    subs.forEach(function(item) {
        const opt       = document.createElement('option');
        opt.value       = item.value;
        opt.textContent = item.text;
        if (selectVal && parseInt(selectVal) === parseInt(item.value)) {
            opt.selected = true;
        }
        sel.appendChild(opt);
    });
}

// ── Wire parent dropdown ─────────────────────────────────────────────────
document.getElementById('parentCatSel').addEventListener('change', function() {
    updateSubCats(this.value, null);
});

// ── On page load: if editing, ensure sub-cat dropdown is populated ────────
(function() {
    const parentId = document.getElementById('parentCatSel').value;
    const subId    = <?= $selectedSub ?: 0 ?>;
    if (parentId) updateSubCats(parentId, subId);
})();

// ── Image preview ────────────────────────────────────────────────────────
function previewImage(input, fieldName) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('preview_' + fieldName).src = e.target.result;
        document.getElementById('preview_wrap_' + fieldName).classList.remove('hidden');
        document.getElementById('prompt_' + fieldName).classList.add('hidden');
        document.getElementById('fname_' + fieldName).textContent = input.files[0].name;
        const zone = document.getElementById('zone_' + fieldName);
        zone.classList.add('border-indigo-300', 'bg-indigo-50/40');
        zone.classList.remove('border-slate-200', 'bg-slate-50');
    };
    reader.readAsDataURL(input.files[0]);
}

// ── Drag & drop ──────────────────────────────────────────────────────────
document.querySelectorAll('.image-drop-zone').forEach(zone => {
    zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('border-indigo-400'); });
    zone.addEventListener('dragleave', ()  => zone.classList.remove('border-indigo-400'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('border-indigo-400');
        const input = zone.querySelector('input[type=file]');
        if (input && e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            previewImage(input, input.id);
        }
    });
});
</script>