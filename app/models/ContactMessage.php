<?php

require_once __DIR__ . '/../../helper/db_connect.php';

class ContactMessage
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(array $payload): int
    {
        $sql = 'INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name'    => $payload['name'],
            ':email'   => $payload['email'],
            ':subject' => $payload['subject'],
            ':message' => $payload['message'],
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM contact_messages ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM contact_messages WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE contact_messages SET status = :status WHERE id = :id');
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM contact_messages WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}


