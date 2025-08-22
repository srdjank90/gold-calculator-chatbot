<?php

class GCC_Shortcodes
{

    public function __construct()
    {
        add_shortcode('gold_calculator_chatbot', array($this, 'chatbot_shortcode'));

        // Add AJAX handlers matching gold-suggestions plugin
        add_action('wp_ajax_calculate_gold_suggestions', array($this, 'calculate_gold_suggestions'));
        add_action('wp_ajax_nopriv_calculate_gold_suggestions', array($this, 'calculate_gold_suggestions'));
        add_action('wp_ajax_add_to_cart_and_view', array($this, 'add_to_cart_and_view'));
        add_action('wp_ajax_nopriv_add_to_cart_and_view', array($this, 'add_to_cart_and_view'));
        add_action('wp_ajax_gcc_submit_contact', array($this, 'handle_submit'));
        add_action('wp_ajax_nopriv_gcc_submit_contact', array($this, 'handle_submit'));
    }

    /**
     * Main chatbot shortcode - Exact copy of gold-suggestions structure
     * Usage: [gold_calculator_chatbot]
     */
    public function chatbot_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'width' => '100%',
            'height' => '500px'
        ), $atts);

        // Get random persona from database
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();
        $persona_data = $database->get_random_active_persona();

        // Fallback to default if no active personas found
        if (!$persona_data) {
            $current_persona = 'ZLATIJA';
            $persona_greeting = 'Zdravo! Ja sam ZLATIJA – vaš vodič kroz svet investicionog zlata. Hajde da pronađemo najbolji paket zlata za vaš budžet! 💰';
            $persona_image = '';
        } else {
            $current_persona = $persona_data->name;
            $persona_greeting = $persona_data->greeting_message;
            $persona_image = $persona_data->image_url;
        }

        // Generate unique ID for this instance
        $chatbot_id = 'gold-suggestions';

        ob_start();

        // Set up attributes for the template
        $atts = array(
            'persona' => $current_persona,
            'persona_greeting' => $persona_greeting,
            'persona_image' => $persona_image,
            'width' => $atts['width'],
            'height' => $atts['height']
        );

        // Include the template
        include GCC_PLUGIN_PATH . 'templates/chatbot.php';

