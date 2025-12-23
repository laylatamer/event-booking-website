# Railway Persistent Volume Setup for Uploads

## Problem
When Railway redeploys, the container filesystem is rebuilt from Git, which means all uploaded files (profile pictures, event images, subcategory images, etc.) are lost. The database still has the file paths, but the actual files are gone.

## Solution: Use Railway Persistent Volumes

Railway supports persistent volumes that survive redeployments. Follow these steps:

### Step 1: Attach a Volume in Railway

**Important**: Volumes are attached via right-click context menu, not in Settings!

1. Go to your Railway project dashboard
2. **Right-click on your service card** (the box representing your web application service)
3. Select **"Attach Volume"** from the context menu
4. In the volume configuration:
   - **Mount Path**: `/app/public/uploads`
   - **Name**: `uploads` (or any name you prefer)
5. Click **"Add"** or **"Create"**

**Note**: 
- Free plan: 1 volume, 0.5GB limit
- Hobby plan: Up to 10 volumes, 5GB each
- If you don't see "Attach Volume" option, your plan may not support volumes

### Step 2: Verify Volume is Mounted

After creating the volume, Railway will automatically mount it to `/app/public/uploads`. All files uploaded to this directory will persist across redeployments.

### Step 3: Test

1. Upload a profile picture or subcategory image
2. Trigger a redeploy (push a commit or manually redeploy)
3. The images should still be there after redeployment

## Alternative: Cloud Storage (Recommended if Volumes Not Available)

If you can't find the volume option or it's not available on your plan, use cloud storage instead:

### Option 1: Cloudinary (Easiest - Free tier available)
- **Free tier**: 25GB storage, 25GB bandwidth/month
- **Pros**: Image optimization, CDN, easy integration
- **Sign up**: https://cloudinary.com

### Option 2: AWS S3 (Most scalable)
- **Free tier**: 5GB storage, 20,000 GET requests/month
- **Pros**: Industry standard, highly scalable

### Option 3: DigitalOcean Spaces
- **Pricing**: $5/month for 250GB
- **Pros**: Simple S3-compatible API

**I can help you integrate Cloudinary if volumes aren't available!** It's the easiest solution and includes image optimization.

## Current Status

The application is already configured to save files to `public/uploads/`:
- Profile pictures: `public/uploads/profile_pics/`
- Event images: `public/uploads/events/`
- Subcategory images: `public/uploads/subcategories/`
- Venue images: `public/uploads/venues/`

Once you mount the Railway volume to `/app/public/uploads`, all uploads will persist.

