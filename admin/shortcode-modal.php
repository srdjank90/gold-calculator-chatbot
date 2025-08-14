<!-- Gold Calculator Shortcode Modal -->
<div id="gcc-shortcode-modal" style="display: none;">
    <div class="gcc-modal-overlay" onclick="gccCloseShortcodeModal()"></div>
    <div class="gcc-modal-content">
        <div class="gcc-modal-header">
            <h3>Gold Calculator Shortcodes</h3>
            <button class="gcc-modal-close" onclick="gccCloseShortcodeModal()">&times;</button>
        </div>
        
        <div class="gcc-modal-body">
            <div class="gcc-shortcode-tabs">
                <button class="gcc-tab-btn gcc-active" onclick="gccShowShortcodeTab('chatbot')">Full Chatbot</button>
                <button class="gcc-tab-btn" onclick="gccShowShortcodeTab('compact')">Compact</button>
                <button class="gcc-tab-btn" onclick="gccShowShortcodeTab('inline')">Inline</button>
                <button class="gcc-tab-btn" onclick="gccShowShortcodeTab('button')">Button</button>
                <button class="gcc-tab-btn" onclick="gccShowShortcodeTab('stats')">Stats</button>
                <button class="gcc-tab-btn" onclick="gccShowShortcodeTab('products')">Products</button>
            </div>
            
            <!-- Full Chatbot Tab -->
            <div id="gcc-tab-chatbot" class="gcc-tab-content gcc-active">
                <h4>Full Chatbot Interface</h4>
                <p>Complete chatbot with all features including conversation flow, product selection, and quote generation.</p>
                
                <div class="gcc-form-grid">
                    <div class="gcc-form-group">
                        <label>Bot Persona</label>
                        <select id="chatbot-persona">
                            <option value="ZLATIJA">ZLATIJA</option>
                            <option value="ZLATA">ZLATA</option>
                            <option value="ZLATKA">ZLATKA</option>
                            <option value="ZLATISLAVA">ZLATISLAVA</option>
                        </select>
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Theme</label>
                        <select id="chatbot-theme">
                            <option value="light">Light</option>
                            <option value="dark">Dark</option>
                            <option value="gold">Gold</option>
                        </select>
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Width</label>
                        <input type="text" id="chatbot-width" value="100%" placeholder="600px, 100%, etc.">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Height</label>
                        <input type="text" id="chatbot-height" value="500px" placeholder="500px, 80vh, etc.">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Position</label>
                        <select id="chatbot-position">
                            <option value="center">Center</option>
                            <option value="left">Left</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Button Color</label>
                        <input type="color" id="chatbot-button-color" value="#f4d03f">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Text Color</label>
                        <input type="color" id="chatbot-text-color" value="#2c3e50">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Background Color</label>
                        <input type="color" id="chatbot-background-color" value="#ffffff">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>
                            <input type="checkbox" id="chatbot-show-header" checked>
                            Show Header
                        </label>
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>
                            <input type="checkbox" id="chatbot-auto-start">
                            Auto Start
                        </label>
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>
                            <input type="checkbox" id="chatbot-shadow" checked>
                            Show Shadow
                        </label>
                    </div>
                    
                    <div class="gcc-form-group gcc-full-width">
                        <label>Custom Greeting (optional)</label>
                        <textarea id="chatbot-greeting" placeholder="Custom greeting message..."></textarea>
                    </div>
                </div>
                
                <div class="gcc-preview-area">
                    <h5>Preview:</h5>
                    <div class="gcc-shortcode-preview" id="chatbot-preview">
                        [gold_calculator_chatbot]
                    </div>
                </div>
            </div>
            
            <!-- Compact Tab -->
            <div id="gcc-tab-compact" class="gcc-tab-content">
                <h4>Compact Chatbot</h4>
                <p>Smaller version with avatar and start button.</p>
                
                <div class="gcc-form-grid">
                    <div class="gcc-form-group">
                        <label>Bot Persona</label>
                        <select id="compact-persona">
                            <option value="ZLATIJA">ZLATIJA</option>
                            <option value="ZLATA">ZLATA</option>
                            <option value="ZLATKA">ZLATKA</option>
                            <option value="ZLATISLAVA">ZLATISLAVA</option>
                        </select>
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Button Text</label>
                        <input type="text" id="compact-button-text" value="Započni konsultaciju">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Button Color</label>
                        <input type="color" id="compact-button-color" value="#f4d03f">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Text Color</label>
                        <input type="color" id="compact-text-color" value="#2c3e50">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Alignment</label>
                        <select id="compact-alignment">
                            <option value="center">Center</option>
                            <option value="left">Left</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>
                            <input type="checkbox" id="compact-show-avatar" checked>
                            Show Avatar
                        </label>
                    </div>
                </div>
                
                <div class="gcc-preview-area">
                    <h5>Preview:</h5>
                    <div class="gcc-shortcode-preview" id="compact-preview">
                        [gold_calculator_compact]
                    </div>
                </div>
            </div>
            
            <!-- Inline Tab -->
            <div id="gcc-tab-inline" class="gcc-tab-content">
                <h4>Inline Chatbot</h4>
                <p>Embedded in content with custom title and description.</p>
                
                <div class="gcc-form-grid">
                    <div class="gcc-form-group">
                        <label>Title</label>
                        <input type="text" id="inline-title" value="Investicija u zlato">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Description</label>
                        <input type="text" id="inline-description" value="Pronađite najbolji paket zlata za vaš budžet">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Button Text</label>
                        <input type="text" id="inline-button-text" value="Počni">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Background Color</label>
                        <input type="color" id="inline-background-color" value="#f8f9fa">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Border Color</label>
                        <input type="color" id="inline-border-color" value="#f4d03f">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Text Color</label>
                        <input type="color" id="inline-text-color" value="#2c3e50">
                    </div>
                </div>
                
                <div class="gcc-preview-area">
                    <h5>Preview:</h5>
                    <div class="gcc-shortcode-preview" id="inline-preview">
                        [gold_calculator_inline]
                    </div>
                </div>
            </div>
            
            <!-- Button Tab -->
            <div id="gcc-tab-button" class="gcc-tab-content">
                <h4>Button Shortcode</h4>
                <p>Simple button that opens chatbot in modal or redirects to full page.</p>
                
                <div class="gcc-form-grid">
                    <div class="gcc-form-group">
                        <label>Button Text</label>
                        <input type="text" id="button-text" value="Konsultacija o zlatu">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Size</label>
                        <select id="button-size">
                            <option value="small">Small</option>
                            <option value="medium" selected>Medium</option>
                            <option value="large">Large</option>
                        </select>
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Style</label>
                        <select id="button-style">
                            <option value="primary" selected>Primary</option>
                            <option value="secondary">Secondary</option>
                            <option value="success">Success</option>
                            <option value="warning">Warning</option>
                        </select>
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Icon</label>
                        <select id="button-icon">
                            <option value="chat" selected>Chat</option>
                            <option value="gold">Gold</option>
                            <option value="money">Money</option>
                            <option value="calculator">Calculator</option>
                            <option value="phone">Phone</option>
                            <option value="email">Email</option>
                        </select>
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Position</label>
                        <select id="button-position">
                            <option value="left">Left</option>
                            <option value="center" selected>Center</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>
                            <input type="checkbox" id="button-modal" checked>
                            Open in Modal
                        </label>
                    </div>
                </div>
                
                <div class="gcc-preview-area">
                    <h5>Preview:</h5>
                    <div class="gcc-shortcode-preview" id="button-preview">
                        [gold_calculator_button]
                    </div>
                </div>
            </div>
            
            <!-- Stats Tab -->
            <div id="gcc-tab-stats" class="gcc-tab-content">
                <h4>Stats Widget</h4>
                <p>Shows current gold prices and statistics.</p>
                
                <div class="gcc-form-grid">
                    <div class="gcc-form-group">
                        <label>Title</label>
                        <input type="text" id="stats-title" value="Trenutne cene zlata">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Layout</label>
                        <select id="stats-layout">
                            <option value="horizontal" selected>Horizontal</option>
                            <option value="vertical">Vertical</option>
                        </select>
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Refresh Interval (seconds)</label>
                        <input type="number" id="stats-refresh" value="30" min="0">
                    </div>
                    
                    <div class="gcc-form-group gcc-full-width">
                        <label>Show Items</label>
                        <div class="gcc-checkbox-group">
                            <label><input type="checkbox" id="stats-show-price" checked> Price</label>
                            <label><input type="checkbox" id="stats-show-exchange" checked> Exchange Rate</label>
                            <label><input type="checkbox" id="stats-show-products" checked> Products Count</label>
                        </div>
                    </div>
                </div>
                
                <div class="gcc-preview-area">
                    <h5>Preview:</h5>
                    <div class="gcc-shortcode-preview" id="stats-preview">
                        [gold_calculator_stats]
                    </div>
                </div>
            </div>
            
            <!-- Products Tab -->
            <div id="gcc-tab-products" class="gcc-tab-content">
                <h4>Products Widget</h4>
                <p>Displays gold products in a grid layout.</p>
                
                <div class="gcc-form-grid">
                    <div class="gcc-form-group">
                        <label>Product Type</label>
                        <select id="products-type">
                            <option value="all" selected>All</option>
                            <option value="bar">Bars Only</option>
                            <option value="ducat">Ducats Only</option>
                        </select>
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Limit</label>
                        <input type="number" id="products-limit" value="6" min="1" max="50">
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Layout</label>
                        <select id="products-layout">
                            <option value="grid" selected>Grid</option>
                            <option value="list">List</option>
                        </select>
                    </div>
                    
                    <div class="gcc-form-group">
                        <label>Columns</label>
                        <input type="number" id="products-columns" value="3" min="1" max="6">
                    </div>
                    
                    <div class="gcc-form-group gcc-full-width">
                        <label>Show Information</label>
                        <div class="gcc-checkbox-group">
                            <label><input type="checkbox" id="products-show-price" checked> Price</label>
                            <label><input type="checkbox" id="products-show-weight" checked> Weight</label>
                            <label><input type="checkbox" id="products-show-type" checked> Type</label>
                        </div>
                    </div>
                </div>
                
                <div class="gcc-preview-area">
                    <h5>Preview:</h5>
                    <div class="gcc-shortcode-preview" id="products-preview">
                        [gold_calculator_products]
                    </div>
                </div>
            </div>
        </div>
        
        <div class="gcc-modal-footer">
            <button class="button button-primary" onclick="gccInsertShortcode()">Insert Shortcode</button>
            <button class="button" onclick="gccCloseShortcodeModal()">Cancel</button>
        </div>
    </div>
