# 🚂 Complete Railway Deployment Guide
## Step-by-Step Instructions for Event Booking Website

---

## 📋 Prerequisites Checklist

Before starting, ensure you have:
- [ ] GitHub account (free)
- [ ] Your project is in a Git repository
- [ ] Project pushed to GitHub
- [ ] Basic understanding of Git commands

---

## Step 1: Prepare Your Project for Railway

### 1.1 Update Database Configuration

We need to update `config/db_connect.php` to use environment variables so it works on Railway.

**Current file uses hardcoded values. We'll update it to support both local and Railway environments.**

### 1.2 Create Railway Configuration File

Create `nixpacks.toml` in your project root to tell Railway how to run your PHP app.

---

## Step 2: Code Changes Required

### 2.1 Update `config/db_connect.php`

Replace the entire file with this Railway-compatible version:

```php
<?php
// Configuration for the database connection
// Supports both local development and Railway deployment
// NOTE: Database names in MySQL should not contain spaces.

// Get database credentials from environment variables (Railway) or use defaults (local)
$host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$db = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'event_ticketing_db';
$user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
$port = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306';
$charset = 'utf8mb4';

// Build DSN with port support
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    // Throw exceptions on error
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    // Return data as associative arrays
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Disable prepared statement emulation
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create a new PDO instance, which attempts the connection
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If the connection fails, terminate the script and show an error.
    die("<h1>Database Connection Failed</h1><p>Please check your credentials in *db_connect.php*.<br>Detailed Error: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// The $pdo object is now successfully connected and ready to use.

class Database {
    private $connection;

    public function __construct() {
        global $pdo;
        $this->connection = $pdo;
    }

    public function getConnection() {
        return $this->connection;
    }
}
?>
```

**What this does:**
- Checks for Railway environment variables first (`MYSQLHOST`, `MYSQLDATABASE`, etc.)
- Falls back to generic environment variables (`DB_HOST`, `DB_NAME`, etc.)
- Falls back to local defaults if no environment variables are set
- Works on both local XAMPP and Railway

### 2.2 Create `nixpacks.toml` in Project Root

Create this file in your project root directory (`event-booking-website/nixpacks.toml`):

```toml
[phases.setup]
nixPkgs = ['php82', 'php82Packages.composer', 'php82Extensions.pdo', 'php82Extensions.pdo_mysql', 'php82Extensions.mysqli', 'php82Extensions.curl', 'php82Extensions.gd', 'php82Extensions.mbstring', 'php82Extensions.zip', 'php82Extensions.xml']

[phases.install]
cmds = [
    'composer install --no-dev --optimize-autoloader || true'
]

[start]
cmd = 'php -S 0.0.0.0:$PORT -t public'
```

**What this does:**
- Installs PHP 8.2 with all required extensions (PDO, MySQL, cURL, GD, etc.)
- Installs Composer (if you use it)
- Runs your app from the `public` folder
- Uses Railway's `$PORT` environment variable

### 2.3 Commit and Push Changes

```bash
git add .
git commit -m "Prepare for Railway deployment"
git push origin main
```

---

## Step 3: Create Railway Account and Project

### 3.1 Sign Up for Railway

1. Go to https://railway.app/
2. Click **"Start a New Project"** or **"Login"**
3. Click **"Deploy from GitHub repo"**
4. Authorize Railway to access your GitHub account
5. Select your repository: `event-booking-website`

### 3.2 Railway Will Auto-Detect PHP

Railway will automatically:
- Detect PHP from your `nixpacks.toml` or project structure
- Start building your application
- Create a service for your app

**Wait for the initial build to complete** (2-5 minutes)

You'll see build logs in real-time. If there are errors, check the logs.

---

## Step 4: Add MySQL Database

### 4.1 Create MySQL Service

1. In Railway dashboard, click **"+ New"** button (top right)
2. Select **"Database"**
3. Select **"Add MySQL"**
4. Railway will create a MySQL database automatically
5. Wait for it to provision (30 seconds - 2 minutes)

### 4.2 Get Database Credentials

1. Click on the **MySQL service** you just created
2. Go to the **"Variables"** tab
3. You'll see these variables (save them for later):
   - `MYSQLHOST` - Database host (e.g., `containers-us-west-xxx.railway.app`)
   - `MYSQLDATABASE` - Database name (usually `railway`)
   - `MYSQLUSER` - Database username (usually `root`)
   - `MYSQLPASSWORD` - Database password (auto-generated)
   - `MYSQLPORT` - Database port (usually `3306`)

