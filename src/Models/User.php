<?php
require_once __DIR__ . '/../Config/db.php';

/**
 * Kiểm tra đăng nhập
 */
function loginUser($conn, $email, $password) {
    $sql = "SELECT id, name, email, password_hash, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password_hash'])) {
        return $user; // Trả về thông tin user nếu đúng
    }
    return false; // Sai email hoặc pass
}

/**
 * Đăng ký tài khoản mới
 */
function registerUser($conn, $name, $email, $password) {
    // 1. Check email tồn tại
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param('s', $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        return "Email này đã được sử dụng.";
    }
    $check->close();

    // 2. Tạo user mới
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $name, $email, $hash);
    
    if ($stmt->execute()) {
        return $stmt->insert_id; // Trả về ID người dùng mới
    }
    return "Lỗi hệ thống, vui lòng thử lại.";
}

/**
 * Lấy thông tin user theo ID
 */
function getUserById($conn, $id) {
    $stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $user;
}

/**
 * Cập nhật thông tin user
 */
function updateUser($conn, $id, $name, $email, $newPassword = null) {
    // Check email trùng với người KHÁC
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->bind_param('si', $email, $id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        return "Email này đã được tài khoản khác sử dụng.";
    }

    if ($newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password_hash = ? WHERE id = ?");
        $stmt->bind_param('sssi', $name, $email, $hash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param('ssi', $name, $email, $id);
    }

    if ($stmt->execute()) {
        return true;
    }
    return "Lỗi cập nhật.";
}
?>