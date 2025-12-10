<?php
namespace DACS\Models;

// Không cần require db.php ở đây nữa vì Controller sẽ truyền kết nối vào
// Nhưng giữ lại nếu bạn muốn test lẻ (tuy nhiên trong mô hình MVC chuẩn thì không cần)

class UserModel {
    private $conn;

    /**
     * Khởi tạo Model với kết nối Database
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Kiểm tra đăng nhập
     * Đổi tên từ loginUser -> login cho ngắn gọn
     */
    public function login($email, $password) {
        $sql = "SELECT id, name, email, password_hash, role FROM users WHERE email = ?";
        
        // Sử dụng $this->conn thay vì $conn truyền vào
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false; // Xử lý trường hợp lỗi prepare statement
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Kiểm tra mật khẩu
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user; // Trả về mảng thông tin user
        }
        
        return false; // Sai email hoặc pass
    }

    /**
     * Đăng ký tài khoản mới
     * Đổi tên từ registerUser -> register
     */
    public function register($name, $email, $password) {
        // 1. Kiểm tra email đã tồn tại chưa
        $check = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            return "Email này đã được sử dụng.";
        }
        $check->close();

        // 2. Tạo user mới
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $name, $email, $hash);
        
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt->close();
            return $newId; // Trả về ID người dùng mới
        }
        
        $stmt->close();
        return "Lỗi hệ thống, vui lòng thử lại.";
    }

    /**
     * Lấy thông tin user theo ID
     */
    public function getUserById($id) {
        $sql = "SELECT id, name, email, created_at, role FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $user;
    }

    /**
     * Cập nhật thông tin user
     * Đổi tên từ updateUser -> update
     */
    public function update($id, $name, $email, $newPassword = null) {
        // 1. Kiểm tra email trùng với người KHÁC (trừ chính mình)
        $check = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->bind_param('si', $email, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            return "Email này đã được tài khoản khác sử dụng.";
        }
        $check->close();

        // 2. Thực hiện cập nhật
        if ($newPassword) {
            // Nếu có đổi mật khẩu
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET name = ?, email = ?, password_hash = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('sssi', $name, $email, $hash, $id);
        } else {
            // Nếu KHÔNG đổi mật khẩu
            $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ssi', $name, $email, $id);
        }

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return true;
        }
        return "Lỗi cập nhật.";
    }
}
?>