**Example values:**
```
MYSQLHOST=containers-us-west-123.railway.app
MYSQLDATABASE=railway
MYSQLUSER=root
MYSQLPASSWORD=abc123xyz456
MYSQLPORT=3306
```

**⚠️ Important:** Copy the `MYSQLPASSWORD` - you won't be able to see it again easily!

---

## Step 5: Connect Database to Your App

### 5.1 Link Database to Your App Service

**Method 1: Automatic Linking (Easiest)**

1. Go to your **PHP app service** (not the MySQL service)
2. Click **"Variables"** tab
3. Railway should automatically show **"Add Reference"** button
4. Click it and select your MySQL service
5. Railway will automatically add all MySQL variables

**Method 2: Manual Linking**

1. In your **PHP app service** → **Variables** tab
2. Click **"Raw Editor"** (top right)
3. Add these variables (copy from MySQL service Variables tab):

```json
{
  "MYSQLHOST": "containers-us-west-xxx.railway.app",
  "MYSQLDATABASE": "railway",
  "MYSQLUSER": "root",
  "MYSQLPASSWORD": "your_password_here",
  "MYSQLPORT": "3306"
}
```

4. Click **"Update Variables"**

### 5.2 Verify Environment Variables

Your PHP app service should now have these variables:
- ✅ `MYSQLHOST`
- ✅ `MYSQLDATABASE`
- ✅ `MYSQLUSER`
- ✅ `MYSQLPASSWORD`
- ✅ `MYSQLPORT`

**To verify:**
1. Go to PHP app service → Variables tab
2. You should see all 5 MySQL variables listed

---

## Step 6: Configure Additional Environment Variables (Optional)

### 6.1 Add App-Specific Variables

In your **PHP app service** → **Variables** tab, you can add:

**Base URL (for your app):**
```
APP_URL=https://your-app-name.railway.app
```

**AI Chatbot Config (if you want to override):**
```
AI_API_KEY=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
```

**Note:** Your `config/ai_config.php` already has the API key, so this is optional.

### 6.2 Complete Variables List

Your final variables should look like:

```
MYSQLHOST=containers-us-west-xxx.railway.app
MYSQLDATABASE=railway
MYSQLUSER=root
MYSQLPASSWORD=your_password
MYSQLPORT=3306
APP_URL=https://your-app-name.railway.app (optional)
```

---

## Step 7: Import Database Schema

### 7.1 Method 1: Using Railway's Database Tab (Easiest)

1. Go to your **MySQL service** in Railway
2. Click **"Data"** tab
3. You'll see a database viewer
4. Click **"Query"** tab
5. Copy and paste SQL from your migration files one by one:

**Import in this order:**
1. `database/database.sql` (main schema)
2. `database/migrations/create_bookings_table.sql`
3. `database/migrations/create_booked_seats_table.sql`
4. `database/migrations/add_booking_customer_fields.sql`
5. `database/migrations/add_seating_system.sql`
6. `database/migrations/add_subcategory_image_url.sql`

**For each file:**
- Open the SQL file locally
- Copy all contents
- Paste into Railway Query tab
- Click "Run" or press Ctrl+Enter
- Wait for "Query executed successfully"

### 7.2 Method 2: Using External MySQL Client

1. Get connection details from Railway MySQL service → **Variables** tab
2. Use MySQL Workbench, DBeaver, or command line:

```bash
mysql -h containers-us-west-xxx.railway.app \
      -u root \
      -p \
      -P 3306 \
      railway < database/database.sql
```

3. Enter password when prompted
4. Repeat for each migration file

### 7.3 Method 3: Using phpMyAdmin (if available)

Some Railway MySQL services provide phpMyAdmin access. Check your MySQL service for a "phpMyAdmin" button or link.

### 7.4 Verify Database Tables

After importing, verify these tables exist:

**Core Tables:**
- ✅ `users`
- ✅ `events`
- ✅ `bookings`
- ✅ `booked_seats`
- ✅ `categories`
- ✅ `subcategories`
- ✅ `ticket_categories`
- ✅ `venues`

