<?php
namespace DACS\Helpers;

class FormatHelper {
    public static function formatPrice($price) {
        // Định dạng tiền tệ VNĐ (ví dụ: 100.000₫)
        return number_format($price, 0, ',', '.') . '₫';
    }
}
?>