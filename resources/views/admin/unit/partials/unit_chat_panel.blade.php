{{-- Reusable Admin Unit Chat Panel (WhatsApp & Telegram Modern Style) --}}
@php
    $chatServiceTitle = $chatServiceTitle ?? 'Layanan';
    $serviceType = $serviceType ?? 'gas';
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
                                        <img src="{{ $userPhoto }}" alt="{{ $chat->user_name ?? 'Warga' }}" class="rounded-circle w-100 h-100 shadow-2xs" style="object-fit: cover;">
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
                
                <!-- Chat Header (Clean WhatsApp Business / Sneat Style) -->
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white" id="adminUnitActiveChatHeader_{{ $serviceType }}" style="min-height: 68px;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="position-relative flex-shrink-0">
                            <div class="avatar avatar-md" id="unitActiveChatAvatarWrap_{{ $serviceType }}">
                                <div class="avatar-initial rounded-circle bg-label-primary fw-bold" id="unitActiveChatAvatar_{{ $serviceType }}">-</div>
                            </div>
                            <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" id="unitActiveChatOnlineDot_{{ $serviceType }}" style="width: 10px; height: 10px; display: none;"></span>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="mb-0 fw-bold text-dark" id="unitActiveChatUserName_{{ $serviceType }}" style="font-size: 0.98rem;">Pilih salah satu percakapan di sebelah kiri</h6>
                                <span class="badge py-0.5 px-2 fw-semibold" style="font-size: 10px; display: none;" id="unitActiveChatStatusBadge_{{ $serviceType }}"></span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5 mt-0.5" id="unitActiveChatSubtitleWrap_{{ $serviceType }}" style="display: none !important;">
                                <span class="text-success small fw-medium" style="font-size: 11.5px;">
                                    <i class="bx bxs-circle me-1" style="font-size: 7px;"></i>Online
                                </span>
                                <span class="text-muted opacity-50">&bull;</span>
                                <span class="text-muted small" style="font-size: 11.5px;">Warga Desa</span>
                            </div>
                        </div>
                    </div>
                    <div id="adminUnitChatActions_{{ $serviceType }}" style="display: none;">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 shadow-2xs d-inline-flex align-items-center gap-1.5 fw-semibold" onclick="resolveAdminUnitActiveChat('{{ $serviceType }}')">
                            <i class="bx bx-check-double fs-6"></i>
                            <span>Tandai Selesai</span>
                        </button>
                    </div>
                </div>

                <!-- Compact Product Bar (Sleek WhatsApp Business Style) -->
                <div id="unitActiveProductBanner_{{ $serviceType }}" class="px-3 py-2 bg-light border-bottom d-flex align-items-center justify-content-between gap-3 shadow-2xs" style="display: none; background: #f8fafc; border-bottom: 1px solid rgba(67, 89, 113, 0.08);">
                    <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                        <div class="position-relative flex-shrink-0" style="width: 40px; height: 40px; border-radius: 8px; overflow: hidden; background: #ffffff; border: 1px solid rgba(0,0,0,0.08);">
                            <img id="unitActiveProductImage_{{ $serviceType }}" src="" alt="Produk" class="w-100 h-100" style="object-fit: cover;">
                        </div>
                        <div class="overflow-hidden">
                            <div class="d-flex align-items-center gap-1.5">
                                <span class="badge bg-label-primary px-1.5 py-0 fw-bold" style="font-size: 9px;">PRODUK DITANYAKAN</span>
                                <span id="unitActiveProductTitle_{{ $serviceType }}" class="mb-0 text-truncate fw-bold text-dark" style="font-size: 0.88rem;"></span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-0.5">
                                <span id="unitActiveProductPrice_{{ $serviceType }}" class="text-success fw-bold" style="font-size: 0.82rem;"></span>
                                <span class="text-muted" style="font-size: 10px;">&bull;</span>
                                <span id="unitActiveProductCategory_{{ $serviceType }}" class="text-muted small" style="font-size: 11px;"></span>
                            </div>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <a id="unitActiveProductLink_{{ $serviceType }}" href="#" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1" style="font-size: 11px;">
                            <i class="bx bx-show me-1"></i> Detail Unit
                        </a>
                    </div>
                </div>

                <!-- Chat Stream Messages (WhatsApp / Telegram Wallpaper Background) -->
                <div class="flex-grow-1 p-3 p-sm-4 overflow-auto d-flex flex-column gap-2" id="adminUnitChatMessagesStream_{{ $serviceType }}" 
                     style="height: 400px; min-height: 360px; background-color: #efeae2; background-image: radial-gradient(rgba(17, 27, 33, 0.06) 1px, transparent 0); background-size: 18px 18px;">
                    
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted py-5 my-auto" id="adminUnitEmptyChatPlaceholder_{{ $serviceType }}">
                        <div class="bg-white p-3 rounded-circle shadow-sm mb-3">
                            <i class="bx bx-conversation fs-1 text-primary opacity-75"></i>
                        </div>
                        <h6 class="fw-bold mb-1 text-dark">Ruang Obrolan Warga</h6>
                        <p class="small text-muted mb-0">Klik nama warga di daftar sebelah kiri untuk membaca dan membalas pesan.</p>
                    </div>
                </div>

                <!-- Chat Input Area (WhatsApp Web Style) -->
                <div class="p-2.5 p-sm-3 border-top" id="adminUnitChatInputContainer_{{ $serviceType }}" style="display: none; background: #f0f2f5;">
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

