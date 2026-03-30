<?php

$user = currentUser();
// Map your DB column names if they differ, e.g.:
$user['full_name'] = $user['full_name'] ?? '';
$user['phone'] = $user['phone'] ?? '';
$user['address'] = $user['address'] ?? '';
$user['city'] = $user['city'] ?? '';
$user['postal_code'] = $user['postal_code'] ?? '';



// At the top of the file, after $user is set — fetch active promotions
$now = date('Y-m-d H:i:s');
$promoStmt = $pdo->prepare("
    SELECT * FROM promotions
    WHERE is_active = 1
      AND (starts_at IS NULL OR starts_at <= :now1)
      AND (ends_at   IS NULL OR ends_at   >= :now2)
    ORDER BY sort_order ASC, id ASC
");
$promoStmt->execute([':now1' => $now, ':now2' => $now]);
$activePromos = $promoStmt->fetchAll(PDO::FETCH_ASSOC);


// ── Real counts from DB ──
$userId = $user['id'];

// Total orders
$orderStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$orderStmt->execute([$userId]);
$totalOrders = (int) $orderStmt->fetchColumn();

// Cart items count
$cartStmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?");
$cartStmt->execute([$userId]);
$cartCount = (int) $cartStmt->fetchColumn();

// Wishlist items
$wishStmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
$wishStmt->execute([$userId]);
$wishlistCount = (int) $wishStmt->fetchColumn();

// ── Ticket submit handler ──
$ticketSuccess = false;
$ticketError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_subject'])) {
    $subject = trim($_POST['ticket_subject'] ?? '');
    $type = trim($_POST['ticket_type'] ?? '');
    $description = trim($_POST['ticket_description'] ?? '');

    if ($subject && $type && $description) {
        $ins = $pdo->prepare("
            INSERT INTO support_tickets (user_id, subject, ticket_type, description, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'Open', NOW(), NOW())
        ");
        $ins->execute([$userId, $subject, $type, $description]);
        $ticketSuccess = true;
    } else {
        $ticketError = 'Please fill in all required fields.';
    }
}

// ── Fetch user's tickets ──
$myTickets = [];
try {
    $tStmt = $pdo->prepare("
        SELECT * FROM support_tickets
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $tStmt->execute([$userId]);
    $myTickets = $tStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}


// ── Profile update handler ──
$profileSuccess = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profile_name'])) {
    // TODO: update users table
    $profileSuccess = true;
}

// ── Fetch user's orders with items ──
$ordersStmt = $pdo->prepare("
    SELECT o.id, o.order_status, o.total_amount, o.created_at,
           o.payment_method
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$ordersStmt->execute([$userId]);
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch order items
$itemsByOrder = [];
if (!empty($orders)) {
    $ids = implode(',', array_column($orders, 'id'));
    $itemsStmt = $pdo->query("
        SELECT oi.order_id, oi.quantity, oi.unit_price,
               p.name AS product_name, p.image_url
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id IN ($ids)
    ");
    foreach ($itemsStmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
        $itemsByOrder[$item['order_id']][] = $item;
    }
}
?>

<style>
    /* ══════════════════════════════════════════
   DASHBOARD VARIABLES
══════════════════════════════════════════ */
    :root {
        --navy: #131921;
        --navy2: #232f3e;
        --navy3: #37475a;
        --gold: #ff9900;
        --gold2: #e68900;
        --sidebar-w: 260px;
    }

    /* ══════════════════════════════════════════
   OUTER LAYOUT
══════════════════════════════════════════ */
    .db-wrap {
        display: flex;
        gap: 0;
        min-height: calc(100vh - 120px);
        background: #f0f2f5;
        font-family: 'DM Sans', sans-serif;
    }

    /* ══════════════════════════════════════════
   SIDEBAR
══════════════════════════════════════════ */
    .db-sidebar {
        width: var(--sidebar-w);
        flex-shrink: 0;
        background: var(--navy);
        display: flex;
        flex-direction: column;
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto;
        z-index: 10;
    }

    /* User card at top of sidebar */
    .db-user-card {
        padding: 28px 20px 22px;
        border-bottom: 1px solid rgba(255, 255, 255, .07);
        background: var(--navy2);
    }

    .db-avatar {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--gold), #ff6b00);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 20px;
        color: var(--navy);
        box-shadow: 0 0 0 3px rgba(255, 153, 0, .25);
        flex-shrink: 0;
        margin-bottom: 12px;
    }

    .db-user-name {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 15px;
        color: white;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .db-user-email {
        font-size: 11.5px;
        color: rgba(255, 255, 255, .45);
        margin-top: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .db-user-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(255, 153, 0, .15);
        border: 1px solid rgba(255, 153, 0, .3);
        color: var(--gold);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        padding: 3px 9px;
        border-radius: 20px;
        margin-top: 8px;
    }

    /* Nav */
    .db-nav {
        padding: 16px 12px;
        flex: 1;
    }

    .db-nav-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .3);
        padding: 0 8px;
        margin: 16px 0 6px;
    }

    .db-nav-item {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 11px 14px;
        border-radius: 10px;
        color: rgba(255, 255, 255, .6);
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all .15s;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        text-decoration: none;
        margin-bottom: 2px;
    }

    .db-nav-item i {
        width: 18px;
        text-align: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .db-nav-item:hover {
        background: rgba(255, 255, 255, .07);
        color: white;
    }

    .db-nav-item.active {
        background: linear-gradient(135deg, rgba(255, 153, 0, .2), rgba(255, 153, 0, .08));
        color: var(--gold);
        border: 1px solid rgba(255, 153, 0, .2);
        font-weight: 700;
    }

    .db-nav-item.active i {
        color: var(--gold);
    }

    .db-nav-sep {
        height: 1px;
        background: rgba(255, 255, 255, .06);
        margin: 10px 8px;
    }

    /* Sidebar bottom */
    .db-sidebar-footer {
        padding: 16px 12px;
        border-top: 1px solid rgba(255, 255, 255, .07);
    }

    .db-logout-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 10px;
        color: rgba(255, 255, 255, .4);
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all .15s;
        text-decoration: none;
        width: 100%;
        border: none;
        background: none;
    }

    .db-logout-btn:hover {
        background: rgba(239, 68, 68, .1);
        color: #f87171;
    }

    /* ══════════════════════════════════════════
   MAIN CONTENT AREA
══════════════════════════════════════════ */
    .db-main {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }

    /* Page header bar */
    .db-page-header {
        background: white;
        border-bottom: 1px solid #e5e7eb;
        padding: 18px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        position: sticky;
        top: 0;
        z-index: 5;
    }

    .db-page-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 20px;
        color: var(--navy);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .db-page-title i {
        color: var(--gold);
    }

    .db-page-subtitle {
        font-size: 13px;
        color: #6b7280;
        margin-top: 2px;
    }

    .db-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        color: #9ca3af;
    }

    .db-breadcrumb span {
        color: #374151;
        font-weight: 600;
    }

    /* Content panels */
    .db-content {
        padding: 28px 32px;
        flex: 1;
    }

    .db-panel {
        display: none;
        animation: panelIn .35s ease both;
    }

    .db-panel.active {
        display: block;
    }

    @keyframes panelIn {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ══════════════════════════════════════════
   PANEL: DASHBOARD
══════════════════════════════════════════ */

    /* Welcome banner */
    .db-welcome {
        background: linear-gradient(135deg, var(--navy) 0%, var(--navy2) 60%, #37475a 100%);
        border-radius: 20px;
        padding: 28px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .db-welcome::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255, 153, 0, .12);
        filter: blur(40px);
    }

    .db-welcome-text h2 {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 22px;
        color: white;
        line-height: 1.2;
    }

    .db-welcome-text h2 span {
        color: var(--gold);
    }

    .db-welcome-text p {
        font-size: 13.5px;
        color: rgba(255, 255, 255, .6);
        margin-top: 5px;
    }

    .db-welcome-icon {
        font-size: 52px;
        opacity: .85;
        flex-shrink: 0;
        animation: waveHi 2s ease-in-out infinite;
    }

    @keyframes waveHi {

        0%,
        100% {
            transform: rotate(0deg);
        }

        25% {
            transform: rotate(18deg);
        }

        75% {
            transform: rotate(-8deg);
        }
    }

    /* Promo slider inside dashboard */
    .db-promo-section {
        margin-bottom: 24px;
    }

    .db-promo-section h3 {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 14px;
        color: #374151;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .db-promo-section h3 i {
        color: var(--gold);
    }

    .db-promo-slider {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        height: 160px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, .1);
    }

    .db-promo-slide {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        padding: 0 28px;
        opacity: 0;
        transition: opacity .5s ease;
        pointer-events: none;
    }

    .db-promo-slide.active {
        opacity: 1;
        pointer-events: auto;
    }

    .db-promo-slide-1 {
        background: linear-gradient(135deg, #0d3b1e, #2d9e52);
    }

    .db-promo-slide-2 {
        background: linear-gradient(135deg, #1a0533, #7c3aed);
    }

    .db-promo-slide-3 {
        background: linear-gradient(135deg, #0c1445, #1d4ed8);
    }

    .db-promo-content {
        flex: 1;
    }

    .db-promo-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(255, 255, 255, .15);
        border: 1px solid rgba(255, 255, 255, .2);
        color: white;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 20px;
        margin-bottom: 8px;
    }

    .db-promo-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 20px;
        color: white;
        line-height: 1.15;
        margin-bottom: 8px;
    }

    .db-promo-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: white;
        color: var(--navy);
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 12px;
        padding: 7px 16px;
        border-radius: 8px;
        text-decoration: none;
        transition: transform .15s;
    }

    .db-promo-btn:hover {
        transform: translateY(-1px);
    }

    .db-promo-emoji {
        font-size: 56px;
        flex-shrink: 0;
        margin-left: 20px;
        opacity: .9;
    }

    .db-promo-dots {
        position: absolute;
        bottom: 12px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 6px;
    }

    .db-promo-dot {
        width: 6px;
        height: 6px;
        border-radius: 3px;
        background: rgba(255, 255, 255, .35);
        cursor: pointer;
        border: none;
        padding: 0;
        transition: width .3s, background .3s;
    }

    .db-promo-dot.active {
        width: 20px;
        background: white;
    }

    /* Stats cards */
    .db-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .db-stat-card {
        background: white;
        border-radius: 16px;
        padding: 20px 22px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform .15s, box-shadow .15s;
    }

    .db-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
    }

    .db-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .db-stat-icon.orange {
        background: #fff8e7;
        color: var(--gold);
    }

    .db-stat-icon.blue {
        background: #eff6ff;
        color: #3b82f6;
    }

    .db-stat-icon.green {
        background: #f0fdf4;
        color: #10b981;
    }

    .db-stat-num {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 28px;
        color: var(--navy);
        line-height: 1;
    }

    .db-stat-lbl {
        font-size: 12.5px;
        color: #6b7280;
        margin-top: 3px;
    }

    .db-stat-link {
        font-size: 11.5px;
        color: var(--gold);
        font-weight: 600;
        text-decoration: none;
        margin-top: 4px;
        display: inline-block;
    }

    .db-stat-link:hover {
        text-decoration: underline;
    }

    /* Quick actions */
    .db-quick h3 {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 14px;
        color: #374151;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .db-quick h3 i {
        color: var(--gold);
    }

    .db-quick-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .db-quick-btn {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        font-size: 12.5px;
        font-weight: 600;
        color: #374151;
        text-decoration: none;
        transition: all .15s;
        text-align: center;
    }

    .db-quick-btn i {
        font-size: 22px;
    }

    .db-quick-btn:hover {
        border-color: var(--gold);
        background: #fff8e7;
        color: var(--gold);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 153, 0, .15);
    }

    /* ══════════════════════════════════════════
   PANEL: MY PROFILE
══════════════════════════════════════════ */
    .db-form-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .db-form-card-header {
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .db-form-card-header i {
        color: var(--gold);
        font-size: 15px;
    }

    .db-form-card-header h3 {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 15px;
        color: var(--navy);
    }

    .db-form-card-body {
        padding: 24px;
    }

    .db-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    .db-form-row.full {
        grid-template-columns: 1fr;
    }

    .db-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .db-field label {
        font-size: 12.5px;
        font-weight: 600;
        color: #374151;
    }

    .db-field input,
    .db-field select,
    .db-field textarea {
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 14px;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        color: var(--navy);
        background: #fafafa;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
        width: 100%;
    }

    .db-field input:focus,
    .db-field select:focus,
    .db-field textarea:focus {
        border-color: var(--gold);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(255, 153, 0, .1);
    }

    .db-field textarea {
        min-height: 90px;
        resize: vertical;
    }

    .db-save-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--navy), var(--navy2));
        color: white;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 14px;
        padding: 12px 24px;
        border-radius: 11px;
        border: none;
        cursor: pointer;
        transition: transform .15s, box-shadow .15s;
        box-shadow: 0 4px 14px rgba(19, 25, 33, .25);
    }

    .db-save-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(19, 25, 33, .3);
    }

    .db-save-btn i {
        color: var(--gold);
    }

    /* Payment pref pills */
    .db-pay-options {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .db-pay-opt {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 8px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        transition: all .15s;
        background: white;
    }

    .db-pay-opt input {
        display: none;
    }

    .db-pay-opt:has(input:checked),
    .db-pay-opt.selected {
        border-color: var(--gold);
        background: #fff8e7;
        color: var(--gold);
    }

    .db-pay-opt i {
        font-size: 16px;
    }

    /* success alert */
    .db-alert-success {
        background: #f0fdf4;
        border: 1.5px solid #86efac;
        border-radius: 10px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 13.5px;
        color: #15803d;
        font-weight: 600;
        margin-bottom: 20px;
    }

    /* ══════════════════════════════════════════
   PANEL: VIEW ORDERS
══════════════════════════════════════════ */
    .db-orders-filter {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .db-filter-pill {
        padding: 7px 16px;
        border-radius: 20px;
        border: 1.5px solid #e5e7eb;
        background: white;
        font-size: 12.5px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        transition: all .15s;
    }

    .db-filter-pill.active {
        border-color: var(--gold);
        background: #fff8e7;
        color: var(--gold);
    }

    .db-filter-pill:hover {
        border-color: var(--gold);
    }

    .db-order-card {
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        padding: 18px 22px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        transition: box-shadow .15s;
    }

    .db-order-card:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
    }

    .db-order-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #fff8e7;
        color: var(--gold);
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .db-order-info {
        flex: 1;
        min-width: 0;
    }

    .db-order-id {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 14px;
        color: var(--navy);
    }

    .db-order-desc {
        font-size: 13px;
        color: #6b7280;
        margin-top: 2px;
    }

    .db-order-date {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 3px;
    }

    .db-order-amount {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 16px;
        color: var(--navy);
        flex-shrink: 0;
    }

    .db-order-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .db-order-status.delivered {
        background: #f0fdf4;
        color: #15803d;
    }

    .db-order-status.processing {
        background: #fff8e7;
        color: #92400e;
    }

    .db-order-status.shipped {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .db-order-status.cancelled {
        background: #fef2f2;
        color: #b91c1c;
    }

    .db-order-view {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 9px;
        border: 1.5px solid #e5e7eb;
        font-size: 12.5px;
        font-weight: 600;
        color: #374151;
        text-decoration: none;
        transition: all .15s;
        flex-shrink: 0;
    }

    .db-order-view:hover {
        border-color: var(--gold);
        color: var(--gold);
        background: #fff8e7;
    }

    .db-empty-state {
        text-align: center;
        padding: 48px 24px;
        background: white;
        border-radius: 16px;
        border: 1px solid #f1f5f9;
    }

    .db-empty-state .emoji {
        font-size: 48px;
        margin-bottom: 12px;
    }

    .db-empty-state h3 {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 16px;
        color: #374151;
    }

    .db-empty-state p {
        font-size: 13.5px;
        color: #9ca3af;
        margin-top: 5px;
    }

    /* ══════════════════════════════════════════
   PANEL: HELP DESK
══════════════════════════════════════════ */
    /* ── Ticket type pills ── */
    .hd-type-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 4px;
    }

    .hd-type-opt {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 7px;
        padding: 14px 10px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        cursor: pointer;
        transition: all .15s;
        background: white;
        font-size: 12.5px;
        font-weight: 600;
        color: #374151;
        text-align: center;
    }

    .hd-type-opt i {
        font-size: 20px;
    }

    .hd-type-opt input {
        display: none;
    }

    .hd-type-opt:has(input:checked) {
        border-color: var(--gold);
        background: #fff8e7;
        color: var(--navy);
    }

    .hd-type-opt:has(input:checked) i {
        color: var(--gold);
    }

    .hd-type-opt:hover {
        border-color: #d1d5db;
        background: #f9fafb;
    }

    /* ── Product rating stars ── */
    .hd-stars {
        display: flex;
        gap: 6px;
    }

    .hd-star {
        font-size: 28px;
        cursor: pointer;
        color: #e5e7eb;
        transition: color .15s, transform .15s;
        background: none;
        border: none;
        padding: 2px;
    }

    .hd-star:hover,
    .hd-star.active {
        color: #f59e0b;
        transform: scale(1.15);
    }

    /* ── Ticket list ── */
    .hd-ticket-row {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        transition: background .12s;
    }

    .hd-ticket-row:last-child {
        border-bottom: none;
    }

    .hd-ticket-row:hover {
        background: #fafcff;
    }

    .hd-ticket-icon {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .hd-ticket-info {
        flex: 1;
        min-width: 0;
    }

    .hd-ticket-subject {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 14px;
        color: var(--navy);
    }

    .hd-ticket-meta {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 3px;
    }

    .hd-ticket-desc {
        font-size: 13px;
        color: #6b7280;
        margin-top: 4px;
        line-height: 1.5;
    }

    .hd-ticket-status {
        padding: 4px 11px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .hd-ticket-status.open {
        background: #fff8e7;
        color: #92400e;
    }

    .hd-ticket-status.resolved {
        background: #f0fdf4;
        color: #15803d;
    }

    .hd-ticket-status.closed {
        background: #f1f5f9;
        color: #64748b;
    }

    /* ── Alert error ── */
    .db-alert-error {
        background: #fef2f2;
        border: 1.5px solid #fca5a5;
        border-radius: 10px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 13.5px;
        color: #b91c1c;
        font-weight: 600;
        margin-bottom: 20px;
    }

    @media (max-width: 600px) {
        .hd-type-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* ══════════════════════════════════════════
   MOBILE HAMBURGER / OVERLAY
══════════════════════════════════════════ */
    .db-mob-toggle {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 100;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: var(--navy);
        color: white;
        font-size: 18px;
        border: none;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, .3);
    }

    .db-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .55);
        z-index: 9;
        backdrop-filter: blur(3px);
    }

    .db-overlay.open {
        display: block;
    }

    /* ══════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════ */
    @media (max-width: 900px) {
        .db-sidebar {
            position: fixed;
            left: -280px;
            top: 0;
            height: 100vh;
            z-index: 10;
            transition: left .3s ease;
            box-shadow: 4px 0 24px rgba(0, 0, 0, .2);
        }

        .db-sidebar.open {
            left: 0;
        }

        .db-mob-toggle {
            display: flex;
        }

        .db-stats {
            grid-template-columns: 1fr 1fr;
        }

        .db-quick-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .db-content {
            padding: 20px 18px;
        }

        .db-page-header {
            padding: 14px 18px;
        }
    }

    @media (max-width: 600px) {
        .db-stats {
            grid-template-columns: 1fr;
        }

        .db-form-row {
            grid-template-columns: 1fr;
        }

        .db-order-card {
            gap: 12px;
        }

        .db-promo-emoji {
            display: none;
        }

        .db-welcome {
            flex-direction: column;
        }
    }
</style>

<!-- ════════════════════════════════════════════════
     DASHBOARD LAYOUT
════════════════════════════════════════════════ -->
<div class="db-wrap">

    <!-- ════════ SIDEBAR ════════ -->
    <aside class="db-sidebar" id="dbSidebar">

        <!-- User card -->
        <div class="db-user-card">
            <div class="db-avatar" id="dbAvatarLetter">K</div>
            <div class="db-user-name" id="dbSidebarName"><?= htmlspecialchars($user['full_name']) ?></div>
            <div class="db-user-email"><?= htmlspecialchars($user['email']) ?></div>
            <div class="db-user-badge"><i class="fa-regular fa-calendar" style="font-size:9px;"></i>
                <?= date('d M Y') ?></div>
        </div>

        <!-- Nav -->
        <nav class="db-nav">
            <div class="db-nav-label">Main</div>
            <button class="db-nav-item active" onclick="dbSwitch('dashboard', this)">
                <i class="fa-solid fa-gauge-high"></i> My Dashboard
            </button>
            <button class="db-nav-item" onclick="dbSwitch('profile', this)">
                <i class="fa-solid fa-user-circle"></i> My Profile
            </button>
            <button class="db-nav-item" onclick="dbSwitch('orders', this)">
                <i class="fa-solid fa-box"></i> View Orders
                <?php if ($totalOrders > 0): ?>
                    <span
                        style="margin-left:auto;background:rgba(255,153,0,.2);color:var(--gold);font-size:10px;font-weight:800;border-radius:8px;padding:2px 7px;"><?= $totalOrders ?></span>
                <?php endif; ?>
            </button>
            <button class="db-nav-item" onclick="dbSwitch('helpdesk', this)">
                <i class="fa-solid fa-headset"></i> Help Desk
            </button>

            <div class="db-nav-sep"></div>
            <div class="db-nav-label">Shop</div>
            <a class="db-nav-item" href="index.php?page=products">
                <i class="fa-solid fa-bag-shopping"></i> Browse Products
            </a>
            <a class="db-nav-item" href="index.php?page=cart">
                <i class="fa-solid fa-cart-shopping"></i> My Cart
                <?php if ($cartCount > 0): ?>
                    <span
                        style="margin-left:auto;background:rgba(255,153,0,.2);color:var(--gold);font-size:10px;font-weight:800;border-radius:8px;padding:2px 7px;"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
        </nav>

        <div class="db-sidebar-footer">
            <a href="index.php?page=logout" class="db-logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Sign Out
            </a>
        </div>
    </aside>

    <!-- Mobile overlay -->
    <div class="db-overlay" id="dbOverlay" onclick="closeSidebar()"></div>

    <!-- ════════ MAIN ════════ -->
    <div class="db-main">

        <!-- Page header -->
        <div class="db-page-header">
            <div>
                <div class="db-page-title">
                    <i class="fa-solid fa-gauge-high" id="dbPageIcon"></i>
                    <span id="dbPageTitle">My Dashboard</span>
                </div>
                <div class="db-page-subtitle" id="dbPageSub">Welcome back! Here's your account overview.</div>
            </div>
            <div class="db-breadcrumb">
                <i class="fa-solid fa-house" style="font-size:11px;"></i>
                <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i>
                <span id="dbBreadcrumb">Dashboard</span>
            </div>
        </div>

        <!-- ════ CONTENT ════ -->
        <div class="db-content">

            <!-- ══ PANEL: DASHBOARD ══ -->
            <div class="db-panel active" id="panel-dashboard">

                <!-- Welcome card -->
                <div class="db-welcome">
                    <div class="db-welcome-text">
                        <h2>Hi, <span><?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?></span>! 👋</h2>
                        <p>Great to see you back. Here's what's happening with your account today.</p>
                    </div>
                    <div class="db-welcome-icon">🛍️</div>
                </div>

                <!-- Active Promotions Slider -->
                <?php if (!empty($activePromos)): ?>
                    <div class="db-promo-section">
                        <h3><i class="fa-solid fa-fire"></i> Active Promotions</h3>
                        <div class="db-promo-slider" id="dbPromo">

                            <?php foreach ($activePromos as $i => $promo):
                                $themeClass = match ($promo['slide_theme']) {
                                    'theme-1' => 'background:linear-gradient(135deg,#3730a3,#4f46e5)',
                                    'theme-2' => 'background:linear-gradient(135deg,#0369a1,#0ea5e9)',
                                    'theme-3' => 'background:linear-gradient(135deg,#6d28d9,#7c3aed)',
                                    'theme-4' => 'background:linear-gradient(135deg,#065f46,#10b981)',
                                    'theme-5' => 'background:linear-gradient(135deg,#b45309,#f59e0b)',
                                    default => 'background:linear-gradient(135deg,#1e293b,#334155)',
                                };
                                ?>
                                <div class="db-promo-slide <?= $i === 0 ? 'active' : '' ?>" style="<?= $themeClass ?>">
                                    <div class="db-promo-content">
                                        <div class="db-promo-tag">
                                            <i class="<?= htmlspecialchars($promo['tag_icon']) ?>" style="font-size:9px;"></i>
                                            <?= htmlspecialchars($promo['tag_label']) ?>
                                        </div>
                                        <div class="db-promo-title"><?= htmlspecialchars($promo['title']) ?></div>
                                        <div style="font-size:13px;color:rgba(255,255,255,.75);margin-bottom:10px;">
                                            <?= htmlspecialchars($promo['discount_text']) ?>
                                        </div>
                                        <a href="<?= htmlspecialchars($promo['link_url']) ?>" class="db-promo-btn">
                                            <i class="<?= htmlspecialchars($promo['btn_icon']) ?>"></i>
                                            <?= htmlspecialchars($promo['btn_label']) ?>
                                        </a>
                                    </div>
                                    <div class="db-promo-emoji"><?= htmlspecialchars($promo['emoji']) ?></div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Dots -->
                            <?php if (count($activePromos) > 1): ?>
                                <div class="db-promo-dots">
                                    <?php foreach ($activePromos as $i => $promo): ?>
                                        <button class="db-promo-dot <?= $i === 0 ? 'active' : '' ?>"
                                            onclick="dbPromoGo(<?= $i ?>)"></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="db-stats">
                    <div class="db-stat-card">
                        <div class="db-stat-icon orange"><i class="fa-solid fa-box"></i></div>
                        <div>
                            <div class="db-stat-num"><?= $totalOrders ?></div>
                            <div class="db-stat-lbl">Total Orders</div>
                            <a href="#" onclick="dbSwitch('orders',null);return false;" class="db-stat-link">View all
                                →</a>
                        </div>
                    </div>
                    <div class="db-stat-card">
                        <div class="db-stat-icon blue"><i class="fa-solid fa-heart"></i></div>
                        <div>
                            <div class="db-stat-num"><?= $wishlistCount ?></div>
                            <div class="db-stat-lbl">Wishlist Items</div>
                            <a href="index.php?page=wishlist" class="db-stat-link">View wishlist →</a>
                        </div>
                    </div>

                    <?php

                    $openTickets = 0;

                    try {
                        $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM support_tickets
        WHERE user_id = ? AND status = 'Open'
    ");
                        $stmt->execute([$userId]);
                        $openTickets = $stmt->fetchColumn();
                    } catch (Exception $e) {
                        $openTickets = 0;
                    }

                    ?>

                    <div class="db-stat-card">
                        <div class="db-stat-icon green"><i class="fa-solid fa-ticket"></i></div>
                        <div>
                            <div class="db-stat-num">
                                <?php if (!empty($myTickets)): ?>    <?= count($myTickets) ?><?php endif; ?></div>
                            <div class="db-stat-lbl">Open Tickets</div>
                            <a href="#" onclick="dbSwitch('helpdesk',null);return false;" class="db-stat-link">View
                                tickets →</a>
                        </div>
                    </div>
                </div>




                <!-- Quick actions -->
                <div class="db-quick">
                    <h3><i class="fa-solid fa-bolt"></i> Quick Actions</h3>
                    <div class="db-quick-grid">
                        <a href="index.php?page=products" class="db-quick-btn">
                            <i class="fa-solid fa-bag-shopping" style="color:var(--gold);"></i>Shop Now
                        </a>
                        <button class="db-quick-btn" onclick="dbSwitch('profile',null)">
                            <i class="fa-solid fa-user-pen" style="color:#3b82f6;"></i>Edit Profile
                        </button>
                        <button class="db-quick-btn" onclick="dbSwitch('orders',null)">
                            <i class="fa-solid fa-list-check" style="color:#10b981;"></i>My Orders
                        </button>
                        <button class="db-quick-btn" onclick="dbSwitch('helpdesk',null)">
                            <i class="fa-solid fa-headset" style="color:#8b5cf6;"></i>Get Help
                        </button>
                    </div>
                </div>

            </div><!-- /panel-dashboard -->

            <!-- ══ PANEL: MY PROFILE ══ -->
            <div class="db-panel" id="panel-profile">

                <?php if ($profileSuccess): ?>
                    <div class="db-alert-success">
                        <i class="fa-solid fa-circle-check"></i> Profile updated successfully!
                    </div>
                <?php endif; ?>

                <form method="post">

                    <!-- Personal Info -->
                    <div class="db-form-card">
                        <div class="db-form-card-header">
                            <i class="fa-solid fa-user"></i>
                            <h3>Personal Information</h3>
                        </div>
                        <div class="db-form-card-body">
                            <div class="db-form-row">
                                <div class="db-field">
                                    <label>Full Name</label>
                                    <input type="text" name="profile_name"
                                        value="<?= htmlspecialchars($user['full_name']) ?>"
                                        placeholder="Your full name">
                                </div>
                                <div class="db-field">
                                    <label>Email Address</label>
                                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>"
                                        placeholder="Email" disabled style="opacity:.55;cursor:not-allowed;">
                                </div>
                            </div>
                            <div class="db-form-row">
                                <div class="db-field">
                                    <label>Phone Number</label>
                                    <input type="tel" name="profile_phone"
                                        value="<?= htmlspecialchars($user['phone']) ?>" placeholder="+94 77 000 0000">
                                </div>
                                <div class="db-field">
                                    <label>Date of Birth</label>
                                    <input type="date" name="profile_dob">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="db-form-card">
                        <div class="db-form-card-header">
                            <i class="fa-solid fa-location-dot"></i>
                            <h3>Shipping Address</h3>
                        </div>
                        <div class="db-form-card-body">
                            <div class="db-form-row full">
                                <div class="db-field">
                                    <label>Street Address</label>
                                    <input type="text" name="profile_address"
                                        value="<?= htmlspecialchars($user['address']) ?>" placeholder="Street address">
                                </div>
                            </div>
                            <div class="db-form-row">
                                <div class="db-field">
                                    <label>City</label>
                                    <input type="text" name="profile_city"
                                        value="<?= htmlspecialchars($user['city']) ?>" placeholder="City">
                                </div>
                                <div class="db-field">
                                    <label>Postal Code</label>
                                    <input type="text" name="profile_postal"
                                        value="<?= htmlspecialchars($user['postal_code']) ?>" placeholder="Postal code">
                                </div>
                            </div>
                            <div class="db-form-row">
                                <div class="db-field">
                                    <label>Province</label>
                                    <select name="profile_province">
                                        <option>Western Province</option>
                                        <option>Central Province</option>
                                        <option>Southern Province</option>
                                        <option>Northern Province</option>
                                        <option>Eastern Province</option>
                                        <option>North Western Province</option>
                                        <option>North Central Province</option>
                                        <option>Uva Province</option>
                                        <option>Sabaragamuwa Province</option>
                                    </select>
                                </div>
                                <div class="db-field">
                                    <label>Country</label>
                                    <input type="text" value="Sri Lanka" disabled
                                        style="opacity:.55;cursor:not-allowed;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Details -->
                    <div class="db-form-card">
                        <div class="db-form-card-header">
                            <i class="fa-solid fa-address-card"></i>
                            <h3>Contact Details</h3>
                        </div>
                        <div class="db-form-card-body">
                            <div class="db-form-row">
                                <div class="db-field">
                                    <label>WhatsApp Number</label>
                                    <input type="tel" name="profile_whatsapp" placeholder="+94 77 000 0000">
                                </div>
                                <div class="db-field">
                                    <label>Alternate Phone</label>
                                    <input type="tel" name="profile_alt_phone" placeholder="+94 11 000 0000">
                                </div>
                            </div>
                            <div class="db-form-row full">
                                <div class="db-field">
                                    <label>Delivery Instructions (optional)</label>
                                    <textarea name="profile_notes"
                                        placeholder="E.g. Leave at gate, call before delivery..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Preferences -->
                    <div class="db-form-card">
                        <div class="db-form-card-header">
                            <i class="fa-solid fa-credit-card"></i>
                            <h3>Payment Preferences</h3>
                        </div>
                        <div class="db-form-card-body">
                            <p style="font-size:13px;color:#6b7280;margin-bottom:14px;">Select your preferred payment
                                method for checkout.</p>
                            <div class="db-pay-options" id="payOpts">
                                <label class="db-pay-opt selected">
                                    <input type="radio" name="payment_pref" value="cod" checked>
                                    <i class="fa-solid fa-money-bill-wave" style="color:#10b981;"></i> Cash on Delivery
                                </label>
                                <label class="db-pay-opt">
                                    <input type="radio" name="payment_pref" value="card">
                                    <i class="fa-solid fa-credit-card" style="color:#3b82f6;"></i> Debit / Credit Card
                                </label>
                                <label class="db-pay-opt">
                                    <input type="radio" name="payment_pref" value="paypal">
                                    <i class="fa-brands fa-paypal" style="color:#003087;"></i> PayPal
                                </label>
                                <label class="db-pay-opt">
                                    <input type="radio" name="payment_pref" value="bank">
                                    <i class="fa-solid fa-building-columns" style="color:#f59e0b;"></i> Bank Transfer
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="db-save-btn">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>

                </form>
            </div><!-- /panel-profile -->

            <!-- ══ PANEL: VIEW ORDERS ══ -->
            <!-- ══ PANEL: VIEW ORDERS ══ -->
            <div class="db-panel" id="panel-orders">

                <?php if (empty($orders)): ?>
                    <div class="db-empty-state">
                        <div class="emoji">📦</div>
                        <h3>No orders yet</h3>
                        <p>You haven't placed any orders. Start shopping!</p>
                        <a href="index.php?page=products"
                            style="display:inline-flex;align-items:center;gap:7px;margin-top:16px;background:var(--gold);color:var(--navy);font-family:'Outfit',sans-serif;font-weight:700;font-size:13px;padding:10px 22px;border-radius:10px;text-decoration:none;">
                            <i class="fa-solid fa-bag-shopping"></i> Browse Products
                        </a>
                    </div>
                <?php else: ?>

                    <!-- Orders filter pills -->
                    <div class="db-orders-filter">
                        <button class="db-filter-pill active" onclick="dbFilterOrders('all',this)">All Orders</button>
                        <button class="db-filter-pill" onclick="dbFilterOrders('pending',this)">Pending</button>
                        <button class="db-filter-pill" onclick="dbFilterOrders('processing',this)">Processing</button>
                        <button class="db-filter-pill" onclick="dbFilterOrders('shipped',this)">Shipped</button>
                        <button class="db-filter-pill" onclick="dbFilterOrders('delivered',this)">Delivered</button>
                        <button class="db-filter-pill" onclick="dbFilterOrders('cancelled',this)">Cancelled</button>
                    </div>

                    <!-- Orders table -->
                    <div class="db-form-card" style="overflow:hidden;">
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;">
                                <thead>
                                    <tr style="background:linear-gradient(135deg,var(--navy),var(--navy2));">
                                        <th
                                            style="padding:14px 18px;text-align:left;font-family:'Outfit',sans-serif;font-weight:700;font-size:12px;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.05em;">
                                            Order</th>
                                        <th
                                            style="padding:14px 18px;text-align:left;font-family:'Outfit',sans-serif;font-weight:700;font-size:12px;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.05em;">
                                            Total</th>

                                        <th
                                            style="padding:14px 18px;text-align:left;font-family:'Outfit',sans-serif;font-weight:700;font-size:12px;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.05em;">
                                            Status</th>
                                        <th
                                            style="padding:14px 18px;text-align:left;font-family:'Outfit',sans-serif;font-weight:700;font-size:12px;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.05em;">
                                            Date</th>
                                        <th
                                            style="padding:14px 18px;text-align:right;font-family:'Outfit',sans-serif;font-weight:700;font-size:12px;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.05em;">
                                            Invoice</th>
                                    </tr>
                                </thead>
                                <tbody id="dbOrdersTableBody">
                                    <?php foreach ($orders as $o):
                                        $st = $o['order_status'] ?? 'Pending';
                                        $items = $itemsByOrder[$o['id']] ?? [];
                                        $firstItem = $items[0]['product_name'] ?? 'Order items';
                                        $extra = count($items) - 1;
                                        $desc = $firstItem . ($extra > 0 ? " +{$extra} more" : '');
                                        $orderNum = $o['order_number'] ?? ('#RH-' . str_pad($o['id'], 6, '0', STR_PAD_LEFT));
                                        $statusLower = strtolower($st);

                                        $statusStyle = match ($statusLower) {
                                            'delivered' => 'background:#f0fdf4;color:#15803d;',
                                            'shipped' => 'background:#eff6ff;color:#1d4ed8;',
                                            'cancelled' => 'background:#fef2f2;color:#b91c1c;',
                                            'pending' => 'background:#fdf4ff;color:#7e22ce;',
                                            default => 'background:#fff8e7;color:#92400e;',
                                        };

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
                                                'unit_price' => $i['unit_price'] ?? 0,
                                            ], $items),
                                        ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <tr data-status="<?= $statusLower ?>" onclick='openTrackModal(<?= $payload ?>)'
                                            style="border-bottom:1px solid #f1f5f9;cursor:pointer;transition:background .12s;"
                                            onmouseover="this.style.background='#fffbf2'"
                                            onmouseout="this.style.background='white'">
                                            <td style="padding:14px 18px;">
                                                <div
                                                    style="font-family:'Outfit',sans-serif;font-weight:700;font-size:13.5px;color:var(--navy);">
                                                    <?= htmlspecialchars($orderNum) ?>
                                                </div>
                                                <div style="font-size:12px;color:#9ca3af;margin-top:2px;">
                                                    <?= htmlspecialchars($desc) ?>
                                                </div>
                                            </td>
                                            <td
                                                style="padding:14px 18px;font-family:'Outfit',sans-serif;font-weight:700;font-size:14px;color:var(--navy);">
                                                <?= formatCurrency($o['total_amount']) ?>
                                            </td>

                                            <td style="padding:14px 18px;">
                                                <span
                                                    style="display:inline-flex;align-items:center;padding:5px 12px;border-radius:20px;font-size:11.5px;font-weight:700;<?= $statusStyle ?>">
                                                    <?= htmlspecialchars($st) ?>
                                                </span>
                                            </td>
                                            <td style="padding:14px 18px;font-size:13px;color:#6b7280;">
                                                <?= date('d M Y', strtotime($o['created_at'])) ?>
                                            </td>
                                            <td style="padding:14px 18px;text-align:right;" onclick="event.stopPropagation()">
                                                <a href="index.php?page=invoice&id=<?= (int) $o['id'] ?>"
                                                    style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:12px;font-weight:700;color:var(--gold);text-decoration:none;transition:all .15s;"
                                                    onmouseover="this.style.borderColor='var(--gold)';this.style.background='#fff8e7'"
                                                    onmouseout="this.style.borderColor='#e5e7eb';this.style.background='white'">
                                                    <i class="fa-solid fa-file-pdf"></i> PDF
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php endif; ?>

            </div><!-- /panel-orders --><!-- /panel-orders -->


            <!-- ══ PANEL: HELP DESK ══ -->
            <div class="db-panel" id="panel-helpdesk">

                <!-- Intro banner -->


                <?php if ($ticketSuccess): ?>
                    <div class="db-alert-success" style="margin-bottom:20px;">
                        <i class="fa-solid fa-circle-check"></i>
                        Your ticket has been submitted successfully! We'll respond within 24 hours.
                    </div>
                <?php endif; ?>

                <?php if ($ticketError): ?>
                    <div class="db-alert-error" style="margin-bottom:20px;">
                        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($ticketError) ?>
                    </div>
                <?php endif; ?>

                <!-- ── SUBMIT TICKET FORM ── -->
                <div class="db-form-card">
                    <div class="db-form-card-header">
                        <i class="fa-solid fa-ticket"></i>
                        <h3>Submit a Support Ticket</h3>
                    </div>
                    <div class="db-form-card-body">
                        <form method="post">

                            <!-- Ticket Type — visual selector -->
                            <div class="db-field" style="margin-bottom:20px;">
                                <label style="margin-bottom:10px;display:block;">
                                    Ticket Type <span style="color:#ef4444;">*</span>
                                </label>
                                <div class="hd-type-grid">
                                    <label class="hd-type-opt">
                                        <input type="radio" name="ticket_type" value="Inquiry" required>
                                        <i class="fa-solid fa-circle-question" style="color:#3b82f6;"></i>
                                        Inquiry
                                    </label>
                                    <label class="hd-type-opt">
                                        <input type="radio" name="ticket_type" value="Complaint">
                                        <i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;"></i>
                                        Complaint
                                    </label>
                                    <label class="hd-type-opt">
                                        <input type="radio" name="ticket_type" value="Return Request">
                                        <i class="fa-solid fa-rotate-left" style="color:#8b5cf6;"></i>
                                        Return Request
                                    </label>
                                    <label class="hd-type-opt">
                                        <input type="radio" name="ticket_type" value="Refund Request">
                                        <i class="fa-solid fa-money-bill-transfer" style="color:#10b981;"></i>
                                        Refund Request
                                    </label>
                                    <label class="hd-type-opt">
                                        <input type="radio" name="ticket_type" value="Feedback">
                                        <i class="fa-solid fa-star" style="color:#f59e0b;"></i>
                                        Feedback
                                    </label>
                                    <label class="hd-type-opt">
                                        <input type="radio" name="ticket_type" value="Other">
                                        <i class="fa-solid fa-ellipsis" style="color:#6b7280;"></i>
                                        Other
                                    </label>
                                </div>
                            </div>

                            <!-- Subject -->
                            <div class="db-form-row full" style="margin-bottom:16px;">
                                <div class="db-field">
                                    <label>Subject <span style="color:#ef4444;">*</span></label>
                                    <input type="text" name="ticket_subject" required
                                        placeholder="e.g. Wrong item received, Late delivery, Product feedback..."
                                        value="<?= htmlspecialchars($_POST['ticket_subject'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- Product Rating (optional) -->
                            <div class="db-field" style="margin-bottom:16px;">
                                <label>Rate Your Experience (optional)</label>
                                <div style="display:flex;align-items:center;gap:16px;margin-top:4px;">
                                    <div class="hd-stars" id="hdStars">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <button type="button" class="hd-star" data-val="<?= $s ?>"
                                                onclick="hdRate(<?= $s ?>)" onmouseover="hdHover(<?= $s ?>)"
                                                onmouseout="hdUnhover()">
                                                <i class="fa-solid fa-star"></i>
                                            </button>
                                        <?php endfor; ?>
                                    </div>
                                    <span id="hdRateLabel" style="font-size:13px;color:#9ca3af;"></span>
                                </div>
                                <input type="hidden" name="rating" id="hdRatingInput" value="">
                            </div>

                            <!-- Description -->
                            <div class="db-form-row full" style="margin-bottom:20px;">
                                <div class="db-field">
                                    <label>Description <span style="color:#ef4444;">*</span></label>
                                    <textarea name="ticket_description" required
                                        placeholder="Please describe your issue, feedback, or request in detail. Include order numbers, product names, or any relevant information..."
                                        style="min-height:130px;"><?= htmlspecialchars($_POST['ticket_description'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <button type="submit" class="db-save-btn">
                                <i class="fa-solid fa-paper-plane"></i> Submit Ticket
                            </button>

                        </form>
                    </div>
                </div>

                <!-- ── MY TICKETS ── -->
                <div class="db-form-card">
                    <div class="db-form-card-header">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <h3>My Tickets
                            <?php if (!empty($myTickets)): ?>
                                <span
                                    style="margin-left:8px;background:#f1f5f9;color:#64748b;font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;"><?= count($myTickets) ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>

                    <?php if (empty($myTickets)): ?>
                        <div style="padding:36px 24px;text-align:center;">
                            <div style="font-size:40px;margin-bottom:10px;">🎫</div>
                            <div style="font-family:'Outfit',sans-serif;font-weight:700;font-size:15px;color:#374151;">No
                                tickets yet</div>
                            <div style="font-size:13px;color:#9ca3af;margin-top:4px;">Submit a ticket above if you need
                                help.</div>
                        </div>

                    <?php else: ?>
                        <div style="padding:0;">
                            <?php foreach ($myTickets as $tk):
                                $tkStatus = ucfirst(strtolower($tk['status'] ?? 'open'));
                                $tkStatusCls = match (strtolower($tk['status'] ?? '')) {
                                    'resolved' => 'resolved',
                                    'closed' => 'closed',
                                    default => 'open',
                                };
                                $tkIconBg = match (strtolower($tk['status'] ?? '')) {
                                    'resolved' => 'background:#f0fdf4;color:#10b981;',
                                    'closed' => 'background:#f1f5f9;color:#64748b;',
                                    default => 'background:#fff8e7;color:#f59e0b;',
                                };
                                $tkTypeIcon = match ($tk['ticket_type'] ?? '') {
                                    'Inquiry' => 'fa-circle-question',
                                    'Complaint' => 'fa-triangle-exclamation',
                                    'Return Request' => 'fa-rotate-left',
                                    'Refund Request' => 'fa-money-bill-transfer',
                                    'Feedback' => 'fa-star',
                                    default => 'fa-ticket',
                                };
                                ?>
                                <div class="hd-ticket-row">
                                    <div class="hd-ticket-icon" style="<?= $tkIconBg ?>">
                                        <i class="fa-solid <?= $tkTypeIcon ?>"></i>
                                    </div>
                                    <div class="hd-ticket-info">
                                        <div class="hd-ticket-subject"><?= htmlspecialchars($tk['subject']) ?></div>
                                        <div class="hd-ticket-meta">
                                            <span
                                                style="background:#f1f5f9;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600;color:#475569;"><?= htmlspecialchars($tk['ticket_type'] ?? 'General') ?></span>
                                            &nbsp;·&nbsp;
                                            <i class="fa-regular fa-calendar" style="margin-right:3px;"></i>
                                            <?= date('d M Y, h:i A', strtotime($tk['created_at'])) ?>
                                            &nbsp;·&nbsp;
                                            Ticket #<?= str_pad($tk['id'], 4, '0', STR_PAD_LEFT) ?>
                                        </div>
                                        <div class="hd-ticket-desc">
                                            <?= htmlspecialchars(mb_substr($tk['description'], 0, 120)) ?>        <?= strlen($tk['description']) > 120 ? '…' : '' ?>
                                        </div>
                                    </div>
                                    <span class="hd-ticket-status <?= $tkStatusCls ?>"><?= $tkStatus ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div><!-- /panel-helpdesk -->

        </div><!-- /db-content -->
    </div><!-- /db-main -->
</div><!-- /db-wrap -->

<!-- Mobile toggle button -->
<button class="db-mob-toggle" id="dbMobToggle" onclick="toggleSidebar()" aria-label="Open menu">
    <i class="fa-solid fa-bars"></i>
</button>

<script>
    // ── Panel switcher ──
    const pageInfo = {
        dashboard: { title: 'My Dashboard', sub: 'Welcome back! Here\'s your account overview.', icon: 'fa-gauge-high', bc: 'Dashboard' },
        profile: { title: 'My Profile', sub: 'Manage your personal information and preferences.', icon: 'fa-user-circle', bc: 'My Profile' },
        orders: { title: 'View Orders', sub: 'Track and manage all your purchases.', icon: 'fa-box', bc: 'Orders' },
        helpdesk: { title: 'Help Desk', sub: 'Submit an inquiry, complaint, or return request.', icon: 'fa-headset', bc: 'Help Desk' },
    };

    function dbSwitch(tab, btn) {
        // panels
        document.querySelectorAll('.db-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('panel-' + tab)?.classList.add('active');

        // nav items
        document.querySelectorAll('.db-nav-item').forEach(n => n.classList.remove('active'));
        if (btn) btn.classList.add('active');
        else {
            document.querySelectorAll('.db-nav-item').forEach(n => {
                if (n.textContent.toLowerCase().includes(tab.replace('dashboard', 'dashboard').replace('helpdesk', 'help'))) n.classList.add('active');
            });
        }

        // page header
        const info = pageInfo[tab] || {};
        document.getElementById('dbPageTitle').textContent = info.title || tab;
        document.getElementById('dbPageSub').textContent = info.sub || '';
        document.getElementById('dbPageIcon').className = 'fa-solid ' + (info.icon || 'fa-circle');
        document.getElementById('dbBreadcrumb').textContent = info.bc || tab;

        // close mobile sidebar
        closeSidebar();

        // scroll top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── Sidebar mobile toggle ──
    function toggleSidebar() {
        const sb = document.getElementById('dbSidebar');
        const ov = document.getElementById('dbOverlay');
        sb.classList.toggle('open');
        ov.classList.toggle('open');
    }
    function closeSidebar() {
        document.getElementById('dbSidebar')?.classList.remove('open');
        document.getElementById('dbOverlay')?.classList.remove('open');
    }

    // ── Promo mini slider ──
    (function () {
        const slides = document.querySelectorAll('.db-promo-slide');
        const dots = document.querySelectorAll('.db-promo-dot');
        if (!slides.length) return;
        let cur = 0, timer;
        function go(i) {
            slides[cur].classList.remove('active');
            if (dots[cur]) dots[cur].classList.remove('active');
            cur = (i + slides.length) % slides.length;
            slides[cur].classList.add('active');
            if (dots[cur]) dots[cur].classList.add('active');
        }
        window.dbPromoGo = go;
        if (slides.length > 1) {
            timer = setInterval(() => go(cur + 1), 4000);
            document.getElementById('dbPromo')?.addEventListener('mouseenter', () => clearInterval(timer));
            document.getElementById('dbPromo')?.addEventListener('mouseleave', () => {
                timer = setInterval(() => go(cur + 1), 4000);
            });
        }
    })();


    // ── Payment pref toggle ──
    document.querySelectorAll('.db-pay-opt').forEach(opt => {
        opt.addEventListener('click', () => {
            document.querySelectorAll('.db-pay-opt').forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
        });
    });

    // ── Order filter pills ──
    document.querySelectorAll('.db-filter-pill').forEach(pill => {
        pill.addEventListener('click', () => {
            document.querySelectorAll('.db-filter-pill').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
        });
    });

    // ── Avatar initial ──
    const name = document.getElementById('dbSidebarName')?.textContent || '';
    const av = document.getElementById('dbAvatarLetter');
    if (av && name) av.textContent = name.trim()[0].toUpperCase();

    // ── Handle URL hash for direct tab linking ──
    const hash = location.hash.replace('#', '');
    if (['profile', 'orders', 'helpdesk'].includes(hash)) {
        const btn = [...document.querySelectorAll('.db-nav-item')].find(b => b.textContent.toLowerCase().includes(hash.replace('helpdesk', 'help')));
        dbSwitch(hash, btn || null);
    }

    function dbFilterOrders(status, btn) {
        document.querySelectorAll('.db-filter-pill').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('#dbOrdersTableBody tr').forEach(row => {
            row.style.display = (status === 'all' || row.dataset.status === status.toLowerCase()) ? '' : 'none';
        });
    }

    // ── Star rating ──
    const rateLabels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
    let currentRating = 0;

    function hdRate(val) {
        currentRating = val;
        document.getElementById('hdRatingInput').value = val;
        document.getElementById('hdRateLabel').textContent = rateLabels[val];
        document.getElementById('hdRateLabel').style.color = '#f59e0b';
        renderStars(val);
    }
    function hdHover(val) { renderStars(val); }
    function hdUnhover() { renderStars(currentRating); }
    function renderStars(val) {
        document.querySelectorAll('.hd-star').forEach((s, i) => {
            s.classList.toggle('active', i < val);
        });
    }
</script>