?>
        <script>
            // Pass data to JavaScript
            window.gold_suggestions_ajax = {
                ajax_url: '<?php echo admin_url("admin-ajax.php"); ?>',
                nonce: '<?php echo wp_create_nonce("gcc_nonce"); ?>',
                language: 'english',
                persona: '<?php echo esc_js($current_persona); ?>',
                persona_greeting: '<?php echo esc_js($persona_greeting); ?>',
                persona_image: '<?php echo esc_js($persona_image); ?>',
                user_avatar_image: '<?php echo esc_js(get_option("gcc_user_avatar_image", "")); ?>',
                budget_options: <?php
                                $budget_buckets = get_option('gcc_budget_buckets', array(
                                    array('value' => 1000, 'text' => '€1,000', 'level' => '1g'),
                                    array('value' => 2500, 'text' => '€2,500', 'level' => '2g+'),
                                    array('value' => 5000, 'text' => '€5,000', 'level' => '5g+'),
                                    array('value' => 10000, 'text' => '€10,000', 'level' => '10g+'),
                                    array('value' => 20000, 'text' => '€20,000', 'level' => '20g+'),
                                    array('value' => 50000, 'text' => '€50,000+', 'level' => '20g+')
                                ));
                                echo json_encode($budget_buckets);
                                ?>,
                plugin_url: '<?php echo esc_js(GCC_PLUGIN_URL); ?>'
            };
        </script>

        <script>
            window.gccChatbotData = window.gccChatbotData || {};
            window.gccChatbotData['<?php echo esc_attr($chatbot_id); ?>'] = {
                persona: '<?php echo esc_js($current_persona); ?>',
                currentStep: 'budget',
                selectedBudget: '',
                selectedType: '',
                selectedDelivery: ''
            };

            function gccAddMessage(chatId, message, isUser = false, showOptions = false, optionsData = null) {
                const messagesContainer = document.getElementById(chatId + '-messages');
                const messageDiv = document.createElement('div');
                messageDiv.className = 'gcc-message ' + (isUser ? 'gcc-user-message' : 'gcc-bot-message');

                if (isUser) {
                    messageDiv.innerHTML = '<div class="gcc-message-content"><p>' + message + '</p></div>';
                    messagesContainer.appendChild(messageDiv);
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                } else {
                    // Add typing animation for bot messages
                    const messageContent = document.createElement('div');
                    messageContent.className = 'gcc-message-content';
                    const paragraph = document.createElement('p');
                    messageContent.appendChild(paragraph);
                    messageDiv.appendChild(messageContent);
                    messagesContainer.appendChild(messageDiv);
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;

                    // Type the message character by character
                    gccTypeMessage(paragraph, message, 0, showOptions, optionsData, messageDiv);
                }
            }

            function gccTypeMessage(element, message, index = 0, showOptions = false, optionsData = null, messageDiv = null) {
                if (index < message.length) {
                    // Check if we're at the start of an HTML tag
                    if (message[index] === '<') {
                        // Find the end of the tag
                        const tagEnd = message.indexOf('>', index);
                        if (tagEnd !== -1) {
                            // Add the entire tag at once
                            element.innerHTML += message.substring(index, tagEnd + 1);
                            setTimeout(() => gccTypeMessage(element, message, tagEnd + 1, showOptions, optionsData, messageDiv), 10);
                            return;
                        }
                    }

                    element.innerHTML += message[index];
                    setTimeout(() => gccTypeMessage(element, message, index + 1, showOptions, optionsData, messageDiv), 30);
                } else if (showOptions && optionsData && messageDiv) {
                    // Add options after typing is complete
                    setTimeout(() => gccAddInlineOptions(messageDiv, optionsData), 500);
                }
            }

            function gccUpdateActions(chatId, buttons) {
                const actionsContainer = document.getElementById(chatId + '-actions');
                actionsContainer.innerHTML = buttons;
            }

            // New helper functions for inline options
            function gccAddInlineOptions(messageDiv, optionsData) {
                const optionsContainer = document.createElement('div');
                optionsContainer.className = 'gcc-inline-options';

                optionsData.options.forEach(option => {
                    const button = document.createElement('button');
                    button.className = 'gcc-inline-option-btn';
                    button.textContent = option.text;
                    button.onclick = () => {
                        if (optionsData.type === 'budget') {
                            gccSelectBudget('gold-suggestions', option.value, option.display);
                        } else if (optionsData.type === 'type') {
                            gccSelectType('gold-suggestions', option.value, option.display);
                        } else if (optionsData.type === 'delivery') {
                            gccSelectDelivery('gold-suggestions', option.value, option.display);
                        }
                    };
                    optionsContainer.appendChild(button);
                });

                messageDiv.appendChild(optionsContainer);
            }

            function gccHideAllOptions(chatId) {
                const messagesContainer = document.getElementById(chatId + '-messages');
                const optionsContainers = messagesContainer.querySelectorAll('.gcc-inline-options');
                optionsContainers.forEach(container => {
                    container.style.display = 'none';
                });
            }

            function gccSelectBudget(chatId, budget, budgetText) {
                window.gccChatbotData[chatId].selectedBudget = budget;
                window.gccChatbotData[chatId].currentStep = 'type';

                // Hide previous options
                gccHideAllOptions(chatId);

                gccAddMessage(chatId, budgetText, true);

                setTimeout(() => {
                    const typeOptions = [{
                            text: 'Više zlatnih poluga',
                            value: 'bar',
                            display: 'Više zlatnih poluga'
                        },
                        {
                            text: 'Više zlatnih dukata',
                            value: 'ducat',
                            display: 'Više zlatnih dukata'
                        },
                        {
                            text: 'Pola dukati, a pola poluge',
                            value: 'combo',
                            display: 'Pola dukati, a pola poluge'
                        }
                    ];
                    gccAddMessage(chatId, '🥈 Odlično! Sada da vidimo kakav tip zlata preferirate.', false, true, {
                        type: 'type',
                        options: typeOptions
                    });
                }, 1000);
            }

            function gccSelectType(chatId, type, typeText) {
                window.gccChatbotData[chatId].selectedType = type;
                window.gccChatbotData[chatId].currentStep = 'delivery';

                // Hide previous options
                gccHideAllOptions(chatId);

                gccAddMessage(chatId, typeText, true);

                setTimeout(() => {
                    const deliveryOptions = [{
                            text: 'Sa stanja',
                            value: 'stock',
                            display: 'Sa stanja'
                        },
                        {
                            text: 'Avansna isplata',
                            value: 'advance',
                            display: 'Avansna isplata'
                        }
                    ];
                    gccAddMessage(chatId, '🥉 Kako biste želeli da primite zlato?', false, true, {
                        type: 'delivery',
                        options: deliveryOptions
                    });
                }, 1000);
            }

            function gccSelectDelivery(chatId, delivery, deliveryText) {
                window.gccChatbotData[chatId].selectedDelivery = delivery;
                window.gccChatbotData[chatId].currentStep = 'products';

                // Hide previous options
                gccHideAllOptions(chatId);

                gccAddMessage(chatId, deliveryText, true);

                setTimeout(() => {
                    gccAddMessage(chatId, '📊 Odlično! Analiziram vaše preferencije i pronalazim najbolje proizvode...');
                    gccShowProductRecommendations(chatId);
                }, 1000);
            }


            function gccShowProductRecommendations(chatId) {
                const chatData = window.gccChatbotData[chatId];

                // Fetch product recommendations based on budget and type
                fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'gcc_get_products',
                            budget: chatData.selectedBudget,
                            type: chatData.selectedType,
                            delivery_method: chatData.selectedDelivery,
                            nonce: '<?php echo wp_create_nonce("gcc_nonce"); ?>'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data.length > 0) {
                            gccShowProductSelection(chatId, data.data);
                        } else {
                            gccAddMessage(chatId, 'Nažalost, nema dostupnih proizvoda za vaš budžet. Molimo kontaktirajte nas za alternativne opcije.');
                            gccShowContactModal(chatId);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching products:', error);
                        gccAddMessage(chatId, 'Greška pri učitavanju proizvoda. Molimo pokušajte ponovo.');
                        gccShowContactModal(chatId);
                    });
            }

            function gccShowProductSelection(chatId, products) {
                let productHtml = '<div class="gcc-product-selection">';
                productHtml += '<h4>📊 Preporučeni proizvodi za vaš budžet:</h4>';
                productHtml += '<div class="gcc-products-grid">';

                products.forEach((product, index) => {
                    const typeText = product.type === 'bar' ? 'Poluga' : 'Dukat';
                    productHtml += `
                    <div class="gcc-product-item" data-product-id="${product.id}">
                        <div class="gcc-product-info">
                            <h5>${product.name}</h5>
                            <p class="gcc-product-type">${typeText} - ${product.weight}</p>
                            <p class="gcc-product-price">€${parseFloat(product.final_price_eur || product.final_price).toFixed(2)}</p>
                            <p class="gcc-product-description">${product.description || 'Kvalitetno investiciono zlato'}</p>
                        </div>
                        <div class="gcc-product-actions">
                            <button class="gcc-confirm-btn" onclick="gccConfirmProduct('${chatId}', ${product.id})">Potvrdi proizvod</button>
                        </div>
                    </div>
                `;
                });

                productHtml += '</div>';
                productHtml += '<div class="gcc-calculator-actions">';
                productHtml += '<button class="gcc-reset-btn" onclick="gccResetCalculator(\'' + chatId + '\')">🔄 Resetuj kalkulator</button>';
                productHtml += '</div>';
                productHtml += '</div>';

                gccUpdateActions(chatId, productHtml);

                // Store product data
                window.gccChatbotData[chatId].availableProducts = products;
                window.gccChatbotData[chatId].selectedProduct = null;
            }

            function gccConfirmProduct(chatId, productId) {
                const chatData = window.gccChatbotData[chatId];
                const product = chatData.availableProducts.find(p => p.id == productId);

                if (!product) {
                    alert('Proizvod nije pronađen.');
                    return;
                }

                // Store selected product
                chatData.selectedProduct = {
                    id: productId,
                    name: product.name,
                    quantity: 1,
                    price: product.final_price,
                    total: product.final_price
                };

                // Add user message
                gccAddMessage(chatId, `Potvrđujem: ${product.name} - €${parseFloat(product.final_price_eur || product.final_price).toFixed(2)}`, true);

                setTimeout(() => {
                    gccAddMessage(chatId, 'Odlično! Da biste finalizovali narudžbu, molimo unesite vaše podatke:');
                    gccShowContactModal(chatId);
                }, 1000);
            }

            function gccResetCalculator(chatId) {
                // Reset all data
                window.gccChatbotData[chatId] = {
                    persona: window.gccChatbotData[chatId].persona,
                    currentStep: 'budget',
                    selectedBudget: '',
                    selectedType: '',
                    selectedDelivery: '',
                    selectedProduct: null,
                    availableProducts: []
                };

                // Clear messages
                const messagesContainer = document.getElementById(chatId + '-messages');
                messagesContainer.innerHTML = '';

                // Add initial messages
                gccAddMessage(chatId, `Zdravo! Ja sam <strong>${window.gccChatbotData[chatId].persona}</strong> – vaš vodič kroz svet investicionog zlata. Hajde da pronađemo najbolji paket zlata za vaš budžet! 💰`);

                setTimeout(() => {
                    gccShowBudgetOptions(chatId);
                }, 1000);
            }

            function gccShowBudgetOptions(chatId) {
                const budgetOptions = [{
                        text: '1.000€',
                        value: '1000',
                        display: '1.000€'
                    },
                    {
                        text: '2.500€',
                        value: '2500',
                        display: '2.500€'
                    },
                    {
                        text: '5.000€',
                        value: '5000',
                        display: '5.000€'
                    },
                    {
                        text: '10.000€',
                        value: '10000',
                        display: '10.000€'
                    },
                    {
                        text: '25.000€',
                        value: '25000',
                        display: '25.000€'
                    }
                ];

                gccAddMessage(chatId, '🥇 Koliki je vaš budžet za investiciju u zlato?', false, true, {
                    type: 'budget',
                    options: budgetOptions
                });
            }

            function gccShowContactForm(chatId) {
                const formHtml =
                    '<div class="gcc-contact-form">' +
                    '<form id="' + chatId + '-contact-form" onsubmit="gccSubmitContact(event, \'' + chatId + '\')">' +
                    '<div class="gcc-form-group">' +
                    '<label>Ime i prezime *</label>' +
                    '<input type="text" name="name" required>' +
                    '</div>' +
                    '<div class="gcc-form-group">' +
                    '<label>Email *</label>' +
                    '<input type="email" name="email" required>' +
                    '</div>' +
                    '<div class="gcc-form-group">' +
                    '<label>Telefon *</label>' +
                    '<input type="tel" name="phone" required>' +
                    '</div>' +
                    '<div class="gcc-form-group">' +
                    '<label>Komentar</label>' +
                    '<textarea name="comment" placeholder="Dodatne informacije..."></textarea>' +
                    '</div>' +
                    '<button type="submit" class="gcc-submit-btn">Pošalji zahtev</button>' +
                    '</form>' +
                    '</div>';

                gccUpdateActions(chatId, formHtml);
            }

            function gccShowContactModal(chatId) {
                const modalHtml =
                    '<div class="gcc-modal-overlay" id="' + chatId + '-modal-overlay">' +
                    '<div class="gcc-modal-content">' +
                    '<div class="gcc-modal-header">' +
                    '<h3>Unesite vaše podatke</h3>' +
                    '<button class="gcc-modal-close" onclick="gccCloseModal(\'' + chatId + '\')">&times;</button>' +
                    '</div>' +
                    '<div class="gcc-modal-body">' +
                    '<form id="' + chatId + '-contact-form" onsubmit="gccSubmitContact(event, \'' + chatId + '\')">' +
                    '<div class="gcc-form-group">' +
                    '<label>Ime i prezime *</label>' +
                    '<input type="text" name="name" required>' +
                    '</div>' +
                    '<div class="gcc-form-group">' +
                    '<label>Email *</label>' +
                    '<input type="email" name="email" required>' +
                    '</div>' +
                    '<div class="gcc-form-group">' +
                    '<label>Telefon *</label>' +
                    '<input type="tel" name="phone" required>' +
                    '</div>' +
                    '<div class="gcc-form-group">' +
                    '<label>Komentar</label>' +
                    '<textarea name="comment" placeholder="Dodatne informacije..."></textarea>' +
                    '</div>' +
                    '<button type="submit" class="gcc-submit-btn">Pošalji zahtev</button>' +
                    '</form>' +
                    '</div>' +
                    '</div>' +
                    '</div>';

                gccUpdateActions(chatId, modalHtml);
            }

            function gccCloseModal(chatId) {
                const modal = document.getElementById(chatId + '-modal-overlay');
                if (modal) {
                    modal.remove();
                }
            }

            function gccSubmitContact(event, chatId) {
                event.preventDefault();

                const form = event.target;
                const formData = new FormData(form);
                const submitBtn = form.querySelector('.gcc-submit-btn');

                submitBtn.disabled = true;
                submitBtn.textContent = 'Slanje...';

                // Add chatbot data
                const chatData = window.gccChatbotData[chatId];
                formData.append('action', 'gcc_submit_contact');
                formData.append('nonce', '<?php echo wp_create_nonce("gcc_submit_contact"); ?>');
                formData.append('budget', chatData.selectedBudget);
                formData.append('type', chatData.selectedType);
                formData.append('delivery', chatData.selectedDelivery);
                formData.append('persona', chatData.persona);
                formData.append('selected_products', JSON.stringify(chatData.selectedProduct ? [chatData.selectedProduct] : []));
                formData.append('total_amount', chatData.selectedProduct ? chatData.selectedProduct.total : 0);

                fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            gccCloseModal(chatId);
                            gccAddMessage(chatId, 'Hvala! Vaš zahtev je uspešno poslat. Uskoro ćemo vam se javiti sa detaljnom ponudom.');
                            gccUpdateActions(chatId, '<p style="text-align: center; color: #27ae60; font-weight: 500;">✅ Uspešno poslato!</p>');
                        } else {
                            alert('Greška: ' + (data.data ? data.data.message : 'Nepoznata greška'));
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Pošalji zahtev';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Došlo je do greške. Molimo pokušajte ponovo.');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Pošalji zahtev';
                    });
            }
        </script>
