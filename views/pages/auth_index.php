<?php
// Đảm bảo biến tồn tại (phòng trường hợp quên truyền)
$errors   = $errors   ?? ['login' => '', 'register' => ''];
$oldInput = $oldInput ?? [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập & Đăng ký</title>

    <link rel="stylesheet" href="/DACS/public/assets/css/auth_styles.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 id="headerTitle">Chào mừng trở lại</h1>
            <p id="headerSubtitle">Đăng nhập để tiếp tục</p>
        </div>

        <div class="form-container">
            <!-- ========== FORM ĐĂNG NHẬP ========== -->
            <form id="loginForm" method="post" action="">
                <?php if (!empty($errors['login'])): ?>
                    <div class="error-box">
                        <?php echo htmlspecialchars($errors['login']); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="loginEmail">Email</label>
                    <div class="input-wrapper">
                        <svg class="input-icon icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <input
                            type="email"
                            id="loginEmail"
                            name="login_email"
                            placeholder="example@email.com"
                            required
                            value="<?php echo isset($oldInput['login_email']) ? htmlspecialchars($oldInput['login_email']) : ''; ?>"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="loginPassword">Mật khẩu</label>
                    <div class="input-wrapper">
                        <svg class="input-icon icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <input
                            type="password"
                            id="loginPassword"
                            name="login_password"
                            placeholder="••••••••"
                            required
                        >
                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword('loginPassword')"
                        >
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember_me">
                        <span>Ghi nhớ đăng nhập</span>
                    </label>
                    <a href="#" class="forgot-password">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="submit-btn">
                    Đăng nhập
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </form>

            <!-- ========== FORM ĐĂNG KÝ ========== -->
            <form id="registerForm" class="hidden" method="post" action="">
                <?php if (!empty($errors['register'])): ?>
                    <div class="error-box">
                        <?php echo htmlspecialchars($errors['register']); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="registerName">Họ và tên</label>
                    <div class="input-wrapper">
                        <svg class="input-icon icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <input
                            type="text"
                            id="registerName"
                            name="register_name"
                            placeholder="Nguyễn Văn A"
                            required
                            value="<?php echo isset($oldInput['register_name']) ? htmlspecialchars($oldInput['register_name']) : ''; ?>"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="registerEmail">Email</label>
                    <div class="input-wrapper">
                        <svg class="input-icon icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <input
                            type="email"
                            id="registerEmail"
                            name="register_email"
                            placeholder="example@email.com"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="registerPassword">Mật khẩu</label>
                    <div class="input-wrapper">
                        <svg class="input-icon icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <input
                            type="password"
                            id="registerPassword"
                            name="register_password"
                            placeholder="••••••••"
                            required
                        >
                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword('registerPassword')"
                        >
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268-2.943 9.542-7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Xác nhận mật khẩu</label>
                    <div class="input-wrapper">
                        <svg class="input-icon icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <input
                            type="password"
                            id="confirmPassword"
                            name="confirm_password"
                            placeholder="••••••••"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    Đăng ký
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </form>

            <div class="divider">
                <span>Hoặc tiếp tục với</span>
            </div>

            <div class="social-login">
                <button class="social-btn">
                    <!-- Google icon -->
                    <svg class="icon" viewBox="0 0 24 24">
                        <path fill="#4285F4"
                              d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853"
                              d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05"
                              d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335"
                              d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Google
                </button>

                <button class="social-btn">
                    <!-- Facebook icon -->
                    <svg class="icon" viewBox="0 0 24 24">
                        <path fill="#1877F2"
                              d="M24 12.073c0-6.627-5.373-12-12-12S0 5.446 0 12.073c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43
                               c0-3.007 1.792-4.669 4.533-4.669
                               1.312 0 2.686.235 2.686.235v2.953h-1.884
                               c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385
                               C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Facebook
                </button>
            </div>

            <div class="toggle-form">
                <span id="toggleText">Chưa có tài khoản? </span>
                <a href="#" id="toggleLink" onclick="toggleForms(event)">Đăng ký ngay</a>
            </div>
        </div>
    </div>
    <script>
    // Logic PHP để check xem nãy vừa submit form nào bị lỗi
        <?php if (!empty($errors['register'])): ?>
            // Nếu có lỗi đăng ký -> Mở form đăng ký ngay lập tức
            document.addEventListener('DOMContentLoaded', function() {
                showRegisterForm();
            });
        <?php endif; ?>
    </script>
    <script src="/DACS/public/assets/js/auth.js"></script>
</body>
</html>
