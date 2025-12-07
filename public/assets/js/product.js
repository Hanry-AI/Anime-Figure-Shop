document.addEventListener('DOMContentLoaded', function () {
    // ===== Gallery (thay ảnh chính khi bấm thumbnail) =====
    const mainImg = document.getElementById('mainProductImage');
    const thumbs = document.querySelectorAll('.thumb-item');
    const thumbList = document.getElementById('thumbList');
    const btnUp = document.querySelector('.thumb-scroll-up');
    const btnDown = document.querySelector('.thumb-scroll-down');

    if (thumbList && btnUp && btnDown) {
        const STEP = 120; // mỗi lần cuộn ~ 1–2 thumbnail

        btnUp.addEventListener('click', () => {
            thumbList.scrollBy({ top: -STEP, behavior: 'smooth' });
        });

        btnDown.addEventListener('click', () => {
            thumbList.scrollBy({ top: STEP, behavior: 'smooth' });
        });
    }
    thumbs.forEach(btn => {
        btn.addEventListener('click', () => {
            const full = btn.dataset.full;
            if (full && mainImg) {
                mainImg.src = full;
            }
            thumbs.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // ===== Số lượng =====
    const qtyInput = document.getElementById('productQty');
    const qtyBtns = document.querySelectorAll('.qty-btn');

    qtyBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const change = parseInt(btn.dataset.change || '0', 10);
            let current = parseInt(qtyInput.value || '1', 10);
            current += change;
            if (current < 1) current = 1;
            qtyInput.value = current;
        });
    });

    // ===== Accordion (mặc định đóng, bấm mới mở) =====
    const headers = document.querySelectorAll('.accordion-header');

    headers.forEach(header => {
        header.addEventListener('click', () => {
            const item = header.parentElement;
            const panel = item.querySelector('.accordion-panel');

            const isOpen = item.classList.contains('open');
            if (isOpen) {
                panel.style.maxHeight = null;
                item.classList.remove('open');
            } else {
                panel.style.maxHeight = panel.scrollHeight + 'px';
                item.classList.add('open');
            }
        });
    });

    // ===== Thêm vào giỏ =====
    const addBtn = document.getElementById('btnAddToCart');

    if (addBtn && window.PRODUCT_DATA) {
        addBtn.addEventListener('click', () => {
            const qty = parseInt(qtyInput.value || '1', 10);

            const payload = {
                id: window.PRODUCT_DATA.id,
                name: window.PRODUCT_DATA.name,
                price: window.PRODUCT_DATA.price,
                img: window.PRODUCT_DATA.img,
                quantity: qty
            };

            // Nếu em đã có hàm addToCart global ở scripts.js thì gọi luôn
            if (typeof window.addToCart === 'function') {
                window.addToCart(payload);
            } else {
                // Demo fallback
                alert(`Đã thêm ${qty} x ${payload.name} vào giỏ (demo).`);
            }
        });
    }
});
