<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Config/db.php';
require_once __DIR__ . '/../../src/Helpers/image_helper.php';

// Lấy id sản phẩm
$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($productId <= 0) {
    header('Location: /DACS/public/index.php');
    exit;
}

// Lấy thông tin sản phẩm chính
$sqlProduct = "
    SELECT 
        id,
        name,
        price,
        category,
        image_url AS img_url
    FROM products
    WHERE id = ?
";

$stmt = $conn->prepare($sqlProduct);
if (!$stmt) {
    die('Database error (product prepare failed)');
}
$stmt->bind_param('i', $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: /DACS/public/index.php');
    exit;
}

// Lấy danh sách ảnh phụ
$sqlImg = "
    SELECT image_url
    FROM product_images
    WHERE product_id = ?
    ORDER BY sort_order ASC, id ASC
";

$stmt = $conn->prepare($sqlImg);
if (!$stmt) {
    die('Database error (images prepare failed)');
}
$stmt->bind_param('i', $productId);
$stmt->execute();
$resImgs = $stmt->get_result();

$images = [];
while ($row = $resImgs->fetch_assoc()) {
    if (!empty($row['image_url'])) {
        $images[] = normalizeImageUrl($row['image_url']);
    }
}
$stmt->close();

// Fallback: nếu chưa có ảnh phụ thì dùng ảnh chính
if (empty($images)) {
    $images[] = normalizeImageUrl($product['img_url']);
}

$firstImg = $images[0];

// Lấy sản phẩm liên quan
$relatedProducts = [];
$sqlRelated = "
    SELECT 
        id,
        name,
        price,
        image_url,
        category
    FROM products
    WHERE category = ?
      AND id != ?
    ORDER BY RAND()
    LIMIT 4
";

$stmt = $conn->prepare($sqlRelated);
if (!$stmt) {
    die('Database error (related prepare failed)');
}
$stmt->bind_param('si', $product['category'], $productId);
$stmt->execute();
$resRelated = $stmt->get_result();

while ($row = $resRelated->fetch_assoc()) {
    // Normalize image URL
    $row['image_url'] = normalizeImageUrl($row['image_url']);
    $relatedProducts[] = $row;
}
$stmt->close();

// Hàm format giá
function format_price(float $number): string
{
    return number_format($number, 0, ',', '.') . '₫';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>
        <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?> - FigureWorld
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    >
    <link rel="stylesheet" href="/DACS/public/assets/css/product.css">
    <link rel="stylesheet" href="../layouts/header.css">
</head>
<body class="product-page">
<?php include __DIR__ . '/../layouts/header.php'; ?>

<main class="product-layout">
    <!-- CARD CHÍNH -->
    <section class="product-main-card">
        <!-- GALLERY BÊN TRÁI -->
        <div class="product-gallery">
            <div class="thumb-column">
                <button
                    type="button"
                    class="thumb-scroll-btn thumb-scroll-up"
                    aria-label="Cuộn lên"
                >
                    <i class="fas fa-chevron-up"></i>
                </button>

                <div class="thumb-list" id="thumbList">
                    <?php foreach ($images as $index => $url): ?>
                        <button
                            type="button"
                            class="thumb-item <?php echo $index === 0 ? 'active' : ''; ?>"
                            data-full="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                            <img
                                src="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php
                                    echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8')
                                         . ' - hình ' . ($index + 1);
                                ?>"
                            >
                        </button>
                    <?php endforeach; ?>
                </div>

                <button
                    type="button"
                    class="thumb-scroll-btn thumb-scroll-down"
                    aria-label="Cuộn xuống"
                >
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>

            <!-- Ảnh chính -->
            <div class="main-image-wrap">
                <img
                    id="mainProductImage"
                    src="<?php echo htmlspecialchars($firstImg, ENT_QUOTES, 'UTF-8'); ?>"
                    alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>
        </div>

        <!-- INFO BÊN PHẢI -->
        <div class="product-info">
            <h1 class="product-title">
                <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>
            </h1>

            <div class="product-tags">
                <span class="tag tag-status">IN STOCK</span>
                <span class="tag tag-brand">FIGUREWORLD</span>
            </div>

            <p class="product-price">
                <?php echo format_price((float) $product['price']); ?>
            </p>

            <p class="product-short">
                Mô hình figure chính hãng, chi tiết sắc nét, phù hợp cho sưu tầm và trưng bày.
            </p>

            <div class="product-meta">
                <div class="meta-row">
                    <span class="meta-label">Loại sản phẩm</span>
                    <span class="meta-value">
                        <?php echo htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8'); ?>
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
                    <input
                        type="text"
                        id="productQty"
                        class="qty-input"
                        value="1"
                        readonly
                    >
                    <button type="button" class="qty-btn" data-change="1">+</button>
                </div>
            </div>

            <div class="product-actions">
                <button
                    type="button"
                    class="btn btn-primary"
                    id="btnAddToCart"
                >
                    <i class="fas fa-cart-plus"></i>
                    Thêm vào giỏ
                </button>

                <a href="/DACS/index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại trang chủ
                </a>
            </div>
        </div>
    </section>

    <!-- CARD DƯỚI: ACCORDION -->
    <section class="product-accordion-card">
        <div class="accordion">
            <div class="accordion-item">
                <button class="accordion-header">
                    <span>Tổng quan sản phẩm</span>
                    <i class="fas fa-plus"></i>
                </button>
                <div class="accordion-panel">
                    <p>
                        Figure được hoàn thiện với độ chi tiết cao, màu sắc rõ nét,
                        phù hợp cho việc trưng bày trên bàn học, kệ sách hoặc tủ kính sưu tầm.
                    </p>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header">
                    <span>Chi tiết</span>
                    <i class="fas fa-plus"></i>
                </button>
                <div class="accordion-panel">
                    <ul>
                        <li>Chiều cao khoảng 15–25 cm (tùy mẫu).</li>
                        <li>Chất liệu: PVC / ABS.</li>
                        <li>Hàng chính hãng, mới 100%.</li>
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
            <h2 class="related-title">
                Sản phẩm liên quan
            </h2>

            <div class="products-grid">
                <?php foreach ($relatedProducts as $p): ?>
                    <div
                        class="product-card"
                        onclick="window.location.href='product.php?id=<?php echo (int) $p['id']; ?>'"
                    >
                        <div class="product-image">
                            <img
                                src="<?php echo htmlspecialchars($p['image_url'], ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?>"
                            >
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">
                                <?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </h3>
                            <div class="product-price product-price--highlight">
                                <?php echo format_price((float) $p['price']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<!-- Data cho JS -->
<script>
    window.PRODUCT_DATA = {
        id: <?php echo (int) $product['id']; ?>,
        name: <?php echo json_encode($product['name'], JSON_UNESCAPED_UNICODE); ?>,
        price: <?php echo (float) $product['price']; ?>,
        img: <?php echo json_encode($firstImg, JSON_UNESCAPED_UNICODE); ?>
    };
</script>

<script src="/DACS/public/assets/js/scripts.js"></script>
<script src="/DACS/public/assets/js/product.js"></script>
</body>
</html>
