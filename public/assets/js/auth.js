// Lấy sẵn các phần tử cần dùng
const loginForm      = document.getElementById('loginForm');
const registerForm   = document.getElementById('registerForm');
const headerTitle    = document.getElementById('headerTitle');
const headerSubtitle = document.getElementById('headerSubtitle');
const toggleText     = document.getElementById('toggleText');
const toggleLink     = document.getElementById('toggleLink');

// Hiển thị form đăng nhập
function showLoginForm() {
    if (!loginForm || !registerForm) return;

    loginForm.classList.remove('hidden');
    registerForm.classList.add('hidden');

    headerTitle.textContent    = 'Chào mừng trở lại';
    headerSubtitle.textContent = 'Đăng nhập để tiếp tục';
    toggleText.textContent     = 'Chưa có tài khoản? ';
    toggleLink.textContent     = 'Đăng ký ngay';
}

// Hiển thị form đăng ký
function showRegisterForm() {
    if (!loginForm || !registerForm) return;

    loginForm.classList.add('hidden');
    registerForm.classList.remove('hidden');

    headerTitle.textContent    = 'Tạo tài khoản';
    headerSubtitle.textContent = 'Đăng ký để bắt đầu';
    toggleText.textContent     = 'Đã có tài khoản? ';
    toggleLink.textContent     = 'Đăng nhập ngay';
}

// Đổi qua lại giữa login / register khi bấm link
function toggleForms(event) {
    event.preventDefault();

    if (loginForm.classList.contains('hidden')) {
        showLoginForm();
    } else {
        showRegisterForm();
    }
}

// Hiện / ẩn mật khẩu
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    input.type = (input.type === 'password') ? 'text' : 'password';
}

// Khi trang load, đọc ?action=... để chọn form mặc định
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const action = urlParams.get('action');

    if (action === 'register') {
        showRegisterForm();
    } else {
        showLoginForm();
    }
});
