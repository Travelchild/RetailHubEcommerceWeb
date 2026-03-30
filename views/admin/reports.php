<?php
// ── EARLY CSV EXPORT (must run before ANY output) ─────────────────────────
if (isset($_GET['export'])) {
    $db = Database::connection();

    $range = $_GET['range'] ?? '30';
    $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');

    if ($range !== 'custom') {
        $days = (int) $range;
        $dateFrom = date('Y-m-d', strtotime("-{$days} days"));
        $dateTo = date('Y-m-d');
    }

    $fromDT = $dateFrom . ' 00:00:00';
    $toDT = $dateTo . ' 23:59:59';

    $exportType = $_GET['export'];

    $rq = function (PDO $db, string $sql, array $p = []): array {
        $s = $db->prepare($sql);
        $s->execute($p);
        return $s->fetchAll(PDO::FETCH_ASSOC);
    };

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="report_' . $exportType . '_' . date('Ymd') . '.csv"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

    if ($exportType === 'sales') {
        fputcsv($out, ['Date', 'Revenue (Rs)', 'Orders']);
        $rows = $rq(
            $db,
            "SELECT DATE(created_at) AS day,
                    COALESCE(SUM(total_amount),0) AS revenue,
                    COUNT(*) AS orders
             FROM orders
             WHERE order_status != 'Cancelled' AND created_at BETWEEN ? AND ?
             GROUP BY DATE(created_at) ORDER BY day ASC",
            [$fromDT, $toDT]
        );
        foreach ($rows as $r)
            fputcsv($out, [$r['day'], $r['revenue'], $r['orders']]);

    } elseif ($exportType === 'products') {
        fputcsv($out, ['Product', 'Brand', 'Qty Sold', 'Revenue (Rs)', 'Stock Remaining']);
        $rows = $rq(
            $db,
            "SELECT p.name, p.brand,
                    SUM(oi.quantity) AS qty_sold,
                    SUM(oi.quantity * oi.unit_price) AS revenue,
                    p.stock_qty AS stock
             FROM order_items oi
             JOIN products p ON p.id = oi.product_id
             JOIN orders o   ON o.id = oi.order_id
             WHERE o.order_status != 'Cancelled' AND o.created_at BETWEEN ? AND ?
             GROUP BY oi.product_id
             ORDER BY revenue DESC LIMIT 100",
            [$fromDT, $toDT]
        );
        foreach ($rows as $r)
            fputcsv($out, [$r['name'], $r['brand'], $r['qty_sold'], $r['revenue'], $r['stock']]);

    } elseif ($exportType === 'customers') {
        fputcsv($out, ['Name', 'Email', 'Orders', 'Total Spend (Rs)', 'Last Order']);
        $rows = $rq(
            $db,
            "SELECT u.full_name AS name, u.email,
                    COUNT(o.id) AS orders,
                    COALESCE(SUM(o.total_amount),0) AS spend,
                    MAX(o.created_at) AS last_order
             FROM users u
             JOIN orders o ON o.user_id = u.id
             WHERE o.order_status != 'Cancelled' AND o.created_at BETWEEN ? AND ?
             GROUP BY u.id
             ORDER BY spend DESC LIMIT 100",
            [$fromDT, $toDT]
        );
        foreach ($rows as $r)
            fputcsv($out, [$r['name'], $r['email'], $r['orders'], $r['spend'], $r['last_order']]);
    }

    fclose($out);
    exit;
}
// ── END EARLY EXPORT ───────────────────────────────────────────────────────

// views/admin/reports.php
$section = 'reports';
$db = Database::connection();

// ── Date range filter ─────────────────────────────────────────────────────
$range = $_GET['range'] ?? '30';
$dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$dateTo = $_GET['date_to'] ?? date('Y-m-d');
$reportTab = $_GET['tab'] ?? 'sales';

if ($range !== 'custom') {
    $days = (int) $range;
    $dateFrom = date('Y-m-d', strtotime("-{$days} days"));
    $dateTo = date('Y-m-d');
}

$fromDT = $dateFrom . ' 00:00:00';
$toDT = $dateTo . ' 23:59:59';

// ── Previous period (for % change) ───────────────────────────────────────
$periodDays = (int) ceil((strtotime($dateTo) - strtotime($dateFrom)) / 86400) + 1;
$prevFrom = date('Y-m-d H:i:s', strtotime($fromDT) - ($periodDays * 86400));
$prevTo = date('Y-m-d H:i:s', strtotime($fromDT) - 1);

// ── Helpers ───────────────────────────────────────────────────────────────
if (!function_exists('rq')) {
    function rq(PDO $db, string $sql, array $p = []): array
    {
        $s = $db->prepare($sql);
        $s->execute($p);
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }
}
if (!function_exists('rqVal')) {
    function rqVal(PDO $db, string $sql, array $p = [])
    {
        $s = $db->prepare($sql);
        $s->execute($p);
        return $s->fetchColumn();
    }
}
if (!function_exists('pct')) {
    function pct(float $now, float $prev): string
    {
        if ($prev == 0)
            return $now > 0 ? '+100%' : '0%';
        $p = round((($now - $prev) / $prev) * 100, 1);
        return ($p >= 0 ? '+' : '') . $p . '%';
    }
}
if (!function_exists('pctClass')) {
    function pctClass(float $now, float $prev): string
    {
        if ($prev == 0)
            return $now > 0 ? 'up' : 'neutral';
        return $now >= $prev ? 'up' : 'down';
    }
}

// ════════════════════════════════════════════════════════
//  SALES DATA
// ════════════════════════════════════════════════════════

$totalRevenue = (float) rqVal(
    $db,
    "SELECT COALESCE(SUM(total_amount),0) FROM orders
     WHERE order_status != 'Cancelled' AND created_at BETWEEN ? AND ?",
    [$fromDT, $toDT]
);

$prevRevenue = (float) rqVal(
    $db,
    "SELECT COALESCE(SUM(total_amount),0) FROM orders
     WHERE order_status != 'Cancelled' AND created_at BETWEEN ? AND ?",
    [$prevFrom, $prevTo]
);

$totalOrders = (int) rqVal(
    $db,
    "SELECT COUNT(*) FROM orders WHERE created_at BETWEEN ? AND ?",
    [$fromDT, $toDT]
);

