{{-- Reusable User Service Chat Widget --}}
@php
    $serviceType = $serviceType ?? 'gas';
    $serviceTitle = $serviceTitle ?? 'Layanan Desa';
    $itemRef = $itemRef ?? '';
    
    $quickReplies = [
        'gas' => [
            'Stok tabung ready hari ini?',
            'Bisa diantar ke rumah?',
            'Bagaimana cara tukar tabung?',
        ],
        'penyewaan' => [
            'Apakah alat siap pakai?',
            'Berapa tarif sewa per hari?',
            'Ketentuan SOP tanggung jawab alat?',
        ],
        'mobil' => [
            'Armada mobil tersedia?',
            'Bisa lepas kunci atau supir?',
            'Syarat sewa apa saja?',
        ],
        'fasilitas_umum' => [
            'Jadwal gedung kosong kapan?',
            'Berapa kapasitas ruangan?',
            'Prosedur peminjaman fasilitas?',
        ],
    ][$serviceType] ?? [
        'Halo admin, saya ingin bertanya.',
        'Ketersediaan layanan ini?',
    ];
@endphp

<!-- Floating Chat Launcher Button -->
<div id="serviceChatLauncher_{{ $serviceType }}" class="service-chat-launcher" onclick="toggleServiceChatWidget('{{ $serviceType }}')" title="Tanya Pengelola {{ $serviceTitle }}">
    <div class="launcher-icon">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
    </div>
    <span class="launcher-text">Chat {{ $serviceTitle }}</span>
</div>

<!-- Chat Widget Box -->
<div id="serviceChatWidget_{{ $serviceType }}" class="service-chat-widget">
    <!-- Header -->
    <div class="service-chat-header">
        <div class="d-flex align-items-center gap-2">
            <div class="service-chat-avatar">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <div>
                <div class="service-chat-title fw-bold">Chat {{ $serviceTitle }}</div>
                <div class="service-chat-status">
                    <span class="status-dot"></span> Online &bull; Petugas BUMDes
                </div>
            </div>
        </div>
        <button type="button" class="service-chat-close-btn" onclick="closeServiceChatWidget('{{ $serviceType }}')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Privacy Notice -->
    <div class="service-chat-privacy">
        <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
        <span>Privasi Terjaga: Komunikasi aman di dalam sistem</span>
    </div>

    <!-- Quick Reply Chips -->
    <div class="service-chat-quick-replies" id="serviceQuickReplies_{{ $serviceType }}">
        <button type="button" class="service-chip-btn bg-primary text-white border-0 fw-semibold" onclick="escalateServiceChat('{{ $serviceType }}')">
            Chat Petugas Desa
        </button>
        @foreach($quickReplies as $chip)
            <button type="button" class="service-chip-btn" onclick="sendServiceQuickReply('{{ $serviceType }}', '{{ addslashes($chip) }}')">
                {{ $chip }}
            </button>
        @endforeach
    </div>

    <!-- Messages Container -->
    <div class="service-chat-body" id="serviceChatMessages_{{ $serviceType }}">
        <div class="text-center py-4 text-muted small">
            <span class="spinner-border spinner-border-sm text-primary me-1"></span> Menghubungkan ke layanan...
        </div>
    </div>

    <!-- Typing Indicator -->
    <div id="serviceTypingIndicator_{{ $serviceType }}" class="px-3 pb-1" style="display: none;">
        <div class="d-flex align-items-center gap-1 text-muted" style="font-size: 11px;">
            <span>Petugas sedang membalas...</span>
        </div>
    </div>

    <!-- Input Footer -->
    <div class="service-chat-footer">
        <input type="text" id="serviceChatInput_{{ $serviceType }}" class="service-chat-input" placeholder="Tulis pertanyaan..." onkeypress="if(event.key==='Enter') sendServiceChatMessage('{{ $serviceType }}')">
        <button type="button" class="service-chat-send-btn" onclick="sendServiceChatMessage('{{ $serviceType }}')">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"></path></svg>
        </button>
    </div>
</div>

