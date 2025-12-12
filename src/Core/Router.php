<?php
namespace DACS\Core;

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

    public function resolve(Request $request) {
        // Lấy thông tin page từ Request object (thay vì $_GET)
        $page   = $request->get('page', 'home');
        $action = $request->get('action', 'index');

        switch ($page) {
            // --- CÁC ROUTE ĐÃ CÓ ---
            case 'anime':
                (new ProductController($this->conn))->indexAnime($request);
                break;
                
            case 'gundam':
                (new ProductController($this->conn))->indexGundam($request);
                break;
                
            case 'marvel':
                (new ProductController($this->conn))->indexMarvel($request);
                break;
    
            case 'product':
                (new ProductController($this->conn))->detail($request); 
                break;

            // --- BỔ SUNG CÁC ROUTE CÒN THIẾU TẠI ĐÂY ---

            case 'contact':
                (new PageController($this->conn))->contact();
                break;

            case 'promo':
                (new PageController($this->conn))->promo();
                break;

            case 'cart':
                (new CartController($this->conn))->index();
                break;

            case 'auth':
                (new AuthController($this->conn))->index();
                break;

            case 'profile':
                (new PageController($this->conn))->profile();
                break;

            // --- MẶC ĐỊNH ---
            case 'home':
            default:
                (new HomeController($this->conn))->index();
                break;
        }
    }
}
?>