<?php
/**
 * VIEW: GIỎ HÀNG (CART PAGE)
 * --------------------------
 * Hiển thị danh sách sản phẩm trong Session.
 * Dữ liệu $finalCart và $totalAmount được truyền từ CartController.
 */

// Sử dụng Helper để định dạng tiền tệ (VD: 100.000₫)
use DACS\Helpers\FormatHelper;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - FigureWorld</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="/DACS/public/assets/css/page.css">
    <link rel="stylesheet" href="/DACS/public/assets/css/cart.css">
    <link rel="stylesheet" href="/DACS/views/layouts/header.css">
    <link rel="stylesheet" href="/DACS/views/layouts/footer.css">
</head>
<body>

    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="cart-page">
        <div class="cart-header">
            <div>
                <div class="cart-title">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Giỏ hàng của bạn</span>
                </div>
                <div class="cart-subtitle">
                    Vui lòng kiểm tra lại sản phẩm và số lượng trước khi thanh toán.
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
                        <i class="fas fa-box-open" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                        <p>Giỏ hàng của bạn đang trống.</p>
                        <a href="/DACS/public/index.php" class="btn-back">
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
                                <th></th> </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($finalCart as $item): ?>
                                <tr data-id="<?= (int)$item['id'] ?>">
                                    
                                    <td>
                                        <div class="cart-item-info">
                                            <img src="<?= htmlspecialchars($item['img'], ENT_QUOTES, 'UTF-8') ?>"
                                                 class="cart-thumb"
                                                 alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>">
                                            <div>
                                                <div class="item-name">
                                                    <a href="/DACS/public/index.php?page=product&id=<?= (int)$item['id'] ?>">
                                                        <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>
                                                    </a>
                                                </div>
                                                <div class="item-meta">
                                                    Mã SP: #<?= (int)$item['id'] ?>
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
                                                onclick="removeItemFromCart(<?= (int)$item['id'] ?>)"
                                                title="Xóa sản phẩm này">
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
                    <span class="summary-value">
                        <?= FormatHelper::formatPrice((float)$totalAmount); ?>
                    </span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Phí vận chuyển</span>
                    <span class="summary-value" style="color: green;">Miễn phí</span>
                </div>
                
                <div class="summary-row total">
                    <span class="summary-label">Tổng thanh toán</span>
                    <span class="total-price">
                        <?= FormatHelper::formatPrice((float)$totalAmount); ?>
                    </span>
                </div>

                <div class="cart-actions">
                    <a href="/DACS/public/index.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                    </a>

                    <?php if (!empty($finalCart)): ?>
                        <button type="button" class="btn-pay" onclick="toggleQR()">
                            Thanh toán bằng QR <i class="fas fa-qrcode"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <div id="qr-box" class="qr-box" style="display:none;">
                    <img src="/DACS/public/assets/img/qr.jpg" alt="QR ngân hàng">
                    <div class="qr-note">
                        Quét mã QR để chuyển khoản: 
                        <strong style="color: #e53e3e;">
                            <?= FormatHelper::formatPrice((float)$totalAmount); ?>
                        </strong>.<br>
                        Nội dung: <strong>[Tên_Bạn] + [SĐT]</strong>.
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        /**
         * Hàm bật/tắt khung QR Code
         */
        function toggleQR() {
            const box = document.getElementById('qr-box');
            if (!box) return;
            // Toggle hiển thị
            box.style.display = (box.style.display === 'none' || box.style.display === '') ? 'block' : 'none';
        }

        /**
         * Hàm xóa sản phẩm khỏi giỏ hàng (Server-side Session)
         * @param {number} productId - ID sản phẩm cần xóa
         */
        function removeItemFromCart(productId) {
            if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) return;

            // Gọi API về CartController để xóa trong Session
            fetch('/DACS/public/index.php?page=cart&action=delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Xóa thành công -> Load lại trang để PHP render lại giỏ hàng mới
                    location.reload();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể xóa sản phẩm.'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi kết nối đến server.');
            });
        }
    </script>

</body>
</html>