</div>

<style>
#gcc-shortcode-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
}

.gcc-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.gcc-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.gcc-modal-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
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
    color: #7f8c8d;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.gcc-modal-close:hover {
    color: #2c3e50;
}

.gcc-modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
}

.gcc-shortcode-tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
    gap: 5px;
}

.gcc-tab-btn {
    padding: 10px 15px;
    border: none;
    background: #f8f9fa;
    cursor: pointer;
    border-radius: 4px 4px 0 0;
    transition: all 0.3s ease;
}

.gcc-tab-btn:hover {
    background: #e9ecef;
}

.gcc-tab-btn.gcc-active {
    background: #f4d03f;
    color: #2c3e50;
}

.gcc-tab-content {
    display: none;
}

.gcc-tab-content.gcc-active {
    display: block;
}

.gcc-tab-content h4 {
    margin-top: 0;
    color: #2c3e50;
}

.gcc-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.gcc-form-group {
    display: flex;
    flex-direction: column;
}

.gcc-form-group.gcc-full-width {
    grid-column: 1 / -1;
}

.gcc-form-group label {
    margin-bottom: 5px;
    font-weight: 500;
    color: #2c3e50;
}

.gcc-form-group input,
.gcc-form-group select,
.gcc-form-group textarea {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.gcc-form-group input:focus,
.gcc-form-group select:focus,
.gcc-form-group textarea:focus {
    outline: none;
    border-color: #f4d03f;
    box-shadow: 0 0 0 2px rgba(244, 208, 63, 0.2);
}

.gcc-checkbox-group {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.gcc-checkbox-group label {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 0;
    font-weight: normal;
}

.gcc-preview-area {
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    margin-top: 20px;
}

.gcc-preview-area h5 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.gcc-shortcode-preview {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    font-family: monospace;
    font-size: 14px;
    color: #d63384;
    word-break: break-all;
}

.gcc-modal-footer {
    padding: 20px;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    background: #f8f9fa;
}

@media (max-width: 768px) {
    .gcc-modal-content {
        width: 95%;
        max-height: 95vh;
    }
    
    .gcc-form-grid {
        grid-template-columns: 1fr;
    }
    
    .gcc-shortcode-tabs {
        flex-wrap: wrap;
    }
    
    .gcc-tab-btn {
        font-size: 12px;
        padding: 8px 10px;
    }
}
</style>

<script>
function gccOpenShortcodeModal() {
    document.getElementById('gcc-shortcode-modal').style.display = 'block';
    gccUpdatePreview('chatbot');
}

function gccCloseShortcodeModal() {
    document.getElementById('gcc-shortcode-modal').style.display = 'none';
}

function gccShowShortcodeTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.gcc-tab-content').forEach(tab => {
        tab.classList.remove('gcc-active');
    });
    
    document.querySelectorAll('.gcc-tab-btn').forEach(btn => {
        btn.classList.remove('gcc-active');
    });
    
    // Show selected tab
    document.getElementById('gcc-tab-' + tabName).classList.add('gcc-active');
    event.target.classList.add('gcc-active');
    
    // Update preview
    gccUpdatePreview(tabName);
}

