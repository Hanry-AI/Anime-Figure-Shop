<?php
namespace DACS\Models;

require_once __DIR__ . '/../Config/db.php';

class UserModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Lấy thông tin user theo ID
     */
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

    /**
     * Cập nhật thông tin user
     */
    public function updateUser($id, $name, $email, $newPassword = null) {
        // 1. Kiểm tra email có bị trùng với người khác không
        $checkSql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("si", $email, $id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            return "Email này đã được sử dụng bởi tài khoản khác.";
        }
        $checkStmt->close();

        // 2. Thực hiện cập nhật
        if ($newPassword) {
            // Nếu có đổi mật khẩu (Cần mã hóa)
            $hashedPass = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $email, $hashedPass, $id);
        } else {
            // Nếu giữ nguyên mật khẩu
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
}
?>