# Gold Calculator Chatbot

A WordPress plugin that provides an interactive chatbot interface for gold investment consultation with multiple bot personas.

## Features

- 🤖 Interactive chatbot with conversation flow
- 💰 Budget-based product recommendations
- 📊 Product management with buying/selling prices
- 📧 Email notification system
- 🔄 XML integration for automatic price updates
- 🎨 Multiple shortcodes for different layouts
- 📱 Responsive design
- 🎭 Multiple bot personas (ZLATIJA, ZLATA, ZLATKA, ZLATISLAVA)

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings in **Gold Calculator → Settings**

## Usage

### Basic Shortcode
```
[gold_calculator_chatbot]
```

### Available Shortcodes

- `[gold_calculator_chatbot]` - Full chatbot interface
- `[gold_calculator_compact]` - Compact version with button
- `[gold_calculator_inline]` - Inline version for content
- `[gold_calculator_button]` - Simple button opening modal
- `[gold_calculator_stats]` - Current gold prices widget
- `[gold_calculator_products]` - Products display grid

### Shortcode Parameters

#### Full Chatbot
```
[gold_calculator_chatbot 
    persona="ZLATIJA" 
    theme="light" 
    width="600px" 
    height="500px"
    button_color="#f4d03f"
    text_color="#2c3e50"
    background_color="#ffffff"
]
```

#### Compact Version
```
[gold_calculator_compact 
    persona="ZLATA" 
    button_text="Start Consultation"
    alignment="center"
]
```

## Configuration

### Settings
- Exchange rates
- Bot personas
- Email templates
- XML integration
- High budget thresholds

### Product Management
- Add/edit products
- Set buying/selling prices
- Configure stock and advance payment options
- Upload product images

### Bucket System
- Configure budget-based product recommendations
- Set weight preferences
- Define Smartpack and Smartbox eligibility

## Database Tables

The plugin creates the following tables:
- `wp_gcc_products` - Product data
- `wp_gcc_quotes` - Customer quotes
- `wp_gcc_buckets` - Budget configuration

## File Structure

```
gold-calculator-chatbot/
├── gold-calculator-chatbot.php (main plugin file)
├── includes/
│   ├── class-database.php
│   ├── class-chatbot-api.php
│   ├── class-admin.php
│   ├── class-email-handler.php
│   ├── class-xml-parser.php
│   └── class-shortcodes.php
├── templates/
│   ├── chatbot.php
│   └── chatbot-templates.php
├── admin/
│   ├── dashboard.php
│   ├── settings.php
│   └── shortcode-modal.php
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
└── SHORTCODES.md (detailed documentation)
```

## Requirements

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+

## Support

For detailed shortcode documentation, see `SHORTCODES.md`

## Version

1.0.0 - Initial release