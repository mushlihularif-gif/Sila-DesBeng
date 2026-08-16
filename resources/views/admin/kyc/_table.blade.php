<div class="table-responsive text-nowrap">
    <table class="table table-hover table-modern align-middle w-100">
        <thead class="text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">
            <tr>
                <th>KTP</th>
                <th>PENGGUNA</th>
                <th>PENGAJUAN</th>
                <th>PERBANDINGAN NIK</th>
                <th>STATUS</th>
                <th class="text-end">AKSI</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse ($data as $kyc)
            <tr>
                <td>
                    @if($kyc->ktp_image_path)
                        <div style="width: 60px; height: 40px; overflow: hidden; border-radius: 6px; border: 1px solid #d9dee3; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <img src="{{ route('media.secure.ktp', basename($kyc->ktp_image_path)) }}" alt="KTP" style="width: 100%; height: 100%; object-fit: cover; filter: blur(2px);" title="Dokumen dilindungi">
                        </div>
                    @else
                        <div style="width: 60px; height: 40px; background: #f8f9fa; border-radius: 6px; display:flex; align-items:center; justify-content:center; color:#a1acb8;">
                            <i class="bx bx-image-alt"></i>
                        </div>
                    @endif
                </td>
                <td>
                    <div class="d-flex justify-content-start align-items-center">
                        <div class="avatar-wrapper me-3">
                            <div class="avatar avatar-md" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background: #e7e7ff; display:flex; align-items:center; justify-content:center; font-weight: bold; color: #696cff;">
                                @if($kyc->user->avatar)
                                    <img src="{{ route('media.avatar', ['filename' => basename($kyc->user->avatar)]) }}" alt="Avatar" class="w-100 h-100" style="object-fit: cover;">
                                @elseif($kyc->user->file)
                                    <img src="{{ route('media.avatar', ['filename' => basename($kyc->user->file->path)]) }}" alt="Avatar" class="w-100 h-100" style="object-fit: cover;">
                                @else
                                    @php
                                        $initials = collect(explode(' ', $kyc->user->name))->map(function($segment) { return strtoupper(substr($segment, 0, 1)); })->take(2)->join('');
                                    @endphp
                                    {{ $initials }}
                                @endif
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark">{{ $kyc->user->name }}</span>
                            <small class="text-muted">{{ $kyc->user->email ?? $kyc->user->phone }}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="fw-semibold text-dark">{{ $kyc->created_at->format('d M Y') }}</div>
                    <small class="text-muted"><i class="bx bx-time-five"></i> {{ $kyc->created_at->format('H:i') }} WIB</small>
                </td>
                <td>
                    @php
                        $censoredOcr = $kyc->nik_from_ocr ? substr($kyc->nik_from_ocr, 0, 4) . str_repeat('*', max(0, strlen($kyc->nik_from_ocr) - 8)) . substr($kyc->nik_from_ocr, -4) : '-';
                        $censoredAsli = $kyc->user->nik ? substr($kyc->user->nik, 0, 4) . str_repeat('*', max(0, strlen($kyc->user->nik) - 8)) . substr($kyc->user->nik, -4) : 'Belum diset';
                    @endphp
                    <div class="fw-bold text-primary">{{ $censoredOcr }} <small class="text-muted fw-normal">(Di Foto KTP)</small></div>
                    <small class="text-muted">{{ $censoredAsli }} <span class="fw-normal">(Data Akun)</span></small>
                </td>
                <td>
                    @if($kyc->status === 'pending')
                        <span class="badge bg-label-warning px-3 py-2 rounded-pill"><i class="bx bx-time me-1"></i> Menunggu</span>
                    @elseif($kyc->status === 'approved')
                        <span class="badge bg-label-success px-3 py-2 rounded-pill"><i class="bx bx-check-shield me-1"></i> Disetujui</span>
                    @else
                        <span class="badge bg-label-danger px-3 py-2 rounded-pill"><i class="bx bx-x-circle me-1"></i> Ditolak</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('admin.kyc.show', $kyc->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" title="Tinjau Berkas">
                        <i class="bx bx-show me-1"></i> Tinjau
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5 bg-transparent border-0">
                    <div class="bg-white p-4 rounded-circle d-inline-block shadow-sm mb-3 border">
                        <i class="bx bx-folder-open fs-1 text-muted"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Belum Ada Pengajuan</h6>
                    <p class="text-muted mb-0">Tidak ada data verifikasi untuk kategori ini.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