<style>
    .service-chat-launcher {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9998;
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        color: #ffffff;
        padding: 10px 18px;
        border-radius: 50rem;
        box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.4);
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        font-weight: 600;
        font-size: 0.9rem;
    }
    .service-chat-launcher:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 15px 30px -5px rgba(2, 132, 199, 0.5);
    }
    .service-chat-widget {
        position: fixed;
        bottom: 80px;
        right: 24px;
        width: 360px;
        max-width: calc(100vw - 32px);
        height: 520px;
        max-height: calc(100vh - 110px);
        background: #ffffff;
        border-radius: 1.25rem;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.25);
        z-index: 9999;
        display: none;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .service-chat-widget.active {
        display: flex;
        animation: chatSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes chatSlideUp {
        from { opacity: 0; transform: translateY(20px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .service-chat-header {
        background: #ffffff;
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .service-chat-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #0284c7;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .service-chat-title {
        font-size: 0.95rem;
        color: #0f172a;
    }
    .service-chat-status {
        font-size: 11px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
    }
    .service-chat-close-btn {
        background: transparent;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 4px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .service-chat-close-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .service-chat-privacy {
        background: #f0fdf4;
        padding: 6px 14px;
        font-size: 11px;
        color: #166534;
        display: flex;
        align-items: center;
        gap: 6px;
        border-bottom: 1px solid #dcfce7;
    }
    .service-chat-quick-replies {
        display: flex;
        gap: 6px;
        padding: 8px 12px;
        background: #f8fafc;
        overflow-x: auto;
        white-space: nowrap;
        border-bottom: 1px solid #e2e8f0;
    }
    .service-chip-btn {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        padding: 4px 10px;
        border-radius: 50rem;
        font-size: 11px;
        color: #334155;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    .service-chip-btn:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }
    .service-chat-body {
        flex: 1;
        padding: 14px;
        overflow-y: auto;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .chat-bubble-warga {
        align-self: flex-end;
        background: #0284c7;
        color: #ffffff;
        padding: 10px 14px;
        border-radius: 14px;
        border-bottom-right-radius: 3px;
        max-width: 80%;
        font-size: 0.88rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .chat-bubble-admin {
        align-self: flex-start;
        background: #ffffff;
        color: #0f172a;
        padding: 10px 14px;
        border-radius: 14px;
        border-bottom-left-radius: 3px;
        max-width: 80%;
        font-size: 0.88rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .chat-bubble-bot {
        align-self: center;
        background: #e0f2fe;
        color: #0369a1;
        padding: 8px 14px;
        border-radius: 50rem;
        font-size: 11px;
        max-width: 90%;
        text-align: center;
        line-height: 1.4;
    }
    .chat-bubble-time {
        font-size: 9px;
        margin-top: 3px;
        opacity: 0.7;
    }
    .service-chat-footer {
        padding: 10px 12px;
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .service-chat-input {
        flex: 1;
        border: 1px solid #cbd5e1;
        border-radius: 50rem;
        padding: 8px 16px;
        font-size: 0.88rem;
        outline: none;
    }
    .service-chat-input:focus {
        border-color: #0284c7;
    }
    .service-chat-send-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #0284c7;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
    }
    .service-chat-send-btn:hover {
        background: #0369a1;
    }
</style>

<script>
    if (typeof window.unitUserChatState === 'undefined') {
        window.unitUserChatState = {};
    }

    function toggleServiceChatWidget(service) {
        const widget = document.getElementById(`serviceChatWidget_${service}`);
        if (widget.classList.contains('active')) {
            closeServiceChatWidget(service);
        } else {
            widget.classList.add('active');
            loadServiceChatHistory(service);
            startServiceChatPolling(service);
        }
    }

    function closeServiceChatWidget(service) {
        const widget = document.getElementById(`serviceChatWidget_${service}`);
        widget.classList.remove('active');
        if (window.unitUserChatState[service] && window.unitUserChatState[service].pollTimer) {
            clearInterval(window.unitUserChatState[service].pollTimer);
            window.unitUserChatState[service].pollTimer = null;
        }
    }

    function loadServiceChatHistory(service, isSilent = false) {
        const tokenKey = `unit_chat_token_${service}`;
        const sessionToken = localStorage.getItem(tokenKey) || '';

        fetch(`{{ url('api/unit-chat') }}/${service}/history`, {
            headers: {
                'X-Chat-Session-Token': sessionToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const session = res.data.session;
                if (session.session_token && !sessionToken) {
                    localStorage.setItem(tokenKey, session.session_token);
                }

                const container = document.getElementById(`serviceChatMessages_${service}`);
                container.innerHTML = '';

                (res.data.messages || []).forEach(msg => {
                    renderServiceMessageBubble(service, msg);
                });

                container.scrollTop = container.scrollHeight;
            }
        })
        .catch(err => console.error(err));
    }

    function renderServiceMessageBubble(service, msg) {
        const container = document.getElementById(`serviceChatMessages_${service}`);
        const bubble = document.createElement('div');
        const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        if (msg.sender_type === 'user') {
            bubble.className = 'chat-bubble-warga';
            bubble.innerHTML = `
                <div>${escapeHtmlService(msg.message)}</div>
                <div class="chat-bubble-time text-end">${time}</div>
            `;
        } else if (msg.sender_type === 'admin') {
            bubble.className = 'chat-bubble-admin';
            bubble.innerHTML = `
                <div class="fw-semibold text-primary mb-1" style="font-size: 11px;">Petugas BUMDes</div>
                <div>${escapeHtmlService(msg.message)}</div>
                <div class="chat-bubble-time text-end text-muted">${time}</div>
            `;
        } else {
            bubble.className = 'chat-bubble-bot';
            bubble.innerHTML = `<div>${escapeHtmlService(msg.message)}</div>`;
        }

        container.appendChild(bubble);
    }

    function sendServiceQuickReply(service, text) {
        const input = document.getElementById(`serviceChatInput_${service}`);
        input.value = text;
        sendServiceChatMessage(service);
    }

    function sendServiceChatMessage(service) {
        const input = document.getElementById(`serviceChatInput_${service}`);
        const text = input.value.trim();
        if (!text) return;

        input.value = '';

        // Immediate push
        renderServiceMessageBubble(service, {
            sender_type: 'user',
            message: text,
            created_at: new Date().toISOString()
        });

        const container = document.getElementById(`serviceChatMessages_${service}`);
        container.scrollTop = container.scrollHeight;

        const tokenKey = `unit_chat_token_${service}`;
        const sessionToken = localStorage.getItem(tokenKey) || '';

        fetch(`{{ url('api/unit-chat') }}/${service}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Chat-Session-Token': sessionToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                message: text,
                session_token: sessionToken,
                item_reference: '{{ addslashes($itemRef) }}'
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                if (res.data.session_token) {
                    localStorage.setItem(tokenKey, res.data.session_token);
                }
                if (res.data.bot_message) {
                    renderServiceMessageBubble(service, res.data.bot_message);
                    container.scrollTop = container.scrollHeight;
                }
            }
        })
        .catch(err => console.error(err));
    }

    function escalateServiceChat(service) {
        const tokenKey = `unit_chat_token_${service}`;
        const sessionToken = localStorage.getItem(tokenKey) || '';

        fetch(`{{ url('api/unit-chat') }}/${service}/escalate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Chat-Session-Token': sessionToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ session_token: sessionToken })
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                if (res.data.bot_message) {
                    renderServiceMessageBubble(service, res.data.bot_message);
                    const container = document.getElementById(`serviceChatMessages_${service}`);
                    container.scrollTop = container.scrollHeight;
                }
            }
        });
    }

    function startServiceChatPolling(service) {
        if (!window.unitUserChatState[service]) {
            window.unitUserChatState[service] = {};
        }
        if (window.unitUserChatState[service].pollTimer) {
            clearInterval(window.unitUserChatState[service].pollTimer);
        }

        window.unitUserChatState[service].pollTimer = setInterval(() => {
            loadServiceChatHistory(service, true);
        }, 4000);
    }

    function escapeHtmlService(string) {
        if (!string) return '';
        return String(string).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
</script>
