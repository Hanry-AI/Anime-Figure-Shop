<?php
namespace DACS\Core;

class Request {
    public function get($key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    public function post($key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    public function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    // Hàm lấy tất cả dữ liệu (nếu cần)
    public function all() {
        return $_REQUEST;
    }
}
?>