$prevOrders = (int) rqVal(
    $db,
    "SELECT COUNT(*) FROM orders WHERE created_at BETWEEN ? AND ?",
    [$prevFrom, $prevTo]
);

$deliveredOrders = (int) rqVal(
    $db,
    "SELECT COUNT(*) FROM orders WHERE order_status='Delivered'  AND created_at BETWEEN ? AND ?",
    [$fromDT, $toDT]
);
$processingOrders = (int) rqVal(
    $db,
    "SELECT COUNT(*) FROM orders WHERE order_status='Processing' AND created_at BETWEEN ? AND ?",
    [$fromDT, $toDT]
);
$cancelledOrders = (int) rqVal(
    $db,
    "SELECT COUNT(*) FROM orders WHERE order_status='Cancelled'  AND created_at BETWEEN ? AND ?",
    [$fromDT, $toDT]
);

$paidOrders = (int) rqVal(
    $db,
    "SELECT COUNT(*) FROM orders WHERE payment_status='Paid' AND created_at BETWEEN ? AND ?",
    [$fromDT, $toDT]
);
$codOrders = (int) rqVal(
    $db,
    "SELECT COUNT(*) FROM orders WHERE (payment_status LIKE '%COD%' OR payment_status LIKE '%Pending%')
     AND created_at BETWEEN ? AND ?",
    [$fromDT, $toDT]
);

$avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;
$prevAvg = $prevOrders > 0 ? round($prevRevenue / $prevOrders, 2) : 0;

$dailyRevenue = rq(
    $db,
    "SELECT DATE(created_at) AS day,
            COALESCE(SUM(total_amount),0) AS revenue,
            COUNT(*) AS orders
     FROM orders
     WHERE order_status != 'Cancelled' AND created_at BETWEEN ? AND ?
     GROUP BY DATE(created_at) ORDER BY day ASC",
    [$fromDT, $toDT]
);

$ordersByStatus = rq(
    $db,
    "SELECT order_status AS status, COUNT(*) AS cnt,
            COALESCE(SUM(total_amount),0) AS rev
     FROM orders WHERE created_at BETWEEN ? AND ?
     GROUP BY order_status ORDER BY cnt DESC",
    [$fromDT, $toDT]
);

$ordersByPayment = rq(
    $db,
    "SELECT payment_gateway AS method,
            COUNT(*) AS cnt,
            COALESCE(SUM(total_amount),0) AS rev
     FROM orders WHERE created_at BETWEEN ? AND ?
     GROUP BY payment_gateway ORDER BY cnt DESC",
    [$fromDT, $toDT]
);

$topProducts = [];
try {
    $topProducts = rq(
        $db,
        "SELECT p.name, p.brand,
                SUM(oi.quantity) AS qty_sold,
                SUM(oi.quantity * oi.unit_price) AS revenue,
                p.stock_qty AS stock
         FROM order_items oi
         JOIN products p ON p.id = oi.product_id
         JOIN orders o   ON o.id = oi.order_id
         WHERE o.order_status != 'Cancelled' AND o.created_at BETWEEN ? AND ?
         GROUP BY oi.product_id
         ORDER BY revenue DESC
         LIMIT 10",
        [$fromDT, $toDT]
    );
} catch (PDOException $e) {
    $topProducts = [];
}

$revenueByCategory = [];
try {
    $revenueByCategory = rq(
        $db,
        "SELECT c.name AS category,
                SUM(oi.quantity * oi.unit_price) AS revenue,
                SUM(oi.quantity) AS units
         FROM order_items oi
         JOIN products p    ON p.id  = oi.product_id
         JOIN categories c  ON c.id  = p.category_id
         JOIN orders o      ON o.id  = oi.order_id
         WHERE o.order_status != 'Cancelled' AND o.created_at BETWEEN ? AND ?
         GROUP BY p.category_id
         ORDER BY revenue DESC LIMIT 8",
        [$fromDT, $toDT]
    );
} catch (PDOException $e) {
    $revenueByCategory = [];
}

// ════════════════════════════════════════════════════════
//  USER ACTIVITY DATA
// ════════════════════════════════════════════════════════
$totalUsers = (int) rqVal($db, "SELECT COUNT(*) FROM users");
$newUsers = (int) rqVal(
    $db,
    "SELECT COUNT(*) FROM users WHERE created_at BETWEEN ? AND ?",
    [$fromDT, $toDT]
);
$prevNewUsers = (int) rqVal(
    $db,
    "SELECT COUNT(*) FROM users WHERE created_at BETWEEN ? AND ?",
    [$prevFrom, $prevTo]
);
$activeUsers = (int) rqVal(
    $db,
    "SELECT COUNT(DISTINCT user_id) FROM orders WHERE created_at BETWEEN ? AND ?",
    [$fromDT, $toDT]
);

$dailyNewUsers = rq(
    $db,
    "SELECT DATE(created_at) AS day, COUNT(*) AS users
     FROM users WHERE created_at BETWEEN ? AND ?
     GROUP BY DATE(created_at) ORDER BY day ASC",
    [$fromDT, $toDT]
);

$topCustomers = rq(
    $db,
    "SELECT u.full_name AS name, u.email,
            COUNT(o.id) AS orders,
            COALESCE(SUM(o.total_amount),0) AS spend,
            MAX(o.created_at) AS last_order
     FROM users u
     JOIN orders o ON o.user_id = u.id
     WHERE o.order_status != 'Cancelled' AND o.created_at BETWEEN ? AND ?
     GROUP BY u.id
     ORDER BY spend DESC LIMIT 10",
    [$fromDT, $toDT]
);

$userTrend = rq(
    $db,
    "SELECT DATE_FORMAT(created_at,'%Y-%m') AS month, COUNT(*) AS users
     FROM users
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
     GROUP BY month ORDER BY month ASC"
);

