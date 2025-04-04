# Tài Liệu Plugin WordPress Quản Lý Sản Phẩm Bán Hàng: WP ECB Plugin

`WP ECB Plugin` là một plugin WordPress được thiết kế để quản lý sản phẩm bán hàng, sử dụng **Custom Post Type (CPT)** trong giai đoạn đầu, với khả năng mở rộng để tích hợp bảng tùy chỉnh cho đơn hàng và giao dịch trong tương lai. Plugin được xây dựng theo phương pháp lập trình hướng đối tượng (OOP), sử dụng tiền tố `wp_ecb` để tránh xung đột, và bao gồm trang quản trị cùng hệ thống shortcode linh hoạt.

---

## 1. Tổng Quan

### Mục tiêu

- **Quản lý sản phẩm**: Tạo, sửa, xóa, và hiển thị sản phẩm trên front-end.
- **Trang quản trị**: Cung cấp giao diện tùy chỉnh để quản lý sản phẩm và shortcode.
- **Shortcode**: Hỗ trợ hiển thị sản phẩm và danh mục trên giao diện người dùng.
- **Khả năng mở rộng**: Chuẩn bị cho việc tích hợp đơn hàng và giao dịch sau này.

### Phương pháp thiết kế

- **OOP**: Sử dụng các class để tổ chức mã nguồn, đảm bảo tính module hóa và dễ bảo trì.
- **CPT**: Quản lý sản phẩm bằng CPT `wp_ecb_product` để tận dụng hệ sinh thái WordPress.
- **Tiền tố `wp_ecb_`**: Áp dụng cho CPT, meta keys, taxonomy, shortcode, v.v.

---

## 2. Cấu Trúc Plugin

### Thư mục

```
wp-ecb-plugin/
├── wp-ecb-plugin.php                # File chính khởi động plugin
├── includes/
│   ├── class-wp-ecb-product.php     # Quản lý CPT sản phẩm
│   ├── class-wp-ecb-product-meta.php# Quản lý meta box
│   ├── class-wp-ecb-shortcode.php   # Quản lý shortcode
│   └── class-wp-ecb-admin.php       # Quản lý trang quản trị
```

### Các Class Chính

1. **`WP_ECB_Plugin`**: Class chính, khởi tạo và kết nối các thành phần.
2. **`WP_ECB_Product`**: Đăng ký CPT `wp_ecb_product` và taxonomy `wp_ecb_product_category`.
3. **`WP_ECB_Product_Meta`**: Xử lý meta box và lưu trữ dữ liệu meta (giá, tồn kho).
4. **`WP_ECB_Shortcode`**: Quản lý các shortcode hiển thị trên front-end.
5. **`WP_ECB_Admin`**: Tạo và quản lý trang quản trị.

---

## 3. Mã Nguồn Chi Tiết

### File `wp-ecb-plugin.php`

File chính khởi động plugin:

```php
<?php
/*
Plugin Name: WP ECB Plugin
Description: A simple e-commerce plugin using CPT with OOP design, admin page, and shortcodes.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit;
}

class WP_ECB_Plugin {
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
    }

    private function load_dependencies() {
        require_once plugin_dir_path(__FILE__) . 'includes/class-wp-ecb-product.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-wp-ecb-product-meta.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-wp-ecb-shortcode.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-wp-ecb-admin.php';
    }

    private function init_components() {
        $product = new WP_ECB_Product();
        $product->register();

        $meta = new WP_ECB_Product_Meta();
        add_action('add_meta_boxes', [$meta, 'add_meta_box']);
        add_action('save_post', [$meta, 'save_meta'], 10, 1);

        new WP_ECB_Shortcode();

        $admin = new WP_ECB_Admin();
        add_action('admin_menu', [$admin, 'register_admin_page']);
    }
}

add_action('plugins_loaded', function() {
    WP_ECB_Plugin::get_instance();
});
```

### File `includes/class-wp-ecb-product.php`

