# Gold Calculator Chatbot Shortcodes

This plugin provides multiple shortcodes for displaying the gold calculator chatbot in various formats and styles.

## Available Shortcodes

### 1. Full Chatbot Interface
**Shortcode:** `[gold_calculator_chatbot]`

The complete chatbot interface with all features including conversation flow, product selection, and quote generation.

#### Parameters:
- `persona` - Bot persona (default: "ZLATIJA")
- `theme` - Color theme: "light", "dark", "gold" (default: "light")
- `width` - Container width (default: "100%")
- `height` - Container height (default: "500px")
- `position` - Alignment: "left", "center", "right" (default: "center")
- `show_header` - Show header with avatar: "true", "false" (default: "true")
- `auto_start` - Auto-start conversation: "true", "false" (default: "false")
- `custom_greeting` - Custom greeting message
- `button_color` - Primary button color (default: "#f4d03f")
- `text_color` - Text color (default: "#2c3e50")
- `background_color` - Background color (default: "#ffffff")
- `border_radius` - Border radius (default: "12px")
- `shadow` - Show shadow: "true", "false" (default: "true")
- `animation` - Animation type: "fade", "slide", "none" (default: "fade")
- `language` - Language code (default: "sr")

#### Examples:
```
[gold_calculator_chatbot]
[gold_calculator_chatbot persona="ZLATA" theme="gold" width="600px"]
[gold_calculator_chatbot persona="ZLATKA" button_color="#e74c3c" custom_greeting="Dobrodošli u svet zlata!"]
```

### 2. Compact Chatbot
**Shortcode:** `[gold_calculator_compact]`

A smaller version with avatar and start button.

#### Parameters:
- `persona` - Bot persona (default: "ZLATIJA")
- `button_text` - Button text (default: "Započni konsultaciju")
- `button_color` - Button color (default: "#f4d03f")
- `text_color` - Text color (default: "#2c3e50")
- `show_avatar` - Show avatar: "true", "false" (default: "true")
- `alignment` - Alignment: "left", "center", "right" (default: "center")

#### Examples:
```
[gold_calculator_compact]
[gold_calculator_compact persona="ZLATA" button_text="Počni odmah" alignment="left"]
[gold_calculator_compact button_color="#27ae60" show_avatar="false"]
```

### 3. Inline Chatbot
**Shortcode:** `[gold_calculator_inline]`

Embedded in content with custom title and description.

#### Parameters:
- `title` - Title text (default: "Investicija u zlato")
- `description` - Description text (default: "Pronađite najbolji paket zlata za vaš budžet")
- `button_text` - Button text (default: "Počni")
- `persona` - Bot persona (default: "ZLATIJA")
- `background_color` - Background color (default: "#f8f9fa")
- `border_color` - Border color (default: "#f4d03f")
- `text_color` - Text color (default: "#2c3e50")

#### Examples:
```
[gold_calculator_inline]
[gold_calculator_inline title="Zlatna prilika" description="Najbolje cene na tržištu" button_text="Započni sada"]
[gold_calculator_inline background_color="#fff3cd" border_color="#e67e22"]
```

### 4. Button Shortcode
**Shortcode:** `[gold_calculator_button]`

Simple button that opens chatbot in modal or redirects to full page.

#### Parameters:
- `text` - Button text (default: "Konsultacija o zlatu")
- `size` - Button size: "small", "medium", "large" (default: "medium")
- `style` - Button style: "primary", "secondary", "success", "warning" (default: "primary")
- `icon` - Icon type: "chat", "gold", "money", "calculator", "phone", "email" (default: "chat")
- `persona` - Bot persona (default: "ZLATIJA")
- `position` - Button position: "left", "center", "right" (default: "center")
- `modal` - Open in modal: "true", "false" (default: "true")

#### Examples:
```
[gold_calculator_button]
[gold_calculator_button text="Konsultacija sa ekspertom" size="large" style="success"]
[gold_calculator_button icon="gold" position="right" modal="false"]
```

### 5. Stats Widget
**Shortcode:** `[gold_calculator_stats]`

Shows current gold prices and statistics.

#### Parameters:
- `show` - Items to show: "price", "exchange", "products" (default: "price,exchange,products")
- `layout` - Layout: "horizontal", "vertical" (default: "horizontal")
- `title` - Widget title (default: "Trenutne cene zlata")
- `refresh_interval` - Auto-refresh interval in seconds (default: "30")

