<?php
require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel
{
    public function roles(): array
    {
        $stmt = $this->db->query('SELECT id, role_name FROM roles ORDER BY id ASC');
        return $stmt->fetchAll();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT u.*, r.role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO users (role_id, full_name, email, password_hash, contact_no, address, payment_preference, status) VALUES (:role_id, :full_name, :email, :password_hash, :contact_no, :address, :payment_preference, :status)');
        return $stmt->execute([
            'role_id' => $data['role_id'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'contact_no' => $data['contact_no'] ?? '',
            'address' => $data['address'] ?? '',
            'payment_preference' => $data['payment_preference'] ?? '',
            'status' => $data['status'] ?? 'Active',
        ]);
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT u.id, u.role_id, u.full_name, u.email, u.status, r.role_name, u.created_at FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.created_at DESC');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT u.*, r.role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function emailExistsForOtherUser(string $email, int $excludeUserId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email AND id != :exclude LIMIT 1');
        $stmt->execute(['email' => $email, 'exclude' => $excludeUserId]);
        return (bool)$stmt->fetch();
    }

    public function updateFull(int $id, array $fields): bool
    {
        $sql = 'UPDATE users SET role_id = :role_id, full_name = :full_name, email = :email, contact_no = :contact_no, address = :address, payment_preference = :payment_preference, status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'role_id' => $fields['role_id'],
            'full_name' => $fields['full_name'],
            'email' => $fields['email'],
            'contact_no' => $fields['contact_no'],
            'address' => $fields['address'],
            'payment_preference' => $fields['payment_preference'],
            'status' => $fields['status'],
        ]);
    }

    public function updatePassword(int $id, string $passwordHash): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $stmt->execute(['id' => $id, 'password_hash' => $passwordHash]);
    }

    public function countOrders(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS c FROM orders WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
        return (int)$stmt->fetch()['c'];
    }

    public function countSupportTickets(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS c FROM support_tickets WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
        return (int)$stmt->fetch()['c'];
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function setInactive(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET status = "Inactive", updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