**Chatbot Tables:**
- ✅ `chatbot_conversations`
- ✅ `chatbot_messages`
- ✅ `chatbot_training`

**Other Tables:**
- ✅ `contact_messages`
- ✅ `ticket_reservations`

**To verify:**
1. Go to MySQL service → Data tab
2. You should see all tables listed on the left sidebar

---

## Step 8: Configure File Uploads

### 8.1 Understanding Railway's Filesystem

**Important:** Railway's filesystem is **ephemeral** - files are deleted when you redeploy!

You have two options:

### 8.2 Option A: Use Railway's Volume (Recommended)

This persists uploads across deployments:

1. Go to your **PHP app service**
2. Click **"Volumes"** tab
3. Click **"Add Volume"**
4. Set:
   - **Mount Path:** `/app/public/uploads`
   - **Name:** `uploads` (or any name)
5. Click **"Add"**
6. Railway will create a persistent volume

**After adding volume:**
- Uploads will persist across deployments
- Files stored in `/app/public/uploads` will be saved
- Volume survives app restarts

### 8.3 Option B: Use External Storage (Best for Production)

For production, consider using:
- **AWS S3** (free tier available)
- **Cloudinary** (free tier available)
- **Google Cloud Storage**
- **DigitalOcean Spaces**

This requires updating your upload code, but is more reliable.

**For now, use Option A (Volumes) to get started quickly.**

### 8.4 Set Upload Directory Permissions

After adding volume, Railway handles permissions automatically. Your upload code should work as-is.

---

## Step 9: Deploy and Test

### 9.1 Trigger Deployment

Railway automatically deploys when you push to GitHub, but you can also:

1. Go to your **PHP app service**
2. Click **"Deployments"** tab
3. Click **"Redeploy"** button (if needed)
4. Wait for deployment to complete

### 9.2 Get Your App URL

1. Go to your **PHP app service**
2. Click **"Settings"** tab
3. Under **"Domains"**, you'll see:
   - **Default Domain:** `your-app-name.railway.app`
   - This is your live URL!

4. Click on the domain to open it in a new tab

### 9.3 Test Your Application

Visit: `https://your-app-name.railway.app`

**Test these features systematically:**

**Basic Functionality:**
- [ ] Homepage loads correctly
- [ ] CSS and images load
- [ ] Navigation works

**User Features:**
- [ ] User registration
- [ ] User login
- [ ] User profile page
- [ ] Logout

**Event Features:**
- [ ] Event listing page
- [ ] Event detail page
- [ ] Event filtering/search
- [ ] Event images display

**Booking Features:**
- [ ] Seat selection (stadium/theatre/standing)
- [ ] Ticket customization
- [ ] Booking creation
- [ ] Checkout process
- [ ] Booking confirmation

**Admin Features:**
- [ ] Admin login
- [ ] Admin dashboard
- [ ] Events management
- [ ] Bookings management
- [ ] Categories management
- [ ] Users management

**Other Features:**
- [ ] Chatbot responds
- [ ] File uploads work
- [ ] Contact form works
- [ ] Mobile responsive

### 9.4 Check Logs if Issues Occur

1. Go to PHP app service → **"Deployments"** tab
2. Click on the latest deployment
3. Click **"View Logs"**
4. Look for errors or warnings

---

## Step 10: Configure Custom Domain (Optional)

### 10.1 Add Custom Domain

1. Go to your **PHP app service** → **"Settings"** → **"Domains"**
2. Click **"Custom Domain"**
3. Enter your domain (e.g., `yourapp.com` or `www.yourapp.com`)
4. Click **"Add"**

### 10.2 Update DNS Records

Railway will provide DNS records to add:

**Example DNS records:**
```
Type: CNAME
Name: www (or @ for root domain)
Value: your-app-name.railway.app
```

1. Go to your domain registrar (GoDaddy, Namecheap, Cloudflare, etc.)
2. Add the CNAME record Railway provides
3. Save changes

### 10.3 Wait for DNS Propagation

- DNS propagation usually takes 5-60 minutes
- Can take up to 24 hours in rare cases
- Check propagation status: https://www.whatsmydns.net/

### 10.4 Automatic HTTPS

Railway automatically provides HTTPS for your custom domain!
- No SSL certificate setup needed
- Automatic renewal
- Free forever

