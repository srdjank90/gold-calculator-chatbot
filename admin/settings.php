<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Gold Calculator Settings</h1>
    
    <?php if (isset($_GET['settings-updated'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>Settings saved successfully!</p>
        </div>
    <?php endif; ?>
    
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=gcc-settings&tab=general'); ?>" 
           class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>">
            General
        </a>
        <a href="<?php echo admin_url('admin.php?page=gcc-settings&tab=chatbot'); ?>" 
           class="nav-tab <?php echo $current_tab === 'chatbot' ? 'nav-tab-active' : ''; ?>">
            Chatbot
        </a>
        <a href="<?php echo admin_url('admin.php?page=gcc-settings&tab=chat_persons'); ?>" 
           class="nav-tab <?php echo $current_tab === 'chat_persons' ? 'nav-tab-active' : ''; ?>">
            Chat Persons
        </a>
        <a href="<?php echo admin_url('admin.php?page=gcc-settings&tab=chat_questions'); ?>" 
           class="nav-tab <?php echo $current_tab === 'chat_questions' ? 'nav-tab-active' : ''; ?>">
            Chat Questions
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
        <?php if ($current_tab === 'general'): ?>
            <div class="general-settings-tab">
                <h3>General Settings</h3>
                <form method="post" action="">
                    <?php wp_nonce_field('gcc_settings'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="exchange_rate">Exchange Rate</label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="exchange_rate" 
                                       name="exchange_rate" 
                                       value="<?php echo esc_attr($data['exchange_rate']); ?>" 
                                       step="0.01" 
                                       class="regular-text" />
                                <p class="description">Current EUR to RSD exchange rate</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="exchange_rate_display">Exchange Rate Display</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="exchange_rate_display" 
                                       name="exchange_rate_display" 
                                       value="<?php echo esc_attr($data['exchange_rate_display']); ?>" 
                                       class="regular-text" />
                                <p class="description">How the exchange rate is displayed to users</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_url">API Endpoint URL</label>
                            </th>
                            <td>
                                <input type="url" 
                                       id="api_url" 
                                       name="api_url" 
                                       value="<?php echo esc_attr($data['api_url']); ?>" 
                                       class="large-text" />
                                <p class="description">URL to your API endpoint containing product data</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_key">API Key</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="api_key" 
                                       name="api_key" 
                                       value="<?php echo esc_attr($data['api_key']); ?>" 
                                       class="large-text" />
                                <p class="description">API key for authentication (optional)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_update_interval">Update Interval</label>
                            </th>
                            <td>
                                <select id="api_update_interval" name="api_update_interval">
                                    <option value="60" <?php selected($data['api_update_interval'], 60); ?>>1 minute</option>
                                    <option value="300" <?php selected($data['api_update_interval'], 300); ?>>5 minutes</option>
                                    <option value="600" <?php selected($data['api_update_interval'], 600); ?>>10 minutes</option>
                                    <option value="1800" <?php selected($data['api_update_interval'], 1800); ?>>30 minutes</option>
                                    <option value="3600" <?php selected($data['api_update_interval'], 3600); ?>>60 minutes</option>
                                </select>
                                <p class="description">How often to sync with the API endpoint</p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
        
        <?php elseif ($current_tab === 'chatbot'): ?>
            <div class="chatbot-settings-tab">
                <h3>Chatbot Settings</h3>
                <form method="post" action="">
                    <?php wp_nonce_field('gcc_chatbot_settings'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="exchange_rate">Exchange Rate</label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="exchange_rate" 
                                       name="exchange_rate" 
                                       value="<?php echo esc_attr($data['exchange_rate']); ?>" 
                                       step="0.01" 
                                       class="regular-text" />
                                <p class="description">Current EUR to RSD exchange rate</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="exchange_rate_display">Exchange Rate Display</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="exchange_rate_display" 
                                       name="exchange_rate_display" 
                                       value="<?php echo esc_attr($data['exchange_rate_display']); ?>" 
                                       class="regular-text" />
                                <p class="description">How the exchange rate is displayed to users</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="trader_info">Trader Info Message</label>
                            </th>
                            <td>
                                <textarea id="trader_info" 
                                          name="trader_info" 
                                          rows="3" 
                                          cols="50" 
                                          class="large-text"><?php echo esc_textarea($data['trader_info']); ?></textarea>
                                <p class="description">Message shown for high-budget inquiries</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="email_template">Email Template</label>
                            </th>
                            <td>
                                <textarea id="email_template" 
                                          name="email_template" 
                                          rows="5" 
                                          cols="50" 
                                          class="large-text"><?php echo esc_textarea($data['email_template']); ?></textarea>
                                <p class="description">Default email template for inquiries</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="high_budget_threshold">High Budget Threshold (EUR)</label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="high_budget_threshold" 
                                       name="high_budget_threshold" 
                                       value="<?php echo esc_attr($data['high_budget_threshold']); ?>" 
                                       class="regular-text" />
                                <p class="description">Budget amount that triggers trader consultation</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="calendly_url">Calendly URL</label>
                            </th>
                            <td>
                                <input type="url" 
                                       id="calendly_url" 
                                       name="calendly_url" 
                                       value="<?php echo esc_attr($data['calendly_url']); ?>" 
                                       class="regular-text" />
                                <p class="description">Calendly link for scheduling meetings</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="user_avatar_image">User Avatar Image</label>
                            </th>
                            <td>
                                <div class="image-upload-container">
                                    <div class="current-image">
                                        <div id="user-avatar-preview" class="image-preview">
                                            <?php if (!empty($data['user_avatar_image'])): ?>
                                                <img src="<?php echo esc_url($data['user_avatar_image']); ?>" alt="User Avatar" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="image-placeholder">
                                                    <span>👤</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="image-upload-controls">
                                        <input type="file" id="user-avatar-upload" accept="image/*" style="display: none;">
                                        <button type="button" class="button" onclick="document.getElementById('user-avatar-upload').click()">
                                            Choose Image
                                        </button>
                                        <button type="button" class="button" onclick="removeUserAvatar()">
                                            Remove
                                        </button>
                                    </div>
                                    <input type="hidden" id="user_avatar_image" name="user_avatar_image" value="<?php echo esc_attr($data['user_avatar_image']); ?>">
                                </div>
                                <p class="description">Upload an avatar image that will be displayed for user messages in the chatbot. If no image is provided, a default user icon will be used.</p>
                            </td>
                        </tr>
                    </table>

                    <h3>Chatbot Appearance Settings</h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="chatbot_font_family">Font Family</label>
                            </th>
                            <td>
                                <select id="chatbot_font_family" name="chatbot_font_family">
                                    <option value="inherit" <?php selected($data['chatbot_font_family'], 'inherit'); ?>>Inherit from theme</option>
                                    <option value="Arial, sans-serif" <?php selected($data['chatbot_font_family'], 'Arial, sans-serif'); ?>>Arial</option>
                                    <option value="'Helvetica Neue', Helvetica, sans-serif" <?php selected($data['chatbot_font_family'], "'Helvetica Neue', Helvetica, sans-serif"); ?>>Helvetica</option>
                                    <option value="Georgia, serif" <?php selected($data['chatbot_font_family'], 'Georgia, serif'); ?>>Georgia</option>
                                    <option value="'Times New Roman', serif" <?php selected($data['chatbot_font_family'], "'Times New Roman', serif"); ?>>Times New Roman</option>
                                    <option value="Verdana, sans-serif" <?php selected($data['chatbot_font_family'], 'Verdana, sans-serif'); ?>>Verdana</option>
                                    <option value="'Comic Sans MS', cursive" <?php selected($data['chatbot_font_family'], "'Comic Sans MS', cursive"); ?>>Comic Sans MS</option>
                                    <option value="'Courier New', monospace" <?php selected($data['chatbot_font_family'], "'Courier New', monospace"); ?>>Courier New</option>
                                    <option value="'Roboto', sans-serif" <?php selected($data['chatbot_font_family'], "'Roboto', sans-serif"); ?>>Roboto</option>
                                    <option value="'Open Sans', sans-serif" <?php selected($data['chatbot_font_family'], "'Open Sans', sans-serif"); ?>>Open Sans</option>
                                </select>
                                <p class="description">Choose the font family for the chatbot interface.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="chat_header_font_family">Header Title Font</label>
                            </th>
                            <td>
                                <select id="chat_header_font_family" name="chat_header_font_family">
                                    <option value="inherit" <?php selected($data['chat_header_font_family'] ?? 'inherit', 'inherit'); ?>>Inherit from chatbot font</option>
                                    <option value="Arial, sans-serif" <?php selected($data['chat_header_font_family'] ?? 'inherit', 'Arial, sans-serif'); ?>>Arial</option>
                                    <option value="'Helvetica Neue', Helvetica, sans-serif" <?php selected($data['chat_header_font_family'] ?? 'inherit', "'Helvetica Neue', Helvetica, sans-serif"); ?>>Helvetica</option>
                                    <option value="Georgia, serif" <?php selected($data['chat_header_font_family'] ?? 'inherit', 'Georgia, serif'); ?>>Georgia</option>
                                    <option value="'Times New Roman', serif" <?php selected($data['chat_header_font_family'] ?? 'inherit', "'Times New Roman', serif"); ?>>Times New Roman</option>
                                    <option value="Verdana, sans-serif" <?php selected($data['chat_header_font_family'] ?? 'inherit', 'Verdana, sans-serif'); ?>>Verdana</option>
                                    <option value="'Comic Sans MS', cursive" <?php selected($data['chat_header_font_family'] ?? 'inherit', "'Comic Sans MS', cursive"); ?>>Comic Sans MS</option>
                                    <option value="'Courier New', monospace" <?php selected($data['chat_header_font_family'] ?? 'inherit', "'Courier New', monospace"); ?>>Courier New</option>
                                    <option value="'Roboto', sans-serif" <?php selected($data['chat_header_font_family'] ?? 'inherit', "'Roboto', sans-serif"); ?>>Roboto</option>
                                    <option value="'Open Sans', sans-serif" <?php selected($data['chat_header_font_family'] ?? 'inherit', "'Open Sans', sans-serif"); ?>>Open Sans</option>
                                </select>
                                <p class="description">Choose a specific font for the header title (H1).</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="chat_container_bg_color">Chat Container Background</label>
                            </th>
                            <td>
                                <input type="color" id="chat_container_bg_color" name="chat_container_bg_color" value="<?php echo esc_attr($data['chat_container_bg_color']); ?>" />
                                <p class="description">Background color of the main chat container.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" colspan="2">
                                <h4>Header Colors</h4>
                            </th>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="chat_header_bg_color">Header Background</label>
                            </th>
                            <td>
                                <input type="color" id="chat_header_bg_color" name="chat_header_bg_color" value="<?php echo esc_attr($data['chat_header_bg_color']); ?>" />
                                <p class="description">Background color of the chat header.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="chat_header_text_color">Header Text</label>
                            </th>
                            <td>
                                <input type="color" id="chat_header_text_color" name="chat_header_text_color" value="<?php echo esc_attr($data['chat_header_text_color']); ?>" />
                                <p class="description">Text color for the chat header.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" colspan="2">
                                <h4>AI Message Colors</h4>
                            </th>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="ai_avatar_bg_color">AI Avatar Background</label>
                            </th>
                            <td>
                                <input type="color" id="ai_avatar_bg_color" name="ai_avatar_bg_color" value="<?php echo esc_attr($data['ai_avatar_bg_color']); ?>" />
                                <p class="description">Background color for AI avatar (when no image is set).</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="ai_avatar_text_color">AI Avatar Text</label>
                            </th>
                            <td>
                                <input type="color" id="ai_avatar_text_color" name="ai_avatar_text_color" value="<?php echo esc_attr($data['ai_avatar_text_color']); ?>" />
                                <p class="description">Text color for AI avatar (when no image is set).</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="ai_bubble_bg_color">AI Message Background</label>
                            </th>
                            <td>
                                <input type="color" id="ai_bubble_bg_color" name="ai_bubble_bg_color" value="<?php echo esc_attr($data['ai_bubble_bg_color']); ?>" />
                                <p class="description">Background color for AI message bubbles.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="ai_bubble_text_color">AI Message Text</label>
                            </th>
                            <td>
                                <input type="color" id="ai_bubble_text_color" name="ai_bubble_text_color" value="<?php echo esc_attr($data['ai_bubble_text_color']); ?>" />
                                <p class="description">Text color for AI messages.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="ai_time_text_color">AI Time Text</label>
                            </th>
                            <td>
                                <input type="color" id="ai_time_text_color" name="ai_time_text_color" value="<?php echo esc_attr($data['ai_time_text_color']); ?>" />
                                <p class="description">Color for AI message timestamps.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" colspan="2">
                                <h4>User Message Colors</h4>
                            </th>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="user_avatar_bg_color">User Avatar Background</label>
                            </th>
                            <td>
                                <input type="color" id="user_avatar_bg_color" name="user_avatar_bg_color" value="<?php echo esc_attr($data['user_avatar_bg_color']); ?>" />
                                <p class="description">Background color for user avatar (when no image is set).</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="user_avatar_text_color">User Avatar Text</label>
                            </th>
                            <td>
                                <input type="color" id="user_avatar_text_color" name="user_avatar_text_color" value="<?php echo esc_attr($data['user_avatar_text_color']); ?>" />
                                <p class="description">Text color for user avatar (when no image is set).</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="user_bubble_bg_color">User Message Background</label>
                            </th>
                            <td>
                                <input type="color" id="user_bubble_bg_color" name="user_bubble_bg_color" value="<?php echo esc_attr($data['user_bubble_bg_color']); ?>" />
                                <p class="description">Background color for user message bubbles.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="user_bubble_text_color">User Message Text</label>
                            </th>
                            <td>
                                <input type="color" id="user_bubble_text_color" name="user_bubble_text_color" value="<?php echo esc_attr($data['user_bubble_text_color']); ?>" />
                                <p class="description">Text color for user messages.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="user_time_text_color">User Time Text</label>
                            </th>
                            <td>
                                <input type="color" id="user_time_text_color" name="user_time_text_color" value="<?php echo esc_attr($data['user_time_text_color']); ?>" />
                                <p class="description">Color for user message timestamps.</p>
                            </td>
                        </tr>
                    </table>

                    <h3>Preview</h3>
                    <div id="chatbot-preview" style="max-width: 400px; margin: 20px 0; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                        <div class="chat-container" id="preview-container" style="height: 400px; box-shadow: none; border-radius: 0;">
                            <div class="chat-header" id="preview-header" style="padding: 15px; border-radius: 0; display: flex; align-items: center; gap: 12px;">
                                <div class="header-avatar" id="preview-header-avatar" style="width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                    <div class="persona-fallback" id="preview-persona-fallback" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; border-radius: 50%;">Z</div>
                                </div>
                                <div class="header-info">
                                    <h1 id="preview-header-text" style="margin: 0; font-size: 16px; font-weight: 600; margin-bottom: 2px;">ZLATIJA</h1>
                                    <p id="preview-header-subtext" style="margin: 0; font-size: 12px; opacity: 0.8;">Your Gold Investment Guide</p>
                                </div>
                            </div>
                            <div class="messages-container" id="preview-messages" style="flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 8px;">
                                <!-- AI Message -->
                                <div class="message-wrapper ai-message" style="display: flex; align-items: flex-start; gap: 12px;">
                                    <div class="avatar" id="preview-ai-avatar" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 16px; overflow: hidden;">
                                        <div class="persona-fallback" id="preview-ai-avatar-text" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; border-radius: 50%;">Z</div>
                                    </div>
                                    <div class="message-content" style="display: flex; flex-direction: column;">
                                        <div class="message-bubble ai-bubble" id="preview-ai-bubble" style="padding: 12px 16px; border-radius: 18px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb;">
                                            <p id="preview-ai-text" style="margin: 0;">🥇 Koliki je vaš budžet za investiciju u zlato?</p>
                                        </div>
                                        <div class="gcc-inline-options" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 8px;">
                                            <button class="gcc-inline-option-btn" style="padding: 6px 12px; border-radius: 12px; font-size: 14px; border: 1px solid; cursor: pointer; transition: all 0.2s ease;">1.000€</button>
                                            <button class="gcc-inline-option-btn" style="padding: 6px 12px; border-radius: 12px; font-size: 14px; border: 1px solid; cursor: pointer; transition: all 0.2s ease;">5.000€</button>
                                            <button class="gcc-inline-option-btn" style="padding: 6px 12px; border-radius: 12px; font-size: 14px; border: 1px solid; cursor: pointer; transition: all 0.2s ease;">10.000€</button>
                                        </div>
                                        <div class="message-time" id="preview-ai-time" style="font-size: 11px; margin-top: 4px;">10:30</div>
                                    </div>
                                </div>
                                <!-- User Message -->
                                <div class="message-wrapper user-message" style="display: flex; align-items: flex-start; gap: 12px; justify-content: flex-end;">
                                    <div class="message-content" style="display: flex; flex-direction: column; align-items: flex-end;">
                                        <div class="message-bubble user-bubble" id="preview-user-bubble" style="padding: 12px 16px; border-radius: 18px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                                            <p id="preview-user-text" style="margin: 0;">5.000€</p>
                                        </div>
                                        <div class="message-time" id="preview-user-time" style="font-size: 11px; margin-top: 4px;">10:31</div>
                                    </div>
                                    <div class="avatar" id="preview-user-avatar" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 16px; overflow: hidden;">
                                        <div class="user-icon" id="preview-user-avatar-text" style="font-size: 14px;">👤</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php submit_button(); ?>
                </form>
            </div>
        
        <?php elseif ($current_tab === 'chat_persons'): ?>
            <div class="personas-tab">
                <h3>Chat Persons</h3>
                <p>Manage your chatbot personas. Each persona has a unique name, greeting message, and optional image.</p>
                
                <div class="personas-toolbar">
                    <button type="button" class="button button-primary" onclick="openPersonaModal()">
                        Add New Persona
                    </button>
                    <div class="search-box">
                        <input type="text" id="persona-search" placeholder="Search personas..." value="<?php echo esc_attr($data['search']); ?>">
                        <button type="button" class="button" onclick="searchPersonas()">Search</button>
                    </div>
                </div>
                
                <div id="personas-container">
                    <table class="wp-list-table widefat fixed striped personas-table">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 60px;">Avatar</th>
                                <th scope="col">Name</th>
                                <th scope="col">Greeting Message</th>
                                <th scope="col" style="width: 100px;">Status</th>
                                <th scope="col" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['personas'])): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px;">
                                        No personas found. <a href="#" onclick="openPersonaModal()">Add your first persona</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data['personas'] as $persona): ?>
                                    <tr data-persona-id="<?php echo esc_attr($persona->id); ?>">
                                        <td>
                                            <div class="persona-avatar">
                                                <?php if (!empty($persona->image_url)): ?>
                                                    <img src="<?php echo esc_url($persona->image_url); ?>" alt="<?php echo esc_attr($persona->name); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="persona-avatar-fallback" style="width: 40px; height: 40px; border-radius: 50%; background: #3b82f6; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">
                                                        <?php echo esc_html(substr($persona->name, 0, 1)); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?php echo esc_html($persona->name); ?></strong>
                                        </td>
                                        <td>
                                            <div class="greeting-preview" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                <?php echo esc_html($persona->greeting_message); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $persona->active ? 'active' : 'inactive'; ?>">
                                                <?php echo $persona->active ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="button button-small" onclick="togglePersonaStatus(<?php echo esc_js($persona->id); ?>)">
                                                <?php echo $persona->active ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                            <button type="button" class="button button-small" onclick="editPersona(<?php echo esc_js($persona->id); ?>)">
                                                Edit
                                            </button>
                                            <button type="button" class="button button-small button-link-delete" onclick="deletePersona(<?php echo esc_js($persona->id); ?>)">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($data['total_personas'] > $data['per_page']): ?>
                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <?php
                            $total_pages = ceil($data['total_personas'] / $data['per_page']);
                            echo paginate_links(array(
                                'base' => admin_url('admin.php?page=gcc-settings&tab=chat_persons&paged=%#%'),
                                'format' => '',
                                'current' => $data['page'],
                                'total' => $total_pages,
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;'
                            ));
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        
        <?php elseif ($current_tab === 'chat_questions'): ?>
            <div class="questions-tab">
                <h3>Chat Questions</h3>
                <p>Manage your chatbot questions and their order. Questions will be asked in the order specified.</p>
                
                <div class="questions-toolbar">
                    <button type="button" class="button button-primary" onclick="openQuestionModal()">
                        Add New Question
                    </button>
                    <button type="button" class="button button-secondary" onclick="refreshDefaultQuestions()" style="margin-left: 10px;">
                        Refresh Default Questions
                    </button>
                    <div class="search-box">
                        <input type="text" id="question-search" placeholder="Search questions..." value="<?php echo esc_attr($data['search']); ?>">
                        <button type="button" class="button" onclick="searchQuestions()">Search</button>
                    </div>
                </div>
                
                <div id="questions-container">
                    <div class="sortable-questions">
                        <?php if (empty($data['questions'])): ?>
                            <div class="no-questions">
                                <p>No questions found. <a href="#" onclick="openQuestionModal()">Add your first question</a></p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($data['questions'] as $question): ?>
                                <div class="question-item" data-question-id="<?php echo esc_attr($question->id); ?>">
                                    <div class="question-header">
                                        <div class="question-drag-handle">
                                            <span class="dashicons dashicons-sort"></span>
                                        </div>
                                        <div class="question-order">
                                            <span class="order-number"><?php echo esc_html($question->question_order); ?></span>
                                        </div>
                                        <div class="question-title">
                                            <strong><?php echo esc_html($question->question); ?></strong>
                                        </div>
                                        <div class="question-status">
                                            <span class="status-badge <?php echo $question->active ? 'active' : 'inactive'; ?>">
                                                <?php echo $question->active ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </div>
                                        <div class="question-actions">
                                            <button type="button" class="button button-small" onclick="toggleQuestionStatus(<?php echo esc_js($question->id); ?>)">
                                                <?php echo $question->active ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                            <button type="button" class="button button-small" onclick="editQuestion(<?php echo esc_js($question->id); ?>)">
                                                Edit
                                            </button>
                                            <button type="button" class="button button-small button-link-delete" onclick="deleteQuestion(<?php echo esc_js($question->id); ?>)">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                    <div class="question-details">
                                        <div class="question-options">
                                            <strong>Options:</strong>
                                            <?php 
                                            $options = json_decode($question->options, true);
                                            if ($options) {
                                                echo '<ul>';
                                                foreach ($options as $option) {
                                                    echo '<li>' . esc_html($option['label']) . ' (' . esc_html($option['value']) . ')</li>';
                                                }
                                                echo '</ul>';
                                            }
                                            ?>
                                        </div>
                                        <?php if (!empty($question->attributes)): ?>
                                            <div class="question-attributes">
                                                <strong>Attributes:</strong>
                                                <?php 
                                                $attributes = json_decode($question->attributes, true);
                                                if ($attributes) {
                                                    echo '<span class="attributes-tags">';
                                                    foreach ($attributes as $key => $value) {
                                                        if ($value) {
                                                            echo '<span class="attribute-tag">' . esc_html($key) . '</span>';
                                                        }
                                                    }
                                                    echo '</span>';
                                                }
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        
        <?php elseif ($current_tab === 'email'): ?>
            <div class="email-settings-tab">
                <h3>Email Settings</h3>
                <form method="post" action="">
                    <?php wp_nonce_field('gcc_email_settings'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="email_method">Email Method</label>
                            </th>
                            <td>
                                <select id="email_method" name="email_method">
                                    <option value="wp_mail" <?php selected($data['email_method'], 'wp_mail'); ?>>WordPress wp_mail</option>
                                    <option value="sendgrid" <?php selected($data['email_method'], 'sendgrid'); ?>>SendGrid</option>
                                    <option value="mailtrap" <?php selected($data['email_method'], 'mailtrap'); ?>>Mailtrap</option>
                                </select>
                                <p class="description">Choose your email delivery method</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="sendgrid_api_key">SendGrid API Key</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="sendgrid_api_key" 
                                       name="sendgrid_api_key" 
                                       value="<?php echo esc_attr($data['sendgrid_api_key']); ?>" 
                                       class="large-text" />
                                <p class="description">Required for SendGrid email delivery</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="sendgrid_sender_email">SendGrid Sender Email</label>
                            </th>
                            <td>
                                <input type="email" 
                                       id="sendgrid_sender_email" 
                                       name="sendgrid_sender_email" 
                                       value="<?php echo esc_attr($data['sendgrid_sender_email']); ?>" 
                                       class="regular-text" />
                                <p class="description">Verified sender email for SendGrid</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="mailtrap_username">Mailtrap Username</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="mailtrap_username" 
                                       name="mailtrap_username" 
                                       value="<?php echo esc_attr($data['mailtrap_username']); ?>" 
                                       class="regular-text" />
                                <p class="description">Mailtrap SMTP username</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="mailtrap_password">Mailtrap Password</label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="mailtrap_password" 
                                       name="mailtrap_password" 
                                       value="<?php echo esc_attr($data['mailtrap_password']); ?>" 
                                       class="regular-text" />
                                <p class="description">Mailtrap SMTP password</p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
        
        <?php elseif ($current_tab === 'cache'): ?>
            <div class="cache-settings-tab">
                <h3>Cache Management</h3>
                <p>Manage plugin cache and performance settings.</p>
                
                <div class="cache-actions">
                    <h4>Cache Actions</h4>
                    <p>Clear cached data to ensure fresh content.</p>
                    <button type="button" class="button button-primary" onclick="clearCache()">
                        Clear All Cache
                    </button>
                    <div id="cache-result" style="margin-top: 10px;"></div>
                </div>
                
                <div class="cache-info">
                    <h4>Cache Information</h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Last API Sync</th>
                            <td><?php echo get_option('gcc_last_api_sync', 'Never'); ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Cached Products</th>
                            <td><?php echo get_option('gcc_cached_products') ? 'Yes' : 'No'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Cache Status</th>
                            <td><?php echo function_exists('wp_cache_flush') ? 'Available' : 'Not Available'; ?></td>
                        </tr>
                    </table>
                </div>
                
                <script>
                function clearCache() {
                    const button = document.querySelector('[onclick="clearCache()"]');
                    const result = document.getElementById('cache-result');
                    
                    button.disabled = true;
                    button.textContent = 'Clearing...';
                    
                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'gcc_clear_cache',
                            nonce: '<?php echo wp_create_nonce("gcc_admin_nonce"); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                result.innerHTML = '<div class="notice notice-success"><p>' + response.data.message + '</p></div>';
                            } else {
                                result.innerHTML = '<div class="notice notice-error"><p>' + response.data.message + '</p></div>';
                            }
                        },
                        error: function() {
                            result.innerHTML = '<div class="notice notice-error"><p>Error clearing cache</p></div>';
                        },
                        complete: function() {
                            button.disabled = false;
                            button.textContent = 'Clear All Cache';
                        }
                    });
                }
                </script>
            </div>
        
        <?php else: ?>
            <div class="unknown-tab">
                <h3>Unknown Tab</h3>
                <p>Tab "<?php echo esc_html($current_tab); ?>" not found.</p>
                <p>Available tabs: general, chatbot, chat_persons, chat_questions, email, cache</p>
            </div>
        <?php endif; ?>
    </div>
