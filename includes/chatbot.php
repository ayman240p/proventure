<!-- ProVenture 24/7 AI Chatbot Widget -->
<div id="cshubChatTeaser" class="cshub-chat-teaser">
    <span>👋 Need help? Ask ProVenture AI 24/7!</span>
    <button type="button" id="cshubChatTeaserClose" class="cshub-chat-teaser-close" aria-label="Dismiss greeting">&times;</button>
</div>

<button type="button" id="cshubChatToggle" class="cshub-chat-toggle" aria-label="Toggle 24/7 AI Assistant" aria-expanded="false">
    <span class="cshub-chat-badge-247">
        <span class="status-dot"></span>24/7
    </span>
    <i class="bi bi-robot icon-chat"></i>
    <i class="bi bi-x-lg icon-close"></i>
</button>

<div id="cshubChatWidget" class="cshub-chat-widget" role="dialog" aria-label="ProVenture 24/7 AI Assistant" aria-hidden="true">
    <!-- Header -->
    <div class="cshub-chat-header">
        <div class="cshub-chat-header-info">
            <div class="cshub-chat-avatar">
                <i class="bi bi-robot"></i>
                <span class="online-indicator"></span>
            </div>
            <div class="cshub-chat-title-wrap">
                <h6 class="cshub-chat-title">
                    ProVenture AI <span class="badge-ai">24/7</span>
                </h6>
                <div class="cshub-chat-status">
                    <span class="text-success fw-bold">●</span> Online &middot; Instant Answers
                </div>
            </div>
        </div>
        <div class="cshub-chat-header-actions">
            <button type="button" id="cshubChatReset" class="cshub-header-btn" title="Restart conversation" aria-label="Restart conversation">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
            <button type="button" id="cshubChatClose" class="cshub-header-btn" title="Close chat" aria-label="Close chat">
                <i class="bi bi-dash-lg"></i>
            </button>
        </div>
    </div>

    <!-- Messages Body -->
    <div id="cshubChatBody" class="cshub-chat-body">
        <div class="cshub-msg-row bot">
            <div class="cshub-msg-avatar"><i class="bi bi-robot"></i></div>
            <div class="cshub-msg-bubble-wrap">
                <div class="cshub-msg-bubble">
                    <p class="mb-1">Hello! 👋 I'm your <strong>ProVenture AI Assistant</strong>, available <strong>24/7</strong>.</p>
                    <p class="mb-1">I can help you discover local micro-services, explain how direct WhatsApp hiring works, or guide you on registering as a freelancer.</p>
                    <p class="mb-0 text-muted small">Choose a quick topic below or type your question:</p>
                </div>
                <div class="cshub-msg-time">Just now</div>
            </div>
        </div>

        <!-- Quick Suggestion Chips -->
        <div class="cshub-chat-chips" id="cshubQuickChips">
            <button type="button" class="cshub-chip-btn" data-query="How do I hire a freelancer?">💼 How to Hire</button>
            <button type="button" class="cshub-chip-btn" data-query="What categories and services are available?">🔍 Browse Services</button>
            <button type="button" class="cshub-chip-btn" data-query="How do I register as a freelancer?">🚀 Become a Provider</button>
            <button type="button" class="cshub-chip-btn" data-query="How does WhatsApp contact work?">💬 WhatsApp Contact</button>
            <button type="button" class="cshub-chip-btn" data-query="Gadget & Computer Repair">🔧 Gadget Repair</button>
            <button type="button" class="cshub-chip-btn" data-query="Graphic Design Services">🎨 Graphic Design</button>
        </div>
    </div>

    <!-- Footer Input Form -->
    <div class="cshub-chat-footer">
        <form id="cshubChatForm" class="cshub-chat-input-form" autocomplete="off">
            <input type="text" id="cshubChatInput" class="cshub-chat-input" placeholder="Ask anything about ProVenture..." aria-label="Chat message input">
            <button type="submit" id="cshubChatSend" class="cshub-chat-send-btn" aria-label="Send message">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
        <div class="cshub-chat-footer-note">
            <i class="bi bi-shield-check text-success"></i> 24/7 Support &middot; Verified Local Micro-Services
        </div>
    </div>
</div>
