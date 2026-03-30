<?php

require_once __DIR__ . '/BaseModel.php';

class Cart extends BaseModel
{
    /** @return array<int, int> product_id => quantity */
    public function linesForUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT product_id, quantity FROM cart WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $lines = [];
        foreach ($stmt->fetchAll() as $row) {
            $lines[(int)$row['product_id']] = (int)$row['quantity'];
        }

        return $lines;
    }

    public function addProduct(int $userId, int $productId, int $qty): void
    {
        if ($productId < 1 || $qty < 1) {
            return;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity) '
            . 'ON DUPLICATE KEY UPDATE quantity = quantity + :quantity_add'
        );
        $stmt->execute([
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $qty,
            'quantity_add' => $qty,
        ]);
    }

    public function setQuantity(int $userId, int $productId, int $qty): void
    {
        if ($productId < 1) {
            return;
        }
        if ($qty < 1) {
            $this->removeLine($userId, $productId);
            return;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity) '
            . 'ON DUPLICATE KEY UPDATE quantity = :quantity_set'
        );
        $stmt->execute([
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $qty,
            'quantity_set' => $qty,
        ]);
    }

    public function removeLine(int $userId, int $productId): void
    {
        $stmt = $this->db->prepare('DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id');
        $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);
    }

    public function clearForUser(int $userId): void
    {
        $stmt = $this->db->prepare('DELETE FROM cart WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
    }
}
