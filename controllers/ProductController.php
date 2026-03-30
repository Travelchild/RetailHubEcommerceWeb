<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class ProductController
{
    private Product $productModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    public function index(): array
    {
        $products = $this->productModel->all([
            'search' => $_GET['search'] ?? '',
            'category_id' => $_GET['category_id'] ?? ''
        ]);
        return ['view' => 'products/index', 'data' => ['products' => $products]];
    }

    public function show(): array
{
    $id      = (int)($_GET['id'] ?? 0);
    $product = $this->productModel->find($id);

    // ── Calculate available stock (stock_qty minus what's in cart) ──
    $availableStock = 0;
    if ($product) {
        $availableStock = (int)($product['stock_qty'] ?? 0);

        if (isLoggedIn()) {
            $pdo       = Database::connection();
            $cartStmt  = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ? AND product_id = ?");
            $cartStmt->execute([currentUser()['id'], $id]);
            $inCart    = (int)$cartStmt->fetchColumn();
            $availableStock = max(0, $availableStock - $inCart);
        }
        $product['available_stock'] = $availableStock;
    }

    $relatedProducts = [];
    if ($product && !empty($product['category_id'])) {
        $relatedProducts = $this->productModel->getRelated(
            (int)$product['category_id'], $id, 4
        );
    }

    if (!isset($_SESSION['recently_viewed'])) {
        $_SESSION['recently_viewed'] = [];
    }
    $_SESSION['recently_viewed'] = array_slice(
        array_unique(array_merge([$id], $_SESSION['recently_viewed'])), 0, 10
    );
    $recentIds = array_filter($_SESSION['recently_viewed'], fn($pid) => $pid !== $id);
    $recentlyViewedProducts = !empty($recentIds)
        ? $this->productModel->getByIds(array_values($recentIds))
        : [];

    return [
        'view' => 'products/show',
        'data' => [
            'product'                => $product,
            'relatedProducts'        => $relatedProducts,
            'recentlyViewedProducts' => $recentlyViewedProducts,
        ]
    ];
}
}