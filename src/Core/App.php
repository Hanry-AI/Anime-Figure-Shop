<?php
namespace DACS\Core;

use DACS\Config\Database;

class App {
    private $db;
    private $router;
    private $request;
    private $session;

    public function __construct() {
        // 1. Khởi tạo Session (OOP)
        $this->session = new Session();

        // 2. Khởi tạo Request (OOP)
        $this->request = new Request();

        // 3. Kết nối Database (OOP)
        $database = new Database();
        $this->db = $database->getConnection();

        // 4. Khởi tạo Router và ném kết nối DB vào
        $this->router = new Router($this->db);
    }

    public function run() {
        // Điều phối request qua router
        $this->router->resolve($this->request);
    }
}
?>