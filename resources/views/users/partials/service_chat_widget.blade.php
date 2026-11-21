{{-- Reusable User Service Chat Widget --}}
@php
    $serviceType = $serviceType ?? 'gas';
    $serviceTitle = $serviceTitle ?? 'Layanan Daerah';
    $itemRef = $itemRef ?? '';

    $explicitRawId = (int) ($targetRegionId ?? (request()->integer('filter_region_id') ?: request()->integer('region_id') ?: 0));
    $explicitResolvedId = $explicitRawId > 0 ? \App\Models\Region::resolveServiceRegionId($explicitRawId) : null;
    $explicitRegion = $explicitResolvedId ? \App\Models\Region::find($explicitResolvedId) : null;
    $explicitIsVillage = $explicitRegion && in_array(strtolower((string) $explicitRegion->type), ['desa', 'kelurahan'], true);

    // Kumpulkan pilihan desa/kelurahan dari item katalog & opsi filter
    $candidateVillageIds = collect();
    if (isset($catalogItems) && is_iterable($catalogItems)) {
        foreach ($catalogItems as $catItem) {
            if (!empty($catItem->region_id)) {
                $vId = \App\Models\Region::resolveServiceRegionId((int) $catItem->region_id);
                if ($vId) {
                    $candidateVillageIds->push($vId);
                }
            }
        }
    }
    if (isset($filterRegions) && is_iterable($filterRegions)) {
        foreach ($filterRegions as $fReg) {
            if (!empty($fReg->id)) {
                $candidateVillageIds->push((int) $fReg->id);
            }
        }
    }

    $userVillageId = null;
    if (auth()->check() && auth()->user()->region_id) {
        $uResolved = \App\Models\Region::resolveServiceRegionId((int) auth()->user()->region_id);
        $uReg = $uResolved ? \App\Models\Region::find($uResolved) : null;
        if ($uReg && in_array(strtolower((string) $uReg->type), ['desa', 'kelurahan'], true)) {
            $userVillageId = (int) $uReg->id;
        }
    }

    $villageChoices = \App\Models\Region::whereIn('id', $candidateVillageIds->unique()->values()->all())
        ->whereIn('type', ['desa', 'kelurahan'])
        ->orderBy('name')
        ->get(['id', 'name', 'type']);

    if ($explicitIsVillage) {
        $chatRegionId = (int) $explicitRegion->id;
    } elseif ($villageChoices->count() === 1) {
        $chatRegionId = (int) $villageChoices->first()->id;
    } elseif ($userVillageId && ($villageChoices->isEmpty() || $villageChoices->contains('id', $userVillageId))) {
        $chatRegionId = $userVillageId;
    } elseif ($villageChoices->isNotEmpty()) {
        $chatRegionId = (int) $villageChoices->first()->id;
    } elseif ($userVillageId) {
        $chatRegionId = $userVillageId;
    } elseif ($explicitResolvedId) {
        $chatRegionId = (int) $explicitResolvedId;
    } else {
        $chatRegionId = \App\Models\Region::defaultServiceVillageId();
    }

    $chatRegion = \App\Models\Region::find($chatRegionId);
    $chatRegionName = $chatRegion ? $chatRegion->name : 'Wilayah Layanan';
    $showVillageSelector = !$explicitIsVillage && $villageChoices->count() > 1;

    $quickReplies = [
        'gas' => [
            'Stok tabung ready hari ini?',
            'Bisa diantar ke rumah?',
            'Bagaimana cara tukar tabung?',
        ],
        'penyewaan' => [
            'Apakah alat siap pakai?',
            'Bagaimana perhitungan tarif sewanya?',
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
    <span id="serviceChatUnreadBadge_{{ $serviceType }}" class="service-launcher-badge" style="display: none;">0</span>
</div>

<!-- Chat Widget Box -->
<div id="serviceChatWidget_{{ $serviceType }}" class="service-chat-widget"
     data-service="{{ $serviceType }}"
     data-region-id="{{ $chatRegionId }}"
     data-region-name="{{ $chatRegionName }}"
     data-item-ref="{{ $itemRef }}">
    <!-- Header -->
    <div class="service-chat-header">
        <div class="service-chat-header-left">
            <div class="service-chat-avatar">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <div style="min-width: 0;">
                <div class="service-chat-title">Chat {{ $serviceTitle }}</div>
                <div class="service-chat-status">
                    <span class="status-dot"></span>
                    <span id="serviceChatRegionLabel_{{ $serviceType }}" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Online &bull; Petugas {{ $chatRegionName }}</span>
                </div>
            </div>
        </div>
        <div class="service-chat-header-actions">
            <button type="button" class="service-chat-clear-btn" onclick="clearServiceChat('{{ $serviceType }}')" title="Bersihkan chat di tampilan Anda (tidak menghapus riwayat petugas)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
            <button type="button" id="serviceChatExpandBtn_{{ $serviceType }}" class="service-chat-expand-btn" onclick="toggleServiceChatExpand('{{ $serviceType }}')" title="Perbesar panel obrolan" aria-expanded="false">
                <svg id="serviceChatExpandIcon_{{ $serviceType }}" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"></path></svg>
                <span id="serviceChatExpandLabel_{{ $serviceType }}">Perbesar</span>
            </button>
            <button type="button" class="service-chat-close-btn" onclick="closeServiceChatWidget('{{ $serviceType }}')" title="Tutup Chat">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    @if($showVillageSelector)
    <div class="service-chat-region-bar">
        <label for="serviceChatRegionSelect_{{ $serviceType }}">Desa Tujuan:</label>
        <select id="serviceChatRegionSelect_{{ $serviceType }}" onchange="changeServiceChatRegion('{{ $serviceType }}', this.value)">
            @foreach($villageChoices as $choice)
                <option value="{{ $choice->id }}" {{ (int) $choice->id === (int) $chatRegionId ? 'selected' : '' }}>
                    {{ $choice->name }}
                </option>
            @endforeach
        </select>
    </div>
    @endif

    <!-- Privacy Notice -->
    <div class="service-chat-privacy">
        <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
        <span id="serviceChatNoticeText_{{ $serviceType }}">Pesan Anda langsung masuk ke Petugas {{ $chatRegionName }}</span>
    </div>

    <!-- Quick Reply Chips -->
    <div class="service-chat-quick-replies" id="serviceQuickReplies_{{ $serviceType }}">
        <button type="button" id="serviceEscalateBtn_{{ $serviceType }}" class="service-chip-btn service-chip-primary" onclick="escalateServiceChat('{{ $serviceType }}')">
            Chat Petugas Layanan
        </button>
        @foreach($quickReplies as $chip)
            <button type="button" class="service-chip-btn" onclick="sendServiceQuickReply('{{ $serviceType }}', '{{ addslashes($chip) }}')">
                {{ $chip }}
            </button>
        @endforeach
    </div>

    <!-- Messages Container -->
    <div class="service-chat-body" id="serviceChatMessages_{{ $serviceType }}">
        <div class="text-center py-4 text-slate-400 text-xs">
            Menghubungkan ke layanan...
        </div>
    </div>

    <!-- Error Notice -->
    <div id="serviceChatError_{{ $serviceType }}" class="service-chat-error" style="display: none;"></div>

    <!-- Input Footer -->
    <div class="service-chat-footer">
        <input type="text" id="serviceChatInput_{{ $serviceType }}" class="service-chat-input" placeholder="Tulis pertanyaan..." autocomplete="off" onkeypress="if(event.key==='Enter') sendServiceChatMessage('{{ $serviceType }}')">
        <button type="button" id="serviceChatSendBtn_{{ $serviceType }}" class="service-chat-send-btn" onclick="sendServiceChatMessage('{{ $serviceType }}')" title="Kirim Pesan">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"></path></svg>
        </button>
    </div>
</div>

<style>
    .service-chat-launcher {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9998;
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        color: #ffffff;
        padding: 10px 20px;
        border-radius: 50rem;
        box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.4);
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
        user-select: none;
    }
    .service-chat-launcher:hover {
        transform: translateX(-50%) translateY(-3px) scale(1.02);
        box-shadow: 0 15px 30px -5px rgba(2, 132, 199, 0.5);
    }
    .service-chat-launcher:active {
        transform: translateX(-50%) translateY(0) scale(0.98);
    }
    .service-launcher-badge {
        background: #ef4444;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.45);
    }
    .service-chat-widget {
        position: fixed;
        bottom: 76px;
        left: 50%;
        transform: translateX(-50%);
        width: 340px;
        max-width: calc(100vw - 24px);
        height: 440px;
        max-height: calc(100vh - 104px);
        background: #ffffff;
        border-radius: 1.15rem;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.25);
        z-index: 9999;
        display: none;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        transition: width 0.22s ease, height 0.22s ease, max-height 0.22s ease, border-radius 0.22s ease;
    }
    .service-chat-widget.is-expanded {
        width: min(660px, calc(100vw - 2rem));
        max-width: calc(100vw - 2rem);
        height: min(82vh, 720px);
        max-height: calc(100vh - 96px);
    }
    .service-chat-widget.active {
        display: flex;
        animation: chatSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes chatSlideUp {
        from { opacity: 0; transform: translateX(-50%) translateY(16px) scale(0.96); }
        to { opacity: 1; transform: translateX(-50%) translateY(0) scale(1); }
    }
    @media (max-width: 768px) {
        .service-chat-launcher {
            bottom: 20px;
            left: 14px;
            right: auto;
            transform: none;
            padding: 9px 14px;
            font-size: 0.78rem;
            gap: 6px;
            max-width: calc(100vw - 96px);
            box-shadow: 0 6px 18px -3px rgba(2, 132, 199, 0.45);
        }
        .service-chat-launcher:hover {
            transform: translateY(-2px);
        }
        .service-chat-launcher:active {
            transform: translateY(0) scale(0.98);
        }
        .service-chat-launcher .launcher-icon svg {
            width: 18px;
            height: 18px;
        }
        .service-chat-launcher .launcher-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .service-chat-widget {
            bottom: 72px;
            left: 50%;
            transform: translateX(-50%);
            width: calc(100vw - 20px);
            max-width: 360px;
            height: 420px;
            max-height: calc(100vh - 96px);
            border-radius: 1rem;
        }
        .service-chat-widget.is-expanded {
            bottom: 64px;
            width: calc(100vw - 16px);
            max-width: calc(100vw - 16px);
            height: calc(100vh - 80px);
            max-height: calc(100vh - 80px);
        }
    }
    @media (max-width: 420px) {
        .service-chat-expand-btn span {
            display: none;
        }
        .service-chat-expand-btn {
            padding: 5px 7px;
        }
        .service-chat-title {
            font-size: 0.84rem;
        }
    }
    .service-chat-header {
        background: #ffffff;
        padding: 11px 13px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }
    .service-chat-header-left {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
        flex: 1;
    }
    .service-chat-header-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }
    .service-chat-clear-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        padding: 0;
        cursor: pointer;
        transition: all 0.18s ease;
        flex-shrink: 0;
    }
    .service-chat-clear-btn:hover {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }
    .service-chat-expand-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #475569;
        border-radius: 999px;
        padding: 4px 9px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.18s ease;
        white-space: nowrap;
    }
    .service-chat-expand-btn:hover {
        background: #e0f2fe;
        border-color: #bae6fd;
        color: #0284c7;
    }
    .service-chat-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .service-chat-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }
    .service-chat-status {
        font-size: 11px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 2px;
    }
    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
        flex-shrink: 0;
    }
    .service-chat-close-btn {
        background: transparent;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 5px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .service-chat-close-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .service-chat-region-bar {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 6px 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11.5px;
        color: #334155;
    }
    .service-chat-region-bar label {
        font-weight: 600;
        white-space: nowrap;
        margin: 0;
    }
    .service-chat-region-bar select {
        flex: 1;
        min-width: 0;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 4px 8px;
        font-size: 11.5px;
        color: #0f172a;
        outline: none;
    }
    .service-chat-privacy {
        background: #f0fdf4;
        padding: 5px 12px;
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
        padding: 7px 12px;
        background: #f8fafc;
        overflow-x: auto;
        white-space: nowrap;
        border-bottom: 1px solid #e2e8f0;
        scrollbar-width: none;
    }
    .service-chat-quick-replies::-webkit-scrollbar {
        display: none;
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
    .service-chip-primary {
        background: #0284c7;
        border-color: #0284c7;
        color: #ffffff;
        font-weight: 600;
    }
    .service-chip-primary:hover {
        background: #0369a1;
        border-color: #0369a1;
        color: #ffffff;
    }
    .service-chip-connected {
        background: #dcfce7;
        border-color: #86efac;
        color: #166534;
        font-weight: 600;
        cursor: default;
    }
    .service-chat-body {
        flex: 1;
        padding: 12px;
        overflow-y: auto;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 8px;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    .service-chat-body::-webkit-scrollbar {
        width: 5px;
    }
    .service-chat-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }
    .chat-bubble-warga {
        align-self: flex-end;
        background: #0284c7;
        color: #ffffff;
        padding: 8px 12px;
        border-radius: 14px;
        border-bottom-right-radius: 3px;
        max-width: 82%;
        font-size: 0.84rem;
        line-height: 1.4;
        word-break: break-word;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .chat-bubble-admin {
        align-self: flex-start;
        background: #ffffff;
        color: #0f172a;
        padding: 8px 12px;
        border-radius: 14px;
        border-bottom-left-radius: 3px;
        max-width: 82%;
        font-size: 0.84rem;
        line-height: 1.4;
        word-break: break-word;
        border: 1px solid #bae6fd;
        box-shadow: 0 1px 3px rgba(2,132,199,0.08);
    }
    .chat-msg-text {
        white-space: pre-wrap;
        word-break: break-word;
    }
    .chat-bubble-bot {
        align-self: center;
        background: #e0f2fe;
        color: #0369a1;
        padding: 7px 12px;
        border-radius: 12px;
        font-size: 11px;
        max-width: 92%;
        text-align: center;
        line-height: 1.4;
    }
    .chat-bubble-time {
        font-size: 9.5px;
        margin-top: 3px;
        opacity: 0.78;
        text-align: right;
        line-height: 1;
    }
    .service-chat-error {
        background: #fef2f2;
        color: #b91c1c;
        border-top: 1px solid #fecaca;
        padding: 6px 14px;
        font-size: 11.5px;
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
        color: #0f172a;
        outline: none;
    }
    .service-chat-input:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
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
        flex-shrink: 0;
        transition: background 0.2s;
    }
    .service-chat-send-btn:hover {
        background: #0369a1;
    }
    .service-chat-send-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>

<script>
    if (typeof window.unitUserChatState === 'undefined') {
        window.unitUserChatState = {};
    }

    function getServiceChatConfig(service) {
        const widget = document.getElementById(`serviceChatWidget_${service}`);
        if (!window.unitUserChatState[service]) {
            window.unitUserChatState[service] = {
                pollTimer: null,
                unreadTimer: null,
                lastFingerprint: '',
                isSending: false,
                isEscalated: false,
                regionId: widget ? Number(widget.getAttribute('data-region-id') || 0) : 0,
                regionName: widget ? (widget.getAttribute('data-region-name') || '') : '',
                itemRef: widget ? (widget.getAttribute('data-item-ref') || '') : '',
            };
        }
        return window.unitUserChatState[service];
    }

    function getServiceChatCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    }

    function getServiceChatTokenKey(service, regionId) {
        return `unit_chat_token_${service}_${regionId || 0}`;
    }

    function showServiceChatError(service, msg) {
        const el = document.getElementById(`serviceChatError_${service}`);
        if (!el) return;
        if (!msg) {
            el.style.display = 'none';
            el.textContent = '';
            return;
        }
        el.textContent = msg;
        el.style.display = 'block';
    }

    function updateServiceEscalatedUi(service, isEscalated) {
        const state = getServiceChatConfig(service);
        state.isEscalated = !!isEscalated;
        const btn = document.getElementById(`serviceEscalateBtn_${service}`);
        if (!btn) return;
        if (state.isEscalated) {
            btn.className = 'service-chip-btn service-chip-connected';
            btn.textContent = 'Terhubung ke Petugas';
            btn.disabled = true;
        } else {
            btn.className = 'service-chip-btn service-chip-primary';
            btn.textContent = 'Chat Petugas Layanan';
            btn.disabled = false;
        }
    }

    function changeServiceChatRegion(service, newRegionId) {
        const state = getServiceChatConfig(service);
        const select = document.getElementById(`serviceChatRegionSelect_${service}`);
        const newName = select && select.selectedOptions[0] ? select.selectedOptions[0].textContent.trim() : '';
        state.regionId = Number(newRegionId || 0);
        state.regionName = newName;
        state.lastFingerprint = '';

        const widget = document.getElementById(`serviceChatWidget_${service}`);
        if (widget) {
            widget.setAttribute('data-region-id', String(state.regionId));
            widget.setAttribute('data-region-name', newName);
        }
        const label = document.getElementById(`serviceChatRegionLabel_${service}`);
        if (label && newName) label.textContent = `Online • Petugas ${newName}`;
        const notice = document.getElementById(`serviceChatNoticeText_${service}`);
        if (notice && newName) notice.textContent = `Pesan Anda langsung masuk ke Petugas ${newName}`;

        updateServiceEscalatedUi(service, false);
        loadServiceChatHistory(service, false);
    }

    function toggleServiceChatWidget(service) {
        const widget = document.getElementById(`serviceChatWidget_${service}`);
        if (!widget) return;
        if (widget.classList.contains('active')) {
            closeServiceChatWidget(service);
        } else {
            widget.classList.add('active');
            const badge = document.getElementById(`serviceChatUnreadBadge_${service}`);
            if (badge) badge.style.display = 'none';
            loadServiceChatHistory(service, false);
            startServiceChatPolling(service);
            setTimeout(() => document.getElementById(`serviceChatInput_${service}`)?.focus(), 120);
        }
    }

    function clearServiceChat(service) {
        if (!confirm('Bersihkan riwayat obrolan ini di tampilan Anda?\nRiwayat di sisi petugas layanan tidak ikut terhapus.')) return;
        const state = getServiceChatConfig(service);
        const tokenKey = getServiceChatTokenKey(service, state.regionId);
        const sessionToken = localStorage.getItem(tokenKey) || '';
        showServiceChatError(service, '');

        fetch(`{{ url('unit-chat') }}/${service}/clear`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getServiceChatCsrf(),
                'X-Chat-Session-Token': sessionToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                region_id: state.regionId || null,
                session_token: sessionToken
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(res => {
            if (res.status === 'success') {
                state.lastFingerprint = '';
                loadServiceChatHistory(service, false);
            } else {
                throw new Error(res.message || 'Gagal membersihkan chat');
            }
        })
        .catch(err => {
            console.error('Gagal membersihkan chat:', err);
            showServiceChatError(service, 'Gagal membersihkan obrolan. Silakan coba lagi.');
        });
    }

    function toggleServiceChatExpand(service) {
        const widget = document.getElementById(`serviceChatWidget_${service}`);
        if (!widget) return;
        const expanded = widget.classList.toggle('is-expanded');
        const btn = document.getElementById(`serviceChatExpandBtn_${service}`);
        const label = document.getElementById(`serviceChatExpandLabel_${service}`);
        const icon = document.getElementById(`serviceChatExpandIcon_${service}`);
        if (btn) {
            btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            btn.setAttribute('title', expanded ? 'Kecilkan panel obrolan' : 'Perbesar panel obrolan');
        }
        if (label) {
            label.textContent = expanded ? 'Kecilkan' : 'Perbesar';
        }
        if (icon) {
            icon.innerHTML = expanded
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 14h6v6M20 10h-6V4M14 10l7-7M10 14l-7 7"></path>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"></path>';
        }
    }

    function closeServiceChatWidget(service) {
        const widget = document.getElementById(`serviceChatWidget_${service}`);
        if (widget) widget.classList.remove('active');
        const state = getServiceChatConfig(service);
        if (state.pollTimer) {
            clearInterval(state.pollTimer);
            state.pollTimer = null;
        }
    }

    function formatServiceChatTime(rawDate) {
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

    function loadServiceChatHistory(service, isSilent = false) {
        const state = getServiceChatConfig(service);
        const tokenKey = getServiceChatTokenKey(service, state.regionId);
        const sessionToken = localStorage.getItem(tokenKey) || '';
        const query = state.regionId ? `?region_id=${encodeURIComponent(state.regionId)}` : '';

        return fetch(`{{ url('unit-chat') }}/${service}/history${query}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Chat-Session-Token': sessionToken,
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(res => {
            if (res.status === 'success' && res.data) {
                showServiceChatError(service, '');
                const session = res.data.session || {};
                const returnedToken = res.data.session_token || session.session_token;
                if (returnedToken) {
                    localStorage.setItem(tokenKey, returnedToken);
                }
                if (res.data.region_name) {
                    const label = document.getElementById(`serviceChatRegionLabel_${service}`);
                    if (label) label.textContent = `Online • Petugas ${res.data.region_name}`;
                }

                updateServiceEscalatedUi(service, !!res.data.is_escalated || session.status === 'escalated');

                const msgs = Array.isArray(res.data.messages) ? res.data.messages : [];
                const fingerprint = msgs.map(m => `${m.id}:${m.sender_type}`).join('|');

                if (isSilent && fingerprint === state.lastFingerprint) {
                    return;
                }
                state.lastFingerprint = fingerprint;

                const container = document.getElementById(`serviceChatMessages_${service}`);
                if (!container) return;

                const isNearBottom = (container.scrollHeight - container.scrollTop - container.clientHeight) < 80;
                container.innerHTML = '';

                msgs.forEach(msg => {
                    renderServiceMessageBubble(service, msg);
                });

                if (!isSilent || isNearBottom) {
                    container.scrollTop = container.scrollHeight;
                }
            }
        })
        .catch(err => {
            console.error('Gagal memuat riwayat chat:', err);
            if (!isSilent) {
                showServiceChatError(service, 'Gagal memuat obrolan. Periksa koneksi internet lalu coba lagi.');
            }
        });
    }

    function renderServiceMessageBubble(service, msg) {
        const container = document.getElementById(`serviceChatMessages_${service}`);
        if (!container || !msg) return;

        const bubble = document.createElement('div');
        const time = formatServiceChatTime(msg.created_at);

        if (msg.sender_type === 'user') {
            bubble.className = 'chat-bubble-warga';
            bubble.innerHTML = `<div class="chat-msg-text">${escapeHtmlService(msg.message)}</div>${time ? `<div class="chat-bubble-time">${time}</div>` : ''}`;
        } else if (msg.sender_type === 'admin') {
            bubble.className = 'chat-bubble-admin';
            const adminLabel = msg.sender && msg.sender.name ? `Petugas • ${msg.sender.name}` : 'Petugas Layanan';
            bubble.innerHTML = `<div style="font-size: 10.5px; font-weight: 700; color: #0284c7; margin-bottom: 2px;">${escapeHtmlService(adminLabel)}</div><div class="chat-msg-text">${escapeHtmlService(msg.message)}</div>${time ? `<div class="chat-bubble-time" style="color: #64748b;">${time}</div>` : ''}`;
        } else {
            bubble.className = 'chat-bubble-bot';
            bubble.innerHTML = `<div class="chat-msg-text">${escapeHtmlService(msg.message)}</div>`;
        }

        container.appendChild(bubble);
    }

    function sendServiceQuickReply(service, text) {
        const input = document.getElementById(`serviceChatInput_${service}`);
        if (input) {
            input.value = text;
            sendServiceChatMessage(service);
        }
    }

    function sendServiceChatMessage(service) {
        const state = getServiceChatConfig(service);
        if (state.isSending) return;

        const input = document.getElementById(`serviceChatInput_${service}`);
        const sendBtn = document.getElementById(`serviceChatSendBtn_${service}`);
        const text = (input?.value || '').trim();
        if (!text) return;

        state.isSending = true;
        if (sendBtn) sendBtn.disabled = true;
        input.value = '';
        showServiceChatError(service, '');

        const container = document.getElementById(`serviceChatMessages_${service}`);
        renderServiceMessageBubble(service, {
            sender_type: 'user',
            message: text,
            created_at: new Date().toISOString()
        });
        if (container) container.scrollTop = container.scrollHeight;

        const tokenKey = getServiceChatTokenKey(service, state.regionId);
        const sessionToken = localStorage.getItem(tokenKey) || '';

        fetch(`{{ url('unit-chat') }}/${service}/send`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getServiceChatCsrf(),
                'X-Chat-Session-Token': sessionToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                message: text,
                region_id: state.regionId || null,
                session_token: sessionToken,
                item_reference: state.itemRef || null
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(res => {
            if (res.status === 'success' && res.data) {
                if (res.data.session_token) {
                    localStorage.setItem(tokenKey, res.data.session_token);
                }
                if (res.data.is_escalated) {
                    updateServiceEscalatedUi(service, true);
                }
                if (res.data.bot_message) {
                    renderServiceMessageBubble(service, res.data.bot_message);
                    if (container) container.scrollTop = container.scrollHeight;
                }
                state.lastFingerprint = '';
            } else {
                throw new Error(res.message || 'Gagal mengirim pesan');
            }
        })
        .catch(err => {
            console.error('Gagal mengirim pesan:', err);
            showServiceChatError(service, 'Pesan gagal dikirim. Silakan coba kirim ulang.');
            if (input && !input.value) input.value = text;
        })
        .finally(() => {
            state.isSending = false;
            if (sendBtn) sendBtn.disabled = false;
            input?.focus();
        });
    }

    function escalateServiceChat(service) {
        const state = getServiceChatConfig(service);
        const btn = document.getElementById(`serviceEscalateBtn_${service}`);
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Menghubungkan...';
        }
        showServiceChatError(service, '');

        const tokenKey = getServiceChatTokenKey(service, state.regionId);
        const sessionToken = localStorage.getItem(tokenKey) || '';

        fetch(`{{ url('unit-chat') }}/${service}/escalate`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getServiceChatCsrf(),
                'X-Chat-Session-Token': sessionToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                region_id: state.regionId || null,
                session_token: sessionToken
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(res => {
            if (res.status === 'success' && res.data) {
                const returnedToken = res.data.session_token || res.data.session?.session_token;
                if (returnedToken) {
                    localStorage.setItem(tokenKey, returnedToken);
                }
                updateServiceEscalatedUi(service, true);
                if (res.data.bot_message) {
                    renderServiceMessageBubble(service, res.data.bot_message);
                    const container = document.getElementById(`serviceChatMessages_${service}`);
                    if (container) container.scrollTop = container.scrollHeight;
                }
                state.lastFingerprint = '';
            } else {
                throw new Error(res.message || 'Gagal menghubungkan ke petugas');
            }
        })
        .catch(err => {
            console.error('Gagal eskalasi chat:', err);
            updateServiceEscalatedUi(service, false);
            showServiceChatError(service, 'Gagal menghubungkan ke petugas. Silakan coba lagi.');
        });
    }

    function startServiceChatPolling(service) {
        const state = getServiceChatConfig(service);
        if (state.pollTimer) {
            clearInterval(state.pollTimer);
        }
        state.pollTimer = setInterval(() => {
            if (document.hidden) return;
            const widget = document.getElementById(`serviceChatWidget_${service}`);
            if (widget && widget.classList.contains('active')) {
                loadServiceChatHistory(service, true);
            }
        }, 4000);
    }

    function checkServiceChatUnread(service) {
        const widget = document.getElementById(`serviceChatWidget_${service}`);
        if (!widget || widget.classList.contains('active')) return;
        const state = getServiceChatConfig(service);
        const tokenKey = getServiceChatTokenKey(service, state.regionId);
        const sessionToken = localStorage.getItem(tokenKey) || '';
        const query = state.regionId ? `?region_id=${encodeURIComponent(state.regionId)}` : '';

        fetch(`{{ url('unit-chat/unread-counts') }}${query}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Chat-Session-Token': sessionToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.ok ? res.json() : null)
        .then(res => {
            if (!res || res.status !== 'success' || !res.data) return;
            const count = Number(res.data[service] || 0);
            const badge = document.getElementById(`serviceChatUnreadBadge_${service}`);
            if (!badge) return;
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : String(count);
                badge.style.display = 'inline-flex';
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(() => {});
    }

    function escapeHtmlService(string) {
        if (!string) return '';
        return String(string).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    (function initServiceChatUnread_{{ $serviceType }}() {
        const svc = '{{ $serviceType }}';
        const state = getServiceChatConfig(svc);
        checkServiceChatUnread(svc);
        if (state.unreadTimer) clearInterval(state.unreadTimer);
        state.unreadTimer = setInterval(() => {
            if (!document.hidden) checkServiceChatUnread(svc);
        }, 15000);
    })();
</script>