</div>


<!-- Persona Modal -->
<div id="persona-modal" class="persona-modal" style="display: none;">
    <div class="persona-modal-overlay" onclick="closePersonaModal()"></div>
    <div class="persona-modal-content">
        <div class="persona-modal-header">
            <h2 id="persona-modal-title">Add New Persona</h2>
            <button type="button" class="persona-modal-close" onclick="closePersonaModal()">&times;</button>
        </div>
        <div class="persona-modal-body">
            <form id="persona-form">
                <input type="hidden" id="persona-id" name="persona_id">
                
                <div class="form-group">
                    <label for="persona-name">Name *</label>
                    <input type="text" id="persona-name" name="name" required class="widefat">
                </div>
                
                <div class="form-group">
                    <label for="persona-greeting">Greeting Message *</label>
                    <textarea id="persona-greeting" name="greeting_message" required class="widefat" rows="4"></textarea>
                    <p class="description">This message will be displayed when the persona is randomly selected</p>
                </div>
                
                <div class="form-group">
                    <label for="persona-image">Avatar Image</label>
                    <div class="image-upload-container">
                        <div class="current-image">
                            <div id="persona-image-preview" class="image-preview">
                                <div class="image-placeholder">
                                    <span>No image selected</span>
                                </div>
                            </div>
                        </div>
                        <div class="image-upload-controls">
                            <input type="file" id="persona-image-upload" accept="image/*" style="display: none;">
                            <button type="button" class="button" onclick="document.getElementById('persona-image-upload').click()">
                                Choose Image
                            </button>
                            <button type="button" class="button" onclick="removePersonaImage()">
                                Remove
                            </button>
                        </div>
                        <input type="hidden" id="persona-image-url" name="image_url">
                    </div>
                    <p class="description">Upload an avatar image for this persona. If no image is provided, the first letter of the name will be used.</p>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="persona-active" name="active" checked>
                        Active
                    </label>
                    <p class="description">Only active personas can be randomly selected by the chatbot</p>
                </div>
            </form>
        </div>
        <div class="persona-modal-footer">
            <button type="button" class="button button-primary" onclick="savePersona()">Save Persona</button>
            <button type="button" class="button" onclick="closePersonaModal()">Cancel</button>
        </div>
    </div>
