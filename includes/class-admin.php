<?php

class GCC_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_gcc_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_gcc_delete_product', array($this, 'delete_product'));
        add_action('wp_ajax_gcc_update_product', array($this, 'update_product'));
        add_action('wp_ajax_gcc_create_product', array($this, 'create_product'));
        add_action('wp_ajax_gcc_get_product', array($this, 'get_product'));
        add_action('wp_ajax_gcc_get_submits', array($this, 'get_submits'));
        add_action('wp_ajax_gcc_delete_submit', array($this, 'delete_submit'));
        add_action('wp_ajax_gcc_clear_cache', array($this, 'clear_cache'));
        
        // Persona management AJAX handlers
        add_action('wp_ajax_gcc_get_personas', array($this, 'get_personas'));
        add_action('wp_ajax_gcc_get_persona', array($this, 'get_persona'));
        add_action('wp_ajax_gcc_create_persona', array($this, 'create_persona'));
        add_action('wp_ajax_gcc_update_persona', array($this, 'update_persona'));
        add_action('wp_ajax_gcc_delete_persona', array($this, 'delete_persona'));
        add_action('wp_ajax_gcc_toggle_persona_active', array($this, 'toggle_persona_active'));
        add_action('wp_ajax_gcc_upload_persona_image', array($this, 'upload_persona_image'));
        
        // Question management AJAX handlers
        add_action('wp_ajax_gcc_get_questions', array($this, 'get_questions'));
        add_action('wp_ajax_gcc_get_question', array($this, 'get_question'));
        add_action('wp_ajax_gcc_create_question', array($this, 'create_question'));
        add_action('wp_ajax_gcc_update_question', array($this, 'update_question'));
        add_action('wp_ajax_gcc_delete_question', array($this, 'delete_question'));
        add_action('wp_ajax_gcc_toggle_question_active', array($this, 'toggle_question_active'));
        add_action('wp_ajax_gcc_update_question_order', array($this, 'update_question_order'));
        add_action('wp_ajax_gcc_refresh_default_questions', array($this, 'refresh_default_questions'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Gold Calculator Chatbot',
            'Gold Calculator',
            'manage_options',
            'gcc-dashboard',
            array($this, 'dashboard_page'),
            'dashicons-chart-line',
            30
        );
        
        add_submenu_page(
            'gcc-dashboard',
            'Products',
            'Products',
            'manage_options',
            'gcc-products',
            array($this, 'products_page')
        );
        
        add_submenu_page(
            'gcc-dashboard',
            'Submits',
            'Submits',
            'manage_options',
            'gcc-submits',
            array($this, 'submits_page')
        );
        
        add_submenu_page(
            'gcc-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'gcc-settings',
            array($this, 'settings_page')
        );
    }
    
    public function register_settings() {
        register_setting('gcc_settings', 'gcc_exchange_rate');
        register_setting('gcc_settings', 'gcc_exchange_rate_display');
        register_setting('gcc_settings', 'gcc_bot_personas');
        register_setting('gcc_settings', 'gcc_current_persona');
        register_setting('gcc_settings', 'gcc_trader_info');
        register_setting('gcc_settings', 'gcc_email_template');
        register_setting('gcc_settings', 'gcc_email_method');
        register_setting('gcc_settings', 'gcc_sendgrid_api_key');
        register_setting('gcc_settings', 'gcc_sendgrid_sender_email');
        register_setting('gcc_settings', 'gcc_mailtrap_username');
        register_setting('gcc_settings', 'gcc_mailtrap_password');
        register_setting('gcc_settings', 'gcc_api_url');
        register_setting('gcc_settings', 'gcc_api_key');
        register_setting('gcc_settings', 'gcc_api_update_interval');
        register_setting('gcc_settings', 'gcc_high_budget_threshold');
        register_setting('gcc_settings', 'gcc_calendly_url');
        register_setting('gcc_settings', 'gcc_budget_buckets');
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'gcc-') !== false) {
            wp_enqueue_script('gcc-admin-js', GCC_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), '1.0.1', true);
            wp_enqueue_style('gcc-admin-css', GCC_PLUGIN_URL . 'assets/css/admin.css', array(), '1.0.1');
            
            wp_localize_script('gcc-admin-js', 'gcc_admin_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('gcc_admin_nonce')
            ));
        }
    }
    
    public function dashboard_page() {
        global $wpdb;
        
        // Get statistics
        $table_submits = $wpdb->prefix . 'gcc_submits';
        $table_products = $wpdb->prefix . 'gcc_products';
        
        $total_submits = $wpdb->get_var("SELECT COUNT(*) FROM $table_submits");
        $recent_submits_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_submits WHERE created_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $total_products = $wpdb->get_var("SELECT COUNT(*) FROM $table_products WHERE status = 'published'");
        $total_products_draft = $wpdb->get_var("SELECT COUNT(*) FROM $table_products WHERE status = 'draft'");
        
        // Get recent submits
        $recent_submits = $wpdb->get_results("SELECT * FROM $table_submits ORDER BY created_date DESC LIMIT 10");
        
        // Get API status
        $api_status = array(
            'url' => get_option('gcc_api_url', ''),
            'last_sync' => get_option('gcc_last_api_sync', 0),
            'last_sync_formatted' => 'Never',
            'status' => 'not_configured'
        );
        
        if (class_exists('GCC_API_Parser')) {
            $api_parser = new GCC_API_Parser();
            $api_status = $api_parser->get_api_status();
        }
        
        $dashboard_file = GCC_PLUGIN_PATH . 'admin/dashboard.php';
        if (file_exists($dashboard_file)) {
            include $dashboard_file;
        } else {
            echo '<div class="wrap"><h1>Gold Calculator Dashboard</h1><p>Dashboard template not found.</p></div>';
        }
    }
    
    public function settings_page() {
        // Get database instance
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        // Get current tab
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
        // Handle form submissions
        if (isset($_POST['submit'])) {
            switch ($current_tab) {
                case 'general':
                    $this->save_settings();
                    break;
                case 'chatbot':
                    $this->save_chatbot_settings();
                    break;
                case 'chat_persons':
                    $this->save_persona_settings();
                    break;
                case 'chat_questions':
                    $this->save_question_settings();
                    break;
                case 'email':
                    $this->save_email_settings();
                    break;
                case 'cache':
                    $this->save_cache_settings();
                    break;
            }
        }
        
        // Get data for current tab
        $data = array();
        
        if ($current_tab === 'general') {
            $data = array(
                'exchange_rate' => get_option('gcc_exchange_rate', 117.5),
                'exchange_rate_display' => get_option('gcc_exchange_rate_display', 'EUR/RSD: 117.5'),
                'bot_personas' => get_option('gcc_bot_personas', array('ZLATIJA', 'ZLATA', 'ZLATKA', 'ZLATISLAVA')),
                'current_persona' => get_option('gcc_current_persona', 'ZLATIJA'),
                'trader_info' => get_option('gcc_trader_info', 'Za veće investicije preporučujemo direktan razgovor sa treiderom.'),
                'email_template' => get_option('gcc_email_template', 'Hvala na interesovanju za investiciono zlato. Uskoro ćemo Vam poslati detaljnu ponudu.'),
                'api_url' => get_option('gcc_api_url', ''),
                'api_key' => get_option('gcc_api_key', ''),
                'api_update_interval' => get_option('gcc_api_update_interval', 300),
                'high_budget_threshold' => get_option('gcc_high_budget_threshold', 30000),
                'calendly_url' => get_option('gcc_calendly_url', '')
            );
        } elseif ($current_tab === 'chatbot') {
            $data = array(
                'exchange_rate' => get_option('gcc_exchange_rate', 117.5),
                'exchange_rate_display' => get_option('gcc_exchange_rate_display', 'EUR/RSD: 117.5'),
                'trader_info' => get_option('gcc_trader_info', 'Za veće investicije preporučujemo direktan razgovor sa treiderom.'),
                'email_template' => get_option('gcc_email_template', 'Hvala na interesovanju za investiciono zlato. Uskoro ćemo Vam poslati detaljnu ponudu.'),
                'high_budget_threshold' => get_option('gcc_high_budget_threshold', 30000),
                'calendly_url' => get_option('gcc_calendly_url', '')
            );
        } elseif ($current_tab === 'chat_persons') {
            $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
            $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
            $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
            
            $data = array(
                'personas' => $database->get_personas_paginated($page, $per_page, $search),
                'total_personas' => $database->get_personas_count($search),
                'page' => $page,
                'per_page' => $per_page,
                'search' => $search
            );
        } elseif ($current_tab === 'chat_questions') {
            $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
            
            $data = array(
                'questions' => $database->get_all_questions(),
                'search' => $search
            );
        } elseif ($current_tab === 'email') {
            $data = array(
                'email_method' => get_option('gcc_email_method', 'wp_mail'),
                'sendgrid_api_key' => get_option('gcc_sendgrid_api_key', ''),
                'sendgrid_sender_email' => get_option('gcc_sendgrid_sender_email', ''),
                'mailtrap_username' => get_option('gcc_mailtrap_username', ''),
                'mailtrap_password' => get_option('gcc_mailtrap_password', '')
            );
        } elseif ($current_tab === 'cache') {
            $data = array(
                'last_api_sync' => get_option('gcc_last_api_sync', 'Never'),
                'cached_products' => get_option('gcc_cached_products') ? 'Yes' : 'No',
                'cache_status' => function_exists('wp_cache_flush') ? 'Available' : 'Not Available'
            );
        }
        
        $settings_file = GCC_PLUGIN_PATH . 'admin/settings.php';
        if (file_exists($settings_file)) {
            include $settings_file;
        } else {
            $this->render_settings_fallback($current_tab, $data);
        }
    }
    
    public function products_page() {
        // Get database instance
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        // Handle form submissions
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add_product':
                    $this->create_product();
                    break;
                case 'update_product':
                    $this->update_product();
                    break;
                case 'delete_product':
                    $this->delete_product();
                    break;
            }
        }
        
        // Get pagination parameters
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $order_by = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : 'created_at';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        
        // Get products with pagination
        $products = $database->get_products_paginated($page, $per_page, $search, $order_by, $order);
        $total_products = $database->get_products_count($search);
        
        $products_file = GCC_PLUGIN_PATH . 'admin/products.php';
        if (file_exists($products_file)) {
            include $products_file;
        } else {
            echo '<div class="wrap"><h1>Gold Calculator Products</h1><p>Products template not found.</p></div>';
        }
    }
    
    public function submits_page() {
        // Get database instance
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        // Handle form submissions
        if (isset($_POST['action']) && $_POST['action'] === 'delete_submit') {
            $this->delete_submit();
        }
        
        // Get pagination parameters
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $order_by = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : 'created_date';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        
        // Get submits with pagination
        $submits = $database->get_submits_paginated($page, $per_page, $search, $order_by, $order);
        $total_submits = $database->get_submits_count($search);
        
        $submits_file = GCC_PLUGIN_PATH . 'admin/submits.php';
        if (file_exists($submits_file)) {
            include $submits_file;
        } else {
            echo '<div class="wrap"><h1>Gold Calculator Submits</h1><p>Submits template not found.</p></div>';
        }
    }
    
    private function save_chatbot_settings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['_wpnonce'], 'gcc_chatbot_settings')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $settings = array(
            'gcc_exchange_rate' => floatval($_POST['exchange_rate']),
            'gcc_exchange_rate_display' => sanitize_text_field($_POST['exchange_rate_display']),
            'gcc_trader_info' => sanitize_textarea_field($_POST['trader_info']),
            'gcc_email_template' => wp_kses_post($_POST['email_template']),
            'gcc_high_budget_threshold' => intval($_POST['high_budget_threshold']),
            'gcc_calendly_url' => esc_url_raw($_POST['calendly_url'])
        );
        
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        
        wp_redirect(admin_url('admin.php?page=gcc-settings&tab=chatbot&settings-updated=true'));
        exit;
    }
    
    private function save_persona_settings() {
        // This method is for handling any bulk persona operations from the form
        // Individual persona operations are handled via AJAX
        wp_redirect(admin_url('admin.php?page=gcc-settings&tab=chat_persons'));
        exit;
    }
    
    private function save_question_settings() {
        // This method is for handling any bulk question operations from the form
        // Individual question operations are handled via AJAX
        wp_redirect(admin_url('admin.php?page=gcc-settings&tab=chat_questions'));
        exit;
    }
    
    private function save_email_settings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['_wpnonce'], 'gcc_email_settings')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $settings = array(
            'gcc_email_method' => sanitize_text_field($_POST['email_method']),
            'gcc_sendgrid_api_key' => sanitize_text_field($_POST['sendgrid_api_key']),
            'gcc_sendgrid_sender_email' => sanitize_email($_POST['sendgrid_sender_email']),
            'gcc_mailtrap_username' => sanitize_text_field($_POST['mailtrap_username']),
            'gcc_mailtrap_password' => sanitize_text_field($_POST['mailtrap_password'])
        );
        
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        
        wp_redirect(admin_url('admin.php?page=gcc-settings&tab=email&settings-updated=true'));
        exit;
    }
    
    private function save_cache_settings() {
        // Cache tab doesn't have form settings, just redirect back
        wp_redirect(admin_url('admin.php?page=gcc-settings&tab=cache'));
        exit;
    }
    
    private function render_settings_fallback($current_tab, $data) {
        ?>
        <div class="wrap">
            <h1>Gold Calculator Settings</h1>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo admin_url('admin.php?page=gcc-settings&tab=general'); ?>" 
                   class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    General Settings
                </a>
                <a href="<?php echo admin_url('admin.php?page=gcc-settings&tab=chatbot'); ?>" 
                   class="nav-tab <?php echo $current_tab === 'chatbot' ? 'nav-tab-active' : ''; ?>">
                    Chatbot
                </a>
                <a href="<?php echo admin_url('admin.php?page=gcc-settings&tab=chat_persons'); ?>" 
                   class="nav-tab <?php echo $current_tab === 'chat_persons' ? 'nav-tab-active' : ''; ?>">
                    Chat Persons
                </a>
                <a href="<?php echo admin_url('admin.php?page=gcc-settings&tab=email'); ?>" 
                   class="nav-tab <?php echo $current_tab === 'email' ? 'nav-tab-active' : ''; ?>">
                    Email
                </a>
                <a href="<?php echo admin_url('admin.php?page=gcc-settings&tab=cache'); ?>" 
                   class="nav-tab <?php echo $current_tab === 'cache' ? 'nav-tab-active' : ''; ?>">
                    Cache
                </a>
            </h2>
            
            <div class="tab-content">
                <p>Settings template not found. Please create admin/settings.php</p>
            </div>
        </div>
        <?php
    }
    
    
    public function save_settings() {
        // Handle regular form submission
        if (isset($_POST['submit']) && !wp_doing_ajax()) {
            // Verify nonce for regular form submission
            if (!wp_verify_nonce($_POST['_wpnonce'], 'gcc_settings')) {
                wp_die('Security check failed');
            }
            
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
            $this->process_settings_save();
            wp_redirect(admin_url('admin.php?page=gcc-settings&settings-updated=true'));
            exit;
        }
        
        // Handle AJAX submission
        if (wp_doing_ajax()) {
            check_ajax_referer('gcc_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_send_json_error(array('message' => 'Insufficient permissions'));
            }
            
            $this->process_settings_save();
            wp_send_json_success(array('message' => 'Settings saved successfully'));
        }
    }
    
    private function process_settings_save() {
        // Handle personas array
        $personas = array();
        if (isset($_POST['bot_personas']) && is_array($_POST['bot_personas'])) {
            $personas = array_map('sanitize_text_field', $_POST['bot_personas']);
        } elseif (isset($_POST['bot_personas']) && is_string($_POST['bot_personas'])) {
            $personas = array_map('trim', explode("\n", $_POST['bot_personas']));
            $personas = array_filter(array_map('sanitize_text_field', $personas));
        }
        
        if (empty($personas)) {
            $personas = array('ZLATIJA', 'ZLATA', 'ZLATKA', 'ZLATISLAVA');
        }
        
        $settings = array(
            'gcc_exchange_rate' => floatval($_POST['exchange_rate']),
            'gcc_exchange_rate_display' => sanitize_text_field($_POST['exchange_rate_display']),
            'gcc_bot_personas' => $personas,
            'gcc_current_persona' => sanitize_text_field($_POST['current_persona']),
            'gcc_trader_info' => sanitize_textarea_field($_POST['trader_info']),
            'gcc_email_template' => wp_kses_post($_POST['email_template']),
            'gcc_email_method' => sanitize_text_field($_POST['email_method'] ?? 'wp_mail'),
            'gcc_sendgrid_api_key' => sanitize_text_field($_POST['sendgrid_api_key'] ?? ''),
            'gcc_sendgrid_sender_email' => sanitize_email($_POST['sendgrid_sender_email'] ?? ''),
            'gcc_mailtrap_username' => sanitize_text_field($_POST['mailtrap_username'] ?? ''),
            'gcc_mailtrap_password' => sanitize_text_field($_POST['mailtrap_password'] ?? ''),
            'gcc_api_url' => esc_url_raw($_POST['api_url']),
            'gcc_api_key' => sanitize_text_field($_POST['api_key']),
            'gcc_api_update_interval' => intval($_POST['api_update_interval']),
            'gcc_high_budget_threshold' => intval($_POST['high_budget_threshold']),
            'gcc_calendly_url' => esc_url_raw($_POST['calendly_url'])
        );
        
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
    }
    
    public function create_product() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $product_id = $database->create_product($_POST);
        
        if ($product_id) {
            wp_send_json_success(array('message' => 'Product created successfully', 'product_id' => $product_id));
        } else {
            wp_send_json_error(array('message' => 'Failed to create product'));
        }
    }
    
    public function update_product() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $product_id = intval($_POST['product_id']);
        $result = $database->update_product($product_id, $_POST);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Product updated successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update product'));
        }
    }
    
    public function delete_product() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $product_id = intval($_POST['product_id']);
        $result = $database->delete_product($product_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Product deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete product'));
        }
    }
    
    public function get_product() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $product_id = intval($_POST['product_id']);
        $product = $database->get_product($product_id);
        
        if ($product) {
            wp_send_json_success($product);
        } else {
            wp_send_json_error(array('message' => 'Product not found'));
        }
    }
    
    public function delete_submit() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $submit_id = intval($_POST['submit_id']);
        $result = $database->delete_submit($submit_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Submit deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete submit'));
        }
    }
    
    public function get_submits() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $submits = $database->get_all_submits();
        
        wp_send_json_success(array('submits' => $submits));
    }
    
    public function clear_cache() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        // Clear WordPress object cache
        wp_cache_flush();
        
        // Clear transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_gcc_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_gcc_%'");
        
        // Clear opcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        // Clear plugin-specific cached data
        delete_option('gcc_cached_products');
        delete_option('gcc_cached_stats');
        
        wp_send_json_success(array('message' => 'Cache cleared successfully'));
    }
    
    
    // === PERSONA MANAGEMENT METHODS ===
    
    public function get_personas() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $personas = $database->get_all_personas();
        
        wp_send_json_success(array('personas' => $personas));
    }
    
    public function get_persona() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $persona_id = intval($_POST['persona_id']);
        $persona = $database->get_persona($persona_id);
        
        if ($persona) {
            wp_send_json_success($persona);
        } else {
            wp_send_json_error(array('message' => 'Persona not found'));
        }
    }
    
    public function create_persona() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $persona_id = $database->create_persona($_POST);
        
        if ($persona_id) {
            wp_send_json_success(array('message' => 'Persona created successfully', 'persona_id' => $persona_id));
        } else {
            wp_send_json_error(array('message' => 'Failed to create persona'));
        }
    }
    
    public function update_persona() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $persona_id = intval($_POST['persona_id']);
        $result = $database->update_persona($persona_id, $_POST);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Persona updated successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update persona'));
        }
    }
    
    public function delete_persona() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $persona_id = intval($_POST['persona_id']);
        $result = $database->delete_persona($persona_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Persona deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete persona'));
        }
    }
    
    public function toggle_persona_active() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $persona_id = intval($_POST['persona_id']);
        $result = $database->toggle_persona_active($persona_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Persona status toggled successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to toggle persona status'));
        }
    }
    
    public function upload_persona_image() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $uploadedfile = $_FILES['persona_image'];
        $upload_overrides = array('test_form' => false);
        
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            wp_send_json_success(array(
                'message' => 'Image uploaded successfully',
                'image_url' => $movefile['url']
            ));
        } else {
            wp_send_json_error(array('message' => 'Upload failed: ' . $movefile['error']));
        }
    }
    
    // === QUESTION MANAGEMENT METHODS ===
    
    public function get_questions() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $questions = $database->get_all_questions();
        
        wp_send_json_success(array('questions' => $questions));
    }
    
    public function get_question() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $question_id = intval($_POST['question_id']);
        $question = $database->get_question($question_id);
        
        if ($question) {
            wp_send_json_success($question);
        } else {
            wp_send_json_error(array('message' => 'Question not found'));
        }
    }
    
    public function create_question() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        // Parse options and attributes from POST data
        $data = array(
            'question' => sanitize_textarea_field($_POST['question']),
            'options' => json_decode(stripslashes($_POST['options']), true),
            'attributes' => json_decode(stripslashes($_POST['attributes']), true),
            'question_order' => intval($_POST['question_order']),
            'active' => intval($_POST['active']),
            'condition_logic' => stripslashes($_POST['condition_logic'])
        );
        
        $question_id = $database->create_question($data);
        
        if ($question_id) {
            wp_send_json_success(array('message' => 'Question created successfully', 'question_id' => $question_id));
        } else {
            wp_send_json_error(array('message' => 'Failed to create question'));
        }
    }
    
    public function update_question() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $question_id = intval($_POST['question_id']);
        
        // Parse options and attributes from POST data
        $data = array(
            'question' => sanitize_textarea_field($_POST['question']),
            'options' => json_decode(stripslashes($_POST['options']), true),
            'attributes' => json_decode(stripslashes($_POST['attributes']), true),
            'question_order' => intval($_POST['question_order']),
            'active' => intval($_POST['active']),
            'condition_logic' => stripslashes($_POST['condition_logic'])
        );
        
        $result = $database->update_question($question_id, $data);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Question updated successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update question'));
        }
    }
    
    public function delete_question() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $question_id = intval($_POST['question_id']);
        $result = $database->delete_question($question_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Question deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete question'));
        }
    }
    
    public function toggle_question_active() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $question_id = intval($_POST['question_id']);
        $result = $database->toggle_question_active($question_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Question status toggled successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to toggle question status'));
        }
    }
    
    public function update_question_order() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $question_id = intval($_POST['question_id']);
        $new_order = intval($_POST['new_order']);
        
        $result = $database->update_question_order($question_id, $new_order);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Question order updated successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update question order'));
        }
    }
    
    public function refresh_default_questions() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        
        $result = $database->refresh_default_questions();
        
        if ($result) {
            wp_send_json_success(array('message' => 'Default questions refreshed successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to refresh default questions'));
        }
    }
    
}