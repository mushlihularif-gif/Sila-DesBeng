{{-- HANYA TAMPIL JIKA BUKAN DI HALAMAN AUTH --}}
@if(!request()->is('auth*') && !request()->routeIs('login') && !request()->routeIs('register'))
@push('styles')
<style>
    /* Chatbot Styles */
    .chatbot-fab {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 65px;
        height: 65px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        cursor: pointer;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s ease;
        user-select: none;
    }
    
    .chatbot-fab:hover {
        transform: scale(1.1);
    }
    
    .chatbot-fab img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .chatbot-fab .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        color: white;
        font-size: 10px;
        font-weight: bold;
        padding: 4px 6px;
        border-radius: 50%;
        border: 2px solid white;
        display: none; /* Show if there is unread message */
    }

    .chatbot-tooltip {
        position: absolute;
        top: -45px;
        right: 0px;
        background: #2563eb;
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        box-shadow: 0 4px 10px rgba(37,99,235,0.3);
        cursor: pointer;
        pointer-events: none;
        opacity: 0;
        transform: translateY(10px);
        transition: opacity 0.4s ease, transform 0.4s ease;
    }

    .chatbot-tooltip.show {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0);
        animation: float-bounce 2.5s infinite ease-in-out;
    }
    
    .chatbot-tooltip .cursor {
        display: inline-block;
        width: 2px;
        animation: blink 1s step-end infinite;
    }
    
    .chatbot-tooltip::after {
        content: '';
        position: absolute;
        bottom: -4px;
        right: 25px;
        width: 12px;
        height: 12px;
        background: #2563eb;
        transform: rotate(45deg);
    }
    
    @keyframes float-bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }
    
    @keyframes blink { 
        50% { opacity: 0; } 
    }

    .chatbot-window {
        position: fixed;
        bottom: 110px;
        right: 30px;
        width: 350px;
        height: 500px;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        z-index: 9998;
        transform: scale(0.9);
        opacity: 0;
        pointer-events: none;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        transform-origin: bottom right;
    }

    .chatbot-window.active {
        transform: scale(1);
        opacity: 1;
        pointer-events: auto;
    }

    .chatbot-header {
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
        color: white;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .chatbot-header-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .chatbot-header-info img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: white;
        padding: 2px;
        object-fit: cover;
    }

    .chatbot-title {
        font-weight: 700;
        font-size: 16px;
        margin: 0;
    }

    .chatbot-subtitle {
        font-size: 11px;
        opacity: 0.8;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .chatbot-subtitle .online-dot {
        width: 6px;
        height: 6px;
        background: #4ade80;
        border-radius: 50%;
        display: inline-block;
    }

    .chatbot-close {
        background: transparent;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;
        padding: 0;
    }

    .chatbot-close:hover {
        opacity: 1;
    }

    .chatbot-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 12px;
        scrollbar-width: thin;
    }
    
    .chatbot-messages::-webkit-scrollbar {
        width: 6px;
    }
    .chatbot-messages::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .msg-bubble {
        max-width: 80%;
        padding: 12px 16px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.5;
        position: relative;
        word-wrap: break-word;
    }

    .msg-bot {
        align-self: flex-start;
        background: white;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }

    .msg-user {
        align-self: flex-end;
        background: #2563eb;
        color: white;
        border-bottom-right-radius: 4px;
        box-shadow: 0 2px 5px rgba(37,99,235,0.2);
    }

    .chatbot-input-area {
        padding: 15px;
        background: white;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }

    .chatbot-input-area textarea {
        flex: 1;
        background: #f1f5f9;
        border: none;
        border-radius: 20px;
        padding: 10px 15px;
        font-size: 14px;
        resize: none;
        max-height: 100px;
        min-height: 40px;
        outline: none;
        font-family: inherit;
    }
    
    .chatbot-input-area textarea:focus {
        background: #e2e8f0;
    }

    .chatbot-send-btn {
        background: #2563eb;
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s, background 0.2s;
        flex-shrink: 0;
        padding: 0;
    }

    .chatbot-send-btn:hover {
        background: #1d4ed8;
        transform: scale(1.05);
    }

    .chatbot-send-btn:disabled {
        background: #94a3b8;
        cursor: not-allowed;
        transform: scale(1);
    }
    
    /* Loading typing effect */
    .typing-indicator {
        display: flex;
        gap: 4px;
        padding: 6px 12px;
    }
    
    .typing-indicator span {
        width: 6px;
        height: 6px;
        background: #94a3b8;
        border-radius: 50%;
        animation: typing 1s infinite alternate;
    }
    
    .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
    
    @keyframes typing {
        from { transform: translateY(0); opacity: 0.5; }
        to { transform: translateY(-4px); opacity: 1; }
    }
    
    /* Mobile adjustments */
    @media (max-width: 480px) {
        .chatbot-window {
            width: calc(100% - 40px);
            right: 20px;
            bottom: 90px;
            height: calc(100vh - 120px);
            max-height: 600px;
        }
        
        .chatbot-fab {
            bottom: 20px;
            right: 20px;
            width: 55px;
            height: 55px;
        }
    }