</div>

<!-- Question Modal -->
<div id="question-modal" class="question-modal" style="display: none;">
    <div class="question-modal-overlay" onclick="closeQuestionModal()"></div>
    <div class="question-modal-content">
        <div class="question-modal-header">
            <h2 id="question-modal-title">Add New Question</h2>
            <button type="button" class="question-modal-close" onclick="closeQuestionModal()">&times;</button>
        </div>
        <div class="question-modal-body">
            <form id="question-form">
                <input type="hidden" id="question-id" name="question_id">
                
                <div class="form-group">
                    <label for="question-text">Question Text *</label>
                    <textarea id="question-text" name="question" required class="widefat" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="question-order">Order</label>
                    <input type="number" id="question-order" name="question_order" value="1" min="1" class="small-text">
                    <p class="description">Order in which this question appears in the chatbot flow</p>
                </div>
                
                <div class="form-group">
                    <label>Options *</label>
                    <div id="options-container">
                        <div class="option-item">
                            <input type="text" name="option_label[]" placeholder="Option label" required class="widefat">
                            <input type="text" name="option_value[]" placeholder="Option value" required class="widefat">
                            <button type="button" class="button button-small remove-option" onclick="removeOption(this)">Remove</button>
                        </div>
                    </div>
                    <button type="button" class="button button-small" onclick="addOption()">Add Option</button>
                </div>
                
                <div class="form-group">
                    <label>Attributes</label>
                    <div class="attributes-container">
                        <label>
                            <input type="checkbox" name="attribute_budget" value="1">
                            Budget - This question captures the user's budget
                        </label>
                        <label>
                            <input type="checkbox" name="attribute_product_type" value="1">
                            Product Type - This question captures the product type preference
                        </label>
                        <label>
                            <input type="checkbox" name="attribute_delivery_method" value="1">
                            Delivery Method - This question captures the delivery method preference
                        </label>
                        <label>
                            <input type="checkbox" name="attribute_combo_percentage" value="1">
                            Combo Percentage - This question captures the percentage split for combo selection
                        </label>
                        <label>
                            <input type="checkbox" name="attribute_weight_preference" value="1">
                            Weight Preference - This question captures weight preference for bars
                        </label>
                        <label>
                            <input type="checkbox" name="attribute_high_budget_action" value="1">
                            High Budget Action - This question captures action for high budget users
                        </label>
                    </div>
                    <p class="description">Select attributes that this question captures for calculation purposes</p>
                </div>
                
                <div class="form-group">
                    <label for="question-condition">Display Condition (optional)</label>
                    <input type="text" id="question-condition" name="condition_logic" class="widefat" placeholder='e.g., budget >= 30000 or product_type == "combo"'>
                    <p class="description">JavaScript-like condition to determine when to show this question. Available variables: budget, product_type, delivery_method, combo_percentage, weight_preference, high_budget_action</p>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="question-active" name="active" checked>
                        Active
                    </label>
                    <p class="description">Only active questions will be shown in the chatbot</p>
                </div>
            </form>
        </div>
        <div class="question-modal-footer">
            <button type="button" class="button button-primary" onclick="saveQuestion()">Save Question</button>
            <button type="button" class="button" onclick="closeQuestionModal()">Cancel</button>
        </div>
    </div>
