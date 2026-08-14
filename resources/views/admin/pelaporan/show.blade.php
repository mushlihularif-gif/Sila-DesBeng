@extends('admin.layouts.admin')
@section('title', 'Detail Laporan')

@section('content')
<style>
    .animate-fade-up {
        animation: fadeUp 0.5s ease-out forwards;
    }
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .timeline {
        position: relative;
        padding-left: 1.5rem;
        list-style: none;
    }
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 5px;
        width: 2px;
        background: #e9ecef;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-icon {
        position: absolute;
        left: -1.5rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #696cff;
        top: 4px;
    }
    .timeline-icon.bg-warning { border-color: #ffab00; }
    .timeline-icon.bg-info { border-color: #03c3ec; }
    .timeline-icon.bg-primary { border-color: #696cff; }
    .timeline-icon.bg-success { border-color: #71dd37; }
    .timeline-icon.bg-danger { border-color: #ff3e1d; }
    .timeline-icon.bg-secondary { border-color: #8592a3; }
    
    .cursor-pointer {
        cursor: pointer;
    }
    .img-hover-scale {
        transition: transform 0.2s ease-in-out;
    }
    .img-hover-scale:hover {
        transform: scale(1.05);
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y animate-fade-up">
    <!-- Breadcrumb -->
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Sistem / <a href="{{ route('admin.pelaporan.index') }}" class="text-muted">Pelaporan Warga</a> /</span> Detail Laporan #{{ $laporan->id }}
    </h4>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.pelaporan.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-md-8">
            <!-- Info Laporan -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header d-flex justify-content-between align-items-center pb-0">
                    <h5 class="card-title mb-0"><i class="bx bx-info-circle text-primary me-2"></i> Informasi Laporan</h5>
                    <div>
                        @if($laporan->isOverdue())
                            <span class="badge bg-danger me-2">SLA Terlewat</span>
                        @endif
                        <span class="badge bg-label-secondary me-2">Tingkat: {{ ucfirst($laporan->escalation_level) }}</span>
                        
                        @php
                            $statusColors = [
                                'Pending' => 'warning',
                                'Proses' => 'info',
                                'Dilanjutkan' => 'primary',
                                'Selesai' => 'success',
                                'Ditolak' => 'danger',
                            ];
                            $color = $statusColors[$laporan->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-label-{{ $color }}">{{ $laporan->status }}</span>
                    </div>
                </div>
                <div class="card-body pt-4">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Kategori</div>
                        <div class="col-sm-8 fw-semibold">{{ $laporan->kategori }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Lokasi</div>
                        <div class="col-sm-8 fw-semibold">{{ $laporan->lokasi ?? 'Tidak ada lokasi' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Deskripsi</div>
                        <div class="col-sm-8 text-break">{{ $laporan->deskripsi }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block mb-1">Dilaporkan Oleh</small>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-primary"><i class="bx bx-user"></i></span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $laporan->user->name ?? 'Anonim' }}</h6>
                                    <small class="text-muted">{{ $laporan->region->name ?? '-' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block mb-1">Tanggal Pelaporan</small>
                            <h6 class="mb-0">{{ $laporan->created_at->format('d M Y, H:i') }}</h6>
                            <small class="text-muted">{{ $laporan->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bukti Foto -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header pb-0">
                    <h5 class="card-title mb-0"><i class="bx bx-camera text-primary me-2"></i> Bukti Foto</h5>
                </div>
                <div class="card-body pt-3">
                    @if(count($laporan->bukti_array) > 0)
                        <div class="row g-3">
                            @foreach($laporan->bukti_array as $index => $bukti)
                                <div class="col-4">
                                    <img src="{{ asset('storage/' . $bukti) }}" 
                                         alt="Bukti {{ $index + 1 }}" 
                                         class="img-fluid rounded cursor-pointer img-hover-scale"
                                         data-bs-toggle="modal" 
                                         data-bs-target="#imageModal"
                                         onclick="showImage('{{ asset('storage/' . $bukti) }}')"
                                         style="object-fit: cover; height: 120px; width: 100%;">
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-secondary mb-0 text-center">
                            Tidak ada bukti foto.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Peta Lokasi -->
            @if($laporan->latitude && $laporan->longitude)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header pb-0">
                    <h5 class="card-title mb-0"><i class="bx bx-map text-primary me-2"></i> Peta Lokasi</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="rounded overflow-hidden" style="height: 300px;">
                        <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
                            src="https://www.openstreetmap.org/export/embed.html?bbox={{ $laporan->longitude - 0.005 }},{{ $laporan->latitude - 0.005 }},{{ $laporan->longitude + 0.005 }},{{ $laporan->latitude + 0.005 }}&layer=mapnik&marker={{ $laporan->latitude }},{{ $laporan->longitude }}" 
                            style="border: 1px solid black">
                        </iframe>
                    </div>
                    <div class="mt-2 text-end">
                        <small>Lat: {{ $laporan->latitude }}, Lng: {{ $laporan->longitude }}</small>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-md-4">
            
            <!-- Aksi Admin -->
            @if(!in_array($laporan->status, ['Selesai', 'Ditolak']))
            <div class="card border-0 shadow-sm rounded-4 mb-4 border-top border-3 border-primary">
                <div class="card-header pb-0">
                    <h5 class="card-title mb-0"><i class="bx bx-cog text-primary me-2"></i> Aksi</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="d-grid gap-2">
                        <!-- Tanggapi -->
                        <button class="btn btn-label-info text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTanggapi" aria-expanded="false" aria-controls="collapseTanggapi">
                            <span><i class="bx bx-message-dots me-2"></i> Tanggapi</span>
                            <i class="bx bx-chevron-down"></i>
                        </button>
                        <div class="collapse" id="collapseTanggapi">
                            <form action="{{ route('admin.pelaporan.respond', $laporan->id) }}" method="POST" class="mt-2 p-3 bg-lighter rounded">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Catatan Tanggapan</label>
                                    <textarea name="catatan" class="form-control" rows="3" required placeholder="Masukkan tanggapan Anda..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-info w-100">Kirim Tanggapan</button>
                            </form>
                        </div>

                        <!-- Eskalasi -->
                        @if($laporan->canBeEscalated())
                        <button class="btn btn-label-warning text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEskalasi" aria-expanded="false" aria-controls="collapseEskalasi">
                            <span><i class="bx bx-up-arrow-alt me-2"></i> Eskalasi</span>
                            <i class="bx bx-chevron-down"></i>
                        </button>
                        <div class="collapse" id="collapseEskalasi">
                            <form action="{{ route('admin.pelaporan.escalate', $laporan->id) }}" method="POST" class="mt-2 p-3 bg-lighter rounded">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Alasan Eskalasi</label>
                                    <textarea name="catatan" class="form-control" rows="3" required placeholder="Mengapa laporan ini dieskalasi?"></textarea>
                                </div>
                                <button type="submit" class="btn btn-warning w-100">Proses Eskalasi</button>
                            </form>
                        </div>
                        @endif

                        <!-- Selesaikan -->
                        <button class="btn btn-label-success text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSelesaikan" aria-expanded="false" aria-controls="collapseSelesaikan">
                            <span><i class="bx bx-check-circle me-2"></i> Selesaikan</span>
                            <i class="bx bx-chevron-down"></i>
                        </button>
                        <div class="collapse" id="collapseSelesaikan">
                            <form action="{{ route('admin.pelaporan.resolve', $laporan->id) }}" method="POST" class="mt-2 p-3 bg-lighter rounded">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Catatan Penyelesaian (Opsional)</label>
                                    <textarea name="catatan" class="form-control" rows="3" placeholder="Tindakan yang telah dilakukan..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">Selesaikan Laporan</button>
                            </form>
                        </div>

                        <!-- Tolak -->
                        <button class="btn btn-label-danger text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTolak" aria-expanded="false" aria-controls="collapseTolak">
                            <span><i class="bx bx-x-circle me-2"></i> Tolak</span>
                            <i class="bx bx-chevron-down"></i>
                        </button>
                        <div class="collapse" id="collapseTolak">
                            <form action="{{ route('admin.pelaporan.reject', $laporan->id) }}" method="POST" class="mt-2 p-3 bg-lighter rounded">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                    <textarea name="catatan" class="form-control" rows="3" required placeholder="Mengapa laporan ini ditolak?"></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger w-100">Tolak Laporan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Timeline Eskalasi -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header pb-0">
                    <h5 class="card-title mb-0"><i class="bx bx-time text-primary me-2"></i> Timeline Laporan</h5>
                </div>
                <div class="card-body pt-4">
                    @if(count($timeline) > 0)
                        <ul class="timeline mb-0">
                            @foreach($timeline as $item)
                            <li class="timeline-item pb-3">
                                <span class="timeline-icon bg-{{ $item['color'] ?? 'primary' }}"></span>
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 d-flex align-items-center">
                                            @if(isset($item['icon']))
                                                <i class="bx {{ $item['icon'] }} text-{{ $item['color'] ?? 'primary' }} me-2"></i>
                                            @endif
                                            {{ $item['title'] }}
                                        </h6>
                                        @if(isset($item['time']) && $item['time'])
                                            <small class="text-muted">{{ $item['time']->format('d M, H:i') }}</small>
                                        @endif
                                    </div>
                                    <p class="mb-1 text-muted small">{{ $item['desc'] }}</p>
                                    @if(isset($item['notes']) && $item['notes'])
                                        <div class="bg-lighter p-2 rounded small mt-1 border-start border-3 border-{{ $item['color'] ?? 'primary' }}">
                                            <em>"{{ $item['notes'] }}"</em>
                                        </div>
                                    @endif
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">Belum ada riwayat aktivitas.</p>
                    @endif
                </div>
            </div>

            <!-- Rating Warga -->
            @if($laporan->rating)
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-label-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bx bx-star text-warning me-2"></i> Penilaian Warga</h5>
                    <div class="d-flex align-items-center mb-2">
                        <div class="text-warning fs-4 me-2">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $laporan->rating->rating)
                                    <i class='bx bxs-star'></i>
                                @else
                                    <i class='bx bx-star'></i>
                                @endif
                            @endfor
                        </div>
                        <span class="fw-bold">{{ $laporan->rating->rating }} / 5</span>
                    </div>
                    @if($laporan->rating->feedback)
                        <div class="bg-white p-3 rounded mt-3 shadow-sm border">
                            <p class="mb-0 fst-italic">"{{ $laporan->rating->feedback }}"</p>
                        </div>
                    @else
                        <p class="text-muted mb-0 mt-2 small">Warga tidak memberikan ulasan tambahan.</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Bukti Foto -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent shadow-none">
            <div class="modal-header border-0 d-flex justify-content-end pb-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <img id="modalImage" src="" class="img-fluid rounded shadow-lg" alt="Bukti Foto Besar">
            </div>
        </div>
    </div>
</div>

<script>
    function showImage(src) {
        document.getElementById('modalImage').src = src;
    }
</script>
@endsection
