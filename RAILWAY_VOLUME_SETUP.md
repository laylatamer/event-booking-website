# Railway Persistent Volume Setup for Uploads

## Problem
When Railway redeploys, the container filesystem is rebuilt from Git, which means all uploaded files (profile pictures, event images, subcategory images, etc.) are lost. The database still has the file paths, but the actual files are gone.

## Solution: Use Railway Persistent Volumes

Railway supports persistent volumes that survive redeployments. Follow these steps:

### Step 1: Create a Volume in Railway

1. Go to your Railway project dashboard
2. Click on your service
3. Go to the **"Volumes"** tab (or **"Settings"** → **"Volumes"**)
4. Click **"New Volume"**
5. Name it: `uploads`
6. Set the mount path: `/app/public/uploads`
7. Click **"Create"**

### Step 2: Verify Volume is Mounted

After creating the volume, Railway will automatically mount it to `/app/public/uploads`. All files uploaded to this directory will persist across redeployments.

### Step 3: Test

1. Upload a profile picture or subcategory image
2. Trigger a redeploy (push a commit or manually redeploy)
3. The images should still be there after redeployment

## Alternative: Cloud Storage (Recommended for Production)

For production, consider using cloud storage services:
- **AWS S3** (with CloudFront CDN)
- **Cloudinary** (image optimization included)
- **DigitalOcean Spaces**
- **Google Cloud Storage**

This is more scalable and reliable than filesystem storage.

## Current Status

The application is already configured to save files to `public/uploads/`:
- Profile pictures: `public/uploads/profile_pics/`
- Event images: `public/uploads/events/`
- Subcategory images: `public/uploads/subcategories/`
- Venue images: `public/uploads/venues/`

Once you mount the Railway volume to `/app/public/uploads`, all uploads will persist.

