# Testing the Error Handler

This guide explains how to test the error handling system that has been implemented.

## Quick Test Methods

### Method 1: Use the Test Page (Easiest)

1. Navigate to: `http://localhost/event-booking-website/app/views/test_error.php`
2. Click any of the test buttons to trigger different types of errors
3. You should be redirected to the custom error page (`error.php`) with a 500 status code

### Method 2: Manual Testing in auth.php

1. Open `app/views/auth.php`
2. Add this line anywhere in the code (after the error handler is loaded):
   ```php
   throw new Exception("Test error - remove this line after testing");
   ```
3. Save and navigate to the auth page
4. You should see the custom error page instead of the default PHP error

### Method 3: Test Database Connection Error

1. Temporarily modify `config/db_connect.php`:
   ```php
   $db = 'wrong_database_name'; // Change this to trigger error
   ```
2. Navigate to any page that uses the database (e.g., `auth.php`)
3. You should see the custom error page

### Method 4: Test Fatal Error

1. In any PHP file that includes the error handler, add:
   ```php
   nonExistentFunction(); // This will cause a fatal error
   ```
2. Navigate to that page
3. You should see the custom error page

## What to Look For

When an error occurs, you should see:

✅ **Custom Error Page** (not the default PHP error page)
- Modern full-page design with orange gradient theme
- Error code displayed prominently (500 for server errors)
- "Back to Home" and "Go Back" buttons
- Animated background with particles

❌ **You should NOT see:**
- Default PHP error messages
- Stack traces in the browser
- White error pages with black text

## Testing Different Error Types

### 1. Fatal Errors (E_ERROR)
```php
nonExistentFunction();
```

### 2. Exceptions
```php
throw new Exception("Test exception");
```

### 3. Database Errors
```php
$pdo->query("SELECT * FROM non_existent_table");
```

### 4. User Errors
```php
trigger_error("Test error", E_USER_ERROR);
```

### 5. Parse Errors (Syntax Errors)
```php
$x = ; // Missing value
```

## Checking Error Logs

Errors are logged to: `logs/error.log`

To view the logs:
1. Navigate to: `event-booking-website/logs/error.log`
2. Open the file in a text editor
3. You should see detailed error information with timestamps

## Testing API Error Handling

For API endpoints (files in `public/api/`), errors should return JSON instead of redirecting:

1. Navigate to an API endpoint
2. Trigger an error (e.g., invalid request)
3. You should receive a JSON response:
   ```json
   {
     "success": false,
     "error": "An error occurred processing your request",
     "message": "Error details here"
   }
   ```

## Cleanup After Testing

**Important:** After testing, remember to:
1. Remove any test code you added
2. Restore any modified configuration files
3. Delete or restrict access to `test_error.php` in production

## Troubleshooting

If the error handler doesn't seem to work:

1. **Check if error handler is loaded:**
   - Make sure `require_once __DIR__ . '/../../config/error_handler.php';` is at the top of your file
   - It should be loaded BEFORE any other code that might cause errors

2. **Check error reporting settings:**
   - The error handler sets `display_errors` to 0, but `error_reporting` should be `E_ALL`
   - Check `php.ini` or verify in your code

3. **Check logs directory:**
   - Make sure the `logs/` directory exists and is writable
   - The error handler will try to create it automatically

4. **Check file paths:**
   - Verify that `error.php` exists at `app/views/error.php`
   - Check that paths in the error handler are correct for your setup

## Expected Behavior

- ✅ Errors redirect to custom error page
- ✅ Errors are logged to `logs/error.log`
- ✅ API errors return JSON responses
- ✅ No default PHP error pages shown
- ✅ Session data is preserved (if session was started)

