<?php 
/*
Plugin Name: KhanhECB E-Commerce 
Description: Plugin E-Commerce for WordPress
Plugin URI: https://github.com/khanhecb/plugin-ecb-e-commerce
Version: 1.0
Author: KhanhECB
*/


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define constants
define('ECB_ECOMMERCE_VERSION', '1.0');
define('ECB_ECOMMERCE_DIR', plugin_dir_path(__FILE__));
define('ECB_ECOMMERCE_URL', plugin_dir_url(__FILE__));
define('ECB_ECOMMERCE_INCLUDES_DIR', plugin_dir_path(__FILE__) . 'includes/');

class ECB_Commerce {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_components();
        // Thêm hook để hiển thị thông báo trong admin
        add_action('admin_notices', [$this, 'display_admin_notices']);
    }

    private function load_dependencies() {
        // Dependencies list file
        $dependencies = [
            'wp-ecb-database.php',
            'wp-ecb-admin.php',
            'wp-ecb-product.php',
            'wp-ecb-product-meta.php',
            'wp-ecb-shortcode.php'
        ];

        foreach ($dependencies as $file) {
            $file_path = ECB_ECOMMERCE_INCLUDES_DIR . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log("File not found: " . $file_path);
                $this->add_warning_notice("Plugin ECB Ecommerce - Dependency file missing: $file");
            }
        }
    }

    private function init_components() {
        // Initialize the database
        if (class_exists('ecb_ecommerce_db')) {
            $this->db = new ecb_ecommerce_db();
        } else {
            $this->add_warning_notice("Plugin ECB Ecommerce - Class 'ecb_ecommerce_db' not found.");
        }

        // Initialize the admin
        if (class_exists('ecb_ecommerce_admin')) {
            $this->admin = new ecb_ecommerce_admin();
        } else {
            $this->add_warning_notice("Plugin ECB Ecommerce - Class 'ecb_ecommerce_admin' not found.");
        }

        // Initialize the product
        if (class_exists('ecb_ecommerce_product')) {
            $this->product = new ecb_ecommerce_product();
        } else {
            $this->add_warning_notice("Plugin ECB Ecommerce - Class 'ecb_ecommerce_product' not found.");
        }

        // Initialize the product meta
        if (class_exists('ecb_ecommerce_product_meta')) {
            $this->product_meta = new ecb_ecommerce_product_meta();
        } else {
            $this->add_warning_notice("Plugin ECB Ecommerce - Class 'ecb_ecommerce_product_meta' not found.");
        }

        // Initialize the shortcode
        if (class_exists('ecb_ecommerce_shortcode')) {
            $this->shortcode = new ecb_ecommerce_shortcode();
        } else {
            $this->add_warning_notice("Plugin ECB Ecommerce - Class 'ecb_ecommerce_shortcode' not found.");
        }
    }

    private function add_warning_notice($message) {
        add_action('admin_notices', function() use ($message) {
            echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html($message) . '</p></div>';
        });
    }

    // Phương thức để hiển thị thông báo
    public function display_admin_notices() {
        // Không cần thêm logic ở đây vì thông báo đã được thêm trực tiếp qua add_action
    }
}

add_action('plugin_loaded', function() {
    $ecb_commerce = ECB_Commerce::get_instance();
});