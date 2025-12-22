<?php
/**
 * Comprehensive Database Check and Fix Script
 * 
 * This script:
 * 1. Checks all required tables exist
 * 2. Fixes chatbot tables
 * 3. Verifies events data
 * 4. Tests API endpoints
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

echo "<h1>Database Diagnostic & Fix Tool</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// Required tables
$requiredTables = [
    'events',
    'venues',
    'main_categories',
    'subcategories',
    'users',
    'bookings',
    'booked_seats',
    'event_ticket_categories',
    'ticket_reservations',
    'chatbot_conversations',
    'chatbot_messages',
    'chatbot_training',
    'contact_messages'
];

echo "<h2>1. Checking Required Tables</h2>";
echo "<table>";
echo "<tr><th>Table</th><th>Status</th><th>Row Count</th></tr>";

$missingTables = [];
foreach ($requiredTables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $countStmt->fetch()['count'];
            $status = $count > 0 ? "<span class='success'>✓ Exists</span>" : "<span class='warning'>⚠ Exists (empty)</span>";
            echo "<tr><td>$table</td><td>$status</td><td>$count</td></tr>";
        } else {
            $missingTables[] = $table;
            echo "<tr><td>$table</td><td><span class='error'>✗ Missing</span></td><td>-</td></tr>";
        }
    } catch (PDOException $e) {
        echo "<tr><td>$table</td><td><span class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span></td><td>-</td></tr>";
    }
}
echo "</table>";

// Fix chatbot tables
echo "<h2>2. Fixing Chatbot Tables</h2>";
$sqlFile = __DIR__ . '/create_chatbot_tables.sql';
if (!file_exists($sqlFile)) {
    $sqlFile = dirname(__DIR__) . '/database/migrations/create_chatbot_tables.sql';
}

if (file_exists($sqlFile)) {
    $sql = file_get_contents($sqlFile);
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    $fixed = 0;
    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;
        try {
            $pdo->exec($statement);
            $fixed++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "<p class='error'>⚠️ " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
    echo "<p class='success'>✅ Fixed chatbot tables ($fixed statements executed)</p>";
} else {
    echo "<p class='error'>❌ SQL file not found: create_chatbot_tables.sql</p>";
}

// Check events data
echo "<h2>3. Checking Events Data</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE status = 'active'");
    $activeCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events");
    $totalCount = $stmt->fetch()['count'];
    
    echo "<p>Total events: <strong>$totalCount</strong></p>";
    echo "<p>Active events: <strong>$activeCount</strong></p>";
    
    if ($activeCount == 0) {
        echo "<p class='warning'>⚠️ No active events found! Events won't display on the website.</p>";
        echo "<p>You need to add events through the admin panel or import your database.</p>";
    }
    
    // Check if events have required relationships
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM events e
        LEFT JOIN subcategories s ON e.subcategory_id = s.id
        LEFT JOIN venues v ON e.venue_id = v.id
        WHERE s.id IS NULL OR v.id IS NULL
    ");
    $orphaned = $stmt->fetch()['count'];
    if ($orphaned > 0) {
        echo "<p class='error'>❌ Found $orphaned events with missing subcategories or venues!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error checking events: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Check API endpoint
echo "<h2>4. Testing Events API</h2>";
$apiUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/api/events_API.php?action=getPublicEvents';
echo "<p>API URL: <a href='$apiUrl' target='_blank'>$apiUrl</a></p>";

// Test database queries used by API
try {
    $stmt = $pdo->query("
        SELECT e.*, 
               s.name as subcategory_name,
               s.main_category_id,
               mc.name as main_category_name,
               v.name as venue_name,
               v.city as venue_city
        FROM events e
        JOIN subcategories s ON e.subcategory_id = s.id
        JOIN main_categories mc ON s.main_category_id = mc.id
        JOIN venues v ON e.venue_id = v.id
        WHERE e.status = 'active' AND e.date >= CURDATE()
        ORDER BY e.date ASC
        LIMIT 10
    ");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p class='success'>✅ Events API query works! Found " . count($events) . " upcoming events.</p>";
    
    if (count($events) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Title</th><th>Date</th><th>Venue</th></tr>";
        foreach (array_slice($events, 0, 5) as $event) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($event['id']) . "</td>";
            echo "<td>" . htmlspecialchars($event['title']) . "</td>";
            echo "<td>" . htmlspecialchars($event['date']) . "</td>";
            echo "<td>" . htmlspecialchars($event['venue_name']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>❌ Events API query failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Summary
echo "<h2>5. Summary</h2>";
if (empty($missingTables) && $activeCount > 0) {
    echo "<p class='success'><strong>✅ Database looks good!</strong></p>";
} else {
    echo "<p class='error'><strong>⚠️ Issues found:</strong></p>";
    echo "<ul>";
    if (!empty($missingTables)) {
        echo "<li>Missing tables: " . implode(', ', $missingTables) . "</li>";
    }
    if ($activeCount == 0) {
        echo "<li>No active events in database</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If chatbot tables are missing, they should be fixed now. Test the chatbot.</li>";
echo "<li>If events are missing, add them through the admin panel or import your database.</li>";
echo "<li>Test the events API: <a href='$apiUrl' target='_blank'>$apiUrl</a></li>";
echo "<li>Delete this file after fixing issues for security.</li>";
echo "</ol>";

