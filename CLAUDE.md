# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress plugin called "Gold Calculator Chatbot" - an interactive chatbot system for gold investment consultation and product recommendations. The plugin provides multiple bot personas (ZLATIJA, ZLATA, ZLATKA, ZLATISLAVA) that guide users through budget-based product selection and quote generation.

## Core Architecture

### Main Plugin Structure
- **Main File**: `gold-calculator-chatbot.php` - Main plugin class that initializes all components
- **Core Classes** (in `includes/` directory):
  - `GCC_Database` - Handles all database operations (products, quotes, personas, questions)
  - `GCC_Chatbot_API` - AJAX handlers for chatbot interactions and product suggestions
  - `GCC_Admin` - WordPress admin interface management
  - `GCC_Email_Handler` - Email notification system
  - `GCC_API_Parser` - XML integration for automatic price updates
  - `GCC_Shortcodes` - WordPress shortcode implementations

### Database Tables
The plugin creates these custom tables:
- `wp_gcc_products` - Product data (gold bars, ducats)
- `wp_gcc_submits` - Customer quotes and inquiries
- `wp_gcc_personas` - Bot personas with greeting messages and images
- `wp_gcc_questions` - Dynamic question system for chatbot flow

### Key Features
1. **Multi-persona Chatbot System** - Different bot personalities with custom greetings
2. **Budget-based Product Recommendations** - Algorithm matches products to user budget
3. **Shortcode System** - Multiple shortcodes for different display modes
4. **Admin Dashboard** - Complete management interface for products, settings, submissions
5. **Email Integration** - Support for wp_mail, SendGrid, and Mailtrap
6. **XML Price Integration** - Automatic product price updates from external feeds
7. **Responsive Design** - Mobile-friendly interface

## Development Commands

### WordPress Development
Since this is a WordPress plugin, development happens within a WordPress environment:

```bash
# Activate plugin (via WordPress admin or WP-CLI)
wp plugin activate gold-calculator-chatbot

# Check plugin status
wp plugin status gold-calculator-chatbot

# Clear WordPress cache
wp cache flush

# Database operations
wp db query "SELECT * FROM wp_gcc_products LIMIT 5"
```

### File Structure Navigation
```
gold-calculator-chatbot/
├── gold-calculator-chatbot.php    # Main plugin file
├── includes/                      # Core PHP classes
│   ├── class-database.php         # Database operations
│   ├── class-chatbot-api.php      # AJAX API handlers
│   ├── class-admin.php            # Admin interface
│   ├── class-email-handler.php    # Email system
│   ├── class-api-parser.php       # XML integration
│   └── class-shortcodes.php       # Shortcode implementations
├── admin/                         # Admin page templates
│   ├── dashboard.php              # Admin dashboard
│   ├── products.php               # Product management
│   ├── settings.php               # Plugin settings
│   └── submits.php                # Quote submissions
├── templates/                     # Frontend templates
│   ├── chatbot.php                # Main chatbot interface
│   └── chatbot-templates.php      # Chatbot components
└── assets/                        # Static assets
    ├── css/                       # Stylesheets
    ├── js/                        # JavaScript files
    └── images/                    # Default images
```

## Shortcode System

The plugin provides multiple shortcodes (detailed in SHORTCODES.md):
- `[gold_calculator_chatbot]` - Full chatbot interface
- `[gold_calculator_compact]` - Compact version with button
- `[gold_calculator_inline]` - Inline version for content
- `[gold_calculator_button]` - Simple button opening modal
- `[gold_calculator_stats]` - Current gold prices widget
- `[gold_calculator_products]` - Products display grid

## API Endpoints (AJAX)

### Public Endpoints
- `gcc_submit_quote` - Submit customer quote
- `gcc_get_exchange_rate` - Get current exchange rate
- `gcc_get_product_suggestion` - Get product recommendations
- `gcc_submit_contact` - Submit contact form

### Admin Endpoints
- `gcc_save_settings` - Save plugin settings
- `gcc_create_product` / `gcc_update_product` / `gcc_delete_product` - Product management
- `gcc_create_persona` / `gcc_update_persona` / `gcc_delete_persona` - Persona management
- `gcc_get_submits` / `gcc_delete_submit` - Submission management

## Configuration

### Key Settings (stored as WordPress options with 'gcc_' prefix):
- **Exchange Rate**: `gcc_exchange_rate`, `gcc_exchange_rate_display`
- **Bot Personas**: `gcc_bot_personas`, `gcc_current_persona`
- **Email Config**: `gcc_notification_email`, `gcc_email_template`
- **API Integration**: `gcc_api_url`, `gcc_api_key`, `gcc_api_update_interval`
- **Budget Thresholds**: `gcc_high_budget_threshold`, `gcc_budget_buckets`
- **Appearance**: Color scheme settings for chatbot UI

### Chatbot Flow Logic
1. **Budget Selection** - User selects investment amount
2. **Product Type** - Choose between bars, ducats, or combination
3. **Delivery Method** - Stock availability vs advance payment
4. **Product Recommendations** - Algorithm suggests optimal products
5. **Contact Form** - Collect user details and generate quote

## Email System

Supports three email methods:
1. **WordPress wp_mail** (default)
2. **SendGrid API** - Requires `gcc_sendgrid_api_key`, `gcc_sendgrid_sender_email`
3. **Mailtrap SMTP** - Requires `gcc_mailtrap_username`, `gcc_mailtrap_password`

## Security Features

- **Nonce Verification** - All AJAX requests use WordPress nonces
- **Capability Checks** - Admin functions require 'manage_options'
- **Input Sanitization** - All user input is sanitized using WordPress functions
- **SQL Injection Protection** - Uses WordPress $wpdb prepared statements
- **File Access Protection** - Direct access prevention with ABSPATH checks

## Testing

The plugin includes no automated tests. Testing should be done:
1. **Manual Testing** - Use WordPress admin interface and frontend shortcodes
2. **Browser Testing** - Verify chatbot functionality across browsers
3. **Email Testing** - Test all three email delivery methods
4. **Mobile Testing** - Ensure responsive design works properly

## Common Development Tasks

### Adding New Bot Persona
1. Use admin interface: **Gold Calculator → Settings → Chat Persons**
2. Or directly via database: `GCC_Database::create_persona()`

### Adding New Products
1. Admin interface: **Gold Calculator → Products**
2. Or bulk import via CSV using `GCC_Database::import_products_from_csv()`

### Modifying Chatbot Flow
- Edit `class-shortcodes.php` for JavaScript logic
- Modify `templates/chatbot.php` for UI structure
- Update `class-chatbot-api.php` for backend processing

### Debugging
- Enable WordPress debug mode: `define('WP_DEBUG', true);`
- Check error logs in `/wp-content/debug.log`
- Use browser developer tools for JavaScript issues
- Admin interface shows API status and recent activity

## Plugin Hooks and Filters

The codebase uses standard WordPress hooks:
- **Activation**: `register_activation_hook()` - Creates tables and default settings
- **Admin Menu**: `add_menu_page()`, `add_submenu_page()`
- **Scripts/Styles**: `wp_enqueue_scripts`, `admin_enqueue_scripts`
- **AJAX**: `wp_ajax_*` and `wp_ajax_nopriv_*` actions

## Dependencies

- **WordPress 5.0+**
- **PHP 7.4+**
- **MySQL 5.6+**
- **jQuery** (for frontend interactions)
- No external PHP libraries or build tools required