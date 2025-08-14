<?php

class GCC_API_Parser {
    
    private $api_url;
    private $update_interval; // in seconds
    
    public function __construct() {
        $this->api_url = get_option('gcc_api_url', 'https://radoviutoku.com/zs-xml');
        $this->update_interval = get_option('gcc_api_update_interval', 300); // 5 minutes default
        
        // Schedule automatic updates
        add_action('gcc_update_products_from_api', array($this, 'update_products_from_api'));
        
        if (!wp_next_scheduled('gcc_update_products_from_api')) {
            wp_schedule_event(time(), 'gcc_custom_interval', 'gcc_update_products_from_api');
        }
        
        // Add custom cron intervals
        add_filter('cron_schedules', array($this, 'add_custom_cron_intervals'));
        
        // Admin AJAX hooks
        add_action('wp_ajax_gcc_manual_api_update', array($this, 'manual_api_update'));
        add_action('wp_ajax_gcc_test_api_connection', array($this, 'test_api_connection'));
    }
    
    public function add_custom_cron_intervals($schedules) {
        $schedules['gcc_1min'] = array(
            'interval' => 60,
            'display' => 'Every 1 minute'
        );
        $schedules['gcc_5min'] = array(
            'interval' => 300,
            'display' => 'Every 5 minutes'
        );
        $schedules['gcc_10min'] = array(
            'interval' => 600,
            'display' => 'Every 10 minutes'
        );
        $schedules['gcc_30min'] = array(
            'interval' => 1800,
            'display' => 'Every 30 minutes'
        );
        $schedules['gcc_60min'] = array(
            'interval' => 3600,
            'display' => 'Every 60 minutes'
        );
        return $schedules;
    }
    
    public function update_products_from_api() {
        if (empty($this->api_url)) {
            error_log('GCC API Parser: No API URL configured');
            return false;
        }
        
        // Check if this is the first sync and add demo products
        $is_first_sync = get_option('gcc_last_api_sync', 0) == 0;
        if ($is_first_sync) {
            $this->add_demo_products();
        }
        
        $api_data = $this->fetch_api_data();
        
        if ($api_data === false) {
            error_log('GCC API Parser: Failed to fetch API data');
            return false;
        }
        
        $products = $this->parse_api_data($api_data);
        
        if (empty($products)) {
            error_log('GCC API Parser: No products found in API response');
            return false;
        }
        
        $updated_count = $this->update_database_products($products);
        
        // Delete demo products after first successful sync
        if ($is_first_sync && $updated_count > 0) {
            $this->delete_demo_products();
        }
        
        // Update last sync timestamp
        update_option('gcc_last_api_sync', time());
        
        error_log("GCC API Parser: Updated {$updated_count} products from API");
        
        return $updated_count;
    }
    
