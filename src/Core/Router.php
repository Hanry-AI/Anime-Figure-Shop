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

    // [OOP] Nhận đối tượng Request thay vì dùng $_GET
    public function resolve(Request $request) {
        $page   = $request->get('page', 'home');
        $action = $request->get('action', 'index');

        switch ($page) {
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

            case 'home':
            default:
                (new HomeController($this->conn))->index();
                break;
        }
    }
}
?>