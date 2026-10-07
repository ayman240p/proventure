// assets/js/script.js
// ProVenture vanilla JS: 24/7 AI Chatbot Widget & Platform Interactive Logic

document.addEventListener('DOMContentLoaded', function () {
    // Initialize 24/7 AI Chatbot Widget
    initCSHubChatbot();
    // Initialize Interactive Buyer Guides Modal
    initBuyerGuideModal();
});

/**
 * ProVenture 24/7 AI Chatbot Controller
 */
function initCSHubChatbot() {
    var toggleBtn = document.getElementById('cshubChatToggle');
    var chatWidget = document.getElementById('cshubChatWidget');
    var closeBtn = document.getElementById('cshubChatClose');
    var resetBtn = document.getElementById('cshubChatReset');
    var teaserBubble = document.getElementById('cshubChatTeaser');
    var teaserClose = document.getElementById('cshubChatTeaserClose');
    var chatBody = document.getElementById('cshubChatBody');
    var chatForm = document.getElementById('cshubChatForm');
    var chatInput = document.getElementById('cshubChatInput');
    var sendBtn = document.getElementById('cshubChatSend');

    if (!toggleBtn || !chatWidget) return;

    var isTyping = false;
    var messageHistory = [];

    // Helper: Play a subtle soft audio chime via Web Audio API
    function playChime() {
        try {
            var AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            var ctx = new AudioContext();
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
            osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.12); // A5
            gain.gain.setValueAtTime(0.04, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.18);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.18);
        } catch (e) {
            // Audio context may be restricted before user gesture; silently ignore
        }
    }

    // Toggle Chat Widget Open/Close
    function toggleChat(open) {
        var shouldOpen = typeof open === 'boolean' ? open : !chatWidget.classList.contains('active');
        if (shouldOpen) {
            chatWidget.classList.add('active');
            toggleBtn.classList.add('active');
            toggleBtn.setAttribute('aria-expanded', 'true');
            if (teaserBubble) {
                teaserBubble.style.opacity = '0';
                teaserBubble.style.pointerEvents = 'none';
                sessionStorage.setItem('cshub_teaser_dismissed', 'true');
            }
            setTimeout(function () {
                if (chatInput) chatInput.focus();
            }, 300);
            scrollToBottom();
        } else {
            chatWidget.classList.remove('active');
            toggleBtn.classList.remove('active');
            toggleBtn.setAttribute('aria-expanded', 'false');
        }
    }

    toggleBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        toggleChat();
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleChat(false);
        });
    }

    // Teaser Bubble click triggers opening
    if (teaserBubble) {
        if (sessionStorage.getItem('cshub_teaser_dismissed') === 'true') {
            teaserBubble.style.display = 'none';
        }
        teaserBubble.addEventListener('click', function (e) {
            if (e.target.closest('#cshubChatTeaserClose')) return;
            toggleChat(true);
        });
    }

    if (teaserClose) {
        teaserClose.addEventListener('click', function (e) {
            e.stopPropagation();
            teaserBubble.style.opacity = '0';
            teaserBubble.style.pointerEvents = 'none';
            sessionStorage.setItem('cshub_teaser_dismissed', 'true');
        });
    }

    // Keyboard navigation: Escape key closes chat
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && chatWidget.classList.contains('active')) {
            toggleChat(false);
        }
    });

    // Reset Chat Conversation
    if (resetBtn) {
        resetBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (confirm('Restart conversation with ProVenture AI Assistant?')) {
                resetConversation();
            }
        });
    }

    function resetConversation() {
        chatBody.innerHTML = `
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
            <div class="cshub-chat-chips" id="cshubQuickChips">
                <button type="button" class="cshub-chip-btn" data-query="How do I hire a freelancer?">💼 How to Hire</button>
                <button type="button" class="cshub-chip-btn" data-query="What categories and services are available?">🔍 Browse Services</button>
                <button type="button" class="cshub-chip-btn" data-query="How do I register as a freelancer?">🚀 Become a Provider</button>
                <button type="button" class="cshub-chip-btn" data-query="How does WhatsApp contact work?">💬 WhatsApp Contact</button>
                <button type="button" class="cshub-chip-btn" data-query="Gadget & Computer Repair">🔧 Gadget Repair</button>
                <button type="button" class="cshub-chip-btn" data-query="Graphic Design Services">🎨 Graphic Design</button>
            </div>
        `;
        bindChipButtons();
        if (chatInput) chatInput.focus();
    }

    function scrollToBottom() {
        setTimeout(function () {
            chatBody.scrollTop = chatBody.scrollHeight;
        }, 50);
    }

    function getTimeString() {
        var now = new Date();
        return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    // Append Message to Chat Body
    function appendMessage(sender, htmlContent) {
        var row = document.createElement('div');
        row.className = 'cshub-msg-row ' + sender;

        var avatarHtml = sender === 'bot'
            ? '<div class="cshub-msg-avatar"><i class="bi bi-robot"></i></div>'
            : '';

        row.innerHTML = avatarHtml + `
            <div class="cshub-msg-bubble-wrap">
                <div class="cshub-msg-bubble">${htmlContent}</div>
                <div class="cshub-msg-time">${getTimeString()}</div>
            </div>
        `;

        chatBody.appendChild(row);
        scrollToBottom();
        return row;
    }

    // Show Typing Indicator
    function showTypingIndicator() {
        var indicator = document.createElement('div');
        indicator.id = 'cshubTypingIndicator';
        indicator.className = 'cshub-msg-row bot';
        indicator.innerHTML = `
            <div class="cshub-msg-avatar"><i class="bi bi-robot"></i></div>
            <div class="cshub-typing-indicator">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        `;
        chatBody.appendChild(indicator);
        scrollToBottom();
    }

    function removeTypingIndicator() {
        var el = document.getElementById('cshubTypingIndicator');
        if (el) el.remove();
    }

    // Bind Click Handlers for Quick Action Chips
    function bindChipButtons() {
        var chips = chatBody.querySelectorAll('.cshub-chip-btn');
        chips.forEach(function (btn) {
            btn.onclick = function () {
                var query = btn.getAttribute('data-query');
                if (query && !isTyping) {
                    processUserMessage(query);
                }
            };
        });
    }
    bindChipButtons();

    // Process Message Sending
    if (chatForm) {
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var text = chatInput.value.trim();
            if (text && !isTyping) {
                processUserMessage(text);
                chatInput.value = '';
            }
        });
    }

    function processUserMessage(userText) {
        isTyping = true;
        if (sendBtn) sendBtn.disabled = true;

        // Escape HTML for user input safety
        var safeText = userText.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        appendMessage('user', safeText);

        showTypingIndicator();

        // Calculate dynamic realistic reading delay (450ms - 850ms)
        var delay = Math.min(850, Math.max(450, userText.length * 15));

        setTimeout(function () {
            removeTypingIndicator();
            var botResponse = generateAIResponse(userText);
            appendMessage('bot', botResponse.html);

            // If the response provides relevant follow-up chips, append them
            if (botResponse.chips && botResponse.chips.length > 0) {
                var chipsWrap = document.createElement('div');
                chipsWrap.className = 'cshub-chat-chips';
                botResponse.chips.forEach(function (chip) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'cshub-chip-btn';
                    btn.setAttribute('data-query', chip.query);
                    btn.innerHTML = chip.label;
                    chipsWrap.appendChild(btn);
                });
                chatBody.appendChild(chipsWrap);
                bindChipButtons();
                scrollToBottom();
            }

            playChime();
            isTyping = false;
            if (sendBtn) sendBtn.disabled = false;
            if (chatInput) chatInput.focus();
        }, delay);
    }

    // AI Knowledge Engine for ProVenture
    function generateAIResponse(query) {
        var q = query.toLowerCase().trim();

        // Check for Greetings
        if (/^(hi|hello|hey|salam|hola|good\s*(morning|afternoon|evening)|yo)\b/.test(q)) {
            return {
                html: `
                    <p class="mb-1">Hello! 👋 Great to meet you. I'm ready 24/7 to assist with anything on <strong>ProVenture</strong>.</p>
                    <p class="mb-0">Are you looking to <strong>hire a local talent</strong> or <strong>offer your own micro-services</strong>?</p>
                `,
                chips: [
                    { label: '💼 How to Hire', query: 'How do I hire a freelancer?' },
                    { label: '🚀 How to Offer Services', query: 'How do I register as a freelancer?' },
                    { label: '🔍 Browse Categories', query: 'What categories are available?' }
                ]
            };
        }

        // How to Hire / Booking / WhatsApp
        if (q.includes('hire') || q.includes('book') || q.includes('order') || q.includes('how to use') || q.includes('engage')) {
            return {
                html: `
                    <p class="mb-1"><strong>Hiring on ProVenture is fast and direct in 3 simple steps:</strong></p>
                    <ul class="mb-2">
                        <li><strong>1. Browse:</strong> Explore services by category, keyword, or price filter.</li>
                        <li><strong>2. Review:</strong> Check provider profiles, transparent pricing, and verified reviews.</li>
                        <li><strong>3. WhatsApp:</strong> Click the green <strong>"Contact via WhatsApp"</strong> button on any service page to chat directly with the provider!</li>
                    </ul>
                    <p class="mb-0">There are no hidden platform fees—you agree on terms and payment directly.</p>
                    <a href="index.php" class="cshub-action-card">
                        <span><i class="bi bi-grid-fill me-1 text-success"></i> Browse Available Services</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                `,
                chips: [
                    { label: '💬 How WhatsApp works', query: 'How does WhatsApp contact work?' },
                    { label: '💰 Pricing details', query: 'How does pricing work?' },
                    { label: '⭐ How reviews work', query: 'How do reviews work?' }
                ]
            };
        }

        // WhatsApp Integration
        if (q.includes('whatsapp') || q.includes('contact') || q.includes('chat') || q.includes('phone') || q.includes('message')) {
            return {
                html: `
                    <p class="mb-1"><strong>Direct WhatsApp Connectivity ⚡</strong></p>
                    <p class="mb-1">Every active service card includes a 1-click WhatsApp button. Clicking it opens a direct WhatsApp chat pre-filled with the service title, so you can:</p>
                    <ul class="mb-2">
                        <li>Discuss project requirements in real time</li>
                        <li>Confirm dates and deadlines</li>
                        <li>Coordinate pickup/delivery or remote work</li>
                    </ul>
                    <p class="mb-0">Fast, personal, and zero middleman delay!</p>
                `,
                chips: [
                    { label: '🔍 Find a service now', query: 'What categories are available?' },
                    { label: '🚀 List your service', query: 'How do I register as a freelancer?' }
                ]
            };
        }

        // Register as Freelancer / Provider / Earning
        if (q.includes('freelancer') || q.includes('provider') || q.includes('register') || q.includes('sign up') || q.includes('seller') || q.includes('earn') || q.includes('offer service')) {
            return {
                html: `
                    <p class="mb-1"><strong>Become a ProVenture Freelancer 🚀</strong></p>
                    <p class="mb-1">Whether you offer tutoring, graphic design, repair, or delivery, you can start earning today:</p>
                    <ul class="mb-2">
                        <li><strong>1. Register:</strong> Create a free account and choose <strong>Freelancer</strong> as your role.</li>
                        <li><strong>2. Add Services:</strong> Create service listings with clear descriptions, pricing, and your WhatsApp number.</li>
                        <li><strong>3. Manage Availability:</strong> Toggle between <em>Available</em> and <em>Busy</em> anytime from your dashboard.</li>
                    </ul>
                    <p class="mb-0">You keep 100% of your earnings!</p>
                    <a href="register.php" class="cshub-action-card">
                        <span><i class="bi bi-person-plus-fill me-1 text-primary"></i> Create Freelancer Account</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                `,
                chips: [
                    { label: '💼 How clients contact me', query: 'How does WhatsApp contact work?' },
                    { label: '🔐 Login to Dashboard', query: 'How do I login?' }
                ]
            };
        }

        // Categories & Services Overview
        if (q.includes('category') || q.includes('categories') || q.includes('services') || q.includes('what can you do') || q.includes('what do you offer') || q.includes('list')) {
            return {
                html: `
                    <p class="mb-1"><strong>Popular Service Categories on ProVenture:</strong></p>
                    <ul class="mb-2">
                        <li>📚 <strong>Academic Tutoring:</strong> Math, Programming, Languages, Assignment coaching.</li>
                        <li>🎨 <strong>Graphic Design:</strong> Logos, Social Media, Posters, UI/UX mockups.</li>
                        <li>🔧 <strong>Gadget Repair:</strong> Phone screens, hardware diagnosis, thermal paste.</li>
                        <li>💻 <strong>Computer Services:</strong> OS reinstallation, malware cleanup, PC builds.</li>
                        <li>🚚 <strong>Delivery / Runner:</strong> Parcel collection, food delivery, errands.</li>
                        <li>📸 <strong>Photography:</strong> Portraits, campus events, product shoots.</li>
                    </ul>
                    <p class="mb-0">Click below to browse active listings in each category!</p>
                `,
                chips: [
                    { label: '📚 Tutoring', query: 'Tell me about Academic Tutoring' },
                    { label: '🎨 Graphic Design', query: 'Tell me about Graphic Design' },
                    { label: '🔧 Gadget Repair', query: 'Tell me about Gadget Repair' },
                    { label: '💻 Computer Services', query: 'Tell me about Computer Services' }
                ]
            };
        }

        // Specific category queries
        if (q.includes('tutor') || q.includes('academic') || q.includes('study') || q.includes('class') || q.includes('math') || q.includes('homework')) {
            return {
                html: `
                    <p class="mb-1">📚 <strong>Academic Tutoring Services</strong></p>
                    <p class="mb-1">Connect with peer tutors and subject matter experts for 1-on-1 coaching in:</p>
                    <ul class="mb-2">
                        <li>Computer Science, Python, Java & Web Dev</li>
                        <li>Calculus, Linear Algebra & Statistics</li>
                        <li>English, Essay Proofreading & Presentation Prep</li>
                    </ul>
                    <p class="mb-0">Rates typically range from <strong>RM 20 to RM 60/hr</strong> with flexible online or campus sessions.</p>
                `,
                chips: [
                    { label: '🔍 Browse Tutors', query: 'What categories are available?' },
                    { label: '💼 How to Hire', query: 'How do I hire a freelancer?' }
                ]
            };
        }

        if (q.includes('design') || q.includes('graphic') || q.includes('logo') || q.includes('poster') || q.includes('canva') || q.includes('banner')) {
            return {
                html: `
                    <p class="mb-1">🎨 <strong>Graphic Design Services</strong></p>
                    <p class="mb-1">Need visual assets for your club, business, or project? ProVenture designers offer:</p>
                    <ul class="mb-2">
                        <li>Modern logo designs & brand identity kits</li>
                        <li>Club event posters, flyers & social media banners</li>
                        <li>Presentation slide deck beautification</li>
                    </ul>
                    <p class="mb-0">Average micro-service packages: <strong>RM 25 - RM 150</strong>.</p>
                `,
                chips: [
                    { label: '💼 How to Hire a Designer', query: 'How do I hire a freelancer?' },
                    { label: '🚀 Offer Design Services', query: 'How do I register as a freelancer?' }
                ]
            };
        }

        if (q.includes('repair') || q.includes('gadget') || q.includes('screen') || q.includes('phone') || q.includes('laptop') || q.includes('fix') || q.includes('broken')) {
            return {
                html: `
                    <p class="mb-1">🔧 <strong>Gadget & Hardware Repair</strong></p>
                    <p class="mb-1">Experienced local technicians can inspect and repair your hardware:</p>
                    <ul class="mb-2">
                        <li>Laptop dust cleaning & thermal paste replacement</li>
                        <li>Smartphone screen & battery replacements</li>
                        <li>Keyboard, charger port & hinge repairs</li>
                    </ul>
                    <p class="mb-0">Direct quote consultation is available via WhatsApp prior to drop-off.</p>
                `,
                chips: [
                    { label: '💻 Computer Services', query: 'Tell me about Computer Services' },
                    { label: '💬 WhatsApp Direct Contact', query: 'How does WhatsApp contact work?' }
                ]
            };
        }

        if (q.includes('computer') || q.includes('windows') || q.includes('format') || q.includes('software') || q.includes('wifi') || q.includes('it') || q.includes('virus')) {
            return {
                html: `
                    <p class="mb-1">💻 <strong>Computer & IT Services</strong></p>
                    <p class="mb-1">Get software and system issues resolved quickly:</p>
                    <ul class="mb-2">
                        <li>Windows / macOS clean installation & driver setup</li>
                        <li>Malware, adware & virus removal</li>
                        <li>Data recovery & backup configuration</li>
                        <li>Custom PC building & component upgrades (RAM/SSD)</li>
                    </ul>
                    <p class="mb-0">Affordable student-friendly rates with quick turnaround.</p>
                `,
                chips: [
                    { label: '💼 How to Hire an IT Specialist', query: 'How do I hire a freelancer?' },
                    { label: '🔍 Browse All Categories', query: 'What categories are available?' }
                ]
            };
        }

        if (q.includes('runner') || q.includes('delivery') || q.includes('parcel') || q.includes('food') || q.includes('errand')) {
            return {
                html: `
                    <p class="mb-1">🚚 <strong>Delivery & Runner Services</strong></p>
                    <p class="mb-1">Need something picked up or dropped off around campus or town?</p>
                    <ul class="mb-2">
                        <li>Mailroom & courier parcel pickup and door delivery</li>
                        <li>Late-night snack or grocery runs</li>
                        <li>Urgent document dispatch</li>
                    </ul>
                    <p class="mb-0">Fast, local, and reliable dispatchers ready on WhatsApp.</p>
                `,
                chips: [
                    { label: '💼 How to Book a Runner', query: 'How do I hire a freelancer?' },
                    { label: '🚀 Become a Runner', query: 'How do I register as a freelancer?' }
                ]
            };
        }

        // Pricing & Payment
        if (q.includes('price') || q.includes('cost') || q.includes('pay') || q.includes('duitnow') || q.includes('fee') || q.includes('rate') || q.includes('commission')) {
            return {
                html: `
                    <p class="mb-1"><strong>Transparent Micro-Service Pricing 💰</strong></p>
                    <ul class="mb-2">
                        <li><strong>Clear Rates:</strong> Each freelancer specifies their starting rate (e.g. RM 15, RM 50, RM 100) directly on their listing.</li>
                        <li><strong>Zero Platform Cuts:</strong> ProVenture doesn't take commissions—freelancers keep 100% of their earnings.</li>
                        <li><strong>Direct Settlement:</strong> Pay via your preferred method (DuitNow QR, bank transfer, or cash upon delivery).</li>
                    </ul>
                `,
                chips: [
                    { label: '🔍 Browse Services', query: 'What categories are available?' },
                    { label: '⭐ Ratings & Trust', query: 'How do reviews work?' }
                ]
            };
        }

        // Reviews & Trust
        if (q.includes('review') || q.includes('rating') || q.includes('trust') || q.includes('safe') || q.includes('star')) {
            return {
                html: `
                    <p class="mb-1"><strong>Community Trust & Star Reviews ⭐</strong></p>
                    <p class="mb-1">ProVenture maintains quality through peer accountability:</p>
                    <ul class="mb-2">
                        <li>Clients can submit 1 to 5-star ratings and written feedback after service completion.</li>
                        <li>Ratings are publicly displayed on service cards to help you choose the best provider.</li>
                        <li>Freelancers with consistently high scores earn top placement on the directory!</li>
                    </ul>
                `,
                chips: [
                    { label: '💼 How to Hire', query: 'How do I hire a freelancer?' },
                    { label: '🔍 Browse Top Rated', query: 'What categories are available?' }
                ]
            };
        }

        // Login / Account
        if (q.includes('login') || q.includes('sign in') || q.includes('account') || q.includes('password') || q.includes('profile')) {
            return {
                html: `
                    <p class="mb-1"><strong>Account Access 🔐</strong></p>
                    <p class="mb-1">You can log in to your Client or Freelancer dashboard to view your inquiries, manage your services, or update your profile.</p>
                    <a href="login.php" class="cshub-action-card">
                        <span><i class="bi bi-box-arrow-in-right me-1 text-primary"></i> Go to Login Page</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                `,
                chips: [
                    { label: '🚀 Create New Account', query: 'How do I register as a freelancer?' },
                    { label: '🔍 Browse Services First', query: 'What categories are available?' }
                ]
            };
        }

        // Support / 24/7 AI Info
        if (q.includes('who are you') || q.includes('ai') || q.includes('bot') || q.includes('support') || q.includes('help') || q.includes('24/7')) {
            return {
                html: `
                    <p class="mb-1">🤖 <strong>ProVenture 24/7 AI Assistant</strong></p>
                    <p class="mb-1">I am your automated local guide, available 24 hours a day, 7 days a week to:</p>
                    <ul class="mb-2">
                        <li>Help you locate the right local talent in seconds</li>
                        <li>Explain booking, payment, and WhatsApp procedures</li>
                        <li>Assist freelancers in setting up their micro-services</li>
                    </ul>
                    <p class="mb-0">Feel free to ask me anything or use the buttons below!</p>
                `,
                chips: [
                    { label: '🔍 Browse Services', query: 'What categories are available?' },
                    { label: '💼 How to Hire', query: 'How do I hire a freelancer?' },
                    { label: '🚀 Become a Freelancer', query: 'How do I register as a freelancer?' }
                ]
            };
        }

        // Intelligent Fallback with helpful action cards
        return {
            html: `
                <p class="mb-1">I'm here to help! While I might not have an exact answer for "<em>${safeQuery(query)}</em>", here are the quickest ways I can assist you right now:</p>
                <ul class="mb-2">
                    <li>Find verified micro-services & local freelancers</li>
                    <li>Learn how direct WhatsApp booking works</li>
                    <li>Learn how to list your skills and start earning</li>
                </ul>
                <p class="mb-0">Try selecting one of these popular topics:</p>
            `,
            chips: [
                { label: '🔍 Browse All Services', query: 'What categories are available?' },
                { label: '💼 How to Hire via WhatsApp', query: 'How do I hire a freelancer?' },
                { label: '🚀 Register as Freelancer', query: 'How do I register as a freelancer?' },
                { label: '💰 Pricing & Payments', query: 'How does pricing work?' }
            ]
        };
    }

    function safeQuery(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').slice(0, 40);
    }
}

