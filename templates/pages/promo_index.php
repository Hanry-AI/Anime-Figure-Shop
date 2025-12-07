<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khuyến Mãi Hot - Ưu Đãi Hấp Dẫn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/DACS/public/assets/css/promo_styles.css">
    <link rel="stylesheet" href="../layouts/header.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <div class="hero-banner">
        <h1>🎉 Khuyến Mãi Hot</h1>
        <p>Săn ngay ưu đãi hấp dẫn - Giảm giá lên đến 70%</p>
    </div>

    <div class="container">
        <div class="featured-banner">
            <div class="banner-content">
                <h2 class="banner-title">Flash Sale 24H</h2>
                <p class="banner-description">Giảm giá cực sốc chỉ trong 24 giờ! Nhanh tay đặt hàng để nhận ưu đãi.</p>
                <div class="countdown" id="countdown">
                    <div class="countdown-item">
                        <span class="countdown-value" id="hours">12</span>
                        <span class="countdown-label">Giờ</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-value" id="minutes">34</span>
                        <span class="countdown-label">Phút</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-value" id="seconds">56</span>
                        <span class="countdown-label">Giây</span>
                    </div>
                </div>
            </div>
            <div class="banner-image">⚡</div>
        </div>

        <div class="filter-tabs">
            <button class="filter-tab active" onclick="filterPromos(event, 'all')">Tất cả</button>
            <button class="filter-tab" onclick="filterPromos(event, 'hot')">Hot Deal</button>
            <button class="filter-tab" onclick="filterPromos(event, 'new')">Mới nhất</button>
            <button class="filter-tab" onclick="filterPromos(event, 'ending')">Sắp hết hạn</button>
        </div>

        <div class="promotion-grid" id="promoGrid">
            <!-- CARD 1 -->
            <div class="promo-card" data-category="hot">
                <div class="promo-badge">HOT 🔥</div>
                <div class="promo-image">🛍️</div>
                <div class="promo-content">
                    <h3 class="promo-title">Giảm 50% Toàn Bộ Sản Phẩm</h3>
                    <p class="promo-description">Áp dụng cho tất cả sản phẩm trong cửa hàng. Không giới hạn số lượng.</p>
                    <div class="promo-details">
                        <div class="promo-discount">-50%</div>
                        <div class="promo-time">
                            <span class="promo-time-label">Còn lại</span>
                            <span class="promo-time-value">3 ngày</span>
                        </div>
                    </div>
                    <div class="promo-code">
                        <div class="code-box">SALE50</div>
                        <button class="copy-btn" onclick="copyCode('SALE50')">Copy</button>
                    </div>
                    <a href="../../index.php?code=SALE50" class="promo-btn">
                        Mua ngay
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- CARD 2 -->
            <div class="promo-card" data-category="new">
                <div class="promo-badge">MỚI ✨</div>
                <div class="promo-image">🎁</div>
                <div class="promo-content">
                    <h3 class="promo-title">Freeship Đơn 0Đ</h3>
                    <p class="promo-description">Miễn phí vận chuyển cho mọi đơn hàng. Giao hàng nhanh trong 24h.</p>
                    <div class="promo-details">
                        <div class="promo-discount">0Đ</div>
                        <div class="promo-time">
                            <span class="promo-time-label">Còn lại</span>
                            <span class="promo-time-value">5 ngày</span>
                        </div>
                    </div>
                    <div class="promo-code">
                        <div class="code-box">FREESHIP</div>
                        <button class="copy-btn" onclick="copyCode('FREESHIP')">Copy</button>
                    </div>
                    <a href="../../index.php?code=FREESHIP" class="promo-btn">
                        Mua ngay
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- CARD 3 -->
            <div class="promo-card" data-category="ending">
                <div class="promo-badge">GẤP ⏰</div>
                <div class="promo-image">💰</div>
                <div class="promo-content">
                    <h3 class="promo-title">Giảm 300K Cho Đơn 1 Triệu</h3>
                    <p class="promo-description">Áp dụng cho đơn hàng từ 1.000.000đ trở lên. Số lượng có hạn.</p>
                    <div class="promo-details">
                        <div class="promo-discount">-300K</div>
                        <div class="promo-time">
                            <span class="promo-time-label">Còn lại</span>
                            <span class="promo-time-value">1 ngày</span>
                        </div>
                    </div>
                    <div class="promo-code">
                        <div class="code-box">GIAM300</div>
                        <button class="copy-btn" onclick="copyCode('GIAM300')">Copy</button>
                    </div>
                    <a href="../../index.php?code=GIAM300" class="promo-btn">
                        Mua ngay
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- CARD 4 -->
            <div class="promo-card" data-category="hot">
                <div class="promo-badge">HOT 🔥</div>
                <div class="promo-image">🎊</div>
                <div class="promo-content">
                    <h3 class="promo-title">Mua 1 Tặng 1</h3>
                    <p class="promo-description">Chương trình mua 1 tặng 1 cho sản phẩm được chọn. Số lượng có hạn.</p>
                    <div class="promo-details">
                        <div class="promo-discount">1+1</div>
                        <div class="promo-time">
                            <span class="promo-time-label">Còn lại</span>
                            <span class="promo-time-value">7 ngày</span>
                        </div>
                    </div>
                    <div class="promo-code">
                        <div class="code-box">BUY1GET1</div>
                        <button class="copy-btn" onclick="copyCode('BUY1GET1')">Copy</button>
                    </div>
                    <a href="../../index.php?code=BUY1GET1" class="promo-btn">
                        Mua ngay
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- CARD 5 -->
            <div class="promo-card" data-category="new">
                <div class="promo-badge">MỚI ✨</div>
                <div class="promo-image">🎯</div>
                <div class="promo-content">
                    <h3 class="promo-title">Hoàn 20% Tối Đa 200K</h3>
                    <p class="promo-description">Hoàn tiền 20% vào ví cho đơn hàng thanh toán online.</p>
                    <div class="promo-details">
                        <div class="promo-discount">20%</div>
                        <div class="promo-time">
                            <span class="promo-time-label">Còn lại</span>
                            <span class="promo-time-value">10 ngày</span>
                        </div>
                    </div>
                    <div class="promo-code">
                        <div class="code-box">CASHBACK20</div>
                        <button class="copy-btn" onclick="copyCode('CASHBACK20')">Copy</button>
                    </div>
                    <a href="../../index.php?code=CASHBACK20" class="promo-btn">
                        Mua ngay
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- CARD 6 -->
            <div class="promo-card" data-category="hot">
                <div class="promo-badge">HOT 🔥</div>
                <div class="promo-image">🌟</div>
                <div class="promo-content">
                    <h3 class="promo-title">Combo Siêu Tiết Kiệm</h3>
                    <p class="promo-description">Mua combo tiết kiệm đến 40%. Càng mua nhiều càng rẻ.</p>
                    <div class="promo-details">
                        <div class="promo-discount">-40%</div>
                        <div class="promo-time">
                            <span class="promo-time-label">Còn lại</span>
                            <span class="promo-time-value">4 ngày</span>
                        </div>
                    </div>
                    <div class="promo-code">
                        <div class="code-box">COMBO40</div>
                        <button class="copy-btn" onclick="copyCode('COMBO40')">Copy</button>
                    </div>
                    <a href="../../index.php?code=COMBO40" class="promo-btn">
                        Mua ngay
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="/DACS/public/assets/js/promo.js"></script>
</body>
</html>