Quản lý CPT sản phẩm:

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_ECB_Product {
    public function register() {
        add_action('init', [$this, 'register_cpt']);
    }

    public function register_cpt() {
        $args = [
            'public' => true,
            'label' => 'Products',
            'supports' => ['title', 'editor', 'thumbnail'],
            'taxonomies' => ['wp_ecb_product_category'],
            'has_archive' => true,
            'rewrite' => ['slug' => 'products'],
        ];
        register_post_type('wp_ecb_product', $args);

        register_taxonomy('wp_ecb_product_category', 'wp_ecb_product', [
            'hierarchical' => true,
            'label' => 'Product Categories',
            'rewrite' => ['slug' => 'product-category'],
        ]);
    }

    public function get_stock($product_id) {
        $stock = get_post_meta($product_id, 'wp_ecb_stock', true);
        // TODO: Khi có bảng wp_ecb_orders, trừ số lượng đã bán
        return $stock ? (int)$stock : 0;
    }
}
```

### File `includes/class-wp-ecb-product-meta.php`

Quản lý meta box:

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_ECB_Product_Meta {
    public function add_meta_box() {
        add_meta_box(
            'wp_ecb_product_details',
            'Product Details',
            [$this, 'render_meta_box'],
            'wp_ecb_product',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        $price = get_post_meta($post->ID, 'wp_ecb_price', true);
        $stock = get_post_meta($post->ID, 'wp_ecb_stock', true);
        $featured = get_post_meta($post->ID, 'wp_ecb_featured', true);
        wp_nonce_field('wp_ecb_save_meta', 'wp_ecb_nonce');
        ?>
        <p><label>Price: <input type="number" name="wp_ecb_price" value="<?php echo esc_attr($price); ?>" step="0.01"></label></p>
        <p><label>Stock: <input type="number" name="wp_ecb_stock" value="<?php echo esc_attr($stock); ?>"></label></p>
        <p><label>Featured: <input type="checkbox" name="wp_ecb_featured" value="1" <?php checked($featured, '1'); ?>></label></p>
        <?php
    }

    public function save_meta($post_id) {
        if (!isset($_POST['wp_ecb_nonce']) || !wp_verify_nonce($_POST['wp_ecb_nonce'], 'wp_ecb_save_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (isset($_POST['wp_ecb_price'])) {
            update_post_meta($post_id, 'wp_ecb_price', sanitize_text_field($_POST['wp_ecb_price']));
        }
        if (isset($_POST['wp_ecb_stock'])) {
            update_post_meta($post_id, 'wp_ecb_stock', sanitize_text_field($_POST['wp_ecb_stock']));
        }
        update_post_meta($post_id, 'wp_ecb_featured', isset($_POST['wp_ecb_featured']) ? '1' : '0');
    }
}
```

### File `includes/class-wp-ecb-shortcode.php`

