<?php
// [QUAN TRỌNG] Khai báo sử dụng Class Helper
use DACS\Helpers\FormatHelper;
use DACS\Helpers\ImageHelper;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>
        <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?> - FigureWorld
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/DACS/public/assets/css/product.css">
    <link rel="stylesheet" href="/DACS/views/layouts/header.css">
</head>
<body class="product-page">
    
<?php include __DIR__ . '/../layouts/header.php'; ?>

<main class="product-layout">
    <section class="product-main-card">
        <div class="product-gallery">
            <div class="thumb-column">
                <button type="button" class="thumb-scroll-btn thumb-scroll-up" aria-label="Cuộn lên">
                    <i class="fas fa-chevron-up"></i>
                </button>

                <div class="thumb-list" id="thumbList">
                    <?php if (!empty($images)): ?>
                        <?php foreach ($images as $index => $img): ?>
                            <button
                                type="button"
                                class="thumb-item <?php echo $index === 0 ? 'active' : ''; ?>"
                                data-full="<?php echo htmlspecialchars($img['image_url'], ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <img
                                    src="<?php echo htmlspecialchars($img['image_url'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo htmlspecialchars($product['name'] . ' - hình ' . ($index + 1), ENT_QUOTES, 'UTF-8'); ?>"
                                >
                            </button>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <button type="button" class="thumb-item active" data-full="<?php echo htmlspecialchars($firstImg, ENT_QUOTES, 'UTF-8'); ?>">
                            <img src="<?php echo htmlspecialchars($firstImg, ENT_QUOTES, 'UTF-8'); ?>" alt="Main Thumb">
                        </button>
                    <?php endif; ?>
                </div>

                <button type="button" class="thumb-scroll-btn thumb-scroll-down" aria-label="Cuộn xuống">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>

            <div class="main-image-wrap">
                <img
                    id="mainProductImage"
                    src="<?php echo htmlspecialchars($firstImg, ENT_QUOTES, 'UTF-8'); ?>"
                    alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>
        </div>

        <div class="product-info">
            <h1 class="product-title">
                <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>
            </h1>

            <div class="product-tags">
                <span class="tag tag-status">IN STOCK</span>
                <span class="tag tag-brand">
                    <?php echo htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>

            <p class="product-price">
                <?php echo FormatHelper::formatPrice((float) $product['price']); ?>
            </p>

            <p class="product-short">
                Mô hình figure chính hãng, chi tiết sắc nét, phù hợp cho sưu tầm và trưng bày.
            </p>

            <div class="product-meta">
                <div class="meta-row">
                    <span class="meta-label">Danh mục</span>
                    <span class="meta-value">
                        <?php echo htmlspecialchars(ucfirst($product['category']), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Tình trạng</span>
                    <span class="meta-value meta-ok">Còn hàng</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Vận chuyển</span>
                    <span class="meta-value">Toàn quốc</span>
                </div>
            </div>

            <div class="qty-block">
                <span class="qty-label">Số lượng</span>
                <div class="qty-control">
                    <button type="button" class="qty-btn" data-change="-1">-</button>
                    <input type="text" id="productQty" class="qty-input" value="1" readonly>
                    <button type="button" class="qty-btn" data-change="1">+</button>
                </div>
            </div>

            <div class="product-actions">
                <button type="button" class="btn btn-primary" id="btnAddToCart">
                    <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                </button>

                <a href="/DACS/public/index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </section>

    <section class="product-accordion-card">
        <div class="accordion">
            <div class="accordion-item">
                <button class="accordion-header">
                    <span>Tổng quan sản phẩm</span>
                    <i class="fas fa-plus"></i>
                </button>
                <div class="accordion-panel">
                    <p>
                        <strong>Series:</strong> <?php echo htmlspecialchars($product['series'] ?? 'N/A'); ?><br>
                        <strong>Thương hiệu:</strong> <?php echo htmlspecialchars($product['brand'] ?? 'N/A'); ?><br>
                        <strong>Tỷ lệ:</strong> <?php echo htmlspecialchars($product['scale'] ?? 'Non-scale'); ?>
                    </p>
                    <p>
                        Figure được hoàn thiện với độ chi tiết cao, màu sắc rõ nét,
                        phù hợp cho việc trưng bày trên bàn học, kệ sách hoặc tủ kính sưu tầm.
                    </p>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header">
                    <span>Chi tiết & Bảo hành</span>
                    <i class="fas fa-plus"></i>
                </button>
                <div class="accordion-panel">
                    <ul>
                        <li>Chất liệu: PVC / ABS an toàn.</li>
                        <li>Hàng chính hãng, mới 100% fullbox.</li>
                        <li>Đổi trả trong 3 ngày nếu có lỗi từ nhà sản xuất.</li>
                    </ul>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header">
                    <span>Lưu ý</span>
                    <i class="fas fa-plus"></i>
                </button>
                <div class="accordion-panel">
                    <p>
                        Màu sắc thực tế có thể chênh lệch nhẹ tùy màn hình hiển thị.
                        Tránh để sản phẩm nơi nhiệt độ quá cao hoặc ánh nắng trực tiếp.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($relatedProducts)): ?>
        <section class="related-products">
            <h2 class="related-title">Sản phẩm liên quan</h2>

            <div class="products-grid">
                <?php foreach ($relatedProducts as $p): ?>
                    <div class="product-card" onclick="window.location.href='index.php?page=product&id=<?php echo (int) $p['id']; ?>'">
                        <div class="product-image">
                            <img
                                src="<?php echo htmlspecialchars(ImageHelper::normalizeUrl($p['image_url']), ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?>"
                            >
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">
                                <?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </h3>
                            <div class="product-price product-price--highlight">
                                <?php echo FormatHelper::formatPrice((float) $p['price']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<script>
    window.PRODUCT_DATA = {
        id: <?php echo (int) $product['id']; ?>,
        name: <?php echo json_encode($product['name'], JSON_UNESCAPED_UNICODE); ?>,
        price: <?php echo (float) $product['price']; ?>,
        // Đảm bảo link ảnh đúng cho JS
        img: <?php echo json_encode(ImageHelper::normalizeUrl($firstImg), JSON_UNESCAPED_UNICODE); ?>
    };
</script>

<script src="/DACS/public/assets/js/scripts.js"></script>
<script src="/DACS/public/assets/js/product.js"></script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>