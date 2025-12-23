# Image Accessibility Fix Summary

## Problem
All images (event photos, venue photos, subcategory photos, profile pictures) were not appearing on mobile phones or laptops that don't have the project locally because:
1. Images were being saved to `uploads/` directory (not web-accessible)
2. Images needed to be in `public/uploads/` to be accessible via web
3. Image URLs weren't being normalized properly for web access

## Solutions Implemented

### 1. Fixed Upload Paths
All image uploads now save to `public/uploads/` instead of just `uploads/`:
- **Profile Pictures**: `public/uploads/profile_pics/`
- **Event Images**: `public/uploads/events/`
- **Event Gallery**: `public/uploads/events/gallery/`
- **Venue Images**: `public/uploads/venues/`
- **Subcategory Images**: `public/uploads/subcategories/`

### 2. Updated Files
- `public/api/users.php` - Profile picture uploads
- `app/views/profile.php` - Profile picture uploads
- `app/views/auth.php` - Registration profile picture uploads
- `app/models/Venue.php` - Venue image uploads
- `public/api/uploads.php` - Event/subcategory image uploads
- `public/image.php` - Image proxy/serving endpoint

### 3. Fixed Image URL Normalization
Updated `imageUrl()` helper function in `app/views/path_helper.php` to:
- Convert `uploads/...` paths to `/uploads/...` (absolute from web root)
- Work correctly on both Railway (where public is web root) and local development
- Handle both absolute URLs and relative paths

### 4. Updated All Image Displays
All views now use `imageUrl()` helper to ensure images are accessible:
- `app/views/homepage.php` - Event and subcategory images
- `app/views/allevents.php` - Event images
- `app/views/sports.php` - Sports event images
- `app/views/entertainment.php` - Entertainment event images
- `app/views/booking.php` - Event banner and gallery images
- `app/controllers/EventController.php` - Normalizes all event/subcategory images in API responses

### 5. Database Migration
Created migration to ensure `profile_image_path` column exists:
- `database/migrations/add_profile_image_path.sql`
- `public/run_migration.php` - Run this once to add the column

## How It Works Now

1. **Image Upload**: Images are saved to `public/uploads/[type]/filename.jpg`
2. **Database Storage**: Database stores relative path: `uploads/[type]/filename.jpg`
3. **Image Display**: `imageUrl()` helper converts to: `/uploads/[type]/filename.jpg`
4. **Web Access**: Images are accessible at: `https://your-domain.com/uploads/[type]/filename.jpg`

## Testing

After Railway redeploys:
1. ✅ Upload a profile picture - should save to `public/uploads/profile_pics/`
2. ✅ Upload an event image - should save to `public/uploads/events/`
3. ✅ All images should be visible on any device (mobile, laptop, etc.)
4. ✅ Images should work on Railway deployment

## Run Migration

Visit once: `https://your-railway-url/run_migration.php`
Or locally: `http://localhost/event-booking-website/public/run_migration.php`

This ensures the `profile_image_path` column exists in the users table.

