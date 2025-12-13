<?php
namespace DACS\Core;

// Import các class cần thiết
use DACS\Config\Database;
use DACS\Core\DbSessionHandler;

class Session {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            
            // 1. Kết nối Database
            $db = new Database();
            $conn = $db->getConnection();

            // 2. Kích hoạt chế độ lưu vào Database
            $handler = new DbSessionHandler($conn);
            session_set_save_handler($handler, true);

            // 3. Bây giờ mới start session
            session_start();
        }
    }

    // Các hàm get/set bên dưới giữ nguyên không đổi
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function remove($key) {
        if (isset($_SESSION[$key])) unset($_SESSION[$key]);
    }

    public static function destroy() {
        session_destroy();
    }
}
?>