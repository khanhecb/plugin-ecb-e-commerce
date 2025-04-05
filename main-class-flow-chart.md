[WordPress Loaded]
        ↓
[plugin_loaded Hook Fired]
        ↓
[ECB_Commerce::get_instance()]
        ↓
[Is $instance null?] --No--> [Return existing instance]
        ↓ Yes
[Create new ECB_Commerce()]
        ↓
[__construct()]
        ↓
 ┌───────────────────────────────────────────┐
 │          load_dependencies()              │
 │ ───────────────────────────────────────── │
 │ require_once 'wp-ecb-database.php'         │
 │ require_once 'wp-ecb-admin.php'            │
 │ require_once 'wp-ecb-product.php'          │
 │ require_once 'wp-ecb-product-meta.php'     │
 │ require_once 'wp-ecb-shortcode.php'        │
 └───────────────────────────────────────────┘
        ↓
 ┌───────────────────────────────────────────┐
 │           init_components()               │
 │ ───────────────────────────────────────── │
 │ $this->db = new ecb_ecommerce_db()         │
 │ → $this->db->create_tables()               │
 │ $this->admin = new ecb_ecommerce_admin()   │
 │ $this->product = new ecb_ecommerce_product()│
 │ $this->product_meta = new ecb_ecommerce_product_meta() │
 │ $this->shortcode = new ecb_ecommerce_shortcode() │
 └───────────────────────────────────────────┘
        ↓
[ECB_Commerce Plugin Fully Loaded ✅]
