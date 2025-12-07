// Mở/đóng FAQ
function toggleFAQ(element) {
    var faqItem  = element.parentElement;              // .faq-item
    var allItems = document.querySelectorAll('.faq-item');

    // Đóng các item khác
    allItems.forEach(function (item) {
        if (item !== faqItem) {
            item.classList.remove('active');
        }
    });

    // Mở/đóng item hiện tại
    faqItem.classList.toggle('active');
}

// Chờ DOM load xong rồi mới gắn sự kiện cho form
document.addEventListener('DOMContentLoaded', function () {
    var contactForm    = document.getElementById('contactForm');
    var successMessage = document.getElementById('successMessage');

    if (!contactForm) {
        return; // Không tìm thấy form thì thoát
    }

    // Đảm bảo lúc đầu message ẩn (nếu CSS chưa xử lý)
    if (successMessage) {
        successMessage.classList.remove('show');
    }

    contactForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Demo: chưa gửi thật đến server

        var name    = document.getElementById('name').value;
        var email   = document.getElementById('email').value;
        var phone   = document.getElementById('phone').value;
        var subject = document.getElementById('subject').value;
        var message = document.getElementById('message').value;

        // Log đơn giản (phục vụ debug)
        console.log('Contact form submitted:', name, email, phone, subject, message);

        // Hiện thông báo thành công
        if (successMessage) {
            successMessage.classList.add('show');
        }

        // Xoá dữ liệu trong form
        contactForm.reset();

        // Ẩn thông báo sau 5 giây
        if (successMessage) {
            setTimeout(function () {
                successMessage.classList.remove('show');
            }, 5000);
        }
    });
});