function gccUpdatePreview(type) {
    let shortcode = '';
    
    switch(type) {
        case 'chatbot':
            shortcode = gccBuildChatbotShortcode();
            break;
        case 'compact':
            shortcode = gccBuildCompactShortcode();
            break;
        case 'inline':
            shortcode = gccBuildInlineShortcode();
            break;
        case 'button':
            shortcode = gccBuildButtonShortcode();
            break;
        case 'stats':
            shortcode = gccBuildStatsShortcode();
            break;
        case 'products':
            shortcode = gccBuildProductsShortcode();
            break;
    }
    
    document.getElementById(type + '-preview').textContent = shortcode;
}

function gccBuildChatbotShortcode() {
    let attrs = [];
    
    const persona = document.getElementById('chatbot-persona').value;
    if (persona !== 'ZLATIJA') attrs.push(`persona="${persona}"`);
    
    const theme = document.getElementById('chatbot-theme').value;
    if (theme !== 'light') attrs.push(`theme="${theme}"`);
    
    const width = document.getElementById('chatbot-width').value;
    if (width !== '100%') attrs.push(`width="${width}"`);
    
    const height = document.getElementById('chatbot-height').value;
    if (height !== '500px') attrs.push(`height="${height}"`);
    
    const position = document.getElementById('chatbot-position').value;
    if (position !== 'center') attrs.push(`position="${position}"`);
    
    const buttonColor = document.getElementById('chatbot-button-color').value;
    if (buttonColor !== '#f4d03f') attrs.push(`button_color="${buttonColor}"`);
    
    const textColor = document.getElementById('chatbot-text-color').value;
    if (textColor !== '#2c3e50') attrs.push(`text_color="${textColor}"`);
    
    const backgroundColor = document.getElementById('chatbot-background-color').value;
    if (backgroundColor !== '#ffffff') attrs.push(`background_color="${backgroundColor}"`);
    
    const showHeader = document.getElementById('chatbot-show-header').checked;
    if (!showHeader) attrs.push('show_header="false"');
    
    const autoStart = document.getElementById('chatbot-auto-start').checked;
    if (autoStart) attrs.push('auto_start="true"');
    
    const shadow = document.getElementById('chatbot-shadow').checked;
    if (!shadow) attrs.push('shadow="false"');
    
    const greeting = document.getElementById('chatbot-greeting').value;
    if (greeting) attrs.push(`custom_greeting="${greeting}"`);
    
    return `[gold_calculator_chatbot${attrs.length ? ' ' + attrs.join(' ') : ''}]`;
}

