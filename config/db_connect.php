<?php
// /config/db_connect.php - Fixed version
// Configuration for the database connection
$host = 'localhost';
$db = 'event_ticketing_db'; 
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

class Database {
    private $pdo;

    public function __construct() {
        global $dsn, $user, $pass, $options;
        
        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            // For API use, don't die - throw exception instead
            throw new Exception("Database Connection Failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}

// Optional: Create a global instance
// $GLOBALS['pdo'] = (new Database())->getConnection();
?>