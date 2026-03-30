<?php
require_once __DIR__ . '/BaseModel.php';

class Order extends BaseModel
{
    public function createOrder(
        int $userId,
        float $total,
        string $address,
        string $paymentMethod,
        string $paymentGateway,
        string $paymentStatus,
        ?string $paymentTransactionId,
        string $orderStatus
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, payment_gateway, payment_status, payment_transaction_id, order_status) '
            . 'VALUES (:user_id, :total_amount, :shipping_address, :payment_method, :payment_gateway, :payment_status, :payment_transaction_id, :order_status)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'total_amount' => $total,
            'shipping_address' => $address,
            'payment_method' => $paymentMethod,
            'payment_gateway' => $paymentGateway,
            'payment_status' => $paymentStatus,
            'payment_transaction_id' => $paymentTransactionId,
            'order_status' => $orderStatus,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findForUser(int $orderId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, u.full_name, u.email, u.contact_no '
            . 'FROM orders o JOIN users u ON u.id = o.user_id '
            . 'WHERE o.id = :id AND o.user_id = :user_id LIMIT 1'
        );
        $stmt->execute(['id' => $orderId, 'user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function itemsForOrder(int $orderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT oi.*, p.name AS product_name, p.sku '
            . 'FROM order_items oi JOIN products p ON p.id = oi.product_id '
            . 'WHERE oi.order_id = :order_id ORDER BY oi.id'
        );
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    public function addItem(int $orderId, int $productId, int $qty, float $unitPrice): bool
    {
        $stmt = $this->db->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (:order_id, :product_id, :quantity, :unit_price, :subtotal)');
        return $stmt->execute([
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'subtotal' => $qty * $unitPrice,
        ]);
    }

    public function byUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT o.*, u.full_name FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC');
        return $stmt->fetchAll();
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE orders SET order_status = :order_status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $stmt->execute(['order_status' => $status, 'id' => $orderId]);
    }

    public function totalRevenue(): float
    {
        $stmt = $this->db->query('SELECT COALESCE(SUM(total_amount), 0) AS total_revenue FROM orders');
        $row = $stmt->fetch();
        return (float)($row['total_revenue'] ?? 0);
    }
}
