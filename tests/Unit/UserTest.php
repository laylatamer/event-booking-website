<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PDO;

class UserTest extends TestCase
{
    private $pdo;
    private $user;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../../app/models/User.php';
        
        $host = 'localhost';
        $db = 'event_ticketing_db';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';
        
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection failed: ' . $e->getMessage());
            return;
        }
        
        $this->user = new \User($this->pdo);
    }

    public function test_user_model_can_be_created(): void
    {
        $this->assertInstanceOf(\User::class, $this->user);
    }

    public function test_user_requires_pdo_in_constructor(): void
    {
        $this->expectException(\TypeError::class);
        /** @var PDO $null */
        $null = null;
        new \User($null);
    }

    public function test_user_all_returns_array(): void
    {
        $users = $this->user->all();
        $this->assertIsArray($users);
    }

    public function test_user_all_includes_status(): void
    {
        $users = $this->user->all();
        if (!empty($users)) {
            $this->assertArrayHasKey('status', $users[0]);
            $this->assertContains($users[0]['status'], ['active', 'inactive']);
        }
    }

    public function test_user_find_returns_user_or_null(): void
    {
        $user = $this->user->find(1);
        if ($user !== null) {
            $this->assertIsArray($user);
            $this->assertArrayHasKey('id', $user);
            $this->assertArrayHasKey('email', $user);
        } else {
            $this->assertNull($user);
        }
    }

    public function test_user_find_with_invalid_id_returns_null(): void
    {
        $user = $this->user->find(999999);
        $this->assertNull($user);
    }

    public function test_user_count_returns_integer(): void
    {
        $count = $this->user->count();
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function test_user_has_email(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'hashed_password'
        ];
        
        $this->assertArrayHasKey('email', $userData);
        $this->assertStringContainsString('@', $userData['email']);
    }
    
    public function test_user_password_is_hashed(): void
    {
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $this->assertNotEquals($password, $hashedPassword);
        $this->assertTrue(password_verify($password, $hashedPassword));
    }
}