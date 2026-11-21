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
        bottom: 104px;
        right: 28px;
        width: 350px;
        max-width: calc(100vw - 24px);
        height: 480px;
        max-height: calc(100vh - 130px);
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
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease, width 0.22s ease, height 0.22s ease, max-height 0.22s ease;
        transform-origin: bottom right;
    }

    .chatbot-window.is-expanded {
        width: min(660px, calc(100vw - 2rem));
        max-width: calc(100vw - 2rem);
        height: min(82vh, 720px);
        max-height: calc(100vh - 96px);
    }

    .chatbot-window.active {
        transform: scale(1);
        opacity: 1;
        pointer-events: auto;
    }

    .chatbot-header {
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
        color: white;
        padding: 13px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .chatbot-header-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }

    .chatbot-expand-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.28);
        color: #ffffff;
        border-radius: 999px;
        padding: 4px 9px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        white-space: nowrap;
    }

    .chatbot-expand-btn:hover {
        background: rgba(255, 255, 255, 0.28);
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
        padding: 9px 15px;
        font-size: 14px;
        line-height: 1.4;
        resize: none;
        overflow-y: hidden;
        max-height: 100px;
        min-height: 40px;
        outline: none;
        font-family: inherit;
        box-sizing: border-box;
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
    @media (max-width: 768px) {
        .chatbot-window {
            width: calc(100% - 24px) !important;
            right: 12px !important;
            bottom: 84px !important;
            height: calc(100vh - 140px) !important;
            max-height: 480px !important;
        }
        .chatbot-window.is-expanded {
            width: calc(100vw - 16px) !important;
            right: 8px !important;
            bottom: 78px !important;
            height: calc(100vh - 94px) !important;
            max-height: calc(100vh - 94px) !important;
        }
        
        .chatbot-fab {
            bottom: 20px !important;
            right: 18px !important;
            width: 56px !important;
            height: 56px !important;
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4) !important;
            z-index: 99999 !important;
        }

        .chatbot-tooltip {
            font-size: 11px !important;
            padding: 4px 10px !important;
            top: -34px !important;
            right: 0px !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3) !important;
        }
    }
    @media (max-width: 420px) {
        .chatbot-expand-btn span {
            display: none;
        }
        .chatbot-expand-btn {
            padding: 5px 7px;
        }
        .chatbot-title {
            font-size: 14px;
        }
    }
</style>
@endpush

<!-- Floating Action Button -->
<div class="chatbot-fab notranslate" translate="no" id="chatbot-fab">
    <div class="chatbot-tooltip notranslate" translate="no" id="chatbot-tooltip"><span id="tooltip-text"></span><span class="cursor">|</span></div>
    <div class="notification-badge" id="chatbot-badge">1</div>
    <img src="{{ asset('User/img/logo/logocb.webp') }}" alt="SegiDaerah Assistant" draggable="false">
</div>

<!-- Chat Window -->
<div class="chatbot-window notranslate" translate="no" id="chatbot-window">
    <div class="chatbot-header">
        <div class="chatbot-header-info">
            <img src="{{ asset('User/img/logo/logocb.webp') }}" alt="AI">
            <div>
                <h4 class="chatbot-title">SegiDaerah Assistant</h4>
                <div class="chatbot-subtitle"><span class="online-dot"></span> Online - Siap Membantu</div>
            </div>
        </div>
        <div class="chatbot-header-actions">
            <button type="button" class="chatbot-expand-btn" id="chatbot-expand" title="Perbesar panel assistant" aria-expanded="false">
                <svg id="chatbot-expand-icon" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"></path></svg>
                <span id="chatbot-expand-label">Perbesar</span>
            </button>
            <button class="chatbot-close" id="chatbot-close" title="Tutup">&times;</button>
        </div>
    </div>
    
    <div class="chatbot-messages" id="chatbot-messages">
        <!-- Default Welcome Message -->
        <div class="msg-bubble msg-bot">
            Halo! Saya <strong>SegiDaerah Assistant</strong>.<br><br>Silakan tanya seputar layanan dan cara penggunaan aplikasi SegiDaerah, maupun pertanyaan umum lainnya!
        </div>
    </div>
    
    <div class="chatbot-input-area">
        <textarea id="chatbot-input" placeholder="Ketik pesan Anda..." rows="1" oninput="this.style.height = '40px'; const h = Math.min(this.scrollHeight, 100); this.style.height = h + 'px'; this.style.overflowY = this.scrollHeight > 100 ? 'auto' : 'hidden';"></textarea>
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
    const expandBtn = document.getElementById('chatbot-expand');
    const expandIcon = document.getElementById('chatbot-expand-icon');
    const expandLabel = document.getElementById('chatbot-expand-label');
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send');
    const messagesContainer = document.getElementById('chatbot-messages');
    const badge = document.getElementById('chatbot-badge');
    const tooltip = document.getElementById('chatbot-tooltip');
    
    let chatHistory = [];
    let isDragging = false;
    let startX, startY, initialX, initialY;

    function formatAssistantReply(rawText) {
        const escaped = String(rawText ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
        return escaped
            .replace(/`([^`]+)`/g, '<code style="background:#f1f5f9;padding:1px 5px;border-radius:4px;font-size:12.5px;">$1</code>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');
    }
    
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
                    appendMessage(formatAssistantReply(msg.text), 'bot', true);
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

    // Reset FAB position if screen resized or device emulation toggled
    window.addEventListener('resize', () => {
        if (fab.style.left || fab.style.top) {
            let maxX = document.documentElement.clientWidth - fab.offsetWidth;
            let maxY = document.documentElement.clientHeight - fab.offsetHeight;
            let currentLeft = parseInt(fab.style.left, 10);
            let currentTop = parseInt(fab.style.top, 10);
            if (!isNaN(currentLeft) && currentLeft > maxX) {
                fab.style.left = '';
                fab.style.right = '';
            }
            if (!isNaN(currentTop) && currentTop > maxY) {
                fab.style.top = '';
                fab.style.bottom = '';
            }
        }
    });

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

    if (expandBtn) {
        expandBtn.addEventListener('click', () => {
            const expanded = windowEl.classList.toggle('is-expanded');
            expandBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            expandBtn.setAttribute('title', expanded ? 'Kecilkan panel assistant' : 'Perbesar panel assistant');
            if (expandLabel) expandLabel.textContent = expanded ? 'Kecilkan' : 'Perbesar';
            if (expandIcon) {
                expandIcon.innerHTML = expanded
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 14h6v6M20 10h-6V4M14 10l7-7M10 14l-7 7"></path>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"></path>';
            }
        });
    }

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
        input.style.overflowY = 'hidden';
        input.focus();

        input.disabled = true;
        sendBtn.disabled = true;

        const loadingId = appendLoading();
        scrollToBottom();

        fetch('/chatbot/ask', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: text,
                history: chatHistory.slice(-10)
            })
        })
        .then(response => response.json())
        .then(data => {
            removeLoading(loadingId);
            input.disabled = false;
            sendBtn.disabled = false;
            input.focus();

            if (data.reply) {
                appendMessage(formatAssistantReply(data.reply), 'bot', true);
                chatHistory.push({role: 'model', text: data.reply});
                saveHistory();
            } else if (data.error || data.message) {
                appendMessage('Maaf, terjadi kendala: ' + (data.error || data.message), 'bot');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            removeLoading(loadingId);
            input.disabled = false;
            sendBtn.disabled = false;
            appendMessage('Maaf, koneksi terputus. Silakan periksa jaringan internet Anda lalu coba lagi.', 'bot');
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
