<?php
// Railway-specific database connection
// This file is a copy of config/db_connect.php for Railway deployment
// It reads from environment variables that Railway provides

// Get database credentials from Railway environment variables
$host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$db = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'event_ticketing_db';
$user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
$port = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306';
$charset = 'utf8mb4';

// Build DSN with port support
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("<h1>Database Connection Failed</h1><p>Please check your credentials.<br>Detailed Error: " . htmlspecialchars($e->getMessage()) . "</p>");
}

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

