<?php
/**
 * Helper functions for image path normalization
 */

/**
 * Normalize image URL from database
 * Converts old upload paths to new img path
 * 
 * @param string|null $imageUrl The image URL from database
 * @return string Normalized image URL
 */
function normalizeImageUrl(?string $imageUrl): string {
    if (empty($imageUrl)) {
        return '/DACS/public/assets/img/placeholder.jpg';
    }
    
    // If already starts with /DACS/public/assets/img/, return as is
    if (strpos($imageUrl, '/DACS/public/assets/img/') === 0) {
        return $imageUrl;
    }
    
    // Convert old uploads path to new img path
    if (strpos($imageUrl, '/DACS/public/uploads/') === 0) {
        return str_replace('/DACS/public/uploads/', '/DACS/public/assets/img/', $imageUrl);
    }
    
    // If path starts with /uploads/, convert to /DACS/public/assets/img/
    if (strpos($imageUrl, '/uploads/') === 0) {
        return str_replace('/uploads/', '/DACS/public/assets/img/', $imageUrl);
    }
    
    // If path starts with /public/uploads/, convert to /DACS/public/assets/img/
    if (strpos($imageUrl, '/public/uploads/') === 0) {
        return str_replace('/public/uploads/', '/DACS/public/assets/img/', $imageUrl);
    }
    
    // If path doesn't start with /, assume it's a filename and prepend the base path
    if (strpos($imageUrl, '/') !== 0) {
        return '/DACS/public/assets/img/' . ltrim($imageUrl, '/');
    }
    
    // If path starts with /DACS/assets/img/ (missing /public/), fix it
    if (strpos($imageUrl, '/DACS/assets/img/') === 0) {
        return str_replace('/DACS/assets/img/', '/DACS/public/assets/img/', $imageUrl);
    }
    
    // If path starts with /assets/img/, prepend /DACS/public
    if (strpos($imageUrl, '/assets/img/') === 0) {
        return '/DACS/public' . $imageUrl;
    }
    
    // If path starts with /public/assets/img/, prepend /DACS
    if (strpos($imageUrl, '/public/assets/img/') === 0) {
        return '/DACS' . $imageUrl;
    }
    
    // Return as is if no conversion needed
    return $imageUrl;
}

