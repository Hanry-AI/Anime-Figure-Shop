<?php
namespace DACS\Helpers;

class ImageHelper {
    public static function normalizeUrl($url) {
        // Nếu không có tên ảnh -> Trả về ảnh mặc định
        if (empty($url)) return 'assets/img/no-image.png';
        
        // Nếu là ảnh từ link ngoài (http/https) -> Giữ nguyên
        if (strpos($url, 'http') === 0) return $url;
        
        // Xóa dấu gạch chéo ở đầu nếu có (để tránh lỗi đường dẫn)
        $url = ltrim($url, '/');

        // Nếu trong DB đã lưu sẵn 'assets/img/...' thì giữ nguyên
        if (strpos($url, 'assets/img/') === 0) {
            return $url;
        }

        // Trường hợp chỉ có tên file (VD: luffy.jpg) -> Thêm 'assets/img/' vào trước
        return 'assets/img/' . $url;
    }
}
?>