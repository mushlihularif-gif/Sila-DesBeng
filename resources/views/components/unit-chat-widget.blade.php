@props([
    'service',
    'title' => 'Layanan Daerah',
    'regionId',
    'regionName',
    'itemName' => '',
    'itemPrice' => '',
    'itemImage' => ''
])

@php
    $resolvedRegionId = \App\Models\Region::resolveServiceRegionId((int) $regionId) ?: (int) $regionId;
    $resolvedRegion = $resolvedRegionId ? \App\Models\Region::find($resolvedRegionId) : null;
    $displayRegionName = trim((string) ($regionName ?: ($resolvedRegion?->name ?? '')));
    $cleanRegionName = str_replace(['Desa ', 'Kelurahan '], '', $displayRegionName);
@endphp

<!-- IN-APP UNIT CHAT WIDGET DAN MODAL (PRIVASI TERJAGA) -->
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
                    <span>Petugas {{ $cleanRegionName ?: 'Wilayah' }}</span>
                </div>
            </div>
        </div>
        <div class="toko-chat-header-actions">
            <button type="button" id="unitChatExpandBtn" onclick="toggleUnitChatExpand()" class="toko-chat-expand-btn" title="Perbesar panel obrolan" aria-expanded="false">
                <svg id="unitChatExpandIcon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"></path></svg>
                <span id="unitChatExpandLabel">Perbesar</span>
            </button>
            <button type="button" onclick="closeUnitChat()" class="toko-chat-close-btn" title="Tutup Chat">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    <!-- Privacy Shield Notice -->
    <div class="toko-chat-privacy">
        <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
        <span>Pesan Anda langsung masuk ke Petugas {{ $displayRegionName ?: 'Layanan' }}</span>
    </div>

    <!-- Quick Reply Chips -->
    <div class="toko-chat-quick-replies" id="unitQuickReplies">
        <button type="button" id="unitEscalateChipBtn" class="toko-chip-btn toko-chip-primary" onclick="escalateToAdminUnit()">Chat Petugas Layanan</button>
        <button type="button" class="toko-chip-btn" onclick="sendUnitQuickReply('Halo, apakah layanan ini masih tersedia?')">Masih tersedia?</button>
        <button type="button" class="toko-chip-btn" onclick="sendUnitQuickReply('Bisa dibantu untuk proses pesanannya?')">Cara pesan?</button>
        <button type="button" class="toko-chip-btn" onclick="sendUnitQuickReply('Apakah ada biaya tambahan?')">Biaya lain?</button>
    </div>

    <!-- Chat Stream Body -->
    <div class="toko-chat-body" id="unitChatMessages">
        <div class="toko-chat-bubble toko">
            <div>Halo{{ Auth::check() ? ' Kak ' . Auth::user()->name : ' Kak' }}! Selamat datang di {{ $title }} {{ $cleanRegionName }}. Ada yang bisa kami bantu?</div>
            <div class="toko-chat-time">{{ date('H:i') }}</div>
        </div>
    </div>

    <!-- Inline Error Notice -->
    <div id="unitChatError" style="display: none; background: #fef2f2; color: #b91c1c; border-top: 1px solid #fecaca; padding: 6px 14px; font-size: 11.5px;"></div>

    <!-- Footer / Input -->
    <div class="toko-chat-footer">
        <input type="text" id="unitChatInput" class="toko-chat-input" placeholder="Tulis pesan..." autocomplete="off" onkeypress="if(event.key==='Enter') sendUnitMessage()">
        <button type="button" id="unitChatSendBtn" onclick="sendUnitMessage()" class="toko-chat-send-btn" title="Kirim Pesan">
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
        width: 340px;
        max-width: calc(100vw - 24px);
        height: 460px;
        max-height: calc(100vh - 96px);
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.2), 0 0 15px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        z-index: 100000;
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
        transform: translateY(20px) scale(0.95);
        transform-origin: bottom right;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1px solid #e2e8f0;
    }
    .toko-chat-widget.is-expanded {
        width: min(660px, calc(100vw - 2rem));
        max-width: calc(100vw - 2rem);
        height: min(82vh, 720px);
        max-height: calc(100vh - 48px);
    }
    @media (max-width: 768px) {
        .toko-chat-widget {
            bottom: 16px;
            right: 12px;
            width: calc(100vw - 24px);
            max-width: 360px;
            height: 440px;
        }
        .toko-chat-widget.is-expanded {
            bottom: 10px;
            right: 8px;
            left: 8px;
            width: calc(100vw - 16px);
            max-width: calc(100vw - 16px);
            height: calc(100vh - 20px);
            max-height: calc(100vh - 20px);
        }
    }
    .toko-chat-widget.active {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }
    .toko-chat-header {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        padding: 12px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        color: white;
    }
    .toko-chat-header-info { display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1; }
    .toko-chat-header-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
    .toko-chat-expand-btn {
        display: inline-flex; align-items: center; gap: 4px; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.28);
        color: #ffffff; border-radius: 999px; padding: 4px 9px; font-size: 11px; font-weight: 600; cursor: pointer; transition: background 0.2s;
        white-space: nowrap;
    }
    .toko-chat-expand-btn:hover { background: rgba(255,255,255,0.3); }
    .toko-chat-avatar-wrap { position: relative; flex-shrink: 0; }
    .toko-chat-avatar { width: 38px; height: 38px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.4); }
    .toko-chat-status-dot { position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; background: #10b981; border: 2px solid #0284c7; border-radius: 50%; }
    .toko-chat-header-title { font-weight: 700; font-size: 0.9rem; line-height: 1.2; }
    .toko-chat-header-subtitle { font-size: 0.72rem; color: #e0f2fe; display: flex; align-items: center; gap: 5px; margin-top: 2px; }
    .toko-chat-close-btn {
        background: rgba(255,255,255,0.2); border: none; color: white; width: 30px; height: 30px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;
    }
    .toko-chat-close-btn:hover { background: rgba(255,255,255,0.3); }
    .toko-chat-privacy {
        background: #ecfdf5; color: #065f46; font-size: 0.72rem; padding: 6px 12px; display: flex; align-items: center; gap: 6px;
        border-bottom: 1px solid #d1fae5; font-weight: 500;
    }
    .toko-chat-quick-replies {
        display: flex; gap: 6px; padding: 7px 12px; background: #f8fafc; overflow-x: auto; white-space: nowrap;
        border-bottom: 1px solid #e2e8f0; scrollbar-width: none;
    }
    .toko-chat-quick-replies::-webkit-scrollbar { display: none; }
    .toko-chip-btn {
        background: #fff; border: 1px solid #cbd5e1; color: #334155; font-size: 0.72rem; padding: 4px 10px;
        border-radius: 999px; cursor: pointer; transition: all 0.2s; font-weight: 500; flex-shrink: 0;
    }
    .toko-chip-btn:hover { background: #f1f5f9; border-color: #94a3b8; }
    .toko-chip-primary { background: #0284c7; border-color: #0284c7; color: #ffffff; font-weight: 600; }
    .toko-chip-primary:hover { background: #0369a1; border-color: #0369a1; color: #ffffff; }
    .toko-chip-connected { background: #dcfce7; border-color: #86efac; color: #166534; font-weight: 600; cursor: default; }
    .toko-chat-body {
        flex: 1; padding: 12px; overflow-y: auto; background: #f8fafc; display: flex; flex-direction: column; gap: 8px;
        scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;
    }
    .toko-chat-body::-webkit-scrollbar { width: 5px; }
    .toko-chat-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    .toko-chat-bubble {
        max-width: 82%; padding: 8px 12px; border-radius: 14px; font-size: 0.84rem; line-height: 1.4; position: relative;
        word-break: break-word;
    }
    .toko-chat-msg-text {
        white-space: pre-wrap;
        word-break: break-word;
    }
    .toko-chat-bubble.toko {
        background: #ffffff; color: #1e293b; align-self: flex-start; border-bottom-left-radius: 4px; border: 1px solid #e2e8f0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.03);
    }
    .toko-chat-bubble.admin {
        background: #e0f2fe; color: #0c4a6e; align-self: flex-start; border-bottom-left-radius: 4px; border: 1px solid #bae6fd;
        box-shadow: 0 2px 5px rgba(2,132,199,0.08);
    }
    .toko-chat-admin-badge {
        display: inline-flex; align-items: center; gap: 4px; font-size: 0.68rem; font-weight: 700; color: #0284c7; margin-bottom: 4px;
    }
    .toko-chat-escalate-btn {
        display: inline-flex; align-items: center; gap: 6px; margin-top: 8px; padding: 6px 12px;
        background: #0284c7; color: #ffffff; border: none; border-radius: 999px; font-size: 0.74rem; font-weight: 600; cursor: pointer;
    }
    .toko-chat-escalate-btn:hover { background: #0369a1; }
    .toko-chat-product-card {
        display: flex; gap: 10px; background: rgba(255,255,255,0.16); padding: 8px; border-radius: 10px; margin-bottom: 8px;
        border: 1px solid rgba(255,255,255,0.28);
    }
    .toko-chat-product-thumb { width: 44px; height: 44px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #fff; }
    .toko-chat-product-title { font-weight: 700; font-size: 0.78rem; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .toko-chat-product-price { font-size: 0.72rem; opacity: 0.92; margin-top: 2px; }
    .toko-chat-bubble.user {
        background: #0ea5e9; color: white; align-self: flex-end; border-bottom-right-radius: 4px; box-shadow: 0 2px 5px rgba(14,165,233,0.2);
    }
    .toko-chat-time { font-size: 0.65rem; margin-top: 6px; text-align: right; opacity: 0.7; }
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
    .toko-chat-send-btn:disabled { opacity: 0.6; cursor: not-allowed; }
    .unit-modal-btn-chat {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 12px 16px;
        background: #f1f5f9; color: #334155; border-radius: 8px; font-weight: 600; font-size: 0.95rem;
        border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s; flex-shrink: 0;
    }
    .unit-modal-btn-chat:hover { background: #e2e8f0; color: #0f172a; }
    .unit-modal-btn-chat svg { width: 18px; height: 18px; color: #0ea5e9; }
</style>

<script>
(() => {
    var unitServiceCode = '{{ $service }}';
    var unitRegionId = {{ (int) $resolvedRegionId }};
    var tokenStorageKey = `unit_chat_token_${unitServiceCode}_${unitRegionId}`;
    var unitChatSessionToken = localStorage.getItem(tokenStorageKey) || localStorage.getItem(`unitChatToken_${unitServiceCode}_${unitRegionId}`) || '';

    function rememberChatToken(data) {
        const token = data?.session_token || data?.session?.session_token;
        if (token) {
            unitChatSessionToken = token;
            localStorage.setItem(tokenStorageKey, token);
        }
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    }

    function showUnitError(msg) {
        const el = document.getElementById('unitChatError');
        if (!el) return;
        if (!msg) {
            el.style.display = 'none';
            el.textContent = '';
            return;
        }
        el.textContent = msg;
        el.style.display = 'block';
    }

    var isUnitChatEscalated = false;
    var isUnitChatOpen = false;
    var unitChatHasPushedItem = false;
    var unitChatIsSending = false;
    var unitChatLastFingerprint = '';
    if (window.unitChatPollInterval) {
        clearInterval(window.unitChatPollInterval);
    }

    var unitItemName = '{{ addslashes($itemName) }}';
    var unitItemPrice = '{{ addslashes($itemPrice) }}';
    var unitItemImage = '{{ addslashes($itemImage) }}';

    function escapeHtml(unsafe) {
        return String(unsafe ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function formatUnitTime(rawDate) {
        try {
            const safe = typeof rawDate === 'string' && rawDate.includes(' ') && !rawDate.includes('T')
                ? rawDate.replace(' ', 'T')
                : rawDate;
            const d = safe ? new Date(safe) : new Date();
            if (!isNaN(d.getTime())) {
                return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            }
        } catch (e) {}
        return '';
    }

    function updateEscalateUi(escalated) {
        isUnitChatEscalated = !!escalated;
        const chip = document.getElementById('unitEscalateChipBtn');
        if (!chip) return;
        if (isUnitChatEscalated) {
            chip.className = 'toko-chip-btn toko-chip-connected';
            chip.textContent = 'Terhubung ke Petugas';
            chip.disabled = true;
        } else {
            chip.className = 'toko-chip-btn toko-chip-primary';
            chip.textContent = 'Chat Petugas Layanan';
            chip.disabled = false;
        }
    }

    window.chatAboutCurrentItem = function() {
        const historyPromise = window.openUnitChat();

        if (!unitChatHasPushedItem && unitItemName) {
            unitChatHasPushedItem = true;
            Promise.resolve(historyPromise).finally(() => {
                const initialMessage = `Halo Petugas, saya ingin bertanya tentang ${unitItemName}.`;
                const itemPayload = {
                    name: unitItemName,
                    price: unitItemPrice,
                    image: unitItemImage
                };
                appendUnitMessageLocally(initialMessage, 'user', itemPayload, new Date().toISOString());
                sendUnitMessageInternal(initialMessage, itemPayload, true);
            });
        }
    };

    window.openUnitChat = function() {
        const widget = document.getElementById('unitChatWidget');
        if (widget) widget.classList.add('active');
        isUnitChatOpen = true;
        const p = loadUnitChatHistory(false);
        const input = document.getElementById('unitChatInput');
        if (input) setTimeout(() => input.focus(), 100);

        if (window.unitChatPollInterval) clearInterval(window.unitChatPollInterval);
        window.unitChatPollInterval = setInterval(() => {
            if (!document.hidden) loadUnitChatHistory(true);
        }, 4500);
        return p;
    };

    window.toggleUnitChatExpand = function() {
        const widget = document.getElementById('unitChatWidget');
        if (!widget) return;
        const expanded = widget.classList.toggle('is-expanded');
        const btn = document.getElementById('unitChatExpandBtn');
        const label = document.getElementById('unitChatExpandLabel');
        const icon = document.getElementById('unitChatExpandIcon');
        if (btn) {
            btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            btn.setAttribute('title', expanded ? 'Kecilkan panel obrolan' : 'Perbesar panel obrolan');
        }
        if (label) label.textContent = expanded ? 'Kecilkan' : 'Perbesar';
        if (icon) {
            icon.innerHTML = expanded
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 14h6v6M20 10h-6V4M14 10l7-7M10 14l-7 7"></path>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"></path>';
        }
    };

    window.closeUnitChat = function() {
        const widget = document.getElementById('unitChatWidget');
        if (widget) widget.classList.remove('active');
        isUnitChatOpen = false;
        if (window.unitChatPollInterval) clearInterval(window.unitChatPollInterval);
    };

    function appendUnitMessageLocally(text, sender, itemData = null, createdAt = null, adminName = null) {
        const messages = document.getElementById('unitChatMessages');
        if (!messages) return;
        const bubble = document.createElement('div');
        const timeStr = formatUnitTime(createdAt || new Date().toISOString());

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
            bubble.innerHTML = `${itemHtml}<div class="toko-chat-msg-text">${escapeHtml(text)}</div><div class="toko-chat-time">${timeStr}</div>`;
        } else if (sender === 'admin') {
            bubble.className = 'toko-chat-bubble admin';
            const badgeText = adminName ? `Petugas • ${adminName}` : 'Petugas Layanan';
            bubble.innerHTML = `<div class="toko-chat-admin-badge"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span>${escapeHtml(badgeText)}</span></div><div class="toko-chat-msg-text">${escapeHtml(text)}</div><div class="toko-chat-time" style="color: #0369a1;">${timeStr}</div>`;
        } else {
            bubble.className = 'toko-chat-bubble toko';
            let escalateBtn = '';
            if (text && text.includes('Chat Petugas') && !isUnitChatEscalated) {
                escalateBtn = `<button type="button" class="toko-chat-escalate-btn" onclick="escalateToAdminUnit()"><span>Chat Petugas Layanan</span></button>`;
            }
            bubble.innerHTML = `<div class="toko-chat-msg-text">${escapeHtml(text)}</div>${escalateBtn}<div class="toko-chat-time">${timeStr}</div>`;
        }

        messages.appendChild(bubble);
        messages.scrollTop = messages.scrollHeight;
    }

    function loadUnitChatHistory(isSilent = false) {
        if (!isUnitChatOpen) return Promise.resolve();

        const url = `{{ url('unit-chat') }}/${unitServiceCode}/history?region_id=${encodeURIComponent(unitRegionId)}`;

        return fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Chat-Session-Token': unitChatSessionToken
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            if (data.status === 'success' && data.data && data.data.messages) {
                showUnitError('');
                rememberChatToken(data.data);
                const msgs = data.data.messages;
                if (data.data.is_escalated || (data.data.session && data.data.session.status === 'escalated')) {
                    updateEscalateUi(true);
                }

                const fingerprint = msgs.map(m => `${m.id}:${m.sender_type}`).join('|');
                if (isSilent && fingerprint === unitChatLastFingerprint) {
                    return;
                }
                unitChatLastFingerprint = fingerprint;

                if (msgs.length > 0) {
                    const messagesContainer = document.getElementById('unitChatMessages');
                    if (messagesContainer) {
                        const isNearBottom = (messagesContainer.scrollHeight - messagesContainer.scrollTop - messagesContainer.clientHeight) < 80;
                        messagesContainer.innerHTML = '';

                        msgs.forEach(msg => {
                            let senderType = 'toko';
                            if (msg.sender_type === 'user') senderType = 'user';
                            if (msg.sender_type === 'admin') senderType = 'admin';

                            let itemData = null;
                            if (msg.item_data) {
                                if (typeof msg.item_data === 'object') {
                                    itemData = msg.item_data;
                                } else if (typeof msg.item_data === 'string') {
                                    try { itemData = JSON.parse(msg.item_data); } catch (e) {}
                                }
                            }

                            appendUnitMessageLocally(
                                msg.message,
                                senderType,
                                itemData,
                                msg.created_at,
                                msg.sender ? msg.sender.name : null
                            );
                        });

                        if (!isSilent || isNearBottom) {
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        }
                    }
                }
            }
        }).catch(err => {
            console.error('Error loading chat:', err);
            if (!isSilent) showUnitError('Gagal memuat riwayat obrolan. Silakan coba lagi.');
        });
    }

    function sendUnitMessageInternal(messageText, itemData = null, silent = false) {
        if (!messageText.trim() || unitChatIsSending) return;

        unitChatIsSending = true;
        const sendBtn = document.getElementById('unitChatSendBtn');
        if (sendBtn) sendBtn.disabled = true;
        showUnitError('');

        const url = `{{ url('unit-chat') }}/${unitServiceCode}/send`;

        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Chat-Session-Token': unitChatSessionToken,
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                message: messageText,
                region_id: unitRegionId,
                session_token: unitChatSessionToken,
                item_reference: (itemData && itemData.name) ? itemData.name : (unitItemName || null),
                item_data: itemData ? JSON.stringify(itemData) : null
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            if (data.status === 'success' && data.data) {
                rememberChatToken(data.data);
                if (data.data.is_escalated) {
                    updateEscalateUi(true);
                }
                if (!silent && data.data.bot_message) {
                    appendUnitMessageLocally(data.data.bot_message.message, 'toko', null, data.data.bot_message.created_at);
                }
                unitChatLastFingerprint = '';
            } else {
                throw new Error(data.message || 'Gagal mengirim pesan');
            }
        }).catch(err => {
            console.error(err);
            showUnitError('Gagal mengirim pesan. Periksa koneksi lalu coba kirim ulang.');
        }).finally(() => {
            unitChatIsSending = false;
            if (sendBtn) sendBtn.disabled = false;
        });
    }

    window.sendUnitMessage = function() {
        const input = document.getElementById('unitChatInput');
        if (!input || unitChatIsSending) return;
        const text = input.value.trim();
        if (!text) return;

        input.value = '';
        appendUnitMessageLocally(text, 'user', null, new Date().toISOString());
        sendUnitMessageInternal(text, null, false);
    };

    window.sendUnitQuickReply = function(text) {
        if (unitChatIsSending) return;
        appendUnitMessageLocally(text, 'user', null, new Date().toISOString());
        sendUnitMessageInternal(text, null, false);
    };

    window.escalateToAdminUnit = function() {
        const chip = document.getElementById('unitEscalateChipBtn');
        if (chip) {
            chip.disabled = true;
            chip.textContent = 'Menghubungkan...';
        }
        showUnitError('');

        const url = `{{ url('unit-chat') }}/${unitServiceCode}/escalate`;

        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Chat-Session-Token': unitChatSessionToken,
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                region_id: unitRegionId,
                session_token: unitChatSessionToken
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            if (data.status === 'success' && data.data) {
                rememberChatToken(data.data);
                updateEscalateUi(true);
                const msg = data.data.bot_message?.message || 'Chat telah diteruskan. Petugas Layanan akan segera membalas pesan Anda di sini.';
                appendUnitMessageLocally(msg, 'toko', null, data.data.bot_message?.created_at);
                unitChatLastFingerprint = '';
            } else {
                throw new Error(data.message || 'Gagal meneruskan chat');
            }
        }).catch(err => {
            console.error(err);
            updateEscalateUi(false);
            showUnitError('Gagal menghubungkan ke petugas. Silakan coba lagi.');
        });
    };

    document.addEventListener('turbo:before-cache', () => {
        if (window.unitChatPollInterval) {
            clearInterval(window.unitChatPollInterval);
        }
    });
})();
</script>