<style>
.wa-chat-container {
    border: 1px solid rgba(67, 89, 113, 0.12);
}
.wa-chat-item:hover {
    background-color: #f8fafc !important;
}
.animate-fade-in {
    animation: fadeInBubble 0.2s ease-in-out;
}
@keyframes fadeInBubble {
    from { opacity: 0; transform: translateY(4px); }
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
        userPhoto: null,
        lastMessageCount: 0
    };

    function filterAdminUnitChats(service) {
        const query = document.getElementById(`searchUnitChatInput_${service}`).value.toLowerCase();
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
        
        // Reset message count if switching sessions
        if (state.activeSessionId !== sessionId) {
            state.lastMessageCount = 0;
        }
        state.activeSessionId = sessionId;

        // Highlight selected chat item
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

        if (!isSilent) {
            const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
            if (stream) {
                stream.innerHTML = '<div class="text-center py-5 my-auto text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Memuat obrolan...</div>';
            }
        }

        const fetchUrl = `{{ url('admin/unit/chat-service') }}/${service}/${sessionId}/messages`;
        const fallbackUrl = `{{ url('admin/chat-service') }}/${service}/${sessionId}/messages`;

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
                const session = res.data.session || {};
                const messages = Array.isArray(res.data.messages) ? res.data.messages : [];
                const userPhoto = res.data.user_photo || null;
                const productInfo = res.data.product_info || null;

                state.productInfo = productInfo;
                state.userPhoto = userPhoto;

                // Update Header
                const userName = session.user_name || (session.user ? session.user.name : 'Warga');
                const userNameEl = document.getElementById(`unitActiveChatUserName_${service}`);
                if (userNameEl) userNameEl.innerText = userName;

                const avatarWrap = document.getElementById(`unitActiveChatAvatarWrap_${service}`);
                if (avatarWrap) {
                    if (userPhoto) {
                        avatarWrap.innerHTML = `<img src="${userPhoto}" alt="${escapeHtml(userName)}" class="rounded-circle w-100 h-100 shadow-2xs" style="object-fit: cover;">`;
                    } else {
                        const initialChar = userName.charAt(0).toUpperCase() || 'W';
                        avatarWrap.innerHTML = `<div class="avatar-initial rounded-circle fw-bold text-white shadow-2xs" style="background: linear-gradient(135deg, #696cff, #4338ca);">${initialChar}</div>`;
                    }
                }

                const onlineDot = document.getElementById(`unitActiveChatOnlineDot_${service}`);
                if (onlineDot) onlineDot.style.display = 'block';

                const subtitleWrap = document.getElementById(`unitActiveChatSubtitleWrap_${service}`);
                if (subtitleWrap) subtitleWrap.style.setProperty('display', 'flex', 'important');

                // Status Badge in Header: only show if escalated or resolved, NEVER show BOT
                const statusBadge = document.getElementById(`unitActiveChatStatusBadge_${service}`);
                if (statusBadge) {
                    if (session.status === 'escalated') {
                        statusBadge.className = 'badge bg-label-warning py-0.5 px-2.5 fw-semibold';
                        statusBadge.innerText = 'Perlu Balasan';
                        statusBadge.style.display = 'inline-block';
                    } else if (session.status === 'resolved') {
                        statusBadge.className = 'badge bg-label-success py-0.5 px-2.5 fw-semibold';
                        statusBadge.innerText = 'Selesai';
                        statusBadge.style.display = 'inline-block';
                    } else {
                        statusBadge.style.display = 'none';
                    }
                }

                // Update Compact Product Bar
                const productBanner = document.getElementById(`unitActiveProductBanner_${service}`);
                if (productBanner) {
                    if (productInfo && productInfo.title) {
                        productBanner.style.setProperty('display', 'flex', 'important');
                        
                        const titleEl = document.getElementById(`unitActiveProductTitle_${service}`);
                        if (titleEl) titleEl.innerText = productInfo.title;

                        const catEl = document.getElementById(`unitActiveProductCategory_${service}`);
                        if (catEl) catEl.innerText = productInfo.category || 'Unit Layanan';

                        const priceEl = document.getElementById(`unitActiveProductPrice_${service}`);
                        if (priceEl) priceEl.innerText = productInfo.price || '';
                        
                        const prodImg = document.getElementById(`unitActiveProductImage_${service}`);
                        if (prodImg && productInfo.image) {
                            prodImg.src = productInfo.image;
                        }

                        const prodLink = document.getElementById(`unitActiveProductLink_${service}`);
                        if (prodLink) {
                            prodLink.href = productInfo.url || '#';
                            if (!productInfo.url || productInfo.url === '#') {
                                prodLink.style.display = 'none';
                            } else {
                                prodLink.style.display = 'inline-block';
                            }
                        }
                    } else {
                        productBanner.style.display = 'none';
                    }
                }

                // Show Actions & Input
                const actionsEl = document.getElementById(`adminUnitChatActions_${service}`);
                if (actionsEl) actionsEl.style.display = 'block';

                const inputContainer = document.getElementById(`adminUnitChatInputContainer_${service}`);
                if (inputContainer) inputContainer.style.display = 'block';

                // Render Messages
                const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
                if (stream) {
                    // In silent polling, skip re-rendering if message count has not changed
                    if (isSilent && state.lastMessageCount === messages.length && messages.length > 0) {
                        // Keep current stream content intact
                    } else {
                        stream.innerHTML = '';

                        if (messages.length === 0) {
                            stream.innerHTML = '<div class="text-center text-muted my-auto py-5"><i class="bx bx-chat fs-1 opacity-50 mb-2"></i><p class="small">Belum ada pesan dalam sesi ini.</p></div>';
                        } else {
                            let firstUserMsgRendered = false;
                            messages.forEach((msg, idx) => {
                                try {
                                    const isFirstUser = !firstUserMsgRendered && msg && msg.sender_type === 'user';
                                    if (isFirstUser) firstUserMsgRendered = true;
                                    renderAdminUnitMessageBubble(service, msg, productInfo, userPhoto, isFirstUser);
                                } catch (bubbleErr) {
                                    console.error('Error rendering message bubble at index ' + idx, bubbleErr);
                                }
                            });
                        }

                        state.lastMessageCount = messages.length;
                        stream.scrollTop = stream.scrollHeight;
                    }
                }

                // Start Polling
                startAdminUnitChatPolling(service, sessionId);
            } else {
                if (!isSilent) {
                    const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
                    if (stream) {
                        stream.innerHTML = `<div class="text-center text-danger my-auto py-5"><i class="bx bx-error-circle fs-1 mb-2"></i><p class="small mb-2">${res.message || 'Gagal memuat percakapan.'}</p><button class="btn btn-sm btn-outline-primary" onclick="loadAdminUnitChat('${service}', ${sessionId})">Coba Lagi</button></div>`;
                    }
                }
            }
        })
        .catch(err => {
            console.error('Error loading chat:', err);
            if (!isSilent) {
                const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
                if (stream) {
                    stream.innerHTML = `<div class="text-center text-danger my-auto py-5"><i class="bx bx-error-circle fs-1 mb-2"></i><p class="small mb-2">Gagal memuat obrolan (${err.message || 'Koneksi bermasalah'}).</p><button class="btn btn-sm btn-outline-primary" onclick="loadAdminUnitChat('${service}', ${sessionId})">Muat Ulang</button></div>`;
                }
            }
        });
    }

    function renderAdminUnitMessageBubble(service, msg, productInfo, userPhoto, isFirstUserMsg = false) {
        const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
        if (!stream || !msg) return;

        const bubble = document.createElement('div');
        
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
            bubble.className = 'd-flex justify-content-end align-items-end gap-2 mb-2 animate-fade-in';
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
            bubble.className = 'd-flex justify-content-start align-items-end gap-2 mb-2 animate-fade-in';
            
            let userAvatarHtml = '';
            if (userPhoto) {
                userAvatarHtml = `<img src="${userPhoto}" class="rounded-circle w-100 h-100 shadow-2xs" style="object-fit: cover;">`;
            } else {
                userAvatarHtml = `<div class="avatar-initial rounded-circle fw-bold text-white shadow-2xs" style="background: linear-gradient(135deg, #696cff, #4338ca); font-size: 10px;">W</div>`;
            }

            let quotedHtml = '';
            if (isFirstUserMsg && productInfo && productInfo.title) {
                quotedHtml = `
                    <div class="p-2 mb-2 rounded-2 border-start border-3 border-primary d-flex align-items-center gap-2" style="background: rgba(105, 108, 255, 0.08);">
                        ${productInfo.image ? `<img src="${productInfo.image}" class="rounded flex-shrink-0" style="width: 38px; height: 38px; object-fit: cover;">` : ''}
                        <div class="overflow-hidden min-w-0">
                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.82rem;">${escapeHtml(productInfo.title)}</div>
                            <div class="text-success fw-bold small" style="font-size: 0.75rem;">${escapeHtml(productInfo.price || '')}</div>
                        </div>
                    </div>
                `;
            }

            bubble.innerHTML = `
                <div class="avatar avatar-xs flex-shrink-0 mb-1">
                    ${userAvatarHtml}
                </div>
                <div style="max-width: 78%;">
                    <div class="bg-white text-dark p-2.5 px-3 rounded-3 shadow-xs border" style="border-bottom-left-radius: 2px !important; border-color: rgba(0,0,0,0.06) !important;">
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
            bubble.className = 'd-flex justify-content-center my-2 animate-fade-in';
            bubble.innerHTML = `
                <div class="bg-white border rounded-pill px-3 py-1.5 shadow-2xs d-flex align-items-center gap-1.5 text-center text-wrap" style="max-width: 82%; font-size: 11.5px; color: #54656f; line-height: 1.4; background: rgba(255,255,255,0.95) !important;">
                    <i class="bx bx-info-circle text-primary flex-shrink-0" style="font-size: 14px;"></i>
                    <span>${msgText}</span>
                </div>
            `;
        }

        stream.appendChild(bubble);
    }

    function sendAdminUnitReply(service) {
        const state = window.unitChatStates[service];
        if (!state.activeSessionId) return;

        const input = document.getElementById(`adminUnitReplyInput_${service}`);
        const text = input.value.trim();
        if (!text) return;

        input.value = '';

        // Immediate visual push
        const nowTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        renderAdminUnitMessageBubble(service, {
            sender_type: 'admin',
            message: text,
            time_formatted: nowTime,
            created_at: new Date().toISOString()
        }, state.productInfo, state.userPhoto, false);

        state.lastMessageCount++;

        const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
        if (stream) stream.scrollTop = stream.scrollHeight;

        const replyUrl = `{{ url('admin/unit/chat-service') }}/${service}/${state.activeSessionId}/reply`;
        const replyFallbackUrl = `{{ url('admin/chat-service') }}/${service}/${state.activeSessionId}/reply`;

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
        if (!state.activeSessionId) return;

        if (!confirm('Tandai sesi obrolan ini telah selesai ditangani?')) return;

        const resolveUrl = `{{ url('admin/unit/chat-service') }}/${service}/${state.activeSessionId}/resolve`;
        const resolveFallbackUrl = `{{ url('admin/chat-service') }}/${service}/${state.activeSessionId}/resolve`;

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
                state.lastMessageCount = 0;
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
                    headerBadge.style.display = 'inline-block';
                }
            }
        })
        .catch(err => {
            console.error('Error resolving chat:', err);
        });
    }

    function startAdminUnitChatPolling(service, sessionId) {
        const state = window.unitChatStates[service];
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
</script>
