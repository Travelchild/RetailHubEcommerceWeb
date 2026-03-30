<div class="grid gap-8 lg:grid-cols-12 my-8 mx-12">
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <div class="lg:col-span-9 xl:col-span-9">
        <h1 class="inline-flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900"><i class="fa-solid fa-headset text-brand-600" aria-hidden="true"></i>Help desk</h1>
        <p class="mt-1 text-sm text-slate-600">Respond to tickets and change resolution status.</p>

        <?php if (!empty($flash)): ?>
            <div class="mt-6 rounded-xl border px-4 py-3 text-sm <?= flashBoxClass($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <div class="mt-8 space-y-5">
            <?php if (empty($tickets)): ?>
                <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 py-16 text-center text-sm text-slate-600"><i class="fa-solid fa-inbox mb-3 text-3xl text-slate-300" aria-hidden="true"></i>No tickets yet.</div>
            <?php endif; ?>
            <?php foreach ($tickets as $t): ?>
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
                    <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900"><?= htmlspecialchars($t['subject']) ?></h2>
                            <p class="mt-1 text-xs text-slate-500"><?= htmlspecialchars($t['full_name']) ?> · <?= htmlspecialchars($t['ticket_type']) ?> · <?= htmlspecialchars($t['created_at']) ?></p>
                        </div>
                        <span class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"><?= htmlspecialchars($t['status']) ?></span>
                    </div>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600"><?= nl2br(htmlspecialchars($t['description'])) ?></p>

                    <?php $replies = $ticketReplies[(int)$t['id']] ?? []; ?>
                    <?php if (!empty($replies)): ?>
                        <div class="mt-5 rounded-xl bg-slate-50 p-4">
                            <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-500"><i class="fa-solid fa-comments text-slate-400" aria-hidden="true"></i>Thread</p>
                            <?php foreach ($replies as $reply): ?>
                                <div class="mt-3 border-t border-slate-200 pt-3 first:border-0 first:pt-0">
                                    <p class="text-sm text-slate-800"><span class="font-semibold"><?= htmlspecialchars($reply['responder_name']) ?>:</span> <?= nl2br(htmlspecialchars($reply['reply_text'])) ?></p>
                                    <p class="mt-1 text-xs text-slate-500"><?= htmlspecialchars($reply['created_at']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="index.php?page=admin-helpdesk-reply" class="mt-5 grid gap-3 sm:grid-cols-12">
                        <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>">
                        <div class="sm:col-span-3">
                            <select name="status" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                <option value="Open" <?= $t['status'] === 'Open' ? 'selected' : '' ?>>Open</option>
                                <option value="In Progress" <?= $t['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="Resolved" <?= $t['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                                <option value="Closed" <?= $t['status'] === 'Closed' ? 'selected' : '' ?>>Closed</option>
                            </select>
                        </div>
                        <div class="sm:col-span-7">
                            <input type="text" name="reply_text" placeholder="Optional reply to customer…" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 py-2 text-sm font-semibold text-white hover:bg-brand-700"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>Update</button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
