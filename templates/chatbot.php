<div class="chat-container">
    <!-- Header -->
    <div class="chat-header">
        <div class="header-avatar">
            <?php if (!empty($atts['persona_image'])): ?>
                <img src="<?php echo esc_url($atts['persona_image']); ?>" alt="<?php echo esc_attr($atts['persona']); ?>" class="persona-image">
            <?php else: ?>
                <img src="<?php echo esc_url(GCC_PLUGIN_URL . 'assets/images/ai-agent-default.webp'); ?>" alt="<?php echo esc_attr($atts['persona']); ?>" class="persona-image">
            <?php endif; ?>
        </div>
        <div class="header-info">
            <h1><?php echo esc_html($atts['persona']); ?></h1>
            <p>Uvek na usluzi i spremna da pomogne</p>
        </div>
    </div>

    <!-- Messages Container -->
    <div class="messages-container" id="gcc-chatbot-messages">
        <!-- AI Message (Left) -->
        <div class="message-wrapper ai-message">
            <div class="avatar">
                <?php if (!empty($atts['persona_image'])): ?>
                    <img src="<?php echo esc_url($atts['persona_image']); ?>" alt="<?php echo esc_attr($atts['persona']); ?>" class="persona-image">
                <?php else: ?>
                    <img src="<?php echo esc_url(GCC_PLUGIN_URL . 'assets/images/ai-agent-default.webp'); ?>" alt="<?php echo esc_attr($atts['persona']); ?>" class="persona-image">
                <?php endif; ?>
            </div>
            <div class="message-content">
                <div class="message-bubble ai-bubble">
                    <p class="persona-greeting"><?php echo wp_kses_post($atts['persona_greeting']); ?></p>
                </div>
                <div class="message-time"></div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Answers Container - kept for compatibility but not shown -->
<div class="answers-container" id="gcc-answers-container" style="display: none;">
    <div class="predefined-answers" id="gcc-predefined-answers"></div>
    <div id="gcc-step-container"></div>
</div>

<!-- Products Container (Outside chat container) -->
<div class="products-container" id="gcc-products-container" style="display: none;">
    <!-- Products will be dynamically loaded here -->
</div>

