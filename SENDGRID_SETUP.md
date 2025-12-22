# SendGrid Email Setup Guide

## Why SendGrid?

Railway blocks outbound SMTP connections (ports 587, 465, 25) to prevent spam. SendGrid uses HTTP API calls instead, which Railway allows.

## Setup Steps

### 1. Create a SendGrid Account

1. Go to https://signup.sendgrid.com/
2. Sign up for a free account (100 emails/day)
3. Verify your email address

### 2. Create an API Key

1. Log in to SendGrid dashboard: https://app.sendgrid.com/
2. Go to **Settings** → **API Keys**
3. Click **Create API Key**
4. Name it: "Event Booking Website"
5. Select **Full Access** (or at least "Mail Send" permissions)
6. Click **Create & View**
7. **COPY THE API KEY** - you won't be able to see it again!

### 3. Verify Your Sender Email (Important!)

1. Go to **Settings** → **Sender Authentication**
2. Click **Verify a Single Sender**
3. Fill in the form:
   - **From Email**: `noreply@egzly.com` (or your domain email)
   - **From Name**: `EحGZLY`
   - **Reply To**: `support@egzly.com`
   - Complete all required fields
4. Check your email and click the verification link
5. **Wait for verification** (can take a few minutes)

### 4. Configure Your Application

1. Open `config/email_config.php`
2. Set `'email_provider' => 'sendgrid'`
3. Paste your API key: `'sendgrid_api_key' => 'SG.xxxxxxxxxxxxx'`
4. Make sure `'from_email'` matches the verified sender email

### Example Configuration:

```php
return [
    'email_provider' => 'sendgrid',  // Use SendGrid API
    'sendgrid_api_key' => 'SG.your_api_key_here',  // Your SendGrid API key
    
    'from_email' => 'noreply@egzly.com',  // Must match verified sender
    'from_name' => 'EحGZLY',
    'reply_to_email' => 'support@egzly.com',
    'reply_to_name' => 'EحGZLY Support',
    
    // ... rest of config
];
```

### 5. Test It!

After Railway rebuilds, try making a booking. Check the logs to see:
- `EmailService: Using SendGrid API for email sending`
- `Email sent successfully using SendGrid to: ...`

## Troubleshooting

### "SendGrid API key not configured"
- Make sure `sendgrid_api_key` is set in `config/email_config.php`
- Check for typos in the API key

### "HTTP 403" or "HTTP 401"
- API key is invalid or expired
- Regenerate the API key in SendGrid dashboard

### "HTTP 400" - "The from address does not match a verified Sender Identity"
- Your `from_email` doesn't match the verified sender in SendGrid
- Go to SendGrid → Settings → Sender Authentication
- Verify the email address you're using

### Emails not arriving
- Check SendGrid dashboard → Activity Feed
- Check spam folder
- Make sure sender email is verified

## Benefits

✅ **No SMTP blocking** - Uses HTTP API  
✅ **Fast** - Usually sends in < 1 second  
✅ **Reliable** - SendGrid handles delivery  
✅ **Free tier** - 100 emails/day  
✅ **QR codes work** - Supports inline attachments  

## Alternative: Mailgun

If you prefer Mailgun (5,000 emails/month free):
1. Sign up at https://www.mailgun.com/
2. Get API key from dashboard
3. Similar setup process

