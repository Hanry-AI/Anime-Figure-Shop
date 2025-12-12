<link rel="stylesheet" href="/DACS/views/layouts/footer.css">

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-content">
        <!-- Thông tin thương hiệu -->
        <div class="footer-section">
            <h3>FigureWorld</h3>
            <p>Chuyên cung cấp figure chính hãng từ Nhật Bản với chất lượng cao và giá cả hợp lý.</p>
            <div class="social-links">
                <a href="#" aria-label="Facebook">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="#" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" aria-label="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
                <a href="#" aria-label="TikTok">
                    <i class="fab fa-tiktok"></i>
                </a>
            </div>
        </div>

        <!-- Danh mục -->
        <div class="footer-section">
            <h3>Danh mục</h3>
            <ul>
                <li><a href="#">Figure Anime</a></li>
                <li><a href="#">Gundam Model Kit</a></li>
                <li><a href="#">Marvel Figures</a></li>
                <li><a href="#">Limited Edition</a></li>
                <li><a href="#">Pre-order</a></li>
            </ul>
        </div>

        <!-- Hỗ trợ khách hàng -->
        <div class="footer-section">
            <h3>Hỗ trợ khách hàng</h3>
            <ul>
                <li><a href="#">Hướng dẫn mua hàng</a></li>
                <li><a href="#">Chính sách đổi trả</a></li>
                <li><a href="#">Phương thức thanh toán</a></li>
                <li><a href="#">Vận chuyển</a></li>
                <li><a href="#">FAQ</a></li>
            </ul>
        </div>

        <!-- Liên hệ -->
        <div class="footer-section">
            <h3>Liên hệ</h3>
            <ul>
                <li>
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Địa chỉ: (cập nhật sau)</span>
                </li>
                <li>
                    <i class="fas fa-phone"></i>
                    <span>Hotline: (cập nhật sau)</span>
                </li>
                <li>
                    <i class="fas fa-envelope"></i>
                    <span>Email: (cập nhật sau)</span>
                </li>
                <li>
                    <i class="fas fa-clock"></i>
                    <span>Giờ làm việc: 8:00 - 21:00</span>
                </li>
            </ul>
        </div>
    </div>
</footer>

<!-- GIỎ HÀNG -->
<div class="cart-overlay" id="cartOverlay"></div>

<aside class="cart-drawer" id="cartDrawer" aria-hidden="true">
    <div class="cart-header">
        <h3>Giỏ hàng</h3>
        <button
            class="cart-close"
            id="cartCloseBtn"   
            aria-label="Đóng giỏ hàng"
            type="button"
        >
            &times;
        </button>
    </div>
    <div class="cart-items" id="cartItems"></div>

    <div class="cart-footer">
        <div class="cart-total">
            <span>Tổng</span>
            <strong id="cartTotal">0₫</strong>
        </div>
        <button
            class="cart-checkout"
            id="cartCheckoutBtn"
            type="button"
        >
            Thanh toán
        </button>
    </div>
</aside>

<!-- GLOBAL LOGIN FLAG + JS -->
<script>
    // Lấy thông tin trực tiếp từ PHP Session
    window.IS_LOGGED_IN = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    
    // Nếu đã đăng nhập thì lấy ID, ngược lại là null
    window.CURRENT_USER_ID = <?php echo isset($_SESSION['user_id']) ? json_encode($_SESSION['user_id']) : 'null'; ?>;
</script>
<script src="/DACS/public/assets/js/scripts.js"></script>
