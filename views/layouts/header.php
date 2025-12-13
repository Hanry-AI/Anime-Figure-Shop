<?php
/**
 * LAYOUT: HEADER
 * --------------
 * Chứa thanh điều hướng, logo, tìm kiếm, giỏ hàng và menu tài khoản.
 * Được include vào tất cả các trang.
 */

// 1. Khởi động Session nếu chưa có (để lấy thông tin đăng nhập & giỏ hàng)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Kiểm tra trạng thái đăng nhập
// Biến $isLoggedIn dùng để ẩn/hiện nút Đăng nhập/Đăng ký
$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $isLoggedIn ? ($_SESSION['user_name'] ?? 'Tài khoản') : null;
$userRole   = $isLoggedIn ? ($_SESSION['user_role'] ?? 'customer') : 'customer';

// 3. Tính toán số lượng giỏ hàng từ Session (Server-side)
// Giúp hiển thị số đúng ngay khi load trang, không cần đợi JS
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}
?>
<link rel="shortcut icon" href="/assets/img/logo.png" type="image/x-icon">
<link rel="icon" href="data:,">
<link rel="stylesheet" href="/DACS/views/layouts/header.css">
<link rel="stylesheet" href="/DACS/views/layouts/footer.css">
<link rel="stylesheet" href="/DACS/public/assets/css/cart.css">

<header class="header">
    <nav class="nav-container">
        
        <ul class="nav-menu">
            
            <?php if ($isLoggedIn && $userRole === 'admin'): ?>
                <li>
                    <a class="nav-link" href="/DACS/views/admin/manage_products.php">
                        <i class="fas fa-cogs"></i> Quản lý SP
                    </a>
                </li>
            <?php endif; ?>

            <li><a class="nav-link" href="/DACS/public/index.php">Trang chủ</a></li>
            <li><a class="nav-link" href="/DACS/public/index.php?page=anime">Anime</a></li>
            <li><a class="nav-link" href="/DACS/public/index.php?page=gundam">Gundam</a></li>
            <li><a class="nav-link" href="/DACS/public/index.php?page=marvel">Marvel</a></li>
            <li><a class="nav-link" href="/DACS/public/index.php?page=promo">Khuyến mãi</a></li>
            <li><a class="nav-link" href="/DACS/public/index.php?page=contact">Liên hệ</a></li>
        </ul>

        <div class="nav-actions">
            
            <form class="search-box" method="get" action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
                <input
                    id="searchInput"
                    name="q"
                    class="search-input"
                    placeholder="Tìm kiếm figure..."
                    value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8') : '' ?>"
                />
                <button class="search-btn" type="submit" aria-label="Tìm kiếm">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <div class="cart-icon" onclick="toggleCart();" style="cursor: pointer;">
                <i class="fas fa-shopping-cart"></i>
                <span id="cartCount" class="cart-count"><?= $cartCount ?></span>
            </div>

            <?php if ($isLoggedIn): ?>
                <div class="user-menu">
                    <button type="button" class="user-btn" id="userMenuBtn">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($userName); ?></span>
                    </button>

                    <div class="user-dropdown" id="userDropdown">
                        <a href="/DACS/views/pages/profile.php">
                            <i class="fas fa-id-badge"></i>
                            <span>Thông tin tài khoản</span>
                        </a>
                        <a href="/DACS/public/index.php?page=auth&action=logout" class="text-danger">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Đăng xuất</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a class="auth-btn login-btn" href="/DACS/public/index.php?page=auth&action=login">
                        <i class="fas fa-sign-in-alt"></i><span>Đăng nhập</span>
                    </a>
                    <a class="auth-btn register-btn" href="/DACS/public/index.php?page=auth&action=register">
                        <i class="fas fa-user-plus"></i><span>Đăng ký</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
    </nav>
</header>