/**
 * ==========================================================
 * Interactive Buyer Guides Modal Controller
 * ==========================================================
 */
var BUYER_GUIDES = {
    plumber: {
        id: 'plumber',
        title: 'Guide to Hiring a Reliable Plumber',
        category: 'Plumbing & Sanitary',
        icon: 'bi-wrench-adjustable',
        intro: 'Plumbing emergencies and pipe issues require swift action, but hiring without verification can lead to recurring leaks and property damage. Use this checklist to verify your plumber before work begins.',
        checklist: [
            {
                title: 'Verify Experience with Your Specific Issue',
                desc: 'Ask about past experience with your exact job — whether it involves concealed pipe leaks, high-pressure water pumps, roof gutters, or sewage unclogging.'
            },
            {
                title: 'Clarify Scope & Emergency Call-Out Fees',
                desc: 'Confirm upfront if there is an inspection, transport, or after-hours surcharge before the plumber travels to your location.'
            },
            {
                title: 'Obtain an Itemized Quote (Labor vs. Parts)',
                desc: 'Request a transparent breakdown separating labor charges from replacement parts (pipes, ball valves, rubber seals, or faucets).'
            },
            {
                title: 'Check Reviews & Workmanship Warranty',
                desc: 'Read real client reviews on ProVenture and confirm at least a 14 to 30-day warranty against recurring leaks on completed joints.'
            }
        ],
        ctaText: 'Find Plumbers on ProVenture',
        query: 'Plumber'
    },
    aircon: {
        id: 'aircon',
        title: 'Guide to Hiring an Aircon Technician',
        category: 'Cooling & Appliances',
        icon: 'bi-snow',
        intro: 'Regular air conditioner servicing maintains energy efficiency and air quality. Avoid overpaying for unnecessary chemical overhauls or gas top-ups with these verification steps.',
        checklist: [
            {
                title: 'Distinguish Normal Servicing from Chemical Overhauls',
                desc: 'Standard routine cleaning (filters, fan blower, faceplate) is sufficient every 3–4 months. Full chemical dismantle is only needed for severe mold, odors, or deep blockages.'
            },
            {
                title: 'Require Pressure Testing Before Adding Gas',
                desc: 'Modern air conditioning systems (R32 / R410A) are sealed closed-loops. Ask the technician to check pressure gauges for physical leaks before agreeing to gas refills.'
            },
            {
                title: 'Inspect Drainage Line & Water Tray Flushing',
                desc: 'Ensure the technician vacuums or pressure-flushes the drainage line to prevent interior water dripping and condensate tray overflow.'
            },
            {
                title: 'Request a Cooling Performance Warranty',
                desc: 'Ask for a 30-day guarantee covering water leaks, cooling temperature, and workmanship post-servicing.'
            }
        ],
        ctaText: 'Find Aircon Technicians on ProVenture',
        query: 'Aircon'
    },
    catering: {
        id: 'catering',
        title: 'Guide to Hiring Event Catering',
        category: 'Events & Hospitality',
        icon: 'bi-cup-hot-fill',
        intro: 'Great food makes an unforgettable event. Ensure punctual setup, generous portions, and food safety compliance by ticking off these essential catering criteria.',
        checklist: [
            {
                title: 'Confirm Guest Headcount & Dietary Guidelines',
                desc: 'Verify Halal certification, vegetarian or vegan requirements, and common allergies (peanuts, shellfish). Always budget a 5–10% portion buffer for unexpected guests.'
            },
            {
                title: 'Clarify Equipment, Cutlery & Cleanup Inclusions',
                desc: 'Confirm whether the package includes chafing dishes, food warmers, serving utensils, disposable cutlery, napkins, and trash disposal bags.'
            },
            {
                title: 'Lock Down Arrival & Setup Timetable',
                desc: 'Require the catering crew to arrive at least 60 to 90 minutes before your guests arrive to allow food to warm and presentation to be perfected.'
            },
            {
                title: 'Review Real Event Photos & Food Portions',
                desc: 'Inspect real past buffet setup photos on ProVenture to assess presentation cleanliness, portion sizes, and food freshness.'
            }
        ],
        ctaText: 'Find Event Catering on ProVenture',
        query: 'Catering'
    },
    renovation: {
        id: 'renovation',
        title: 'Guide to Hiring a Renovation Contractor',
        category: 'Home Improvement',
        icon: 'bi-hammer',
        intro: 'Home renovations involve significant time and capital. Protect your budget and property by following disciplined contractor vetting, clear contracts, and staged payments.',
        checklist: [
            {
                title: 'Verify Business Registration & CIDB / SSM Records',
                desc: 'Confirm the contractor operates a registered business and request verifiable references or recent project walkthroughs in your area.'
            },
            {
                title: 'Insist on a Written Bill of Quantities (BOQ)',
                desc: 'Avoid broad lump-sum quotes. Ensure every item (tile specifications, paint brands, hacking dimensions, electrical points) is clearly itemized.'
            },
            {
                title: 'Structure Milestone-Based Progressive Payments',
                desc: 'Never pay 100% upfront. Structure payments around verified completion stages (e.g. 15% deposit, 30% after wet works, 30% after carpentry, 25% on handover).'
            },
            {
                title: 'Establish a Written Defect Liability Period (DLP)',
                desc: 'Obtain a 3 to 6-month warranty where the contractor is contractually bound to rectify plaster cracks, hollow tiles, or paint defects at no added cost.'
            }
        ],
        ctaText: 'Find Renovation Pros on ProVenture',
        query: 'Renovation'
    },
    handyman: {
        id: 'handyman',
        title: 'Guide to Hiring a Handyman',
        category: 'General Repairs',
        icon: 'bi-tools',
        intro: 'A versatile handyman can fix multiple minor household annoyances in a single trip. Maximize efficiency and value with this preparation checklist.',
        checklist: [
            {
                title: 'Group Tasks into a Single Punch-List',
                desc: 'Combine drilling, curtain rod hanging, door hinge lubrication, silicone resealing, and furniture assembly into one visit to get the best value.'
            },
            {
                title: 'Share Clear Photos & Measurements on WhatsApp',
                desc: 'Send clear pictures of mounting surfaces (concrete, drywall, tiles) and issues beforehand so the handyman brings the correct drills, anchors, and fasteners.'
            },
            {
                title: 'Clarify Who Supplies Parts & Consumables',
                desc: 'Confirm whether replacement screws, wall plugs, replacement bulbs, or caulking are included in the price or billed separately.'
            },
            {
                title: 'Agree on Flat Rate vs. Hourly Pricing',
                desc: 'Agree on the pricing structure upfront before work commences to avoid unexpected charges if an assembly task takes longer than estimated.'
            }
        ],
        ctaText: 'Find Handymen on ProVenture',
        query: 'Handyman'
    },
    tutor: {
        id: 'tutor',
        title: 'Guide to Hiring a Home Tutor',
        category: 'Academic & Tutoring',
        icon: 'bi-mortarboard-fill',
        intro: 'The right private tutor inspires confidence and academic excellence. Ensure your student receives tailored instruction by assessing credentials and teaching style.',
        checklist: [
            {
                title: 'Verify Academic Credentials & Syllabus Familiarity',
                desc: 'Confirm the tutor has proven mastery of the target curriculum (KSSR, SPM, IGCSE, A-Levels, Coding, or Languages) and recent syllabus updates.'
            },
            {
                title: 'Schedule a Paid Trial Session First',
                desc: 'Book an initial trial lesson to observe the tutor’s patience, explanation clarity, and rapport with the student before committing to monthly arrangements.'
            },
            {
                title: 'Define Learning Milestones & Progress Reviews',
                desc: 'Set concrete objectives (exam preparation, weak subject remediation, homework guidance) and agree on monthly progress feedback.'
            },
            {
                title: 'Clarify Rates, Rescheduling & Cancellation Rules',
                desc: 'Confirm hourly rates, payment schedules, and notice required if a lesson needs to be rescheduled due to sickness or school activities.'
            }
        ],
        ctaText: 'Find Home Tutors on ProVenture',
        query: 'Tutor'
    },
    learn: {
        id: 'learn',
        title: 'How to Create a Good Listing to Attract More Clients',
        category: 'Freelancer Playbook',
        icon: 'bi-rocket-takeoff-fill',
        intro: 'A well-crafted service listing builds instant trust, ranks higher on ProVenture search, and turns casual visitors into direct WhatsApp inquiries. Follow these 5 proven steps to maximize your bookings.',
        checklist: [
            {
                title: 'Write a Specific, Action-Oriented Title',
                desc: 'Avoid vague titles like "I do design". Use clear, searchable keywords stating what you deliver and who it is for (e.g. "Modern Vector Logo Design & Brand Identity Package").'
            },
            {
                title: 'Upload High-Resolution Portfolio Samples',
                desc: 'Real photos of past work, before-and-after comparisons, or clean mockups attract 3.5x more inquiries. Avoid generic, watermarked stock imagery.'
            },
            {
                title: 'Be Completely Transparent with Starting Rates',
                desc: 'State your starting or hourly price upfront. Clients avoid listings that say "contact for price" without a baseline. Clearly define what is covered in your base package.'
            },
            {
                title: 'Structure Your Description with Bullet Points',
                desc: 'Clearly break down: 1) What is included in the service, 2) Information needed from the client, 3) Estimated completion time, and 4) Revision terms.'
            },
            {
                title: 'Respond Rapidly & Professionally on WhatsApp',
                desc: 'Replies within 15–30 minutes dramatically boost booking rates. Prepare a polite welcome message and quick intake questionnaire ready to send when clients reach out.'
            }
        ],
        ctaText: 'Post a Service Listing Now',
        ctaUrl: 'freelancer/add-service.php'
    }
};

