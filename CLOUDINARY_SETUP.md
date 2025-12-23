# Cloudinary Setup Guide

## Why Cloudinary?

Railway's filesystem is ephemeral - all uploaded files are lost on redeploy. Cloudinary provides:
- ✅ Persistent cloud storage (files never lost)
- ✅ Free tier: 25GB storage, 25GB bandwidth/month
- ✅ Automatic image optimization
- ✅ CDN included (fast image delivery)
- ✅ No server management needed

## Step 1: Create Cloudinary Account

1. Go to https://cloudinary.com/users/register/free
2. Sign up for free account
3. Verify your email

## Step 2: Get Your Credentials

1. After logging in, go to Dashboard
2. You'll see your credentials:
   - **Cloud Name** (e.g., `dxyz123`)
   - **API Key** (e.g., `123456789012345`)
   - **API Secret** (e.g., `abcdefghijklmnopqrstuvwxyz`)

## Step 3: Add to Railway Environment Variables

1. Go to your Railway project dashboard
2. Click on your service
3. Go to **"Variables"** tab
4. Click **"New Variable"**
5. Add these three variables:

   ```
   CLOUDINARY_CLOUD_NAME = your_cloud_name
   CLOUDINARY_API_KEY = your_api_key
   CLOUDINARY_API_SECRET = your_api_secret
   ```

6. Click **"Add"** for each variable
7. Railway will automatically redeploy

## Step 4: Verify It Works

1. After Railway redeploys, try uploading:
   - A profile picture
   - A subcategory image
   - An event image
2. Check that images appear and persist after redeploy

## What Happens Now?

- **Before**: Images saved to `public/uploads/` → Lost on redeploy ❌
- **After**: Images saved to Cloudinary → Persist forever ✅

## Fallback Behavior

If Cloudinary credentials are not set, the system will:
- Fall back to local storage (for local development)
- Log a warning that Cloudinary is not configured

## Need Help?

If you have issues:
1. Check Railway logs for Cloudinary errors
2. Verify environment variables are set correctly
3. Make sure Cloudinary account is active