Quản lý shortcode:

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_ECB_Shortcode {
    public function __construct() {
        add_shortcode('wp_ecb_product_list', [$this, 'render_product_list']);
        add_shortcode('wp_ecb_featured_products', [$this, 'render_featured_products']);
        add_shortcode('wp_ecb_product_categories', [$this, 'render_product_categories']);
    }

    public function render_product_list($atts) {
        $atts = shortcode_atts(['limit' => 10], $atts, 'wp_ecb_product_list');
        $query = new WP_Query([
            'post_type' => 'wp_ecb_product',
            'posts_per_page' => intval($atts['limit']),
            'post_status' => 'publish',
        ]);
        $output = '<ul class="wp-ecb-product-list">';
        while ($query->have_posts()) {
            $query->the_post();
            $price = get_post_meta(get_the_ID(), 'wp_ecb_price', true);
            $output .= '<li>' . esc_html(get_the_title()) . ' - $' . esc_html($price) . '</li>';
        }
        $output .= '</ul>';
        wp_reset_postdata();
        return $output;
    }

    public function render_featured_products($atts) {
        $atts = shortcode_atts(['limit' => 5], $atts, 'wp_ecb_featured_products');
        $query = new WP_Query([
            'post_type' => 'wp_ecb_product',
            'posts_per_page' => intval($atts['limit']),
            'post_status' => 'publish',
            'meta_query' => [
                ['key' => 'wp_ecb_featured', 'value' => '1', 'compare' => '=']
            ]
        ]);
        $output = '<ul class="wp-ecb-featured-products">';
        while ($query->have_posts()) {
            $query->the_post();
            $price = get_post_meta(get_the_ID(), 'wp_ecb_price', true);
            $output .= '<li>' . esc_html(get_the_title()) . ' - $' . esc_html($price) . ' (Featured)</li>';
        }
        $output .= '</ul>';
        wp_reset_postdata();
        return $output;
    }

    public function render_product_categories($atts) {
        $atts = shortcode_atts(['parent' => 0], $atts, 'wp_ecb_product_categories');
        $categories = get_terms([
            'taxonomy' => 'wp_ecb_product_category',
            'parent' => intval($atts['parent']),
            'hide_empty' => false,
        ]);
        $output = '<ul class="wp-ecb-product-categories">';
        foreach ($categories as $category) {
            $output .= '<li><a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a></li>';
        }
        $output .= '</ul>';
        return $output;
    }

    public function get_shortcodes() {
        return [
            'wp_ecb_product_list' => [
                'description' => 'Hiển thị danh sách sản phẩm.',
                'example' => '[wp_ecb_product_list limit="10"]',
            ],
            'wp_ecb_featured_products' => [
                'description' => 'Hiển thị sản phẩm nổi bật.',
                'example' => '[wp_ecb_featured_products limit="5"]',
            ],
            'wp_ecb_product_categories' => [
                'description' => 'Hiển thị danh mục sản phẩm.',
                'example' => '[wp_ecb_product_categories parent="0"]',
            ],
        ];
    }
}
```

### File `includes/class-wp-ecb-admin.php`

Quản lý trang quản trị:

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_ECB_Admin {
    private $shortcode_manager;

    public function __construct() {
        require_once plugin_dir_path(__FILE__) . 'class-wp-ecb-shortcode.php';
        $this->shortcode_manager = new WP_ECB_Shortcode();
    }

    public function register_admin_page() {
        add_menu_page(
            'WP ECB Manager',
            'ECB Manager',
            'manage_options',
            'wp_ecb_manager',
            [$this, 'render_admin_page'],
            'dashicons-store',
            30
        );
    }

    public function render_admin_page() {
        $this->handle_actions();
        $products = $this->get_products();
        $shortcodes = $this->shortcode_manager->get_shortcodes();
        ?>
        <div class="wrap">
            <h1>WP ECB Manager</h1>

            <h2>Danh sách sản phẩm</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo esc_html($product->ID); ?></td>
                            <td><?php echo esc_html($product->post_title); ?></td>
                            <td><?Chào bạn! Dưới đây là tài liệu Markdown được viết lại hoàn toàn, tích hợp tất cả các nội dung đã thảo luận trước đó về plugin WordPress quản lý sản phẩm bán hàng (`WP ECB Plugin`). Tài liệu này bao gồm thiết kế bằng CPT, sử dụng OOP, tiền tố `wp_ecb`, trang quản trị, quản lý shortcode, và được tổ chức lại để rõ ràng, dễ hiểu hơn.

---

<xaiArtifact artifact_id="cdb2a13c-585d-4c6b-8022-7603c1e9b5b3" artifact_version_id="cb866077-b15c-4458-865a-815d8537a7f6" title="WP ECB Plugin Documentation" contentType="text/markdown">
# Tài Liệu Plugin WordPress Quản Lý Sản Phẩm Bán Hàng: WP ECB Plugin

`WP ECB Plugin` là một plugin WordPress được thiết kế để quản lý sản phẩm bán hàng, sử dụng **Custom Post Type (CPT)** trong giai đoạn đầu, với khả năng mở rộng để tích hợp bảng tùy chỉnh cho đơn hàng và giao dịch trong tương lai. Plugin được xây dựng theo phương pháp lập trình hướng đối tượng (OOP), sử dụng tiền tố `wp_ecb` để tránh xung đột, và bao gồm trang quản trị cùng hệ thống shortcode linh hoạt.

---

## 1. Tổng Quan

### Mục tiêu
- **Quản lý sản phẩm**: Tạo, chỉnh sửa, xóa và hiển thị sản phẩm trên front-end.
- **Trang quản trị**: Cung cấp giao diện tùy chỉnh để quản lý sản phẩm và shortcode.
- **Shortcode**: Hỗ trợ hiển thị danh sách sản phẩm, sản phẩm nổi bật, và danh mục trên giao diện người dùng.
- **Khả năng mở rộng**: Chuẩn bị tích hợp quản lý đơn hàng và giao dịch khi cần.

### Phương pháp thiết kế
- **OOP**: Sử dụng các class để tổ chức mã nguồn, đảm bảo tính module hóa và dễ bảo trì.
- **CPT**: Quản lý sản phẩm bằng CPT `wp_ecb_product` để tận dụng hệ sinh thái WordPress.
- **Tiền tố `wp_ecb_`**: Áp dụng cho CPT, meta keys, taxonomy, shortcode, v.v.

---

## 2. Cấu Trúc Plugin

### Thư mục
```