</div>

<style>
/* Persona table styles */
.personas-table .persona-avatar {
    text-align: center;
}

.personas-table .status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.personas-table .status-badge.active {
    background: #d4edda;
    color: #155724;
}

.personas-table .status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

.personas-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.personas-toolbar .search-box {
    display: flex;
    gap: 10px;
}

.personas-toolbar .search-box input {
    width: 200px;
}

/* Modal styles */
.persona-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.persona-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.persona-modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
}

.persona-modal-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.persona-modal-header h2 {
    margin: 0;
}

.persona-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.persona-modal-close:hover {
    color: #000;
}

.persona-modal-body {
    padding: 20px;
    flex: 1;
    overflow-y: auto;
}

.persona-modal-footer {
    padding: 20px;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.image-upload-container {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.image-preview {
    width: 80px;
    height: 80px;
    border: 2px dashed #ddd;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-placeholder {
    text-align: center;
    color: #666;
    font-size: 12px;
}

.image-upload-controls {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Questions tab styles */
.questions-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.questions-toolbar .search-box {
    display: flex;
    gap: 10px;
}

.questions-toolbar .search-box input {
    width: 200px;
}

.sortable-questions {
    min-height: 50px;
}

.question-item {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 15px;
    padding: 15px;
    position: relative;
}

.question-item:hover {
    border-color: #999;
}

.question-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
}

.question-drag-handle {
    cursor: move;
    color: #666;
}

.question-drag-handle:hover {
    color: #333;
}

.question-order {
    background: #f1f1f1;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #666;
}

.question-title {
    flex: 1;
}

.question-status .status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.question-status .status-badge.active {
    background: #d4edda;
    color: #155724;
}

.question-status .status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

.question-actions {
    display: flex;
    gap: 5px;
}

.question-details {
    border-top: 1px solid #eee;
    padding-top: 10px;
    margin-top: 10px;
}

.question-options ul {
    margin: 5px 0;
    padding-left: 20px;
}

.question-options li {
    margin: 2px 0;
}

.question-attributes {
    margin-top: 10px;
}

.attributes-tags {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.attribute-tag {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.no-questions {
    text-align: center;
    padding: 40px;
    color: #666;
}

/* Question modal styles */
.question-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.question-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.question-modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 700px;
    max-height: 80vh;
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
}

.question-modal-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.question-modal-header h2 {
    margin: 0;
}

.question-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.question-modal-close:hover {
    color: #000;
}

.question-modal-body {
    padding: 20px;
    flex: 1;
    overflow-y: auto;
}

.question-modal-footer {
    padding: 20px;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.option-item {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
}

.option-item input {
    flex: 1;
}

.option-item .remove-option {
    flex-shrink: 0;
}

.attributes-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.attributes-container label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: normal;
}
</style>

<script>
// Persona management JavaScript
let currentPersonaId = null;

function openPersonaModal(personaId = null) {
    currentPersonaId = personaId;
    const modal = document.getElementById('persona-modal');
    const title = document.getElementById('persona-modal-title');
    const form = document.getElementById('persona-form');
    
    if (personaId) {
        title.textContent = 'Edit Persona';
        loadPersonaData(personaId);
    } else {
        title.textContent = 'Add New Persona';
        form.reset();
        document.getElementById('persona-id').value = '';
        document.getElementById('persona-image-url').value = '';
        updateImagePreview('');
    }
    
    modal.style.display = 'flex';
}

function closePersonaModal() {
    document.getElementById('persona-modal').style.display = 'none';
    currentPersonaId = null;
}

function loadPersonaData(personaId) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'gcc_get_persona',
            persona_id: personaId,
            nonce: gcc_admin_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                const persona = response.data;
                document.getElementById('persona-id').value = persona.id;
                document.getElementById('persona-name').value = persona.name;
                document.getElementById('persona-greeting').value = persona.greeting_message;
                document.getElementById('persona-image-url').value = persona.image_url || '';
                document.getElementById('persona-active').checked = persona.active == 1;
                updateImagePreview(persona.image_url);
            } else {
                alert('Error loading persona: ' + response.data.message);
            }
        },
        error: function() {
            alert('Error loading persona data');
        }
    });
}

