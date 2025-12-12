<?php
namespace DACS\Config;

/**
 * Class Database
 * Đóng gói kết nối CSDL.
 * Dù file tên là db.php nhưng Class tên là Database.
 */
class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $dbname = "dacs2";
    
    public $conn;
    
    public function __construct() {
        $this->conn = new \mysqli($this->host, $this->user, $this->pass, $this->dbname);
        
        if ($this->conn->connect_error) {
            die("Lỗi kết nối: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>