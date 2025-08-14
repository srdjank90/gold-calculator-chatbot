jQuery(document).ready(function ($) {
  // Chatbot state
  let chatbotState = {
    currentQuestion: 0,
    budget: 0,
    budgetDisplay: "",
    product_type: "",
    combo_percentage: 60,
    weight_preference: "",
    delivery_method: "",
    high_budget_action: "",
    selectedProducts: [],
    totalValue: 0,
    currency: "EUR",
    exchangeRate: 117.5,
    quoteType: "email",
    userAnswers: {},
  };

  // Questions will be loaded from database
  let questions = [];

  // Initialize chatbot
  function initChatbot() {
    updateMessageTime();

    // Add initial greeting with persona
    const persona =
      (typeof gold_suggestions_ajax !== "undefined" &&
        gold_suggestions_ajax.persona) ||
      (typeof gcc_ajax !== "undefined" && gcc_ajax.persona) ||
      "ZLATIJA";

    const greetingMessage =
      (typeof gold_suggestions_ajax !== "undefined" &&
        gold_suggestions_ajax.persona_greeting) ||
      (typeof gcc_ajax !== "undefined" && gcc_ajax.persona_greeting) ||
      `Hi, I'm ${persona} – your guide through the world of investment gold. Let's find the best gold package for your budget!`;

    // Add greeting to existing message or replace it
    const existingGreeting = $(".persona-greeting");
    if (existingGreeting.length) {
      existingGreeting.html(greetingMessage);
    }

    // Load all questions from database
    loadAllQuestionsFromDatabase();
  }

  // Load all questions from database at start
  function loadAllQuestionsFromDatabase() {
    $.ajax({
      url:
        (typeof gold_suggestions_ajax !== "undefined" &&
          gold_suggestions_ajax.ajax_url) ||
        (typeof gcc_ajax !== "undefined" && gcc_ajax.ajax_url) ||
        "/wp-admin/admin-ajax.php",
      type: "POST",
      data: {
        action: "gcc_get_all_chatbot_questions",
        nonce:
          (typeof gold_suggestions_ajax !== "undefined" &&
            gold_suggestions_ajax.nonce) ||
          (typeof gcc_ajax !== "undefined" && gcc_ajax.nonce) ||
          "",
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          questions = response.data;

          // Start with first question
          setTimeout(() => {
            if (questions.length > 0) {
              showNextValidQuestion();
            } else {
              addBotMessage(
                "Izvinite, nema dostupnih pitanja. Molimo kontaktirajte nas direktno."
              );
            }
          }, 1500);
        } else {
          console.error("Error loading questions:", response.data);
          addBotMessage(
            "Izvinite, došlo je do greške pri učitavanju pitanja. Molimo pokušajte ponovo."
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error loading questions:", error);
        addBotMessage(
          "Izvinite, došlo je do greške pri učitavanju pitanja. Molimo pokušajte ponovo."
        );
      },
    });
  }

  // Client-side condition evaluation
  function evaluateCondition(condition, userAnswers) {
    console.log('=== CONDITION EVALUATION ===');
    console.log('Original condition:', condition);
    console.log('User answers:', userAnswers);
    
    // Handle empty or null conditions
    if (!condition || typeof condition !== "string" || condition.trim() === "") {
      console.log('No condition, returning true');
      return true;
    }

    // Clean up the condition string
    let evaluatedCondition = condition.trim();
    
    // Handle escaped quotes from database
    evaluatedCondition = evaluatedCondition.replace(/\\"/g, '"');
    console.log('After cleaning escaped quotes:', evaluatedCondition);

    // Replace each variable with its value
    for (const [key, value] of Object.entries(userAnswers)) {
      // Create a regex that matches the variable name as a whole word
      const regex = new RegExp(`\\b${key}\\b`, "g");
      let replacement;
      
      if (typeof value === "string") {
        replacement = `"${value}"`;
      } else if (typeof value === "number") {
        replacement = value.toString();
      } else if (typeof value === "boolean") {
        replacement = value.toString();
      } else {
        replacement = JSON.stringify(value);
      }
      
      console.log(`Replacing ${key} with ${replacement}`);
      evaluatedCondition = evaluatedCondition.replace(regex, replacement);
    }
    
    console.log('After variable replacement:', evaluatedCondition);

    // Check if there are still unresolved variables
    // Look for words that are not inside quotes and not JavaScript keywords
    const tempCondition = evaluatedCondition.replace(/"[^"]*"/g, ""); // Remove quoted strings
    const unresolvedVars = tempCondition.match(/\b[a-zA-Z_][a-zA-Z0-9_]*\b/g);
    const jsKeywords = ['true', 'false', 'null', 'undefined'];
    const actualUnresolved = unresolvedVars ? unresolvedVars.filter(v => !jsKeywords.includes(v)) : [];
    
    if (actualUnresolved && actualUnresolved.length > 0) {
      console.log('Unresolved variables found:', actualUnresolved);
      return false;
    }

    // Simple replacement for == to === (be more careful)
    evaluatedCondition = evaluatedCondition.replace(/\s*==\s*/g, ' === ');
    evaluatedCondition = evaluatedCondition.replace(/\s*!=\s*/g, ' !== ');
    
    console.log('Final evaluated condition:', evaluatedCondition);

    try {
      // Use Function constructor to safely evaluate
      const result = Function(`"use strict"; return (${evaluatedCondition})`)();
      console.log('Evaluation result:', result);
      return Boolean(result);
    } catch (e) {
      console.error('Error evaluating condition:', condition, e, 'Evaluated:', evaluatedCondition);
      return false;
    }
  }

  // Find next valid question based on conditions and answered status
  function showNextValidQuestion() {
    console.log('=== FINDING NEXT VALID QUESTION ===');
    console.log('Current question index:', chatbotState.currentQuestion);
    console.log('Total questions:', questions.length);
    
    for (let i = chatbotState.currentQuestion; i < questions.length; i++) {
      const question = questions[i];
      const attributes = JSON.parse(question.attributes || "{}");

      console.log(`\nChecking question ${i}: ${question.question}`);
      console.log('Question condition:', question.condition_logic);
      console.log('Question attributes:', attributes);

      // Check if condition is met
      const conditionMet = evaluateCondition(question.condition_logic, chatbotState.userAnswers);
      if (!conditionMet) {
        console.log("Condition not met for question:", question.question);
        continue; // Skip this question, condition not met
      }

      // Check if this question has been answered
      let isAnswered = false;
      if (attributes.budget && chatbotState.userAnswers.budget)
        isAnswered = true;
      if (attributes.product_type && chatbotState.userAnswers.product_type)
        isAnswered = true;
      if (
        attributes.combo_percentage &&
        chatbotState.userAnswers.combo_percentage
      )
        isAnswered = true;
      if (
        attributes.delivery_method &&
        chatbotState.userAnswers.delivery_method
      )
        isAnswered = true;
      if (
        attributes.weight_preference &&
        chatbotState.userAnswers.weight_preference
      )
        isAnswered = true;
      if (
        attributes.high_budget_action &&
        chatbotState.userAnswers.high_budget_action
      )
        isAnswered = true;

      console.log('Question already answered:', isAnswered);

      if (!isAnswered) {
        console.log('Showing question:', i);
        chatbotState.currentQuestion = i;
        showQuestion(i);
        return;
      }
    }

    // All questions answered or no more questions, load products
    console.log('No more valid questions, loading products');
    setTimeout(() => {
      addBotMessage(
        "Odlično! Sada ću vam pripremiti predlog proizvoda za vaš budžet."
      );
      loadProducts();
    }, 1000);
  }

  // Load products based on selection
  function loadProducts() {
    // Show loading
    addBotMessage("Molim sačekajte, priprema se ponuda... ⏳");

    console.log('=== LOADING PRODUCTS ===');
    console.log('Budget:', chatbotState.budget);
    console.log('Product type:', chatbotState.product_type);
    console.log('Combo percentage:', chatbotState.combo_percentage);
    console.log('Weight preference:', chatbotState.weight_preference);
    console.log('Delivery method:', chatbotState.delivery_method);

    $.ajax({
      url:
        (typeof gold_suggestions_ajax !== "undefined" &&
          gold_suggestions_ajax.ajax_url) ||
        (typeof gcc_ajax !== "undefined" && gcc_ajax.ajax_url) ||
        "/wp-admin/admin-ajax.php",
      type: "POST",
      data: {
        action: "gcc_calculate_optimal_products",
        nonce:
          (typeof gold_suggestions_ajax !== "undefined" &&
            gold_suggestions_ajax.nonce) ||
          (typeof gcc_ajax !== "undefined" && gcc_ajax.nonce) ||
          "",
        budget: chatbotState.budget,
        product_type: chatbotState.product_type,
        combo_percentage: chatbotState.combo_percentage,
        weight_preference: chatbotState.weight_preference,
        delivery_method: chatbotState.delivery_method,
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          console.log('Products calculated:', response.data);
          chatbotState.selectedProducts = response.data.products;
          chatbotState.totalValue = response.data.total_value;
          calculateTotal(); // Recalculate total to ensure consistency
          showProductSelection();
        } else {
          console.error('Product calculation failed:', response.data);
          addBotMessage(
            "Izvinite, došlo je do greške. Molimo pokušajte ponovo."
          );
        }
      },
      error: function () {
        addBotMessage("Izvinite, došlo je do greške. Molimo pokušajte ponovo.");
      },
    });
  }

  // Calculate total value
  function calculateTotal() {
    chatbotState.totalValue = chatbotState.selectedProducts.reduce(
      (sum, product) => {
        if (product.total_price) {
          return sum + parseFloat(product.total_price);
        }
        return sum + parseFloat(product.final_price) * (product.quantity || 1);
      },
      0
    );
  }

  // Show product selection
  function showProductSelection() {
    // Remove loading message
    $(".chat-message").last().remove();

    if (chatbotState.selectedProducts.length === 0) {
      addBotMessage(
        "Nema dostupnih proizvoda za vaš budžet. Molimo kontaktirajte nas za više informacija."
      );
      showContactForm();
      return;
    }

    // Use the actual total value from the calculation
    const actualTotal = chatbotState.totalValue;
    addBotMessage(
      `Evo predloga za vaš budžet od €${chatbotState.budget.toLocaleString()} (ukupno: €${actualTotal.toFixed(2)}):`
    );

    // Create product list HTML - compact inline display
    let productListHtml = '<div class="gcc-product-list-compact">';

    chatbotState.selectedProducts.forEach((product) => {
      const quantity = product.quantity || 1;
      const unitPrice = parseFloat(product.final_price);
      const totalPrice = product.total_price ? parseFloat(product.total_price) : unitPrice * quantity;
      
      productListHtml += `
                <div class="gcc-product-item-compact" data-product-id="${
                  product.id
                }">
                    <div class="gcc-product-info-compact">
                        <span class="gcc-product-name">${product.name}</span>
                        <span class="gcc-product-details">${
                          product.weight
                        } • ${getProductTypeDisplay(product.type)}</span>
                        <span class="gcc-product-price-compact">€${unitPrice.toFixed(2)} ${quantity > 1 ? '× ' + quantity : ''}</span>
                        <span class="gcc-product-quantity-display">Količina: ${quantity}</span>
                        ${quantity > 1 ? `<span class="gcc-product-total-price">Ukupno: €${totalPrice.toFixed(2)}</span>` : ''}
                    </div>
                </div>
            `;
    });

    productListHtml += "</div>";

    // Add total section
    productListHtml += `
            <div class="gcc-total-section">
                <div class="gcc-total-value">
                    <strong>Ukupno: <span id="gcc-total-amount">€0</span></strong>
                    <small>(<span id="gcc-total-rsd">0 RSD</span>)</small>
                </div>
            </div>
        `;

    // Add action buttons
    productListHtml += `
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
        `;

    // Show products in products container
    $("#gcc-products-container").html(productListHtml).show();

    // Update total
    updateTotal();

    // Bind events
    $("#gcc-email-quote-btn").on("click", () => {
      chatbotState.quoteType = "email";
      showContactForm();
    });
    $("#gcc-call-trader-btn").on("click", () => {
      chatbotState.quoteType = "call";
      showContactForm();
    });
    $("#gcc-start-over-btn").on("click", resetChatbot);
  }

  // Calculate actual budget (slightly under chosen amount)
  function calculateActualBudget(budget) {
    switch (budget) {
      case 1000:
        return 987;
      case 2500:
        return 2487;
      case 5000:
        return 4987;
      case 10000:
        return 9987;
      case 20000:
        return 19987;
      case 50000:
        return 49987;
      default:
        return budget - 13; // General formula
    }
  }

  // Get suggestion level based on budget
  function getSuggestionLevel(budget) {
    if (budget >= 20000) return "20g+"; // includes Smartbox
    if (budget >= 10000) return "10g+"; // includes Smartpack
    if (budget >= 5000) return "5g+";
    if (budget >= 2500) return "2g+";
    return "1g"; // €1,000 budget
  }

  // Get product type display name
  function getProductTypeDisplay(type) {
    switch (type) {
      case "bar":
        return "Poluga";
      case "ducat":
        return "Dukat";
      case "plate":
        return "Pločica";
      default:
        return type;
    }
  }

  // Show contact form
  function showContactForm() {
    const actionText =
      chatbotState.quoteType === "email"
        ? "email sa ponudom"
        : "poziv od trgovca";
    addBotMessage(
      `Odlično! Da biste dobili ${actionText}, molimo unesite vaše podatke:`
    );

    const formHtml = `
            <div class="gcc-contact-form">
                <form id="gcc-contact-form">
                    <div class="gcc-form-group">
                        <label for="gcc-name">Ime i prezime *</label>
                        <input type="text" id="gcc-name" name="name" required>
                    </div>
                    <div class="gcc-form-group">
                        <label for="gcc-email">Email *</label>
                        <input type="email" id="gcc-email" name="email" required>
                    </div>
                    <div class="gcc-form-group">
                        <label for="gcc-phone">Telefon *</label>
                        <input type="tel" id="gcc-phone" name="phone" required>
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
        `;

    $("#gcc-products-container").html(formHtml).show();

    // Bind form events
    $("#gcc-contact-form").on("submit", handleContactSubmission);
    $("#gcc-cancel-form-btn").on("click", () => {
      $("#gcc-products-container").html("").hide();
      showProductSelection();
    });
  }

  // Handle contact form submission
  function handleContactSubmission(e) {
    e.preventDefault();

    const submitBtn = $(".gcc-submit-btn");
    const submitText = $(".gcc-submit-text");
    const submitLoader = $(".gcc-submit-loader");

    // Show loading
    submitBtn.prop("disabled", true);
    submitText.hide();
    submitLoader.show();

    const formData = {
      action: "gcc_submit_contact",
      nonce:
        (typeof gold_suggestions_ajax !== "undefined" &&
          gold_suggestions_ajax.nonce) ||
        (typeof gcc_ajax !== "undefined" && gcc_ajax.nonce) ||
        "",
      name: $("#gcc-name").val(),
      email: $("#gcc-email").val(),
      phone: $("#gcc-phone").val(),
      message: $("#gcc-message").val(),
      budget: chatbotState.budget,
      budget_display: chatbotState.budgetDisplay,
      product_type: chatbotState.product_type,
      combo_percentage: chatbotState.combo_percentage,
      weight_preference: chatbotState.weight_preference,
      delivery_method: chatbotState.delivery_method,
      selected_products: chatbotState.selectedProducts,
      total_value: chatbotState.totalValue,
      quote_type: chatbotState.quoteType,
    };

    $.ajax({
      url:
        (typeof gold_suggestions_ajax !== "undefined" &&
          gold_suggestions_ajax.ajax_url) ||
        (typeof gcc_ajax !== "undefined" && gcc_ajax.ajax_url) ||
        "/wp-admin/admin-ajax.php",
      type: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          addBotMessage(
            "Hvala! Vaš zahtev je uspešno poslat. Uskoro ćemo vam se javiti sa detaljnom ponudom."
          );
          $("#gcc-products-container").html(
            '<div class="gcc-success-message">Uspešno poslato! ✅</div>'
          );

          setTimeout(() => {
            const resetHtml =
              '<button class="gcc-cta-btn gcc-primary" onclick="resetChatbot()">Napravi novi proračun</button>';
            $("#gcc-products-container").html(resetHtml);
          }, 2000);
        } else {
          alert(
            "Greška: " +
              (response.data ? response.data.message : "Nepoznata greška")
          );
          resetSubmitButton();
        }
      },
      error: function () {
        alert("Došlo je do greške. Molimo pokušajte ponovo.");
        resetSubmitButton();
      },
    });

    function resetSubmitButton() {
      submitBtn.prop("disabled", false);
      submitText.show();
      submitLoader.hide();
    }
  }

  // Update total display
  function updateTotal() {
    $("#gcc-total-amount").text("€" + chatbotState.totalValue.toFixed(2));
    $("#gcc-total-rsd").text(
      (chatbotState.totalValue * chatbotState.exchangeRate).toFixed(0) + " RSD"
    );
  }

  // Handle schedule meeting
  function handleScheduleMeeting() {
    // This would integrate with Calendly or similar service
    addBotMessage(
      "Uskoro ćete biti preusmereni na kalendar za zakazivanje sastanka."
    );
    // window.open('https://calendly.com/your-link', '_blank');
  }

  // Add bot message
  function addBotMessage(message) {
    const avatarHtml = getPersonaAvatarHtml();
    const messageHtml = `
            <div class="message-wrapper ai-message">
                <div class="avatar">
                    ${avatarHtml}
                </div>
                <div class="message-content">
                    <div class="message-bubble ai-bubble">
                        <p>${message}</p>
                    </div>
                    <div class="message-time">${getCurrentTime()}</div>
                    <div class="gcc-inline-options-container" style="display: none;"></div>
                </div>
            </div>
        `;
    $("#gcc-chatbot-messages").append(messageHtml);
    scrollToBottom();
  }

  // Add user message
  function addUserMessage(message) {
    const messageHtml = `
            <div class="message-wrapper user-message">
                <div class="message-content">
                    <div class="message-bubble user-bubble">
                        <p>${message}</p>
                    </div>
                    <div class="message-time">${getCurrentTime()}</div>
                </div>
                <div class="avatar">
                    <div class="user-icon">👤</div>
                </div>
            </div>
        `;
    $("#gcc-chatbot-messages").append(messageHtml);
    scrollToBottom();
  }

  // Get current time
  function getCurrentTime() {
    const now = new Date();
    return (
      now.getHours().toString().padStart(2, "0") +
      ":" +
      now.getMinutes().toString().padStart(2, "0")
    );
  }

  // Get persona avatar HTML
  function getPersonaAvatarHtml() {
    const personaImage =
      (typeof gold_suggestions_ajax !== "undefined" &&
        gold_suggestions_ajax.persona_image) ||
      (typeof gcc_ajax !== "undefined" && gcc_ajax.persona_image) ||
      "";

    const persona =
      (typeof gold_suggestions_ajax !== "undefined" &&
        gold_suggestions_ajax.persona) ||
      (typeof gcc_ajax !== "undefined" && gcc_ajax.persona) ||
      "ZLATIJA";

    if (personaImage) {
      return `<img src="${personaImage}" alt="${persona}" class="persona-image">`;
    } else {
      return `<div class="persona-fallback">${persona.charAt(0)}</div>`;
    }
  }

  // Update message time
  function updateMessageTime() {
    const firstMessageTime = $(".message-time").first();
    if (firstMessageTime.length > 0) {
      firstMessageTime.text(getCurrentTime());
    }
  }

  // Scroll to bottom
  function scrollToBottom() {
    const messages = $("#gcc-chatbot-messages");
    if (messages.length > 0 && messages[0]) {
      messages.scrollTop(messages[0].scrollHeight);
    }
  }

  // Reset chatbot
  function resetChatbot() {
    chatbotState = {
      currentQuestion: 0,
      budget: 0,
      budgetDisplay: "",
      product_type: "",
      combo_percentage: 60,
      weight_preference: "",
      delivery_method: "",
      high_budget_action: "",
      selectedProducts: [],
      totalValue: 0,
      currency: "EUR",
      exchangeRate: 117.5,
      quoteType: "email",
      userAnswers: {},
    };

    // Clear messages except the first one
    $("#gcc-chatbot-messages").find(".message-wrapper").not(":first").remove();
    $("#gcc-step-container").html("");
    $("#gcc-answers-container").hide();
    $("#gcc-products-container").hide();
    
    // Clear any remaining inline options
    $(".gcc-inline-options-container").hide().empty();

    // Start over
    setTimeout(() => {
      showNextValidQuestion();
    }, 500);
  }

  // Show question with typing animation
  function showQuestion(questionIndex) {
    if (questionIndex >= questions.length) {
      // All questions answered, load products
      loadProducts();
      return;
    }

    const question = questions[questionIndex];
    chatbotState.currentQuestion = questionIndex;

    // Add bot message with typing animation
    addBotMessageWithTyping(question.question, () => {
      showAnswerOptions(question);
    });
  }

  // Add bot message with typing animation
  function addBotMessageWithTyping(message, callback) {
    const avatarHtml = getPersonaAvatarHtml();
    const messageHtml = `
            <div class="message-wrapper ai-message">
                <div class="avatar">
                    ${avatarHtml}
                </div>
                <div class="message-content">
                    <div class="message-bubble ai-bubble">
                        <p class="gcc-typing-text"></p>
                    </div>
                    <div class="message-time">${getCurrentTime()}</div>
                    <div class="gcc-inline-options-container" style="display: none;"></div>
                </div>
            </div>
        `;
    $("#gcc-chatbot-messages").append(messageHtml);
    scrollToBottom();

    // Start typing animation
    const $typingElement = $(".gcc-typing-text").last();
    typeMessage($typingElement, message, callback);
  }

  // Typing animation effect like gold-suggestions
  function typeMessage($element, text, callback) {
    $element.text("");
    let i = 0;
    const typeInterval = setInterval(function () {
      if (i < text.length) {
        $element.text(text.substring(0, i + 1));
        i++;
      } else {
        clearInterval(typeInterval);
        if (callback) callback();
      }
    }, 30);
  }

  // Show answer options
  function showAnswerOptions(question) {
    // Parse options from database
    const options = JSON.parse(question.options);

    // Get the latest message's inline options container
    const $inlineContainer = $(".gcc-inline-options-container").last();
    
    // Clear any existing options in all containers
    $(".gcc-inline-options-container").hide().empty();
    
    // Create options HTML
    let optionsHtml = '<div class="gcc-inline-options">';

    options.forEach((option, index) => {
      let optionText = option.label || option.text;
      if (option.description) {
        optionText += ` ${option.description}`;
      }
      if (option.rsd) {
        optionText += ` (${option.rsd})`;
      }

      optionsHtml += `
        <button class="gcc-inline-option-btn gcc-option-btn" data-question-id="${
          question.id
        }" data-value="${option.value}" data-display="${
        option.display || option.label || option.text
      }">
          ${optionText}
        </button>
      `;
    });

    optionsHtml += '</div>';

    // Add exchange rate info for budget question
    const attributes = JSON.parse(question.attributes || "{}");
    if (attributes.budget) {
      optionsHtml += '<div class="gcc-exchange-info"><small>Kurs: EUR/RSD 117.50</small></div>';
    }

    // Add options to the latest message
    $inlineContainer.html(optionsHtml).show();
    
    // Bind click events
    $(".gcc-option-btn").on("click", handleOptionSelection);
    
    // Scroll to show the options
    scrollToBottom();
  }

  // Handle option selection
  function handleOptionSelection() {
    const questionId = $(this).data("question-id");
    const value = $(this).data("value");
    const displayText = $(this).data("display") || $(this).text();
    let userMessage = displayText;

    // Clean up the text (remove prefix and extra info)
    if (userMessage.includes(") ")) {
      userMessage = userMessage.substring(userMessage.indexOf(") ") + 2);
    }

    // Get current question to determine attribute
    const currentQuestion = questions[chatbotState.currentQuestion];
    const attributes = JSON.parse(currentQuestion.attributes || "{}");

    // Save answer based on attribute
    if (attributes.budget) {
      chatbotState.budget = parseInt(value);
      chatbotState.budgetDisplay = displayText;
      chatbotState.userAnswers.budget = parseInt(value);
    } else if (attributes.product_type) {
      chatbotState.product_type = value;
      chatbotState.userAnswers.product_type = value;
    } else if (attributes.delivery_method) {
      chatbotState.delivery_method = value;
      chatbotState.userAnswers.delivery_method = value;
    } else if (attributes.combo_percentage) {
      chatbotState.combo_percentage = parseInt(value);
      chatbotState.userAnswers.combo_percentage = parseInt(value);
    } else if (attributes.weight_preference) {
      chatbotState.weight_preference = value;
      chatbotState.userAnswers.weight_preference = value;
    } else if (attributes.high_budget_action) {
      chatbotState.high_budget_action = value;
      chatbotState.userAnswers.high_budget_action = value;
    }

    // Add user message
    addUserMessage(userMessage);

    // Hide all inline options
    $(".gcc-inline-options-container").hide().empty();

    // Handle special cases
    if (attributes.high_budget_action && value === "schedule") {
      // Handle scheduling meeting
      setTimeout(() => {
        addBotMessage(
          "Odlično! Uskoro ćete biti preusmereni na kalendar za zakazivanje sastanka."
        );
        handleScheduleMeeting();
      }, 1000);
      return;
    }

    // Move to next valid question
    setTimeout(() => {
      chatbotState.currentQuestion++;
      showNextValidQuestion();
    }, 1000);
  }

  // Initialize when page loads
  $(document).ready(function () {
    // Add a small delay to ensure DOM is fully ready
    setTimeout(function () {
      initChatbot();
    }, 100);
  });
});