function savePersona() {
    const form = document.getElementById('persona-form');
    const formData = new FormData(form);
    const personaId = document.getElementById('persona-id').value;
    
    formData.append('action', personaId ? 'gcc_update_persona' : 'gcc_create_persona');
    formData.append('nonce', '<?php echo wp_create_nonce("gcc_admin_nonce"); ?>');
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                closePersonaModal();
                location.reload(); // Refresh the page to show updated data
            } else {
                alert('Error saving persona: ' + response.data.message);
            }
        },
        error: function() {
            alert('Error saving persona');
        }
    });
}

function editPersona(personaId) {
    openPersonaModal(personaId);
}

function deletePersona(personaId) {
    if (!confirm('Are you sure you want to delete this persona?')) {
        return;
    }
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'gcc_delete_persona',
            persona_id: personaId,
            nonce: gcc_admin_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error deleting persona: ' + response.data.message);
            }
        },
        error: function() {
            alert('Error deleting persona');
        }
    });
}

function togglePersonaStatus(personaId) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'gcc_toggle_persona_active',
            persona_id: personaId,
            nonce: gcc_admin_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error toggling persona status: ' + response.data.message);
            }
        },
        error: function() {
            alert('Error toggling persona status');
        }
    });
}

function searchPersonas() {
    const searchTerm = document.getElementById('persona-search').value;
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('search', searchTerm);
    currentUrl.searchParams.set('paged', '1');
    window.location.href = currentUrl.toString();
}

