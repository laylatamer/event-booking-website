<?php
/**
 * Database Import Script for Railway
 * 
 * This script imports your database from SQL file.
 * 
 * ⚠️ SECURITY WARNING: DELETE THIS FILE AFTER IMPORTING!
 * This file should NOT be committed to Git or left on the server.
 */

// Only allow this script to run if explicitly enabled
// Set this to true temporarily to run the import
$ALLOW_IMPORT = true;

if (!$ALLOW_IMPORT) {
    die("Import is disabled. Set \$ALLOW_IMPORT = true to enable.");
}

// Load database connection (go up one level from public folder)
require_once dirname(__DIR__) . '/config/db_connect.php';

// SQL file path (go up one level from public folder)
$sqlFile = dirname(__DIR__) . '/database/event_ticketing_db.sql';

// Check if file exists
if (!file_exists($sqlFile)) {
    die("❌ SQL file not found: $sqlFile\n\nPlease ensure database/event_ticketing_db.sql exists.");
}

echo "📦 Starting database import...\n";
echo "📄 Reading SQL file: $sqlFile\n\n";

// Read SQL file
$sql = file_get_contents($sqlFile);

if (empty($sql)) {
    die("❌ SQL file is empty!\n");
}

// Remove BOM if present
$sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);

// Split SQL into individual statements
// Handle multi-line statements and comments properly
$statements = [];
$currentStatement = '';
$inString = false;
$stringChar = '';
$inComment = false;
$commentType = '';

$lines = explode("\n", $sql);

foreach ($lines as $line) {
    $line = rtrim($line);
    
    // Skip empty lines
    if (empty($line) && empty($currentStatement)) {
        continue;
    }
    
    // Handle comments
    if (preg_match('/^\s*--/', $line) || preg_match('/^\s*#/', $line)) {
        continue; // Skip comment lines
    }
    
    if (preg_match('/\/\*/', $line)) {
        $inComment = true;
        $commentType = '/*';
    }
    
    if ($inComment) {
        if (preg_match('/\*\//', $line)) {
            $inComment = false;
            // Remove comment from line
            $line = preg_replace('/.*?\*\//', '', $line);
        } else {
            continue; // Skip comment lines
        }
    }
    
    // Handle strings
    $chars = str_split($line);
    foreach ($chars as $char) {
        if (!$inString && ($char === '"' || $char === "'" || $char === '`')) {
            $inString = true;
            $stringChar = $char;
        } elseif ($inString && $char === $stringChar) {
            // Check if escaped
            $prevChar = end($chars);
            if ($prevChar !== '\\') {
                $inString = false;
                $stringChar = '';
            }
        }
    }
    
    $currentStatement .= $line . "\n";
    
    // If line ends with semicolon and we're not in a string, it's a complete statement
    if (substr(rtrim($line), -1) === ';' && !$inString) {
        $statement = trim($currentStatement);
        if (!empty($statement) && strlen($statement) > 5) { // Ignore very short statements
            $statements[] = $statement;
        }
        $currentStatement = '';
    }
}

// If there's a remaining statement without semicolon, add it
if (!empty(trim($currentStatement))) {
    $statements[] = trim($currentStatement);
}

echo "📊 Found " . count($statements) . " SQL statements to execute\n\n";

// Execute statements
$success = 0;
$errors = 0;
$errorMessages = [];

foreach ($statements as $index => $statement) {
    // Skip empty statements
    if (empty(trim($statement))) {
        continue;
    }
    
    // Skip certain statements that might cause issues
    if (preg_match('/^(USE|SET|DELIMITER)/i', trim($statement))) {
        continue;
    }
    
    try {
        $pdo->exec($statement);
        $success++;
        
        // Show progress every 10 statements
        if (($index + 1) % 10 === 0) {
            echo "✓ Processed " . ($index + 1) . " statements...\n";
        }
    } catch (PDOException $e) {
        $errors++;
        $errorMsg = $e->getMessage();
        $errorMessages[] = "Statement " . ($index + 1) . ": " . $errorMsg;
        
        // Don't stop on certain errors (like table already exists)
        if (strpos($errorMsg, 'already exists') !== false || 
            strpos($errorMsg, 'Duplicate') !== false) {
            // These are usually okay - table/data already exists
            continue;
        }
        
        echo "⚠️  Warning on statement " . ($index + 1) . ": " . $errorMsg . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ Import Complete!\n\n";
echo "📈 Statistics:\n";
echo "   Success: $success statements\n";
echo "   Errors: $errors statements\n";

if ($errors > 0 && !empty($errorMessages)) {
    echo "\n⚠️  Error Details:\n";
    foreach (array_slice($errorMessages, 0, 10) as $msg) {
        echo "   - $msg\n";
    }
    if (count($errorMessages) > 10) {
        echo "   ... and " . (count($errorMessages) - 10) . " more errors\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "⚠️  SECURITY WARNING:\n";
echo "   DELETE THIS FILE (import_database.php) NOW!\n";
echo "   It should NOT remain on your server.\n";
echo str_repeat("=", 50) . "\n";
?>

