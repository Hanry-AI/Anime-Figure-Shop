// Đọc giỏ hàng từ localStorage (dùng chung cho mọi trang)
function loadCartFromStorage() {
    try {
        const raw = localStorage.getItem('fw_cart');
        if (!raw) return [];

        const data = JSON.parse(raw);
        if (!Array.isArray(data)) return [];

        return data
            .map(function (item) {
                return {
                    id: Number(item.id) || 0,
                    name: item.name || '',
                    // price chỉ dùng cho HIỂN THỊ ở client
                    price: Math.max(Number(item.price) || 0, 0),
                    quantity: Math.max(Number(item.quantity) || 0, 0)
                };
            })
            .filter(function (item) {
                return item.id && item.quantity > 0;
            });
    } catch (e) {
        console.error('Lỗi đọc giỏ hàng từ localStorage', e);
        return [];
    }
}

// Lưu giỏ hàng xuống localStorage
function saveCartToStorage() {
    try {
        localStorage.setItem('fw_cart', JSON.stringify(cart));
    } catch (e) {
        console.error('Lỗi lưu giỏ hàng vào localStorage', e);
    }
}

// Giỏ hàng: khởi tạo từ localStorage
// Kiểm tra xem cart đã được khai báo chưa để tránh lỗi redeclaration
if (typeof cart === 'undefined') {
    var cart = loadCartFromStorage();
}

// ------------------- Giỏ hàng -------------------

// Thêm sản phẩm vào giỏ
// LƯU Ý: price ở đây chỉ lấy để hiển thị trong giỏ hàng client-side.
// Thanh toán thực tế phải dùng giá từ DB ở cart.php.
function addToCart(product) {
    if (!product || !product.id) return;

    const existing = cart.find(function (item) {
        return item.id === product.id;
    });

    if (existing) {
        // Nếu sản phẩm đã có, chỉ tăng quantity, KHÔNG thay đổi price
        existing.quantity += 1;
    } else {
        const safePrice = Math.max(Number(product.price) || 0, 0);

        cart.push({
            id: product.id,
            name: product.name || '',
            price: safePrice, // chỉ hiển thị
            quantity: 1
        });
    }

    saveCartToStorage();
    updateCartCount();
    showToast('Đã thêm "' + product.name + '" vào giỏ hàng', 'success');
}

// Cập nhật số lượng hiển thị trên icon giỏ hàng (id="cartCount")
function updateCartCount() {
    const total = cart.reduce(function (sum, item) {
        return sum + item.quantity;
    }, 0);

    const badge = document.getElementById('cartCount');
    if (badge) {
        badge.textContent = total;
    }
}

// Fallback: hiện giỏ hàng bằng alert nếu trang KHÔNG có layout cart drawer
// CHỈ là hiển thị tham khảo cho người dùng.
function showCartAlertFallback() {
    if (cart.length === 0) {
        alert('Giỏ hàng hiện đang trống');
        return;
    }

    let message = 'Giỏ hàng của bạn (giá hiển thị ở client):\n\n';
    let total = 0;

    cart.forEach(function (item) {
        const lineTotal = item.price * item.quantity;
        total += lineTotal;

        message += '- ' + item.name +
            ' | ' + formatPrice(item.price) +
            ' x ' + item.quantity +
            ' = ' + formatPrice(lineTotal) + '\n';
    });

    message += '\nTổng cộng (tham khảo): ' + formatPrice(total) +
        '\n\nLưu ý: Số tiền thanh toán thực tế sẽ được tính lại trên server từ Database.';
    alert(message);
}

// Vẽ lại giỏ hàng trong drawer (khi mở, hoặc khi + / - / xoá)
function renderCartDrawer() {
    const itemsEl = document.getElementById('cartItems');
    const totalEl = document.getElementById('cartTotal');
    if (!itemsEl || !totalEl) {
        return;
    }

    itemsEl.innerHTML = '';
    let total = 0;

    if (cart.length === 0) {
        itemsEl.innerHTML = '<p style="padding:16px;color:#6b7280;">Giỏ hàng hiện đang trống.</p>';
        totalEl.textContent = '0₫';
        return;
    }

    cart.forEach(function (item) {
        const lineTotal = item.price * item.quantity;
        total += lineTotal;

        const row = document.createElement('div');
        row.className = 'cart-item';

        const left = document.createElement('div');

        const title = document.createElement('div');
        title.className = 'cart-item-title';
        title.textContent = item.name;

        const sub = document.createElement('div');
        sub.className = 'cart-item-sub';
        sub.textContent = formatPrice(item.price);

        const controls = document.createElement('div');
        controls.className = 'cart-item-controls';
        controls.innerHTML =
            '<button class="cart-qty-btn" data-action="decrease" data-id="' + item.id + '">-</button>' +
            '<span class="cart-qty-value">' + item.quantity + '</span>' +
            '<button class="cart-qty-btn" data-action="increase" data-id="' + item.id + '">+</button>' +
            '<button class="cart-remove-btn" data-action="remove" data-id="' + item.id + '">Xóa</button>';

        left.appendChild(title);
        left.appendChild(sub);
        left.appendChild(controls);

        const right = document.createElement('div');
        right.className = 'cart-item-price';
        right.textContent = formatPrice(lineTotal);

        row.appendChild(left);
        row.appendChild(right);
        itemsEl.appendChild(row);
    });

    totalEl.textContent = formatPrice(total);
}

