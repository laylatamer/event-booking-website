<?php
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

//$pdo = null; layla changed this
$GLOBALS['pdo'] = null;

try {
    // Create a new PDO instance, which attempts the connection
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If the connection fails, terminate the script and show an error.
    die("<h1>Database Connection Failed</h1><p>Please check your credentials in **db_connect.php**.<br>Detailed Error: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// The $pdo object is now successfully connected and ready to use.

class Database {
    private $connection;

    public function __construct() {
        $this->connection = $GLOBALS['pdo'];
    }

    public function getConnection() {
        return $this->connection;
    }
}
?>
