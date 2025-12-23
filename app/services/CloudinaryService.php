<?php
/**
 * Cloudinary Service for Image Uploads
 * Handles all image uploads to Cloudinary cloud storage
 * Falls back to local storage if Cloudinary is not configured
 */

// Try to load Cloudinary SDK (may not be installed)
$cloudinaryLoaded = false;
try {
    $vendorPath = __DIR__ . '/../../vendor/autoload.php';
    if (file_exists($vendorPath)) {
        require_once $vendorPath;
        // Check if Cloudinary classes exist
        if (class_exists('Cloudinary\Cloudinary')) {
            $cloudinaryLoaded = true;
        }
    }
} catch (Throwable $e) {
    // Cloudinary SDK not available
    $cloudinaryLoaded = false;
}

class CloudinaryService {
    private $cloudinary;
    private $enabled;
    
    public function __construct() {
        // Check if Cloudinary SDK is loaded
        if (!class_exists('Cloudinary\Cloudinary')) {
            $this->enabled = false;
            error_log("CloudinaryService: Cloudinary SDK class not found");
            return;
        }
        
        // Check if Cloudinary is configured - trim whitespace from env vars
        $cloudName = trim(getenv('CLOUDINARY_CLOUD_NAME') ?: '');
        $apiKey = trim(getenv('CLOUDINARY_API_KEY') ?: '');
        $apiSecret = trim(getenv('CLOUDINARY_API_SECRET') ?: '');
        
        // Check if all required values are present
        if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
            $this->enabled = false;
            error_log("CloudinaryService: Missing environment variables. CloudName: " . (!empty($cloudName) ? 'set' : 'empty') . ", APIKey: " . (!empty($apiKey) ? 'set' : 'empty') . ", APISecret: " . (!empty($apiSecret) ? 'set' : 'empty'));
            return;
        }
        
        // Try to initialize Cloudinary
        try {
            // Pass configuration directly to constructor (Cloudinary PHP SDK v3)
            $config = [
                'cloud' => [
                    'cloud_name' => $cloudName,
                    'api_key' => $apiKey,
                    'api_secret' => $apiSecret
                ],
                'url' => [
                    'secure' => true
                ]
            ];
            
            // Create Cloudinary instance with configuration
            $this->cloudinary = new \Cloudinary\Cloudinary($config);
            
            // Verify configuration was set correctly
            $actualCloudName = $this->cloudinary->configuration->cloud->cloudName;
            
            if ($actualCloudName === $cloudName) {
                $this->enabled = true;
                error_log("CloudinaryService: Successfully initialized with cloud name: " . $cloudName);
            } else {
                $this->enabled = false;
                error_log("CloudinaryService: Configuration verification failed. Expected: " . $cloudName . ", Got: " . $actualCloudName);
            }
        } catch (Throwable $e) {
            error_log("Cloudinary initialization failed: " . $e->getMessage());
            error_log("Cloudinary initialization stack trace: " . $e->getTraceAsString());
            $this->enabled = false;
        }
    }
    
    /**
     * Check if Cloudinary service is enabled and ready to use
     * @return bool
     */
    public function isEnabled() {
        return $this->enabled === true && $this->cloudinary !== null;
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
        
        // Check if Cloudinary classes are available
        if (!class_exists('Cloudinary\Api\Upload\UploadApi')) {
            return ['success' => false, 'message' => 'Cloudinary SDK not installed'];
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
            $uploadApi = new \Cloudinary\Api\Upload\UploadApi();
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
            error_log("Cloudinary upload stack trace: " . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        } catch (Throwable $e) {
            error_log("Cloudinary upload fatal error: " . $e->getMessage());
            error_log("Cloudinary upload stack trace: " . $e->getTraceAsString());
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
        
        if (!class_exists('Cloudinary\Api\Upload\UploadApi')) {
            return false;
        }
        
        try {
            $uploadApi = new \Cloudinary\Api\Upload\UploadApi();
            $uploadApi->destroy($publicId);
            return true;
        } catch (Exception $e) {
            error_log("Cloudinary delete error: " . $e->getMessage());
            return false;
        }
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

