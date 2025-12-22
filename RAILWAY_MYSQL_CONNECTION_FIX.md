# 🔧 Fix Railway MySQL Connection Issues

## Problem: Can't Connect MySQL Workbench to Railway

Railway MySQL databases often **block external connections** for security. Here are solutions:

---

## Solution 1: Use Railway CLI (Easiest - Recommended)

Railway CLI can connect directly without external access issues.

### Step 1: Install Railway CLI

**Windows (PowerShell):**
```powershell
iwr https://railway.app/install.ps1 | iex
```

**Mac/Linux:**
```bash
curl -fsSL https://railway.app/install.sh | sh
```

**Or via npm (if you have Node.js):**
```bash
npm install -g @railway/cli
```

### Step 2: Login to Railway
```bash
railway login
```
This will open your browser to authenticate.

### Step 3: Link to Your Project
```bash
railway link
```
Select your project when prompted.

### Step 4: Connect to MySQL and Import
```bash
railway connect mysql
```
This opens an interactive MySQL shell connected to your Railway database.

### Step 5: Import SQL File
Once connected, you can import:

**Option A: Import from file**
```bash
# Exit the MySQL shell first (type: exit)
# Then run:
railway connect mysql < event_ticketing_db.sql
```

**Option B: Copy-paste SQL**
1. Run: `railway connect mysql`
2. This opens MySQL shell
3. Open your SQL file in a text editor
4. Copy all SQL
5. Paste into the MySQL shell
6. Press Enter to execute

---

## Solution 2: Use Railway's Built-in Import (If Available)

Some Railway MySQL services have an import feature:

1. Go to MySQL service → **"Database"** → **"Data"** tab
2. Look for **"Import"** or **"Upload"** button
3. If available, click it and select your SQL file
4. Wait for import to complete

---

## Solution 3: Enable Public Network Access (If Needed)

**⚠️ Warning:** This exposes your database to the internet. Only do this temporarily for import, then disable it.

1. Go to MySQL service → **"Database"** → **"Credentials"** tab
2. Look for **"Public Network"** toggle or setting
3. Enable it temporarily
4. Try connecting again from MySQL Workbench
5. **After import, disable it again for security!**

---

## Solution 4: Use Railway's Query Feature (If Available)

Some Railway versions have a hidden query feature:

1. Go to MySQL service → **"Database"** → **"Data"** tab
2. Look for:
   - A small SQL icon
   - "Run SQL" button
   - Three dots menu (⋯) with "Query" option
   - Keyboard shortcut hint
3. If you find it:
   - Open your SQL file
   - Copy all content
   - Paste into the query editor
   - Run it

---

## Solution 5: Create a Temporary PHP Script on Railway

Deploy a simple PHP script to Railway that imports your database:

### Step 1: Create Import Script

Create `import_db.php` in your project root:

```php
<?php
// import_db.php - Temporary script to import database
// DELETE THIS FILE AFTER IMPORTING!

require_once __DIR__ . '/config/db_connect.php';

// Read SQL file
$sqlFile = __DIR__ . '/database/database.sql';

if (!file_exists($sqlFile)) {
    die("SQL file not found: $sqlFile");
}

$sql = file_get_contents($sqlFile);

// Split by semicolons (basic - might need improvement for complex SQL)
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && !preg_match('/^--/', $stmt);
    }
);

echo "Starting import...\n";
echo "Found " . count($statements) . " statements\n\n";

$success = 0;
$errors = 0;

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    try {
        $pdo->exec($statement);
        $success++;
        echo "✓ Executed statement\n";
    } catch (PDOException $e) {
        $errors++;
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n\nImport complete!\n";
echo "Success: $success\n";
echo "Errors: $errors\n";
echo "\n⚠️ DELETE THIS FILE NOW FOR SECURITY!\n";
?>
```

### Step 2: Deploy to Railway
1. Commit and push this file
2. Railway will auto-deploy

### Step 3: Run Import
1. Visit: `https://your-app.railway.app/import_db.php`
2. Wait for import to complete
3. **DELETE the file immediately after!**

---

## Solution 6: Use Railway's Connect Feature

Railway has a "Connect" button that might help:

1. Go to MySQL service → **"Database"** → **"Data"** tab
2. Look for **"Connect"** button (you mentioned seeing this)
3. Click it - it might show connection options
4. Look for "Import" or "Upload SQL" option

---

## Recommended: Use Railway CLI (Solution 1)

**Why Railway CLI is best:**
- ✅ No connection issues (connects internally)
- ✅ Works reliably
- ✅ Free and easy to use
- ✅ No security concerns

**Quick Steps:**
```bash
# 1. Install
npm install -g @railway/cli

# 2. Login
railway login

# 3. Link project
railway link

# 4. Import database
railway connect mysql < event_ticketing_db.sql
```

That's it! Your database will be imported.

---

## Still Having Issues?

If none of these work:

1. **Check Railway MySQL Status:**
   - Make sure MySQL service shows "Online" (green dot)
   - Check if there are any errors in MySQL logs

2. **Verify Connection Details:**
   - Go to MySQL service → Variables tab
   - Check `MYSQLHOST`, `MYSQLPORT`, etc.
   - Make sure you're using the exact values shown

3. **Try Different Method:**
   - Railway CLI is most reliable
   - Or use the PHP import script method

4. **Contact Railway Support:**
   - Railway Discord: https://discord.gg/railway
   - Very helpful community!

---

**The Railway CLI method (Solution 1) is your best bet! It avoids all connection issues.** 🚀

