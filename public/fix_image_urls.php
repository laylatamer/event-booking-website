<?php
/**
 * Fix image URLs in database - convert full URLs to relative paths
 * Run this once to fix existing image URLs in the database
 */

require_once __DIR__ . '/../config/db_connect.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<h1>Fix Image URLs in Database</h1>";
echo "<p>Converting full URLs (http://localhost/...) to relative paths (uploads/...)</p>";

try {
    $pdo = $database->getConnection();
    
    // Fix events table
    echo "<h2>Fixing Events Table</h2>";
    $stmt = $pdo->query("SELECT id, image_url, gallery_images FROM events WHERE image_url LIKE 'http%' OR gallery_images LIKE '%http%'");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $eventsFixed = 0;
    foreach ($events as $event) {
        $id = $event['id'];
        $updates = [];
        
        // Fix image_url
        if (!empty($event['image_url']) && preg_match('/^https?:\/\//', $event['image_url'])) {
            // Extract relative path from URL
            $url = $event['image_url'];
            // Remove protocol and domain
            $url = preg_replace('/^https?:\/\/[^\/]+/', '', $url);
            // Remove /event-booking-website/public/ or /public/ prefix
            $url = preg_replace('#^/event-booking-website/public/#', '', $url);
            $url = preg_replace('#^/public/#', '', $url);
            // Remove leading slash
            $url = ltrim($url, '/');
            // Ensure it starts with uploads/
            if (strpos($url, 'uploads/') !== 0) {
                // Try to extract uploads/ part
                if (preg_match('#uploads/[^/]+/.+#', $url, $matches)) {
                    $url = $matches[0];
                } else {
                    // If we can't find uploads/, skip this one
                    echo "<p style='color: orange;'>⚠ Skipping event $id: Could not extract relative path from: {$event['image_url']}</p>";
                    continue;
                }
            }
            $updates['image_url'] = $url;
        }
        
        // Fix gallery_images
        if (!empty($event['gallery_images']) && strpos($event['gallery_images'], 'http') !== false) {
            $gallery = json_decode($event['gallery_images'], true);
            if (is_array($gallery)) {
                $fixedGallery = [];
                foreach ($gallery as $img) {
                    if (preg_match('/^https?:\/\//', $img)) {
                        // Extract relative path
                        $imgUrl = preg_replace('/^https?:\/\/[^\/]+/', '', $img);
                        $imgUrl = preg_replace('#^/event-booking-website/public/#', '', $imgUrl);
                        $imgUrl = preg_replace('#^/public/#', '', $imgUrl);
                        $imgUrl = ltrim($imgUrl, '/');
                        if (strpos($imgUrl, 'uploads/') !== 0) {
                            if (preg_match('#uploads/[^/]+/.+#', $imgUrl, $matches)) {
                                $imgUrl = $matches[0];
                            } else {
                                continue; // Skip this image
                            }
                        }
                        $fixedGallery[] = $imgUrl;
                    } else {
                        $fixedGallery[] = $img;
                    }
                }
                $updates['gallery_images'] = json_encode($fixedGallery);
            }
        }
        
        // Update database if we have changes
        if (!empty($updates)) {
            $setParts = [];
            $params = [':id' => $id];
            
            if (isset($updates['image_url'])) {
                $setParts[] = 'image_url = :image_url';
                $params[':image_url'] = $updates['image_url'];
            }
            
            if (isset($updates['gallery_images'])) {
                $setParts[] = 'gallery_images = :gallery_images';
                $params[':gallery_images'] = $updates['gallery_images'];
            }
            
            $updateSql = "UPDATE events SET " . implode(', ', $setParts) . " WHERE id = :id";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute($params);
            
            $eventsFixed++;
            echo "<p style='color: green;'>✓ Fixed event $id</p>";
        }
    }
    
    echo "<p><strong>Events fixed: $eventsFixed</strong></p>";
    
    // Fix subcategories table
    echo "<h2>Fixing Subcategories Table</h2>";
    $stmt = $pdo->query("SELECT id, image_url FROM subcategories WHERE image_url LIKE 'http%'");
    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $subcategoriesFixed = 0;
    foreach ($subcategories as $subcat) {
        $id = $subcat['id'];
        $url = $subcat['image_url'];
        
        // Extract relative path
        $url = preg_replace('/^https?:\/\/[^\/]+/', '', $url);
        $url = preg_replace('#^/event-booking-website/public/#', '', $url);
        $url = preg_replace('#^/public/#', '', $url);
        $url = ltrim($url, '/');
        
        if (strpos($url, 'uploads/') !== 0) {
            if (preg_match('#uploads/[^/]+/.+#', $url, $matches)) {
                $url = $matches[0];
            } else {
                echo "<p style='color: orange;'>⚠ Skipping subcategory $id: Could not extract relative path</p>";
                continue;
            }
        }
        
        $updateStmt = $pdo->prepare("UPDATE subcategories SET image_url = :image_url WHERE id = :id");
        $updateStmt->execute([':image_url' => $url, ':id' => $id]);
        
        $subcategoriesFixed++;
        echo "<p style='color: green;'>✓ Fixed subcategory $id</p>";
    }
    
    echo "<p><strong>Subcategories fixed: $subcategoriesFixed</strong></p>";
    
    // Fix venues table
    echo "<h2>Fixing Venues Table</h2>";
    $stmt = $pdo->query("SELECT id, image_url FROM venues WHERE image_url LIKE 'http%'");
    $venues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $venuesFixed = 0;
    foreach ($venues as $venue) {
        $id = $venue['id'];
        $url = $venue['image_url'];
        
        // Extract relative path
        $url = preg_replace('/^https?:\/\/[^\/]+/', '', $url);
        $url = preg_replace('#^/event-booking-website/public/#', '', $url);
        $url = preg_replace('#^/public/#', '', $url);
        $url = ltrim($url, '/');
        
        if (strpos($url, 'uploads/') !== 0) {
            if (preg_match('#uploads/[^/]+/.+#', $url, $matches)) {
                $url = $matches[0];
            } else {
                echo "<p style='color: orange;'>⚠ Skipping venue $id: Could not extract relative path</p>";
                continue;
            }
        }
        
        $updateStmt = $pdo->prepare("UPDATE venues SET image_url = :image_url WHERE id = :id");
        $updateStmt->execute([':image_url' => $url, ':id' => $id]);
        
        $venuesFixed++;
        echo "<p style='color: green;'>✓ Fixed venue $id</p>";
    }
    
    echo "<p><strong>Venues fixed: $venuesFixed</strong></p>";
    
    echo "<h2 style='color: green;'>✅ All done! Total fixed:</h2>";
    echo "<ul>";
    echo "<li>Events: $eventsFixed</li>";
    echo "<li>Subcategories: $subcategoriesFixed</li>";
    echo "<li>Venues: $venuesFixed</li>";
    echo "</ul>";
    echo "<p><a href='../app/views/admin/index.php'>Go to Admin Panel</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error!</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

