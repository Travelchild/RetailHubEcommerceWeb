<?php
require_once __DIR__ . '/../models/SupportTicket.php';

class SupportController
{
    private SupportTicket $ticketModel;

    public function __construct()
    {
        $this->ticketModel = new SupportTicket();
    }

    public function index(): array
    {
        $message = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = $this->ticketModel->create([
                'user_id' => (int)$_SESSION['user']['id'],
                'subject' => trim($_POST['subject'] ?? ''),
                'ticket_type' => trim($_POST['ticket_type'] ?? 'Inquiry'),
                'description' => trim($_POST['description'] ?? ''),
            ]);
            $message = $ok ? 'Ticket submitted successfully.' : 'Could not submit ticket.';
        }

        return ['view' => 'support/index', 'data' => ['message' => $message]];
    }
}