function updateImagePreview(imageUrl) {
    const preview = document.getElementById('persona-image-preview');
    if (imageUrl) {
        preview.innerHTML = '<img src="' + imageUrl + '" alt="Persona image">';
    } else {
        preview.innerHTML = '<div class="image-placeholder"><span>No image selected</span></div>';
    }
}

function removePersonaImage() {
    document.getElementById('persona-image-url').value = '';
    updateImagePreview('');
}

// Handle image upload
document.getElementById('persona-image-upload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('action', 'gcc_upload_persona_image');
    formData.append('persona_image', file);
    formData.append('nonce', '<?php echo wp_create_nonce("gcc_admin_nonce"); ?>');
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                document.getElementById('persona-image-url').value = response.data.image_url;
                updateImagePreview(response.data.image_url);
            } else {
                alert('Error uploading image: ' + response.data.message);
            }
        },
        error: function() {
            alert('Error uploading image');
        }
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePersonaModal();
        closeQuestionModal();
    }
});

// Question management JavaScript
let currentQuestionId = null;

function openQuestionModal(questionId = null) {
    currentQuestionId = questionId;
    const modal = document.getElementById('question-modal');
    const title = document.getElementById('question-modal-title');
    const form = document.getElementById('question-form');
    
    if (questionId) {
        title.textContent = 'Edit Question';
        loadQuestionData(questionId);
    } else {
        title.textContent = 'Add New Question';
        form.reset();
        document.getElementById('question-id').value = '';
        // Add default option
        const container = document.getElementById('options-container');
        container.innerHTML = '<div class="option-item"><input type="text" name="option_label[]" placeholder="Option label" required class="widefat"><input type="text" name="option_value[]" placeholder="Option value" required class="widefat"><button type="button" class="button button-small remove-option" onclick="removeOption(this)">Remove</button></div>';
    }
    
    modal.style.display = 'flex';
}