// ════════════════════════════════════════════════════════
//  PRODUCT / INVENTORY DATA
// ════════════════════════════════════════════════════════
$totalProducts = (int) rqVal($db, "SELECT COUNT(*) FROM products WHERE is_active=1");
$lowStockCount = (int) rqVal($db, "SELECT COUNT(*) FROM products WHERE is_active=1 AND stock_qty > 0 AND stock_qty <= 5");
$outOfStockCount = (int) rqVal($db, "SELECT COUNT(*) FROM products WHERE is_active=1 AND stock_qty = 0");
$inventoryValue = (float) rqVal($db, "SELECT COALESCE(SUM(price * stock_qty),0) FROM products WHERE is_active=1");

$lowStockProducts = rq(
    $db,
    "SELECT p.name, p.brand, p.stock_qty, c.name AS category, p.price
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.is_active=1 AND p.stock_qty <= 5
     ORDER BY p.stock_qty ASC LIMIT 15"
);

// ════════════════════════════════════════════════════════
//  TRENDS DATA
// ════════════════════════════════════════════════════════
$monthlyRevenue = rq(
    $db,
    "SELECT DATE_FORMAT(created_at,'%Y-%m') AS month,
            COALESCE(SUM(total_amount),0) AS revenue,
            COUNT(*) AS orders
     FROM orders
     WHERE order_status != 'Cancelled'
       AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
     GROUP BY month ORDER BY month ASC"
);

$peakHours = rq(
    $db,
    "SELECT HOUR(created_at) AS hr, COUNT(*) AS cnt
     FROM orders WHERE created_at BETWEEN ? AND ?
     GROUP BY hr ORDER BY hr ASC",
    [$fromDT, $toDT]
);

$peakDays = rq(
    $db,
    "SELECT DAYNAME(created_at) AS day_name,
            DAYOFWEEK(created_at) AS dow,
            COUNT(*) AS cnt
     FROM orders WHERE created_at BETWEEN ? AND ?
     GROUP BY dow, day_name ORDER BY dow ASC",
    [$fromDT, $toDT]
);