</style>
@endpush

<!-- Floating Action Button -->
<div class="chatbot-fab" id="chatbot-fab">
    <div class="chatbot-tooltip" id="chatbot-tooltip"><span id="tooltip-text"></span><span class="cursor">|</span></div>
    <div class="notification-badge" id="chatbot-badge">1</div>
    <img src="{{ asset('User/img/logo/logocb.webp') }}" alt="SiladesBeng Assistant" draggable="false">
</div>

<!-- Chat Window -->
<div class="chatbot-window" id="chatbot-window">
    <div class="chatbot-header">
        <div class="chatbot-header-info">
            <img src="{{ asset('User/img/logo/logocb.webp') }}" alt="AI">
            <div>
                <h4 class="chatbot-title">SiladesBeng Assistant</h4>
                <div class="chatbot-subtitle"><span class="online-dot"></span> Online - AI Ready</div>
            </div>
        </div>
        <button class="chatbot-close" id="chatbot-close">&times;</button>
    </div>
    
    <div class="chatbot-messages" id="chatbot-messages">
        <!-- Default Welcome Message -->
        <div class="msg-bubble msg-bot">
            Halo! Saya SiladesBeng Assistant ðŸ‘‹<br><br>Ada yang bisa saya bantu hari ini tentang cara penyewaan alat, pembelian gas, pelaporan, atau layanan kami lainnya?
        </div>
    </div>
    
    <div class="chatbot-input-area">
        <textarea id="chatbot-input" placeholder="Ketik pesan Anda..." rows="1" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea>
        <button class="chatbot-send-btn" id="chatbot-send" aria-label="Send Message">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
        </button>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const fab = document.getElementById('chatbot-fab');
    const windowEl = document.getElementById('chatbot-window');
    const closeBtn = document.getElementById('chatbot-close');
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send');
    const messagesContainer = document.getElementById('chatbot-messages');
    const badge = document.getElementById('chatbot-badge');
    const tooltip = document.getElementById('chatbot-tooltip');
    
    let chatHistory = [];
    let isDragging = false;
    let startX, startY, initialX, initialY;
    
    function saveHistory() {
        sessionStorage.setItem('SiladesBeng_chat_history', JSON.stringify(chatHistory));
    }

    // Restore History
    let savedHistory = sessionStorage.getItem('SiladesBeng_chat_history');
    if (savedHistory) {
        try {
            chatHistory = JSON.parse(savedHistory);
            chatHistory.forEach(msg => {
                if (msg.role === 'user') {
                    appendMessage(msg.text, 'user');
                } else if (msg.role === 'model') {
                    let formattedReply = msg.text
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.*?)\*/g, '<em>$1</em>')
                        .replace(/\n/g, '<br>');
                    appendMessage(formattedReply, 'bot', true);
                }
            });
        } catch(e) {}
    }

    // Restore Window State
    let isChatOpen = sessionStorage.getItem('SiladesBeng_chat_open') === 'true';
    if (isChatOpen) {
        windowEl.classList.add('active');
        badge.style.display = 'none';
        tooltip.classList.remove('show');
        setTimeout(scrollToBottom, 100);
    }
    
    // Auto-show badge after 3 seconds to get attention
    setTimeout(() => {
        if(!windowEl.classList.contains('active') && chatHistory.length === 0) {
            badge.style.display = 'block';
        }
    }, 3000);
    
    // Tooltip Animation Logic
    const tooltipTextEl = document.getElementById('tooltip-text');
    const fullText = "Tanya Assistant?";
    
    // Clear previous intervals if Turbo navigates
    if (window.chatbotTooltipInterval) clearInterval(window.chatbotTooltipInterval);
    if (window.chatbotTypingInterval) clearInterval(window.chatbotTypingInterval);

    function playTooltipAnimation() {
        if(windowEl.classList.contains('active')) return; // Don't play if chat is open

        tooltip.classList.add('show');
        tooltipTextEl.textContent = "";
        
        let i = 0;
        if (window.chatbotTypingInterval) clearInterval(window.chatbotTypingInterval);
        window.chatbotTypingInterval = setInterval(() => {
            if(windowEl.classList.contains('active')) {
                clearInterval(window.chatbotTypingInterval);
                return;
            }
            tooltipTextEl.textContent += fullText.charAt(i);
            i++;
            if(i >= fullText.length) {
                clearInterval(window.chatbotTypingInterval);
                // Hide after 4 seconds of reading time
                setTimeout(() => {
                    if(!windowEl.classList.contains('active')) {
                        tooltip.classList.remove('show');
                    }
                }, 4000);
            }
        }, 100); // 100ms per letter typing speed
    }

    // Start cycle: wait 2 seconds, play once, then repeat every 7 seconds
    setTimeout(() => {
        playTooltipAnimation();
        window.chatbotTooltipInterval = setInterval(playTooltipAnimation, 7000);
    }, 2000);

    // Draggable Logic for FAB
    fab.addEventListener('mousedown', dragStart);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', dragEnd);
    
    // Touch support
    fab.addEventListener('touchstart', dragStart, {passive: false});
    document.addEventListener('touchmove', drag, {passive: false});
    document.addEventListener('touchend', dragEnd);

    function dragStart(e) {
        if (e.target.closest('#chatbot-badge')) return; // Ignore if clicking badge
        
        if (e.type === 'touchstart') {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        } else {
            startX = e.clientX;
            startY = e.clientY;
        }
        
        initialX = fab.offsetLeft;
        initialY = fab.offsetTop;
    }

    function drag(e) {
        if (startX === undefined) return;
        
        let currentX, currentY;
        if (e.type === 'touchmove') {
            currentX = e.touches[0].clientX;
            currentY = e.touches[0].clientY;
        } else {
            currentX = e.clientX;
            currentY = e.clientY;
        }
        
        let dx = currentX - startX;
        let dy = currentY - startY;
        
        // If moved more than 15px, consider it a drag (prevent accidental clicks)
        if (Math.abs(dx) > 15 || Math.abs(dy) > 15) {
            isDragging = true;
            
            if(e.type === 'touchmove') e.preventDefault();
            
            let newX = initialX + dx;
            let newY = initialY + dy;
            
            let maxX = document.documentElement.clientWidth - fab.offsetWidth;
            let maxY = document.documentElement.clientHeight - fab.offsetHeight;
            
            newX = Math.max(0, Math.min(newX, maxX));
            newY = Math.max(0, Math.min(newY, maxY));
            
            fab.style.right = 'auto';
            fab.style.bottom = 'auto';
            fab.style.left = newX + 'px';
            fab.style.top = newY + 'px';
        }
    }

    function dragEnd(e) {
        if (startX === undefined) return;
        startX = undefined;
        startY = undefined;
        
        setTimeout(() => {
            isDragging = false;
        }, 50);
    }

    // Toggle Window
    fab.addEventListener('click', (e) => {
        if (isDragging) return; // Ignore click if we were dragging
        windowEl.classList.toggle('active');
        badge.style.display = 'none'; // Hide badge when opened
        
        if (windowEl.classList.contains('active')) {
            tooltip.classList.remove('show'); // Hide tooltip when opened
            setTimeout(() => input.focus(), 300);
            scrollToBottom();
        }
        sessionStorage.setItem('SiladesBeng_chat_open', windowEl.classList.contains('active'));
    });

    closeBtn.addEventListener('click', () => {
        windowEl.classList.remove('active');
        sessionStorage.setItem('SiladesBeng_chat_open', 'false');
    });

    // Send Message
    function sendMessage() {
        const text = input.value.trim();
        if (!text) return;

        appendMessage(text, 'user');
        chatHistory.push({role: 'user', text: text});
        saveHistory();
        
        input.value = '';
        input.style.height = '40px'; 
        input.focus();
        
        input.disabled = true;
        sendBtn.disabled = true;
        
        const loadingId = appendLoading();
        scrollToBottom();

        fetch('/chatbot/ask', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: text,
                history: chatHistory.slice(-10) // Only send last 10 messages for context
            })
        })
        .then(response => response.json())
        .then(data => {
            removeLoading(loadingId);
            input.disabled = false;
            sendBtn.disabled = false;
            input.focus();
            
            if (data.reply) {
                // Parse markdown-like bold (**) and newlines to HTML
                let formattedReply = data.reply
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/\n/g, '<br>');
                
                appendMessage(formattedReply, 'bot', true);
                chatHistory.push({role: 'model', text: data.reply});
                saveHistory();
            } else if (data.error) {
                appendMessage("âŒ Error: " + data.error, 'bot');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            removeLoading(loadingId);
            input.disabled = false;
            sendBtn.disabled = false;
            appendMessage("âŒ Maaf, koneksi terputus. Silakan periksa jaringan internet Anda.", 'bot');
        });
    }

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    function appendMessage(text, sender, isHtml = false) {
        const div = document.createElement('div');
        div.className = `msg-bubble msg-${sender}`;
        if(isHtml) {
            div.innerHTML = text;
        } else {
            div.textContent = text;
        }
        messagesContainer.appendChild(div);
        scrollToBottom();
    }
    
    function appendLoading() {
        const id = 'loading-' + Date.now();
        const div = document.createElement('div');
        div.id = id;
        div.className = `msg-bubble msg-bot typing-indicator`;
        div.innerHTML = `<span></span><span></span><span></span>`;
        messagesContainer.appendChild(div);
        return id;
    }
    
    function removeLoading(id) {
        const el = document.getElementById(id);
        if(el) el.remove();
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
})();
</script>
@endpush
@endif

