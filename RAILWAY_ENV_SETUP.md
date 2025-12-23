# Railway Environment Variables Setup

## SendGrid API Key Configuration

To use SendGrid for email sending, you need to set the API key as an environment variable in Railway.

### Steps:

1. **Go to your Railway project dashboard**
   - Visit: https://railway.app
   - Select your project: `event-booking-website`

2. **Navigate to Variables tab**
   - Click on your service
   - Go to the **Variables** tab

3. **Add the environment variable**
   - Click **"New Variable"**
   - **Variable Name:** `SENDGRID_API_KEY`
   - **Value:** (Paste your SendGrid API key here - get it from https://app.sendgrid.com/settings/api_keys)
   - Click **"Add"**

4. **Redeploy your service**
   - Railway will automatically redeploy when you add environment variables
   - Or manually trigger a redeploy from the **Deployments** tab

### Verify it's working:

After deployment, test a booking. The email should be sent via SendGrid.

### Security Note:

✅ **Good:** API key is stored as an environment variable (not in code)
✅ **Good:** API key won't be committed to Git
✅ **Good:** Railway encrypts environment variables at rest

---

## Other Environment Variables (if needed)

If you need to override other email settings, you can also set:
- `FROM_EMAIL` - Override the from email address
- `FROM_NAME` - Override the from name

But these are optional - the config file values will be used if not set.

