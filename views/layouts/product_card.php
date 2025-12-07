<?php
/**
 * File này dùng để hiển thị 1 thẻ sản phẩm.
 * Biến $p được truyền từ vòng lặp foreach ở file cha (index).
 */
// Load image helper if not already loaded
if (!function_exists('normalizeImageUrl')) {
    require_once __DIR__ . '/../../src/Helpers/image_helper.php';
}
?>
<div class="product-card"
     data-id="<?= (int)$p['id'] ?>"
     data-name="<?= htmlspecialchars($p['name']) ?>"
     data-series="<?= htmlspecialchars($p['series'] ?? '') ?>"
     data-brand="<?= htmlspecialchars($p['brand'] ?? '') ?>"
     data-scale="<?= htmlspecialchars($p['scale'] ?? '') ?>"
     data-price="<?= (int)$p['price'] ?>">

    <a href="/DACS/public/index.php?page=product&id=<?= (int)$p['id'] ?>" class="product-thumb-link">
        <div class="product-image">
            <img src="<?= htmlspecialchars(normalizeImageUrl($p['image_url'] ?? null)) ?>"
                 alt="<?= htmlspecialchars($p['name']) ?>"
                 style="max-width: 100%; max-height: 100%; object-fit: contain;">
        </div>
    </a>

    <div class="product-info">
        <a href="/DACS/public/index.php?page=product&id=<?= (int)$p['id'] ?>" class="product-title-link">
            <div class="product-title">
                <?= htmlspecialchars($p['name']) ?>
            </div>
        </a>

        <div class="product-price">
            <span class="price-current">
                <?= number_format($p['price'], 0, ',', '.') ?>₫
            </span>
        </div>

        <div class="product-actions">
            <button type="button" class="add-to-cart">
                <i class="fas fa-cart-plus"></i>
                Thêm vào giỏ
            </button>
        </div>
    </div>
</div>