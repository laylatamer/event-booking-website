// Event Ticketing Chatbot - Error-Handling Version
(function () {
    'use strict';

    // Don't load if already loaded
    if (window.chatbotLoaded) return;
    window.chatbotLoaded = true;

    // Helper to get absolute path to API based on script location
    const getApiUrl = () => {
        // 1. Try to find the script tag for this widget
        const scripts = document.getElementsByTagName('script');
        let scriptUrl = '';

        for (let i = 0; i < scripts.length; i++) {
            if (scripts[i].src && scripts[i].src.includes('chatbot-widget.js')) {
                scriptUrl = scripts[i].src;
                break;
            }
        }

        // 2. Derive API URL from script URL
        if (scriptUrl) {
            // If script is at .../public/js/chatbot-widget.js
            // API should be at .../public/api/chatbot.php
            return scriptUrl.replace('/js/chatbot-widget.js', '/api/chatbot.php');
        }

        // Fallback: Try to guess based on current location
        const path = window.location.pathname;
        const root = path.split('/public/')[0];
        if (root !== path) {
            return `${window.location.origin}${root}/public/api/chatbot.php`;
        }

        // Final fallback - assume relatively standard structure
        return '/event-booking-website/public/api/chatbot.php';
    };

    // Configuration - UPDATED with dynamic path
    const config = {
        // Dynamic absolute URL
        apiUrl: getApiUrl(),

        colors: {
            primary: '#5f5c5cff',
            secondary: '#f54308ff'
        }
    };


    // Create chatbot HTML
    const chatbotHTML = `
        <div id="event-chatbot" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
            <!-- Toggle Button -->
            <button id="chatbot-toggle" 
                    style="width: 60px; height: 60px; background: linear-gradient(135deg, ${config.colors.primary} 0%, ${config.colors.secondary} 100%); 
                           border: none; border-radius: 50%; cursor: pointer; color: white; font-size: 24px;
                           box-shadow: 0 4px 15px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center;
                           transition: transform 0.3s ease;">
                🎫
                <span style="position: absolute; width: 60px; height: 60px; background: rgba(255,255,255,0.3); 
                             border-radius: 50%; animation: pulse 2s infinite;"></span>
            </button>
            
            <!-- Chat Window -->
            <div id="chatbot-window" 
                 style="position: absolute; bottom: 70px; right: 0; width: 350px; height: 500px; background: white;
                        border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); display: none;
                        flex-direction: column; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                <!-- Header -->
                <div style="background: linear-gradient(135deg, ${config.colors.primary} 0%, ${config.colors.secondary} 100%); 
                            color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <span>🎭</span> Event Assistant
                    </h3>
                    <button id="chatbot-close" 
                            style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; 
                                   line-height: 1; width: 24px; height: 24px; display: flex; align-items: center; 
                                   justify-content: center; border-radius: 50%; transition: background 0.2s;">
                        ×
                    </button>
                </div>
                
                <!-- Messages Area -->
                <div id="chatbot-messages" 
                     style="flex: 1; padding: 20px; overflow-y: auto; background: #f8f9fa; font-size: 14px;">
                    <div style="background: white; border-radius: 10px; padding: 15px; margin-bottom: 20px; 
                                box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-left: 4px solid ${config.colors.primary}">
                        <p style="margin: 0 0 10px 0; color: #333; font-weight: 500;">Hi! I'm your event booking assistant. How can I help you today?</p>
                        <p style="margin: 0 0 10px 0; font-size: 13px; color: #555;">I can help with:</p>
                        <ul style="margin: 10px 0; padding-left: 20px; font-size: 13px; color: #555;">
                            <li style="margin: 5px 0;">🎟️ Ticket bookings</li>
                            <li style="margin: 5px 0;">📅 Event information</li>
                            <li style="margin: 5px 0;">💰 Refunds & policies</li>
                            <li style="margin: 5px 0;">❓ General support</li>
                        </ul>
                        <p style="margin: 0; font-size: 12px; color: #666;">Try asking me a question!</p>
                    </div>
                </div>
                
                <!-- Input Area -->
                <div style="border-top: 1px solid #e9ecef; padding: 15px; background: white;">
                    <div style="display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
                        <button class="quick-btn" data-question="How do I book tickets?" 
                                style="background: #e9ecef; border: none; border-radius: 20px; padding: 6px 12px; 
                                       font-size: 12px; color: #495057; cursor: pointer; transition: all 0.2s;">
                            Book Tickets
                        </button>
                        <button class="quick-btn" data-question="What events are coming?" 
                                style="background: #e9ecef; border: none; border-radius: 20px; padding: 6px 12px; 
                                       font-size: 12px; color: #495057; cursor: pointer; transition: all 0.2s;">
                            Upcoming Events
                        </button>
                        <button class="quick-btn" data-question="Refund policy?" 
                                style="background: #e9ecef; border: none; border-radius: 20px; padding: 6px 12px; 
                                       font-size: 12px; color: #495057; cursor: pointer; transition: all 0.2s;">
                            Refund Policy
                        </button>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="chatbot-input" 
                               placeholder="Ask about events, tickets, or bookings..." 
                               style="flex: 1; padding: 10px 15px; border: 1px solid #dee2e6; 
                                      border-radius: 25px; font-size: 14px; outline: none; transition: border 0.2s;"
                               autocomplete="off">
                        <button id="chatbot-send" 
                                style="background: linear-gradient(135deg, ${config.colors.primary} 0%, ${config.colors.secondary} 100%); 
                                       color: white; border: none; border-radius: 25px; padding: 0 20px; 
                                       font-size: 14px; font-weight: 500; cursor: pointer; transition: transform 0.2s;">
                            Send
                        </button>
                    </div>
                    <small style="display: block; text-align: center; margin-top: 10px; color: #6c757d; font-size: 11px;">
                        Event booking assistant • Available 24/7
                    </small>
                </div>
            </div>
        </div>
    `;

    // Inject chatbot into page
    document.body.insertAdjacentHTML('beforeend', chatbotHTML);

    // Add pulse animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        .quick-btn:hover {
            background: ${config.colors.primary} !important;
            color: white !important;
        }
        #chatbot-send:hover {
            transform: translateY(-1px);
        }
        #chatbot-close:hover {
            background: rgba(255, 255, 255, 0.2) !important;
        }
        #chatbot-input:focus {
            border-color: ${config.colors.primary} !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
        }
    `;
    document.head.appendChild(style);

    // Chatbot logic
    class SimpleChatbot {
        constructor() {
            this.isOpen = false;
            this.isLoading = false;
            this.apiTested = false;
            this.apiWorking = false;
            console.log('Chatbot initialized with API URL:', config.apiUrl);
            this.init();
        }

        init() {
            this.bindEvents();

            // Test API connection
            this.testApiConnection();

            // Auto-open on help pages
            if (window.location.pathname.includes('booking') ||
                window.location.pathname.includes('checkout') ||
                window.location.pathname.includes('customize')) {
                setTimeout(() => this.openChat(), 2000);
            }
        }

        bindEvents() {
            // Toggle button
            document.getElementById('chatbot-toggle').addEventListener('click', () => {
                this.toggleChat();
            });

            // Close button
            document.getElementById('chatbot-close').addEventListener('click', () => {
                this.closeChat();
            });

            // Send message
            document.getElementById('chatbot-send').addEventListener('click', () => {
                this.sendMessage();
            });

            // Enter key
            document.getElementById('chatbot-input').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.sendMessage();
            });

            // Quick questions
            document.querySelectorAll('.quick-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const question = e.target.getAttribute('data-question');
                    document.getElementById('chatbot-input').value = question;
                    this.sendMessage();
                });
            });
        }

        async testApiConnection() {
            try {
                console.log('Testing API connection at:', config.apiUrl);
                const testResponse = await fetch(config.apiUrl, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });

                console.log('API response status:', testResponse.status);
                const text = await testResponse.text();
                console.log('API raw response:', text.substring(0, 200));

                // Try to parse as JSON
                try {
                    const data = JSON.parse(text);
                    this.apiWorking = data.success !== false;
                    console.log('API test result:', this.apiWorking ? 'SUCCESS' : 'FAILED');
                } catch (e) {
                    this.apiWorking = false;
                    console.warn('API returned non-JSON response:', e.message);

                    // Test alternative URLs
                    await this.testAlternativeUrls();
                }

                this.apiTested = true;

                if (!this.apiWorking) {
                    this.addSystemMessage('⚠️ Chatbot API is currently unavailable. Using offline mode.');
                }

            } catch (error) {
                console.error('API test failed:', error);
                this.apiTested = true;
                this.apiWorking = false;
                this.addSystemMessage('⚠️ Chatbot is in offline mode. Basic responses only.');
            }
        }

        async testAlternativeUrls() {
            console.log('Testing alternative URLs...');

            // Get script URL again for fallback calculations
            const scripts = document.getElementsByTagName('script');
            let scriptUrl = '';
            for (let i = 0; i < scripts.length; i++) {
                if (scripts[i].src && scripts[i].src.includes('chatbot-widget.js')) {
                    scriptUrl = scripts[i].src;
                    break;
                }
            }

            const urls = [
                config.apiUrl, // Try the calculated one first
                scriptUrl.replace('/js/chatbot-widget.js', '/api/chatbot.php'),
                '/event-booking-website/public/api/chatbot.php',
                '/public/api/chatbot.php',
                './api/chatbot.php',
                '../api/chatbot.php',
                window.location.origin + '/event-booking-website/public/api/chatbot.php',
                window.location.origin + '/public/api/chatbot.php'
            ];

            for (const url of urls) {
                try {
                    console.log('Testing URL:', url);
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' }
                    });
                    if (response.ok) {
                        const text = await response.text();
                        console.log('Response from', url, ':', text.substring(0, 100));
                        if (text.includes('success') || text.includes('API') || text.includes('json')) {
                            config.apiUrl = url;
                            this.apiWorking = true;
                            console.log('Found working API URL:', url);
                            this.addSystemMessage('✅ Chatbot API connection restored!');
                            break;
                        }
                    }
                } catch (e) {
                    console.log('Failed to test', url, ':', e.message);
                    continue;
                }
            }
        }

        toggleChat() {
            this.isOpen = !this.isOpen;
            const window = document.getElementById('chatbot-window');
            const toggle = document.getElementById('chatbot-toggle');

            if (this.isOpen) {
                window.style.display = 'flex';
                toggle.style.transform = 'scale(1.1)';
                document.getElementById('chatbot-input').focus();

                // Show API status if not working
                if (this.apiTested && !this.apiWorking) {
                    this.addSystemMessage('Note: Chatbot API connection failed. Some features limited.');
                }
            } else {
                window.style.display = 'none';
                toggle.style.transform = 'scale(1)';
            }
        }

        openChat() {
            this.isOpen = true;
            document.getElementById('chatbot-window').style.display = 'flex';
            document.getElementById('chatbot-toggle').style.transform = 'scale(1.1)';
            document.getElementById('chatbot-input').focus();
        }

        closeChat() {
            this.isOpen = false;
            document.getElementById('chatbot-window').style.display = 'none';
            document.getElementById('chatbot-toggle').style.transform = 'scale(1)';
        }

        async sendMessage() {
            const input = document.getElementById('chatbot-input');
            const message = input.value.trim();

            if (!message || this.isLoading) return;

            // Add user message
            this.addMessage(message, 'user');
            input.value = '';

            // Show typing indicator
            this.showTyping();

            try {
                // Send with user context
                console.log('Sending message to API:', config.apiUrl);
                const response = await fetch(config.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ message: message })
                });

                console.log('API response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('API response data:', data);

                if (data.success) {
                    setTimeout(() => {
                        this.hideTyping();
                        this.addMessage(data.response, 'bot');

                        // Show debug info in console
                        console.log('Chatbot response:', {
                            user_id: data.user_id,
                            conversation_id: data.conversation_id,
                            session_id: data.session_id
                        });
                    }, 800);
                } else {
                    throw new Error(data.error || 'Failed to get response');
                }

            } catch (error) {
                console.error('Chatbot error:', error);
                this.hideTyping();
                this.addMessage('Sorry, I encountered an error. Please try again.', 'bot');

                // Fallback to basic responses
                const fallback = this.getOfflineResponse(message);
                setTimeout(() => {
                    this.addMessage(fallback, 'bot');
                }, 500);
            }
        }

        getOfflineResponse(message) {
            const lowerMessage = message.toLowerCase();

            // Greetings
            if (/hello|hi|hey/.test(lowerMessage)) {
                return "Hello! I'm your event assistant. (Offline Mode)";
            }

            // Booking
            if (/book|buy|ticket|purchase/.test(lowerMessage)) {
                return "To book tickets: Visit event page → Select tickets → Proceed to checkout → Complete payment. For real-time help, please refresh the page.";
            }

            // Events
            if (/event|show|concert|sports|what's on/.test(lowerMessage)) {
                return "Check our homepage for upcoming events including sports, concerts, and movies. Please refresh to see live event data.";
            }

            // Refund
            if (/refund|cancel|return/.test(lowerMessage)) {
                return "Refunds available up to 48 hours before event. Contact support at support@event-booking.com.";
            }

            // Default
            const responses = [
                "I'm currently in offline mode. Please refresh the page or try again later for full functionality.",
                "For immediate assistance, please email support@event-booking.com or check our FAQ page.",
                "I can help with ticket bookings, event info, and general questions. Try refreshing the page for live responses."
            ];

            return responses[Math.floor(Math.random() * responses.length)];
        }

        addSystemMessage(text) {
            const messages = document.getElementById('chatbot-messages');
            const messageDiv = document.createElement('div');

            messageDiv.innerHTML = `
                <div style="margin-bottom: 15px; text-align: center;">
                    <div style="padding: 8px 12px; background: #fff3cd; color: #856404; 
                                border-radius: 10px; border: 1px solid #ffeaa7; font-size: 12px;
                                display: inline-block; max-width: 90%;">
                        ${this.escapeHtml(text)}
                    </div>
                </div>
            `;

            messages.appendChild(messageDiv);
            messages.scrollTop = messages.scrollHeight;
        }

        addMessage(text, sender) {
            const messages = document.getElementById('chatbot-messages');
            const messageDiv = document.createElement('div');

            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            messageDiv.innerHTML = `
                <div style="margin-bottom: 15px; max-width: 80%; ${sender === 'user' ? 'margin-left: auto;' : ''} 
                           animation: fadeIn 0.3s ease;">
                    <div style="padding: 10px 15px; background: ${sender === 'user' ? 'linear-gradient(135deg, ' + config.colors.primary + ' 0%, ' + config.colors.secondary + ' 100%)' : 'white'}; 
                                color: ${sender === 'user' ? 'white' : '#333'}; border-radius: ${sender === 'user' ? '15px 15px 5px 15px' : '15px 15px 15px 5px'};
                                border: ${sender === 'user' ? 'none' : '1px solid #e9ecef'}; font-size: 14px; line-height: 1.4;
                                word-wrap: break-word; box-shadow: ${sender === 'user' ? '0 2px 5px rgba(0,0,0,0.1)' : 'none'};">
                        ${this.escapeHtml(text).replace(/\n/g, '<br>')}
                    </div>
                    <div style="font-size: 11px; color: #6c757d; margin-top: 4px; text-align: ${sender === 'user' ? 'right' : 'left'}">
                        ${time} ${sender === 'bot' ? '• Assistant' : '• You'}
                    </div>
                </div>
            `;

            // Add fadeIn animation
            if (!document.querySelector('#chatbot-animations')) {
                const animStyle = document.createElement('style');
                animStyle.id = 'chatbot-animations';
                animStyle.textContent = `
                    @keyframes fadeIn {
                        from { opacity: 0; transform: translateY(5px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                `;
                document.head.appendChild(animStyle);
            }

            messages.appendChild(messageDiv);
            messages.scrollTop = messages.scrollHeight;
        }

        showTyping() {
            this.isLoading = true;
            const messages = document.getElementById('chatbot-messages');
            const typingDiv = document.createElement('div');
            typingDiv.id = 'chatbot-typing';

            typingDiv.innerHTML = `
                <div style="margin-bottom: 15px; max-width: 80%;">
                    <div style="padding: 10px 15px; background: white; border-radius: 15px 15px 15px 5px; 
                                border: 1px solid #e9ecef; display: flex; gap: 4px; align-items: center;">
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: ${config.colors.primary}; 
                                    animation: typingDot 1.4s infinite both;"></div>
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: ${config.colors.primary}; 
                                    animation: typingDot 1.4s infinite both; animation-delay: 0.2s;"></div>
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: ${config.colors.primary}; 
                                    animation: typingDot 1.4s infinite both; animation-delay: 0.4s;"></div>
                        <span style="font-size: 12px; color: #6c757d; margin-left: 8px;">Thinking...</span>
                    </div>
                </div>
            `;

            // Add typing animation if not exists
            if (!document.querySelector('#typing-animation')) {
                const typingStyle = document.createElement('style');
                typingStyle.id = 'typing-animation';
                typingStyle.textContent = `
                    @keyframes typingDot {
                        0%, 80%, 100% { transform: scale(0.6); opacity: 0.6; }
                        40% { transform: scale(1); opacity: 1; }
                    }
                `;
                document.head.appendChild(typingStyle);
            }

            messages.appendChild(typingDiv);
            messages.scrollTop = messages.scrollHeight;
        }

        hideTyping() {
            this.isLoading = false;
            const typing = document.getElementById('chatbot-typing');
            if (typing) typing.remove();
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Initialize when page loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.eventChatbot = new SimpleChatbot();
        });
    } else {
        window.eventChatbot = new SimpleChatbot();
    }

})();