function closeQuestionModal() {
    document.getElementById('question-modal').style.display = 'none';
    currentQuestionId = null;
}

function loadQuestionData(questionId) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'gcc_get_question',
            question_id: questionId,
            nonce: '<?php echo wp_create_nonce("gcc_admin_nonce"); ?>'
        },
        success: function(response) {
            if (response.success) {
                const question = response.data;
                document.getElementById('question-id').value = question.id;
                document.getElementById('question-text').value = question.question;
                document.getElementById('question-order').value = question.question_order;
                document.getElementById('question-active').checked = question.active == 1;
                document.getElementById('question-condition').value = question.condition_logic || '';
                
                // Load options
                const options = JSON.parse(question.options);
                const container = document.getElementById('options-container');
                container.innerHTML = '';
                options.forEach(function(option) {
                    const optionHtml = '<div class="option-item"><input type="text" name="option_label[]" placeholder="Option label" required class="widefat" value="' + option.label + '"><input type="text" name="option_value[]" placeholder="Option value" required class="widefat" value="' + option.value + '"><button type="button" class="button button-small remove-option" onclick="removeOption(this)">Remove</button></div>';
                    container.innerHTML += optionHtml;
                });
                
                // Load attributes
                const attributes = JSON.parse(question.attributes || '{}');
                document.querySelector('input[name="attribute_budget"]').checked = attributes.budget || false;
                document.querySelector('input[name="attribute_product_type"]').checked = attributes.product_type || false;
                document.querySelector('input[name="attribute_delivery_method"]').checked = attributes.delivery_method || false;
                document.querySelector('input[name="attribute_combo_percentage"]').checked = attributes.combo_percentage || false;
                document.querySelector('input[name="attribute_weight_preference"]').checked = attributes.weight_preference || false;
                document.querySelector('input[name="attribute_high_budget_action"]').checked = attributes.high_budget_action || false;
            } else {
                alert('Error loading question: ' + response.data.message);
            }
        },
        error: function() {
            alert('Error loading question data');
        }
    });
}

function saveQuestion() {
    const form = document.getElementById('question-form');
    const formData = new FormData(form);
    const questionId = document.getElementById('question-id').value;
    
    // Collect options
    const labels = form.querySelectorAll('input[name="option_label[]"]');
    const values = form.querySelectorAll('input[name="option_value[]"]');
    const options = [];
    
    for (let i = 0; i < labels.length; i++) {
        if (labels[i].value.trim() && values[i].value.trim()) {
            options.push({
                label: labels[i].value.trim(),
                value: values[i].value.trim()
            });
        }
    }
    
    if (options.length === 0) {
        alert('Please add at least one option');
        return;
    }
    
    // Collect attributes
    const attributes = {
        budget: document.querySelector('input[name="attribute_budget"]').checked,
        product_type: document.querySelector('input[name="attribute_product_type"]').checked,
        delivery_method: document.querySelector('input[name="attribute_delivery_method"]').checked,
        combo_percentage: document.querySelector('input[name="attribute_combo_percentage"]').checked,
        weight_preference: document.querySelector('input[name="attribute_weight_preference"]').checked,
        high_budget_action: document.querySelector('input[name="attribute_high_budget_action"]').checked
    };
    
    const data = {
        action: questionId ? 'gcc_update_question' : 'gcc_create_question',
        question_id: questionId,
        question: document.getElementById('question-text').value,
        question_order: document.getElementById('question-order').value,
        active: document.getElementById('question-active').checked ? 1 : 0,
        options: JSON.stringify(options),
        attributes: JSON.stringify(attributes),
        condition_logic: document.getElementById('question-condition').value,
        nonce: '<?php echo wp_create_nonce("gcc_admin_nonce"); ?>'
    };
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: data,
        success: function(response) {
            if (response.success) {
                closeQuestionModal();
                location.reload();
            } else {
                alert('Error saving question: ' + response.data.message);
            }
        },
        error: function() {
            alert('Error saving question');
        }
    });
}

function editQuestion(questionId) {
    openQuestionModal(questionId);
}

function deleteQuestion(questionId) {
    if (!confirm('Are you sure you want to delete this question?')) {
        return;
    }
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'gcc_delete_question',
            question_id: questionId,
            nonce: '<?php echo wp_create_nonce("gcc_admin_nonce"); ?>'
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error deleting question: ' + response.data.message);
            }
        },
        error: function() {
            alert('Error deleting question');
        }
    });
}

function toggleQuestionStatus(questionId) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'gcc_toggle_question_active',
            question_id: questionId,
            nonce: '<?php echo wp_create_nonce("gcc_admin_nonce"); ?>'
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error toggling question status: ' + response.data.message);
            }
        },
        error: function() {
            alert('Error toggling question status');
        }
    });
}

function searchQuestions() {
    const searchTerm = document.getElementById('question-search').value;
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('search', searchTerm);
    currentUrl.searchParams.set('paged', '1');
    window.location.href = currentUrl.toString();
}

function addOption() {
    const container = document.getElementById('options-container');
    const optionHtml = '<div class="option-item"><input type="text" name="option_label[]" placeholder="Option label" required class="widefat"><input type="text" name="option_value[]" placeholder="Option value" required class="widefat"><button type="button" class="button button-small remove-option" onclick="removeOption(this)">Remove</button></div>';
    container.innerHTML += optionHtml;
}

function removeOption(button) {
    const container = document.getElementById('options-container');
    if (container.children.length > 1) {
        button.parentElement.remove();
    } else {
        alert('At least one option is required');
    }
}

// Initialize sortable questions (requires jQuery UI)
jQuery(document).ready(function($) {
    if (typeof $.fn.sortable !== 'undefined') {
        $('.sortable-questions').sortable({
            items: '.question-item',
            handle: '.question-drag-handle',
            cursor: 'move',
            opacity: 0.7,
            update: function(event, ui) {
                const questionId = ui.item.data('question-id');
                const newOrder = ui.item.index() + 1;
                
                // Update question order via AJAX
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'gcc_update_question_order',
                        question_id: questionId,
                        new_order: newOrder,
                        nonce: '<?php echo wp_create_nonce("gcc_admin_nonce"); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update order numbers in UI
                            $('.question-item').each(function(index) {
                                $(this).find('.order-number').text(index + 1);
                            });
                        } else {
                            alert('Error updating question order: ' + response.data.message);
                            $('.sortable-questions').sortable('cancel');
                        }
                    },
                    error: function() {
                        alert('Error updating question order');
                        $('.sortable-questions').sortable('cancel');
                    }
                });
            }
        });
    }
});

