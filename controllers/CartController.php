<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Cart.php';

class CartController
{
    private Product $productModel;
    private Cart $cartModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->cartModel = new Cart();
    }

    public function index(): array
    {
        $userId = (int)currentUser()['id'];
        $items = [];
        $total = 0.0;
        foreach ($this->cartModel->linesForUser($userId) as $productId => $qty) {
            $product = $this->productModel->find((int)$productId);
            if ($product) {
                $product['qty'] = $qty;
                $product['subtotal'] = $qty * (float)$product['price'];
                $total += $product['subtotal'];
                $items[] = $product;
            }
        }

        return ['view' => 'cart/index', 'data' => ['items' => $items, 'total' => $total]];
    }

    public function add(): void
    {
        $userId = (int)currentUser()['id'];
        $id = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        if ($id > 0) {
            $this->cartModel->addProduct($userId, $id, $qty);
        }
        redirect('index.php?page=cart');
    }

    public function update(): void
    {
        $userId = (int)currentUser()['id'];
        $id = (int)($_POST['product_id'] ?? 0);
        $qty = max(0, (int)($_POST['qty'] ?? 0));
        $this->cartModel->setQuantity($userId, $id, $qty);
        redirect('index.php?page=cart');
    }
}
