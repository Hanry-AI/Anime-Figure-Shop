<?php
// views/pages/profile.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Load file cấu hình và Model
// Lưu ý: Nếu đã chạy qua index.php (có autoload) thì các dòng require này có thể thừa, 
// nhưng giữ lại để đảm bảo file chạy được độc lập nếu cần.
require_once __DIR__ . '/../../src/Config/db.php';
// [SỬA LỖI 1]: Đổi User.php thành UserModel.php
require_once __DIR__ . '/../../src/Models/UserModel.php'; 

use DACS\Config\Database; // [MỚI] Sử dụng namespace Database
use DACS\Models\UserModel;

// 2. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: /DACS/public/index.php?page=auth&action=login');
    exit;
}

// 3. [SỬA LỖI 2] Khởi tạo kết nối DB
// File db.php chỉ định nghĩa Class, ta phải "new" nó để lấy connection
try {
    $db = new Database();
    $conn = $db->getConnection();
} catch (Exception $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

// Khởi tạo Model
$userModel = new UserModel($conn);
$userId = $_SESSION['user_id'];
$successMsg = '';
$errorMsg = '';

// 4. Xử lý Logic (Cập nhật thông tin)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $newPass = !empty($_POST['new_password']) ? $_POST['new_password'] : null;
    $confirmPass = !empty($_POST['confirm_new_password']) ? $_POST['confirm_new_password'] : null;

    // Validate
    if ($newPass && $newPass !== $confirmPass) {
        $errorMsg = 'Mật khẩu xác nhận không khớp.';
    } else {
        $res = $userModel->updateUser($userId, $name, $email, $newPass);

        if ($res === true) {
            $successMsg = 'Cập nhật thành công.';
            $_SESSION['user_name'] = $name; 
        } else {
            $errorMsg = $res; 
        }
    }
}

// 5. Lấy thông tin user mới nhất
$currentUser = $userModel->getUserById($userId);
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
// Include header (đường dẫn tương đối)
if (file_exists(__DIR__ . '/../layouts/header.php')) {
    include __DIR__ . '/../layouts/header.php';
}
?>

<main class="profile-main">
    <h2 class="profile-title">Thông tin tài khoản</h2>

    <?php if ($errorMsg): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($errorMsg); ?>
        </div>
    <?php endif; ?>

    <?php if ($successMsg): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($successMsg); ?>
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
                <button type="button" class="toggle-password" data-target="newPassword">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="confirmNewPassword">Xác nhận mật khẩu mới</label>
            <div class="input-wrapper">
                <input type="password" id="confirmNewPassword" name="confirm_new_password" placeholder="••••••••">
                <button type="button" class="toggle-password" data-target="confirmNewPassword">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <p class="profile-meta">
            Tài khoản được tạo lúc:
            <strong><?php echo htmlspecialchars($currentUser['created_at']); ?></strong>
        </p>

        <button type="submit" class="submit-btn">Lưu thay đổi</button>
    </form>
    <?php else: ?>
        <p>Không tìm thấy thông tin tài khoản.</p>
    <?php endif; ?>
</main>

<script src="/DACS/public/assets/js/profile.js"></script>
<script src="/DACS/public/assets/js/scripts.js"></script>
</body>
</html>