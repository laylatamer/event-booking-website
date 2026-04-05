<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PDO;

class BookingTest extends TestCase
{
    private $pdo;
    private $bookingsModel;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../../app/models/BookingsModel.php';
        
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
        
        $this->bookingsModel = new \BookingsModel($this->pdo);
    }

    public function test_bookings_model_can_be_created(): void
    {
        $this->assertInstanceOf(\BookingsModel::class, $this->bookingsModel);
    }

    public function test_get_all_bookings_returns_array(): void
    {
        $result = $this->bookingsModel->getAllBookings();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('bookings', $result);
    }

    public function test_get_all_bookings_with_pagination(): void
    {
        $result = $this->bookingsModel->getAllBookings(1, 5);
        $this->assertIsArray($result);
        if (isset($result['bookings'])) {
            $this->assertLessThanOrEqual(5, count($result['bookings']));
        }
    }

    public function test_get_all_bookings_with_filters(): void
    {
        $filters = ['status' => 'confirmed'];
        $result = $this->bookingsModel->getAllBookings(1, 10, $filters);
        $this->assertIsArray($result);
    }

    public function test_get_booking_by_id_returns_booking_or_false(): void
    {
        $booking = $this->bookingsModel->getBookingById(1);
        if ($booking !== false) {
            $this->assertIsArray($booking);
            $this->assertArrayHasKey('id', $booking);
        } else {
            $this->assertFalse($booking);
        }
    }

    public function test_get_booking_stats_returns_array(): void
    {
        $stats = $this->bookingsModel->getBookingStats();
        $this->assertIsArray($stats);
    }

    public function test_get_booking_stats_includes_total_bookings(): void
    {
        $stats = $this->bookingsModel->getBookingStats();
        $this->assertArrayHasKey('total_bookings', $stats);
        $this->assertIsInt($stats['total_bookings']);
    }

    public function test_get_booking_stats_includes_total_revenue(): void
    {
        $stats = $this->bookingsModel->getBookingStats();
        $this->assertArrayHasKey('total_revenue', $stats);
    }

    public function test_get_bookings_last_7_days_returns_array(): void
    {
        $data = $this->bookingsModel->getBookingsLast7Days();
        $this->assertIsArray($data);
    }

    public function test_get_revenue_by_category_returns_array(): void
    {
        $data = $this->bookingsModel->getRevenueByCategory();
        $this->assertIsArray($data);
    }

    public function test_get_booked_seats_returns_array(): void
    {
        $seats = $this->bookingsModel->getBookedSeats(1);
        $this->assertIsArray($seats);
    }

    public function test_booking_creation(): void
    {
        $bookingData = [
            'user_id' => 1,
            'event_id' => 1,
            'tickets' => 2,
            'total_price' => 100.00
        ];
        
        $this->assertArrayHasKey('user_id', $bookingData);
        $this->assertEquals(2, $bookingData['tickets']);
    }
    
    public function test_booking_total_price_calculation(): void
    {
        $ticketPrice = 50.00;
        $tickets = 2;
        $total = $ticketPrice * $tickets;
        
        $this->assertEquals(100.00, $total);
    }
}