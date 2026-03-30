<?php
class WishlistController {

    private $pdo;

    public function __construct() {
        $this->pdo = Database::connection();
    }

    public function toggle() {
        $userId    = currentUser()['id'];
        $productId = (int)($_POST['product_id'] ?? 0);
        $action    = $_POST['action'] ?? 'toggle';
        $redirect  = $_POST['redirect'] ?? 'index.php?page=wishlist';

        if ($productId > 0) {
            if ($action === 'remove') {
                $this->pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")
                    ->execute([$userId, $productId]);

            } elseif ($action === 'add_to_cart') {
                $check = $this->pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
                $check->execute([$userId, $productId]);
                if ($check->fetch()) {
                    $this->pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?")
                        ->execute([$userId, $productId]);
                } else {
                    $this->pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)")
                        ->execute([$userId, $productId]);
                }
                $this->pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")
                    ->execute([$userId, $productId]);

            } else {
                // toggle add/remove
                $check = $this->pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
                $check->execute([$userId, $productId]);
                if ($check->fetch()) {
                    $this->pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")
                        ->execute([$userId, $productId]);
                } else {
                    $this->pdo->prepare("INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())")
                        ->execute([$userId, $productId]);
                }
            }
        }

        header('Location: ' . $redirect);
        exit;
    }

    public function addAllToCart() {
        $userId   = currentUser()['id'];
        $redirect = $_POST['redirect'] ?? 'index.php?page=cart';

        $stmt = $this->pdo->prepare("
            SELECT w.product_id
            FROM wishlist w
            JOIN products p ON p.id = w.product_id
            WHERE w.user_id = ? AND p.stock_qty > 0
        ");
        $stmt->execute([$userId]);
        $products = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($products as $productId) {
            $check = $this->pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
            $check->execute([$userId, $productId]);
            if ($check->fetch()) {
                $this->pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?")
                    ->execute([$userId, $productId]);
            } else {
                $this->pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)")
                    ->execute([$userId, $productId]);
            }
            $this->pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")
                ->execute([$userId, $productId]);
        }

        header('Location: ' . $redirect);
        exit;
    }
}