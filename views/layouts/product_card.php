<?php
/**
 * PARTIAL VIEW: THẺ SẢN PHẨM (PRODUCT CARD)
 * -----------------------------------------
 * File này đóng vai trò như một "khuôn mẫu" nhỏ.
 * Nó được các trang danh mục (Gundam, Anime, Marvel) gọi đi gọi lại nhiều lần 
 * bên trong vòng lặp foreach để hiển thị danh sách sản phẩm.
 * * @var array $p Biến chứa thông tin của 1 sản phẩm cụ thể (được truyền từ file cha).
 */

// BƯỚC 1: KHAI BÁO SỬ DỤNG CLASS HELPER
// -------------------------------------
// Thay vì require file thủ công (dễ lỗi), ta dùng "use" để gọi Class.
// Composer (file autoload.php ở index.php) sẽ tự động tìm và nạp file chứa Class này.
use DACS\Helpers\ImageHelper;   // Class chuyên xử lý đường dẫn ảnh
use DACS\Helpers\FormatHelper;  // Class chuyên định dạng tiền tệ

// Lưu ý: Không cần kiểm tra if(!function_exists...) nữa vì Class là duy nhất.
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
            <img src="<?= htmlspecialchars(ImageHelper::normalizeUrl($p['image_url'] ?? null)) ?>"
                 alt="<?= htmlspecialchars($p['name']) ?>"
                 style="width: 100%; height: 100%; object-fit: cover;">
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
                <?= FormatHelper::formatPrice($p['price']) ?>
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