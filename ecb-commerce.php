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
define('ECB_ECOMMERCE_ASSETS_DIR', plugin_dir_url(__FILE__) . 'assets/');

class ECB_Commerce {
    private static $instance = null;
    private $class_instances = []; // Lưu trữ các instance lớp

    // Singleton pattern - đảm bảo chỉ có một instance của lớp
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Hàm khởi tạo
    private function __construct() {
        $this->load_dependencies();
        $this->init_components();
        add_action('admin_notices', [$this, 'display_admin_notices']); // Thêm hook hiển thị thông báo
    }


    // Thêm thông báo cảnh báo vào Admin
    private function add_warning_notice($message) {
        add_action('admin_notices', function() use ($message) {
            echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html($message) . '</p></div>';
        });
    }

    // Phương thức để hiển thị thông báo trong admin
    public function display_admin_notices() {
        // Không cần thêm logic ở đây vì thông báo đã được thêm trực tiếp qua add_action
    }


    // Tải các lớp phụ thuộc
    private function load_dependencies() {
        $dependencies = [
            'admin/wp-ecb-admin.php',
            'db/wp-ecb-database.php',
            'product/wp-ecb-product.php',
            'shortcode/wp-ecb-shortcode.php',
            'user/wp-ecb-user.php',
        ];        

        foreach ($dependencies as $file) {
            $file_path = ECB_ECOMMERCE_INCLUDES_DIR . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log("File not found: " . $file_path);
                $this->add_warning_notice("Plugin ECB Ecommerce - Dependency file missing: $file");
                // Dừng plugin nếu tệp quan trọng bị thiếu
                if ($file == 'db/wp-ecb-database.php') {
                    deactivate_plugins(plugin_basename(__FILE__));
                    wp_die('Critical error: Missing required database file. Plugin deactivated.');
                }
            }
        }
    }

    // Khởi tạo các lớp và đối tượng cần thiết
    private function init_components() {
        $class_list = [
            'ecb_ecommerce_admin',
            'ecb_ecommerce_db',
            'ecb_ecommerce_product',
            'ecb_ecommerce_shortcode',
            'ecb_ecommerce_user',
        ];
        foreach ($class_list as $class_name) {
            if (class_exists($class_name)) {
                $this->$class_name = new $class_name();
            } else {
                error_log("Class not found: " . $class_name);
                $this->add_warning_notice("Plugin ECB Ecommerce - Class missing: $class_name");
            }
        }
    }

    // Tải và tạo instance cho các lớp
    public function load_class($class_name) {
        $class_file = ECB_ECOMMERCE_INCLUDES_DIR . strtolower($class_name) . '.php';
        if (file_exists($class_file)) {
            $this->class_instances[$class_name] = new $class_name();
        } else {
            error_log("Class file not found: " . $class_file);
            $this->add_warning_notice("Plugin ECB Ecommerce - Class file missing: $class_file");
        }
    }

}

// Khởi tạo instance của plugin khi WordPress đã tải xong
add_action('plugin_loaded', function() {
    $ecb_commerce = ECB_Commerce::get_instance();
});