---

## Step 11: Post-Deployment Configuration

### 11.1 Update Base URLs in Code (if needed)

Most of your code should work automatically, but check:

**`public/js/chatbot-widget.js`** (line 37):
```javascript
// Update fallback URL if needed
// Should auto-detect, but if chatbot doesn't work, update:
return '/public/api/chatbot.php';
```

**`public/api/uploads.php`** (line 92):
```php
// Should auto-detect, but verify it uses:
$baseUrl = 'https://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . '/';
```

### 11.2 Configure AI Chatbot

Your `config/ai_config.php` should work as-is. The API key is already configured.

If you want to override it with environment variables:
1. Add `AI_API_KEY` to Railway variables
2. Update `AIChatbotService.php` to read from environment

### 11.3 Set Up Monitoring

1. In Railway → Your service → **"Metrics"** tab
2. Monitor:
   - **CPU usage** - Should be low for small apps
   - **Memory usage** - Watch for memory leaks
   - **Request count** - Track traffic
   - **Error rate** - Should be 0% or very low

### 11.4 Set Up Alerts (Optional)

1. Go to Railway → **Settings** → **Notifications**
2. Set up email alerts for:
   - Deployment failures
   - High resource usage
   - Service downtime

---

## Step 12: Troubleshooting

### Common Issues and Solutions

#### Issue 1: Database Connection Failed

**Symptoms:**
- Error: "Database Connection Failed"
- 500 Internal Server Error
- App won't load

**Solutions:**
1. Verify all MySQL environment variables are set in PHP app service
2. Check that MySQL service is running (green status)
3. Ensure variables are in PHP app service, not just MySQL service
4. Verify variable names match exactly: `MYSQLHOST`, `MYSQLDATABASE`, etc.
5. Check database credentials are correct
6. Redeploy after adding variables

**Debug:**
- Check Railway logs for specific error message
- Verify DSN string in logs

#### Issue 2: 500 Internal Server Error

**Symptoms:**
- Blank page or 500 error
- App doesn't load

**Solutions:**
1. Check Railway logs: Service → Deployments → View Logs
2. Look for PHP errors in logs
3. Verify `nixpacks.toml` is correct
4. Check file permissions
5. Verify `public` folder exists and has `index.php` or entry point

**Debug:**
- Enable error display temporarily (not recommended for production)
- Check PHP error logs in Railway

#### Issue 3: Files Not Uploading

**Symptoms:**
- Uploads fail
- Files disappear after redeploy

**Solutions:**
1. Ensure volume is mounted correctly (Step 8)
2. Check upload directory exists: `/app/public/uploads`
3. Verify volume mount path is correct
4. Check file permissions (should be automatic)
5. Verify upload size limits in Railway

**Debug:**
- Check Railway logs for upload errors
- Verify volume is attached: Service → Volumes tab

#### Issue 4: Routes Not Working / 404 Errors

**Symptoms:**
- Pages return 404
- Routes don't work
- API endpoints fail

**Solutions:**
1. Verify `nixpacks.toml` points to `public` folder: `-t public`
2. Check `.htaccess` if using Apache rewrites (Railway uses PHP built-in server)
3. Update routes to work without `.htaccess` if needed
4. Verify entry point is in `public` folder

**Note:** Railway uses PHP built-in server, so some Apache-specific features may not work.

#### Issue 5: Environment Variables Not Working

**Symptoms:**
- Database connection fails
- Config values are wrong
- App uses default values instead of Railway values

**Solutions:**
1. Ensure variables are in PHP app service (not MySQL service)
2. Redeploy after adding variables
3. Verify variable names match exactly (case-sensitive)
4. Use `getenv()` in PHP to read them
5. Check Raw Editor to see all variables

**Debug:**
- Add temporary `phpinfo()` to see all environment variables
- Check Railway Variables tab shows all variables

#### Issue 6: Build Fails

**Symptoms:**
- Deployment fails
- Build errors in logs

**Solutions:**
1. Check `nixpacks.toml` syntax is correct
2. Verify PHP version and extensions are available
3. Check for missing dependencies
4. Review build logs for specific errors
5. Ensure all required files are committed to Git

**Debug:**
- Check build logs line by line
- Verify file paths are correct

#### Issue 7: Slow Performance

**Symptoms:**
- App is slow
- Timeouts

