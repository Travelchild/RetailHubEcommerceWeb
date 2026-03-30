<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../includes/Database.php';

class Category extends BaseModel
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    // Returns ALL categories with parent_id included — essential for tree building
    public function all(): array
    {
        $stmt = $this->pdo->query(
            "SELECT id, name, parent_id, sort_order, is_active
             FROM categories
             WHERE is_active = 1
             ORDER BY parent_id ASC, sort_order ASC, name ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO categories (name, parent_id, sort_order, is_active)
             VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['name'],
            $data['parent_id'] ?: null,
            $data['sort_order'] ?? 0,
            $data['is_active']  ?? 1,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE categories SET name=?, parent_id=?, sort_order=?, is_active=? WHERE id=?"
        );
        return $stmt->execute([
            $data['name'],
            $data['parent_id'] ?: null,
            $data['sort_order'] ?? 0,
            $data['is_active']  ?? 1,
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}