<!-- Hidden elements for dynamic content -->
<div id="gcc-templates" style="display: none;">

    <!-- Budget Selection Template -->
    <div id="gcc-budget-template">
        <div class="gcc-step-content">
            <p class="gcc-step-question">Koliki je vaš budžet za investiciju u zlato?</p>
            <div class="gcc-budget-options">
                <button class="gcc-budget-btn" data-budget="1000" data-display="€1,000">€1,000</button>
                <button class="gcc-budget-btn" data-budget="2500" data-display="€2,500">€2,500</button>
                <button class="gcc-budget-btn" data-budget="5000" data-display="€5,000">€5,000</button>
                <button class="gcc-budget-btn" data-budget="10000" data-display="€10,000">€10,000</button>
                <button class="gcc-budget-btn" data-budget="20000" data-display="€20,000">€20,000</button>
                <button class="gcc-budget-btn" data-budget="50000" data-display="€50,000+">€50,000+</button>
            </div>
            <div class="gcc-exchange-info">
                <small>Kurs: EUR/RSD 117.50</small>
            </div>
        </div>
    </div>

    <!-- Product Type Selection Template -->
    <div id="gcc-product-type-template">
        <div class="gcc-step-content">
            <p class="gcc-step-question">Kakav tip zlata preferirate?</p>
            <div class="gcc-product-type-options">
                <button class="gcc-product-type-btn" data-type="bars">
                    <div class="gcc-option-icon">🟫</div>
                    <div class="gcc-option-text">
                        <strong>Više zlatnih poluga</strong>
                        <small>Klasične zlatne poluge</small>
                    </div>
                </button>
                <button class="gcc-product-type-btn" data-type="ducats">
                    <div class="gcc-option-icon">🪙</div>
                    <div class="gcc-option-text">
                        <strong>Više zlatnih dukata</strong>
                        <small>Tradicionalni zlatni dukati</small>
                    </div>
                </button>
                <button class="gcc-product-type-btn" data-type="combo">
                    <div class="gcc-option-icon">🎯</div>
                    <div class="gcc-option-text">
                        <strong>Pola dukati, a pola poluge</strong>
                        <small>Pola dukati, a pola poluge</small>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Weight Preference Template -->
    <div id="gcc-weight-preference-template">
        <div class="gcc-step-content">
            <p class="gcc-step-question">Preferirate li:</p>
            <div class="gcc-weight-options">
                <button class="gcc-weight-btn" data-weight="lighter">
                    <div class="gcc-option-icon">⚖️</div>
                    <div class="gcc-option-text">
                        <strong>Više lakših poluga</strong>
                        <small>Veća likvidnost</small>
                    </div>
                </button>
                <button class="gcc-weight-btn" data-weight="heavier">
                    <div class="gcc-option-icon">🏋️</div>
                    <div class="gcc-option-text">
                        <strong>Manje težih poluga</strong>
                        <small>Niža premija</small>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Delivery Method Template -->
    <div id="gcc-delivery-method-template">
        <div class="gcc-step-content">
            <p class="gcc-step-question">Kako biste želeli da primite zlato?</p>
            <div class="gcc-delivery-options">
                <button class="gcc-delivery-btn" data-method="stock">
                    <div class="gcc-option-icon">🏪</div>
                    <div class="gcc-option-text">
                        <strong>Sa stanja</strong>
                        <small>Dostupno odmah, viša cena</small>
                    </div>
                </button>
                <button class="gcc-delivery-btn" data-method="advance">
                    <div class="gcc-option-icon">⏰</div>
                    <div class="gcc-option-text">
                        <strong>Avansna isplata</strong>
                        <small>100% unapred, ~10 dana, niža cena</small>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Product Selection Template -->
    <div id="gcc-product-selection-template">
        <div class="gcc-step-content">
            <p class="gcc-step-question">Evo predloga za vaš budžet:</p>
            <div id="gcc-product-list">
                <!-- Products will be loaded here -->
            </div>
            <div class="gcc-total-section">
                <div class="gcc-total-value">
                    <strong>Ukupno: <span id="gcc-total-amount">€0</span></strong>
                    <small>(<span id="gcc-total-rsd">0 RSD</span>)</small>
                </div>
            </div>
            <div class="gcc-cta-options">
                <button id="gcc-email-quote-btn" class="gcc-cta-btn gcc-primary">
                    📧 Pošaljite mi ponudu na email
                </button>
                <button id="gcc-call-trader-btn" class="gcc-cta-btn gcc-secondary">
                    📞 Neka me pozove trgovac
                </button>
                <button id="gcc-start-over-btn" class="gcc-cta-btn gcc-tertiary">
                    🔄 Počni ispočetka
                </button>
            </div>
        </div>
    </div>

    <!-- Quote Form Template -->
    <div id="gcc-quote-form-template">
        <div class="gcc-step-content">
            <p class="gcc-step-question">Molimo unesite vaše podatke:</p>
            <form id="gcc-quote-form">
                <div class="gcc-form-row">
                    <div class="gcc-form-group">
                        <label for="gcc-name">Ime i prezime *</label>
                        <input type="text" id="gcc-name" name="name" required>
                    </div>
                    <div class="gcc-form-group">
                        <label for="gcc-email">Email *</label>
                        <input type="email" id="gcc-email" name="email" required>
                    </div>
                </div>
                <div class="gcc-form-row">
                    <div class="gcc-form-group">
                        <label for="gcc-phone">Telefon *</label>
                        <input type="tel" id="gcc-phone" name="phone" required>
                    </div>
                </div>
                <div class="gcc-form-group">
                    <label for="gcc-message">Poruka (opciono)</label>
                    <textarea id="gcc-message" name="message" rows="3" placeholder="Dodatne napomene..."></textarea>
                </div>
                <div class="gcc-form-actions">
                    <button type="submit" class="gcc-submit-btn gcc-primary">
                        <span class="gcc-submit-text">Pošalji zahtev</span>
                        <span class="gcc-submit-loader" style="display: none;">⏳</span>
                    </button>
                    <button type="button" id="gcc-cancel-form-btn" class="gcc-cancel-btn gcc-secondary">
                        Otkaži
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Message Template -->
    <div id="gcc-success-template">
        <div class="gcc-step-content gcc-success-content">
            <div class="gcc-success-icon">✅</div>
            <h3>Zahtev je uspešno poslat!</h3>
            <p>Vaš broj tiketa: <strong id="gcc-ticket-number"></strong></p>
            <p>Uskoro ćete dobiti email sa detaljnom ponudom.</p>
            <div class="gcc-success-actions">
                <button id="gcc-new-calculation-btn" class="gcc-cta-btn gcc-primary">
                    Napravi novi proračun
                </button>
            </div>
        </div>
    </div>

    <!-- High Budget Template -->
    <div id="gcc-high-budget-template">
        <div class="gcc-step-content">
            <div class="gcc-high-budget-message">
                <div class="gcc-vip-icon">👑</div>
                <h3>Za veće investicije preporučujemo direktan razgovor sa trejderom.</h3>
                <p>Naš tim eksperata će vam pružiti personalizovanu konsultaciju i najbolje uslove.</p>
            </div>
            <div class="gcc-vip-options">
                <button id="gcc-schedule-meeting-btn" class="gcc-cta-btn gcc-primary">
                    📅 Zakažite sastanak
                </button>
                <button id="gcc-continue-calculation-btn" class="gcc-cta-btn gcc-secondary">
                    Nastavi sa kalkulacijom
                </button>
            </div>
        </div>
    </div>

</div>
</div>