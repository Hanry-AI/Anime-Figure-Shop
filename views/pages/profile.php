<?php
// views/pages/profile.php

// 1. Khởi động Session an toàn (chỉ start nếu chưa có)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Load file cấu hình và Model
// SỬA LỖI: Đường dẫn phải trỏ đến UserModel.php (không phải User.php)
require_once __DIR__ . '/../../src/Config/db.php';
require_once __DIR__ . '/../../src/Models/UserModel.php';

// Sử dụng Namespace
use DACS\Config\Database;
use DACS\Models\UserModel;

// 3. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: /DACS/public/index.php?page=auth&action=login');
    exit;
}

// 4. Khởi tạo kết nối Database
// (File view này đang chạy độc lập nên cần tự tạo kết nối)
try {
    $db = new Database();
    $conn = $db->getConnection();
} catch (Exception $e) {
    die("Lỗi kết nối: " . $e->getMessage());
}

// 5. Lấy thông tin user
$userModel = new UserModel($conn);
$userId = $_SESSION['user_id'];
$currentUser = $userModel->getUserById($userId);

$successMsg = '';
$errorMsg = '';

// 6. Xử lý khi bấm nút Lưu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $newPass = !empty($_POST['new_password']) ? $_POST['new_password'] : null;
    $confirmPass = !empty($_POST['confirm_new_password']) ? $_POST['confirm_new_password'] : null;

    if ($newPass && $newPass !== $confirmPass) {
        $errorMsg = 'Mật khẩu xác nhận không khớp.';
    } else {
        $res = $userModel->updateUser($userId, $name, $email, $newPass);
        if ($res === true) {
            $successMsg = 'Cập nhật thành công.';
            $_SESSION['user_name'] = $name; // Cập nhật lại tên trong session
            // Refresh lại thông tin user để hiển thị mới nhất
            $currentUser = $userModel->getUserById($userId);
        } else {
            $errorMsg = $res;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin tài khoản</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/DACS/public/assets/css/profile_styles.css">
    <link rel="stylesheet" href="/DACS/public/assets/css/styles.css">
    <link rel="stylesheet" href="/DACS/views/layouts/header.css">
</head>
<body class="profile-page">

<?php 
// Include header nếu file tồn tại
if (file_exists(__DIR__ . '/../layouts/header.php')) {
    include __DIR__ . '/../layouts/header.php';
}
?>

<main class="profile-main">
    <h2 class="profile-title">Thông tin tài khoản</h2>

    <?php if ($errorMsg): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMsg); ?>
        </div>
    <?php endif; ?>

    <?php if ($successMsg): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMsg); ?>
        </div>
    <?php endif; ?>

    <?php if ($currentUser): ?>
    <form method="post" id="profileForm">
        <div class="form-group">
            <label for="nameInput">Họ và tên</label>
            <div class="input-wrapper">
                <input type="text" id="nameInput" name="name"
                       value="<?php echo htmlspecialchars($currentUser['name']); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="emailInput">Email</label>
            <div class="input-wrapper">
                <input type="email" id="emailInput" name="email"
                       value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="newPassword">Mật khẩu mới (để trống nếu không đổi)</label>
            <div class="input-wrapper">
                <input type="password" id="newPassword" name="new_password" placeholder="••••••••">
            </div>
        </div>

        <div class="form-group">
            <label for="confirmNewPassword">Xác nhận mật khẩu mới</label>
            <div class="input-wrapper">
                <input type="password" id="confirmNewPassword" name="confirm_new_password" placeholder="••••••••">
            </div>
        </div>

        <p class="profile-meta">
            Ngày tham gia: 
            <strong><?php echo htmlspecialchars(date('d/m/Y', strtotime($currentUser['created_at']))); ?></strong>
        </p>

        <button type="submit" class="submit-btn">Lưu thay đổi</button>
    </form>
    <?php else: ?>
        <p>Không tìm thấy thông tin tài khoản (ID: <?php echo $userId; ?>).</p>
    <?php endif; ?>
</main>

<script src="/DACS/public/assets/js/scripts.js"></script>
</body>
</html>