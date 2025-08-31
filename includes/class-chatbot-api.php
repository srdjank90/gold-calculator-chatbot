<?php

class GCC_Chatbot_API {
    
    public function __construct() {
        add_action('wp_ajax_gcc_submit_quote', array($this, 'submit_quote'));
        add_action('wp_ajax_nopriv_gcc_submit_quote', array($this, 'submit_quote'));
        
        add_action('wp_ajax_gcc_get_exchange_rate', array($this, 'get_exchange_rate'));
        add_action('wp_ajax_nopriv_gcc_get_exchange_rate', array($this, 'get_exchange_rate'));
        
        add_action('wp_ajax_gcc_get_product_suggestion', array($this, 'get_product_suggestion'));
        add_action('wp_ajax_nopriv_gcc_get_product_suggestion', array($this, 'get_product_suggestion'));
    }
    
    public function submit_quote() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'gcc_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }
        
        // Validate required fields
        $required_fields = array('name', 'email', 'phone', 'budget_range', 'product_type', 'delivery_method');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array('message' => 'Missing required field: ' . $field));
            }
        }
        
        // Sanitize and prepare data
        $quote_data = array(
            'name' => sanitize_text_field($_POST['name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'message' => sanitize_textarea_field($_POST['message']),
            'budget_range' => sanitize_text_field($_POST['budget_range']),
            'product_type' => sanitize_text_field($_POST['product_type']),
            'delivery_method' => sanitize_text_field($_POST['delivery_method']),
            'weight_preference' => sanitize_text_field($_POST['weight_preference']),
            'selected_products' => json_decode(stripslashes($_POST['selected_products']), true),
            'total_value' => floatval($_POST['total_value']),
            'currency' => sanitize_text_field($_POST['currency']),
            'quote_type' => sanitize_text_field($_POST['quote_type'])
        );
        
        // Save to database
        $database = new GCC_Database();
        $ticket_number = $database->save_quote($quote_data);
        
        if ($ticket_number) {
            // Send emails
            $email_handler = new GCC_Email_Handler();
            $email_sent = $email_handler->send_quote_emails($quote_data, $ticket_number);
            
            if ($email_sent) {
                wp_send_json_success(array(
                    'message' => 'Quote submitted successfully',
                    'ticket_number' => $ticket_number
                ));
            } else {
                wp_send_json_error(array('message' => 'Quote saved but email failed to send'));
            }
        } else {
            wp_send_json_error(array('message' => 'Failed to save quote'));
        }
    }
    
    public function get_exchange_rate() {
        check_ajax_referer('gcc_nonce', 'nonce');
        
        $exchange_rate = get_option('gcc_exchange_rate', 117.5);
        $display_text = get_option('gcc_exchange_rate_display', 'EUR/RSD: 117.5');
        
        wp_send_json_success(array(
            'rate' => $exchange_rate,
            'display' => $display_text
        ));
    }
    
    public function get_product_suggestion() {
        check_ajax_referer('gcc_nonce', 'nonce');
        
        $budget = floatval($_POST['budget']);
        $type = sanitize_text_field($_POST['type']);
        $delivery_method = sanitize_text_field($_POST['delivery_method']);
        $weight_preference = sanitize_text_field($_POST['weight_preference']);
        
        $database = new GCC_Database();
        $products = $database->get_products_for_budget($budget, $type, $delivery_method);
        
        // Apply weight preference logic
        if ($weight_preference && !empty($products)) {
            $products = $this->apply_weight_preference($products, $weight_preference, $budget);
        }
        
        // Calculate optimal combination
        $suggestion = $this->calculate_optimal_combination($products, $budget);
        
        wp_send_json_success($suggestion);
    }
    
    private function apply_weight_preference($products, $preference, $budget) {
        if ($preference === 'lighter') {
            // Sort by weight ascending (lighter first)
            usort($products, function($a, $b) {
                return $this->parse_weight($a->weight) - $this->parse_weight($b->weight);
            });
        } else {
            // Sort by weight descending (heavier first)
            usort($products, function($a, $b) {
                return $this->parse_weight($b->weight) - $this->parse_weight($a->weight);
            });
        }
        
        return $products;
    }
    
    private function parse_weight($weight_str) {
        // Convert weight strings like "1g", "5g", "10g" to numeric values
        preg_match('/(\d+(?:\.\d+)?)/', $weight_str, $matches);
        return isset($matches[1]) ? floatval($matches[1]) : 0;
    }
    
    private function calculate_optimal_combination($products, $budget) {
        if (empty($products)) {
            return array();
        }
        
        $suggestion = array();
        $remaining_budget = $budget;
        $exchange_rate = get_option('gcc_exchange_rate', 117.5);
        $remaining_budget_rsd = $budget * $exchange_rate;
        
        // Shuffle products to add randomization to offers
        shuffle($products);
        
        // Filter products that can fit in budget
        $affordable_products = array_filter($products, function($product) use ($remaining_budget_rsd) {
            return $product->final_price <= $remaining_budget_rsd;
        });
        
        if (empty($affordable_products)) {
            return array();
        }
        
        // Strategy: Maximize budget utilization by using greedy algorithm
        // Sort products by value density (descending) - most value per RSD first
        usort($affordable_products, function($a, $b) {
            $value_density_a = $this->parse_weight($a->weight) / $a->final_price;
            $value_density_b = $this->parse_weight($b->weight) / $b->final_price;
            return $value_density_b <=> $value_density_a;
        });
        
        // First pass: Use greedy approach to maximize budget
        foreach ($affordable_products as $product) {
            $price = $product->final_price;
            
            if ($price > $remaining_budget_rsd) {
                continue;
            }
            
            // Calculate max possible quantity for this product
            $max_quantity = floor($remaining_budget_rsd / $price);
            
            if ($max_quantity <= 0) {
                continue;
            }
            
            // Use maximum quantity to get closest to budget
            $quantity = $max_quantity;
            $product_total = $price * $quantity;
            
            $product_suggestion = array(
                'id' => $product->id,
                'name' => $product->name,
                'weight' => $product->weight,
                'price' => $price,
                'quantity' => $quantity,
                'total' => $product_total,
                'image_url' => $product->image_url,
                'final_price_eur' => $product->final_price_eur ?? ($price / $exchange_rate)
            );
            
            $suggestion[] = $product_suggestion;
            $remaining_budget_rsd -= $product_total;
            
            // Continue until budget is nearly exhausted
            if ($remaining_budget_rsd < 100) { // Stop when less than 100 RSD remains
                break;
            }
        }
        
        // Second pass: Fill remaining budget with smaller items if possible
        $this->fill_remaining_budget($suggestion, $affordable_products, $remaining_budget_rsd, $exchange_rate);
        
        // Sort by price ascending to show cheaper items first
        usort($suggestion, function($a, $b) {
            return $a['price'] - $b['price'];
        });
        
        return $suggestion;
    }
    
    /**
     * Fill remaining budget with smaller denomination items
     */
    private function fill_remaining_budget(&$suggestion, $products, $remaining_budget, $exchange_rate) {
        $used_ids = array_column($suggestion, 'id');
        
        // Find products that can still fit in remaining budget and aren't already used
        $remaining_products = array_filter($products, function($product) use ($used_ids, $remaining_budget) {
            return !in_array($product->id, $used_ids) && $product->final_price <= $remaining_budget;
        });
        
        // Sort by price ascending to start with cheapest items
        usort($remaining_products, function($a, $b) {
            return $a->final_price <=> $b->final_price;
        });
        
        // Add as many small items as possible to maximize budget usage
        foreach ($remaining_products as $product) {
            $price = $product->final_price;
            
            if ($price > $remaining_budget) {
                continue;
            }
            
            $max_quantity = floor($remaining_budget / $price);
            if ($max_quantity <= 0) {
                continue;
            }
            
            $product_total = $price * $max_quantity;
            
            $product_suggestion = array(
                'id' => $product->id,
                'name' => $product->name,
                'weight' => $product->weight,
                'price' => $price,
                'quantity' => $max_quantity,
                'total' => $product_total,
                'image_url' => $product->image_url,
                'final_price_eur' => $product->final_price_eur ?? ($price / $exchange_rate)
            );
            
            $suggestion[] = $product_suggestion;
            $remaining_budget -= $product_total;
            
            // Stop when we can't afford any more items
            if ($remaining_budget < 50) { // Less than 50 RSD remaining
                break;
            }
        }
    }
    
    
    public function get_bot_personas() {
        $personas = get_option('gcc_bot_personas', array('ZLATIJA', 'ZLATA', 'ZLATKA', 'ZLATISLAVA'));
        return $personas;
    }
    
    public function get_current_persona() {
        $current = get_option('gcc_current_persona', 'ZLATIJA');
        return $current;
    }
    
    public function rotate_persona() {
        $personas = $this->get_bot_personas();
        $current = $this->get_current_persona();
        
        $current_index = array_search($current, $personas);
        $next_index = ($current_index + 1) % count($personas);
        $next_persona = $personas[$next_index];
        
        update_option('gcc_current_persona', $next_persona);
        
        return $next_persona;
    }
}