function gccBuildCompactShortcode() {
    let attrs = [];
    
    const persona = document.getElementById('compact-persona').value;
    if (persona !== 'ZLATIJA') attrs.push(`persona="${persona}"`);
    
    const buttonText = document.getElementById('compact-button-text').value;
    if (buttonText !== 'Započni konsultaciju') attrs.push(`button_text="${buttonText}"`);
    
    const buttonColor = document.getElementById('compact-button-color').value;
    if (buttonColor !== '#f4d03f') attrs.push(`button_color="${buttonColor}"`);
    
    const textColor = document.getElementById('compact-text-color').value;
    if (textColor !== '#2c3e50') attrs.push(`text_color="${textColor}"`);
    
    const alignment = document.getElementById('compact-alignment').value;
    if (alignment !== 'center') attrs.push(`alignment="${alignment}"`);
    
    const showAvatar = document.getElementById('compact-show-avatar').checked;
    if (!showAvatar) attrs.push('show_avatar="false"');
    
    return `[gold_calculator_compact${attrs.length ? ' ' + attrs.join(' ') : ''}]`;
}

function gccBuildInlineShortcode() {
    let attrs = [];
    
    const title = document.getElementById('inline-title').value;
    if (title !== 'Investicija u zlato') attrs.push(`title="${title}"`);
    
    const description = document.getElementById('inline-description').value;
    if (description !== 'Pronađite najbolji paket zlata za vaš budžet') attrs.push(`description="${description}"`);
    
    const buttonText = document.getElementById('inline-button-text').value;
    if (buttonText !== 'Počni') attrs.push(`button_text="${buttonText}"`);
    
    const backgroundColor = document.getElementById('inline-background-color').value;
    if (backgroundColor !== '#f8f9fa') attrs.push(`background_color="${backgroundColor}"`);
    
    const borderColor = document.getElementById('inline-border-color').value;
    if (borderColor !== '#f4d03f') attrs.push(`border_color="${borderColor}"`);
    
    const textColor = document.getElementById('inline-text-color').value;
    if (textColor !== '#2c3e50') attrs.push(`text_color="${textColor}"`);
    
    return `[gold_calculator_inline${attrs.length ? ' ' + attrs.join(' ') : ''}]`;
}

