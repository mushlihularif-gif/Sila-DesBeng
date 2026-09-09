@props([
    'service',
    'title' => 'Layanan BUMDes',
    'regionId',
    'regionName',
    'itemName' => '',
    'itemPrice' => '',
    'itemImage' => ''
])

@php
    $cleanRegionName = str_replace(['Desa ', 'Kelurahan '], '', $regionName);
@endphp

<!-- IN-APP UNIT CHAT WIDGET & MODAL (PRIVASI TERJAGA) -->
<div class="toko-chat-widget" id="unitChatWidget">
    <!-- Header -->
    <div class="toko-chat-header">
        <div class="toko-chat-header-info">
            <div class="toko-chat-avatar-wrap">
                <div class="toko-chat-avatar flex items-center justify-center bg-sky-600 text-white font-black text-sm">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <span class="toko-chat-status-dot"></span>
            </div>
            <div class="toko-chat-header-text">
                <div class="toko-chat-header-title">{{ $title }}</div>
                <div class="toko-chat-header-subtitle">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Admin {{ $cleanRegionName }} &bull; Online</span>
                </div>
            </div>
        </div>
        <button type="button" onclick="closeUnitChat()" class="toko-chat-close-btn" title="Tutup Chat">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Privacy Shield Notice -->
    <div class="toko-chat-privacy">
        <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
        <span>Privasi Terjaga: Identitas Anda aman terlindungi</span>
    </div>

    <!-- Quick Reply Chips -->
    <div class="toko-chat-quick-replies" id="unitQuickReplies">
        <button type="button" class="toko-chip-btn bg-blue-50 text-blue-700 border-blue-200 font-bold" onclick="escalateToAdminUnit()">Chat Petugas Admin</button>
        <button type="button" class="toko-chip-btn" onclick="sendUnitQuickReply('Halo, apakah layanan ini masih tersedia?')">Masih tersedia?</button>
        <button type="button" class="toko-chip-btn" onclick="sendUnitQuickReply('Bisa dibantu untuk proses pesanannya?')">Cara pesan?</button>
        <button type="button" class="toko-chip-btn" onclick="sendUnitQuickReply('Apakah ada biaya tambahan?')">Biaya lain?</button>
    </div>


    <!-- Chat Stream Body -->
    <div class="toko-chat-body" id="unitChatMessages">
        <!-- Greeting Bubble -->
        <div class="toko-chat-bubble toko">
            <div>Halo{{ Auth::check() ? ' Kak ' . Auth::user()->name : ' Kak' }}! Selamat datang di {{ $title }} BUMDes {{ $cleanRegionName }}. Ada yang bisa kami bantu?</div>
            <div class="toko-chat-time">{{ date('H:i') }}</div>
        </div>
    </div>

    <!-- Typing Indicator -->
    <div id="unitTypingIndicator" class="px-3 pb-1" style="display: none;">
        <div class="toko-typing-indicator">
            <span class="text-[11px] text-slate-500 font-medium mr-1">Admin mengetik</span>
            <div class="toko-typing-dot"></div>
            <div class="toko-typing-dot"></div>
            <div class="toko-typing-dot"></div>
        </div>
    </div>

    <!-- Footer / Input -->
    <div class="toko-chat-footer">
        <input type="text" id="unitChatInput" class="toko-chat-input" placeholder="Tulis pesan..." autocomplete="off" onkeypress="if(event.key==='Enter') sendUnitMessage()">
        <button type="button" onclick="sendUnitMessage()" class="toko-chat-send-btn" title="Kirim Pesan">
            <svg class="w-4 h-4" style="margin-left: 2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"></path>
            </svg>
        </button>
    </div>
</div>

