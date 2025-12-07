<?php
namespace DACS\Controllers;

class HomeController {
    public function index() {
        // Dùng PROJECT_ROOT đã định nghĩa bên index.php hoặc đường dẫn tương đối
        // Cách dùng __DIR__ an toàn hơn
        require_once __DIR__ . '/../../views/pages/index.php';
    }
}