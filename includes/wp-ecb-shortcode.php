<?php
/**
 * Class for Shortcode 
 * Author: KhanhECB
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ecb_ecommerce_shortcode {
    private $parent;

    public function __construct($parent = null) {
        $this->parent = $parent;
        // Đăng ký shortcode
        add_shortcode('ecb_login_form', [$this, 'render_login_form']);
    }

    public function render_login_form($atts) {
        // Thiết lập tham số mặc định cho shortcode
        $atts = shortcode_atts([
            'redirect' => home_url(), // Redirect mặc định về homepage
        ], $atts, 'ecb_login_form');

        ob_start();
        // Render form đăng nhập nếu chưa đăng nhập
        wp_login_form([
            'echo' => true, // Hiển thị Form
            'redirect' => $atts['redirect'], // Dùng redirect từ shortcode
            'form_id' => 'ecb-login-form',
            'label_username' => __('Username'),
            'label_password' => __('Password'),
            'label_remember' => __('Remember Me'),
            'label_log_in' => __('Log In'),
            'remember' => true,
        ]);

        return ob_get_clean();
    }
}