// ── Pre-compute max values for progress bars ──────────────────────────────
$maxProductRevenue = !empty($topProducts) ? max(array_column($topProducts, 'revenue')) : 1;
$maxCategoryRevenue = !empty($revenueByCategory) ? max(array_column($revenueByCategory, 'revenue')) : 1;
$maxCustomerSpend = !empty($topCustomers) ? max(array_column($topCustomers, 'spend')) : 1;

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<style>
    /* ── KPI card ── */
    .kpi-card {
        background: #fff;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        padding: 1.1rem 1.3rem;
        box-shadow: 0 1px 6px rgba(0, 0, 0, .04);
        display: flex;
        flex-direction: column;
        gap: .3rem;
    }

    .kpi-icon {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: .7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        margin-bottom: .25rem;
    }

    .kpi-label {
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
    }

    .kpi-value {
        font-size: 1.65rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
    }

    .kpi-change {
        font-size: .72rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: .3rem;
        margin-top: .1rem;
    }

    .kpi-change.up {
        color: #16a34a;
    }

    .kpi-change.down {
        color: #dc2626;
    }

    .kpi-change.neutral {
        color: #64748b;
    }

    .kpi-sub {
        font-size: .7rem;
        color: #94a3b8;
    }

    /* ── Tabs ── */
    .report-tabs {
        display: flex;
        gap: .25rem;
        background: #f1f5f9;
        border-radius: .75rem;
        padding: .25rem;
        width: fit-content;
        flex-wrap: wrap;
    }

    .rtab {
        padding: .45rem 1.1rem;
        border-radius: .55rem;
        font-size: .8rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        color: #64748b;
        transition: all .18s;
    }

    .rtab.active,
    .rtab:hover {
        background: #fff;
        color: #4f46e5;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
    }

    /* ── Section card ── */
    .rep-card {
        background: #fff;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 6px rgba(0, 0, 0, .04);
        overflow: hidden;
    }

    .rep-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .9rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        background: #fafbff;
    }

    .rep-card-title {
        font-size: .9rem;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .rep-card-body {
        padding: 1.1rem 1.25rem;
    }

    /* ── Table ── */
    .rep-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .8rem;
    }

    .rep-table thead th {
        padding: .5rem .75rem;
        text-align: left;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .rep-table tbody td {
        padding: .55rem .75rem;
        border-bottom: 1px solid #f1f5f9;
        color: #374151;
    }

    .rep-table tbody tr:last-child td {
        border-bottom: none;
    }

    .rep-table tbody tr:hover td {
        background: #f8f9ff;
    }

    /* ── Status badges ── */
    .st-badge {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .15rem .55rem;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .st-Delivered {
        background: #dcfce7;
        color: #16a34a;
    }

    .st-Processing {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .st-Cancelled {
        background: #fee2e2;
        color: #dc2626;
    }

    .st-Shipped {
        background: #ede9fe;
        color: #6d28d9;
    }

    .st-Pending {
        background: #fef9c3;
        color: #854d0e;
    }

    /* ── Progress bar ── */
    .prog-bar {
        height: 5px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
        min-width: 60px;
    }

    .prog-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
    }

    /* ── Stock colours ── */
    .stock-ok {
        color: #16a34a;
        font-weight: 700;
    }

    .stock-low {
        color: #d97706;
        font-weight: 700;
    }

    .stock-zero {
        color: #dc2626;
        font-weight: 700;
    }

    /* ── Date filter ── */
    .date-filter {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: .75rem;
        padding: .45rem .75rem;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
    }

    .date-filter select,
    .date-filter input[type=date] {
        border: 1px solid #e2e8f0;
        border-radius: .5rem;
        padding: .3rem .6rem;
        font-size: .8rem;
        color: #374151;
        outline: none;
        background: #fafafa;
    }

    .date-filter select:focus,
    .date-filter input:focus {
        border-color: #6366f1;
    }

    .date-filter label {
        font-size: .75rem;
        font-weight: 600;
        color: #64748b;
    }

    /* ── Export button ── */
    .btn-export {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .35rem .85rem;
        border-radius: .6rem;
        font-size: .75rem;
        font-weight: 600;
        background: #f1f5f9;
        color: #374151;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        text-decoration: none;
        transition: .15s;
    }

    .btn-export:hover {
        background: #e0e7ff;
        color: #4f46e5;
        border-color: #c7d2fe;
    }

    /* ── Grid helpers ── */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }

    .two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .three-col {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1rem;
    }

    .chart-wrap {
        position: relative;
        width: 100%;
    }

    /* ── Peak hour heatmap ── */
    .hour-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 3px;
    }

    .hour-cell {
        aspect-ratio: 1;
        border-radius: 3px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        cursor: default;
        transition: .15s;
    }

    .hour-cell:hover {
        transform: scale(1.15);
        z-index: 2;
    }

    .hour-cell .hlabel {
        font-size: 7px;
        font-weight: 600;
        margin-top: 1px;
    }

    @media(max-width:900px) {
        .kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .two-col,
        .three-col {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width:600px) {
        .kpi-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- ═══════════════════════════════════════
     OUTER GRID
════════════════════════════════════════ -->
<div class="grid gap-8 lg:grid-cols-12 my-8 mx-12">

    <?php include __DIR__ . '/partials/nav.php'; ?>

    <div class="lg:col-span-9 xl:col-span-9">

        <!-- Page header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="inline-flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900">
                    <i class="fa-solid fa-chart-line text-indigo-500" aria-hidden="true"></i>Reports &amp; Analytics
                </h1>
                <p class="mt-1 text-sm text-slate-600">Sales performance, user activity, and business trend insights.
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="?page=admin-reports&tab=<?= $reportTab ?>&range=<?= $range ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>&export=sales"
                    class="btn-export"><i class="fa-solid fa-file-csv"></i> Sales CSV</a>
                <a href="?page=admin-reports&tab=<?= $reportTab ?>&range=<?= $range ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>&export=products"
                    class="btn-export"><i class="fa-solid fa-file-csv"></i> Products CSV</a>
                <a href="?page=admin-reports&tab=<?= $reportTab ?>&range=<?= $range ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>&export=customers"
                    class="btn-export"><i class="fa-solid fa-file-csv"></i> Customers CSV</a>
            </div>
        </div>

        <!-- Flash -->
        <?php if (!empty($flash)): ?>
            <div class="mt-4 rounded-xl border px-4 py-3 text-sm <?= flashBoxClass($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Date filter -->
        <form method="GET" action="index.php" class="mt-6">
            <input type="hidden" name="page" value="admin-reports">
            <input type="hidden" name="tab" value="<?= htmlspecialchars($reportTab) ?>">
            <div class="date-filter">
                <label>Period:</label>
                <select name="range" id="rangeSelect" onchange="toggleCustom(this.value)">
                    <option value="7" <?= $range === '7' ? 'selected' : '' ?>>Last 7 days</option>
                    <option value="30" <?= $range === '30' ? 'selected' : '' ?>>Last 30 days</option>
                    <option value="90" <?= $range === '90' ? 'selected' : '' ?>>Last 90 days</option>
                    <option value="365" <?= $range === '365' ? 'selected' : '' ?>>Last 12 months</option>
                    <option value="custom" <?= $range === 'custom' ? 'selected' : '' ?>>Custom range</option>
                </select>
                <span id="customDates"
                    style="display:<?= $range === 'custom' ? 'flex' : 'none' ?>;align-items:center;gap:.4rem;">
                    <label>From:</label><input type="date" name="date_from" value="<?= $dateFrom ?>">
                    <label>To:</label><input type="date" name="date_to" value="<?= $dateTo ?>">
                </span>
                <button type="submit" class="btn-export" style="background:#4f46e5;color:#fff;border-color:#4f46e5;">
                    <i class="fa-solid fa-rotate-right"></i> Apply
                </button>
                <span class="text-xs text-slate-400 ml-2">
                    <?= date('M j, Y', strtotime($dateFrom)) ?> — <?= date('M j, Y', strtotime($dateTo)) ?>
                </span>
            </div>
        </form>

        <!-- Tabs -->
        <div class="mt-5 mb-5">
            <div class="report-tabs">
                <?php foreach ([
                    ['sales', 'fa-sack-dollar', 'Sales'],
                    ['users', 'fa-users', 'Users'],
                    ['products', 'fa-boxes-stacked', 'Products'],
                    ['trends', 'fa-chart-area', 'Trends'],
                ] as [$slug, $icon, $label]): ?>
                    <a href="?page=admin-reports&tab=<?= $slug ?>&range=<?= $range ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>"
                        class="rtab <?= $reportTab === $slug ? 'active' : '' ?>">
                        <i class="fa-solid <?= $icon ?>"></i> <?= $label ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ═══════ TAB: SALES ═══════ -->
        <?php if ($reportTab === 'sales'): ?>

            <div class="kpi-grid mb-5">
                <?php foreach ([
                    ['Total Revenue', 'Rs ' . number_format($totalRevenue, 2), $totalRevenue, $prevRevenue, 'fa-sack-dollar', 'bg-indigo-50 text-indigo-600'],
                    ['Total Orders', number_format($totalOrders), $totalOrders, $prevOrders, 'fa-bag-shopping', 'bg-sky-50 text-sky-600'],
                    ['Avg Order Value', 'Rs ' . number_format($avgOrderValue, 2), $avgOrderValue, $prevAvg, 'fa-receipt', 'bg-emerald-50 text-emerald-600'],
                    ['Paid Orders', number_format($paidOrders), $paidOrders, 0, 'fa-credit-card', 'bg-green-50 text-green-600'],
                ] as [$lbl, $val, $now, $prev, $ic, $cl]):
                    $c2 = pctClass($now, $prev);
                    $pt = pct($now, $prev);
                    ?>
                    <div class="kpi-card">
                        <div class="kpi-icon <?= $cl ?>"><i class="fa-solid <?= $ic ?>"></i></div>
                        <div class="kpi-label"><?= $lbl ?></div>
                        <div class="kpi-value"><?= $val ?></div>
                        <?php if ($prev > 0): ?>
                            <div class="kpi-change <?= $c2 ?>">
                                <i class="fa-solid fa-arrow-<?= $c2 === 'up' ? 'trend-up' : 'trend-down' ?>"></i>
                                <?= $pt ?> vs previous period
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Order status mini-cards -->
            <div class="three-col mb-5">
                <?php foreach ([
                    ['Delivered', $deliveredOrders, 'fa-circle-check', 'bg-green-50 text-green-600'],
                    ['Processing', $processingOrders, 'fa-gears', 'bg-blue-50 text-blue-600'],
                    ['Cancelled', $cancelledOrders, 'fa-circle-xmark', 'bg-red-50 text-red-600'],
                ] as [$lbl, $val, $ic, $cl]): ?>
                    <div class="kpi-card" style="flex-direction:row;align-items:center;gap:.75rem;padding:.85rem 1rem;">
                        <div class="kpi-icon <?= $cl ?>" style="margin:0;flex-shrink:0;"><i class="fa-solid <?= $ic ?>"></i>
                        </div>
                        <div>
                            <div class="kpi-label"><?= $lbl ?></div>
                            <div class="kpi-value" style="font-size:1.3rem"><?= $val ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Charts row -->
            <div class="two-col mb-5">
                <div class="rep-card">
                    <div class="rep-card-header">
                        <div class="rep-card-title"><i class="fa-solid fa-chart-column text-indigo-500"></i> Daily Revenue
                        </div>
                    </div>
                    <div class="rep-card-body">
                        <div class="chart-wrap" style="height:220px"><canvas id="dailyRevenueChart"></canvas></div>
                    </div>
                </div>
                <div class="rep-card">
                    <div class="rep-card-header">
                        <div class="rep-card-title"><i class="fa-solid fa-chart-pie text-violet-500"></i> Orders by Status
                        </div>
                    </div>
                    <div class="rep-card-body" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
                        <div class="chart-wrap" style="height:200px;max-width:200px"><canvas id="statusDonutChart"></canvas>
                        </div>
                        <div style="flex:1;min-width:120px">
                            <?php foreach ($ordersByStatus as $st): ?>
                                <div
                                    style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;font-size:.78rem;">
                                    <span class="st-badge st-<?= htmlspecialchars($st['status']) ?>">
                                        <?= htmlspecialchars($st['status']) ?>
                                    </span>
                                    <span style="font-weight:700"><?= $st['cnt'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment methods -->
            <div class="rep-card mb-5">
                <div class="rep-card-header">
                    <div class="rep-card-title"><i class="fa-solid fa-credit-card text-sky-500"></i> Revenue by Payment
                        Method</div>
                </div>
                <div class="rep-card-body">
                    <?php if (empty($ordersByPayment)): ?>
                        <p class="text-center text-slate-400 py-4 text-sm">No data for this period.</p>
                    <?php else:
                        $maxPay = max(array_column($ordersByPayment, 'rev') ?: [1]);
                        foreach ($ordersByPayment as $pm): ?>
                            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.7rem;font-size:.8rem;">
                                <div
                                    style="width:110px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;color:#374151">
                                    <?= htmlspecialchars($pm['method'] ?: 'Unknown') ?>
                                </div>
                                <div class="prog-bar" style="flex:1">
                                    <div class="prog-fill" style="width:<?= $maxPay > 0 ? round(($pm['rev'] / $maxPay) * 100) : 0 ?>%"></div>
                                </div>
                                <div style="width:130px;text-align:right;font-weight:700;color:#4f46e5">Rs
                                    <?= number_format($pm['rev'], 2) ?></div>
                                <div style="width:50px;text-align:right;color:#94a3b8"><?= $pm['cnt'] ?> orders</div>
                            </div>
                        <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Top Products table -->
            <div class="rep-card mb-5">
                <div class="rep-card-header">
                    <div class="rep-card-title"><i class="fa-solid fa-trophy text-yellow-500"></i> Top Selling Products
                    </div>
                    <a href="?page=admin-reports&tab=sales&range=<?= $range ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>&export=products"
                        class="btn-export"><i class="fa-solid fa-download"></i> Export</a>
                </div>
                <div class="rep-card-body" style="padding:0">
                    <?php if (empty($topProducts)): ?>
                        <p class="text-center text-slate-400 py-8 text-sm">No product sales data for this period.</p>
                    <?php else: ?>
                        <table class="rep-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Brand</th>
                                    <th>Qty Sold</th>
                                    <th>Revenue</th>
                                    <th>Stock Left</th>
                                    <th>Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $i => $p): ?>
                                    <tr>
                                        <td class="text-slate-400"><?= $i + 1 ?></td>
                                        <td class="font-semibold text-slate-800"><?= htmlspecialchars($p['name']) ?></td>
                                        <td><?= htmlspecialchars($p['brand']) ?></td>
                                        <td><?= number_format($p['qty_sold']) ?></td>
                                        <td class="font-bold text-indigo-700">Rs <?= number_format($p['revenue'], 2) ?></td>
                                        <td><?php $sq = (int) $p['stock'];
                                        echo '<span class="' . ($sq === 0 ? 'stock-zero' : ($sq <= 5 ? 'stock-low' : 'stock-ok')) . '">' . $sq . '</span>'; ?>
                                        </td>
                                        <td>
                                            <div class="prog-bar">
                                                <div class="prog-fill"
                                                    style="width:<?= $maxProductRevenue > 0 ? round(($p['revenue'] / $maxProductRevenue) * 100) : 0 ?>%">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Revenue by category -->
            <div class="rep-card mb-5">
                <div class="rep-card-header">
                    <div class="rep-card-title"><i class="fa-solid fa-layer-group text-sky-500"></i> Revenue by Category
                    </div>
                </div>
                <div class="rep-card-body">
                    <?php if (empty($revenueByCategory)): ?>
                        <p class="text-center text-slate-400 py-4 text-sm">No category data for this period.</p>
                    <?php else:
                        foreach ($revenueByCategory as $cat): ?>
                            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.7rem;font-size:.8rem;">
                                <div
                                    style="width:130px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;color:#374151">
                                    <?= htmlspecialchars($cat['category']) ?></div>
                                <div class="prog-bar" style="flex:1">
                                    <div class="prog-fill"
                                        style="width:<?= $maxCategoryRevenue > 0 ? round(($cat['revenue'] / $maxCategoryRevenue) * 100) : 0 ?>%">
                                    </div>
                                </div>
                                <div style="width:120px;text-align:right;font-weight:700;color:#4f46e5">Rs
                                    <?= number_format($cat['revenue'], 2) ?></div>
                                <div style="width:55px;text-align:right;color:#94a3b8"><?= $cat['units'] ?> units</div>
                            </div>
                        <?php endforeach; endif; ?>
                </div>
            </div>

        <?php endif; // sales ?>

        <!-- ═══════ TAB: USERS ═══════ -->
        <?php if ($reportTab === 'users'): ?>

            <div class="kpi-grid mb-5">
                <?php foreach ([
                    ['Total Users', number_format($totalUsers), $totalUsers, 0, 'fa-users', 'bg-indigo-50 text-indigo-600'],
                    ['New This Period', number_format($newUsers), $newUsers, $prevNewUsers, 'fa-user-plus', 'bg-sky-50 text-sky-600'],
                    ['Active Buyers', number_format($activeUsers), $activeUsers, 0, 'fa-user-check', 'bg-emerald-50 text-emerald-600'],
                    ['Avg Orders/User', $activeUsers > 0 ? round($totalOrders / $activeUsers, 1) : '—', 0, 0, 'fa-basket-shopping', 'bg-violet-50 text-violet-600'],
                ] as [$lbl, $val, $now, $prev, $ic, $cl]):
                    $c2 = pctClass($now, $prev);
                    $pt = pct($now, $prev);
                    ?>
                    <div class="kpi-card">
                        <div class="kpi-icon <?= $cl ?>"><i class="fa-solid <?= $ic ?>"></i></div>
                        <div class="kpi-label"><?= $lbl ?></div>
                        <div class="kpi-value"><?= $val ?></div>
                        <?php if ($prev > 0): ?>
                            <div class="kpi-change <?= $c2 ?>"><i
                                    class="fa-solid fa-arrow-<?= $c2 === 'up' ? 'trend-up' : 'trend-down' ?>"></i> <?= $pt ?> vs previous
                                period</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="two-col mb-5">
                <div class="rep-card">
                    <div class="rep-card-header">
                        <div class="rep-card-title"><i class="fa-solid fa-user-plus text-sky-500"></i> Daily New
                            Registrations</div>
                    </div>
                    <div class="rep-card-body">
                        <div class="chart-wrap" style="height:200px"><canvas id="newUsersChart"></canvas></div>
                    </div>
                </div>
                <div class="rep-card">
                    <div class="rep-card-header">
                        <div class="rep-card-title"><i class="fa-solid fa-chart-line text-violet-500"></i> 12-Month
                            Registration Trend</div>
                    </div>
                    <div class="rep-card-body">
                        <div class="chart-wrap" style="height:200px"><canvas id="userTrendChart"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="rep-card mb-5">
                <div class="rep-card-header">
                    <div class="rep-card-title"><i class="fa-solid fa-crown text-yellow-500"></i> Top Customers by Spend
                    </div>
                    <a href="?page=admin-reports&tab=users&range=<?= $range ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>&export=customers"
                        class="btn-export"><i class="fa-solid fa-download"></i> Export</a>
                </div>
                <div class="rep-card-body" style="padding:0">
                    <?php if (empty($topCustomers)): ?>
                        <p class="text-center text-slate-400 py-8 text-sm">No customer data for this period.</p>
                    <?php else: ?>
                        <table class="rep-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Orders</th>
                                    <th>Total Spend</th>
                                    <th>Last Order</th>
                                    <th>Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topCustomers as $i => $c): ?>
                                    <tr>
                                        <td class="text-slate-400"><?= $i + 1 ?></td>
                                        <td class="font-semibold"><?= htmlspecialchars($c['name']) ?></td>
                                        <td class="text-slate-500 text-xs"><?= htmlspecialchars($c['email']) ?></td>
                                        <td><?= $c['orders'] ?></td>
                                        <td class="font-bold text-indigo-700">Rs <?= number_format($c['spend'], 2) ?></td>
                                        <td class="text-slate-400 text-xs">
                                            <?= $c['last_order'] ? date('M j, Y', strtotime($c['last_order'])) : '—' ?></td>
                                        <td>
                                            <div class="prog-bar">
                                                <div class="prog-fill"
                                                    style="width:<?= $maxCustomerSpend > 0 ? round(($c['spend'] / $maxCustomerSpend) * 100) : 0 ?>%">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        <?php endif; // users ?>

        <!-- ═══════ TAB: PRODUCTS ═══════ -->
        <?php if ($reportTab === 'products'): ?>

            <div class="kpi-grid mb-5">
                <?php foreach ([
                    ['Active Products', number_format($totalProducts), 'fa-boxes-stacked', 'bg-indigo-50 text-indigo-600'],
                    ['Low Stock (≤5)', number_format($lowStockCount), 'fa-triangle-exclamation', 'bg-yellow-50 text-yellow-600'],
                    ['Out of Stock', number_format($outOfStockCount), 'fa-circle-xmark', 'bg-red-50 text-red-600'],
                    ['Inventory Value', 'Rs ' . number_format($inventoryValue, 2), 'fa-warehouse', 'bg-emerald-50 text-emerald-600'],
                ] as [$lbl, $val, $ic, $cl]): ?>
                    <div class="kpi-card">
                        <div class="kpi-icon <?= $cl ?>"><i class="fa-solid <?= $ic ?>"></i></div>
                        <div class="kpi-label"><?= $lbl ?></div>
                        <div class="kpi-value" style="<?= strlen($val) > 10 ? 'font-size:1.1rem' : '' ?>"><?= $val ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="rep-card mb-5">
                <div class="rep-card-header">
                    <div class="rep-card-title"><i class="fa-solid fa-trophy text-yellow-500"></i> Best-Selling Products
                        (this period)</div>
                    <a href="?page=admin-reports&tab=products&range=<?= $range ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>&export=products"
                        class="btn-export"><i class="fa-solid fa-download"></i> Export</a>
                </div>
                <div class="rep-card-body" style="padding:0">
                    <?php if (empty($topProducts)): ?>
                        <p class="text-center text-slate-400 py-8 text-sm">No sales data for this period.</p>
                    <?php else: ?>
                        <table class="rep-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Brand</th>
                                    <th>Qty Sold</th>
                                    <th>Revenue</th>
                                    <th>Stock Left</th>
                                    <th>Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $i => $p):
                                    $sq = (int) $p['stock']; ?>
                                    <tr>
                                        <td class="text-slate-400"><?= $i + 1 ?></td>
                                        <td class="font-semibold"><?= htmlspecialchars($p['name']) ?></td>
                                        <td><?= htmlspecialchars($p['brand']) ?></td>
                                        <td><?= number_format($p['qty_sold']) ?></td>
                                        <td class="font-bold text-indigo-700">Rs <?= number_format($p['revenue'], 2) ?></td>
                                        <td><span class="<?= $sq === 0 ? 'stock-zero' : ($sq <= 5 ? 'stock-low' : 'stock-ok') ?>"><?= $sq ?>
                                                <?= $sq === 0 ? '❌' : ($sq <= 5 ? '⚠️' : '✓') ?></span></td>
                                        <td>
                                            <div class="prog-bar">
                                                <div class="prog-fill"
                                                    style="width:<?= $maxProductRevenue > 0 ? round(($p['revenue'] / $maxProductRevenue) * 100) : 0 ?>%">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rep-card mb-5">
                <div class="rep-card-header">
                    <div class="rep-card-title">
                        <i class="fa-solid fa-triangle-exclamation text-yellow-500"></i> Low / Out-of-Stock Alert
                        <?php if ($lowStockCount + $outOfStockCount > 0): ?>
                            <span
                                style="background:#fef9c3;color:#854d0e;padding:.1rem .5rem;border-radius:999px;font-size:.65rem;font-weight:700;">
                                <?= $lowStockCount + $outOfStockCount ?> items need attention
                            </span>
                        <?php endif; ?>
                    </div>
                    <a href="index.php?page=admin-products" class="btn-export"><i
                            class="fa-solid fa-arrow-up-right-from-square"></i> Manage Stock</a>
                </div>
                <div class="rep-card-body" style="padding:0">
                    <?php if (empty($lowStockProducts)): ?>
                        <p class="text-center text-green-600 py-8 text-sm"><i
                                class="fa-solid fa-circle-check text-xl"></i><br>All products are well-stocked!</p>
                    <?php else: ?>
                        <table class="rep-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Price</th>
                                    <th>Stock Qty</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStockProducts as $p): ?>
                                    <tr>
                                        <td class="font-semibold"><?= htmlspecialchars($p['name']) ?></td>
                                        <td class="text-slate-500"><?= htmlspecialchars($p['category'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($p['brand']) ?></td>
                                        <td>Rs <?= number_format($p['price'], 2) ?></td>
                                        <td class="font-bold <?= $p['stock_qty'] == 0 ? 'text-red-600' : 'text-yellow-600' ?>">
                                            <?= $p['stock_qty'] ?></td>
                                        <td>
                                            <?php if ($p['stock_qty'] == 0): ?>
                                                <span class="st-badge" style="background:#fee2e2;color:#dc2626">Out of Stock</span>
                                            <?php else: ?>
                                                <span class="st-badge" style="background:#fef9c3;color:#854d0e">Low Stock</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        <?php endif; // products ?>

        <!-- ═══════ TAB: TRENDS ═══════ -->
        <?php if ($reportTab === 'trends'): ?>

            <div class="rep-card mb-5">
                <div class="rep-card-header">
                    <div class="rep-card-title"><i class="fa-solid fa-chart-area text-indigo-500"></i> 12-Month Revenue
                        &amp; Orders Trend</div>
                </div>
                <div class="rep-card-body">
                    <div class="chart-wrap" style="height:260px"><canvas id="monthlyTrendChart"></canvas></div>
                </div>
            </div>

            <div class="two-col mb-5">
                <div class="rep-card">
                    <div class="rep-card-header">
                        <div class="rep-card-title"><i class="fa-solid fa-clock text-sky-500"></i> Peak Order Hours</div>
                    </div>
                    <div class="rep-card-body">
                        <?php
                        $hourMap = [];
                        foreach ($peakHours as $h)
                            $hourMap[(int) $h['hr']] = (int) $h['cnt'];
                        $maxH = max(1, ...(!empty($hourMap) ? array_values($hourMap) : [1]));
                        ?>
                        <div class="hour-grid">
                            <?php for ($h = 0; $h < 24; $h++):
                                $cnt = $hourMap[$h] ?? 0;
                                $pct2 = $maxH > 0 ? $cnt / $maxH : 0;
                                $alpha = round(0.08 + $pct2 * 0.85, 2);
                                $bg = "rgba(99,102,241,$alpha)";
                                $tc = $pct2 > 0.5 ? '#fff' : '#4f46e5';
                                $lbl = $h < 12 ? ($h === 0 ? '12a' : "{$h}a") : ($h === 12 ? '12p' : ($h - 12) . 'p');
                                ?>
                                <div class="hour-cell" style="background:<?= $bg ?>;color:<?= $tc ?>"
                                    title="<?= $lbl ?>: <?= $cnt ?> orders">
                                    <span><?= $cnt ?: '' ?></span>
                                    <span class="hlabel"><?= $lbl ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <p class="text-xs text-slate-400 mt-3 text-center">Darker cells = more orders. Hover for details.
                        </p>
                    </div>
                </div>
                <div class="rep-card">
                    <div class="rep-card-header">
                        <div class="rep-card-title"><i class="fa-solid fa-calendar-days text-violet-500"></i> Orders by Day
                            of Week</div>
                    </div>
                    <div class="rep-card-body">
                        <div class="chart-wrap" style="height:200px"><canvas id="peakDaysChart"></canvas></div>
                    </div>
                </div>
            </div>

            <!-- Trend insight KPIs -->
            <?php
            $bestMonth = !empty($monthlyRevenue) ? array_reduce($monthlyRevenue, fn($c, $r) => (!$c || $r['revenue'] > $c['revenue']) ? $r : $c) : null;
            $worstMonth = !empty($monthlyRevenue) ? array_reduce($monthlyRevenue, fn($c, $r) => (!$c || $r['revenue'] < $c['revenue']) ? $r : $c) : null;
            $avgMonthly = !empty($monthlyRevenue) ? array_sum(array_column($monthlyRevenue, 'revenue')) / count($monthlyRevenue) : 0;
            $peakHourEl = !empty($peakHours) ? array_reduce($peakHours, fn($c, $r) => (!$c || $r['cnt'] > $c['cnt']) ? $r : $c) : null;
            ?>
            <div class="kpi-grid mb-5">
                <div class="kpi-card">
                    <div class="kpi-icon bg-indigo-50 text-indigo-600"><i class="fa-solid fa-star"></i></div>
                    <div class="kpi-label">Best Month</div>
                    <div class="kpi-value" style="font-size:1.1rem">
                        <?= $bestMonth ? date('M Y', strtotime($bestMonth['month'] . '-01')) : '—' ?></div>
                    <div class="kpi-sub">Rs <?= $bestMonth ? number_format($bestMonth['revenue'], 0) : '—' ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon bg-sky-50 text-sky-600"><i class="fa-solid fa-chart-bar"></i></div>
                    <div class="kpi-label">Avg Monthly Revenue</div>
                    <div class="kpi-value" style="font-size:1.1rem">Rs <?= number_format($avgMonthly, 0) ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon bg-yellow-50 text-yellow-600"><i class="fa-solid fa-clock"></i></div>
                    <div class="kpi-label">Peak Hour</div>
                    <div class="kpi-value" style="font-size:1.1rem">
                        <?php if ($peakHourEl):
                            $ph = (int) $peakHourEl['hr'];
                            echo $ph < 12 ? ($ph === 0 ? '12am' : "{$ph}am") : ($ph === 12 ? '12pm' : ($ph - 12) . 'pm'); else:
                            echo '—'; endif; ?>
                    </div>
                    <div class="kpi-sub"><?= $peakHourEl ? $peakHourEl['cnt'] . ' orders' : '' ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon bg-red-50 text-red-600"><i class="fa-solid fa-arrow-trend-down"></i></div>
                    <div class="kpi-label">Slowest Month</div>
                    <div class="kpi-value" style="font-size:1.1rem">
                        <?= $worstMonth ? date('M Y', strtotime($worstMonth['month'] . '-01')) : '—' ?></div>
                    <div class="kpi-sub">Rs <?= $worstMonth ? number_format($worstMonth['revenue'], 0) : '—' ?></div>
                </div>
            </div>

        <?php endif; // trends ?>

    </div><!-- /col -->
</div><!-- /grid -->

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
    Chart.defaults.font.family = "'ui-sans-serif', sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#64748b';

    const PURPLE = '#6366f1', PURPLE_BG = 'rgba(99,102,241,.12)';
    const SKY = '#0ea5e9', SKY_BG = 'rgba(14,165,233,.12)';

    function mkLine(id, labels, data, label, color, bg) {
        const ctx = document.getElementById(id); if (!ctx) return;
        new Chart(ctx, {
            type: 'line', data: { labels, datasets: [{ label, data, borderColor: color, backgroundColor: bg, tension: .35, fill: true, pointRadius: 3, borderWidth: 2 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' }, ticks: { callback: v => v >= 1000 ? 'Rs ' + (v / 1000).toFixed(0) + 'k' : 'Rs ' + v } } }
            }
        });
    }
    function mkBar(id, labels, data, label, color) {
        const ctx = document.getElementById(id); if (!ctx) return;
        new Chart(ctx, {
            type: 'bar', data: { labels, datasets: [{ label, data, backgroundColor: color, borderRadius: 4 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' } } }
            }
        });
    }

    const dailyRevData = <?= json_encode(array_values($dailyRevenue)) ?>;
    const orderStatData = <?= json_encode(array_values($ordersByStatus)) ?>;
    const newUserData = <?= json_encode(array_values($dailyNewUsers)) ?>;
    const userTrendData = <?= json_encode(array_values($userTrend)) ?>;
    const monthlyData = <?= json_encode(array_values($monthlyRevenue)) ?>;
    const peakDayData = <?= json_encode(array_values($peakDays)) ?>;

    // Sales tab
    mkLine('dailyRevenueChart', dailyRevData.map(r => r.day), dailyRevData.map(r => parseFloat(r.revenue)), 'Revenue', PURPLE, PURPLE_BG);

    (function () {
        const ctx = document.getElementById('statusDonutChart'); if (!ctx || !orderStatData.length) return;
        const palette = { 'Delivered': '#22c55e', 'Processing': '#3b82f6', 'Cancelled': '#ef4444', 'Shipped': '#8b5cf6', 'Pending': '#f59e0b' };
        new Chart(ctx, {
            type: 'doughnut',
            data: { labels: orderStatData.map(r => r.status), datasets: [{ data: orderStatData.map(r => r.cnt), backgroundColor: orderStatData.map(r => palette[r.status] || '#94a3b8'), borderWidth: 2, borderColor: '#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { display: false } } }
        });
    })();

    // Users tab
    mkBar('newUsersChart', newUserData.map(r => r.day), newUserData.map(r => parseInt(r.users)), 'New Users', SKY);
    mkLine('userTrendChart', userTrendData.map(r => r.month), userTrendData.map(r => parseInt(r.users)), 'Registrations', '#8b5cf6', 'rgba(139,92,246,.1)');

    // Trends tab
    (function () {
        const ctx = document.getElementById('monthlyTrendChart'); if (!ctx || !monthlyData.length) return;
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(r => r.month), datasets: [
                    { type: 'line', label: 'Revenue', data: monthlyData.map(r => parseFloat(r.revenue)), borderColor: PURPLE, backgroundColor: PURPLE_BG, tension: .35, fill: true, yAxisID: 'y', pointRadius: 4, borderWidth: 2 },
                    { type: 'bar', label: 'Orders', data: monthlyData.map(r => parseInt(r.orders)), backgroundColor: 'rgba(14,165,233,.25)', borderColor: SKY, borderWidth: 1, borderRadius: 4, yAxisID: 'y2' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true } } },
                scales: {
                    x: { grid: { display: false } },
                    y: { position: 'left', grid: { color: '#f1f5f9' }, ticks: { callback: v => 'Rs ' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v) } },
                    y2: { position: 'right', grid: { display: false }, ticks: { callback: v => v + ' orders' } }
                }
            }
        });
    })();

    mkBar('peakDaysChart', peakDayData.map(r => r.day_name.slice(0, 3)), peakDayData.map(r => parseInt(r.cnt)), 'Orders', '#8b5cf6');

    function toggleCustom(val) {
        document.getElementById('customDates').style.display = val === 'custom' ? 'flex' : 'none';
    }
</script>