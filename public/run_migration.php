<?php
/**
 * Run database migration to add profile_image_path column
 * Access this file once via browser to run the migration
 */

require_once __DIR__ . '/../config/db_connect.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<h1>Database Migration: Add profile_image_path Column</h1>";

try {
    // Read migration file
    $migrationFile = __DIR__ . '/../database/migrations/add_profile_image_path.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Split by semicolons and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip empty lines and comments
        }
        
        // Replace IF NOT EXISTS with manual check for MySQL compatibility
        if (stripos($statement, 'ADD COLUMN IF NOT EXISTS') !== false) {
            // Extract column name and definition
            preg_match('/ADD COLUMN IF NOT EXISTS\s+`?(\w+)`?\s+(.+?)(?:\s+AFTER|$)/i', $statement, $matches);
            
            if (!empty($matches[1])) {
                $columnName = $matches[1];
                
                // Check if column exists
                $checkStmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE '$columnName'");
                if ($checkStmt->rowCount() === 0) {
                    // Column doesn't exist, add it
                    $statement = str_ireplace('IF NOT EXISTS', '', $statement);
                    $pdo->exec($statement);
                    echo "<p style='color: green;'>✓ Added column: $columnName</p>";
                } else {
                    echo "<p style='color: blue;'>⊘ Column already exists: $columnName</p>";
                }
            }
        } elseif (stripos($statement, 'CREATE INDEX IF NOT EXISTS') !== false) {
            // Handle index creation
            preg_match('/CREATE INDEX IF NOT EXISTS\s+`?(\w+)`?\s+ON\s+`?(\w+)`?/i', $statement, $matches);
            
            if (!empty($matches[1])) {
                $indexName = $matches[1];
                $tableName = $matches[2];
                
                // Check if index exists
                $checkStmt = $pdo->query("SHOW INDEX FROM `$tableName` WHERE Key_name = '$indexName'");
                if ($checkStmt->rowCount() === 0) {
                    // Index doesn't exist, create it
                    $statement = str_ireplace('IF NOT EXISTS', '', $statement);
                    $pdo->exec($statement);
                    echo "<p style='color: green;'>✓ Created index: $indexName</p>";
                } else {
                    echo "<p style='color: blue;'>⊘ Index already exists: $indexName</p>";
                }
            }
        } else {
            // Execute other statements directly
            $pdo->exec($statement);
            echo "<p style='color: green;'>✓ Executed statement</p>";
        }
    }
    
    echo "<h2 style='color: green;'>Migration completed successfully!</h2>";
    echo "<p><a href='../app/views/admin/index.php'>Go to Admin Panel</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Migration failed!</h2>";
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

