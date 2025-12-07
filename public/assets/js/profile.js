document.addEventListener('DOMContentLoaded', () => {
    /* === THUMBNAIL -> MAIN IMAGE === */
    const mainImg = document.getElementById('productMainImg');
    const thumbs = document.querySelectorAll('.thumb');

    thumbs.forEach(thumb => {
        thumb.addEventListener('click', () => {
            const src = thumb.getAttribute('data-large-src');
            if (!src || !mainImg) return;

            mainImg.src = src;

            thumbs.forEach(t => t.classList.remove('thumb-active'));
            thumb.classList.add('thumb-active');
        });
    });

    /* === SỐ LƯỢNG === */
    const qtyInput = document.getElementById('quantityInput');
    const qtyBtns = document.querySelectorAll('[data-qty-action]');

    qtyBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (!qtyInput) return;
            let qty = parseInt(qtyInput.value || '1', 10);

            if (btn.dataset.qtyAction === 'minus') {
                qty = Math.max(1, qty - 1);
            } else {
                qty += 1;
            }
            qtyInput.value = qty;
        });
    });

    /* === ACCORDION – ĐÓNG HẾT BAN ĐẦU === */
    const accordionItems = document.querySelectorAll('.accordion-item');

    accordionItems.forEach(item => {
        const header = item.querySelector('.accordion-header');
        const panel = item.querySelector('.accordion-panel');

        if (!header || !panel) return;

        // Đảm bảo tất cả đều đóng khi load
        item.classList.remove('is-open');
        panel.style.maxHeight = null;

        header.addEventListener('click', () => {
            const isOpen = item.classList.contains('is-open');

            // Đóng tất cả
            accordionItems.forEach(other => {
                const otherPanel = other.querySelector('.accordion-panel');
                other.classList.remove('is-open');
                if (otherPanel) otherPanel.style.maxHeight = null;
            });

            // Nếu item này đang đóng thì mở
            if (!isOpen) {
                item.classList.add('is-open');
                panel.style.maxHeight = panel.scrollHeight + 'px';
            }
        });
    });

    /* === (OPTIONAL) THÊM VÀO GIỎ – HOẶC TÍCH HỢP SAU === */
    const addBtn = document.getElementById('addToCartDetail');
    if (addBtn) {
        addBtn.addEventListener('click', () => {
            const id = addBtn.dataset.productId;
            const name = addBtn.dataset.productName;
            const price = parseFloat(addBtn.dataset.productPrice || '0');
            const qty = parseInt((qtyInput && qtyInput.value) || '1', 10);

            // Ở đây em có thể gọi AJAX hoặc dùng localStorage để lưu giỏ hàng
            console.log('Add to cart:', { id, name, price, qty });
            alert('Đã thêm ' + qty + ' x ' + name + ' vào giỏ (demo).');
        });
    }
});
