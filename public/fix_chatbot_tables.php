<?php
/**
 * Fix Chatbot Tables
 * 
 * This script creates or fixes the chatbot tables if they're missing columns
 * Run this once after importing your database
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load database connection
$dbConfigPath = __DIR__ . '/db_connect_railway.php';
if (!file_exists($dbConfigPath)) {
    $dbConfigPath = dirname(__DIR__) . '/config/db_connect.php';
}

require_once $dbConfigPath;

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("❌ Database connection failed!");
}

echo "<h1>Fixing Chatbot Tables</h1>";

// Read SQL file
$sqlFile = __DIR__ . '/create_chatbot_tables.sql';
if (!file_exists($sqlFile)) {
    $sqlFile = dirname(__DIR__) . '/database/migrations/create_chatbot_tables.sql';
}

if (!file_exists($sqlFile)) {
    die("❌ SQL file not found: create_chatbot_tables.sql");
}

$sql = file_get_contents($sqlFile);

// Split into individual statements
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && !preg_match('/^--/', $stmt);
    }
);

$success = 0;
$errors = [];

foreach ($statements as $statement) {
    if (empty(trim($statement))) continue;
    
    try {
        $pdo->exec($statement);
        $success++;
        echo "<p>✅ Executed statement successfully</p>";
    } catch (PDOException $e) {
        // Ignore "table already exists" errors
        if (strpos($e->getMessage(), 'already exists') === false) {
            $errors[] = $e->getMessage();
            echo "<p>⚠️ Warning: " . htmlspecialchars($e->getMessage()) . "</p>";
        } else {
            echo "<p>ℹ️ Table already exists (skipped)</p>";
        }
    }
}

echo "<h2>Summary</h2>";
echo "<p>✅ Successfully executed: $success statements</p>";

if (!empty($errors)) {
    echo "<h3>Errors:</h3>";
    foreach ($errors as $error) {
        echo "<p style='color: red;'>❌ " . htmlspecialchars($error) . "</p>";
    }
}

// Verify tables
echo "<h2>Verifying Tables</h2>";
$tables = ['chatbot_conversations', 'chatbot_messages', 'chatbot_training'];

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            // Check if table has 'id' column
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'id'");
            if ($stmt->rowCount() > 0) {
                echo "<p>✅ Table '$table' exists with 'id' column</p>";
            } else {
                echo "<p style='color: red;'>❌ Table '$table' exists but missing 'id' column</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Table '$table' does not exist</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error checking table '$table': " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

echo "<hr>";
echo "<p><strong>Done!</strong> You can delete this file after verifying the tables are correct.</p>";

