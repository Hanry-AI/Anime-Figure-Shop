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
            // --- CÁC ROUTE CŨ ... ---

            case 'anime':
                // [QUAN TRỌNG] Truyền $request vào hàm indexAnime
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
    
            // ... (Các case khác bạn cũng nên truyền $request vào nếu cần dùng) ...

            case 'home':
            default:
                (new HomeController($this->conn))->index();
                break;
        }
    }
}
?>