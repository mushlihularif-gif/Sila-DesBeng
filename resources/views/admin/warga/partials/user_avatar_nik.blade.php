@php
    $uName = $user->name ?? ($fallbackName ?? 'Warga');
    $uNik = trim($user->nik ?? '');
    $maskedNik = '-';
    if ($uNik !== '') {
        $len = strlen($uNik);
        if ($len >= 8) {
            $maskedNik = substr($uNik, 0, 4) . str_repeat('*', max(4, $len - 8)) . substr($uNik, -4);
        } elseif ($len > 4) {
            $maskedNik = substr($uNik, 0, 2) . str_repeat('*', $len - 4) . substr($uNik, -2);
        } else {
            $maskedNik = $uNik;
        }
    }

    $avatarUrl = null;
    if ($user) {
        if (!empty($user->avatar)) {
            $avatarUrl = str_starts_with($user->avatar, 'http')
                ? $user->avatar
                : route('media.avatar', ['filename' => basename($user->avatar)]);
        } elseif ($user->file && !empty($user->file->filename)) {
            $avatarUrl = route('media.profile', ['filename' => $user->file->filename]);
        }
    }

    $initials = 'W';
    if (!empty($uName)) {
        $words = explode(' ', trim($uName));
        $initials = strtoupper(substr($words[0], 0, 1));
        if (isset($words[1]) && !empty($words[1])) {
            $initials .= strtoupper(substr($words[1], 0, 1));
        }
    }
@endphp

<div class="d-flex align-items-center gap-2">
    <div class="avatar flex-shrink-0" style="width: 38px; height: 38px;">
        @if($avatarUrl)
            <img src="{{ $avatarUrl }}" alt="{{ $uName }}" class="rounded-circle shadow-xs" style="width: 38px; height: 38px; object-fit: cover;" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'avatar-initial rounded-circle bg-label-primary shadow-xs fw-bold\' style=\'width:38px;height:38px;font-size:13px;display:inline-flex;align-items:center;justify-content:center;\'>{{ $initials }}</span>';">
        @else
            <span class="avatar-initial rounded-circle bg-label-primary shadow-xs fw-bold notranslate" translate="no" style="width: 38px; height: 38px; font-size: 13px; display: inline-flex; align-items: center; justify-content: center;">
                {{ $initials }}
            </span>
        @endif
    </div>
    <div class="min-w-0">
        <span class="fw-bold text-dark text-truncate d-block" style="max-width: {{ ($isMobile ?? false) ? '165px' : '200px' }};" title="{{ $uName }}">
            {{ $uName }}
        </span>
        <div class="d-flex align-items-center gap-1 mt-0.5" style="font-size: 0.78rem;">
            <span class="text-muted">NIK:</span>
            @if($uNik !== '')
                <span class="nik-display font-monospace text-secondary fw-semibold" data-full="{{ $uNik }}" data-masked="{{ $maskedNik }}">{{ $maskedNik }}</span>
                <button type="button" class="btn btn-icon btn-xs text-muted p-0 border-0 bg-transparent btn-toggle-nik" title="Tampilkan NIK lengkap" style="width: 18px; height: 18px; line-height: 1;">
                    <i class="bx bx-show fs-6"></i>
                </button>
                <button type="button" class="btn btn-icon btn-xs text-muted p-0 border-0 bg-transparent btn-copy-nik" data-nik="{{ $uNik }}" title="Salin NIK" style="width: 18px; height: 18px; line-height: 1;">
                    <i class="bx bx-copy fs-6"></i>
                </button>
            @else
                <span class="text-muted italic">-</span>
            @endif
        </div>
    </div>
</div>