<style>
    /* In-App Chat Widget Styles */
    .toko-chat-widget {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 380px;
        max-width: calc(100vw - 32px);
        height: 600px;
        max-height: calc(100vh - 100px);
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.2), 0 0 15px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        z-index: 100000;
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
        transform: translateY(20px) scale(0.95);
        transform-origin: bottom right;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1px solid #e2e8f0;
    }
    .toko-chat-widget.active {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }
    
    .toko-chat-header {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        padding: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
    }
    .toko-chat-header-info { display: flex; align-items: center; gap: 12px; }
    .toko-chat-avatar-wrap { position: relative; }
    .toko-chat-avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.3); }
    .toko-chat-status-dot { position: absolute; bottom: 2px; right: 2px; width: 12px; height: 12px; background: #34d399; border-radius: 50%; border: 2px solid #fff; }
    .toko-chat-header-text { display: flex; flex-direction: column; }
    .toko-chat-header-title { font-weight: 700; font-size: 1.05rem; line-height: 1.2; text-shadow: 0 1px 2px rgba(0,0,0,0.1); }
    .toko-chat-header-subtitle { font-size: 0.75rem; color: #e0f2fe; display: flex; align-items: center; gap: 4px; margin-top: 3px; font-weight: 500; }
    .toko-chat-close-btn { color: white; opacity: 0.8; padding: 8px; border-radius: 50%; transition: all 0.2s; }
    .toko-chat-close-btn:hover { background: rgba(255,255,255,0.15); opacity: 1; }
    
    .toko-chat-privacy { background: #ecfdf5; padding: 6px 16px; font-size: 0.7rem; color: #059669; display: flex; align-items: center; gap: 6px; font-weight: 600; border-bottom: 1px solid #d1fae5; }
    
    .toko-chat-quick-replies {
        display: flex; gap: 8px; overflow-x: auto; padding: 12px 16px; background: #f8fafc; border-bottom: 1px solid #f1f5f9;
        scrollbar-width: none;
    }
    .toko-chat-quick-replies::-webkit-scrollbar { display: none; }
    .toko-chip-btn {
        white-space: nowrap; padding: 6px 12px; background: white; border: 1px solid #e2e8f0; border-radius: 16px;
        font-size: 0.75rem; color: #475569; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    .toko-chip-btn:hover { background: #f1f5f9; border-color: #cbd5e1; color: #0f172a; }
    
    .toko-chat-body {
        flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; background: #ffffff;
    }
    .toko-chat-bubble {
        max-width: 85%; padding: 10px 14px; border-radius: 16px; font-size: 0.875rem; line-height: 1.5; position: relative;
    }
    .toko-chat-bubble.toko {
        background: #f1f5f9; color: #334155; align-self: flex-start; border-bottom-left-radius: 4px; border: 1px solid #e2e8f0;
    }
    .toko-chat-bubble.admin {
        background: #f0f9ff; color: #0c4a6e; align-self: flex-start; border-bottom-left-radius: 4px; border: 1px solid #bae6fd;
    }
    .toko-chat-admin-badge { display: flex; align-items: center; gap: 4px; font-size: 0.7rem; font-weight: 700; color: #0284c7; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.02em; }
    .toko-chat-escalate-btn {
        margin-top: 8px; display: flex; align-items: center; gap: 6px; padding: 6px 12px; background: #0ea5e9; color: white;
        border-radius: 8px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; width: 100%; justify-content: center;
    }
    .toko-chat-escalate-btn:hover { background: #0284c7; }
    
    .toko-chat-product-card {
        background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; display: flex; gap: 10px; margin-bottom: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .toko-chat-product-thumb { width: 44px; height: 44px; object-fit: cover; border-radius: 4px; border: 1px solid #f1f5f9; }
    .toko-chat-product-title { font-size: 0.8rem; font-weight: 600; color: #1e293b; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.3; margin-bottom: 2px; }
    .toko-chat-product-price { font-size: 0.8rem; font-weight: 700; color: #ea580c; }
    
    .toko-chat-bubble.user {
        background: #0ea5e9; color: white; align-self: flex-end; border-bottom-right-radius: 4px; box-shadow: 0 2px 5px rgba(14,165,233,0.2);
    }
    .toko-chat-time { font-size: 0.65rem; margin-top: 6px; text-align: right; opacity: 0.7; }
    
    /* Typing Indicator */
    .toko-typing-indicator { display: flex; align-items: center; }
    .toko-typing-dot { width: 4px; height: 4px; background: #94a3b8; border-radius: 50%; margin: 0 2px; animation: tokoTyping 1.4s infinite ease-in-out both; }
    .toko-typing-dot:nth-child(2) { animation-delay: -0.32s; }
    .toko-typing-dot:nth-child(3) { animation-delay: -0.16s; }
    @keyframes tokoTyping { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); } }
    
    .toko-chat-footer {
        padding: 12px 16px; background: #fff; border-top: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px;
    }
    .toko-chat-input {
        flex: 1; padding: 10px 14px; background: #f1f5f9; border: 1px solid transparent; border-radius: 24px; font-size: 0.875rem;
        transition: all 0.2s; color: #334155; outline: none;
    }
    .toko-chat-input:focus { background: #fff; border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56,189,248,0.1); }
    .toko-chat-send-btn {
        width: 40px; height: 40px; border-radius: 50%; background: #0ea5e9; color: white; display: flex; align-items: center; justify-content: center;
        border: none; cursor: pointer; transition: all 0.2s; flex-shrink: 0; box-shadow: 0 2px 6px rgba(14,165,233,0.3);
    }
    .toko-chat-send-btn:hover { background: #0284c7; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(14,165,233,0.4); }

    /* Button inside the detail page to open chat */
    .unit-modal-btn-chat {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 12px 16px;
        background: #f1f5f9; color: #334155; border-radius: 8px; font-weight: 600; font-size: 0.95rem;
        border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s; flex-shrink: 0;
    }
    .unit-modal-btn-chat:hover { background: #e2e8f0; color: #0f172a; }
    .unit-modal-btn-chat svg { width: 18px; height: 18px; color: #0ea5e9; }
</style>

<script>
    // UNIT CHAT SYSTEM (BOT + ESKALASI ADMIN)
    const unitServiceCode = '{{ $service }}';
    const unitRegionId = '{{ $regionId }}';
    let unitChatSessionToken = localStorage.getItem(`unitChatToken_${unitServiceCode}_${unitRegionId}`);
    if (!unitChatSessionToken) {
        unitChatSessionToken = 'session_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem(`unitChatToken_${unitServiceCode}_${unitRegionId}`, unitChatSessionToken);
    }
    
    let isUnitChatEscalated = false;
    let isUnitChatOpen = false;
    let unitChatHasPushedItem = false;
    let unitChatPollInterval;

    const unitItemName = '{{ addslashes($itemName) }}';
    const unitItemPrice = '{{ addslashes($itemPrice) }}';
    const unitItemImage = '{{ addslashes($itemImage) }}';

    function escapeHtml(unsafe) {
        return (unsafe || '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }

    function chatAboutCurrentItem() {
        openUnitChat();
        
        if (!unitChatHasPushedItem && unitItemName) {
            setTimeout(() => {
                const initialMessage = `Halo Admin, saya ingin bertanya tentang ${unitItemName}.`;
                sendUnitMessageInternal(initialMessage, {
                    name: unitItemName,
                    price: unitItemPrice,
                    image: unitItemImage
                }, true); // silent parameter
                unitChatHasPushedItem = true;
                
                appendUnitMessageLocally(initialMessage, 'user', {
                    name: unitItemName,
                    price: unitItemPrice,
                    image: unitItemImage
                });
            }, 300);
        }
    }

    function openUnitChat() {
        document.getElementById('unitChatWidget').classList.add('active');
        isUnitChatOpen = true;
        loadUnitChatHistory();
        setTimeout(() => document.getElementById('unitChatInput').focus(), 100);
        
        if(unitChatPollInterval) clearInterval(unitChatPollInterval);
        unitChatPollInterval = setInterval(loadUnitChatHistory, 5000);
    }

    function closeUnitChat() {
        document.getElementById('unitChatWidget').classList.remove('active');
        isUnitChatOpen = false;
        if(unitChatPollInterval) clearInterval(unitChatPollInterval);
    }

    function appendUnitMessageLocally(text, sender, itemData = null) {
        const messages = document.getElementById('unitChatMessages');
        const bubble = document.createElement('div');
        const timeStr = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        
        let itemHtml = '';
        if (itemData && itemData.name) {
            const imgHtml = itemData.image ? `<img src="${escapeHtml(itemData.image)}" class="toko-chat-product-thumb" alt="${escapeHtml(itemData.name)}">` : '';
            itemHtml = `
                <div class="toko-chat-product-card">
                    ${imgHtml}
                    <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center;">
                        <div class="toko-chat-product-title">${escapeHtml(itemData.name)}</div>
                        <div class="toko-chat-product-price">${escapeHtml(itemData.price || '')}</div>
                    </div>
                </div>
            `;
        }

        if (sender === 'user') {
            bubble.className = 'toko-chat-bubble user';
            bubble.innerHTML = `
                ${itemHtml}
                <div>${escapeHtml(text)}</div>
                <div class="toko-chat-time">${timeStr}</div>
            `;
        } else if (sender === 'admin') {
            bubble.className = 'toko-chat-bubble admin';
            bubble.innerHTML = `
                <div class="toko-chat-admin-badge">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Admin Unit</span>
                </div>
                <div>${escapeHtml(text)}</div>
                <div class="toko-chat-time" style="color: #0369a1;">${timeStr}</div>
            `;
        } else {
            bubble.className = 'toko-chat-bubble toko';
            let escalateBtn = '';
            if (text && text.includes('Chat Petugas') && !isUnitChatEscalated) {
                escalateBtn = `
                    <button type="button" class="toko-chat-escalate-btn" onclick="escalateToAdminUnit()">
                        <span>Chat Petugas Admin</span>
                    </button>
                `;
            }
            bubble.innerHTML = `
                <div>${escapeHtml(text)}</div>
                ${escalateBtn}
                <div class="toko-chat-time">${timeStr}</div>
            `;
        }
        
        messages.appendChild(bubble);
        messages.scrollTo({ top: messages.scrollHeight, behavior: 'smooth' });
    }

    function loadUnitChatHistory() {
        if (!isUnitChatOpen) return;
        
        const url = `/api/unit-chat/${unitServiceCode}/history?region_id=${unitRegionId}`;
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Chat-Session-Token': unitChatSessionToken
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.data && data.data.messages) {
                const msgs = data.data.messages;
                if (msgs.length > 0) {
                    if (data.data.session && data.data.session.status === 'escalated') {
                        isUnitChatEscalated = true;
                        document.getElementById('unitQuickReplies').style.display = 'none';
                    }
                    
                    const messagesContainer = document.getElementById('unitChatMessages');
                    messagesContainer.innerHTML = ''; 
                    
                    msgs.forEach(msg => {
                        let senderType = 'toko';
                        if (msg.sender_type === 'user') senderType = 'user';
                        if (msg.sender_type === 'admin') senderType = 'admin';
                        
                        let itemData = null;
                        if (msg.item_data || msg.item_reference) {
                            try {
                                itemData = JSON.parse(msg.item_data || msg.item_reference);
                            } catch(e){}
                        }
                        
                        appendUnitMessageLocally(msg.message, senderType, itemData);
                    });
                }
            }
        }).catch(err => console.error("Error loading chat:", err));
    }

    function sendUnitMessageInternal(messageText, itemData = null, silent = false) {
        if (!messageText.trim()) return;
        
        const url = `/api/unit-chat/${unitServiceCode}/send`;
        const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Chat-Session-Token': unitChatSessionToken,
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                message: messageText,
                region_id: unitRegionId,
                item_reference: itemData ? JSON.stringify(itemData) : null,
                item_data: itemData ? JSON.stringify(itemData) : null
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (!silent) {
                    if (data.data.bot_message) {
                        setTimeout(() => {
                            appendUnitMessageLocally(data.data.bot_message.message, 'toko');
                        }, 600);
                    }
                }
            }
        }).catch(err => {
            console.error(err);
            if (!silent) alert('Gagal mengirim pesan');
        });
    }

    function sendUnitMessage() {
        const input = document.getElementById('unitChatInput');
        const text = input.value;
        if (!text.trim()) return;
        
        input.value = '';
        appendUnitMessageLocally(text, 'user');
        sendUnitMessageInternal(text, null, false);
    }

    function sendUnitQuickReply(text) {
        appendUnitMessageLocally(text, 'user');
        sendUnitMessageInternal(text, null, false);
    }

    function escalateToAdminUnit() {
        const btn = document.querySelector('.toko-chat-escalate-btn');
        if (btn) btn.innerHTML = 'Sedang meneruskan...';
        
        const url = `/api/unit-chat/${unitServiceCode}/escalate`;
        const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Chat-Session-Token': unitChatSessionToken,
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                region_id: unitRegionId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                isUnitChatEscalated = true;
                document.getElementById('unitQuickReplies').style.display = 'none';
                
                setTimeout(() => {
                    appendUnitMessageLocally("<i class='bx bx-check-circle text-green-500 me-1'></i> Chat telah diteruskan. Petugas Admin akan segera membalas pesan Anda di sini.", 'toko');
                }, 500);
            }
        }).catch(err => {
            console.error(err);
            alert('Gagal meneruskan chat.');
        });
    }
</script>
