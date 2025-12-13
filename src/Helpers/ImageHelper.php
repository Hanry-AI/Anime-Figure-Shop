<?php
namespace DACS\Helpers;

class ImageHelper {
    public static function normalizeUrl($url) {
        // [CẤU HÌNH] Đường dẫn gốc đến thư mục ảnh
        // Dấu / ở đầu đại diện cho thư mục gốc của localhost (http://localhost/)
        // Thay đổi '/DACS/' nếu bạn đổi tên thư mục dự án
        $basePath = '/DACS/public/assets/img/';

        // 1. Nếu không có tên ảnh -> Trả về ảnh mặc định
        if (empty($url)) {
            return $basePath . 'no-image.jpg'; 
        }
        
        // 2. Nếu là ảnh lấy từ link ngoài (http/https) -> Giữ nguyên
        if (strpos($url, 'http') === 0) {
            return $url;
        }
        
        // 3. Lấy tên file gốc (basename) để loại bỏ mọi đường dẫn thừa cũ
        // Ví dụ: "DACS/public/assets/img/anh.jpg" -> Chỉ lấy "anh.jpg"
        $filename = basename($url);

        // 4. Ghép với đường dẫn tuyệt đối
        // Kết quả sẽ luôn là: /DACS/public/assets/img/ten-anh.jpg
        return $basePath . $filename;
    }
}