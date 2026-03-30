<?php
require_once __DIR__ . '/BaseModel.php';

class SupportTicket extends BaseModel
{
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO support_tickets (user_id, subject, ticket_type, description, status) VALUES (:user_id, :subject, :ticket_type, :description, "Open")');
        return $stmt->execute($data);
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT s.*, u.full_name FROM support_tickets s JOIN users u ON u.id = s.user_id ORDER BY s.created_at DESC');
        return $stmt->fetchAll();
    }

    public function updateStatus(int $ticketId, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE support_tickets SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $stmt->execute(['status' => $status, 'id' => $ticketId]);
    }

    public function addReply(int $ticketId, int $responderId, string $replyText): bool
    {
        $stmt = $this->db->prepare('INSERT INTO support_ticket_replies (ticket_id, responder_id, reply_text) VALUES (:ticket_id, :responder_id, :reply_text)');
        return $stmt->execute([
            'ticket_id' => $ticketId,
            'responder_id' => $responderId,
            'reply_text' => $replyText
        ]);
    }

    public function repliesByTicketId(int $ticketId): array
    {
        $stmt = $this->db->prepare('SELECT r.*, u.full_name AS responder_name FROM support_ticket_replies r JOIN users u ON u.id = r.responder_id WHERE r.ticket_id = :ticket_id ORDER BY r.created_at ASC');
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }
}
