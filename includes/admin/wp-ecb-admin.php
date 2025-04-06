<?php
/**
 * Class for Admin page Plugin
 * Author: KhanhECB
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('ECB_ECOMMERCE_ADMIN_DIR', plugin_dir_path(__FILE__));

class ecb_ecommerce_admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function include_file($file){
        if (file_exists($file)) {
            include_once $file;
        }
        else{
            echo '<div class="wrap"><h1>File Not Found</h1><p>No find '.$file.'</p></div>';
        }
    }

    public function user_check(){
        // Kiểm tra quyền truy cập
        if (!current_user_can('manage_options')) {
            echo '<div class="wrap"><h1>Permission Denied</h1><p>You do not have permission to access this page.</p></div>';
            exit; // Ngừng thực thi nếu không có quyền
        }   
    }

    public function render_admin_page($file)
    {
        $this->user_check();
        $this->include_file(ECB_ECOMMERCE_ADMIN_DIR . $file);
    }

    public function add_admin_menu()
    {
        // Admin Menu
        add_menu_page(
            'ECB E-Commerce',
            'ECB E-Commerce',
            'manage_options',
            'ecb-ecommerce',
            array($this, 'admin_page'),
            'dashicons-cart',
            6
        );

        // SubMenu List
        $submenus = [
            'ecb-ecommerce-dashboard' => ['Dashboard', 'wp-ecb-admin-dashboard.php'],
            'ecb-ecommerce-settings' => ['Settings', 'wp-ecb-admin-setting.php'],
            'ecb-ecommerce-shortcode-manager' => ['Shortcode Manager', 'wp-ecb-admin-shortcode.php'],
            'ecb-ecommerce-order-manager' => ['Order Manager', 'wp-ecb-admin-order.php'],
            'ecb-ecommerce-product-manager' => ['Product Manager', 'wp-ecb-admin-product.php'],
            'ecb-ecommerce-user-manager' => ['User Manager', 'wp-ecb-admin-user.php'],
        ];

        foreach ($submenus as $slug => $submenu) {
            add_submenu_page(
                'ecb-ecommerce',
                $submenu[0],
                $submenu[0],
                'manage_options',
                $slug,
                function () use ($submenu) {
                    $this->render_admin_page($submenu[1]);
                }
            );
        }
    }
    public function admin_page() {
        $this->render_admin_page('wp-ecb-admin-dashboard.php');
    }
}