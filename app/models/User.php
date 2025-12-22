<?php

require_once __DIR__ . '/../../config/db_connect.php';

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT id, first_name, last_name, email, phone_number, profile_image_path, created_at, last_login, is_admin FROM users ORDER BY created_at DESC');
        $users = $stmt->fetchAll();
        
        // Calculate status based on last_login (inactive if 2+ months ago or never logged in and account is 2+ months old)
        foreach ($users as &$user) {
            $now = new DateTime();
            $checkDate = null;
            
            if ($user['last_login'] !== null && $user['last_login'] !== '') {
                // User has logged in before - check last_login date
                $checkDate = new DateTime($user['last_login']);
            } else {
                // User never logged in - check created_at date instead
                $checkDate = new DateTime($user['created_at']);
            }
            
            $diff = $now->diff($checkDate);
            $daysAgo = $diff->days;
            $monthsAgo = ($diff->y * 12) + $diff->m;
            
            // Mark as inactive if 2+ months (60+ days) have passed
            if ($daysAgo >= 60) {
                $user['status'] = 'inactive';
            } else {
                $user['status'] = 'active';
            }
        }
        
        return $users;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, first_name, last_name, email, phone_number, profile_image_path, created_at, last_login, is_admin FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function create(array $payload): int
    {
        $sql = 'INSERT INTO users (first_name, last_name, email, password_hash, phone_number, address, city, profile_image_path, created_at, last_login)
                VALUES (:first_name, :last_name, :email, :password_hash, :phone_number, :address, :city, :profile_image_path, NOW(), NULL)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':first_name'         => $payload['first_name'],
            ':last_name'          => $payload['last_name'],
            ':email'              => $payload['email'],
            ':password_hash'      => $payload['password_hash'],
            ':phone_number'       => $payload['phone_number'] ?? null,
            ':address'            => $payload['address'] ?? null,
            ':city'               => $payload['city'] ?? null,
            ':profile_image_path' => $payload['profile_image_path'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $payload): bool
    {
        // Build update query dynamically based on provided fields
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($payload['first_name'])) {
            $fields[] = 'first_name = :first_name';
            $params[':first_name'] = $payload['first_name'];
        }
        if (isset($payload['last_name'])) {
            $fields[] = 'last_name = :last_name';
            $params[':last_name'] = $payload['last_name'];
        }
        if (isset($payload['email'])) {
            $fields[] = 'email = :email';
            $params[':email'] = $payload['email'];
        }
        if (isset($payload['phone_number'])) {
            $fields[] = 'phone_number = :phone_number';
            $params[':phone_number'] = $payload['phone_number'] ?: null;
        }
        if (isset($payload['address'])) {
            $fields[] = 'address = :address';
            $params[':address'] = $payload['address'] ?: null;
        }
        if (isset($payload['city'])) {
            $fields[] = 'city = :city';
            $params[':city'] = $payload['city'] ?: null;
        }
        if (isset($payload['country'])) {
            $fields[] = 'country = :country';
            $params[':country'] = $payload['country'] ?: null;
        }
        if (isset($payload['state'])) {
            $fields[] = 'state = :state';
            $params[':state'] = $payload['state'] ?: null;
        }
        if (isset($payload['profile_image_path'])) {
            $fields[] = 'profile_image_path = :profile_image_path';
            $params[':profile_image_path'] = $payload['profile_image_path'] ?: null;
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return true;
    }

    public function count(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM users');
        return (int) $stmt->fetchColumn();
    }
}