    private function fetch_api_data() {
        $args = array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'Gold Calculator Chatbot'
            )
        );
        
        // Add API key if configured
        $api_key = get_option('gcc_api_key', '');
        if (!empty($api_key)) {
            $args['headers']['Authorization'] = 'Bearer ' . $api_key;
        }
        
        $response = wp_remote_get($this->api_url, $args);
        
        if (is_wp_error($response)) {
            error_log('GCC API Parser Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $http_code = wp_remote_retrieve_response_code($response);
        
        if ($http_code !== 200) {
            error_log('GCC API Parser: HTTP error ' . $http_code);
            return false;
        }
        
        if (empty($body)) {
            error_log('GCC API Parser: Empty response body');
            return false;
        }
        
        // Parse XML data
        $data = simplexml_load_string($body);
        
        if ($data === false) {
            error_log('GCC API Parser: XML parse error');
            return false;
        }
        
        // Convert SimpleXML to array
        $data = json_decode(json_encode($data), true);
        
        return $data;
    }
    
    private function parse_api_data($api_data) {
        $products = array();
        
        // Handle different XML response formats
        $product_data = $api_data;
        
        // If response has a 'products' key, use that
        if (isset($api_data['products']) && is_array($api_data['products'])) {
            $product_data = $api_data['products'];
        }
        
        // If response has a 'data' key, use that
        if (isset($api_data['data']) && is_array($api_data['data'])) {
            $product_data = $api_data['data'];
        }
        
        // If response has items directly, use that
        if (isset($api_data['item']) && is_array($api_data['item'])) {
            $product_data = $api_data['item'];
        }
        
        // Handle single item wrapped in array
        if (!isset($product_data[0]) && is_array($product_data)) {
            $product_data = array($product_data);
        }
        
        foreach ($product_data as $product_node) {
            $product = array(
                'name' => isset($product_node['name']) ? (string) $product_node['name'] : '',
                'description' => isset($product_node['description']) ? (string) $product_node['description'] : '',
                'article_number' => isset($product_node['article_number']) ? (string) $product_node['article_number'] : '',
                'type' => $this->determine_product_type(isset($product_node['name']) ? (string) $product_node['name'] : ''),
                'weight' => isset($product_node['weight']) ? (string) $product_node['weight'] : '',
                'price_net' => isset($product_node['price_net']) ? (float) $product_node['price_net'] : 0,
                'price_gross' => isset($product_node['price_gross']) ? (float) $product_node['price_gross'] : 0,
                'number_products' => isset($product_node['number_products']) ? (int) $product_node['number_products'] : 0,
                'status' => 'published', // Default to published for imported products
                'featured_image' => isset($product_node['featured_image']) ? (string) $product_node['featured_image'] : '',
                'buying_price' => isset($product_node['buying_price']) ? (float) $product_node['buying_price'] : 0,
                'selling_price' => isset($product_node['selling_price']) ? (float) $product_node['selling_price'] : 0,
                'image_url' => isset($product_node['image_url']) ? (string) $product_node['image_url'] : '',
                'stock_available' => $this->parse_boolean(isset($product_node['stock_available']) ? $product_node['stock_available'] : true),
                'advance_payment_available' => $this->parse_boolean(isset($product_node['advance_payment_available']) ? $product_node['advance_payment_available'] : true),
                'external_id' => isset($product_node['id']) ? (string) $product_node['id'] : '', // For tracking external references
                'price_updated_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
                'stock_markup_percent' => isset($product_node['stock_markup_percent']) ? (float) $product_node['stock_markup_percent'] : 5.0,
                'advance_discount_percent' => isset($product_node['advance_discount_percent']) ? (float) $product_node['advance_discount_percent'] : 3.0,
                'is_active' => 1
            );
            
            // Validate required fields
            if (!empty($product['name']) && !empty($product['weight']) && ($product['price_gross'] > 0 || $product['selling_price'] > 0)) {
                $products[] = $product;
            }
        }
        
        return $products;
    }
    
    private function determine_product_type($name) {
        $name_lower = strtolower($name);
        
        if (strpos($name_lower, 'dukat') !== false) {
            return 'ducat';
        } elseif (strpos($name_lower, 'poluga') !== false || strpos($name_lower, 'bar') !== false) {
            return 'bar';
        }
        
        // Default to bar if can't determine
        return 'bar';
    }
    
    private function parse_boolean($value) {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        
        $value = strtolower(trim($value));
        return in_array($value, array('true', '1', 'yes', 'da')) ? 1 : 0;
    }
    
    private function update_database_products($products) {
        global $wpdb;
        
        $table_products = $wpdb->prefix . 'gcc_products';
        $updated_count = 0;
        
        foreach ($products as $product) {
            // Check if product exists by external_id or name
            $existing_product = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM $table_products WHERE external_id = %s OR (name = %s AND weight = %s)",
                $product['external_id'],
                $product['name'],
                $product['weight']
            ));
            
            if ($existing_product) {
                // Update existing product
                $result = $wpdb->update(
                    $table_products,
                    $product,
                    array('id' => $existing_product->id)
                );
            } else {
                // Insert new product
                $result = $wpdb->insert($table_products, $product);
            }
            
            if ($result !== false) {
                $updated_count++;
            }
        }
        
        return $updated_count;
    }
    
    public function manual_api_update() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        $updated_count = $this->update_products_from_api();
        
        if ($updated_count !== false) {
            wp_send_json_success(array(
                'message' => "Successfully updated {$updated_count} products",
                'count' => $updated_count,
                'last_sync' => get_option('gcc_last_api_sync')
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to update products from API'));
        }
    }
    
    public function test_api_connection() {
        check_ajax_referer('gcc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        $api_data = $this->fetch_api_data();
        
        if ($api_data === false) {
            wp_send_json_error(array('message' => 'Failed to connect to API endpoint'));
        }
        
        $products = $this->parse_api_data($api_data);
        
        if (empty($products)) {
            wp_send_json_error(array('message' => 'Connected but no valid products found'));
        }
        
        wp_send_json_success(array(
            'message' => 'Connection successful',
            'product_count' => count($products),
            'sample_products' => array_slice($products, 0, 3) // Show first 3 products as sample
        ));
    }
    
    public function get_api_status() {
        $last_sync = get_option('gcc_last_api_sync', 0);
        $api_url = get_option('gcc_api_url', '');
        
        return array(
            'url' => $api_url,
            'last_sync' => $last_sync,
            'last_sync_formatted' => $last_sync ? date('Y-m-d H:i:s', $last_sync) : 'Never',
            'next_sync' => wp_next_scheduled('gcc_update_products_from_api'),
            'status' => empty($api_url) ? 'not_configured' : ($last_sync > 0 ? 'active' : 'pending')
        );
    }
    
    public function force_sync_now() {
        // Clear scheduled event and run immediately
        wp_clear_scheduled_hook('gcc_update_products_from_api');
        
        $result = $this->update_products_from_api();
        
        // Get current interval setting
        $interval = get_option('gcc_api_update_interval', 300);
        $schedule = $this->get_schedule_name($interval);
        
        // Reschedule with current interval
        wp_schedule_event(time() + $interval, $schedule, 'gcc_update_products_from_api');
        
        return $result;
    }
    
    private function get_schedule_name($interval) {
        switch ($interval) {
            case 60:
                return 'gcc_1min';
            case 300:
                return 'gcc_5min';
            case 600:
                return 'gcc_10min';
            case 1800:
                return 'gcc_30min';
            case 3600:
                return 'gcc_60min';
            default:
                return 'gcc_5min';
        }
    }
    
    public function update_schedule() {
        // Clear existing schedule
        wp_clear_scheduled_hook('gcc_update_products_from_api');
        
        // Get current interval
        $interval = get_option('gcc_api_update_interval', 300);
        $schedule = $this->get_schedule_name($interval);
        
        // Schedule with new interval
        wp_schedule_event(time() + $interval, $schedule, 'gcc_update_products_from_api');
    }
    
    private function add_demo_products() {
        global $wpdb;
        
        $table_products = $wpdb->prefix . 'gcc_products';
        
        // Demo products to add
        $demo_products = array(
            array(
                'name' => 'Austrijski Dukat (DEMO)',
                'type' => 'ducat',
                'weight' => '3.49g',
                'buying_price' => 260.00,
                'selling_price' => 280.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 5.0,
                'advance_discount_percent' => 3.0,
                'external_id' => 'demo_dukat_1',
                'is_active' => 1,
                'is_demo' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array(
                'name' => 'Zlatna Poluga 10g (DEMO)',
                'type' => 'bar',
                'weight' => '10g',
                'buying_price' => 650.00,
                'selling_price' => 680.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 5.0,
                'advance_discount_percent' => 3.0,
                'external_id' => 'demo_bar_10g',
                'is_active' => 1,
                'is_demo' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array(
                'name' => 'Zlatna Poluga 20g (DEMO)',
                'type' => 'bar',
                'weight' => '20g',
                'buying_price' => 1300.00,
                'selling_price' => 1360.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 5.0,
                'advance_discount_percent' => 3.0,
                'external_id' => 'demo_bar_20g',
                'is_active' => 1,
                'is_demo' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array(
                'name' => 'Zlatna Poluga 50g (DEMO)',
                'type' => 'bar',
                'weight' => '50g',
                'buying_price' => 3250.00,
                'selling_price' => 3400.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 5.0,
                'advance_discount_percent' => 3.0,
                'external_id' => 'demo_bar_50g',
                'is_active' => 1,
                'is_demo' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            )
        );
        
        $added_count = 0;
        foreach ($demo_products as $product) {
            // Check if demo product already exists
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_products WHERE external_id = %s",
                $product['external_id']
            ));
            
            if (!$exists) {
                $result = $wpdb->insert($table_products, $product);
                if ($result) {
                    $added_count++;
                }
            }
        }
        
        if ($added_count > 0) {
            error_log("GCC API Parser: Added {$added_count} demo products");
        }
        
        return $added_count;
    }
    
    private function delete_demo_products() {
        global $wpdb;
        
        $table_products = $wpdb->prefix . 'gcc_products';
        
        $result = $wpdb->delete($table_products, array('is_demo' => 1));
        
        if ($result) {
            error_log("GCC API Parser: Deleted {$result} demo products after first sync");
        }
        
        return $result;
    }
}