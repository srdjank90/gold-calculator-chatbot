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
        
        // First pass: Use greedy approach but be more conservative with quantities
        foreach ($affordable_products as $product) {
            $price = $product->final_price;
            
            if ($price > $remaining_budget_rsd) {
                continue;
            }
            
            // Calculate a reasonable quantity (not max) to leave room for variety
            $max_quantity = floor($remaining_budget_rsd / $price);
            
            if ($max_quantity <= 0) {
                continue;
            }
            
            // Use a conservative quantity to allow for more product variety
            $quantity = min($max_quantity, max(1, floor($max_quantity * 0.6))); // Use 60% of max or at least 1
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
            
            // Only stop if we have very little budget left
            if ($remaining_budget_rsd < ($price * 0.5)) { // If we can't afford even half of this product
                break;
            }
        }
        
        // Second pass: Fill remaining budget with smaller items if possible
        $this->fill_remaining_budget($suggestion, $affordable_products, $remaining_budget_rsd, $exchange_rate, $budget);
        
        // Third pass: Aggressive budget maximization - keep trying until nothing fits
        $this->aggressive_budget_fill($suggestion, $affordable_products, $remaining_budget_rsd, $exchange_rate, $budget);
        
        // Sort by weight ascending (low to high), with products without weight at the end
        usort($suggestion, function($a, $b) {
            $weight_a = $this->parse_weight($a['weight']);
            $weight_b = $this->parse_weight($b['weight']);
            
            // Products without weight (0) go to the end
            if ($weight_a == 0 && $weight_b == 0) {
                return 0; // Both have no weight, maintain order
            }
            if ($weight_a == 0) {
                return 1; // a has no weight, goes after b
            }
            if ($weight_b == 0) {
                return -1; // b has no weight, goes after a
            }
            
            // Both have weights, sort ascending (low to high)
            return $weight_a <=> $weight_b;
        });
        
        return $suggestion;
    }
    
    /**
     * Fill remaining budget with smaller denomination items
     */
    private function fill_remaining_budget(&$suggestion, $products, $remaining_budget, $exchange_rate, $original_budget_eur) {
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
            
            // Stop when we can't afford any more items - use smaller tolerance for larger budgets
            $tolerance_rsd = $original_budget_eur > 3000 ? ($original_budget_eur * $exchange_rate * 0.005) : 50; // 0.5% for >3000 EUR, else 50 RSD
            if ($remaining_budget < $tolerance_rsd) {
                break;
            }
        }
    }
    
    /**
     * Aggressive budget filling - keeps trying to add products until nothing fits
     */
    private function aggressive_budget_fill(&$suggestion, $products, &$remaining_budget, $exchange_rate, $original_budget_eur) {
        $tolerance_rsd = $original_budget_eur > 3000 ? ($original_budget_eur * $exchange_rate * 0.005) : 50;
        
        // Keep trying to add products until we really can't fit anything
        $max_iterations = 50; // Prevent infinite loop
        $iteration = 0;
        
        while ($iteration < $max_iterations && $remaining_budget > $tolerance_rsd) {
            $added_product = false;
            $used_ids = array_column($suggestion, 'id');
            
            // Find all products that can fit in remaining budget
            $available_products = array_filter($products, function($product) use ($remaining_budget) {
                return $product->final_price <= $remaining_budget;
            });
            
            if (empty($available_products)) {
                break; // No products can fit
            }
            
            // Sort by price descending to try to use more budget
            usort($available_products, function($a, $b) {
                return $b->final_price <=> $a->final_price;
            });
            
            // Try to add the most expensive product that fits
            foreach ($available_products as $product) {
                if ($product->final_price <= $remaining_budget) {
                    $price = $product->final_price;
                    
                    // Check if we already have this product, if so, add to existing quantity
                    $existing_key = null;
                    foreach ($suggestion as $key => $existing_product) {
                        if ($existing_product['id'] == $product->id) {
                            $existing_key = $key;
                            break;
                        }
                    }
                    
                    if ($existing_key !== null) {
                        // Add to existing product
                        $suggestion[$existing_key]['quantity']++;
                        $suggestion[$existing_key]['total'] += $price;
                    } else {
                        // Add new product
                        $product_suggestion = array(
                            'id' => $product->id,
                            'name' => $product->name,
                            'weight' => $product->weight,
                            'price' => $price,
                            'quantity' => 1,
                            'total' => $price,
                            'image_url' => $product->image_url,
                            'final_price_eur' => $product->final_price_eur ?? ($price / $exchange_rate)
                        );
                        $suggestion[] = $product_suggestion;
                    }
                    
                    $remaining_budget -= $price;
                    $added_product = true;
                    break; // Added one product, try again
                }
            }
            
            if (!$added_product) {
                break; // Couldn't add anything, stop
            }
            
            $iteration++;
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