{{-- Reusable Admin Unit Chat Panel (WhatsApp & Telegram Modern Style) --}}
@php
    $chatServiceTitle = $chatServiceTitle ?? 'Layanan';
    $serviceType = $serviceType ?? 'gas';
    $firstChatId = (!empty($chats) && count($chats) > 0) ? $chats[0]->id : null;

    $adminQuickReplies = [
        'gas' => [
            ['label' => 'Stok gas ready', 'text' => 'Stok gas tersedia hari ini, silakan langsung melakukan pemesanan melalui sistem.'],
            ['label' => 'Bisa diantar', 'text' => 'Bisa diantar ke rumah Anda, pastikan titik alamat pengiriman sudah sesuai saat memesan.'],
            ['label' => 'Info tukar tabung', 'text' => 'Untuk isi ulang, mohon siapkan tabung kosong ukuran sama dalam kondisi baik.'],
        ],
        'penyewaan' => [
            ['label' => 'Alat tersedia', 'text' => 'Siap, alat ini tersedia dan siap digunakan sesuai jadwal.'],
            ['label' => 'Bisa langsung diajukan', 'text' => 'Bisa langsung melakukan permohonan sewa melalui halaman produk di sistem.'],
            ['label' => 'Konfirmasi jadwal', 'text' => 'Mohon konfirmasi tanggal mulai dan durasi penyewaan alat.'],
        ],
        'mobil' => [
            ['label' => 'Armada tersedia', 'text' => 'Armada tersedia sesuai jadwal tersebut, silakan ajukan pemesanan melalui sistem.'],
            ['label' => 'Termasuk supir', 'text' => 'Penyewaan transportasi sudah termasuk supir dari petugas pengelola layanan.'],
            ['label' => 'Konfirmasi tujuan', 'text' => 'Mohon informasikan tujuan perjalanan, tanggal, dan jam keberangkatan.'],
        ],
        'fasilitas_umum' => [
            ['label' => 'Fasilitas tersedia', 'text' => 'Fasilitas tersedia pada tanggal tersebut, silakan ajukan pemesanan melalui sistem.'],
            ['label' => 'Konfirmasi acara', 'text' => 'Mohon informasikan tanggal kegiatan, durasi, dan perkiraan jumlah peserta.'],
            ['label' => 'Ketentuan SOP', 'text' => 'Penggunaan fasilitas mengikuti ketentuan SOP kebersihan dan ketertiban yang berlaku.'],
        ],
    ][$serviceType] ?? [
        ['label' => 'Layanan tersedia', 'text' => 'Siap, layanan ini tersedia dan siap digunakan.'],
        ['label' => 'Bisa langsung diajukan', 'text' => 'Silakan langsung melakukan pengajuan melalui sistem.'],
    ];
