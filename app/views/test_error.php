<?php
/**
 * Error Handler Test Page
 * This file intentionally triggers various types of errors to test the error handler
 * 
 * WARNING: This file is for testing purposes only. Remove or restrict access in production.
 */

// Load error handler first
require_once __DIR__ . '/../../config/error_handler.php';

// Load session (optional, but good to test with sessions)
require_once __DIR__ . '/../../database/session_init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Handler Test | Eحgzly</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #0a0b0f;
            color: #e5e7eb;
            padding: 40px 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        h1 {
            color: #ff7a3e;
            margin-bottom: 30px;
            font-size: 32px;
        }
        
        .warning {
            background: rgba(255, 122, 62, 0.1);
            border: 1px solid rgba(255, 122, 62, 0.3);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 30px;
            color: #ffd9c5;
        }
        
        .test-section {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 122, 62, 0.2);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }
        
        .test-section h2 {
            color: #fff;
            margin-bottom: 12px;
            font-size: 20px;
        }
        
        .test-section p {
            color: #cbd5e1;
            margin-bottom: 16px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #ff7a3e, #d65a2e);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 122, 62, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .btn-danger:hover {
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }
        
        .code {
            background: rgba(0, 0, 0, 0.3);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #ffd9c5;
        }
        
        .back-link {
            margin-top: 30px;
            text-align: center;
        }
        
        .back-link a {
            color: #ff7a3e;
            text-decoration: none;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Error Handler Test Page</h1>
        
        <div class="warning">
            <strong>⚠️ Warning:</strong> This page is for testing purposes only. It will intentionally trigger errors to test the error handling system. Make sure to remove or restrict access to this file in production.
        </div>
        
        <div class="test-section">
            <h2>Test 1: Fatal Error (E_ERROR)</h2>
            <p>This will trigger a fatal error by calling a non-existent function. The error handler should catch this and redirect to the error page.</p>
            <form method="POST" action="">
                <input type="hidden" name="test" value="fatal_error">
                <button type="submit" class="btn btn-danger">Trigger Fatal Error</button>
            </form>
        </div>
        
        <div class="test-section">
            <h2>Test 2: Exception</h2>
            <p>This will throw an uncaught exception. The exception handler should catch this and redirect to the error page.</p>
            <form method="POST" action="">
                <input type="hidden" name="test" value="exception">
                <button type="submit" class="btn btn-danger">Throw Exception</button>
            </form>
        </div>
        
        <div class="test-section">
            <h2>Test 3: Database Error</h2>
            <p>This will attempt to execute an invalid SQL query. The error handler should catch the PDO exception and redirect to the error page.</p>
            <form method="POST" action="">
                <input type="hidden" name="test" value="database_error">
                <button type="submit" class="btn btn-danger">Trigger Database Error</button>
            </form>
        </div>
        
        <div class="test-section">
            <h2>Test 4: Parse Error (Syntax Error)</h2>
            <p>This will attempt to include a file with a syntax error. Note: Parse errors are caught by the shutdown handler.</p>
            <form method="POST" action="">
                <input type="hidden" name="test" value="parse_error">
                <button type="submit" class="btn btn-danger">Trigger Parse Error</button>
            </form>
        </div>
        
        <div class="test-section">
            <h2>Test 5: User Error (E_USER_ERROR)</h2>
            <p>This will trigger a user-defined error. The error handler should catch this and redirect to the error page.</p>
            <form method="POST" action="">
                <input type="hidden" name="test" value="user_error">
                <button type="submit" class="btn btn-danger">Trigger User Error</button>
            </form>
        </div>
        
        <div class="back-link">
            <a href="homepage.php">← Back to Homepage</a>
        </div>
    </div>
    
    <?php
    // Handle test requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test'])) {
        $test = $_POST['test'];
        
        switch ($test) {
            case 'fatal_error':
                // Trigger a fatal error by calling non-existent function
                nonExistentFunction();
                break;
                
            case 'exception':
                // Throw an uncaught exception
                throw new Exception("This is a test exception to verify the error handler is working correctly.");
                break;
                
            case 'database_error':
                // Try to execute invalid SQL
                try {
                    require_once __DIR__ . '/../../config/db_connect.php';
                    $pdo->query("SELECT * FROM non_existent_table_xyz_123");
                } catch (PDOException $e) {
                    // Re-throw to test exception handler
                    throw new Exception("Database error: " . $e->getMessage());
                }
                break;
                
            case 'parse_error':
                // Create a temporary file with syntax error and include it
                $tempFile = sys_get_temp_dir() . '/test_parse_error_' . time() . '.php';
                file_put_contents($tempFile, '<?php $x = ; // Syntax error');
                include $tempFile;
                unlink($tempFile);
                break;
                
            case 'user_error':
                // Trigger user-defined error
                trigger_error("This is a test user error to verify the error handler is working correctly.", E_USER_ERROR);
                break;
        }
    }
    ?>
</body>
</html>

