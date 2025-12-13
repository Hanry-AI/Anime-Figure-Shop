<?php
namespace DACS\Models;

// Đảm bảo file db.php tồn tại khi file này được gọi độc lập (nếu cần)
// Nhưng thường Controller đã gọi rồi. Dòng này giữ lại để an toàn.
require_once __DIR__ . '/../Config/db.php';

class UserModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * --- [MỚI] HÀM ĐĂNG NHẬP ---
     * Kiểm tra email và mật khẩu
     */
    public function login($email, $password) {
        // 1. Tìm user theo email
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // 2. Nếu tìm thấy user
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // 3. Kiểm tra mật khẩu hash
            if (password_verify($password, $user['password'])) {
                return $user; // Trả về mảng thông tin user
            }
        }

        // Sai email hoặc sai pass đều trả về false
        return false;
    }

    /**
     * --- [MỚI] HÀM ĐĂNG KÝ ---
     * Thêm user mới vào DB
     */
    public function register($name, $email, $password) {
        // 1. Kiểm tra xem email đã tồn tại chưa
        $checkSql = "SELECT id FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($checkSql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            return "Email này đã được sử dụng.";
        }

        // 2. Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // 3. Chèn vào DB (Mặc định role là 'user')
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $hashed_password);

        if ($stmt->execute()) {
            // Trả về ID của user vừa tạo (số nguyên)
            return $this->conn->insert_id;
        } else {
            return "Lỗi hệ thống: " . $stmt->error;
        }
    }

    // --- CÁC HÀM CŨ CỦA BẠN (GIỮ NGUYÊN) ---

    public function getUserById($id) {
        $sql = "SELECT id, name, email, role, created_at FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    public function updateUser($id, $name, $email, $newPassword = null) {
        // 1. Kiểm tra email trùng
        $checkSql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("si", $email, $id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            return "Email này đã được sử dụng bởi tài khoản khác.";
        }
        $checkStmt->close();

        // 2. Update
        if ($newPassword) {
            $hashedPass = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $email, $hashedPass, $id);
        } else {
            $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssi", $name, $email, $id);
        }

        if ($stmt->execute()) {
            return true;
        } else {
            return "Lỗi hệ thống: " . $stmt->error;
        }
    }

    public function updateCart($userId, $cartData) {
        // Chuyển mảng thành chuỗi JSON để lưu
        $json = json_encode($cartData);
        $stmt = $this->conn->prepare("UPDATE users SET cart_data = ? WHERE id = ?");
        $stmt->bind_param("si", $json, $userId);
        return $stmt->execute();
    }
    
    public function getCartData($userId) {
        $stmt = $this->conn->prepare("SELECT cart_data FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return json_decode($row['cart_data'], true) ?? [];
        }
        return [];
    }
}
?>