// Thay đổi số lượng 1 sản phẩm trong giỏ (delta = +1 hoặc -1)
function changeCartQuantity(productId, delta) {
    const idx = cart.findIndex(function (item) {
        return item.id === productId;
    });
    if (idx === -1) return;

    cart[idx].quantity += delta;
    if (cart[idx].quantity <= 0) {
        cart.splice(idx, 1);
    }

    saveCartToStorage();
    updateCartCount();
    renderCartDrawer();
}

// Xoá hẳn 1 sản phẩm khỏi giỏ
function removeCartItem(productId) {
    cart = cart.filter(function (item) {
        return item.id !== productId;
    });

    saveCartToStorage();
    updateCartCount();
    renderCartDrawer();
}

// Mở giỏ hàng dạng drawer (nếu có), nếu không thì dùng alert fallback
// Các số tiền ở đây cũng CHỈ mang tính hiển thị cho client.
function toggleCart() {
    if (cart.length === 0) {
        showToast('Giỏ hàng hiện đang trống', 'warning');
        return;
    }

    const overlay = document.getElementById('cartOverlay');
    const drawer  = document.getElementById('cartDrawer');

    // Nếu trang không có layout cart (ví dụ 1 page khác) => dùng alert
    if (!overlay || !drawer) {
        showCartAlertFallback();
        return;
    }

    renderCartDrawer();

    overlay.classList.add('active');
    drawer.classList.add('active');
}

// Thanh toán:
// - CHỈ gửi id + quantity lên cart.php
// - KHÔNG gửi price, KHÔNG gửi tổng tiền
// => Server PHẢI tự lấy giá từ DB và tính tiền để tránh bị thao túng frontend.
function checkout() {
    if (cart.length === 0) {
        showToast('Giỏ hàng hiện đang trống', 'warning');
        return;
    }

    // Tạo form POST ẩn gửi sang cart.php
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/DACS/pages/cart.php';

    // Chỉ gửi id + quantity, KHÔNG gửi price
    const payload = cart.map(function (item) {
        return {
            id: item.id,
            quantity: item.quantity
        };
    });

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'cart';
    input.value = JSON.stringify(payload);

    form.appendChild(input);
    document.body.appendChild(form);

    form.submit();
}

// ------------------- Scroll tới section -------------------

