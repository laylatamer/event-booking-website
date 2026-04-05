<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PDO;

class EventTest extends TestCase
{
    private $pdo;
    private $event;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../../app/models/Event.php';
        
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
        
        $this->event = new \Event($this->pdo);
    }

    public function test_event_can_be_created(): void
    {
        $this->assertInstanceOf(\Event::class, $this->event);
    }

    public function test_event_requires_pdo_in_constructor(): void
    {
        $this->expectException(\TypeError::class);
        /** @var PDO $null */
        $null = null;
        new \Event($null);
    }

    public function test_read_all_returns_statement(): void
    {
        $stmt = $this->event->readAll();
        $this->assertInstanceOf(\PDOStatement::class, $stmt);
    }

    public function test_read_one_returns_boolean(): void
    {
        $this->event->id = 1;
        $result = $this->event->readOne();
        $this->assertIsBool($result);
    }

    public function test_read_one_with_invalid_id_returns_false(): void
    {
        $this->event->id = 999999;
        $result = $this->event->readOne();
        $this->assertFalse($result);
    }

    public function test_get_by_category_returns_statement(): void
    {
        $stmt = $this->event->getByCategory('Sports');
        $this->assertInstanceOf(\PDOStatement::class, $stmt);
    }

    public function test_get_upcoming_returns_statement(): void
    {
        $stmt = $this->event->getUpcoming(10);
        $this->assertInstanceOf(\PDOStatement::class, $stmt);
    }

    public function test_get_upcoming_respects_limit(): void
    {
        $stmt = $this->event->getUpcoming(5);
        $events = $stmt->fetchAll();
        $this->assertLessThanOrEqual(5, count($events));
    }

    public function test_count_returns_integer(): void
    {
        $count = $this->event->count();
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }
}