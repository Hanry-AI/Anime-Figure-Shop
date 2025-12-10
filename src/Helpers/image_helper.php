<?php
// src/Helpers/image_helper.php

if (!function_exists('normalizeImageUrl')) {
    /**
     * Chuẩn hóa đường dẫn ảnh
     * - Nếu là link online (http...) -> Giữ nguyên
     * - Nếu là link cục bộ -> Đảm bảo có /DACS/public/...
     */
    function normalizeImageUrl($path) {
        if (empty($path)) {
            // Ảnh mặc định nếu không có ảnh
            return '/DACS/public/assets/img/no-image.jpg';
        }

        // Nếu là link online (ví dụ copy từ google)
        if (strpos($path, 'http') === 0) {
            return $path;
        }

        // Nếu đã có /DACS/ rồi thì thôi
        if (strpos($path, '/DACS/') === 0) {
            return $path;
        }

        // Nếu đường dẫn bắt đầu bằng public/
        if (strpos($path, 'public/') === 0) {
            return '/DACS/' . $path;
        }
        
        // Nếu đường dẫn bắt đầu bằng assets/
        if (strpos($path, 'assets/') === 0) {
            return '/DACS/public/' . $path;
        }

        // Mặc định thêm prefix dự án
        return '/DACS/public/assets/img/' . ltrim($path, '/');
    }
}
?>