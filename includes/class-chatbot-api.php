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
        
        // Strategy: Create diverse combinations instead of maxing out one product
        $max_products_to_include = min(count($affordable_products), 5); // Include up to 5 different products
        $selected_products = array_slice($affordable_products, 0, $max_products_to_include);
        
        // For each selected product, calculate a reasonable quantity (not maximum)
        foreach ($selected_products as $product) {
            $price = $product->final_price;
            
            if ($price > $remaining_budget_rsd) {
                continue;
            }
            
            // Calculate max possible quantity for this product
            $max_quantity = floor($remaining_budget_rsd / $price);
            
            if ($max_quantity <= 0) {
                continue;
            }
            
            // Use strategic quantity allocation instead of maxing out
            $strategic_quantity = $this->calculate_strategic_quantity($max_quantity, $remaining_budget_rsd, $price, count($selected_products));
            
            if ($strategic_quantity > 0) {
                $product_total = $price * $strategic_quantity;
                
                $product_suggestion = array(
                    'id' => $product->id,
                    'name' => $product->name,
                    'weight' => $product->weight,
                    'price' => $price,
                    'quantity' => $strategic_quantity,
                    'total' => $product_total,
                    'image_url' => $product->image_url,
                    'final_price_eur' => $product->final_price_eur ?? ($price / $exchange_rate)
                );
                
                $suggestion[] = $product_suggestion;
                $remaining_budget_rsd -= $product_total;
            }
            
            // Stop if we've used 85% of budget to leave room for variety
            if ($remaining_budget_rsd < ($budget * $exchange_rate * 0.15)) {
                break;
            }
        }
        
        // If we still have significant budget left, try to add one more expensive item
        if ($remaining_budget_rsd > ($budget * $exchange_rate * 0.2) && count($suggestion) > 0) {
            $this->try_add_bonus_item($suggestion, $affordable_products, $remaining_budget_rsd);
        }
        
        // Sort by price ascending to show cheaper items first
        usort($suggestion, function($a, $b) {
            return $a['price'] - $b['price'];
        });
        
        return $suggestion;
    }
    
    /**
     * Calculate strategic quantity instead of maximum quantity to create variety
     */
    private function calculate_strategic_quantity($max_quantity, $remaining_budget, $item_price, $total_products) {
        // For expensive items (> 30% of budget), limit to 1-2 pieces
        if ($item_price > ($remaining_budget * 0.3)) {
            return min($max_quantity, rand(1, 2));
        }
        
        // For medium items (10-30% of budget), limit to 2-4 pieces
        if ($item_price > ($remaining_budget * 0.1)) {
            return min($max_quantity, rand(2, 4));
        }
        
        // For cheaper items, allow more but still limit for variety
        if ($total_products > 3) {
            // If we have many products, limit each to create variety
            return min($max_quantity, rand(3, 8));
        } else {
            // If we have few products, allow more of each
            return min($max_quantity, rand(5, 12));
        }
    }
    
    /**
     * Try to add one more item with remaining budget
     */
    private function try_add_bonus_item(&$suggestion, $products, $remaining_budget) {
        // Get products not already in suggestion
        $used_ids = array_column($suggestion, 'id');
        $available_products = array_filter($products, function($product) use ($used_ids, $remaining_budget) {
            return !in_array($product->id, $used_ids) && $product->final_price <= $remaining_budget;
        });
        
        if (!empty($available_products)) {
            // Pick a random available product
            $bonus_product = $available_products[array_rand($available_products)];
            $quantity = floor($remaining_budget / $bonus_product->final_price);
            
            if ($quantity > 0) {
                $exchange_rate = get_option('gcc_exchange_rate', 117.5);
                $suggestion[] = array(
                    'id' => $bonus_product->id,
                    'name' => $bonus_product->name,
                    'weight' => $bonus_product->weight,
                    'price' => $bonus_product->final_price,
                    'quantity' => min($quantity, 3), // Limit bonus items
                    'total' => $bonus_product->final_price * min($quantity, 3),
                    'image_url' => $bonus_product->image_url,
                    'final_price_eur' => $bonus_product->final_price_eur ?? ($bonus_product->final_price / $exchange_rate)
                );
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