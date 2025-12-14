<?php
// Load error handler first (if not already loaded)
if (!function_exists('redirectToErrorPage')) {
    require_once __DIR__ . '/error_handler.php';
}

// Configuration for the database connection
// NOTE: Database names in MySQL should not contain spaces.
$host = 'localhost';
$db = 'event_ticketing_db'; // CRITICAL: Ensure this matches your phpMyAdmin database name exactly!
$user = 'root';
$pass = ''; // Leave blank if you have no password set in MySQL
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    // Throw exceptions on error
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    // Return data as associative arrays
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Disable prepared statement emulation
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create a new PDO instance, which attempts the connection
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If the connection fails, use error handler to redirect to error page
    // This will be caught by the exception handler
    throw new Exception("Database connection failed: " . $e->getMessage());
}

// The $pdo object is now successfully connected and ready to use.

class Database {
    private $connection;

    public function __construct() {
        global $pdo;
        $this->connection = $pdo;
    }

    public function getConnection() {
        return $this->connection;
    }
}
?>