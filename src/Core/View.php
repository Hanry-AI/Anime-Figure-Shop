<?php
namespace DACS\Core;

class View {
    /**
     * Hàm render giao diện
     * @param string $path Đường dẫn view (VD: 'pages/home')
     * @param array $data Mảng dữ liệu muốn truyền sang view (VD: ['products' => $list])
     */
    public static function render($path, $data = []) {
        // 1. Giải nén mảng $data thành các biến riêng biệt
        // VD: ['name' => 'Gundam'] sẽ thành biến $name = 'Gundam'
        extract($data);

        // 2. Tạo đường dẫn tuyệt đối đến file view
        // PROJECT_ROOT đã được define ở index.php
        $fullPath = PROJECT_ROOT . '/views/' . $path . '.php';

        // 3. Kiểm tra và nhúng file
        if (file_exists($fullPath)) {
            require $fullPath;
        } else {
            echo "Error: View file not found at " . $fullPath;
        }
    }
}
?>