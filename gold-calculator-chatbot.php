<?php

/**
 * Plugin Name: Gold Calculator Chatbot
 * Description: A chatbot assistant for gold investment purposes
 * Version: 1.0.1
 * Author: Srdjan Kordic
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Main plugin class
class GoldCalculatorChatbot
{

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    public function init()
    {
        // Define constants after WordPress is loaded
        if (!defined('GCC_PLUGIN_URL')) {
            define('GCC_PLUGIN_URL', plugin_dir_url(__FILE__));
        }
        if (!defined('GCC_PLUGIN_PATH')) {
            define('GCC_PLUGIN_PATH', plugin_dir_path(__FILE__));
        }

        // Initialize plugin components
        $this->load_dependencies();
        $this->setup_hooks();
    }

    private function load_dependencies()
    {
        // Define required files
        $required_files = array(
            'class-database.php',
            'class-chatbot-api.php',
            'class-admin.php',
            'class-email-handler.php',
            'class-api-parser.php',
            'class-shortcodes.php'
        );

        // Load dependencies safely
        foreach ($required_files as $file) {
            $file_path = GCC_PLUGIN_PATH . 'includes/' . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log("Gold Calculator Chatbot: Required file missing - " . $file);
            }
        }

        // Initialize components safely
        try {
            if (class_exists('GCC_Database')) {
                new GCC_Database();
            }

            if (class_exists('GCC_Chatbot_API')) {
                new GCC_Chatbot_API();
            }

            if (class_exists('GCC_Admin')) {
                new GCC_Admin();
            }

            if (class_exists('GCC_Email_Handler')) {
                new GCC_Email_Handler();
            }

            if (class_exists('GCC_API_Parser')) {
                new GCC_API_Parser();
            }

            if (class_exists('GCC_Shortcodes')) {
                new GCC_Shortcodes();
            }
        } catch (Exception $e) {
            error_log("Gold Calculator Chatbot initialization error: " . $e->getMessage());
        }
    }

    private function setup_hooks()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'add_chatbot_modal'));
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script('gcc-chatbot-js', GCC_PLUGIN_URL . 'assets/js/chatbot.js', array('jquery'), '2.3.8', true);
        wp_enqueue_style('gcc-chatbot-css', GCC_PLUGIN_URL . 'assets/css/chatbot.css', array(), '2.3.8');
        
        // Add custom chatbot styles
        $this->add_custom_chatbot_styles();

        // Get persona data
        $persona_data = $this->get_random_persona();

        // Localize script for AJAX
        wp_localize_script('gcc-chatbot-js', 'gcc_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gcc_nonce'),
            'persona' => $persona_data['name'],
            'persona_greeting' => $persona_data['greeting_message'],
            'persona_image' => $persona_data['image_url'],
            'user_avatar_image' => get_option('gcc_user_avatar_image', ''),
            'budget_options' => $this->get_budget_options()
        ));
    }

    public function add_custom_chatbot_styles() {
        // Get all appearance settings
        $font_family = get_option('gcc_chatbot_font_family', 'inherit');
        $header_font_family = get_option('gcc_chat_header_font_family', 'inherit');
        $container_bg = get_option('gcc_chat_container_bg_color', '#ffffff');
        $header_bg = get_option('gcc_chat_header_bg_color', '#3c2415');
        $header_text = get_option('gcc_chat_header_text_color', '#fdf7e7');
        $ai_avatar_bg = get_option('gcc_ai_avatar_bg_color', '#3b82f6');
        $ai_avatar_text = get_option('gcc_ai_avatar_text_color', '#ffffff');
        $ai_bubble_bg = get_option('gcc_ai_bubble_bg_color', '#fdf7e7');
        $ai_bubble_text = get_option('gcc_ai_bubble_text_color', '#3c2415');
        $ai_time_text = get_option('gcc_ai_time_text_color', '#6b7280');
        $user_avatar_bg = get_option('gcc_user_avatar_bg_color', '#10b981');
        $user_avatar_text = get_option('gcc_user_avatar_text_color', '#ffffff');
        $user_bubble_bg = get_option('gcc_user_bubble_bg_color', '#3b82f6');
        $user_bubble_text = get_option('gcc_user_bubble_text_color', '#ffffff');
        $user_time_text = get_option('gcc_user_time_text_color', '#6b7280');

        // Generate custom CSS
        $custom_css = "
        /* Gold Calculator Chatbot Custom Styles */
        .chat-container {
            background-color: {$container_bg} !important;
        }
        
        .chat-container * {
            font-family: {$font_family} !important;
        }
        
        .chat-header {
            background: {$header_bg} !important;
            color: {$header_text} !important;
        }
        
        .chat-header h1,
        .chat-header p {
            color: {$header_text} !important;
        }
        
        .chat-header h1 {
            font-family: {$header_font_family} !important;
        }
        
        .header-avatar .persona-fallback {
            background: {$ai_avatar_bg} !important;
            color: {$ai_avatar_text} !important;
        }
        
        .ai-message .avatar {
            background: {$ai_avatar_bg} !important;
            color: {$ai_avatar_text} !important;
        }
        
        .persona-fallback {
            background: {$ai_avatar_bg} !important;
            color: {$ai_avatar_text} !important;
        }
        
        .ai-bubble {
            background: {$ai_bubble_bg} !important;
            color: {$ai_bubble_text} !important;
        }
        
        .gcc-inline-option-btn {
            background: {$ai_bubble_bg} !important;
            color: {$ai_bubble_text} !important;
            border: 1px solid {$ai_bubble_text} !important;
        }
        
        .gcc-inline-option-btn:hover {
            background: {$ai_bubble_text} !important;
            color: {$ai_bubble_bg} !important;
        }
        
        .ai-message .message-time {
            color: {$ai_time_text} !important;
        }
        
        .user-message .avatar {
            background: {$user_avatar_bg} !important;
            color: {$user_avatar_text} !important;
        }
        
        .user-icon {
            color: {$user_avatar_text} !important;
        }
        
        .user-bubble {
            background: {$user_bubble_bg} !important;
            color: {$user_bubble_text} !important;
        }
        
        .user-message .message-time {
            color: {$user_time_text} !important;
        }
        ";

        // Add inline styles
        wp_add_inline_style('gcc-chatbot-css', $custom_css);
    }

    public function add_chatbot_modal()
    {
        // Add modal for button shortcodes
        echo '<div id="gcc-chatbot-modal" class="gcc-modal" style="display: none;">
            <div class="gcc-modal-overlay" onclick="gccCloseChatbotModal()"></div>
            <div class="gcc-modal-content">
                <div class="gcc-modal-header">
                    <h3>Konsultacija o zlatu</h3>
                    <button class="gcc-modal-close" onclick="gccCloseChatbotModal()">&times;</button>
                </div>
                <div id="gcc-modal-chatbot-container"></div>
            </div>
        </div>';

        // Add modal styles
        echo '<style>
        .gcc-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.5);
        }
        .gcc-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .gcc-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 12px;
            width: 90%;
            max-width: 700px;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .gcc-modal-header {
            padding: 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #f4d03f 0%, #f1c40f 100%);
        }
        .gcc-modal-header h3 {
            margin: 0;
            color: #2c3e50;
        }
        .gcc-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #2c3e50;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.3s ease;
        }
        .gcc-modal-close:hover {
            background: rgba(0, 0, 0, 0.1);
        }
        #gcc-modal-chatbot-container {
            height: 500px;
            overflow-y: auto;
        }
        </style>';

        // Add modal JavaScript
        echo '<script>
        function gccOpenChatbotModal(persona) {
            persona = persona || "ZLATIJA";
            const modal = document.getElementById("gcc-chatbot-modal");
            const container = document.getElementById("gcc-modal-chatbot-container");
            
            // Load chatbot content
            container.innerHTML = `
                <div class="gcc-chatbot-wrapper">
                    <div class="gcc-chatbot-messages">
                        <div class="gcc-message gcc-bot-message">
                            <div class="gcc-message-content">
                                <p>Zdravo! Ja sam <strong>${persona}</strong> – vaš vodič kroz svet investicionog zlata. Hajde da pronađemo najbolji paket zlata za vaš budžet! 💰</p>
                            </div>
                            <div class="gcc-message-time">${new Date().toLocaleTimeString("sr-RS", {hour: "2-digit", minute: "2-digit"})}</div>
                        </div>
                    </div>
                    <div class="gcc-chatbot-input-area">
                        <div class="gcc-step-container"></div>
                    </div>
                </div>
            `;
            
            modal.style.display = "block";
            
            // Initialize chatbot if function exists
            if (typeof initChatbotInModal === "function") {
                initChatbotInModal();
            }
        }
        
        function gccCloseChatbotModal() {
            document.getElementById("gcc-chatbot-modal").style.display = "none";
        }
        
        function gccOpenFullChatbot(persona) {
            persona = persona || "ZLATIJA";
            
            // Check if there\'s a chatbot on the page
            const existingChatbot = document.querySelector(".gcc-chatbot-wrapper");
            if (existingChatbot) {
                existingChatbot.scrollIntoView({ behavior: "smooth" });
                return;
            }
            
            // Try to open in modal
            gccOpenChatbotModal(persona);
        }
        
        // Close modal on escape key
        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape") {
                gccCloseChatbotModal();
            }
        });
        </script>';
    }

    private function get_random_persona()
    {
        // Get database instance
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();

        // Get random active persona from database
        $persona = $database->get_random_active_persona();

        // Fallback to default if no active personas found
        if (!$persona) {
            return array(
                'name' => 'ZLATIJA',
                'greeting_message' => 'Zdravo! Ja sam ZLATIJA – vaš vodič kroz svet investicionog zlata. Hajde da pronađemo najbolji paket zlata za vaš budžet! 💰',
                'image_url' => ''
            );
        }

        return array(
            'name' => $persona->name,
            'greeting_message' => $persona->greeting_message,
            'image_url' => $persona->image_url
        );
    }

    private function get_budget_options()
    {
        return get_option('gcc_budget_buckets', array(
            array('value' => 1000, 'text' => '€1,000', 'level' => '1g'),
            array('value' => 2500, 'text' => '€2,500', 'level' => '2g+'),
            array('value' => 5000, 'text' => '€5,000', 'level' => '5g+'),
            array('value' => 10000, 'text' => '€10,000', 'level' => '10g+'),
            array('value' => 20000, 'text' => '€20,000', 'level' => '20g+'),
            array('value' => 50000, 'text' => '€50,000+', 'level' => '20g+')
        ));
    }

    public function activate()
    {
        try {
            // Define constants for activation
            if (!defined('GCC_PLUGIN_PATH')) {
                define('GCC_PLUGIN_PATH', plugin_dir_path(__FILE__));
            }
            if (!defined('GCC_PLUGIN_URL')) {
                define('GCC_PLUGIN_URL', plugin_dir_url(__FILE__));
            }

            // Load database class if not already loaded
            if (!class_exists('GCC_Database')) {
                $database_file = GCC_PLUGIN_PATH . 'includes/class-database.php';
                if (file_exists($database_file)) {
                    require_once $database_file;
                }
            }

            // Create tables if class exists
            if (class_exists('GCC_Database')) {
                $database = new GCC_Database();
                $database->create_tables();
                $database->add_demo_products_on_activation();
            }

            // Create default settings
            $default_settings = array(
                'exchange_rate' => 117.5,
                'exchange_rate_display' => 'EUR/RSD: 117.5',
                'bot_personas' => array('ZLATIJA', 'ZLATA', 'ZLATKA', 'ZLATISLAVA'),
                'current_persona' => 'ZLATIJA',
                'trader_info' => 'Za veće investicije preporučujemo direktan razgovor sa treiderom.',
                'email_template' => 'Hvala na interesovanju za investiciono zlato. Uskoro ćemo Vam poslati detaljnu ponudu.',
                'api_url' => 'https://radoviutoku.com/zs-xml',
                'api_key' => '',
                'api_update_interval' => 300,
                'high_budget_threshold' => 30000,
                'calendly_url' => '',
                
                // New default appearance settings
                'chatbot_font_family' => 'inherit',
                'chat_header_font_family' => 'inherit',
                'chat_header_bg_color' => '#3c2415',
                'chat_header_text_color' => '#fdf7e7',
                'chat_container_bg_color' => '#ffffff',
                'ai_avatar_bg_color' => '#3b82f6',
                'ai_avatar_text_color' => '#ffffff',
                'ai_bubble_bg_color' => '#fdf7e7',
                'ai_bubble_text_color' => '#3c2415',
                'ai_time_text_color' => '#6b7280',
                'user_avatar_bg_color' => '#10b981',
                'user_avatar_text_color' => '#ffffff',
                'user_bubble_bg_color' => '#3b82f6',
                'user_bubble_text_color' => '#ffffff',
                'user_time_text_color' => '#6b7280',
                'user_avatar_image' => ''
            );

            foreach ($default_settings as $key => $value) {
                update_option('gcc_' . $key, $value);
            }

            // Flush rewrite rules
            flush_rewrite_rules();
        } catch (Exception $e) {
            error_log("Gold Calculator Chatbot activation error: " . $e->getMessage());
            // Don't fail activation, just log the error
        }
    }

    public function deactivate()
    {
        // Clean up if needed
        flush_rewrite_rules();
    }
}

// Initialize the plugin
new GoldCalculatorChatbot();
