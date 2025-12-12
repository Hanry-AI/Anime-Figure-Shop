<?php
// [QUAN TRỌNG] Sử dụng Class Helper định dạng tiền tệ
use DACS\Helpers\FormatHelper;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận đơn hàng - FigureWorld</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/DACS/public/assets/css/page.css">
    <link rel="stylesheet" href="/DACS/public/assets/css/cart.css">
</head>
<body>

<main class="cart-page">
    <div class="cart-header">
        <div>
            <div class="cart-title">
                <i class="fas fa-shopping-bag"></i>
                <span>Xác nhận đơn hàng</span>
            </div>
            <div class="cart-subtitle">
                Vui lòng kiểm tra lại các sản phẩm trước khi thanh toán.
            </div>
        </div>
        <div class="cart-count-badge">
            <?= count($finalCart) ?> sản phẩm
        </div>
    </div>

    <div class="cart-layout">
        <div class="cart-table-wrap">
            <?php if (empty($finalCart)): ?>
                <div class="cart-empty">
                    Giỏ hàng trống. <a href="/DACS/public/index.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Quay lại mua sắm
                    </a>
                </div>
            <?php else: ?>
                <table class="cart-table">
                    <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Đơn giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($finalCart as $item): ?>
                        <tr data-id="<?= (int)$item['id'] ?>"
                            data-line-total="<?= (float)$item['line_total'] ?>">
                            <td>
                                <div class="cart-item-info">
                                    <img src="<?= htmlspecialchars($item['img'], ENT_QUOTES, 'UTF-8') ?>"
                                         class="cart-thumb"
                                         alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>">
                                    <div>
                                        <div class="item-name">
                                            <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                        <div class="item-meta">
                                            Mã: #<?= (int)$item['id'] ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="item-price">
                                <?= FormatHelper::formatPrice((float)$item['price']); ?>
                            </td>
                            <td class="item-qty">
                                <span class="item-qty-badge">x <?= (int)$item['qty'] ?></span>
                            </td>
                            <td class="item-line-total">
                                <?= FormatHelper::formatPrice((float)$item['line_total']); ?>
                            </td>
                            <td class="item-actions">
                                <button type="button"
                                        class="btn-remove"
                                        title="Xóa sản phẩm này khỏi đơn tạm tính">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="cart-summary">
            <div class="summary-title">Tóm tắt đơn hàng</div>

            <div class="summary-row">
                <span class="summary-label">Tạm tính</span>
                <span class="summary-value" id="subtotal-text">
                    <?= FormatHelper::formatPrice((float)$totalAmount); ?>
                </span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Phí vận chuyển</span>
                <span class="summary-value">Miễn phí</span>
            </div>
            <div class="summary-row total">
                <span class="summary-label">Tổng thanh toán</span>
                <span class="total-price" id="total-price-text">
                    <?= FormatHelper::formatPrice((float)$totalAmount); ?>
                </span>
            </div>

            <div class="cart-actions">
                <a href="/DACS/public/index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                </a>

                <button type="button" class="btn-pay" onclick="toggleQR()">
                    Thanh toán bằng QR <i class="fas fa-qrcode"></i>
                </button>
            </div>

            <div id="qr-box" class="qr-box" style="display:none;">
                <img src="/DACS/public/assets/img/qr.jpg" alt="QR ngân hàng">
                <div class="qr-note">
                    Quét mã QR để chuyển khoản với số tiền
                    <strong id="qr-amount">
                        <?= FormatHelper::formatPrice((float)$totalAmount); ?>
                    </strong>.<br>
                    Nội dung chuyển khoản: <strong>SĐT hoặc tên của bạn</strong>.
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    let currentTotal = <?= (float)$totalAmount ?>;

    function formatCurrency(n) {
        return n.toLocaleString('vi-VN') + '₫';
    }

    function updateTotals() {
        const totalText = document.getElementById('total-price-text');
        const subtotalText = document.getElementById('subtotal-text');
        const qrAmount = document.getElementById('qr-amount');

        const safeTotal = Math.max(0, currentTotal);

        if (totalText) totalText.textContent = formatCurrency(safeTotal);
        if (subtotalText) subtotalText.textContent = formatCurrency(safeTotal);
        if (qrAmount) qrAmount.textContent = formatCurrency(safeTotal);
    }

    function toggleQR() {
        const box = document.getElementById('qr-box');
        if (!box) return;
        box.style.display = (box.style.display === 'none' || box.style.display === '') ? 'block' : 'none';
    }

    // Xóa sản phẩm khỏi bảng (tạm tính phía client)
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-remove');
        if (!btn) return;

        const row = btn.closest('tr');
        if (!row) return;

        const lineTotal = parseFloat(row.dataset.lineTotal || '0');
        currentTotal -= lineTotal;

        row.parentNode.removeChild(row);
        updateTotals();

        // Nếu không còn dòng nào, reload về trang chủ cho sạch
        const tbody = document.querySelector('.cart-table tbody');
        if (!tbody || tbody.children.length === 0) {
            location.reload();
        }
    });

    updateTotals();
</script>

</body>
</html>