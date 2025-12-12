<?php
namespace DACS\Core;

// Import các Controller cần dùng
use DACS\Controllers\AuthController;
use DACS\Controllers\ProductController;
use DACS\Controllers\HomeController;
use DACS\Controllers\PageController;
use DACS\Controllers\CartController;

class Router {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function run() {
        $page   = $_GET['page'] ?? 'home';
        $action = $_GET['action'] ?? 'index';

        switch ($page) {
            // --- AUTHENTICATION ---
            case 'auth':
            case 'login':
            case 'register':
                $controller = new AuthController($this->conn);
                if ($action === 'logout') {
                    $controller->logout();
                } else {
                    $controller->index();
                }
                break;

            // --- SẢN PHẨM ---
            case 'anime':
                (new ProductController($this->conn))->indexAnime();
                break;
                
            case 'gundam':
                (new ProductController($this->conn))->indexGundam();
                break;
                
            case 'marvel':
                (new ProductController($this->conn))->indexMarvel();
                break;
    
            case 'product':
                (new ProductController($this->conn))->detail(); 
                break;
    
            // --- CÁC TRANG KHÁC ---
            case 'cart':
                (new CartController($this->conn))->index();
                break;

            case 'contact':
                (new PageController($this->conn))->contact();
                break;
    
            case 'promo':
                (new PageController($this->conn))->promo();
                break;
                
            case 'profile':
                (new PageController($this->conn))->profile();
                break;

            // --- TRANG CHỦ ---
            case 'home':
            default:
                (new HomeController($this->conn))->index();
                break;
        }
    }
}
?>