<?php
        return ob_get_clean();
    }

    /**
     * Handle contact form submission
     */
    public function handle_submit()
    {
        // Verify nonce only if it's provided
        if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
            if (!wp_verify_nonce($_POST['nonce'], 'gcc_nonce')) {
                wp_send_json_error(array('message' => 'Security check failed'));
            }
        }

        // Get database instance
        if (!class_exists('GCC_Database')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-database.php';
        }
        $database = new GCC_Database();

        // Prepare submit data
        $submit_data = array(
            'name' => sanitize_text_field($_POST['name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'comment' => sanitize_textarea_field($_POST['comment']),
            'budget' => sanitize_text_field($_POST['budget']),
            'type' => sanitize_text_field($_POST['type']),
            'delivery' => sanitize_text_field($_POST['delivery']),
            'persona' => sanitize_text_field($_POST['persona']),
            'selected_products' => json_decode(stripslashes($_POST['selected_products']), true),
            'total_amount' => floatval($_POST['total_amount'])
        );

        // Add additional context to comment
        $full_comment = "Budžet: " . $submit_data['budget'] . "\n";
        $full_comment .= "Tip zlata: " . $submit_data['type'] . "\n";
        $full_comment .= "Način isporuke: " . $submit_data['delivery'] . "\n";
        $full_comment .= "Persona: " . $submit_data['persona'] . "\n";

        // Add selected products information
        if (!empty($submit_data['selected_products'])) {
            $full_comment .= "Odabrani proizvodi:\n";
            foreach ($submit_data['selected_products'] as $product) {
                $full_comment .= "- " . $product['name'] . " (Količina: " . $product['quantity'] . ", Cena: €" . number_format($product['price'], 2) . ", Ukupno: €" . number_format($product['total'], 2) . ")\n";
            }
            $full_comment .= "Ukupna vrednost: " . number_format($submit_data['total_amount'], 2) . " RSD\n";
        }

        $full_comment .= "\nKomentar: " . $submit_data['comment'];

        $submit_data['comment'] = $full_comment;

        // Save to database
        $result = $database->save_submit($submit_data);

        if ($result) {
            // Send email notification (optional)
            $this->send_email_notification($submit_data);

            wp_send_json_success(array('message' => 'Submit saved successfully', 'id' => $result));
        } else {
            wp_send_json_error(array('message' => 'Failed to save submit'));
        }
    }

    /**
     * Send email notification
     */
    private function send_email_notification($data)
    {
        $admin_email = get_option('gcc_notification_email', get_option('admin_email'));
        $subject = 'Novi upit za zlato - ' . $data['name'];

        $message = "Novi upit za zlato:\n\n";
        $message .= "Ime: " . $data['name'] . "\n";
        $message .= "Email: " . $data['email'] . "\n";
        $message .= "Telefon: " . $data['phone'] . "\n";
        $message .= "Budžet: " . $data['budget'] . "\n";
        $message .= "Tip zlata: " . $data['type'] . "\n";
        $message .= "Način isporuke: " . $data['delivery'] . "\n";
        $message .= "Persona: " . $data['persona'] . "\n\n";
        $message .= "Komentar:\n" . $data['comment'];

        $email_method = get_option('gcc_email_method', 'wp_mail');

        switch ($email_method) {
            case 'sendgrid':
                $this->send_sendgrid_email($admin_email, $subject, $message);
                break;
            case 'mailtrap':
                $this->send_mailtrap_email($admin_email, $subject, $message);
                break;
            default:
                wp_mail($admin_email, $subject, $message);
                break;
        }
    }

    /**
     * Send email via SendGrid
     */
    private function send_sendgrid_email($to, $subject, $message)
    {
        $api_key = get_option('gcc_sendgrid_api_key');
        $sender_email = get_option('gcc_sendgrid_sender_email');

        if (empty($api_key) || empty($sender_email)) {
            // Fallback to wp_mail
            wp_mail($to, $subject, $message);
            return;
        }

        $url = 'https://api.sendgrid.com/v3/mail/send';

        $data = array(
            'personalizations' => array(
                array(
                    'to' => array(
                        array('email' => $to)
                    ),
                    'subject' => $subject
                )
            ),
            'from' => array(
                'email' => $sender_email
            ),
            'content' => array(
                array(
                    'type' => 'text/plain',
                    'value' => $message
                )
            )
        );

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data)
        ));

        if (is_wp_error($response)) {
            // Fallback to wp_mail
            wp_mail($to, $subject, $message);
        }
    }

    /**
     * Send email via Mailtrap
     */
    private function send_mailtrap_email($to, $subject, $message)
    {
        $username = get_option('gcc_mailtrap_username');
        $password = get_option('gcc_mailtrap_password');

        if (empty($username) || empty($password)) {
            error_log('GCC Mailtrap: Missing username or password');
            // Fallback to wp_mail
            wp_mail($to, $subject, $message);
            return;
        }

        // Configure PHPMailer for Mailtrap
        add_action('phpmailer_init', function ($phpmailer) use ($username, $password) {
            $phpmailer->isSMTP();
            $phpmailer->Host = 'sandbox.smtp.mailtrap.io';
            $phpmailer->SMTPAuth = true;
            $phpmailer->Port = 2525;
            $phpmailer->Username = $username;
            $phpmailer->Password = $password;
            $phpmailer->SMTPSecure = 'tls';
            $site_domain = parse_url(get_site_url(), PHP_URL_HOST);
            $phpmailer->From = 'noreply@' . $site_domain;
            $phpmailer->FromName = 'Gold Calculator';

            // Enable SMTP debugging for development
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $phpmailer->SMTPDebug = 2;
                $phpmailer->Debugoutput = function ($str, $level) {
                    error_log("SMTP Debug: $str");
                };
            }
        });

        $result = wp_mail($to, $subject, $message);

        if (!$result) {
            error_log('GCC Mailtrap: Failed to send email');
        } else {
            error_log('GCC Mailtrap: Email sent successfully');
        }

        return $result;
    }
}