function showProducts() {
    const section = document.getElementById('products');
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

function showCategories() {
    const section = document.getElementById('categories');
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

// ------------------- Loading overlay -------------------

function showLoading() {
    const el = document.getElementById('loading');
    if (el) {
        el.style.display = 'flex';
    }
}

function hideLoading() {
    const el = document.getElementById('loading');
    if (el) {
        el.style.display = 'none';
    }
}

// ------------------- Utils -------------------

function formatPrice(number) {
    const n = Number(number) || 0;
    return n.toLocaleString('vi-VN') + '₫';
}

// Toast đơn giản ở góc phải màn hình
function showToast(message, type) {
    const toast = document.createElement('div');
    
    // Chỉ gán class, không gán style nội tuyến nữa
    // Mặc định type là 'info' nếu không truyền
    toast.className = 'toast ' + (type || 'info'); 
    toast.textContent = message;

    // XÓA HẾT các dòng toast.style.xxx = ... cũ đi
    // Vì CSS đã lo việc này rồi!

    document.body.appendChild(toast);

    setTimeout(function () {
        // Thêm hiệu ứng mờ dần trước khi xóa (Optional)
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 3000);
}

// ------------------- Khởi tạo DOM -------------------

document.addEventListener('DOMContentLoaded', function () {
    // Đọc trạng thái đăng nhập từ attribute trên <body> (data-logged-in="1|0")
    const body = document.body;
    if (body) {
        window.IS_LOGGED_IN = body.getAttribute('data-logged-in') === '1';
    } else {
        window.IS_LOGGED_IN = false;
    }

    // Đồng bộ số lượng giỏ hàng dựa trên localStorage
    updateCartCount();

    // Bắt sự kiện click nút "Thêm vào giỏ" trong danh sách sản phẩm nổi bật / các page shop
    const grid = document.getElementById('productsGrid');
    if (grid) {
        grid.addEventListener('click', function (e) {
            const btn = e.target.closest('.add-to-cart');
            if (!btn) return;

            // CHẶN khi chưa đăng nhập
            if (!window.IS_LOGGED_IN) {
                e.preventDefault();
                showToast('Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng.', 'warning');
                // muốn redirect qua trang login thì thêm window.location.href = '...';
                return;
            }

            const card = btn.closest('.product-card');
            if (!card) return;

            const id = Number(card.getAttribute('data-id')) || 0;
            if (!id) return;

            const titleElement = card.querySelector('.product-title');
            const nameAttr = card.getAttribute('data-name');
            const name = nameAttr || (titleElement ? titleElement.textContent : '');

            // Lưu ý: price lấy từ data-price CHỈ để hiển thị ở client.
            // Giá thanh toán sẽ được tính lại ở server từ DB.
            const priceAttr = card.getAttribute('data-price');
            const price = Math.max(Number(priceAttr) || 0, 0);

            addToCart({
                id: id,
                name: name,
                price: price
            });
        });
    }

    // Form newsletter
    const form = document.querySelector('.newsletter-form');
    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const emailInput = form.querySelector('.newsletter-input');
            const email = emailInput ? emailInput.value.trim() : '';

            if (!email) return;

            showLoading();

            setTimeout(function () {
                hideLoading();
                showToast('Cảm ơn bạn đã đăng ký: ' + email, 'success');
                form.reset();
            }, 1000);
        });
    }

    // Gắn lại handler cho 2 nút CTA (phòng trường hợp href đã đổi)
    const primaryBtn = document.querySelector('.cta-btn.cta-primary');
    if (primaryBtn) {
        primaryBtn.addEventListener('click', function (e) {
            e.preventDefault();
            showProducts();
        });
    }

    const secondaryBtn = document.querySelector('.cta-btn.cta-secondary');
    if (secondaryBtn) {
        secondaryBtn.addEventListener('click', function (e) {
            e.preventDefault();
            showCategories();
        });
    }

    // Xử lý đóng cart drawer nếu tồn tại
    const overlay = document.getElementById('cartOverlay');
    const drawer  = document.getElementById('cartDrawer');
    const closeBtn = document.getElementById('cartCloseBtn');
    const checkoutBtn = document.getElementById('cartCheckoutBtn');
    const cartItemsContainer = document.getElementById('cartItems');

    function closeCartDrawer() {
        if (overlay) overlay.classList.remove('active');
        if (drawer)  drawer.classList.remove('active');
    }

    if (overlay) {
        overlay.addEventListener('click', closeCartDrawer);
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', closeCartDrawer);
    }
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function () {
            if (cart.length === 0) {
                showToast('Giỏ hàng hiện đang trống', 'warning');
                return;
            }
            closeCartDrawer();
            checkout();
        });
    }

    // Event delegation cho nút + / - / Xóa trong giỏ hàng
    if (cartItemsContainer) {
        cartItemsContainer.addEventListener('click', function (e) {
            const btn = e.target.closest('button[data-action]');
            if (!btn) return;

            const id = Number(btn.getAttribute('data-id')) || 0;
            if (!id) return;

            const action = btn.getAttribute('data-action');
            if (action === 'increase') {
                changeCartQuantity(id, 1);
            } else if (action === 'decrease') {
                changeCartQuantity(id, -1);
            } else if (action === 'remove') {
                removeCartItem(id);
            }
        });
    }

    // Thông báo chào mừng nhẹ nhàng
    setTimeout(function () {
        showToast('Chào mừng đến với FigureWorld! 🎉', 'success');
    }, 800);

    // Dropdown user ở header
    const userBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    if (userBtn && userDropdown) {
        // Bấm vào nút -> toggle dropdown
        userBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            userDropdown.classList.toggle('open');
        });

        // Bấm ra ngoài -> đóng dropdown
        document.addEventListener('click', function () {
            userDropdown.classList.remove('open');
        });
    }
});
