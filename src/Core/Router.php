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
        $page   = $request->get('page', 'home');
        $action = $request->get('action', 'index'); // Lấy tên hàm cần chạy

        switch ($page) {
            case 'auth':
                (new AuthController($this->conn))->index();
                break;

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

            case 'contact':
                (new PageController($this->conn))->contact();
                break;

            case 'promo':
                (new PageController($this->conn))->promo();
                break;

            case 'profile':
                (new PageController($this->conn))->profile();
                break;

            case 'cart':
                $cartController = new CartController($this->conn);
                switch ($action) {
                    case 'add':
                        $cartController->add($request);
                        break;
                    case 'update':
                        $cartController->update($request);
                        break;
                    case 'delete':
                        $cartController->delete($request);
                        break;
                    case 'api_info': // [MỚI] API lấy thông tin giỏ hàng cho Sidebar
                        $cartController->apiInfo();
                        break;
                    case 'index':
                    default:
                        $cartController->index();
                        break;
                }
                break;

            case 'home':
            default:
                (new HomeController($this->conn))->index();
                break;
        }
    }
}