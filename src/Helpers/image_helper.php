<?php
// src/Helpers/image_helper.php

if (!function_exists('normalizeImageUrl')) {
    /**
     * Chuẩn hóa đường dẫn ảnh (Phiên bản Robust)
     * Hàm này sẽ tự động sửa đường dẫn sai thành đúng
     * Ví dụ: 
     * - "abc.jpg" -> "/DACS/public/assets/img/abc.jpg"
     * - "assets/img/abc.jpg" -> "/DACS/public/assets/img/abc.jpg"
     * - "/DACS/assets/img/abc.jpg" -> "/DACS/public/assets/img/abc.jpg" (Sửa lỗi thiếu public)
     */
    function normalizeImageUrl($path) {
        // 1. CẤU HÌNH TÊN THƯ MỤC DỰ ÁN
        // Nếu bạn đổi tên thư mục chứa web, hãy sửa dòng này
        $baseFolder = '/DACS'; 

        // 2. Xử lý trường hợp không có ảnh
        if (empty($path)) {
            return $baseFolder . '/public/assets/img/no-image.jpg';
        }

        // 3. Nếu là link online (http/https) -> Giữ nguyên
        if (strpos($path, 'http') === 0) {
            return $path;
        }

        // 4. LÀM SẠCH ĐƯỜNG DẪN (CLEAN PATH)
        // Mục tiêu: Đưa mọi đường dẫn lộn xộn về dạng đơn giản nhất (chỉ còn tên file hoặc assets/...)
        
        // Bước A: Nếu đường dẫn bắt đầu bằng tên dự án (VD: /DACS/assets...), cắt bỏ nó đi
        if (strpos($path, $baseFolder) === 0) {
            $path = substr($path, strlen($baseFolder));
        }

        // Bước B: Xóa dấu gạch chéo ở đầu nếu có
        $path = ltrim($path, '/');

        // Bước C: Nếu đường dẫn bắt đầu bằng 'public/', cắt bỏ nó đi
        // (Để ta thống nhất cách thêm public vào sau)
        if (strpos($path, 'public/') === 0) {
            $path = substr($path, 7); // 7 là độ dài của chuỗi 'public/'
        }

        // Bước D: Xóa tiếp dấu gạch chéo ở đầu nếu còn dư
        $path = ltrim($path, '/');

        // 5. TÁI TẠO ĐƯỜNG DẪN CHUẨN (REBUILD)
        // Lúc này $path chỉ còn dạng "assets/img/anh.jpg" hoặc "anh.jpg"
        
        // Trường hợp 1: Đường dẫn có chứa 'assets/'
        if (strpos($path, 'assets/') === 0) {
            return $baseFolder . '/public/' . $path;
        }

        // Trường hợp 2: Chỉ là tên file (VD: naruto.png)
        // Mặc định trỏ vào thư mục img
        return $baseFolder . '/public/assets/img/' . $path;
    }
}
?>