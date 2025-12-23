<?php
/**
 * Cloudinary Service for Image Uploads
 * Handles all image uploads to Cloudinary cloud storage
 * Falls back to local storage if Cloudinary is not configured
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class CloudinaryService {
    private $cloudinary;
    private $enabled;
    
    public function __construct() {
        // Check if Cloudinary is configured
        $cloudName = getenv('CLOUDINARY_CLOUD_NAME') ?: '';
        $apiKey = getenv('CLOUDINARY_API_KEY') ?: '';
        $apiSecret = getenv('CLOUDINARY_API_SECRET') ?: '';
        
        $this->enabled = !empty($cloudName) && !empty($apiKey) && !empty($apiSecret);
        
        if ($this->enabled) {
            try {
                Configuration::instance([
                    'cloud' => [
                        'cloud_name' => $cloudName,
                        'api_key' => $apiKey,
                        'api_secret' => $apiSecret
                    ],
                    'url' => [
                        'secure' => true
                    ]
                ]);
                $this->cloudinary = new Cloudinary();
            } catch (Exception $e) {
                error_log("Cloudinary initialization failed: " . $e->getMessage());
                $this->enabled = false;
            }
        }
    }
    
    /**
     * Upload an image file to Cloudinary
     * @param array $file $_FILES array element
     * @param string $folder Cloudinary folder (e.g., 'profile_pics', 'events', 'subcategories')
     * @param string $publicId Optional public ID (if not provided, generates unique ID)
     * @return array ['success' => bool, 'url' => string, 'public_id' => string] or ['success' => false, 'message' => string]
     */
    public function uploadImage($file, $folder = 'uploads', $publicId = null) {
        if (!$this->enabled) {
            return ['success' => false, 'message' => 'Cloudinary not configured'];
        }
        
        try {
            // Validate file
            if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                return ['success' => false, 'message' => 'Invalid file upload'];
            }
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime, $allowedTypes)) {
                return ['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP'];
            }
            
            // Check file size (10MB max)
            if ($file['size'] > 10 * 1024 * 1024) {
                return ['success' => false, 'message' => 'File too large. Maximum size: 10MB'];
            }
            
            // Generate public ID if not provided
            if (!$publicId) {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $publicId = $folder . '/' . uniqid() . '_' . time();
            } else {
                $publicId = $folder . '/' . $publicId;
            }
            
            // Upload to Cloudinary
            $uploadApi = new UploadApi();
            $result = $uploadApi->upload($file['tmp_name'], [
                'public_id' => $publicId,
                'folder' => $folder,
                'resource_type' => 'image',
                'overwrite' => true,
                'invalidate' => true
            ]);
            
            // Return Cloudinary URL
            return [
                'success' => true,
                'url' => $result['secure_url'], // Full Cloudinary URL
                'public_id' => $result['public_id'],
                'cloudinary_url' => $result['secure_url']
            ];
            
        } catch (Exception $e) {
            error_log("Cloudinary upload error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Delete an image from Cloudinary
     * @param string $publicId Cloudinary public ID
     * @return bool Success status
     */
    public function deleteImage($publicId) {
        if (!$this->enabled) {
            return false;
        }
        
        try {
            $uploadApi = new UploadApi();
            $uploadApi->destroy($publicId);
            return true;
        } catch (Exception $e) {
            error_log("Cloudinary delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if Cloudinary is enabled
     * @return bool
     */
    public function isEnabled() {
        return $this->enabled;
    }
    
    /**
     * Get Cloudinary URL from public ID
     * @param string $publicId
     * @return string
     */
    public function getUrl($publicId) {
        if (!$this->enabled) {
            return '';
        }
        
        try {
            return $this->cloudinary->image($publicId)->secure()->toUrl();
        } catch (Exception $e) {
            error_log("Cloudinary URL generation error: " . $e->getMessage());
            return '';
        }
    }
}

