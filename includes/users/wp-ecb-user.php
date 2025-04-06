<?php
/**
 * User Manager Class
 * Description: This class handles user-related functionalities for the ECB Ecommerce plugin.
 * Author: KhanhECB
 */

if (!defined('ABSPATH')) {
    exit;
}

class ecb_ecommerce_user {
    public function __construct() {
        // Hook vào các sự kiện WordPress liên quan tới user
        add_action('user_register', [$this, 'handle_user_register']);
        add_action('wp_login', [$this, 'handle_user_login'], 10, 2);
        add_action('woocommerce_checkout_update_user_meta', [$this, 'save_user_checkout_data']);
    }

    public function handle_user_register($user_id) {
        // Xử lý sau khi user đăng ký
    }

    public function handle_user_login($user_login, $user) {
        // Xử lý sau khi user đăng nhập
    }

    public function save_user_checkout_data($user_id) {
        // Lưu thêm thông tin trong quá trình checkout
    }

    // Bạn cũng có thể thêm các phương thức:
    // - get_purchase_history($user_id)
    // - update_user_role_after_purchase($user_id)
}
