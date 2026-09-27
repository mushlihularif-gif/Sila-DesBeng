{{-- Reusable Admin Unit Chat Panel (WhatsApp & Telegram Modern Style) --}}
@php
    $chatServiceTitle = $chatServiceTitle ?? 'Layanan';
    $serviceType = $serviceType ?? 'gas';
    $firstChatId = (!empty($chats) && count($chats) > 0) ? $chats[0]->id : null;
@endphp

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 wa-chat-container">
    <div class="card-header bg-white border-bottom py-2.5 py-sm-3 px-3 px-sm-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="avatar avatar-sm bg-label-primary text-primary rounded-circle d-flex align-items-center justify-content-center">
                <i class="bx bx-chat fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 fs-6 fs-sm-5 text-dark">Chat Warga &bull; {{ $chatServiceTitle }}</h5>
                <small class="text-muted d-none d-sm-block">Komunikasi langsung real-time seputar unit {{ strtolower($chatServiceTitle) }}</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <span class="badge bg-label-primary px-3 py-1.5 py-sm-2 rounded-pill font-semibold text-nowrap">
                Total Sesi: {{ count($chats ?? []) }}
            </span>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="row g-0">
            <!-- Left Panel: Chat List (WhatsApp Web Style) -->
            <div class="col-12 col-md-4 col-lg-4 border-end bg-white" style="min-height: 560px;">
                <div class="p-3 border-bottom" style="background: #f8fafc;">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text bg-white border-0 ps-3"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" id="searchUnitChatInput_{{ $serviceType }}" class="form-control bg-white border-0 ps-2 rounded-pill shadow-2xs" placeholder="Cari nama warga..." onkeyup="filterAdminUnitChats('{{ $serviceType }}')" style="font-size: 0.88rem;">
                    </div>
                </div>

                <div class="overflow-auto wa-contact-list" id="adminUnitChatListContainer_{{ $serviceType }}" style="max-height: 520px;">
                    @forelse($chats as $chat)
                        @php
                            $userPhoto = ($chat->user && $chat->user->profile_photo_url) ? $chat->user->profile_photo_url : null;
                            $firstChar = strtoupper(substr($chat->user_name ?? ($chat->user->name ?? 'W'), 0, 1));
                        @endphp
                        <div class="admin-unit-chat-item-{{ $serviceType }} p-3 border-bottom d-flex align-items-center gap-3 cursor-pointer transition-all wa-chat-item" 
                             id="unitChatItem_{{ $serviceType }}_{{ $chat->id }}"
                             onclick="loadAdminUnitChat('{{ $serviceType }}', {{ $chat->id }})"
                             data-user-name="{{ strtolower($chat->user_name ?? ($chat->user->name ?? 'Warga')) }}"
                             style="cursor: pointer; border-left: 4px solid transparent; transition: background-color 0.15s ease;">
                            
                            <!-- Avatar Warga -->
                            <div class="position-relative flex-shrink-0">
                                <div class="avatar avatar-md">
                                    @if($userPhoto)
                                        <img src="{{ $userPhoto }}" alt="{{ $chat->user_name ?? 'Warga' }}" class="rounded-circle w-100 h-100 shadow-2xs" style="object-fit: cover;" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'avatar-initial rounded-circle fw-bold text-white shadow-2xs\' style=\'background: linear-gradient(135deg, #696cff, #4338ca); font-size: 14px;\'>{{ $firstChar }}</div>';">
                                    @else
                                        <div class="avatar-initial rounded-circle fw-bold text-white shadow-2xs" style="background: linear-gradient(135deg, #696cff, #4338ca); font-size: 14px;">
                                            {{ $firstChar }}
                                        </div>
                                    @endif
                                </div>
                                <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                            </div>

                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 text-truncate fw-bold text-dark" style="font-size: 0.92rem;">
                                        {{ $chat->user_name ?? ($chat->user->name ?? 'Warga') }}
                                    </h6>
                                    <span class="text-muted" style="font-size: 11px;">
                                        {{ $chat->last_message_at ? $chat->last_message_at->format('H:i') : '' }}
                                    </span>
                                </div>
                                
                                @if($chat->item_reference)
                                    <div class="badge bg-label-info py-0 px-2 mb-1 text-truncate" style="font-size: 10px; max-width: 175px;">
                                        <i class="bx bx-bookmark me-1"></i>{{ $chat->item_reference }}
                                    </div>
                                @endif
                                
                                <p class="mb-0 text-muted small text-truncate" style="max-width: 180px; font-size: 0.8rem;" id="unitChatPreview_{{ $serviceType }}_{{ $chat->id }}">
                                    {{ $chat->last_message ?? 'Memulai percakapan...' }}
                                </p>
                                
                                <div class="d-flex align-items-center gap-1 mt-1.5">
                                    @if($chat->status === 'escalated')
                                        <span class="badge bg-label-warning py-0.5 px-2" style="font-size: 9.5px;" id="unitChatBadge_{{ $serviceType }}_{{ $chat->id }}">Perlu Balasan</span>
                                    @elseif($chat->status === 'resolved')
                                        <span class="badge bg-label-success py-0.5 px-2" style="font-size: 9.5px;" id="unitChatBadge_{{ $serviceType }}_{{ $chat->id }}">Selesai</span>
                                    @else
                                        <span class="badge py-0.5 px-2 d-none" style="font-size: 9.5px;" id="unitChatBadge_{{ $serviceType }}_{{ $chat->id }}"></span>
                                    @endif

                                    @if($chat->unread_admin_count > 0)
                                        <span class="badge bg-success rounded-pill ms-auto py-0.5 px-2" style="font-size: 10px;" id="unitChatUnread_{{ $serviceType }}_{{ $chat->id }}">
                                            {{ $chat->unread_admin_count }}
                                        </span>
                                    @else
                                        <span class="badge bg-success rounded-pill ms-auto py-0.5 px-2 d-none" style="font-size: 10px;" id="unitChatUnread_{{ $serviceType }}_{{ $chat->id }}"></span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bx bx-message-rounded-x fs-1 opacity-50 mb-2"></i>
                            <p class="small mb-0">Belum ada obrolan dari warga untuk layanan ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right Panel: Active Chat Stream & WhatsApp UI -->
            <div class="col-12 col-md-8 col-lg-8 d-flex flex-column bg-white position-relative" style="min-height: 560px;">
                
                <!-- 1. EMPTY STATE (Visible when no chat is active) -->
                <div id="adminUnitNoChatSelected_{{ $serviceType }}" class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center p-4 my-auto" style="background: #f8fafc; min-height: 560px;">
                    <div class="avatar avatar-xl bg-label-primary rounded-circle mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px;">
                        <i class="bx bx-conversation text-primary" style="font-size: 36px;"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Ruang Obrolan Warga</h5>
                    <p class="text-muted small mb-3" style="max-width: 320px;">
                        Pilih salah satu percakapan di sebelah kiri untuk membaca dan membalas pesan warga secara langsung.
                    </p>
                    <div class="badge bg-white border text-muted px-3 py-1.5 rounded-pill shadow-2xs font-normal" style="font-size: 11px;">
                        <i class="bx bx-shield-quarter text-success me-1"></i> Terhubung langsung ke sistem layanan BUMDes
                    </div>
                </div>

                <!-- 2. ACTIVE CHAT WRAPPER (Shown when a chat is active) -->
                <div id="adminUnitActiveChatWrapper_{{ $serviceType }}" class="d-none flex-column flex-grow-1 w-100">
                    
                    <!-- Chat Header (Clean WhatsApp Business / Sneat Style) -->
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white" style="min-height: 68px;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="position-relative flex-shrink-0">
                                <div class="avatar avatar-md" id="unitActiveChatAvatarWrap_{{ $serviceType }}">
                                    <div class="avatar-initial rounded-circle bg-label-primary fw-bold" id="unitActiveChatAvatar_{{ $serviceType }}">-</div>
                                </div>
                                <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" id="unitActiveChatOnlineDot_{{ $serviceType }}" style="width: 10px; height: 10px;"></span>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="mb-0 fw-bold text-dark fs-6" id="unitActiveChatUserName_{{ $serviceType }}">-</h6>
                                    <span class="badge py-0.5 px-2 fw-semibold d-none" style="font-size: 10px;" id="unitActiveChatStatusBadge_{{ $serviceType }}"></span>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 mt-0.5" id="unitActiveChatSubtitleWrap_{{ $serviceType }}">
                                    <span class="text-success small fw-medium" style="font-size: 11.5px;">
                                        <i class="bx bxs-circle me-1" style="font-size: 7px;"></i>Online
                                    </span>
                                    <span class="text-muted opacity-50">&bull;</span>
                                    <span class="text-muted small" style="font-size: 11.5px;">Warga Terhubung</span>
                                </div>
                            </div>
                        </div>
                        <div id="adminUnitChatActions_{{ $serviceType }}">
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 shadow-2xs d-inline-flex align-items-center gap-1.5 fw-semibold" onclick="resolveAdminUnitActiveChat('{{ $serviceType }}')">
                                <i class="bx bx-check-double fs-6"></i>
                                <span>Tandai Selesai</span>
                            </button>
                        </div>
                    </div>

                    <!-- Chat Stream Messages (WhatsApp / Telegram Wallpaper Background) -->
                    <div class="flex-grow-1 p-3 p-sm-4 overflow-auto d-flex flex-column gap-2" id="adminUnitChatMessagesStream_{{ $serviceType }}" 
                         style="height: 460px; min-height: 400px; background-color: #efeae2; background-image: radial-gradient(rgba(17, 27, 33, 0.06) 1px, transparent 0); background-size: 18px 18px;">
                    </div>

                    <!-- Chat Input Area (WhatsApp Web Style) -->
                    <div class="p-2.5 p-sm-3 border-top" id="adminUnitChatInputContainer_{{ $serviceType }}" style="background: #f0f2f5;">
                        <!-- Quick reply template chips -->
                        <div class="d-flex align-items-center gap-1.5 mb-2 overflow-auto pb-1" style="white-space: nowrap;">
                            <span class="text-muted small me-1 flex-shrink-0" style="font-size: 11px;"><i class="bx bx-zap text-warning me-1"></i>Balasan Cepat:</span>
                            <button type="button" class="btn btn-xs btn-white bg-white border rounded-pill shadow-2xs text-secondary px-2.5 py-1" onclick="insertAdminQuickReply('{{ $serviceType }}', 'Siap, unit ini tersedia dan siap digunakan.')" style="font-size: 11px;">Siap, unit tersedia</button>
                            <button type="button" class="btn btn-xs btn-white bg-white border rounded-pill shadow-2xs text-secondary px-2.5 py-1" onclick="insertAdminQuickReply('{{ $serviceType }}', 'Bisa langsung melakukan permohonan sewa melalui sistem.')" style="font-size: 11px;">Bisa langsung diajukan</button>
                            <button type="button" class="btn btn-xs btn-white bg-white border rounded-pill shadow-2xs text-secondary px-2.5 py-1" onclick="insertAdminQuickReply('{{ $serviceType }}', 'Mohon konfirmasi tanggal dan jadwal penggunaan unit.')" style="font-size: 11px;">Konfirmasi jadwal</button>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <div class="flex-grow-1 position-relative">
                                <input type="text" id="adminUnitReplyInput_{{ $serviceType }}" class="form-control rounded-pill px-3 py-2 bg-white border shadow-2xs" placeholder="Ketik balasan untuk warga (tekan Enter untuk kirim)..." onkeypress="if(event.key === 'Enter') sendAdminUnitReply('{{ $serviceType }}')" style="font-size: 0.92rem; border-color: #d1d7db;">
                            </div>
                            <button class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" type="button" onclick="sendAdminUnitReply('{{ $serviceType }}')" style="width: 42px; height: 42px;" title="Kirim Pesan">
                                <i class="bx bx-send fs-5"></i>
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<style>
.wa-chat-container {
    border: 1px solid rgba(67, 89, 113, 0.12);
}
.wa-chat-item:hover {
    background-color: #f8fafc !important;
}
.admin-unit-msg-bubble {
    opacity: 1 !important;
    visibility: visible !important;
    transform: none !important;
}
.unit-chat-user-bubble {
    padding: 14px 16px 10px;
}
.unit-chat-product-quote {
    width: 100%;
    box-sizing: border-box;
    padding: 12px;
    margin-bottom: 12px;
    background: rgba(105, 108, 255, 0.08);
    border-left: 4px solid #696cff;
}
.chat-bubble-pop {
    opacity: 1 !important;
    animation: chatBubblePopIn 0.15s ease-out forwards;
}
@keyframes chatBubblePopIn {
    from { opacity: 0; transform: translateY(3px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
    if (typeof window.unitChatStates === 'undefined') {
        window.unitChatStates = {};
    }
    window.unitChatStates['{{ $serviceType }}'] = {
        activeSessionId: null,
        pollInterval: null,
        productInfo: null,
        userPhoto: null
    };

    function filterAdminUnitChats(service) {
        const query = (document.getElementById(`searchUnitChatInput_${service}`)?.value || '').toLowerCase();
        const items = document.querySelectorAll(`.admin-unit-chat-item-${service}`);
        items.forEach(item => {
            const name = item.getAttribute('data-user-name') || '';
            if (name.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function insertAdminQuickReply(service, text) {
        const input = document.getElementById(`adminUnitReplyInput_${service}`);
        if (input) {
            input.value = text;
            input.focus();
        }
    }

    function loadAdminUnitChat(service, sessionId, isSilent = false) {
        const state = window.unitChatStates[service];
        if (!state) return;
        
        const isSwitchingSession = (state.activeSessionId !== sessionId);
        state.activeSessionId = sessionId;

        // Switch Right Panel Views: Hide Empty State, Show Active Chat Wrapper
        const emptyState = document.getElementById(`adminUnitNoChatSelected_${service}`);
        const activeWrapper = document.getElementById(`adminUnitActiveChatWrapper_${service}`);
        if (emptyState) {
            emptyState.classList.add('d-none');
            emptyState.classList.remove('d-flex');
        }
        if (activeWrapper) {
            activeWrapper.classList.remove('d-none');
            activeWrapper.classList.add('d-flex');
        }

        // Highlight selected chat item in left sidebar
        document.querySelectorAll(`.admin-unit-chat-item-${service}`).forEach(el => {
            el.style.backgroundColor = '';
            el.style.borderLeftColor = 'transparent';
        });

        const activeItem = document.getElementById(`unitChatItem_${service}_${sessionId}`);
        if (activeItem) {
            activeItem.style.backgroundColor = '#eef2ff';
            activeItem.style.borderLeftColor = '#696cff';

            // Clear unread badge
            const unreadBadge = document.getElementById(`unitChatUnread_${service}_${sessionId}`);
            if (unreadBadge) {
                unreadBadge.classList.add('d-none');
                unreadBadge.innerText = '';
            }
        }

        const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);

        // Show spinner ONLY when switching to a different session or stream is empty, NEVER during silent polling
        if (!isSilent && (isSwitchingSession || (stream && !stream.querySelector('.admin-unit-msg-bubble')))) {
            if (stream) {
                stream.innerHTML = '<div class="text-center py-5 my-auto text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Memuat obrolan...</div>';
            }
        }

        // Use clean relative endpoints to guarantee compatibility across HTTPS and any domain
        const appBase = '{{ request()->getBaseUrl() }}';
        const fetchUrl = `${appBase}/admin/unit/chat-service/${service}/${sessionId}/messages`;
        const fallbackUrl = `${appBase}/admin/chat-service/${service}/${sessionId}/messages`;

        fetch(fetchUrl, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) {
                return fetch(fallbackUrl, { headers: { 'Accept': 'application/json' } }).then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                });
            }
            return res.json();
        })
        .then(res => {
            if (res.status === 'success' && res.data) {
                // Ensure response matches current active session
                if (state.activeSessionId !== sessionId) return;

                const session = res.data.session || {};
                const rawMessages = res.data.messages || [];
                const messages = Array.isArray(rawMessages) ? rawMessages : Object.values(rawMessages);
                const userPhoto = res.data.user_photo || null;
                const productInfo = res.data.product_info || null;

                state.productInfo = productInfo;
                state.userPhoto = userPhoto;

                // Update Header Name
                const userName = session.user_name || (session.user ? session.user.name : 'Warga');
                const userNameEl = document.getElementById(`unitActiveChatUserName_${service}`);
                if (userNameEl) userNameEl.innerText = userName;

                // Update Header Avatar
                const avatarWrap = document.getElementById(`unitActiveChatAvatarWrap_${service}`);
                if (avatarWrap) {
                    if (userPhoto) {
                        avatarWrap.innerHTML = `<img src="${userPhoto}" alt="${escapeHtml(userName)}" class="rounded-circle w-100 h-100 shadow-2xs" style="object-fit: cover;" onerror="this.style.display='none'">`;
                    } else {
                        const initialChar = userName.charAt(0).toUpperCase() || 'W';
                        avatarWrap.innerHTML = `<div class="avatar-initial rounded-circle fw-bold text-white shadow-2xs" style="background: linear-gradient(135deg, #696cff, #4338ca);">${initialChar}</div>`;
                    }
                }

                // Update Status Badge in Header
                const statusBadge = document.getElementById(`unitActiveChatStatusBadge_${service}`);
                if (statusBadge) {
                    if (session.status === 'escalated') {
                        statusBadge.className = 'badge bg-label-warning py-0.5 px-2.5 fw-semibold';
                        statusBadge.innerText = 'Perlu Balasan';
                        statusBadge.classList.remove('d-none');
                    } else if (session.status === 'resolved') {
                        statusBadge.className = 'badge bg-label-success py-0.5 px-2.5 fw-semibold';
                        statusBadge.innerText = 'Selesai';
                        statusBadge.classList.remove('d-none');
                    } else {
                        statusBadge.classList.add('d-none');
                    }
                }

                // Render Messages safely
                if (stream) {
                    const hasSpinner = stream.querySelector('.spinner-border') !== null;
                    const hasExistingBubbles = stream.querySelector('.admin-unit-msg-bubble') !== null;

                    if (isSwitchingSession || !isSilent || hasSpinner || !hasExistingBubbles) {
                        // Full initial render for this session
                        stream.innerHTML = '';

                        if (messages.length === 0) {
                            stream.innerHTML = '<div class="text-center text-muted my-auto py-5"><i class="bx bx-chat fs-1 opacity-50 mb-2"></i><p class="small">Belum ada pesan dalam sesi ini.</p></div>';
                        } else {
                            let firstUserMsgRendered = false;
                            messages.forEach(msg => {
                                if (!msg) return;
                                const isFirstUser = !firstUserMsgRendered && msg.sender_type === 'user';
                                if (isFirstUser) firstUserMsgRendered = true;
                                renderAdminUnitMessageBubble(service, msg, productInfo, userPhoto, isFirstUser);
                            });
                            stream.scrollTop = stream.scrollHeight;
                        }
                    } else {
                        // Incremental update (polling): ONLY append new messages if not already in DOM
                        let hasNew = false;
                        messages.forEach(msg => {
                            if (!msg) return;
                            const existingEl = document.getElementById(`adminUnitMsg_${service}_${msg.id}`);
                            if (!existingEl) {
                                renderAdminUnitMessageBubble(service, msg, productInfo, userPhoto, false);
                                hasNew = true;
                            }
                        });
                        if (hasNew) {
                            stream.scrollTop = stream.scrollHeight;
                        }
                    }
                }

                // Start or maintain polling
                startAdminUnitChatPolling(service, sessionId);
            }
        })
        .catch(err => {
            console.error('Error loading chat:', err);
            if (!isSilent && stream && !stream.querySelector('.admin-unit-msg-bubble')) {
                stream.innerHTML = `<div class="text-center text-danger my-auto py-5"><i class="bx bx-error-circle fs-1 mb-2"></i><p class="small mb-2">Gagal memuat obrolan (${err.message || 'Koneksi bermasalah'}).</p><button class="btn btn-sm btn-outline-primary" onclick="loadAdminUnitChat('${service}', ${sessionId})">Muat Ulang</button></div>`;
            }
        });
    }

    function renderAdminUnitMessageBubble(service, msg, productInfo, userPhoto, isFirstUserMsg = false) {
        const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
        if (!stream || !msg) return;

        // Prevent duplicate rendering
        const msgUniqueId = msg.id ? `adminUnitMsg_${service}_${msg.id}` : `adminUnitMsg_${service}_temp_${Date.now()}`;
        if (msg.id && document.getElementById(msgUniqueId)) {
            return;
        }

        try {
            const bubble = document.createElement('div');
            bubble.id = msgUniqueId;
            
            // Safe time parsing
            let time = msg.time_formatted || '';
            if (!time && msg.created_at) {
                try {
                    const safeDateStr = typeof msg.created_at === 'string' && msg.created_at.includes(' ') && !msg.created_at.includes('T')
                        ? msg.created_at.replace(' ', 'T')
                        : msg.created_at;
                    const d = new Date(safeDateStr);
                    if (!isNaN(d.getTime())) {
                        time = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    }
                } catch (e) {}
            }
            if (!time) time = '';

            const msgText = escapeHtml(msg.message || '');

            if (msg.sender_type === 'admin') {
                bubble.className = 'admin-unit-msg-bubble chat-bubble-pop d-flex justify-content-end align-items-end gap-2 mb-2';
                bubble.innerHTML = `
                    <div style="max-width: 78%;">
                        <div class="p-2.5 px-3 rounded-3 shadow-xs text-dark" style="background: #d9fdd3; border-bottom-right-radius: 2px !important; border: 1px solid rgba(0,0,0,0.04);">
                            <p class="mb-0" style="font-size: 0.93rem; color: #111b21; white-space: pre-wrap; line-height: 1.45;">${msgText}</p>
                            <div class="d-flex align-items-center justify-content-end gap-1 mt-1" style="font-size: 10px; color: #667781;">
                                <span>${time}</span>
                                <i class="bx bx-check-double text-primary" style="font-size: 15px;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="avatar avatar-xs flex-shrink-0 mb-1">
                        <div class="avatar-initial rounded-circle bg-primary text-white fw-bold shadow-2xs" style="font-size: 10px;">AD</div>
                    </div>
                `;
            } else if (msg.sender_type === 'user') {
                bubble.className = 'admin-unit-msg-bubble chat-bubble-pop d-flex justify-content-start align-items-end gap-2 mb-2';
                
                let userAvatarHtml = '';
                if (userPhoto) {
                    userAvatarHtml = `<img src="${userPhoto}" class="rounded-circle w-100 h-100 shadow-2xs" style="object-fit: cover;" onerror="this.style.display='none'">`;
                } else {
                    userAvatarHtml = `<div class="avatar-initial rounded-circle fw-bold text-white shadow-2xs" style="background: linear-gradient(135deg, #696cff, #4338ca); font-size: 10px;">W</div>`;
                }

                // Spacious WhatsApp-style Product Quote inside the chat bubble
                let quotedHtml = '';
                if (isFirstUserMsg && productInfo && productInfo.title) {
                    const prodTitle = escapeHtml(productInfo.title || '');
                    const prodPrice = escapeHtml(productInfo.price || '');
                    const prodImg = productInfo.image ? escapeHtml(productInfo.image) : '';
                    const prodUrl = (productInfo.url && productInfo.url !== '#') ? productInfo.url : '';

                    quotedHtml = `
                        <div class="unit-chat-product-quote rounded-3 d-flex align-items-center justify-content-between gap-3 shadow-2xs">
                            <div class="d-flex align-items-center gap-3 overflow-hidden min-w-0">
                                ${prodImg ? `<img src="${prodImg}" alt="${prodTitle}" class="rounded flex-shrink-0 shadow-2xs" style="width: 48px; height: 48px; object-fit: cover;" onerror="this.style.display='none'">` : ''}
                                <div class="overflow-hidden min-w-0">
                                    <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 9.5px; letter-spacing: 0.5px;">Unit Layanan yang Ditanyakan</div>
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.9rem;">${prodTitle}</div>
                                    ${prodPrice ? `<div class="text-success fw-bold small mt-1" style="font-size: 0.84rem;">${prodPrice}</div>` : ''}
                                </div>
                            </div>
                            ${prodUrl ? `
                                <div class="flex-shrink-0">
                                    <a href="${prodUrl}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;">
                                        <i class="bx bx-show me-1"></i> Detail
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                    `;
                }

                bubble.innerHTML = `
                    <div class="avatar avatar-xs flex-shrink-0 mb-1">
                        ${userAvatarHtml}
                    </div>
                    <div style="max-width: 80%;">
                        <div class="unit-chat-user-bubble bg-white text-dark rounded-3 shadow-xs border" style="border-bottom-left-radius: 2px !important; border-color: rgba(0,0,0,0.06) !important;">
                            ${quotedHtml}
                            <p class="mb-0" style="font-size: 0.93rem; color: #111b21; white-space: pre-wrap; line-height: 1.45;">${msgText}</p>
                            <div class="text-end mt-1" style="font-size: 10px; color: #667781;">
                                <span>${time}</span>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                // Notice / Sambutan Awal Otomatis
                bubble.className = 'admin-unit-msg-bubble chat-bubble-pop d-flex justify-content-center my-2';
                bubble.innerHTML = `
                    <div class="bg-white border rounded-pill px-3 py-1.5 shadow-2xs d-flex align-items-center gap-1.5 text-center text-wrap" style="max-width: 82%; font-size: 11.5px; color: #54656f; line-height: 1.4; background: rgba(255,255,255,0.95) !important;">
                        <i class="bx bx-info-circle text-primary flex-shrink-0" style="font-size: 14px;"></i>
                        <span>${msgText}</span>
                    </div>
                `;
            }

            stream.appendChild(bubble);
        } catch (err) {
            console.error('Error rendering message bubble:', err);
            const fallback = document.createElement('div');
            fallback.id = msgUniqueId;
            fallback.className = 'admin-unit-msg-bubble chat-bubble-pop p-2 mb-2 bg-light rounded text-dark';
            fallback.innerText = msg.message || '';
            stream.appendChild(fallback);
        }
    }

    function sendAdminUnitReply(service) {
        const state = window.unitChatStates[service];
        if (!state || !state.activeSessionId) return;

        const input = document.getElementById(`adminUnitReplyInput_${service}`);
        const text = input.value.trim();
        if (!text) return;

        input.value = '';

        // Immediate visual push
        const nowTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const tempMsgId = 'temp_' + Date.now();
        renderAdminUnitMessageBubble(service, {
            id: tempMsgId,
            sender_type: 'admin',
            message: text,
            time_formatted: nowTime,
            created_at: new Date().toISOString()
        }, state.productInfo, state.userPhoto, false);

        const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
        if (stream) stream.scrollTop = stream.scrollHeight;

        const appBase = '{{ request()->getBaseUrl() }}';
        const replyUrl = `${appBase}/admin/unit/chat-service/${service}/${state.activeSessionId}/reply`;
        const replyFallbackUrl = `${appBase}/admin/chat-service/${service}/${state.activeSessionId}/reply`;

        fetch(replyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: text })
        })
        .then(res => {
            if (!res.ok) {
                return fetch(replyFallbackUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: text })
                }).then(r => r.json());
            }
            return res.json();
        })
        .then(res => {
            if (res.status === 'success') {
                if (res.data && res.data.chat_message && res.data.chat_message.id) {
                    const tempEl = document.getElementById(`adminUnitMsg_${service}_${tempMsgId}`);
                    if (tempEl) {
                        tempEl.id = `adminUnitMsg_${service}_${res.data.chat_message.id}`;
                    }
                }
                const preview = document.getElementById(`unitChatPreview_${service}_${state.activeSessionId}`);
                if (preview) preview.innerText = text;

                const badge = document.getElementById(`unitChatBadge_${service}_${state.activeSessionId}`);
                if (badge) {
                    badge.className = 'badge bg-label-warning py-0.5 px-2';
                    badge.innerText = 'Perlu Balasan';
                    badge.classList.remove('d-none');
                }
            }
        })
        .catch(err => {
            console.error('Error replying:', err);
        });
    }

    function resolveAdminUnitActiveChat(service) {
        const state = window.unitChatStates[service];
        if (!state || !state.activeSessionId) return;

        if (!confirm('Tandai sesi obrolan ini telah selesai ditangani?')) return;

        const appBase = '{{ request()->getBaseUrl() }}';
        const resolveUrl = `${appBase}/admin/unit/chat-service/${service}/${state.activeSessionId}/resolve`;
        const resolveFallbackUrl = `${appBase}/admin/chat-service/${service}/${state.activeSessionId}/resolve`;

        fetch(resolveUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) {
                return fetch(resolveFallbackUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(r => r.json());
            }
            return res.json();
        })
        .then(res => {
            if (res.status === 'success') {
                const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
                if (stream) stream.innerHTML = '';
                loadAdminUnitChat(service, state.activeSessionId, false);

                const badge = document.getElementById(`unitChatBadge_${service}_${state.activeSessionId}`);
                if (badge) {
                    badge.className = 'badge bg-label-success py-0.5 px-2';
                    badge.innerText = 'Selesai';
                    badge.classList.remove('d-none');
                }

                const headerBadge = document.getElementById(`unitActiveChatStatusBadge_${service}`);
                if (headerBadge) {
                    headerBadge.className = 'badge bg-label-success py-0.5 px-2.5 fw-semibold';
                    headerBadge.innerText = 'Selesai';
                    headerBadge.classList.remove('d-none');
                }
            }
        })
        .catch(err => {
            console.error('Error resolving chat:', err);
        });
    }

    function startAdminUnitChatPolling(service, sessionId) {
        const state = window.unitChatStates[service];
        if (!state) return;
        if (state.pollInterval) {
            clearInterval(state.pollInterval);
        }

        state.pollInterval = setInterval(() => {
            if (state.activeSessionId === sessionId) {
                loadAdminUnitChat(service, sessionId, true);
            }
        }, 4000);
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Auto load first conversation if available
    @if($firstChatId)
    function autoInitAdminUnitChat_{{ $serviceType }}() {
        loadAdminUnitChat('{{ $serviceType }}', {{ $firstChatId }});
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoInitAdminUnitChat_{{ $serviceType }});
    } else {
        setTimeout(autoInitAdminUnitChat_{{ $serviceType }}, 100);
    }
    @endif
</script>