**Solutions:**
1. Check Railway Metrics for resource usage
2. Optimize database queries
3. Enable caching if possible
4. Consider upgrading Railway plan
5. Check for memory leaks

---

## Step 13: Railway Dashboard Overview

### Key Sections in Railway Dashboard:

1. **Projects** - All your Railway projects
   - Click to see all services in a project

2. **Services** - Individual services (PHP app, MySQL, etc.)
   - Each service has its own configuration

3. **Deployments** - Deployment history and logs
   - See all deployments
   - View logs for each deployment
   - Redeploy from here

4. **Variables** - Environment variables
   - Add/edit/delete variables
   - Link services automatically
   - Use Raw Editor for bulk editing

5. **Metrics** - Performance monitoring
   - CPU usage
   - Memory usage
   - Request count
   - Error rate

6. **Settings** - Service configuration
   - Service name
   - Domains
   - Build settings
   - Health checks

7. **Volumes** - Persistent storage
   - Add volumes for file uploads
   - Manage persistent data

8. **Domains** - Custom domain setup
   - Add custom domains
   - Automatic HTTPS

---

## Step 14: Railway Pricing

### Free Tier:
- **$5/month in credits** (free forever)
- Usually enough for small-medium apps
- Automatic HTTPS
- Unlimited deployments
- MySQL database included

### If You Exceed Free Credits:
- **Hobby Plan:** $5/month
  - Additional credits
  - Priority support
- **Pro Plan:** $20/month
  - More credits
  - Better performance
  - Production-ready

### Cost Estimation:
- **PHP App:** ~$0.50-2/month (depending on usage)
- **MySQL Database:** ~$0.50-1/month
- **Total:** Usually under $5/month (covered by free tier!)

**For most projects, the free $5/month credits are sufficient!**

---

## Step 15: Best Practices

### 15.1 Environment Variables
- Never commit sensitive data to Git
- Use Railway Variables for all secrets
- Use different variables for different environments

### 15.2 Database
- Always backup your database
- Use migrations for schema changes
- Test migrations locally first

### 15.3 File Uploads
- Use Volumes for persistent storage
- Consider external storage for production
- Set appropriate file size limits

### 15.4 Monitoring
- Check metrics regularly
- Set up alerts for errors
- Monitor resource usage

### 15.5 Security
- Keep dependencies updated
- Use HTTPS (automatic on Railway)
- Validate all user inputs
- Use prepared statements (you're already doing this!)

---

## ✅ Final Checklist

Before going live, verify:

**Configuration:**
- [ ] Database connection works
- [ ] All tables imported successfully
- [ ] Environment variables configured
- [ ] `nixpacks.toml` created
- [ ] `db_connect.php` updated for Railway

**Deployment:**
- [ ] App deploys without errors
- [ ] Build logs show success
- [ ] No errors in deployment logs

**Functionality:**
- [ ] Homepage loads correctly
- [ ] User registration/login works
- [ ] Events display correctly
- [ ] Booking system functional
- [ ] Admin panel accessible
- [ ] File uploads work
- [ ] Chatbot responds
- [ ] All features tested

**Infrastructure:**
- [ ] Custom domain configured (if using)
- [ ] HTTPS is active (automatic on Railway)
- [ ] Volume mounted for uploads
- [ ] Monitoring set up

---

## 🎉 You're Live!

Your event booking website is now deployed on Railway!

**Your live URL:** `https://your-app-name.railway.app`

**Next Steps:**
- Monitor your app in Railway dashboard
- Set up custom domain (optional)
- Configure backups (Railway handles this automatically)
- Scale if needed (Railway auto-scales)
- Share your live URL!

---

## 📞 Need Help?

- **Railway Docs:** https://docs.railway.app/
- **Railway Discord:** https://discord.gg/railway (very helpful community!)
- **Railway Support:** Available in dashboard
- **Railway Status:** https://status.railway.app/

**Common Questions:**
- Q: How do I see my database?
  A: MySQL service → Data tab → Query tab
- Q: How do I update my app?
  A: Push to GitHub, Railway auto-deploys
- Q: How do I add more environment variables?
  A: Service → Variables tab → New Variable
- Q: How do I see logs?
  A: Service → Deployments → View Logs

---

**Good luck with your deployment! 🚀**