wp-ecb-plugin/
├── wp-ecb-plugin.php # File chính khởi động plugin
├── includes/
│ ├── class-wp-ecb-product.php # Quản lý CPT sản phẩm
│ ├── class-wp-ecb-product-meta.php# Quản lý meta box
│ ├── class-wp-ecb-shortcode.php # Quản lý shortcode
│ └── class-wp-ecb-admin.php # Quản lý trang quản trị

````
### Các Class Chính
1. **`WP_ECB_Plugin`**: Class chính, khởi tạo và kết nối các thành phần.
2. **`WP_ECB_Product`**: Đăng ký CPT `wp_ecb_product` và taxonomy `wp_ecb_product_category`.
3. **`WP_ECB_Product_Meta`**: Xử lý meta box và lưu trữ dữ liệu meta (giá, tồn kho).
4. **`WP_ECB_Shortcode`**: Quản lý các shortcode hiển thị trên front-end.
5. **`WP_ECB_Admin`**: Tạo và quản lý trang quản trị.

---

## 3. Mã Nguồn Chi Tiết

### File `wp-ecb-plugin.php`
File chính khởi động plugin:

```php
<?php
/*
Plugin Name: WP ECB Plugin
Description: A simple e-commerce plugin using CPT with OOP design, admin page, and shortcodes.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit;
}

class WP_ECB_Plugin {
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
    }

    private function load_dependencies() {
        require_once plugin_dir_path(__FILE__) . 'includes/class-wp-ecb-product.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-wp-ecb-product-meta.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-wp-ecb-shortcode.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-wp-ecb-admin.php';
    }

    private function init_components() {
        $product = new WP_ECB_Product();
        $product->register();

        $meta = new WP_ECB_Product_Meta();
        add_action('add_meta_boxes', [$meta, 'add_meta_box']);
        add_action('save_post', [$meta, 'save_meta'], 10, 1);

        new WP_ECB_Shortcode();

        $admin = new WP_ECB_Admin();
        add_action('admin_menu', [$admin, 'register_admin_page']);
    }
}

add_action('plugins_loaded', function() {
    WP_ECB_Plugin::get_instance();
});
````

### File `includes/class-wp-ecb-product.php`

Quản lý CPT sản phẩm:

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_ECB_Product {
    public function register() {
        add_action('init', [$this, 'register_cpt']);
    }

    public function register_cpt() {
        $args = [
            'public' => true,
            'label' => 'Products',
            'supports' => ['title', 'editor', 'thumbnail'],
            'taxonomies' => ['wp_ecb_product_category'],
            'has_archive' => true,
            'rewrite' => ['slug' => 'products'],
        ];
        register_post_type('wp_ecb_product', $args);

        register_taxonomy('wp_ecb_product_category', 'wp_ecb_product', [
            'hierarchical' => true,
            'label' => 'Product Categories',
            'rewrite' => ['slug' => 'product-category'],
        ]);
    }

    public function get_stock($product_id) {
        $stock = get_post_meta($product_id, 'wp_ecb_stock', true);
        // TODO: Khi có bảng wp_ecb_orders, trừ số lượng đã bán
        return $stock ? (int)$stock : 0;
    }
}
```

### File `includes/class-wp-ecb-product-meta.php`

