/**
 * SCRIPTS.JS - LOGIC CLIENT HOÀN CHỈNH
 * --------------------------------------
 * 1. Thêm/Xóa giỏ hàng qua AJAX (Server Session).
 * 2. Quản lý Sidebar Giỏ hàng (Drawer).
 * 3. Các hiệu ứng UI (Toast, Loading, Dropdown).
 */

/* =================================================================
   1. CÁC HÀM TIỆN ÍCH (UTILITIES)
   ================================================================= */

/**
 * Hiển thị thông báo nổi (Toast)
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Hiển thị / Ẩn Loading
function showLoading() {
    const el = document.getElementById('loading');
    if (el) el.style.display = 'flex';
}
function hideLoading() {
    const el = document.getElementById('loading');
    if (el) el.style.display = 'none';
}

// Cuộn trang mượt
function showProducts() {
    const section = document.getElementById('products');
    if (section) section.scrollIntoView({ behavior: 'smooth' });
}
function showCategories() {
    const section = document.getElementById('categories');
    if (section) section.scrollIntoView({ behavior: 'smooth' });
}


/* =================================================================
   2. LOGIC GIỎ HÀNG (AJAX & SIDEBAR)
   ================================================================= */

/**
 * Thêm sản phẩm vào giỏ (Gửi API)
 */
function addToCart(product) {
    if (!product || !product.id) return;
    const qty = parseInt(product.quantity) || 1;

    // Gửi request lên Server
    fetch('/DACS/public/index.php?page=cart&action=add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: product.id, quantity: qty })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('Đã thêm vào giỏ!', 'success');
            // Cập nhật số lượng trên icon
            updateCartBadge(data.total_count);
            // Mở sidebar để khách thấy ngay
            toggleCart(); 
        } else {
            showToast('Lỗi: ' + data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Lỗi kết nối server.', 'error');
    });
}

/**
 * Xóa sản phẩm khỏi giỏ (Gửi API)
 */