#### Examples:
```
[gold_calculator_stats]
[gold_calculator_stats show="price,exchange" layout="vertical" title="Cene zlata"]
[gold_calculator_stats refresh_interval="60"]
```

### 6. Products Widget
**Shortcode:** `[gold_calculator_products]`

Displays gold products in a grid layout.

#### Parameters:
- `type` - Product type: "all", "bar", "ducat" (default: "all")
- `limit` - Number of products to show (default: "6")
- `layout` - Layout: "grid", "list" (default: "grid")
- `show_price` - Show price: "true", "false" (default: "true")
- `show_weight` - Show weight: "true", "false" (default: "true")
- `show_type` - Show type: "true", "false" (default: "true")
- `columns` - Number of columns (default: "3")

#### Examples:
```
[gold_calculator_products]
[gold_calculator_products type="bar" limit="4" columns="2"]
[gold_calculator_products show_price="false" show_type="false"]
```

## Advanced Usage

### Combining Shortcodes
You can combine multiple shortcodes on the same page:

```
[gold_calculator_stats show="price,exchange" layout="horizontal"]

[gold_calculator_products type="bar" limit="4"]

[gold_calculator_button text="Započni konsultaciju" size="large"]
```

### Custom Styling
You can add custom CSS to further customize the appearance:

```css
.gcc-chatbot-wrapper {
    border: 2px solid #your-color;
    border-radius: 15px;
}

.gcc-budget-btn {
    background: linear-gradient(45deg, #your-color1, #your-color2);
}
```

### Using with Page Builders
The shortcodes work with popular page builders like Elementor, Divi, and Beaver Builder. Simply add a shortcode element and paste the desired shortcode.

### Responsive Design
All shortcodes are fully responsive and will adapt to different screen sizes automatically.

## Plugin Settings

Before using the shortcodes, make sure to configure the plugin settings:

1. Go to **Gold Calculator → Settings** in WordPress admin
2. Configure exchange rates, bot personas, and email templates
3. Set up XML integration for automatic product updates
4. Customize the admin interface

## Troubleshooting

### Common Issues:

1. **Shortcode not displaying**: Make sure the plugin is activated
2. **Styles not loading**: Check if there are any JavaScript errors in browser console
3. **Products not showing**: Verify that products are added in the admin panel
4. **Email not sending**: Check email configuration and SMTP settings

### Support:
For support and updates, visit the plugin admin page or check the documentation.

## JavaScript API

### Available Functions:
- `gccOpenChatbotModal(persona)` - Open chatbot in modal
- `gccCloseChatbotModal()` - Close chatbot modal
- `gccOpenFullChatbot(persona)` - Open full chatbot or scroll to existing one

### Example Usage:
```javascript
// Open chatbot modal with specific persona
gccOpenChatbotModal('ZLATA');

// Open chatbot programmatically
document.getElementById('my-button').addEventListener('click', function() {
    gccOpenFullChatbot('ZLATIJA');
});
```

## Hooks and Filters

### Available Filters:
- `gcc_chatbot_greeting` - Modify greeting message
- `gcc_product_selection` - Modify product selection logic
- `gcc_email_template` - Modify email templates

### Example Usage:
```php
add_filter('gcc_chatbot_greeting', function($greeting, $persona) {
    return "Dobrodošli! Ja sam {$persona} i tu sam da vam pomognem!";
}, 10, 2);
```

## Performance Optimization

### Best Practices:
1. Use specific shortcodes instead of always using the full chatbot
2. Limit the number of products shown in product widgets
3. Set appropriate refresh intervals for stats widgets
4. Use caching plugins for better performance

### Caching:
The plugin includes built-in caching for:
- Product data
- Exchange rates
- Bot responses
- XML feed data

## Security Features

- Nonce verification for all AJAX requests
- Input sanitization and validation
- SQL injection protection
- XSS prevention
- CSRF protection

## Browser Support

The plugin supports all modern browsers:
- Chrome 60+
- Firefox 55+
- Safari 11+
- Edge 79+
- Opera 47+

## Changelog

### Version 1.0.0
- Initial release with full shortcode support
- Complete chatbot functionality
- Admin interface
- XML integration
- Email system
- Multi-persona support