@endphp

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 wa-chat-container" id="adminUnitChatPanel_{{ $serviceType }}">
    <div class="card-header bg-white border-bottom py-2.5 py-sm-3 px-3 px-sm-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
        <div class="d-flex align-items-center gap-2 flex-nowrap wa-no-wrap min-w-0">
            <div class="avatar avatar-sm bg-label-primary text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                <i class="bx bx-chat fs-5"></i>
            </div>
            <div class="min-w-0">
                <h5 class="fw-bold mb-0 fs-6 fs-sm-5 text-dark text-truncate">Chat Warga &bull; {{ $chatServiceTitle }}</h5>
                <small class="text-muted d-none d-sm-block">Komunikasi langsung real-time seputar unit {{ strtolower($chatServiceTitle) }}</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap flex-shrink-0 w-100 w-sm-auto justify-content-between justify-content-sm-end">
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1" onclick="refreshAdminUnitSessions('{{ $serviceType }}', false)" title="Perbarui daftar obrolan">
                <i class="bx bx-refresh"></i><span>Segarkan</span>
            </button>
            <span class="badge bg-label-primary px-3 py-1.5 py-sm-2 rounded-pill font-semibold text-nowrap" id="adminUnitTotalSessions_{{ $serviceType }}">
                Total Sesi: {{ count($chats ?? []) }}
            </span>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="row g-0">
            <!-- Left Panel: Chat List -->
            <div class="col-12 col-md-4 col-lg-4 bg-white wa-chat-sidebar">
                <div class="p-2.5 p-sm-3 border-bottom" style="background: #f8fafc;">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text bg-white border-0 ps-3"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" id="searchUnitChatInput_{{ $serviceType }}" class="form-control bg-white border-0 ps-2 rounded-pill shadow-2xs" placeholder="Cari nama warga..." oninput="filterAdminUnitChats('{{ $serviceType }}')" style="font-size: 0.88rem;">
                    </div>
                </div>

                <div class="overflow-auto wa-contact-list" id="adminUnitChatListContainer_{{ $serviceType }}" style="max-height: 520px;">
                    @forelse($chats as $chat)
                        @php
                            $displayName = method_exists($chat, 'displayUserName')
                                ? $chat->displayUserName()
                                : ($chat->user_name ?? ($chat->user->name ?? 'Warga'));
                            $userPhoto = ($chat->user && $chat->user->profile_photo_url) ? $chat->user->profile_photo_url : null;
                            $firstChar = strtoupper(mb_substr($displayName, 0, 1) ?: 'W');
                            $adminClearedId = (auth()->check() && method_exists($chat, 'adminClearedBeforeId'))
                                ? $chat->adminClearedBeforeId((int) auth()->id())
                                : 0;
                            $latestMsgObj = $chat->relationLoaded('latestMessage') ? $chat->latestMessage : null;
                            $hasVisibleAfterClear = !$adminClearedId || ($latestMsgObj && (int) $latestMsgObj->id > $adminClearedId);
                            $lastSender = $hasVisibleAfterClear ? $latestMsgObj?->sender_type : null;
                            $displayUnread = $hasVisibleAfterClear ? (int) $chat->unread_admin_count : 0;
                            $needsReply = $hasVisibleAfterClear && $chat->status !== 'resolved' && ($displayUnread > 0 || ($chat->status === 'escalated' && $lastSender !== 'admin'));
                        @endphp
                        <div class="admin-unit-chat-item-{{ $serviceType }} p-2.5 p-sm-3 border-bottom d-flex align-items-center gap-2.5 gap-sm-3 flex-nowrap wa-no-wrap cursor-pointer transition-all wa-chat-item"
                             id="unitChatItem_{{ $serviceType }}_{{ $chat->id }}"
                             onclick="loadAdminUnitChat('{{ $serviceType }}', {{ $chat->id }})"
                             data-session-id="{{ $chat->id }}"
                             data-user-name="{{ strtolower($displayName) }}"
                             style="cursor: pointer; border-left: 4px solid transparent; transition: background-color 0.15s ease;">

                            <!-- Avatar Warga -->
                            <div class="position-relative flex-shrink-0">
                                <div class="avatar avatar-md">
                                    @if($userPhoto)
                                        <img src="{{ $userPhoto }}" alt="{{ $displayName }}" class="rounded-circle w-100 h-100 shadow-2xs" style="object-fit: cover;" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'avatar-initial rounded-circle fw-bold text-white shadow-2xs\' style=\'background: linear-gradient(135deg, #696cff, #4338ca); font-size: 14px;\'>{{ $firstChar }}</div>';">
                                    @else
                                        <div class="avatar-initial rounded-circle fw-bold text-white shadow-2xs" style="background: linear-gradient(135deg, #696cff, #4338ca); font-size: 14px;">
                                            {{ $firstChar }}
                                        </div>
                                    @endif
                                </div>
                                <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                            </div>

                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-center gap-2 flex-nowrap wa-no-wrap mb-1">
                                    <h6 class="mb-0 text-truncate fw-bold text-dark" style="font-size: 0.9rem;">
                                        {{ $displayName }}
                                    </h6>
                                    <span class="text-muted flex-shrink-0" style="font-size: 11px;">
                                        {{ $chat->last_message_at ? $chat->last_message_at->format('H:i') : ($chat->updated_at ? $chat->updated_at->format('H:i') : '') }}
                                    </span>
                                </div>

                                @if($chat->item_reference)
                                    <div class="badge bg-label-info py-0 px-2 mb-1 text-truncate d-inline-block" style="font-size: 10px; max-width: 100%;">
                                        <i class="bx bx-bookmark me-1"></i>{{ $chat->item_label }}
                                    </div>
                                @endif

                                <p class="mb-0 text-muted small text-truncate" style="max-width: 100%; font-size: 0.8rem;" id="unitChatPreview_{{ $serviceType }}_{{ $chat->id }}">
                                    {{ $hasVisibleAfterClear ? ($chat->last_message ?? 'Memulai percakapan...') : 'Obrolan dibersihkan (tampilan Anda)' }}
                                </p>

                                <div class="d-flex align-items-center gap-1 mt-1.5 flex-nowrap wa-no-wrap">
                                    @if($chat->status === 'resolved')
                                        <span class="badge bg-label-success py-0.5 px-2" style="font-size: 9.5px;" id="unitChatBadge_{{ $serviceType }}_{{ $chat->id }}">Selesai</span>
                                    @elseif($needsReply)
                                        <span class="badge bg-label-warning py-0.5 px-2" style="font-size: 9.5px;" id="unitChatBadge_{{ $serviceType }}_{{ $chat->id }}">Perlu Balasan</span>
                                    @elseif($lastSender === 'admin')
                                        <span class="badge bg-label-info py-0.5 px-2" style="font-size: 9.5px;" id="unitChatBadge_{{ $serviceType }}_{{ $chat->id }}">Dibalas</span>
                                    @else
                                        <span class="badge bg-label-secondary py-0.5 px-2" style="font-size: 9.5px;" id="unitChatBadge_{{ $serviceType }}_{{ $chat->id }}">Aktif</span>
                                    @endif

                                    @if($displayUnread > 0)
                                        <span class="badge bg-success rounded-pill ms-auto py-0.5 px-2" style="font-size: 10px;" id="unitChatUnread_{{ $serviceType }}_{{ $chat->id }}">
                                            {{ $displayUnread }}
                                        </span>
                                    @else
                                        <span class="badge bg-success rounded-pill ms-auto py-0.5 px-2 d-none" style="font-size: 10px;" id="unitChatUnread_{{ $serviceType }}_{{ $chat->id }}"></span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 px-3 text-muted" id="adminUnitEmptyList_{{ $serviceType }}">
                            <i class="bx bx-message-rounded-x fs-1 opacity-50 mb-2"></i>
                            <p class="small mb-0">Belum ada obrolan dari warga untuk layanan ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right Panel: Active Chat Stream -->
            <div class="col-12 col-md-8 col-lg-8 d-flex flex-column bg-white position-relative wa-chat-main">

                <!-- 1. EMPTY STATE -->
                <div id="adminUnitNoChatSelected_{{ $serviceType }}" class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center p-4 my-auto wa-chat-empty" style="background: #f8fafc;">
                    <div class="avatar avatar-xl bg-label-primary rounded-circle mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px;">
                        <i class="bx bx-conversation text-primary" style="font-size: 36px;"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Ruang Obrolan Warga</h5>
                    <p class="text-muted small mb-3" style="max-width: 320px;">
                        Pilih salah satu percakapan di sebelah kiri untuk membaca dan membalas pesan warga secara langsung.
                    </p>
                    <div class="badge bg-white border text-muted px-3 py-1.5 rounded-pill shadow-2xs font-normal text-wrap" style="font-size: 11px;">
                        <i class="bx bx-shield-quarter text-success me-1"></i> Terhubung langsung ke sistem layanan wilayah
                    </div>
                </div>

                <!-- 2. ACTIVE CHAT WRAPPER -->
                <div id="adminUnitActiveChatWrapper_{{ $serviceType }}" class="d-none flex-column flex-grow-1 w-100">

                    <!-- Chat Header -->
                    <div class="p-2.5 p-sm-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2 bg-white wa-active-header">
                        <div class="d-flex align-items-center gap-2 gap-sm-3 flex-nowrap wa-no-wrap min-w-0">
                            <button type="button" class="btn btn-sm btn-icon btn-light border rounded-circle d-md-none flex-shrink-0 wa-mobile-back-btn" onclick="closeAdminUnitMobileChat('{{ $serviceType }}')" title="Kembali ke daftar obrolan" aria-label="Kembali ke daftar obrolan">
                                <i class="bx bx-arrow-back fs-5"></i>
                            </button>
                            <div class="position-relative flex-shrink-0">
                                <div class="avatar wa-active-avatar" id="unitActiveChatAvatarWrap_{{ $serviceType }}">
                                    <div class="avatar-initial rounded-circle bg-label-primary fw-bold" id="unitActiveChatAvatar_{{ $serviceType }}">-</div>
                                </div>
                                <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" id="unitActiveChatOnlineDot_{{ $serviceType }}" style="width: 10px; height: 10px;"></span>
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <h6 class="mb-0 fw-bold text-dark text-truncate wa-active-name" id="unitActiveChatUserName_{{ $serviceType }}">-</h6>
                                    <span class="badge py-0.5 px-2 fw-semibold d-none flex-shrink-0" style="font-size: 10px;" id="unitActiveChatStatusBadge_{{ $serviceType }}"></span>
                                </div>
                                <div class="d-flex align-items-center gap-1 mt-0.5 flex-nowrap wa-no-wrap" id="unitActiveChatSubtitleWrap_{{ $serviceType }}">
                                    <span class="text-success small fw-medium text-nowrap" style="font-size: 11px;">
                                        <i class="bx bxs-circle me-1" style="font-size: 6px; vertical-align: middle;"></i>Online
                                    </span>
                                    <span class="text-muted opacity-50">&bull;</span>
                                    <span class="text-muted small text-truncate" style="font-size: 11px;">Warga Terhubung</span>
                                </div>
                            </div>
                        </div>
                        <div id="adminUnitChatActions_{{ $serviceType }}" class="d-flex align-items-center gap-1.5 gap-sm-2 flex-nowrap wa-no-wrap justify-content-end flex-shrink-0 wa-active-actions">
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 px-sm-3 py-1 shadow-2xs d-inline-flex align-items-center justify-content-center gap-1 fw-semibold flex-fill flex-md-grow-0 text-nowrap" onclick="clearAdminUnitActiveChat('{{ $serviceType }}')" title="Bersihkan chat hanya di tampilan Anda (tidak menghapus riwayat warga)">
                                <i class="bx bx-trash"></i>
                                <span>Bersihkan</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2.5 px-sm-3 py-1 shadow-2xs d-inline-flex align-items-center justify-content-center gap-1 fw-semibold flex-fill flex-md-grow-0 text-nowrap" onclick="resolveAdminUnitActiveChat('{{ $serviceType }}')">
                                <i class="bx bx-check-double"></i>
                                <span>Selesai</span>
                            </button>
                        </div>
                    </div>

                    <!-- Chat Stream Messages -->
                    <div class="flex-grow-1 p-2.5 p-sm-4 overflow-auto d-flex flex-column gap-2 wa-chat-stream" id="adminUnitChatMessagesStream_{{ $serviceType }}"
                         style="background-color: #efeae2; background-image: radial-gradient(rgba(17, 27, 33, 0.06) 1px, transparent 0); background-size: 18px 18px;">
                    </div>

                    <!-- Inline Reply Error Banner -->
                    <div id="adminUnitChatError_{{ $serviceType }}" class="alert alert-danger rounded-0 border-0 py-2 px-3 mb-0 small d-none" role="alert"></div>

                    <!-- Chat Input Area -->
                    <div class="p-2 p-sm-3 border-top" id="adminUnitChatInputContainer_{{ $serviceType }}" style="background: #f0f2f5;">
                        <!-- Quick reply template chips -->
                        <div class="d-flex align-items-center gap-1.5 mb-2 overflow-auto pb-1 flex-nowrap wa-no-wrap" style="white-space: nowrap; scrollbar-width: none;">
                            <span class="text-muted small me-1 flex-shrink-0" style="font-size: 11px;"><i class="bx bx-zap text-warning me-1"></i>Cepat:</span>
                            @foreach($adminQuickReplies as $qr)
                                <button type="button" class="btn btn-xs btn-white bg-white border rounded-pill shadow-2xs text-secondary px-2.5 py-1 flex-shrink-0 text-nowrap" onclick="insertAdminQuickReply('{{ $serviceType }}', '{{ addslashes($qr['text']) }}')" style="font-size: 11px;">{{ $qr['label'] }}</button>
                            @endforeach
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-nowrap wa-no-wrap">
                            <div class="flex-grow-1 min-w-0 position-relative">
                                <input type="text" id="adminUnitReplyInput_{{ $serviceType }}" class="form-control rounded-pill px-3 py-2 bg-white border shadow-2xs" placeholder="Ketik balasan untuk warga..." autocomplete="off" onkeypress="if(event.key === 'Enter') sendAdminUnitReply('{{ $serviceType }}')" style="font-size: 0.9rem; border-color: #d1d7db;">
                            </div>
                            <button class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm p-0" id="adminUnitReplyBtn_{{ $serviceType }}" type="button" onclick="sendAdminUnitReply('{{ $serviceType }}')" style="width: 40px; height: 40px;" title="Kirim Pesan">
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
.wa-active-avatar {
    width: 40px;
    height: 40px;
}
.wa-active-name {
    font-size: 0.95rem;
    max-width: 100%;
}
.wa-chat-stream {
    height: 460px;
    min-height: 380px;
}
.wa-msg-bubble-wrap {
    max-width: 78%;
    min-width: 0;
}
.wa-msg-text {
    overflow-wrap: anywhere;
    word-break: break-word;
}
.wa-system-notice {
    max-width: 82%;
    font-size: 11.5px;
    color: #54656f;
    line-height: 1.45;
    background: rgba(255, 255, 255, 0.95) !important;
    border-radius: 12px !important;
    overflow-wrap: anywhere;
    word-break: break-word;
}
@media (min-width: 768px) {
    .wa-chat-sidebar {
        min-height: 540px;
        border-right: 1px solid #eceef1;
    }
    .wa-chat-main,
    .wa-chat-empty {
        min-height: 540px;
    }
}
@media (max-width: 767.98px) {
    .wa-chat-container:not(.wa-mobile-chat-open) .wa-chat-main {
        display: none !important;
    }
    .wa-chat-container.wa-mobile-chat-open .wa-chat-sidebar {
        display: none !important;
    }
    .wa-chat-container.wa-mobile-chat-open .wa-chat-main {
        display: flex !important;
    }
    .wa-contact-list {
        max-height: 430px !important;
    }
    .wa-mobile-back-btn {
        width: 34px !important;
        height: 34px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
    }
    .wa-active-avatar {
        width: 36px !important;
        height: 36px !important;
    }
    .wa-active-name {
        font-size: 0.88rem !important;
    }
    .wa-active-actions {
        padding-top: 0.45rem;
        border-top: 1px solid #f1f5f9;
    }
    .wa-active-actions .btn {
        font-size: 0.76rem !important;
        padding: 0.35rem 0.65rem !important;
    }
    .wa-chat-stream {
        height: 370px !important;
        min-height: 300px !important;
    }
    .wa-msg-bubble-wrap {
        max-width: 88% !important;
    }
    .wa-system-notice {
        max-width: 94% !important;
        font-size: 11px !important;
    }
}
.admin-unit-msg-bubble {
    opacity: 1 !important;
    visibility: visible !important;
    transform: none !important;
}
.unit-chat-user-bubble {
    padding: 11px 13px 9px;
}
.unit-chat-product-quote {
    width: 100%;
    box-sizing: border-box;
    padding: 10px;
    margin-bottom: 10px;
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
        sessionsPollInterval: null,
        isSending: false,
        productInfo: null,
        userPhoto: null
    };

    function getAdminUnitCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    }

    function isAdminUnitChatVisible(service) {
        const panel = document.getElementById(`adminUnitChatPanel_${service}`);
        if (!panel) return false;
        return !!(panel.offsetWidth || panel.offsetHeight || panel.getClientRects().length);
    }

    function showAdminUnitError(service, msg) {
        const el = document.getElementById(`adminUnitChatError_${service}`);
        if (!el) return;
        if (!msg) {
            el.classList.add('d-none');
            el.textContent = '';
            return;
        }
        el.textContent = msg;
        el.classList.remove('d-none');
    }

    function filterAdminUnitChats(service) {
        const query = (document.getElementById(`searchUnitChatInput_${service}`)?.value || '').toLowerCase().trim();
        const items = document.querySelectorAll(`.admin-unit-chat-item-${service}`);
        items.forEach(item => {
            const name = item.getAttribute('data-user-name') || '';
            item.style.display = name.includes(query) ? 'flex' : 'none';
        });
    }

    function insertAdminQuickReply(service, text) {
        const input = document.getElementById(`adminUnitReplyInput_${service}`);
        if (input) {
            input.value = text;
            input.focus();
        }
    }

    function updateAdminUnitTabBadge(service, totalUnread) {
        document.querySelectorAll(`[data-unit-chat-tab-badge="${service}"]`).forEach(badge => {
            const count = Number(totalUnread || 0);
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : String(count);
                badge.classList.remove('d-none');
            } else {
                badge.textContent = '0';
                badge.classList.add('d-none');
            }
        });
    }

    function renderAdminUnitSessionList(service, sessions) {
        const container = document.getElementById(`adminUnitChatListContainer_${service}`);
        const state = window.unitChatStates[service];
        if (!container || !state) return;

        if (!Array.isArray(sessions) || sessions.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5 text-muted" id="adminUnitEmptyList_${service}">
                    <i class="bx bx-message-rounded-x fs-1 opacity-50 mb-2"></i>
                    <p class="small mb-0">Belum ada obrolan dari warga untuk layanan ini.</p>
                </div>
            `;
            return;
        }

        const html = sessions.map(chat => {
            const name = chat.user_name || 'Warga';
            const initial = (name.trim().charAt(0) || 'W').toUpperCase();
            const isActive = Number(state.activeSessionId) === Number(chat.id);
            const activeStyle = isActive
                ? 'cursor: pointer; background-color: #eef2ff; border-left: 4px solid #696cff; transition: background-color 0.15s ease;'
                : 'cursor: pointer; border-left: 4px solid transparent; transition: background-color 0.15s ease;';

            const avatarHtml = chat.user_photo
                ? `<img src="${escapeHtml(chat.user_photo)}" alt="${escapeHtml(name)}" class="rounded-circle w-100 h-100 shadow-2xs" style="object-fit: cover;" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'avatar-initial rounded-circle fw-bold text-white shadow-2xs\\' style=\\'background: linear-gradient(135deg, #696cff, #4338ca); font-size: 14px;\\'>${escapeHtml(initial)}</div>';">`
                : `<div class="avatar-initial rounded-circle fw-bold text-white shadow-2xs" style="background: linear-gradient(135deg, #696cff, #4338ca); font-size: 14px;">${escapeHtml(initial)}</div>`;

            const itemBadgeHtml = chat.item_label
                ? `<div class="badge bg-label-info py-0 px-2 mb-1 text-truncate d-inline-block" style="font-size: 10px; max-width: 100%;"><i class="bx bx-bookmark me-1"></i>${escapeHtml(chat.item_label)}</div>`
                : '';

            let statusBadgeHtml = `<span class="badge bg-label-secondary py-0.5 px-2" style="font-size: 9.5px;" id="unitChatBadge_${service}_${chat.id}">Aktif</span>`;
            if (chat.status === 'resolved') {
                statusBadgeHtml = `<span class="badge bg-label-success py-0.5 px-2" style="font-size: 9.5px;" id="unitChatBadge_${service}_${chat.id}">Selesai</span>`;
            } else if (chat.needs_reply) {
                statusBadgeHtml = `<span class="badge bg-label-warning py-0.5 px-2" style="font-size: 9.5px;" id="unitChatBadge_${service}_${chat.id}">Perlu Balasan</span>`;
            } else if (chat.last_sender_type === 'admin') {
                statusBadgeHtml = `<span class="badge bg-label-info py-0.5 px-2" style="font-size: 9.5px;" id="unitChatBadge_${service}_${chat.id}">Dibalas</span>`;
            }

            const unreadCount = isActive ? 0 : Number(chat.unread_admin_count || 0);
            const unreadHtml = unreadCount > 0
                ? `<span class="badge bg-success rounded-pill ms-auto py-0.5 px-2" style="font-size: 10px;" id="unitChatUnread_${service}_${chat.id}">${unreadCount}</span>`
                : `<span class="badge bg-success rounded-pill ms-auto py-0.5 px-2 d-none" style="font-size: 10px;" id="unitChatUnread_${service}_${chat.id}"></span>`;

            return `
                <div class="admin-unit-chat-item-${service} p-2.5 p-sm-3 border-bottom d-flex align-items-center gap-2.5 gap-sm-3 flex-nowrap wa-no-wrap cursor-pointer transition-all wa-chat-item"
                     id="unitChatItem_${service}_${chat.id}"
                     onclick="loadAdminUnitChat('${service}', ${chat.id})"
                     data-session-id="${chat.id}"
                     data-user-name="${escapeHtml(name.toLowerCase())}"
                     style="${activeStyle}">
                    <div class="position-relative flex-shrink-0">
                        <div class="avatar avatar-md">${avatarHtml}</div>
                        <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-center gap-2 flex-nowrap wa-no-wrap mb-1">
                            <h6 class="mb-0 text-truncate fw-bold text-dark" style="font-size: 0.9rem;">${escapeHtml(name)}</h6>
                            <span class="text-muted flex-shrink-0" style="font-size: 11px;">${escapeHtml(chat.last_message_time || '')}</span>
                        </div>
                        ${itemBadgeHtml}
                        <p class="mb-0 text-muted small text-truncate" style="max-width: 100%; font-size: 0.8rem;" id="unitChatPreview_${service}_${chat.id}">${escapeHtml(chat.last_message || 'Memulai percakapan...')}</p>
                        <div class="d-flex align-items-center gap-1 mt-1.5 flex-nowrap wa-no-wrap">
                            ${statusBadgeHtml}
                            ${unreadHtml}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = html;
        filterAdminUnitChats(service);
    }

    function closeAdminUnitMobileChat(service) {
        const panel = document.getElementById(`adminUnitChatPanel_${service}`);
        if (panel) {
            panel.classList.remove('wa-mobile-chat-open');
        }
    }

    function refreshAdminUnitSessions(service, autoSelectIfVisible = false) {
        const state = window.unitChatStates[service];
        if (!state) return;

        const appBase = '{{ request()->getBaseUrl() }}';
        const url = `${appBase}/admin/unit/chat-service/${service}/sessions`;

        fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(res => {
            if (res.status === 'success' && res.data) {
                const sessions = res.data.sessions || [];
                const totalEl = document.getElementById(`adminUnitTotalSessions_${service}`);
                if (totalEl) totalEl.textContent = `Total Sesi: ${res.data.total_sessions ?? sessions.length}`;

                updateAdminUnitTabBadge(service, res.data.total_unread ?? 0);
                renderAdminUnitSessionList(service, sessions);

                if (autoSelectIfVisible && window.innerWidth >= 768 && !state.activeSessionId && sessions.length > 0 && isAdminUnitChatVisible(service)) {
                    loadAdminUnitChat(service, sessions[0].id, false);
                }
            }
        })
        .catch(err => {
            console.error('Gagal memperbarui daftar obrolan:', err);
        });
    }

    function loadAdminUnitChat(service, sessionId, isSilent = false) {
        const state = window.unitChatStates[service];
        if (!state) return;

        const isSwitchingSession = (Number(state.activeSessionId) !== Number(sessionId));
        state.activeSessionId = Number(sessionId);

        if (!isSilent) {
            showAdminUnitError(service, '');
            const panel = document.getElementById(`adminUnitChatPanel_${service}`);
            if (panel) {
                panel.classList.add('wa-mobile-chat-open');
            }
        }

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

        document.querySelectorAll(`.admin-unit-chat-item-${service}`).forEach(el => {
            el.style.backgroundColor = '';
            el.style.borderLeftColor = 'transparent';
        });

        const activeItem = document.getElementById(`unitChatItem_${service}_${sessionId}`);
        if (activeItem) {
            activeItem.style.backgroundColor = '#eef2ff';
            activeItem.style.borderLeftColor = '#696cff';

            const unreadBadge = document.getElementById(`unitChatUnread_${service}_${sessionId}`);
            if (unreadBadge) {
                unreadBadge.classList.add('d-none');
                unreadBadge.innerText = '';
            }
        }

        const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
        if (!isSilent && (isSwitchingSession || (stream && !stream.querySelector('.admin-unit-msg-bubble')))) {
            if (stream) {
                stream.innerHTML = '<div class="text-center py-5 my-auto text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Memuat obrolan...</div>';
            }
        }

        const appBase = '{{ request()->getBaseUrl() }}';
        const fetchUrl = `${appBase}/admin/unit/chat-service/${service}/${sessionId}/messages`;

        fetch(fetchUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(res => {
            if (res.status === 'success' && res.data) {
                if (Number(state.activeSessionId) !== Number(sessionId)) return;

                const session = res.data.session || {};
                const rawMessages = res.data.messages || [];
                const messages = Array.isArray(rawMessages) ? rawMessages : Object.values(rawMessages);
                const userPhoto = res.data.user_photo || null;
                const productInfo = res.data.product_info || null;

                state.productInfo = productInfo;
                state.userPhoto = userPhoto;

                const userName = session.user_name || 'Warga';
                const userNameEl = document.getElementById(`unitActiveChatUserName_${service}`);
                if (userNameEl) userNameEl.innerText = userName;

                const avatarWrap = document.getElementById(`unitActiveChatAvatarWrap_${service}`);
                if (avatarWrap) {
                    if (userPhoto) {
                        avatarWrap.innerHTML = `<img src="${escapeHtml(userPhoto)}" alt="${escapeHtml(userName)}" class="rounded-circle w-100 h-100 shadow-2xs" style="object-fit: cover;" onerror="this.style.display='none'">`;
                    } else {
                        const initialChar = userName.charAt(0).toUpperCase() || 'W';
                        avatarWrap.innerHTML = `<div class="avatar-initial rounded-circle fw-bold text-white shadow-2xs" style="background: linear-gradient(135deg, #696cff, #4338ca);">${escapeHtml(initialChar)}</div>`;
                    }
                }

                const statusBadge = document.getElementById(`unitActiveChatStatusBadge_${service}`);
                if (statusBadge) {
                    if (session.status === 'resolved') {
                        statusBadge.className = 'badge bg-label-success py-0.5 px-2.5 fw-semibold';
                        statusBadge.innerText = 'Selesai';
                        statusBadge.classList.remove('d-none');
                    } else if (session.last_sender_type === 'admin') {
                        statusBadge.className = 'badge bg-label-info py-0.5 px-2.5 fw-semibold';
                        statusBadge.innerText = 'Dibalas';
                        statusBadge.classList.remove('d-none');
                    } else if (session.status === 'escalated') {
                        statusBadge.className = 'badge bg-label-warning py-0.5 px-2.5 fw-semibold';
                        statusBadge.innerText = 'Perlu Balasan';
                        statusBadge.classList.remove('d-none');
                    } else {
                        statusBadge.className = 'badge bg-label-secondary py-0.5 px-2.5 fw-semibold';
                        statusBadge.innerText = 'Aktif';
                        statusBadge.classList.remove('d-none');
                    }
                }

                if (stream) {
                    const hasSpinner = stream.querySelector('.spinner-border') !== null;
                    const hasExistingBubbles = stream.querySelector('.admin-unit-msg-bubble') !== null;

                    if (isSwitchingSession || !isSilent || hasSpinner || !hasExistingBubbles) {
                        stream.innerHTML = '';

                        if (messages.length === 0) {
                            stream.innerHTML = '<div class="text-center text-muted my-auto py-5"><i class="bx bx-chat fs-1 opacity-50 mb-2"></i><p class="small mb-0">Obrolan kosong atau sudah dibersihkan di tampilan Anda.<br>Pesan baru dari warga akan muncul otomatis di sini.</p></div>';
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

                startAdminUnitChatPolling(service, sessionId);
            }
        })
        .catch(err => {
            console.error('Error loading chat:', err);
            if (!isSilent && stream && !stream.querySelector('.admin-unit-msg-bubble')) {
                stream.innerHTML = `<div class="text-center text-danger my-auto py-5"><i class="bx bx-error-circle fs-1 mb-2"></i><p class="small mb-2">Gagal memuat obrolan (${escapeHtml(err.message || 'Koneksi bermasalah')}).</p><button class="btn btn-sm btn-outline-primary" onclick="loadAdminUnitChat('${service}', ${sessionId})">Muat Ulang</button></div>`;
            }
        });
    }

    function renderAdminUnitMessageBubble(service, msg, productInfo, userPhoto, isFirstUserMsg = false) {
        const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
        if (!stream || !msg) return;

        const msgUniqueId = msg.id ? `adminUnitMsg_${service}_${msg.id}` : `adminUnitMsg_${service}_temp_${Date.now()}`;
        if (msg.id && document.getElementById(msgUniqueId)) {
            return;
        }

        try {
            const bubble = document.createElement('div');
            bubble.id = msgUniqueId;

            let time = msg.time_formatted || '';
            if (!time && msg.created_at) {
                try {
                    const safeDateStr = typeof msg.created_at === 'string' && msg.created_at.includes(' ') && !msg.created_at.includes('T')
                        ? msg.created_at.replace(' ', 'T')
                        : msg.created_at;
                    const d = new Date(safeDateStr);
                    if (!isNaN(d.getTime())) {
                        time = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    }
                } catch (e) {}
            }

            const msgText = escapeHtml(msg.message || '');

            if (msg.sender_type === 'admin') {
                const senderLabel = msg.sender_name ? `<div class="fw-semibold mb-1" style="font-size: 10.5px; color: #0f766e;">${escapeHtml(msg.sender_name)}</div>` : '';
                bubble.className = 'admin-unit-msg-bubble chat-bubble-pop d-flex justify-content-end align-items-end gap-2 flex-nowrap wa-no-wrap mb-2';
                bubble.innerHTML = `
                    <div class="wa-msg-bubble-wrap">
                        <div class="p-2.5 px-3 rounded-3 shadow-xs text-dark" style="background: #d9fdd3; border-bottom-right-radius: 2px !important; border: 1px solid rgba(0,0,0,0.04);">
                            ${senderLabel}
                            <p class="mb-0 wa-msg-text" style="font-size: 0.88rem; color: #111b21; white-space: pre-wrap; line-height: 1.45;">${msgText}</p>
                            <div class="d-flex align-items-center justify-content-end gap-1 mt-1 flex-nowrap wa-no-wrap" style="font-size: 10px; color: #667781;">
                                <span>${time}</span>
                                <i class="bx bx-check-double text-primary" style="font-size: 15px;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="avatar avatar-xs flex-shrink-0 mb-1 d-none d-sm-flex">
                        <div class="avatar-initial rounded-circle bg-primary text-white fw-bold shadow-2xs" style="font-size: 10px;">AD</div>
                    </div>
                `;
            } else if (msg.sender_type === 'user') {
                bubble.className = 'admin-unit-msg-bubble chat-bubble-pop d-flex justify-content-start align-items-end gap-2 flex-nowrap wa-no-wrap mb-2';

                let userAvatarHtml = userPhoto
                    ? `<img src="${escapeHtml(userPhoto)}" class="rounded-circle w-100 h-100 shadow-2xs" style="object-fit: cover;" onerror="this.style.display='none'">`
                    : `<div class="avatar-initial rounded-circle fw-bold text-white shadow-2xs" style="background: linear-gradient(135deg, #696cff, #4338ca); font-size: 10px;">W</div>`;

                let quotedHtml = '';
                if (isFirstUserMsg && productInfo && productInfo.title) {
                    const prodTitle = escapeHtml(productInfo.title || '');
                    const prodPrice = escapeHtml(productInfo.price || '');
                    const prodImg = productInfo.image ? escapeHtml(productInfo.image) : '';
                    const prodUrl = escapeHtml(safeChatProductUrl(productInfo.url));

                    quotedHtml = `
                        <div class="unit-chat-product-quote rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2 shadow-2xs">
                            <div class="d-flex align-items-center gap-2 overflow-hidden min-w-0 flex-nowrap wa-no-wrap">
                                ${prodImg ? `<img src="${prodImg}" alt="${prodTitle}" class="rounded flex-shrink-0 shadow-2xs" style="width: 44px; height: 44px; object-fit: cover;" onerror="this.style.display='none'">` : ''}
                                <div class="overflow-hidden min-w-0">
                                    <div class="text-uppercase text-muted fw-bold mb-0.5" style="font-size: 9px; letter-spacing: 0.4px;">Unit Layanan yang Ditanyakan</div>
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.85rem;">${prodTitle}</div>
                                    ${prodPrice ? `<div class="text-success fw-bold small mt-0.5" style="font-size: 0.8rem;">${prodPrice}</div>` : ''}
                                </div>
                            </div>
                            ${prodUrl ? `
                                <div class="flex-shrink-0">
                                    <a href="${prodUrl}" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;">
                                        <i class="bx bx-show me-1"></i> Detail
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                    `;
                }

                bubble.innerHTML = `
                    <div class="avatar avatar-xs flex-shrink-0 mb-1 d-none d-sm-flex">
                        ${userAvatarHtml}
                    </div>
                    <div class="wa-msg-bubble-wrap">
                        <div class="unit-chat-user-bubble bg-white text-dark rounded-3 shadow-xs border" style="border-bottom-left-radius: 2px !important; border-color: rgba(0,0,0,0.06) !important;">
                            ${quotedHtml}
                            <p class="mb-0 wa-msg-text" style="font-size: 0.88rem; color: #111b21; white-space: pre-wrap; line-height: 1.45;">${msgText}</p>
                            <div class="text-end mt-1" style="font-size: 10px; color: #667781;">
                                <span>${time}</span>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                bubble.className = 'admin-unit-msg-bubble chat-bubble-pop d-flex justify-content-center my-2';
                bubble.innerHTML = `
                    <div class="bg-white border px-3 py-2 shadow-2xs d-flex align-items-start gap-2 flex-nowrap wa-no-wrap text-start wa-system-notice">
                        <i class="bx bx-info-circle text-primary flex-shrink-0 mt-0.5" style="font-size: 15px;"></i>
                        <span class="min-w-0">${msgText}</span>
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
        if (!state || !state.activeSessionId || state.isSending) return;

        const input = document.getElementById(`adminUnitReplyInput_${service}`);
        const sendBtn = document.getElementById(`adminUnitReplyBtn_${service}`);
        const text = (input?.value || '').trim();
        if (!text) return;

        state.isSending = true;
        if (sendBtn) sendBtn.disabled = true;
        showAdminUnitError(service, '');
        input.value = '';

        const nowTime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
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

        fetch(replyUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getAdminUnitCsrf(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: text })
        })
        .then(async res => {
            const payload = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(payload.message || ('Gagal mengirim balasan (HTTP ' + res.status + ')'));
            }
            return payload;
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
                    badge.className = 'badge bg-label-info py-0.5 px-2';
                    badge.innerText = 'Dibalas';
                    badge.classList.remove('d-none');
                }

                const headerBadge = document.getElementById(`unitActiveChatStatusBadge_${service}`);
                if (headerBadge) {
                    headerBadge.className = 'badge bg-label-info py-0.5 px-2.5 fw-semibold';
                    headerBadge.innerText = 'Dibalas';
                    headerBadge.classList.remove('d-none');
                }
            } else {
                throw new Error(res.message || 'Gagal mengirim balasan.');
            }
        })
        .catch(err => {
            console.error('Error replying:', err);
            const tempEl = document.getElementById(`adminUnitMsg_${service}_${tempMsgId}`);
            if (tempEl) tempEl.remove();
            if (input && !input.value) input.value = text;
            showAdminUnitError(service, err.message || 'Balasan gagal terkirim. Silakan coba lagi.');
        })
        .finally(() => {
            state.isSending = false;
            if (sendBtn) sendBtn.disabled = false;
            input?.focus();
        });
    }

    function clearAdminUnitActiveChat(service) {
        const state = window.unitChatStates[service];
        if (!state || !state.activeSessionId) return;

        if (!confirm('Bersihkan isi obrolan ini hanya di tampilan Anda?\nRiwayat di sisi warga dan pengelola lain tidak ikut terhapus.')) return;

        const appBase = '{{ request()->getBaseUrl() }}';
        const clearUrl = `${appBase}/admin/unit/chat-service/${service}/${state.activeSessionId}/clear`;

        fetch(clearUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': getAdminUnitCsrf(),
                'Accept': 'application/json'
            }
        })
        .then(async res => {
            const payload = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(payload.message || ('HTTP ' + res.status));
            return payload;
        })
        .then(res => {
            if (res.status === 'success') {
                showAdminUnitError(service, '');
                const stream = document.getElementById(`adminUnitChatMessagesStream_${service}`);
                if (stream) {
                    stream.innerHTML = '<div class="text-center text-muted my-auto py-5"><i class="bx bx-check-circle text-success fs-1 opacity-75 mb-2"></i><p class="small mb-0">Obrolan di tampilan Anda telah dibersihkan.<br>Pesan baru dari warga akan langsung muncul di sini.</p></div>';
                }
                const preview = document.getElementById(`unitChatPreview_${service}_${state.activeSessionId}`);
                if (preview) preview.innerText = 'Obrolan dibersihkan (tampilan Anda)';
                refreshAdminUnitSessions(service, false);
            }
        })
        .catch(err => {
            console.error('Error clearing chat:', err);
            showAdminUnitError(service, err.message || 'Gagal membersihkan obrolan.');
        });
    }

    function resolveAdminUnitActiveChat(service) {
        const state = window.unitChatStates[service];
        if (!state || !state.activeSessionId) return;

        if (!confirm('Tandai sesi obrolan ini telah selesai ditangani?')) return;

        const appBase = '{{ request()->getBaseUrl() }}';
        const resolveUrl = `${appBase}/admin/unit/chat-service/${service}/${state.activeSessionId}/resolve`;

        fetch(resolveUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': getAdminUnitCsrf(),
                'Accept': 'application/json'
            }
        })
        .then(async res => {
            const payload = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(payload.message || ('HTTP ' + res.status));
            return payload;
        })
        .then(res => {
            if (res.status === 'success') {
                showAdminUnitError(service, '');
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
            showAdminUnitError(service, err.message || 'Gagal menandai obrolan selesai.');
        });
    }

    function startAdminUnitChatPolling(service, sessionId) {
        const state = window.unitChatStates[service];
        if (!state) return;
        if (state.pollInterval) {
            clearInterval(state.pollInterval);
        }

        state.pollInterval = setInterval(() => {
            if (!document.hidden && Number(state.activeSessionId) === Number(sessionId) && isAdminUnitChatVisible(service)) {
                loadAdminUnitChat(service, sessionId, true);
            }
        }, 4000);
    }

    function safeChatProductUrl(value) {
        if (!value || value === '#') return '';
        try {
            const url = new URL(value, window.location.origin);
            return ['http:', 'https:'].includes(url.protocol) && url.origin === window.location.origin ? url.href : '';
        } catch (_) { return ''; }
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

    function initAdminUnitChatPanel_{{ $serviceType }}() {
        const svc = '{{ $serviceType }}';
        const state = window.unitChatStates[svc];
        if (!state) return;

        // Polling daftar sesi secara real-time (update Total Sesi, badge unread tab, & pesan baru warga)
        if (state.sessionsPollInterval) {
            clearInterval(state.sessionsPollInterval);
        }
        state.sessionsPollInterval = setInterval(() => {
            if (!document.hidden) {
                refreshAdminUnitSessions(svc, true);
            }
        }, 5000);

        // Jika tab Chat sedang aktif sejak awal di desktop, langsung buka sesi pertama (di HP tampilkan daftar dulu)
        @if($firstChatId)
        if (window.innerWidth >= 768 && isAdminUnitChatVisible(svc)) {
            loadAdminUnitChat(svc, {{ $firstChatId }}, false);
        }
        @endif

        // Ketika admin/staf berpindah ke tab "Layanan Pesan", langsung segarkan sesi & buka sesi pertama
        const panel = document.getElementById(`adminUnitChatPanel_${svc}`);
        const tabPane = panel ? panel.closest('.tab-pane') : null;
        if (tabPane && tabPane.id) {
            document.querySelectorAll(`[data-bs-target="#${tabPane.id}"]`).forEach(tabBtn => {
                tabBtn.addEventListener('shown.bs.tab', () => {
                    refreshAdminUnitSessions(svc, true);
                    if (state.activeSessionId) {
                        loadAdminUnitChat(svc, state.activeSessionId, false);
                    }
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdminUnitChatPanel_{{ $serviceType }});
    } else {
        setTimeout(initAdminUnitChatPanel_{{ $serviceType }}, 100);
    }
</script>
