<?php
// File: public/index.php
session_start();

define('PROJECT_ROOT', dirname(__DIR__));

require_once PROJECT_ROOT . '/src/Config/db.php';
require_once PROJECT_ROOT . '/src/Helpers/image_helper.php';
require_once PROJECT_ROOT . '/src/Controllers/AuthController.php';

use DACS\Controllers\AuthController;

$page   = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'index';

switch ($page) {
    case 'auth':
    case 'login':
    case 'register':
        $controller = new AuthController();
        if ($action === 'logout') {
            $controller->logout();
        } else {
            $controller->index();
        }
        break;

    case 'home':
    default:
        // ĐÃ SỬA: Gọi vào file views/pages/index.php theo ý bạn
        require_once PROJECT_ROOT . '/views/pages/index.php'; 
        break;
}
?>