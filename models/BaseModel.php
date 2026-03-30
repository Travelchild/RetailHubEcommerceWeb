<?php
require_once __DIR__ . '/../includes/Database.php';

class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }
}
