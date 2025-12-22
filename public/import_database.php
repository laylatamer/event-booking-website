<?php
/**
 * Database Import Script for Railway
 * 
 * This script imports your database from SQL file.
 * 
 * ⚠️ SECURITY WARNING: DELETE THIS FILE AFTER IMPORTING!
 * This file should NOT be committed to Git or left on the server.
 */

// Enable error display temporarily for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Only allow this script to run if explicitly enabled
// Set this to true temporarily to run the import
$ALLOW_IMPORT = true;

if (!$ALLOW_IMPORT) {
    die("Import is disabled. Set \$ALLOW_IMPORT = true to enable.");
}

// Try to load database connection with error handling
try {
    // Get project root - go up from public folder
    $projectRoot = dirname(__DIR__);
    
    // Normalize path (remove double slashes, etc.)
    $projectRoot = str_replace('//', '/', $projectRoot);
    if ($projectRoot === '/' || $projectRoot === '') {
        // Fallback: try to find project root by going up from current directory
        $projectRoot = '/app';
    }
    
    $dbConfigPath = rtrim($projectRoot, '/') . '/config/db_connect.php';
    
    // Debug info
    $debugInfo = [
        "Current __DIR__: " . __DIR__,
        "Project root: " . $projectRoot,
        "Config path: " . $dbConfigPath,
        "File exists: " . (file_exists($dbConfigPath) ? 'Yes' : 'No'),
        "Parent dir exists: " . (is_dir($projectRoot) ? 'Yes' : 'No'),
        "Config dir exists: " . (is_dir($projectRoot . '/config') ? 'Yes' : 'No')
    ];
    
    if (!file_exists($dbConfigPath)) {
        throw new Exception("Database config file not found: $dbConfigPath\n\nDebug info:\n" . implode("\n", $debugInfo));
    }
    require_once $dbConfigPath;
    
    // Check if $pdo was created
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Database connection failed. PDO object not created.");
    }
} catch (Exception $e) {
    die("❌ Database Connection Error: " . nl2br(htmlspecialchars($e->getMessage())));
}

// SQL file path (go up one level from public folder)
$projectRoot = dirname(__DIR__);
$projectRoot = str_replace('//', '/', $projectRoot);
if ($projectRoot === '/' || $projectRoot === '') {
    $projectRoot = '/app';
}

$sqlFile = rtrim($projectRoot, '/') . '/database/event_ticketing_db.sql';

// Check if file exists
if (!file_exists($sqlFile)) {
    die("❌ SQL file not found: $sqlFile<br><br>" .
        "Please ensure database/event_ticketing_db.sql exists.<br>" .
        "Current directory: " . __DIR__ . "<br>" .
        "Project root: " . $projectRoot . "<br>" .
        "Looking for: " . htmlspecialchars($sqlFile) . "<br>" .
        "Project root exists: " . (is_dir($projectRoot) ? 'Yes' : 'No') . "<br>" .
        "Database directory exists: " . (is_dir($projectRoot . '/database') ? 'Yes' : 'No'));
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

