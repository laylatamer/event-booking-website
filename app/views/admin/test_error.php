<?php
/**
 * Test Error Handler
 * This file is for testing the error handler
 * 
 * To test:
 * 1. Uncomment one of the error types below
 * 2. Navigate to: /event-booking-website/app/views/admin/test_error.php
 * 3. You should be redirected to the error page
 */

// Include error handler FIRST
require_once __DIR__ . '/../../../config/error_handler.php';

// Require admin access
require_once __DIR__ . '/../../../database/session_init.php';
requireAdmin();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Error Handler Test</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        code { background: #f4f4f4; padding: 2px 5px; }
    </style>
</head>
<body>
    <h1>Error Handler Test Page</h1>
    <p>This page tests the error handler. Uncomment one of the error types below to test.</p>
    
    <div class="test-section">
        <h2>Test 1: Fatal Error (Undefined Function)</h2>
        <p>Uncomment the line below to test:</p>
        <code>// callUndefinedFunction();</code>
    </div>
    
    <div class="test-section">
        <h2>Test 2: Uncaught Exception</h2>
        <p>Uncomment the line below to test:</p>
        <code>// throw new Exception("Test exception");</code>
    </div>
    
    <div class="test-section">
        <h2>Test 3: Fatal Error (Call to Non-Object Method)</h2>
        <p>Uncomment the line below to test:</p>
        <code>// $null = null; $null->method();</code>
    </div>
    
    <div class="test-section">
        <h2>Test 4: Division by Zero (Warning - won't redirect, just logs)</h2>
        <p>Uncomment the line below to test:</p>
        <code>// $result = 1 / 0;</code>
    
    
    <p><strong>Note:</strong> Parse errors (syntax errors) cannot be caught by PHP error handlers because they occur during compilation. They will be handled by Apache's ErrorDocument directive.</p>
    
    <p><a href="index.php">Back to Admin Panel</a></p>
</body>
</html>

