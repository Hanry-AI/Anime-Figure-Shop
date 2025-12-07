// Biến kiểm tra để tránh chạy trùng lặp (nếu lỡ file bị load 2 lần)
if (!window.IS_PRODUCT_JS_LOADED) {
    window.IS_PRODUCT_JS_LOADED = true;

    document.addEventListener('DOMContentLoaded', function () {
        setupGallery();     // 1. Cài đặt thư viện ảnh
        setupQuantity();    // 2. Cài đặt nút tăng giảm số lượng
        setupAccordion();   // 3. Cài đặt đóng/mở nội dung
        setupAddToCart();   // 4. Cài đặt nút thêm vào giỏ
    });
}

// --- 1. Xử lý Thư viện ảnh (Gallery) ---
function setupGallery() {
    const mainImg = document.getElementById('mainProductImage');
    const thumbList = document.getElementById('thumbList');
    const thumbs = document.querySelectorAll('.thumb-item');
    const btnUp = document.querySelector('.thumb-scroll-up');
    const btnDown = document.querySelector('.thumb-scroll-down');

    // Xử lý nút cuộn lên/xuống
    if (thumbList && btnUp && btnDown) {
        const STEP = 120;
        btnUp.addEventListener('click', () => thumbList.scrollBy({ top: -STEP, behavior: 'smooth' }));
        btnDown.addEventListener('click', () => thumbList.scrollBy({ top: STEP, behavior: 'smooth' }));
    }

    // Xử lý khi bấm vào ảnh nhỏ
    thumbs.forEach(btn => {
        btn.addEventListener('click', () => {
            const fullUrl = btn.dataset.full;
            if (fullUrl && mainImg) mainImg.src = fullUrl;

            // Đổi màu viền active
            document.querySelector('.thumb-item.active')?.classList.remove('active');
            btn.classList.add('active');
        });
    });
}

// --- 2. Xử lý Số lượng (Quantity) ---
function setupQuantity() {
    const qtyInput = document.getElementById('productQty');
    const qtyBtns = document.querySelectorAll('.qty-btn');

    if (!qtyInput) return;

    qtyBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const change = parseInt(btn.dataset.change || '0');
            let current = parseInt(qtyInput.value || '1');
            
            current += change;
            if (current < 1) current = 1; // Không cho nhỏ hơn 1
            
            qtyInput.value = current;
        });
    });
}

// --- 3. Xử lý Accordion (Đóng mở thông tin) ---
function setupAccordion() {
    const headers = document.querySelectorAll('.accordion-header');

    headers.forEach(header => {
        header.addEventListener('click', () => {
            const item = header.parentElement;
            const panel = item.querySelector('.accordion-panel');
            if (!item || !panel) return;

            const isOpen = item.classList.contains('open');
            
            if (isOpen) {
                // Đang mở -> Đóng lại
                panel.style.maxHeight = null;
                item.classList.remove('open');
            } else {
                // Đang đóng -> Mở ra
                panel.style.maxHeight = panel.scrollHeight + 'px';
                item.classList.add('open');
            }
        });
    });
}

// --- 4. Xử lý Thêm vào giỏ hàng ---
function setupAddToCart() {
    const addBtn = document.getElementById('btnAddToCart');
    const qtyInput = document.getElementById('productQty');

    // Kiểm tra đủ điều kiện mới chạy
    if (!addBtn || !window.PRODUCT_DATA) return;

    addBtn.addEventListener('click', () => {
        const qty = parseInt(qtyInput ? qtyInput.value : 1);

        // Tạo gói dữ liệu sản phẩm
        const payload = {
            id: window.PRODUCT_DATA.id,
            name: window.PRODUCT_DATA.name,
            price: window.PRODUCT_DATA.price,
            img: window.PRODUCT_DATA.img,
            quantity: qty
        };

        // Gọi hàm xử lý giỏ hàng chung (trong scripts.js)
        if (typeof window.addToCart === 'function') {
            window.addToCart(payload);
        } else {
            alert(`Đã thêm ${qty} x ${payload.name} vào giỏ (Demo).`);
        }
    });
}