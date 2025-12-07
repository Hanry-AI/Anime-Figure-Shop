<?php
session_start();
require_once __DIR__ . '/../../src/Models/User.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /DACS/views/pages/auth_index.php?action=login');
    exit;
}

$userId = $_SESSION['user_id'];
$successMsg = '';
$errorMsg = '';

// Lấy thông tin user
$currentUser = getUserById($conn, $userId);

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = updateUser(
        $conn, 
        $userId, 
        $_POST['name'], 
        $_POST['email'], 
        !empty($_POST['new_password']) ? $_POST['new_password'] : null
    );

    if ($res === true) {
        $successMsg = 'Cập nhật thành công.';
        $_SESSION['user_name'] = $_POST['name']; // Update session
        $currentUser = getUserById($conn, $userId); // Reload data
    } else {
        $errorMsg = $res;
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
    <link rel="stylesheet" href="../layouts/header.css">
</head>
<body class="profile-page">
<?php include __DIR__ . '/../layouts/header.php'; ?>

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
                <button type="button"
                        class="toggle-password"
                        data-target="newPassword">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="confirmNewPassword">Xác nhận mật khẩu mới</label>
            <div class="input-wrapper">
                <input type="password" id="confirmNewPassword" name="confirm_new_password" placeholder="••••••••">
                <button type="button"
                        class="toggle-password"
                        data-target="confirmNewPassword">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
        </div>

        <p class="profile-meta">
            Tài khoản được tạo lúc:
            <strong><?php echo htmlspecialchars($currentUser['created_at']); ?></strong>
        </p>

        <button type="submit" class="submit-btn">Lưu thay đổi</button>
    </form>
    <?php endif; ?>
</main>

<script src="/DACS/public/assets/js/profile.js"></script>
</body>
</html>
