<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $isLoggedIn ? ($_SESSION['user_name'] ?? 'Tài khoản') : null;
$userRole   = $isLoggedIn ? ($_SESSION['user_role'] ?? 'customer') : 'customer';
?>

<header class="header">
    <nav class="nav-container">
        <link rel="stylesheet" href="/DACS/views/layouts/header.css">

        <ul class="nav-menu">
            <?php if ($isLoggedIn && $userRole === 'admin'): ?>
                <li>
                    <a class="nav-link" href="/DACS/views/admin/manage_products.php">
                        Quản lý SP
                    </a>
                </li>
            <?php endif; ?>
            <li><a class="nav-link" href="/DACS/public/index.php">Trang chủ</a></li>
            <li><a class="nav-link" href="/DACS/views/pages/anime_index.php">Anime</a></li>
            <li><a class="nav-link" href="/DACS/views/pages/gundam_index.php">Gundam</a></li>
            <li><a class="nav-link" href="/DACS/views/pages/marvel_index.php">Marvel</a></li>
            <li><a class="nav-link" href="/DACS/views/pages/promo_index.php">Khuyến mãi</a></li>
            <li><a class="nav-link" href="/DACS/views/pages/contact_index.php">Liên hệ</a></li>
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
                <button class="search-btn" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <div class="cart-icon" onclick="toggleCart()">
                <i class="fas fa-shopping-cart"></i>
                <span id="cartCount" class="cart-count">0</span>
            </div>

            <?php if ($isLoggedIn): ?>
                <!-- ĐÃ ĐĂNG NHẬP -->
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
                        <a href="/DACS/public/index.php?page=auth&action=logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Đăng xuất</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- CHƯA ĐĂNG NHẬP -->
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