function gccBuildButtonShortcode() {
    let attrs = [];
    
    const text = document.getElementById('button-text').value;
    if (text !== 'Konsultacija o zlatu') attrs.push(`text="${text}"`);
    
    const size = document.getElementById('button-size').value;
    if (size !== 'medium') attrs.push(`size="${size}"`);
    
    const style = document.getElementById('button-style').value;
    if (style !== 'primary') attrs.push(`style="${style}"`);
    
    const icon = document.getElementById('button-icon').value;
    if (icon !== 'chat') attrs.push(`icon="${icon}"`);
    
    const position = document.getElementById('button-position').value;
    if (position !== 'center') attrs.push(`position="${position}"`);
    
    const modal = document.getElementById('button-modal').checked;
    if (!modal) attrs.push('modal="false"');
    
    return `[gold_calculator_button${attrs.length ? ' ' + attrs.join(' ') : ''}]`;
}

function gccBuildStatsShortcode() {
    let attrs = [];
    
    const title = document.getElementById('stats-title').value;
    if (title !== 'Trenutne cene zlata') attrs.push(`title="${title}"`);
    
    const layout = document.getElementById('stats-layout').value;
    if (layout !== 'horizontal') attrs.push(`layout="${layout}"`);
    
    const refresh = document.getElementById('stats-refresh').value;
    if (refresh !== '30') attrs.push(`refresh_interval="${refresh}"`);
    
    let showItems = [];
    if (document.getElementById('stats-show-price').checked) showItems.push('price');
    if (document.getElementById('stats-show-exchange').checked) showItems.push('exchange');
    if (document.getElementById('stats-show-products').checked) showItems.push('products');
    
    if (showItems.join(',') !== 'price,exchange,products') {
        attrs.push(`show="${showItems.join(',')}"`);
    }
    
    return `[gold_calculator_stats${attrs.length ? ' ' + attrs.join(' ') : ''}]`;
}

function gccBuildProductsShortcode() {
    let attrs = [];
    
    const type = document.getElementById('products-type').value;
    if (type !== 'all') attrs.push(`type="${type}"`);
    
    const limit = document.getElementById('products-limit').value;
    if (limit !== '6') attrs.push(`limit="${limit}"`);
    
    const layout = document.getElementById('products-layout').value;
    if (layout !== 'grid') attrs.push(`layout="${layout}"`);
    
    const columns = document.getElementById('products-columns').value;
    if (columns !== '3') attrs.push(`columns="${columns}"`);
    
    const showPrice = document.getElementById('products-show-price').checked;
    if (!showPrice) attrs.push('show_price="false"');
    
    const showWeight = document.getElementById('products-show-weight').checked;
    if (!showWeight) attrs.push('show_weight="false"');
    
    const showType = document.getElementById('products-show-type').checked;
    if (!showType) attrs.push('show_type="false"');
    
    return `[gold_calculator_products${attrs.length ? ' ' + attrs.join(' ') : ''}]`;
}

function gccInsertShortcode() {
    const activeTab = document.querySelector('.gcc-tab-content.gcc-active');
    const shortcode = activeTab.querySelector('.gcc-shortcode-preview').textContent;
    
    // Insert into WordPress editor
    if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
        wp.media.editor.insert(shortcode);
    } else if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor) {
        tinyMCE.activeEditor.insertContent(shortcode);
    } else {
        // Fallback for text editor
        const editor = document.getElementById('content');
        if (editor) {
            editor.value += shortcode;
        }
    }
    
    gccCloseShortcodeModal();
}

// Update previews when form fields change
document.addEventListener('DOMContentLoaded', function() {
    const formElements = document.querySelectorAll('#gcc-shortcode-modal input, #gcc-shortcode-modal select, #gcc-shortcode-modal textarea');
    
    formElements.forEach(element => {
        element.addEventListener('change', function() {
            const activeTab = document.querySelector('.gcc-tab-btn.gcc-active');
            if (activeTab) {
                const tabName = activeTab.onclick.toString().match(/gccShowShortcodeTab\('([^']+)'\)/)[1];
                gccUpdatePreview(tabName);
            }
        });
    });
});
</script>