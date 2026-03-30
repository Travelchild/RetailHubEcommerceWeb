<?php
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$page = $_GET['page'] ?? 'home';
$result = ['view' => 'home', 'data' => []];

if ($page === 'admin-reports' && isset($_GET['export'])) {
    requireLogin();

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
    fwrite($out, "\xEF\xBB\xBF");

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

switch ($page) {
    case 'login':
        require_once __DIR__ . '/../controllers/AuthController.php';
        $result = (new AuthController())->login();
        break;
    case 'register':
        require_once __DIR__ . '/../controllers/AuthController.php';
        $result = (new AuthController())->register();
        break;
    case 'logout':
        require_once __DIR__ . '/../controllers/AuthController.php';
        (new AuthController())->logout();
        break;
    case 'products':
        require_once __DIR__ . '/../controllers/ProductController.php';
        $result = (new ProductController())->index();
        break;
    case 'product':
        require_once __DIR__ . '/../controllers/ProductController.php';
        $result = (new ProductController())->show();
        break;
    case 'cart':
        requireLogin();
        require_once __DIR__ . '/../controllers/CartController.php';
        $result = (new CartController())->index();
        break;
    case 'cart-add':
        requireLogin();
        require_once __DIR__ . '/../controllers/CartController.php';
        (new CartController())->add();
        break;
    case 'cart-update':
        requireLogin();
        require_once __DIR__ . '/../controllers/CartController.php';
        (new CartController())->update();
        break;
    case 'checkout':
        requireLogin();
        require_once __DIR__ . '/../controllers/OrderController.php';
        $result = (new OrderController())->checkout();
        break;
    case 'orders':
        requireLogin();
        require_once __DIR__ . '/../controllers/OrderController.php';
        $result = (new OrderController())->history();
        break;
    case 'invoice':
        requireLogin();
        require_once __DIR__ . '/../controllers/OrderController.php';
        (new OrderController())->invoicePdf();
        exit;
    case 'support':
        requireLogin();
        require_once __DIR__ . '/../controllers/SupportController.php';
        $result = (new SupportController())->index();
        break;
    case 'admin':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        $result = (new AdminController())->dashboard();
        break;
    case 'admin-products':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        $result = (new AdminController())->products();
        break;
    case 'admin-promotions':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        $result = (new AdminController())->promotions();
        break;
    case 'admin-categories':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        $result = (new AdminController())->categories();
        break;
    case 'admin-reports':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        $result = (new AdminController())->reports();
        break;
    case 'admin-users':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        $result = (new AdminController())->users();
        break;
    case 'admin-user-create':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        $result = (new AdminController())->createUserForm();
        break;
    case 'admin-user-store':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->storeUser();
        break;
    case 'admin-user-edit':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        $result = (new AdminController())->editUserForm();
        break;
    case 'admin-user-update':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->updateUserFull();
        break;
    case 'admin-user-delete':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->deleteUser();
        break;
    case 'admin-orders':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        $result = (new AdminController())->orders();
        break;
    case 'admin-order-update':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->updateOrderStatus();
        break;
    case 'admin-order-delete':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->deleteOrder();
        break;
    case 'admin-helpdesk':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        $result = (new AdminController())->helpdesk();
        break;
    case 'admin-helpdesk-reply':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->helpdeskReply();
        break;
    case 'admin-product-create':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        $result = (new AdminController())->createProduct();
        break;
    case 'admin-product-edit':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        $result = (new AdminController())->editProduct();
        break;
    case 'admin-product-delete':
        requireLogin();
        require_once __DIR__ . '/../controllers/AdminController.php';
        (new AdminController())->deleteProduct();
        break;
    case 'wishlist':
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
            require_once __DIR__ . '/../controllers/WishlistController.php';
            (new WishlistController())->toggle();
        }
        $result = ['view' => 'wishlist/index', 'data' => []];
        break;
    case 'wishlist-add-all':
        requireLogin();
        require_once __DIR__ . '/../controllers/WishlistController.php';
        (new WishlistController())->addAllToCart();
        break;
    case 'dashboard':
        requireLogin();
        $result = ['view' => 'user/dashboard', 'data' => []];
        break;
    case 'chatbot':
    include __DIR__ . '/../views/chatbot/index.php';
    exit;
}

$data = $result['data'];
extract($data);
include __DIR__ . '/../views/layouts/header.php';
include __DIR__ . '/../views/' . $result['view'] . '.php';
include __DIR__ . '/../views/layouts/footer.php';