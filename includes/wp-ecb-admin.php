<?php
/**
 * Class for Admin page Plugin
 * Author: KhanhECB
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ecb_ecommerce_admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        // add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }


    public function add_admin_menu()
    {
        add_menu_page(
            'ECB Commerce',
            'ECB Commerce',
            'manage_options',
            'ecb-commerce',
            array($this, 'admin_page'),
            'dashicons-cart',
            6
        );
    }

    public function enqueue_scripts($hook)
    {

        // if ($hook != 'toplevel_page_ecb_ecommerce') return;
        // wp_enqueue_style('my-admin-style', plugin_dir_url(__FILE__) . 'css/admin-style.css');
        // wp_enqueue_script('my-admin-script', plugin_dir_url(__FILE__) . 'js/admin-script.js');
    }

    public function admin_page() {
        // Kiểm tra quyền truy cập
        if (!current_user_can('manage_options')) {
            return;
        }

        // Hiển thị nội dung trang admin
        echo '<div class="wrap">';
        echo '<h1>ECB Commerce Admin Page</h1>';
        echo '<p>Welcome to the ECB Commerce admin page!</p>';
        echo '</div>';
    }
}