function refreshDefaultQuestions() {
    if (!confirm('Are you sure you want to refresh default questions? This will delete all existing questions and recreate them with the latest defaults.')) {
        return;
    }
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'gcc_refresh_default_questions',
            nonce: '<?php echo wp_create_nonce("gcc_admin_nonce"); ?>'
        },
        success: function(response) {
            if (response.success) {
                alert('Default questions refreshed successfully!');
                location.reload();
            } else {
                alert('Error refreshing default questions: ' + response.data.message);
            }
        },
        error: function() {
            alert('Error refreshing default questions');
        }
    });
}

// User Avatar Functions
function updateUserAvatarPreview(imageUrl) {
    const preview = document.getElementById('user-avatar-preview');
    if (imageUrl) {
        preview.innerHTML = '<img src="' + imageUrl + '" alt="User Avatar" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">';
    } else {
        preview.innerHTML = '<div class="image-placeholder"><span>👤</span></div>';
    }
}

function removeUserAvatar() {
    document.getElementById('user_avatar_image').value = '';
    updateUserAvatarPreview('');
}

// Handle user avatar upload
document.addEventListener('DOMContentLoaded', function() {
    const userAvatarUpload = document.getElementById('user-avatar-upload');
    if (userAvatarUpload) {
        userAvatarUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('action', 'gcc_upload_user_avatar');
            formData.append('user_avatar', file);
            formData.append('nonce', '<?php echo wp_create_nonce("gcc_admin_nonce"); ?>');
            
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        document.getElementById('user_avatar_image').value = response.data.image_url;
                        updateUserAvatarPreview(response.data.image_url);
                    } else {
                        alert('Error uploading image: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Error uploading image');
                }
            });
        });
    }

    // Live Preview Functionality
    function updateChatbotPreview() {
        const fontFamily = document.getElementById('chatbot_font_family')?.value || 'inherit';
        const headerFontFamily = document.getElementById('chat_header_font_family')?.value || 'inherit';
        const containerBg = document.getElementById('chat_container_bg_color')?.value || '#ffffff';
        const headerBg = document.getElementById('chat_header_bg_color')?.value || '#3c2415';
        const headerText = document.getElementById('chat_header_text_color')?.value || '#fdf7e7';
        const aiAvatarBg = document.getElementById('ai_avatar_bg_color')?.value || '#3b82f6';
        const aiAvatarText = document.getElementById('ai_avatar_text_color')?.value || '#ffffff';
        const aiBubbleBg = document.getElementById('ai_bubble_bg_color')?.value || '#fdf7e7';
        const aiBubbleText = document.getElementById('ai_bubble_text_color')?.value || '#3c2415';
        const aiTimeText = document.getElementById('ai_time_text_color')?.value || '#6b7280';
        const userAvatarBg = document.getElementById('user_avatar_bg_color')?.value || '#10b981';
        const userAvatarText = document.getElementById('user_avatar_text_color')?.value || '#ffffff';
        const userBubbleBg = document.getElementById('user_bubble_bg_color')?.value || '#3b82f6';
        const userBubbleText = document.getElementById('user_bubble_text_color')?.value || '#ffffff';
        const userTimeText = document.getElementById('user_time_text_color')?.value || '#6b7280';

        const previewContainer = document.getElementById('preview-container');
        if (!previewContainer) return;

        // Apply font family to entire container
        previewContainer.style.fontFamily = fontFamily;
        previewContainer.style.backgroundColor = containerBg;

        // Update header
        const previewHeader = document.getElementById('preview-header');
        const previewHeaderText = document.getElementById('preview-header-text');
        const previewHeaderSubtext = document.getElementById('preview-header-subtext');
        const previewPersonaFallback = document.getElementById('preview-persona-fallback');
        
        if (previewHeader) previewHeader.style.background = headerBg;
        if (previewHeaderText) {
            previewHeaderText.style.color = headerText;
            previewHeaderText.style.fontFamily = headerFontFamily;
        }
        if (previewHeaderSubtext) previewHeaderSubtext.style.color = headerText;
        if (previewPersonaFallback) {
            previewPersonaFallback.style.backgroundColor = aiAvatarBg;
            previewPersonaFallback.style.color = aiAvatarText;
        }

        // Also update header avatar container
        const previewHeaderAvatar = document.getElementById('preview-header-avatar');
        if (previewHeaderAvatar) {
            previewHeaderAvatar.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
        }

        // Update AI message
        const previewAiAvatar = document.getElementById('preview-ai-avatar');
        const previewAiAvatarText = document.getElementById('preview-ai-avatar-text');
        const previewAiBubble = document.getElementById('preview-ai-bubble');
        const previewAiText = document.getElementById('preview-ai-text');
        const previewAiTime = document.getElementById('preview-ai-time');

        if (previewAiAvatar) previewAiAvatar.style.backgroundColor = aiAvatarBg;
        if (previewAiAvatarText) previewAiAvatarText.style.color = aiAvatarText;
        if (previewAiBubble) previewAiBubble.style.backgroundColor = aiBubbleBg;
        if (previewAiText) previewAiText.style.color = aiBubbleText;
        if (previewAiTime) previewAiTime.style.color = aiTimeText;

        // Update inline option buttons
        const previewOptionBtns = document.querySelectorAll('#preview-container .gcc-inline-option-btn');
        previewOptionBtns.forEach(btn => {
            btn.style.backgroundColor = aiBubbleBg;
            btn.style.color = aiBubbleText;
            btn.style.borderColor = aiBubbleText;
        });

        // Update user message
        const previewUserAvatar = document.getElementById('preview-user-avatar');
        const previewUserAvatarText = document.getElementById('preview-user-avatar-text');
        const previewUserBubble = document.getElementById('preview-user-bubble');
        const previewUserText = document.getElementById('preview-user-text');
        const previewUserTime = document.getElementById('preview-user-time');

        if (previewUserAvatar) previewUserAvatar.style.backgroundColor = userAvatarBg;
        if (previewUserAvatarText) previewUserAvatarText.style.color = userAvatarText;
        if (previewUserBubble) previewUserBubble.style.backgroundColor = userBubbleBg;
        if (previewUserText) previewUserText.style.color = userBubbleText;
        if (previewUserTime) previewUserTime.style.color = userTimeText;
    }

    // Add event listeners to all color inputs and font selection
    const colorInputs = [
        'chatbot_font_family',
        'chat_header_font_family',
        'chat_container_bg_color',
        'chat_header_bg_color',
        'chat_header_text_color',
        'ai_avatar_bg_color',
        'ai_avatar_text_color',
        'ai_bubble_bg_color',
        'ai_bubble_text_color',
        'ai_time_text_color',
        'user_avatar_bg_color',
        'user_avatar_text_color',
        'user_bubble_bg_color',
        'user_bubble_text_color',
        'user_time_text_color'
    ];

    colorInputs.forEach(function(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('change', updateChatbotPreview);
            input.addEventListener('input', updateChatbotPreview);
        }
    });

    // Initial preview update
    setTimeout(updateChatbotPreview, 500);
});
</script>