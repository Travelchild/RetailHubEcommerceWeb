<?php
function orderStatusPill(string $status): string
{
    if ($status === 'Pending')
        return 'bg-amber-100 text-amber-900';
    if ($status === 'Processing')
        return 'bg-sky-100 text-sky-900';
    if ($status === 'Shipped')
        return 'bg-brand-100 text-brand-900';
    if ($status === 'Delivered')
        return 'bg-emerald-100 text-emerald-900';
    if ($status === 'Cancelled')
        return 'bg-red-100 text-red-800';
    return 'bg-slate-100 text-slate-800';
}

// Map DB status → step index (0-based)
// Steps: 0=Order Placed, 1=Accepted, 2=In Progress, 3=On the Way, 4=Delivered
function statusToStep(string $status): int
{
    return match (strtolower($status)) {
        'pending' => 0,
        'processing' => 1,
        'confirmed' => 1,
        'shipped' => 3,
        'delivered' => 4,
        default => 0,
    };
}
?>

<style>
    /* ════════════════════════════════════════════
   TRACKING MODAL STYLES
════════════════════════════════════════════ */
    .trk-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
        backdrop-filter: blur(4px);
    }

    .trk-overlay.open {
        display: flex;
    }

    .trk-modal {
        background: #fff;
        border-radius: 24px;
        width: 100%;
        max-width: 760px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 32px 80px rgba(0, 0, 0, .22);
        animation: trkPop .22s cubic-bezier(.34, 1.4, .64, 1) both;
    }

    @keyframes trkPop {
        from {
            transform: scale(.93) translateY(18px);
            opacity: 0;
        }

        to {
            transform: scale(1) translateY(0);
            opacity: 1;
        }
    }

    /* ── Modal header ── */
    .trk-header {
        padding: 24px 28px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .trk-header-left h2 {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 22px;
        color: #0f172a;
        margin: 0 0 4px;
        letter-spacing: -.02em;
    }

    .trk-header-left .trk-breadcrumb {
        font-size: 13px;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .trk-header-left .trk-breadcrumb span {
        color: #475569;
    }

    .trk-close {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #f1f5f9;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 15px;
        flex-shrink: 0;
        transition: background .15s, color .15s;
    }

    .trk-close:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* ── Order info row ── */
    .trk-info-row {
        padding: 18px 28px;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: center;
    }

    .trk-info-item {}

    .trk-info-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #94a3b8;
        margin-bottom: 3px;
    }

    .trk-info-value {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        font-family: 'Outfit', sans-serif;
    }

    .trk-info-value.mono {
        font-family: 'Courier New', monospace;
        color: #334155;
    }

    /* ── Order Status label ── */
    .trk-body {
        padding: 28px 28px 32px;
    }

    .trk-status-heading {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 16px;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .trk-order-ref {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 32px;
    }

    .trk-order-ref strong {
        color: #334155;
        font-family: 'Courier New', monospace;
    }

    /* ════════════════════════════════════════════
   HORIZONTAL STEPPER  (matches reference)
════════════════════════════════════════════ */
    .trk-stepper {
        display: flex;
        align-items: flex-start;
        position: relative;
        margin-bottom: 32px;
        overflow-x: auto;
        padding-bottom: 4px;
    }

    /* connecting line behind dots */
    .trk-stepper-track {
        position: absolute;
        top: 22px;
        /* centre of 44px dot */
        left: calc(10% - 0px);
        right: calc(10% - 0px);
        height: 3px;
        background: #e2e8f0;
        border-radius: 2px;
        z-index: 0;
    }

    /* filled portion */
    .trk-stepper-fill {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        background: linear-gradient(90deg, #2d6a4f, #52b788);
        border-radius: 2px;
        transition: width .6s ease;
    }

    /* each step column */
    .trk-step {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0;
        position: relative;
        z-index: 1;
        min-width: 90px;
    }

    /* dot + icon */
    .trk-step-dot {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #e2e8f0;
        background: white;
        position: relative;
        z-index: 2;
        transition: all .3s;
        margin-bottom: 10px;
    }

    .trk-step-dot svg,
    .trk-step-dot i {
        font-size: 18px;
        color: #94a3b8;
        transition: color .3s;
    }

    /* ── State: completed ── */
    .trk-step.done .trk-step-dot {
        background: #2d6a4f;
        border-color: #2d6a4f;
        box-shadow: 0 0 0 4px rgba(45, 106, 79, .12);
    }

    .trk-step.done .trk-step-dot i {
        color: white;
    }

    /* green check replaces icon */
    .trk-step.done .trk-step-dot::after {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        color: white;
        font-size: 16px;
        position: absolute;
    }

    .trk-step.done .trk-step-dot i {
        display: none;
    }

    /* ── State: active (current) ── */
    .trk-step.active .trk-step-dot {
        background: white;
        border-color: #2d6a4f;
        border-width: 3px;
        box-shadow: 0 0 0 5px rgba(45, 106, 79, .15);
    }

    .trk-step.active .trk-step-dot i {
        color: #2d6a4f;
    }

    .trk-step.active .trk-step-label {
        color: #2d6a4f;
        font-weight: 800;
    }

    /* ── State: upcoming ── */
    .trk-step.upcoming .trk-step-dot {
        background: white;
        border-color: #e2e8f0;
    }

    .trk-step.upcoming .trk-step-dot i {
        color: #cbd5e1;
    }

    /* ── State: cancelled ── */
    .trk-step.cancelled .trk-step-dot {
        background: #fef2f2;
        border-color: #fca5a5;
    }

    .trk-step.cancelled .trk-step-dot i {
        color: #ef4444;
    }

    /* step label */
    .trk-step-label {
        font-family: 'DM Sans', sans-serif;
        font-size: 12.5px;
        font-weight: 600;
        color: #64748b;
        text-align: center;
        margin-bottom: 6px;
        transition: color .3s;
    }

    /* step date/time */
    .trk-step-date {
        font-size: 11px;
        color: #94a3b8;
        text-align: center;
        line-height: 1.5;
        min-height: 32px;
    }

    .trk-step-date .trk-date-main {
        color: #475569;
        font-weight: 600;
        font-size: 11.5px;
    }

    .trk-step-date .trk-date-time {
        color: #94a3b8;
    }

    .trk-step-date .trk-date-expected {
        color: #94a3b8;
        font-style: italic;
    }

    /* ── Cancelled banner ── */
    .trk-cancelled-banner {
        background: #fef2f2;
        border: 1.5px solid #fecaca;
        border-radius: 12px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 24px;
        font-size: 13.5px;
        color: #b91c1c;
        font-weight: 600;
    }

    .trk-cancelled-banner i {
        font-size: 16px;
    }

    /* ── Order items table ── */
    .trk-items-heading {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 14px;
        color: #0f172a;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 7px;
        border-top: 1px solid #f1f5f9;
        padding-top: 22px;
    }

    .trk-items-heading i {
        color: #ff9900;
    }

    .trk-items-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 20px;
    }

    .trk-item-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
    }

    .trk-item-img {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: #e2e8f0;
        flex-shrink: 0;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        border: 1px solid #e2e8f0;
    }

    .trk-item-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .trk-item-name {
        font-size: 13.5px;
        font-weight: 700;
        color: #1e293b;
    }

    .trk-item-qty {
        font-size: 12px;
        color: #64748b;
        margin-top: 2px;
    }

    .trk-item-price {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 14px;
        color: #0f172a;
        margin-left: auto;
        flex-shrink: 0;
    }

    /* ── Total + invoice row ── */
    .trk-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        background: linear-gradient(135deg, #131921, #232f3e);
        border-radius: 14px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .trk-total-lbl {
        font-size: 13px;
        color: rgba(255, 255, 255, .6);
        font-weight: 600;
    }

    .trk-total-val {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: 22px;
        color: #ff9900;
    }

    .trk-invoice-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        background: rgba(255, 153, 0, .15);
        color: #ff9900;
        border: 1.5px solid rgba(255, 153, 0, .35);
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: all .15s;
        cursor: pointer;
    }

    .trk-invoice-btn:hover {
        background: rgba(255, 153, 0, .25);
        color: #ff9900;
    }

    /* ── Clickable table rows ── */
    .ord-row {
        cursor: pointer;
        transition: background .12s;
    }

    .ord-row:hover {
        background: #fffbf2;
    }

    .ord-row:hover td:first-child {
        color: #ff9900;
    }

    /* ── Responsive ── */
    @media (max-width: 600px) {
        .trk-header {
            padding: 18px 18px 16px;
        }

        .trk-body {
            padding: 20px 18px 24px;
        }

        .trk-info-row {
            padding: 14px 18px;
            gap: 14px;
        }

        .trk-step-dot {
            width: 36px;
            height: 36px;
        }

        .trk-step {
            min-width: 70px;
        }

        .trk-step-label {
            font-size: 11px;
        }

        .trk-step-date {
            font-size: 10px;
        }

        .trk-stepper-track {
            top: 18px;
        }

        .trk-footer {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<!-- ════════════════════════════════════════════
     PAGE HEADER
════════════════════════════════════════════ -->
<div class="mb-6 mt-8 ml-12 mr-12 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="inline-flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900">
            <i class="fa-solid fa-clock-rotate-left text-brand-600"></i>Order history
        </h1>
        <p class="mt-1 text-sm text-slate-600">Click any order to view tracking details.</p>
    </div>
    <span
        class="inline-flex w-fit items-center gap-2 rounded-full bg-slate-100 px-4 py-1.5 text-sm font-semibold text-slate-700">
        <i class="fa-solid fa-box text-xs opacity-70"></i><?= count($orders) ?> orders
    </span>
</div>

<!-- ════════════════════════════════════════════
     ORDERS TABLE
════════════════════════════════════════════ -->
<div class="mb-8 ml-12 mr-12 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Order</th>
                    <th class="px-5 py-3">Total</th>
                    <th class="px-5 py-3">Payment</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Date</th>
                    <th class="px-5 py-3 text-right">Invoice</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($orders as $o):
                    $st = $o['order_status'] ?? 'Pending';
                    $items = $itemsByOrder[$o['id']] ?? [];
                    $firstItem = $items[0]['product_name'] ?? 'Order items';
                    $extra = count($items) - 1;
                    $desc = $firstItem . ($extra > 0 ? " +{$extra} more" : '');
                    $orderNum = $o['order_number'] ?? ('#RH-' . str_pad($o['id'], 6, '0', STR_PAD_LEFT));

                    // Build data payload for JS modal
                    $payload = htmlspecialchars(json_encode([
                        'id' => $o['id'],
                        'order_number' => $orderNum,
                        'status' => $st,
                        'total_amount' => $o['total_amount'],
                        'created_at' => $o['created_at'],
                        'payment_method' => $o['payment_method'] ?? 'N/A',
                        'payment_status' => $o['payment_status'] ?? 'Unpaid',
                        'shipping_address' => $o['shipping_address'] ?? '',
                        'notes' => $o['notes'] ?? '',
                        'items' => array_map(fn($i) => [
                            'product_name' => $i['product_name'] ?? 'Product',
                            'image_url' => $i['image_url'] ?? '',
                            'quantity' => $i['quantity'],
                            'unit_price' => $i['unit_price'] ?? $i['price'] ?? 0,
                        ], $items),
                    ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr class="ord-row" onclick='openTrackModal(<?= $payload ?>)' title="Click to track order">
                        <td class="px-5 py-4 font-medium text-slate-900">
                            <div class="font-bold"><?= htmlspecialchars($orderNum) ?></div>
                            <div class="text-xs text-slate-400 mt-0.5"><?= htmlspecialchars($desc) ?></div>
                        </td>
                        <td class="px-5 py-4 text-slate-600 font-semibold"><?= formatCurrency($o['total_amount']) ?></td>
                        <td class="px-5 py-4 text-slate-600">
                            <span
                                class="text-xs font-medium text-slate-800"><?= htmlspecialchars($o['payment_status'] ?? '—') ?></span>
                            <?php if (!empty($o['payment_transaction_id'])): ?>
                                <span
                                    class="mt-0.5 block text-[11px] text-slate-500"><?= htmlspecialchars((string) $o['payment_transaction_id']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4">
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?= orderStatusPill($st) ?>"><?= htmlspecialchars($st) ?></span>
                        </td>
                        <td class="px-5 py-4 text-slate-500"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                        <td class="px-5 py-4 text-right" onclick="event.stopPropagation()">
                            <a href="index.php?page=invoice&id=<?= (int) $o['id'] ?>"
                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-brand-700 hover:border-brand-300 hover:bg-brand-50">
                                <i class="fa-solid fa-file-pdf"></i>PDF
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ════════════════════════════════════════════
     TRACKING MODAL
════════════════════════════════════════════ -->
<div class="trk-overlay" id="trkOverlay" onclick="trkClickOutside(event)">
    <div class="trk-modal" id="trkModal">

        <!-- Header -->
        <div class="trk-header">
            <div class="trk-header-left">
                <h2>Track Your Order</h2>
                <div class="trk-breadcrumb">
                    <span>Home</span>
                    <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i>
                    <span>Track Your Order</span>
                </div>
            </div>
            <button class="trk-close" onclick="closeTrackModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Order quick-info strip -->
        <div class="trk-info-row">
            <div class="trk-info-item">
                <div class="trk-info-label">Order ID</div>
                <div class="trk-info-value mono" id="trkOrderNum">—</div>
            </div>
            <div class="trk-info-item">
                <div class="trk-info-label">Order Date</div>
                <div class="trk-info-value" id="trkOrderDate">—</div>
            </div>
            <div class="trk-info-item">
                <div class="trk-info-label">Total Amount</div>
                <div class="trk-info-value" id="trkOrderTotal" style="color:#ff9900;">—</div>
            </div>
            <div class="trk-info-item">
                <div class="trk-info-label">Payment</div>
                <div class="trk-info-value" id="trkPayMethod">—</div>
            </div>
        </div>

        <!-- Body -->
        <div class="trk-body">

            <!-- Cancelled banner (hidden unless cancelled) -->
            <div class="trk-cancelled-banner" id="trkCancelledBanner" style="display:none;">
                <i class="fa-solid fa-circle-xmark"></i>
                <span>This order has been cancelled. Please contact support if you need assistance.</span>
            </div>

            <!-- Order status heading -->
            <div class="trk-status-heading">Order Status</div>
            <div class="trk-order-ref" id="trkOrderRef">Order ID : <strong>—</strong></div>

            <!-- ═══════════════════════════════════
             HORIZONTAL STEPPER
        ═══════════════════════════════════ -->
            <div class="trk-stepper" id="trkStepper">
                <!-- Track line -->
                <div class="trk-stepper-track">
                    <div class="trk-stepper-fill" id="trkFill" style="width:0%;"></div>
                </div>

                <!-- Step 0: Order Placed -->
                <div class="trk-step" id="tstep-0">
                    <div class="trk-step-dot"><i class="fa-solid fa-clipboard-list"></i></div>
                    <div class="trk-step-label">Order Placed</div>
                    <div class="trk-step-date" id="tdate-0">—</div>
                </div>

                <!-- Step 1: Accepted -->
                <div class="trk-step" id="tstep-1">
                    <div class="trk-step-dot"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="trk-step-label">Accepted</div>
                    <div class="trk-step-date" id="tdate-1">—</div>
                </div>

                <!-- Step 2: In Progress -->
                <div class="trk-step" id="tstep-2">
                    <div class="trk-step-dot"><i class="fa-solid fa-box-open"></i></div>
                    <div class="trk-step-label">In Progress</div>
                    <div class="trk-step-date" id="tdate-2">—</div>
                </div>

                <!-- Step 3: On the Way -->
                <div class="trk-step" id="tstep-3">
                    <div class="trk-step-dot"><i class="fa-solid fa-truck"></i></div>
                    <div class="trk-step-label">On the Way</div>
                    <div class="trk-step-date" id="tdate-3">—</div>
                </div>

                <!-- Step 4: Delivered -->
                <div class="trk-step" id="tstep-4">
                    <div class="trk-step-dot"><i class="fa-solid fa-house-circle-check"></i></div>
                    <div class="trk-step-label">Delivered</div>
                    <div class="trk-step-date" id="tdate-4">—</div>
                </div>
            </div><!-- /stepper -->

            <!-- Delivery address -->
            <div
                style="margin-bottom:20px; padding:14px 16px; background:#f8fafc; border-radius:12px; border:1px solid #f1f5f9;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-bottom:5px;">
                    <i class="fa-solid fa-location-dot" style="color:#ff9900; margin-right:4px;"></i>Delivery Address
                </div>
                <div id="trkAddress" style="font-size:13.5px; font-weight:600; color:#334155;">—</div>
            </div>





        </div><!-- /trk-body -->
    </div><!-- /trk-modal -->
</div><!-- /trk-overlay -->

<script>
    (function () {
        'use strict';

        // ── Expected date helpers ──
        // We generate "expected" dates based on order creation date + step offset days.
        const STEP_OFFSETS = [0, 0, 1, 2, 4]; // days after order placed for each step

        function fmtDate(d) {
            // "20 Apr 2024"
            return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        }
        function fmtTime(d) {
            // "11:00 AM"
            return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        }
        function addDays(d, n) {
            const r = new Date(d); r.setDate(r.getDate() + n); return r;
        }

        // Map DB status → completed step index
        function statusToStepIdx(st) {
            const s = (st || '').toLowerCase();
            if (s === 'delivered') return 4;
            if (s === 'shipped') return 3;
            if (s === 'processing' || s === 'confirmed') return 2;
            if (s === 'accepted') return 1;
            if (s === 'pending') return 0;
            return 0;
        }

        // ── Open modal ──
        window.openTrackModal = function (order) {
            const overlay = document.getElementById('trkOverlay');
            const st = (order.status || 'pending');
            const isCancelled = st.toLowerCase() === 'cancelled';
            const curStep = isCancelled ? -1 : statusToStepIdx(st);
            const created = new Date(order.created_at);

            // Header info
            document.getElementById('trkOrderNum').textContent = order.order_number || ('#RH-' + String(order.id).padStart(6, '0'));
            document.getElementById('trkOrderDate').textContent = fmtDate(created) + ' ' + fmtTime(created);
            document.getElementById('trkOrderTotal').textContent = 'LKR ' + Number(order.total_amount).toLocaleString('en-LK');
            document.getElementById('trkPayMethod').textContent = order.payment_method || 'N/A';
            document.getElementById('trkOrderRef').innerHTML = 'Order ID : <strong>#' + (order.order_number || order.id) + '</strong>';
            document.getElementById('trkAddress').textContent = order.shipping_address || 'Not provided';


            // Cancelled banner
            document.getElementById('trkCancelledBanner').style.display = isCancelled ? 'flex' : 'none';

            // ── Stepper states ──
            for (let i = 0; i < 5; i++) {
                const stepEl = document.getElementById('tstep-' + i);
                const dateEl = document.getElementById('tdate-' + i);
                stepEl.classList.remove('done', 'active', 'upcoming', 'cancelled-step');

                if (isCancelled) {
                    stepEl.classList.add('upcoming');
                    dateEl.innerHTML = '<span class="trk-date-expected">Cancelled</span>';
                    continue;
                }

                // Date/time for this step
                const stepDate = addDays(created, STEP_OFFSETS[i]);
                const isPast = i <= curStep;

                if (i < curStep) {
                    stepEl.classList.add('done');
                    dateEl.innerHTML =
                        '<div class="trk-date-main">' + fmtDate(stepDate) + '</div>' +
                        '<div class="trk-date-time">' + fmtTime(stepDate) + '</div>';

                } else if (i === curStep) {
                    stepEl.classList.add('active');
                    dateEl.innerHTML =
                        '<div class="trk-date-main">' + fmtDate(stepDate) + '</div>' +
                        '<div class="trk-date-time">' + fmtTime(stepDate) + '</div>';

                } else {
                    stepEl.classList.add('upcoming');
                    const exp = addDays(created, STEP_OFFSETS[i]);
                    dateEl.innerHTML =
                        '<div class="trk-date-expected">Expected</div>' +
                        '<div class="trk-date-main">' + fmtDate(exp) + '</div>';
                }
            }

            // ── Progress fill (0 steps done = 0%, 4 done = 100%) ──
            const fillPct = isCancelled ? 0 : (curStep / 4) * 100;
            document.getElementById('trkFill').style.width = fillPct + '%';




            // Open
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        };

        window.closeTrackModal = function () {
            document.getElementById('trkOverlay').classList.remove('open');
            document.body.style.overflow = '';
        };

        window.trkClickOutside = function (e) {
            if (e.target === document.getElementById('trkOverlay')) closeTrackModal();
        };

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeTrackModal();
        });

        function escH(s) {
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

    })();
</script>