function removeFromCartDrawer(id) {
    if (!confirm('Bạn muốn xóa sản phẩm này?')) return;
    
    fetch('/DACS/public/index.php?page=cart&action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(res => res.json())
    .then(data => {
        // Tải lại nội dung sidebar để cập nhật danh sách
        toggleCart(); 
    })
    .catch(err => console.error(err));
}

/**
 * Mở / Đóng Sidebar Giỏ hàng
 * - Nếu đang đóng -> Gọi API lấy dữ liệu -> Vẽ HTML -> Mở ra
 */
function toggleCart() {
    const overlay = document.getElementById('cartOverlay');
    const drawer = document.getElementById('cartDrawer');
    const itemsContainer = document.getElementById('cartItems');

    // Nếu trang không có layout sidebar (VD: trang admin), chuyển hướng luôn
    if (!overlay || !drawer) {
        window.location.href = '/DACS/public/index.php?page=cart';
        return;
    }

    // Hiển thị khung sidebar
    overlay.classList.add('active');
    drawer.classList.add('active');
    
    // Hiện loading trong lúc chờ dữ liệu
    if (itemsContainer) itemsContainer.innerHTML = '<div style="padding:20px;text-align:center;color:#666;">Đang tải giỏ hàng...</div>';

    // Gọi API lấy dữ liệu giỏ hàng mới nhất từ Session
    fetch('/DACS/public/index.php?page=cart&action=api_info')
        .then(res => res.json())
        .then(data => {
            renderCartDrawer(data);
            updateCartBadge(data.count);
        })
        .catch(err => {
            console.error(err);
            itemsContainer.innerHTML = '<p style="padding:20px;color:red;text-align:center;">Lỗi tải dữ liệu.</p>';
        });
}

function closeCartDrawer() {
    const overlay = document.getElementById('cartOverlay');
    const drawer = document.getElementById('cartDrawer');
    if (overlay) overlay.classList.remove('active');
    if (drawer) drawer.classList.remove('active');
}

/**
 * Vẽ HTML danh sách sản phẩm lên Sidebar
 */
function renderCartDrawer(data) {
    const itemsEl = document.getElementById('cartItems');
    const totalEl = document.getElementById('cartTotal');
    
    if (!itemsEl) return;

    // Trường hợp giỏ rỗng
    if (!data || !data.items || data.items.length === 0) {
        itemsEl.innerHTML = `
            <div class="cart-empty" style="padding:40px 20px;text-align:center;color:#999;">
                <i class="fas fa-shopping-basket" style="font-size:32px;margin-bottom:10px;"></i>
                <p>Giỏ hàng đang trống</p>
            </div>`;
        if(totalEl) totalEl.textContent = '0₫';
        return;
    }

    // Trường hợp có hàng
    let html = '';
    data.items.forEach(item => {
        html += `
            <div class="cart-item" style="display:flex; justify-content:space-between; padding:15px 0; border-bottom:1px dashed #eee;">
                <div style="flex:1; padding-right:10px;">
                    <div class="cart-item-title" style="font-weight:600; font-size:14px; margin-bottom:4px;">
                        ${item.name}
                    </div>
                    <div class="cart-item-sub" style="font-size:12px; color:#666;">
                        ${item.price_formatted} x ${item.quantity}
                    </div>
                </div>
                <div style="text-align:right;">
                    <div class="cart-item-price" style="font-weight:700; color:#e53e3e; margin-bottom:5px;">
                        ${(item.line_total).toLocaleString('vi-VN')}₫
                    </div>
                    <button onclick="removeFromCartDrawer(${item.id})" style="border:none; background:none; color:#999; cursor:pointer; font-size:12px;">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </div>
            </div>
        `;
    });

    itemsEl.innerHTML = html;
    if (totalEl) totalEl.textContent = data.total_formatted;
}

function updateCartBadge(count) {
    const badge = document.getElementById('cartCount');
    if (badge) badge.textContent = count || 0;
}


/* =================================================================
   3. KHỞI TẠO (DOM READY)
   ================================================================= */

document.addEventListener('DOMContentLoaded', function () {
    // A. Kiểm tra trạng thái đăng nhập
    const body = document.body;
    window.IS_LOGGED_IN = body ? body.getAttribute('data-logged-in') === '1' : false;

    // B. Xử lý nút "Thêm vào giỏ" (Event Delegation)
    const grid = document.getElementById('productsGrid');
    if (grid) {
        grid.addEventListener('click', function (e) {
            const btn = e.target.closest('.add-to-cart');
            if (!btn) return;
            e.preventDefault();

            // Yêu cầu đăng nhập (Tùy chọn)
            if (!window.IS_LOGGED_IN) {
                showToast('Vui lòng đăng nhập để mua hàng.', 'warning');
                return;
            }

            const card = btn.closest('.product-card');
            if (!card) return;
            
            const id = Number(card.getAttribute('data-id')) || 0;
            if (id > 0) {
                addToCart({ id: id, quantity: 1 });
            }
        });
    }

    // C. Xử lý đóng mở Sidebar
    const overlay = document.getElementById('cartOverlay');
    const closeBtn = document.getElementById('cartCloseBtn');
    const checkoutBtn = document.getElementById('cartCheckoutBtn');

    if (overlay) overlay.addEventListener('click', closeCartDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeCartDrawer);
    
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            // Chuyển sang trang thanh toán chi tiết
            window.location.href = '/DACS/public/index.php?page=cart';
        });
    }

    // D. Dropdown Menu User (Header)
    const userBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    if (userBtn && userDropdown) {
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('open');
        });
        document.addEventListener('click', () => userDropdown.classList.remove('open'));
    }

    // E. Các sự kiện khác (Newsletter, CTA...)
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            showLoading();
            setTimeout(() => {
                hideLoading();
                showToast('Đăng ký thành công!', 'success');
                newsletterForm.reset();
            }, 1000);
        });
    }

    const ctaButtons = document.querySelectorAll('.cta-btn');
    ctaButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const target = document.getElementById(href.substring(1));
                if (target) target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});