function initBuyerGuideModal() {
    var modalEl = document.getElementById('buyerGuideModal');
    if (!modalEl) return;

    var modalTitle = document.getElementById('buyerGuideModalLabel');
    var modalCategory = document.getElementById('guideModalCategory');
    var modalIcon = document.getElementById('guideModalIcon');
    var modalIntro = document.getElementById('guideModalIntro');
    var checklistContainer = document.getElementById('guideModalChecklist');
    var modalCta = document.getElementById('guideModalCta');
    var modalCtaText = document.getElementById('guideModalCtaText');

    function populateGuide(guideKey) {
        var guide = BUYER_GUIDES[guideKey] || BUYER_GUIDES['plumber'];
        if (!guide) return;

        if (modalTitle) modalTitle.textContent = guide.title;
        if (modalCategory) modalCategory.textContent = guide.category;
        if (modalIntro) modalIntro.textContent = guide.intro;

        if (modalIcon) {
            modalIcon.className = 'bi ' + guide.icon + ' fs-4';
        }

        // Render checklist items
        if (checklistContainer) {
            checklistContainer.innerHTML = guide.checklist.map(function (item, index) {
                return `
                    <div class="guide-checklist-item d-flex gap-3 align-items-start">
                        <span class="guide-step-num">${index + 1}</span>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1 text-dark dark-text-light">${item.title}</strong>
                            <span class="text-secondary small d-block" style="line-height: 1.55;">${item.desc}</span>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Set CTA button target
        var basePath = (window.location.pathname.indexOf('/cshub/') !== -1) ? '/cshub/' : '';
        var targetUrl;
        if (guide.ctaUrl) {
            targetUrl = basePath + guide.ctaUrl.replace(/^\/cshub\//, '').replace(/^\//, '');
        } else {
            targetUrl = basePath + 'search.php?q=' + encodeURIComponent(guide.query || '');
        }
        if (modalCta) {
            modalCta.setAttribute('href', targetUrl);
        }
        if (modalCtaText) {
            modalCtaText.textContent = guide.ctaText;
        }
    }

    // Listen for Bootstrap show.bs.modal event
    modalEl.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;
        var guideKey = 'plumber';
        if (trigger && trigger.getAttribute) {
            guideKey = trigger.getAttribute('data-guide') || 'plumber';
        }
        populateGuide(guideKey);
    });

    // Check URL query parameter (e.g. ?guide=plumber) or hash (e.g. #guide-plumber)
    try {
        var params = new URLSearchParams(window.location.search);
        var guideParam = params.get('guide');
        var hashMatch = window.location.hash.match(/#guide-([a-z]+)/);
        var activeKey = guideParam || (hashMatch ? hashMatch[1] : null);

        if (activeKey && BUYER_GUIDES[activeKey]) {
            populateGuide(activeKey);
            if (window.bootstrap && window.bootstrap.Modal) {
                var bsModal = new bootstrap.Modal(modalEl);
                bsModal.show();
            }
        }
    } catch (e) {
        // Silently ignore URL parsing errors
    }
}