Quản lý meta box:

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_ECB_Product_Meta {
    public function add_meta_box() {
        add_meta_box(
            'wp_ecb_product_details',
            'Product Details',
            [$this, 'render_meta_box'],
            'wp_ecb_product',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        $price = get_post_meta($post->ID, 'wp_ecb_price', true);
        $stock = get_post_meta($post->ID, 'wp_ecb_stock', true);
        $featured = get_post_meta($post->ID, 'wp_ecb_featured', true);
        wp_nonce_field('wp_ecb_save_meta', 'wp_ecb_nonce');
        ?>
        <p><label>Price: <input type="number" name="wp_ecb_price" value="<?php echo esc_attr($price); ?>" step="0.01"></label></p>
        <p><label>Stock: <input type="number" name="wp_ecb_stock" value="<?php echo esc_attr($stock); ?>"></label></p>
        <p><label>Featured: <input type="checkbox" name="wp_ecb_featured" value="1" <?php checked($featured, '1'); ?>></label></p>
        <?php
    }

    public function save_meta($post_id) {
        if (!isset($_POST['wp_ecb_nonce']) || !wp_verify_nonce($_POST['wp_ecb_nonce'], 'wp_ecb_save_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (isset($_POST['wp_ecb_price'])) {
            update_post_meta($post_id, 'wp_ecb_price', sanitize_text_field($_POST['wp_ecb_price']));
        }
        if (isset($_POST['wp_ecb_stock'])) {
            update_post_meta($post_id, 'wp_ecb_stock', sanitize_text_field($_POST['wp_ecb_stock']));
        }
        update_post_meta($post_id, 'wp_ecb_featured', isset($_POST['wp_ecb_featured']) ? '1' : '0');
    }
}
```

### File `includes/class-wp-ecb-shortcode.php`

Quản lý shortcode:

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_ECB_Shortcode {
    public function __construct() {
        add_shortcode('wp_ecb_product_list', [$this, 'render_product_list']);
        add_shortcode('wp_ecb_featured_products', [$this, 'render_featured_products']);
        add_shortcode('wp_ecb_product_categories', [$this, 'render_product_categories']);
    }

    public function render_product_list($atts) {
        $atts = shortcode_atts(['limit' => 10], $atts, 'wp_ecb_product_list');
        $query = new WP_Query([
            'post_type' => 'wp_ecb_product',
            'posts_per_page' => intval($atts['limit']),
            'post_status' => 'publish',
        ]);
        $output = '<ul class="wp-ecb-product-list">';
        while ($query->have_posts()) {
            $query->the_post();
            $price = get_post_meta(get_the_ID(), 'wp_ecb_price', true);
            $output .= '<li>' . esc_html(get_the_title()) . ' - $' . esc_html($price) . '</li>';
        }
        $output .= '</ul>';
        wp_reset_postdata();
        return $output;
    }

    public function render_featured_products($atts) {
        $atts = shortcode_atts(['limit' => 5], $atts, 'wp_ecb_featured_products');
        $query = new WP_Query([
            'post_type' => 'wp_ecb_product',
            'posts_per_page' => intval($atts['limit']),
            'post_status' => 'publish',
            'meta_query' => [
                ['key' => 'wp_ecb_featured', 'value' => '1', 'compare' => '=']
            ]
        ]);
        $output = '<ul class="wp-ecb-featured-products">';
        while ($query->have_posts()) {
            $query->the_post();
            $price = get_post_meta(get_the_ID(), 'wp_ecb_price', true);
            $output .= '<li>' . esc_html(get_the_title()) . ' - $' . esc_html($price) . ' (Featured)</li>';
        }
        $output .= '</ul>';
        wp_reset_postdata();
        return $output;
    }

    public function render_product_categories($atts) {
        $atts = shortcode_atts(['parent' => 0], $atts, 'wp_ecb_product_categories');
        $categories = get_terms([
            'taxonomy' => 'wp_ecb_product_category',
            'parent' => intval($atts['parent']),
            'hide_empty' => false,
        ]);
        $output = '<ul class="wp-ecb-product-categories">';
        foreach ($categories as $category) {
            $output .= '<li><a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a></li>';
        }
        $output .= '</ul>';
        return $output;
    }

    public function get_shortcodes() {
        return [
            'wp_ecb_product_list' => [
                'description' => 'Hiển thị danh sách sản phẩm.',
                'example' => '[wp_ecb_product_list limit="10"]',
            ],
            'wp_ecb_featured_products' => [
                'description' => 'Hiển thị sản phẩm nổi bật.',
                'example' => '[wp_ecb_featured_products limit="5"]',
            ],
            'wp_ecb_product_categories' => [
                'description' => 'Hiển thị danh mục sản phẩm.',
                'example' => '[wp_ecb_product_categories parent="0"]',
            ],
        ];
    }
}
```

### File `includes/class-wp-ecb-admin.php`

Quản lý trang quản trị:

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_ECB_Admin {
    private $shortcode_manager;

    public function __construct() {
        require_once plugin_dir_path(__FILE__) . 'class-wp-ecb-shortcode.php';
        $this->shortcode_manager = new WP_ECB_Shortcode();
    }

    public function register_admin_page() {
        add_menu_page(
            'WP ECB Manager',
            'ECB Manager',
            'manage_options',
            'wp_ecb_manager',
            [$this, 'render_admin_page'],
            'dashicons-store',
            30
        );
    }

    public function render_admin_page() {
        $this->handle_actions();
        $products = $this->get_products();
        $shortcodes = $this->shortcode_manager->get_shortcodes();
        ?>
        <div class="wrap">
            <h1>WP ECB Manager</h1>

            <h2>Danh sách sản phẩm</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo esc_html($product->ID); ?></td>
                            <td><?php echo esc_html($product->post_title); ?></td>
                            <td><?php echo esc_html(get_post_meta($product->ID, 'wp_ecb_price', true)); ?></td>
                            <td><?php echo esc_html(get_post_meta($product->ID, 'wp_ecb_stock', true)); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=wp_ecb_manager&action=edit&product_id=' . $product->ID); ?>">Sửa</a> |
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wp_ecb_manager&action=delete&product_id=' . $product->ID), 'wp_ecb_delete_product_' . $product->ID); ?>" onclick="return confirm('Bạn có chắc muốn xóa?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>Thêm sản phẩm mới</h2>
            <form method="post" action="">
                <?php wp_nonce_field('wp_ecb_add_product', 'wp_ecb_add_nonce'); ?>
                <p><label>Tên sản phẩm: <input type="text" name="wp_ecb_product_name" required></label></p>
                <p><label>Giá: <input type="number" name="wp_ecb_price" step="0.01" required></label></p>
                <p><label>Tồn kho: <input type="number" name="wp_ecb_stock" required></label></p>
                <p><input type="submit" name="wp_ecb_add_product" value="Thêm sản phẩm" class="button button-primary"></p>
            </form>

            <h2>Danh sách Shortcode</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Tên Shortcode</th>
                        <th>Mô tả</th>
                        <th>Ví dụ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shortcodes as $name => $details): ?>
                        <tr>
                            <td><?php echo esc_html($name); ?></td>
                            <td><?php echo esc_html($details['description']); ?></td>
                            <td><code><?php echo esc_html($details['example']); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private function get_products() {
        $query = new WP_Query([
            'post_type' => 'wp_ecb_product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);
        $products = $query->get_posts();
        wp_reset_postdata();
        return $products;
    }

    private function handle_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['wp_ecb_add_product']) && isset($_POST['wp_ecb_add_nonce']) && wp_verify_nonce($_POST['wp_ecb_add_nonce'], 'wp_ecb_add_product')) {
            $product_id = wp_insert_post([
                'post_title' => sanitize_text_field($_POST['wp_ecb_product_name']),
                'post_type' => 'wp_ecb_product',
                'post_status' => 'publish',
            ]);
            if ($product_id) {
                update_post_meta($product_id, 'wp_ecb_price', sanitize_text_field($_POST['wp_ecb_price']));
                update_post_meta($product_id, 'wp_ecb_stock', sanitize_text_field($_POST['wp_ecb_stock']));
                echo '<div class="notice notice-success"><p>Đã thêm sản phẩm thành công!</p></div>';
            }
        }

        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['product_id']) && check_admin_referer('wp_ecb_delete_product_' . $_GET['product_id'])) {
            $product_id = intval($_GET['product_id']);
            if (wp_delete_post($product_id, true)) {
                echo '<div class="notice notice-success"><p>Đã xóa sản phẩm thành công!</p></div>';
            }
        }

        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['product_id'])) {
            wp_redirect(admin_url('post.php?post=' . intval($_GET['product_id']) . '&action=edit'));
            exit;
        }
    }
}
```

---

## 4. Tính Năng Hiện Tại

### Quản lý sản phẩm

- **CPT**: `wp_ecb_product` lưu trữ thông tin sản phẩm (tên, mô tả, hình ảnh).
- **Meta**: `wp_ecb_price` (giá), `wp_ecb_stock` (tồn kho), `wp_ecb_featured` (nổi bật).
- **Taxonomy**: `wp_ecb_product_category` để phân loại sản phẩm.

### Trang quản trị

- **Menuสิน- **Danh sách sản phẩm**: Hiển thị ID, tên, giá, tồn kho, với tùy chọn sửa/xóa.
- **Thêm sản phẩm**: Form để thêm sản phẩm mới trực tiếp từ trang quản trị.
- **Danh sách shortcode**: Hiển thị các shortcode hiện có với mô tả và ví dụ.

### Shortcode

| Tên Shortcode | Mô tả | Ví dụ |
| --- | --- | --- |
| `wp_ecb_product_list` | Hiển thị danh sách sản phẩm | `[wp_ecb_product_list limit="10"]` |
| `wp_ecb_featured_products` | Hiển thị sản phẩm nổi bật | `[wp_ecb_featured_products limit="5"]` |
| `wp_ecb_product_categories` | Hiển thị danh mục sản phẩm | `[wp_ecb_product_categories parent="0"]` |

---

## 5. Hướng Dẫn Sử Dụng

1. **Cài đặt**:
  - Tải thư mục `wp-ecb-plugin` vào thư mục `/wp-content/plugins/`.
  - Kích hoạt plugin trong dashboard WordPress.
2. **Quản lý sản phẩm**:
  - Vào menu **ECB Manager** để thêm, sửa, xóa sản phẩm.
3. **Hiển thị trên front-end**:
  - Sử dụng shortcode (ví dụ: `[wp_ecb_product_list]`) trong bài viết hoặc trang.
4. **Chỉnh sửa chi tiết**:
  - Vào **Products > All Products** để chỉnh sửa sản phẩm qua giao diện CPT mặc định.

---

## 6. Chuẩn Bị Mở Rộng

### Tích hợp bảng tùy chỉnh

- **Đơn hàng**: Dự phòng bảng `wp_ecb_orders` và `wp_ecb_order_items` để quản lý đơn hàng.
  
- **Giao dịch**: Dự phòng bảng `wp_ecb_transactions` để lưu lịch sử thanh toán.
  
- **Ví dụ**:
  
  ```php
  class WP_ECB_Order {
      public function create_table() {
          global $wpdb;
          $table_name = $wpdb->prefix . 'ecb_orders';
          $sql = "CREATE TABLE $table_name (
              order_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              total_amount DECIMAL(10,2) NOT NULL,
              status ENUM('pending', 'completed') DEFAULT 'pending',
              created_at DATETIME NOT NULL,
              PRIMARY KEY (order_id)
          ) $wpdb->get_charset_collate();";
          require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
          dbDelta($sql);
      }
  }
  ```
  

### Mở rộng shortcode

- Thêm shortcode mới bằng cách thêm phương thức vào `WP_ECB_Shortcode` và cập nhật `get_shortcodes()`.

---

## 7. Lưu Ý

- **Bảo mật**: Sử dụng nonce và kiểm tra quyền để bảo vệ các hành động trong admin.
- **Hiệu suất**: Hiện tại dùng `WP_Query` cho CPT; khi tích hợp bảng tùy chỉnh, chuyển sang `$wpdb` để tối ưu.
- **Tùy chỉnh giao diện**: Thêm CSS cho class như `wp-ecb-product-list` để tùy chỉnh front-end.
- **Mở rộng**: Ghi chú trong mã (TODO) đánh dấu nơi sẽ tích hợp đơn hàng/giao dịch.

---

## 8. Kết Luận

`WP ECB Plugin` cung cấp giải pháp quản lý sản phẩm bán hàng đơn giản nhưng mạnh mẽ, với thiết kế OOP linh hoạt, trang quản trị tiện lợi, và hệ thống shortcode dễ sử dụng. Giai đoạn đầu tập trung vào CPT, nhưng đã sẵn sàng mở rộng để tích hợp đơn hàng và giao dịch khi cần, đảm bảo hiệu suất và khả năng bảo trì lâu dài.