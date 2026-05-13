<!-- AI Chatbot Widget -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/chatbot.css">
<div class="chatbot-widget" id="chatbotWidget">
    <div class="chatbot-widget-header" id="chatbotWidgetHeader">
        <div class="chatbot-widget-avatar">
            <i class="fas fa-robot"></i>
            <div class="online-indicator"></div>
        </div>
        <div class="chatbot-widget-info">
            <span class="chatbot-widget-name">GeoBot AI</span>
            <span class="chatbot-widget-status">Always Online</span>
        </div>
        <button class="chatbot-widget-close" id="chatbotWidgetClose">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="chatbot-widget-messages" id="chatbotWidgetMessages">
        <div class="chatbot-message bot">
            <div class="chatbot-msg-avatar"><i class="fas fa-robot"></i></div>
            <div class="chatbot-msg-bubble">
                <p>Hello! 👋 I'm GeoBot, your AI assistant. How can I help you today?</p>
                <span class="chatbot-msg-time">Just now</span>
            </div>
        </div>
        <div class="chatbot-suggestions">
            <button class="chatbot-suggestion-btn" onclick="sendChatbotMessage('What services do you offer?')">
                Services
            </button>
            <button class="chatbot-suggestion-btn" onclick="sendChatbotMessage('How do I book an appointment?')">
                Book Appointment
            </button>
            <button class="chatbot-suggestion-btn" onclick="sendChatbotMessage('What are your prices?')">
                Pricing
            </button>
        </div>
    </div>
    <div class="chatbot-widget-input">
        <input type="text" id="chatbotWidgetInput" placeholder="Type your message..." 
               onkeypress="if(event.key==='Enter') sendChatbotMessage()">
        <button id="chatbotWidgetSend" onclick="sendChatbotMessage()">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<button class="chatbot-widget-toggle" id="chatbotWidgetToggle">
    <i class="fas fa-comments"></i>
    <span class="chatbot-widget-badge" id="chatbotWidgetBadge" style="display:none;">1</span>
</button>
