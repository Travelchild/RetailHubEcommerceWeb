<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../includes/Database.php';

class Product extends BaseModel
{
    protected PDO $pdo;

    public function __construct()
    {
        // Initialize $pdo directly — works regardless of BaseModel implementation
        $this->pdo = Database::connection();
    }

    // ── All active products for storefront ───────────────────────────────
    public function all(array $filters = []): array
    {
        $sql    = "SELECT p.*, c.name AS category_name
                   FROM products p
                   LEFT JOIN categories c ON c.id = p.category_id
                   WHERE p.is_active = 1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql     .= " AND (p.name LIKE ? OR p.brand LIKE ? OR p.sku LIKE ?)";
            $term     = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['category_id'])) {
            $sql     .= " AND p.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── All products for admin (includes inactive) ───────────────────────
    public function allForAdmin(): array
    {
        $stmt = $this->pdo->query(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             ORDER BY p.created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Single product ───────────────────────────────────────────────────
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ── Related products (same category, excluding self) ─────────────────
    public function getRelated(int $categoryId, int $excludeId, int $limit = 4): array
    {
        $limit = (int)$limit; // LIMIT ? fails on MariaDB — embed as integer literal
        $stmt  = $this->pdo->prepare(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.is_active = 1
               AND p.category_id = ?
               AND p.id != ?
             ORDER BY p.created_at DESC
             LIMIT {$limit}"
        );
        $stmt->execute([$categoryId, $excludeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Recently viewed by IDs ───────────────────────────────────────────
    public function getByIds(array $ids): array
    {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id IN ($placeholders) AND p.is_active = 1"
        );
        $stmt->execute(array_values($ids));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Create ───────────────────────────────────────────────────────────
    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO products
                (category_id, name, brand, sku, description, price, stock_qty,
                 image_url, image_url_2, image_url_3, image_url_4, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['category_id'] ?? null,
            $data['name'],
            $data['brand']       ?? '',
            $data['sku'],
            $data['description'] ?? '',
            $data['price'],
            $data['stock_qty'],
            $data['image_url']   ?? '',
            $data['image_url_2'] ?? '',
            $data['image_url_3'] ?? '',
            $data['image_url_4'] ?? '',
            $data['is_active']   ?? 1,
        ]);
    }

    // ── Update ───────────────────────────────────────────────────────────
    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE products SET
                category_id = ?,
                name        = ?,
                brand       = ?,
                sku         = ?,
                description = ?,
                price       = ?,
                stock_qty   = ?,
                image_url   = ?,
                image_url_2 = ?,
                image_url_3 = ?,
                image_url_4 = ?,
                is_active   = ?
             WHERE id = ?"
        );
        return $stmt->execute([
            $data['category_id'] ?? null,
            $data['name'],
            $data['brand']       ?? '',
            $data['sku'],
            $data['description'] ?? '',
            $data['price'],
            $data['stock_qty'],
            $data['image_url']   ?? '',
            $data['image_url_2'] ?? '',
            $data['image_url_3'] ?? '',
            $data['image_url_4'] ?? '',
            $data['is_active']   ?? 1,
            $id,
        ]);
    }

    // ── Delete ───────────────────────────────────────────────────────────
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
}