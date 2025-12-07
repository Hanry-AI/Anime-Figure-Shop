<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên Hệ - Chúng Tôi Luôn Sẵn Sàng Hỗ Trợ</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../layouts/header.css">
    <link rel="stylesheet" href="/DACS/public/assets/css/contact_styles.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- BANNER / HERO -->
    <section class="contact-hero">
        <div class="contact-hero-inner">
            <h1>💬 Liên Hệ Với Chúng Tôi</h1>
            <p>Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn. Hãy để lại thông tin, chúng tôi sẽ
                phản hồi trong thời gian sớm nhất!</p>
        </div>
    </section>

    <div class="container">
        <!-- FORM LIÊN HỆ (chỉ còn 1 cột) -->
        <div class="contact-grid">
            <div class="contact-form-card">
                <h2 class="form-title">Gửi Tin Nhắn</h2>
                <p class="form-subtitle">
                    Điền thông tin bên dưới và chúng tôi sẽ liên hệ lại với bạn
                </p>

                <div id="successMessage" class="success-message">
                    <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Gửi tin nhắn thành công! Chúng tôi sẽ phản hồi sớm.</span>
                </div>

                <form id="contactForm" method="post" action="">
                    <div class="form-group">
                        <label for="name">Họ và tên <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" id="name" name="name"
                                   placeholder="Nguyễn Văn A" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email"
                                   placeholder="example@email.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <div class="input-wrapper">
                            <input type="tel" id="phone" name="phone"
                                   placeholder="0912 345 678">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subject">Chủ đề <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <select id="subject" name="subject" required>
                                <option value="">Chọn chủ đề...</option>
                                <option value="support">Hỗ trợ kỹ thuật</option>
                                <option value="sales">Tư vấn bán hàng</option>
                                <option value="partnership">Hợp tác kinh doanh</option>
                                <option value="feedback">Góp ý, phản hồi</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message">Nội dung <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <textarea id="message" name="message"
                                      placeholder="Nhập nội dung tin nhắn của bạn..." required></textarea>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        Gửi tin nhắn
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- FAQ (giữ lại nếu bạn muốn) -->
        <div class="faq-section">
            <h2 class="faq-title">Câu Hỏi Thường Gặp</h2>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Thời gian phản hồi là bao lâu?</span>
                    <div class="faq-icon">▼</div>
                </div>
                <div class="faq-answer">
                    Chúng tôi cam kết phản hồi mọi yêu cầu trong vòng 24 giờ làm việc.
                    Đối với các trường hợp khẩn cấp, vui lòng gọi đến hotline để được hỗ trợ nhanh nhất.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Tôi có thể đến văn phòng trực tiếp không?</span>
                    <div class="faq-icon">▼</div>
                </div>
                <div class="faq-answer">
                    Có, bạn có thể đến văn phòng của chúng tôi trong giờ làm việc. Tuy nhiên, chúng tôi khuyến khích bạn
                    đặt lịch hẹn trước để được phục vụ tốt nhất.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Có hỗ trợ qua chat trực tuyến không?</span>
                    <div class="faq-icon">▼</div>
                </div>
                <div class="faq-answer">
                    Có, chúng tôi có dịch vụ chat trực tuyến trên website. Đội ngũ hỗ trợ sẽ sẵn sàng giải đáp thắc mắc
                    của bạn trong giờ làm việc.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Làm sao để hợp tác kinh doanh?</span>
                    <div class="faq-icon">▼</div>
                </div>
                <div class="faq-answer">
                    Vui lòng gửi đề xuất hợp tác qua email hoặc form liên hệ, chọn chủ đề
                    "Hợp tác kinh doanh". Đội ngũ phát triển kinh doanh sẽ liên hệ lại với bạn trong thời gian sớm nhất.
                </div>
            </div>
        </div>
    </div>

    <script src="/DACS/public/assets/js/contact.js"></script>
</body>
</html>
