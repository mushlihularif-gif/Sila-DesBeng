{{-- Reusable Admin Unit Chat Panel --}}
@php
    $chatServiceTitle = $chatServiceTitle ?? 'Layanan';
    $serviceType = $serviceType ?? 'gas';
@endphp

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-1"><i class="bx bx-chat me-2 text-primary"></i> Chat Warga - {{ $chatServiceTitle }}</h5>
            <p class="text-muted small mb-0">Komunikasi langsung dengan warga seputar {{ strtolower($chatServiceTitle) }} (Privasi terjaga, tanpa nomor HP)</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-label-primary px-3 py-2 rounded-pill font-semibold">
                Total Sesi: {{ count($chats ?? []) }}
            </span>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="row g-0">
            <!-- Left Panel: Chat List -->
            <div class="col-12 col-md-4 col-lg-4 border-end" style="background: #fafafa; min-height: 520px;">
                <div class="p-3 border-bottom bg-white">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" id="searchUnitChatInput_{{ $serviceType }}" class="form-control bg-light border-0 ps-0" placeholder="Cari nama warga..." onkeyup="filterAdminUnitChats('{{ $serviceType }}')">
                    </div>
                </div>

                <div class="overflow-auto" id="adminUnitChatListContainer_{{ $serviceType }}" style="max-height: 510px;">
                    @forelse($chats as $chat)
                        <div class="admin-unit-chat-item-{{ $serviceType }} p-3 border-bottom d-flex align-items-center gap-3 cursor-pointer transition-all" 
                             id="unitChatItem_{{ $serviceType }}_{{ $chat->id }}"
                             onclick="loadAdminUnitChat('{{ $serviceType }}', {{ $chat->id }})"
                             data-user-name="{{ strtolower($chat->user_name ?? ($chat->user->name ?? 'Warga')) }}"
                             style="cursor: pointer; border-left: 4px solid transparent;">
                            <div class="avatar avatar-md flex-shrink-0">
                                <div class="avatar-initial rounded-circle bg-label-primary fw-bold">
                                    {{ strtoupper(substr($chat->user_name ?? ($chat->user->name ?? 'W'), 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 text-truncate fw-semibold" style="font-size: 0.92rem;">
                                        {{ $chat->user_name ?? ($chat->user->name ?? 'Warga') }}
                                    </h6>
                                    <span class="text-muted" style="font-size: 11px;">
                                        {{ $chat->last_message_at ? $chat->last_message_at->format('H:i') : '' }}
                                    </span>
                                </div>
                                @if($chat->item_reference)
                                    <div class="badge bg-label-info py-0 px-2 mb-1 text-truncate" style="font-size: 10px; max-width: 170px;">
                                        <i class="bx bx-bookmark me-1"></i>{{ $chat->item_reference }}
                                    </div>
                                @endif
                                <p class="mb-0 text-muted small text-truncate" style="max-width: 180px;" id="unitChatPreview_{{ $serviceType }}_{{ $chat->id }}">
                                    {{ $chat->last_message ?? 'Memulai percakapan...' }}
                                </p>
                                <div class="d-flex align-items-center gap-1 mt-1">
                                    @if($chat->status === 'escalated')
                                        <span class="badge bg-label-warning py-0 px-2" style="font-size: 10px;" id="unitChatBadge_{{ $serviceType }}_{{ $chat->id }}">Perlu Balasan</span>
                                    @elseif($chat->status === 'resolved')
                                        <span class="badge bg-label-success py-0 px-2" style="font-size: 10px;" id="unitChatBadge_{{ $serviceType }}_{{ $chat->id }}">Selesai</span>
                                    @else
                                        <span class="badge bg-label-secondary py-0 px-2" style="font-size: 10px;" id="unitChatBadge_{{ $serviceType }}_{{ $chat->id }}">Bot</span>
                                    @endif

                                    @if($chat->unread_admin_count > 0)
                                        <span class="badge bg-danger rounded-pill ms-auto py-0 px-2" style="font-size: 10px;" id="unitChatUnread_{{ $serviceType }}_{{ $chat->id }}">
                                            {{ $chat->unread_admin_count }} baru
                                        </span>
                                    @else
                                        <span class="badge bg-danger rounded-pill ms-auto py-0 px-2 d-none" style="font-size: 10px;" id="unitChatUnread_{{ $serviceType }}_{{ $chat->id }}"></span>
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

            <!-- Right Panel: Chat Stream & Reply Box -->
            <div class="col-12 col-md-8 col-lg-8 d-flex flex-column" style="min-height: 520px;">
                <!-- Chat Header -->
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white" id="adminUnitActiveChatHeader_{{ $serviceType }}" style="min-height: 65px;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-sm">
                            <div class="avatar-initial rounded-circle bg-label-primary fw-bold" id="unitActiveChatAvatar_{{ $serviceType }}">-</div>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold" id="unitActiveChatUserName_{{ $serviceType }}">Pilih salah satu percakapan di sebelah kiri</h6>
                            <div class="d-flex align-items-center gap-2" id="unitActiveChatSubtitleWrap_{{ $serviceType }}" style="display: none !important;">
                                <span class="badge bg-label-info py-0 px-2" style="font-size: 10px;" id="unitActiveChatItemRef_{{ $serviceType }}"></span>
                                <span class="badge bg-label-secondary py-0 px-2" style="font-size: 10px;" id="unitActiveChatStatusBadge_{{ $serviceType }}"></span>
                            </div>
                        </div>
                    </div>
                    <div id="adminUnitChatActions_{{ $serviceType }}" style="display: none;">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="resolveAdminUnitActiveChat('{{ $serviceType }}')">
                            <i class="bx bx-check-double me-1"></i> Tandai Selesai
                        </button>
                    </div>
                </div>

                <!-- Chat Stream Messages -->
                <div class="flex-grow-1 p-4 overflow-auto d-flex flex-column gap-3" id="adminUnitChatMessagesStream_{{ $serviceType }}" style="max-height: 420px; min-height: 380px; background: #f8fafc;">
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted py-5 my-auto" id="adminUnitEmptyChatPlaceholder_{{ $serviceType }}">
                        <div class="bg-white p-3 rounded-circle shadow-sm mb-3">
                            <i class="bx bx-conversation fs-1 text-primary opacity-75"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Ruang Obrolan Warga</h6>
                        <p class="small text-muted mb-0">Klik nama warga di daftar sebelah kiri untuk membaca dan membalas pesan.</p>
                    </div>
                </div>

                <!-- Chat Input Area -->
                <div class="p-3 border-top bg-white" id="adminUnitChatInputContainer_{{ $serviceType }}" style="display: none;">
                    <div class="input-group">
                        <input type="text" id="adminUnitReplyInput_{{ $serviceType }}" class="form-control" placeholder="Ketik balasan admin untuk warga..." onkeypress="if(event.key === 'Enter') sendAdminUnitReply('{{ $serviceType }}')">
                        <button class="btn btn-primary px-4" type="button" onclick="sendAdminUnitReply('{{ $serviceType }}')">
                            <i class="bx bx-send me-1"></i> Kirim
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof window.unitChatStates === 'undefined') {
        window.unitChatStates = {};
    }
    window.unitChatStates['{{ $serviceType }}'] = {
        activeSessionId: null,
        pollInterval: null
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

    function loadAdminUnitChat(service, sessionId, isSilent = false) {
        const state = window.unitChatStates[service];
        state.activeSessionId = sessionId;

        // Reset highlight
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
            stream.innerHTML = '<div class="text-center py-5 my-auto text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Memuat obrolan...</div>';
        }

        fetch(`{{ url('admin/chat-service') }}/${service}/${sessionId}/messages`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const session = res.data.session;
                const messages = res.data.messages;

                // Update Header
                const userName = session.user_name || (session.user ? session.user.name : 'Warga');
                document.getElementById(`unitActiveChatUserName_${service}`).innerText = userName;
                document.getElementById(`unitActiveChatAvatar_${service}`).innerText = userName.charAt(0).toUpperCase();

                const subtitleWrap = document.getElementById(`unitActiveChatSubtitleWrap_${service}`);
                subtitleWrap.style.setProperty('display', 'flex', 'important');

                const itemRefEl = document.getElementById(`unitActiveChatItemRef_${service}`);
                if (session.item_reference) {
                    itemRefEl.innerText = session.item_reference;
                    itemRefEl.style.display = 'inline-block';
                } else {
                    itemRefEl.style.display = 'none';
                }

                const statusBadge = document.getElementById(`unitActiveChatStatusBadge_${service}`);
                if (session.status === 'escalated') {
                    statusBadge.className = 'badge bg-label-warning py-0 px-2';
                    statusBadge.innerText = 'Perlu Balasan';
                } else if (session.status === 'resolved') {
                    statusBadge.className = 'badge bg-label-success py-0 px-2';
                    statusBadge.innerText = 'Selesai';
                } else {
                    statusBadge.className = 'badge bg-label-secondary py-0 px-2';
                    statusBadge.innerText = 'Bot';
                }

                // Show Actions & Input
                document.getElementById(`adminUnitChatActions_${service}`).style.display = 'block';
                document.getElementById(`adminUnitChatInputContainer_${service}`).style.display = 'block';

                // Render Messages
                const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
                stream.innerHTML = '';

                if (messages.length === 0) {
                    stream.innerHTML = '<div class="text-center text-muted my-auto py-5"><i class="bx bx-chat fs-1 opacity-50 mb-2"></i><p class="small">Belum ada pesan dalam sesi ini.</p></div>';
                } else {
                    messages.forEach(msg => {
                        renderAdminUnitMessageBubble(service, msg);
                    });
                }

                stream.scrollTop = stream.scrollHeight;

                // Start polling
                startAdminUnitChatPolling(service, sessionId);
            }
        })
        .catch(err => {
            console.error('Error loading chat:', err);
        });
    }

    function renderAdminUnitMessageBubble(service, msg) {
        const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
        const bubble = document.createElement('div');
        const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        if (msg.sender_type === 'admin') {
            bubble.className = 'd-flex justify-content-end mb-2';
            bubble.innerHTML = `
                <div style="max-width: 75%;">
                    <div class="bg-primary text-white p-3 rounded-4 shadow-sm" style="border-bottom-right-radius: 4px !important;">
                        <p class="mb-0" style="font-size: 0.92rem; white-space: pre-wrap;">${escapeHtml(msg.message)}</p>
                    </div>
                    <div class="text-end text-muted mt-1" style="font-size: 10px;">
                        <span>${time}</span> &bull; <span class="fw-semibold text-primary">Admin</span>
                    </div>
                </div>
            `;
        } else if (msg.sender_type === 'user') {
            bubble.className = 'd-flex justify-content-start mb-2';
            bubble.innerHTML = `
                <div style="max-width: 75%;">
                    <div class="bg-white text-dark p-3 rounded-4 shadow-sm border" style="border-bottom-left-radius: 4px !important;">
                        <p class="mb-0" style="font-size: 0.92rem; white-space: pre-wrap;">${escapeHtml(msg.message)}</p>
                    </div>
                    <div class="text-start text-muted mt-1" style="font-size: 10px;">
                        <span>${time}</span> &bull; <span>Warga</span>
                    </div>
                </div>
            `;
        } else {
            // bot / system
            bubble.className = 'd-flex justify-content-center my-2';
            bubble.innerHTML = `
                <div class="badge bg-label-secondary px-3 py-2 rounded-pill text-wrap" style="max-width: 85%; font-size: 11px; font-weight: normal; line-height: 1.4;">
                    <i class="bx bx-bot me-1"></i> ${escapeHtml(msg.message)}
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
        renderAdminUnitMessageBubble(service, {
            sender_type: 'admin',
            message: text,
            created_at: new Date().toISOString()
        });

        const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
        stream.scrollTop = stream.scrollHeight;

        fetch(`{{ url('admin/chat-service') }}/${service}/${state.activeSessionId}/reply`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: text })
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const preview = document.getElementById(`unitChatPreview_${service}_${state.activeSessionId}`);
                if (preview) preview.innerText = text;

                const badge = document.getElementById(`unitChatBadge_${service}_${state.activeSessionId}`);
                if (badge) {
                    badge.className = 'badge bg-label-warning py-0 px-2';
                    badge.innerText = 'Perlu Balasan';
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

        fetch(`{{ url('admin/chat-service') }}/${service}/${state.activeSessionId}/resolve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                loadAdminUnitChat(service, state.activeSessionId, true);

                const badge = document.getElementById(`unitChatBadge_${service}_${state.activeSessionId}`);
                if (badge) {
                    badge.className = 'badge bg-label-success py-0 px-2';
                    badge.innerText = 'Selesai';
                }
            }
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

    function escapeHtml(string) {
        if (!string) return '';
        return String(string).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
</script>
