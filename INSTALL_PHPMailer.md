# PHPMailer Installation Instructions

To enable email sending with SMTP, you need to install PHPMailer. Here are two methods:

## Method 1: Using Composer (Recommended)

1. **Install Composer** (if not already installed):
   - Download from: https://getcomposer.org/download/
   - Or use the Windows installer: https://getcomposer.org/Composer-Setup.exe

2. **Open PowerShell or Command Prompt** in your project directory:
   ```bash
   cd C:\xampp\htdocs\event-booking-website
   ```

3. **Install PHPMailer**:
   ```bash
   composer require phpmailer/phpmailer
   ```

4. PHPMailer will be installed in the `vendor` directory automatically.

5. **Configure email settings** in `config/email_config.php`:
   - Update `smtp_host`, `smtp_port`, `smtp_username`, and `smtp_password`
   - For Gmail, you'll need to use an App Password (see Gmail Setup below)

## Method 2: Manual Installation (Without Composer)

1. **Download PHPMailer**:
   - Go to: https://github.com/PHPMailer/PHPMailer/releases
   - Download the latest release ZIP file

2. **Extract and copy files**:
   - Extract the ZIP file
   - Create directory: `vendor/phpmailer/phpmailer/src/`
   - Copy all PHP files from the extracted `src/` folder to `vendor/phpmailer/phpmailer/src/`

3. **Your structure should look like**:
   ```
   event-booking-website/
   ├── vendor/
   │   └── phpmailer/
   │       └── phpmailer/
   │           └── src/
   │               ├── PHPMailer.php
   │               ├── SMTP.php
   │               ├── Exception.php
   │               └── ... (other files)
   ```

4. **Configure email settings** in `config/email_config.php`

## Gmail Setup (If using Gmail SMTP)

1. **Enable 2-Step Verification** on your Google account:
   - Go to: https://myaccount.google.com/security
   - Enable 2-Step Verification

2. **Generate App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and your device
   - Copy the generated 16-character password

3. **Update `config/email_config.php`**:
   ```php
   'smtp_host' => 'smtp.gmail.com',
   'smtp_port' => 587,
   'smtp_secure' => 'tls',
   'smtp_auth' => true,
   'smtp_username' => 'your-email@gmail.com',
   'smtp_password' => 'your-16-character-app-password',  // Use App Password, not regular password
   'from_email' => 'your-email@gmail.com',
   ```

## Testing Email Configuration

After installation, you can test the email functionality:

1. **Using the test page**:
   - Open: `http://localhost/event-booking-website/public/api/test_email.php?email=your-email@example.com`

2. **Or test during checkout**:
   - Complete a booking with card payment
   - Check your email inbox
   - Check PHP error logs if emails aren't received

## Troubleshooting

### If PHPMailer is not found:
- Verify the files are in the correct location
- Check file permissions
- Review error logs for specific path issues

### If emails still don't send:
- Verify SMTP credentials are correct
- Check firewall/antivirus isn't blocking port 587/465
- For Gmail: Make sure you're using an App Password, not your regular password
- Check PHP error logs for detailed error messages

### Common SMTP Settings:

**Gmail:**
- Host: smtp.gmail.com
- Port: 587 (TLS) or 465 (SSL)
- Security: tls or ssl

**Outlook/Hotmail:**
- Host: smtp-mail.outlook.com
- Port: 587
- Security: tls

**Yahoo:**
- Host: smtp.mail.yahoo.com
- Port: 587
- Security: tls

**Mailtrap (Testing):**
- Host: smtp.mailtrap.io
- Port: 2525
- Security: tls
- Username/Password: Get from Mailtrap dashboard
