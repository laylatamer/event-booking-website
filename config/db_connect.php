<?php
// Configuration for the database connection
// Supports both local development and Railway deployment
// NOTE: Database names in MySQL should not contain spaces.

// Get database credentials from environment variables (Railway) or use defaults (local)
$host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$db = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'event_ticketing_db';
$user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
$port = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306';
$charset = 'utf8mb4';

// Build DSN with port support
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
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
    // If the connection fails, terminate the script and show an error.
    die("<h1>Database Connection Failed</h1><p>Please check your credentials in *db_connect.php*.<br>Detailed Error: " . htmlspecialchars($e->getMessage()) . "</p>");
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