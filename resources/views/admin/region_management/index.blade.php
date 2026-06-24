@extends('admin.layouts.admin')

@section('title', 'Kelola Hierarki Wilayah')

@section('styles')
<style>
    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #696cff;
        font-weight: 600;
        box-shadow: none;
    }
    .accordion-button {
        font-weight: 600;
    }
    .region-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        transition: all 0.2s ease-in-out;
    }
    .region-card:hover {
        border-color: #696cff;
        box-shadow: 0 0.125rem 0.25rem rgba(105, 108, 255, 0.4);
    }
    .admin-info-box {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        border-left: 4px solid #696cff;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
                        <div class="mb-3 mb-md-0">
                            @if(in_array(auth()->user()->role, ['admin_rw', 'admin_rt']) || $targetType == 'rt')
                                <a href="{{ route('admin.kelola-wilayah.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
                                    <i class="bx bx-arrow-back me-1"></i> Kembali
                                </a>
                            @endif
                            <h4 class="card-title text-primary fw-bold mb-1">Manajemen Wilayah: {{ $parentRegion->name }}</h4>
                            <p class="card-text text-muted mb-0">Kelola hierarki struktur tingkat bawah (RW/RT) beserta akun kepengurusannya secara terpusat.</p>
                        </div>
                        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addRegionModal">
                            <i class="bx bx-plus me-1"></i> Tambah {{ strtoupper($targetType) }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Menampilkan RW sebagai Accordion -->
            @if(count($childrenRegions) > 0)
                <div class="accordion mt-3" id="regionAccordion">
                    @foreach($childrenRegions as $index => $child)
                        <div class="accordion-item card mb-3 shadow-sm border-0">
                            <h2 class="accordion-header" id="heading{{ $child->id }}">
                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $child->id }}" aria-expanded="false" aria-controls="collapse{{ $child->id }}">
                                    <div class="d-flex align-items-center w-100 pe-3">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded-circle bg-label-primary"><i class="bx bx-map"></i></span>
                                        </div>
                                        <div class="me-auto">
                                            <h6 class="mb-0 text-dark">{{ $child->name }}</h6>
                                            <small class="text-muted text-uppercase">{{ $child->type }}</small>
                                        </div>
                                        <div class="d-flex gap-2">
                                            @if($child->type == 'rw')
                                                <span class="badge bg-label-info px-3 py-2 rounded-pill d-inline-flex align-items-center justify-content-center">{{ $child->children->count() }} RT Terdaftar</span>
                                            @endif
                                            @if($child->users->count() > 0)
                                                <span class="badge bg-label-success px-3 py-2 rounded-pill d-inline-flex align-items-center justify-content-center"><i class='bx bx-check-circle me-1'></i> Akun Aktif</span>
                                            @else
                                                <span class="badge bg-label-warning px-3 py-2 rounded-pill d-inline-flex align-items-center justify-content-center"><i class='bx bx-error-circle me-1'></i> Belum Ada Akun</span>
                                            @endif
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $child->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $child->id }}" data-bs-parent="#regionAccordion">
                                <div class="accordion-body border-top p-4">
                                    
                                    <div class="row">
                                        <!-- Kolom Kiri: Detail Pengurus & Aksi Wilayah -->
                                        <div class="col-md-5 mb-4 mb-md-0">
                                            <h6 class="fw-semibold mb-3">Informasi Kepengurusan {{ strtoupper($child->type) }}</h6>
                                            
                                            <div class="admin-info-box mb-3">
                                                @if($child->users->count() > 0)
                                                    @foreach($child->users as $admin)
                                                        <div class="d-flex align-items-start mb-3">
                                                            <div class="avatar avatar-md me-3 shadow-sm rounded-circle overflow-hidden" style="width: 50px; height: 50px;">
                                                                @if ($admin->file)
                                                                    <img src="{{ $admin->file->file_stream }}" alt="Avatar" class="w-100 h-100" style="object-fit: cover;">
                                                                @else
                                                                    <span class="avatar-initial rounded-circle bg-primary text-white fs-4"><i class="bx bx-user"></i></span>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0">{{ $admin->name }}</h6>
                                                                <small class="text-muted d-block">{{ $admin->email }}</small>
                                                                <span class="badge bg-label-success px-3 py-2 mt-2 rounded-pill"><i class='bx bx-shield-quarter me-1'></i>{{ strtoupper(str_replace('_', ' ', $admin->role)) }}</span>
                                                            </div>
                                                        </div>
                                                        <form action="{{ route('admin.kelola-wilayah.destroy-admin', $admin->id) }}" method="POST" class="mt-3" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pengurus ini secara permanen? Wilayah tetap dipertahankan.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill w-100">
                                                                <i class="bx bx-user-x me-1"></i> Hapus Akun
                                                            </button>
                                                        </form>
                                                    @endforeach
                                                @else
                                                    <div class="text-center py-3">
                                                        <i class='bx bx-user-circle text-muted fs-1 mb-2'></i>
                                                        <p class="text-muted mb-2">Belum ada akun pengurus untuk wilayah ini.</p>
                                                        <button type="button" class="btn btn-sm btn-primary w-100" data-bs-toggle="modal" data-bs-target="#generateAdminModal{{ $child->id }}">
                                                            <i class="bx bx-user-plus me-1"></i> Buat Akun Pengurus
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>

                                            <h6 class="fw-semibold mb-3 mt-4">Aksi Wilayah</h6>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill flex-grow-1" data-bs-toggle="modal" data-bs-target="#editRegionModal{{ $child->id }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit Nama
                                                </button>
                                                @if($child->users->count() == 0 && $child->children()->count() == 0)
                                                    <form action="{{ route('admin.kelola-wilayah.destroy', $child->id) }}" method="POST" class="d-inline flex-grow-1" onsubmit="return confirm('Apakah Anda yakin ingin menghapus struktur wilayah ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill w-100">
                                                            <i class="bx bx-trash me-1"></i> Hapus Wilayah
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Kolom Kanan: Daftar Sub-Wilayah (Hanya untuk RW) -->
                                        @if($child->type == 'rw')
                                        <div class="col-md-7 border-start">
                                            <div class="d-flex align-items-center justify-content-between mb-3 ps-md-3">
                                                <h6 class="fw-semibold mb-0">Daftar Sub-Wilayah (RT)</h6>
                                                <button type="button" class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#addRTModal{{ $child->id }}">
                                                    <i class="bx bx-plus me-1"></i> Tambah RT
                                                </button>
                                            </div>

                                            <div class="ps-md-3">
                                                @if($child->children->count() > 0)
                                                    <div class="list-group list-group-flush">
                                                        @foreach($child->children as $rt)
                                                            <div class="list-group-item d-flex justify-content-between align-items-center p-3 region-card mb-2 rounded-3">
                                                                <div class="d-flex align-items-center">
                                                                    <!-- Avatar RT -->
                                                                    <div class="avatar avatar-sm me-3 shadow-sm rounded-circle overflow-hidden">
                                                                        @if ($rt->users->count() > 0 && $rt->users->first()->file)
                                                                            <img src="{{ $rt->users->first()->file->file_stream }}" alt="Avatar" class="w-100 h-100" style="object-fit: cover;">
                                                                        @else
                                                                            <span class="avatar-initial rounded-circle bg-label-secondary text-primary"><i class="bx bx-user"></i></span>
                                                                        @endif
                                                                    </div>
                                                                    <div>
                                                                        <h6 class="mb-0 text-dark">{{ $rt->name }}</h6>
                                                                        @if($rt->users->count() > 0)
                                                                            <small class="text-success"><i class='bx bx-check-circle'></i> Ada Pengurus ({{ $rt->users->first()->name }})</small>
                                                                        @else
                                                                            <small class="text-warning"><i class='bx bx-error-circle'></i> Belum Ada Akun</small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex gap-2">
                                                                    @if($rt->users->count() == 0)
                                                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-circle px-2" data-bs-toggle="modal" data-bs-target="#generateAdminModal{{ $rt->id }}" title="Buat Akun RT">
                                                                            <i class="bx bx-user-plus"></i>
                                                                        </button>
                                                                    @else
                                                                        <form action="{{ route('admin.kelola-wilayah.destroy-admin', $rt->users->first()->id) }}" method="POST" onsubmit="return confirm('Yakin hapus akun RT ini?');">
                                                                            @csrf @method('DELETE')
                                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle px-2" title="Hapus Akun RT"><i class="bx bx-user-x"></i></button>
                                                                        </form>
                                                                    @endif
                                                                    
                                                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle px-2" data-bs-toggle="modal" data-bs-target="#editRegionModal{{ $rt->id }}" title="Edit Nama RT">
                                                                        <i class="bx bx-edit-alt"></i>
                                                                    </button>

                                                                    @if($rt->users->count() == 0)
                                                                    <form action="{{ route('admin.kelola-wilayah.destroy', $rt->id) }}" method="POST" onsubmit="return confirm('Hapus struktur RT ini?');">
                                                                        @csrf @method('DELETE')
                                                                        <button type="submit" class="btn btn-sm btn-danger rounded-circle px-2" title="Hapus Wilayah RT"><i class="bx bx-trash"></i></button>
                                                                    </form>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Include Modals for RT -->
                                                            @include('admin.region_management.partials.modals', ['region' => $rt])
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-center py-4 bg-light rounded-3">
                                                        <i class="bx bx-map-pin text-muted fs-2 mb-2"></i>
                                                        <p class="text-muted mb-0">Belum ada RT di dalam RW ini.</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    
                                </div>
                            </div>
                        </div>

                        <!-- Include Modals for RW -->
                        @include('admin.region_management.partials.modals', ['region' => $child])
                        
                        <!-- Modal Add RT khusus di bawah RW ini -->
                        @if($child->type == 'rw')
                        <div class="modal fade" id="addRTModal{{ $child->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content rounded-4 border-0 shadow-lg">
                                    <form action="{{ route('admin.kelola-wilayah.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="type" value="rt">
                                        <input type="hidden" name="parent_id" value="{{ $child->id }}">
                                        
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title fw-bold text-primary"><i class="bx bx-map-pin me-2"></i> Tambah RT di {{ $child->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="bg-label-primary p-3 rounded-3 mb-4">
                                                <label class="form-label fw-semibold text-primary">Nama RT <span class="text-danger">*</span></label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="bx bx-home-circle"></i></span>
                                                    <input type="text" name="name" class="form-control" placeholder="Contoh: RT 01" required>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-grow-1 border-bottom"></div>
                                                <span class="px-3 text-muted small fw-semibold">BUAT AKUN ADMIN (OPSIONAL)</span>
                                                <div class="flex-grow-1 border-bottom"></div>
                                            </div>
                                            
                                            <p class="text-muted small text-center mb-4">Isi form di bawah jika Anda ingin langsung membuatkan akun akses untuk pengurus wilayah ini.</p>

                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">Nama Pengurus</label>
                                                    <div class="input-group input-group-merge">
                                                        <span class="input-group-text"><i class="bx bx-user"></i></span>
                                                        <input type="text" name="admin_name" class="form-control" placeholder="Contoh: Bpk. Budi Santoso">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">Email Pengurus</label>
                                                    <div class="input-group input-group-merge">
                                                        <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                                        <input type="email" name="admin_email" class="form-control" placeholder="Email aktif">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">Password Sementara</label>
                                                    <div class="input-group input-group-merge">
                                                        <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                                                        <input type="text" name="admin_password" class="form-control" placeholder="Biarkan kosong untuk: password123">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="bx bx-save me-1"></i> Simpan Data</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                    @endforeach
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="bx bx-map-alt text-muted" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="mb-1">Belum ada struktur {{ strtoupper($targetType) }}</h6>
                        <p class="text-muted mb-0">Silakan tambah struktur wilayah Anda terlebih dahulu menggunakan tombol Tambah di atas.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Add Region (Utama) -->
<div class="modal fade" id="addRegionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form action="{{ route('admin.kelola-wilayah.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="bx bx-map-pin me-2"></i> Tambah {{ strtoupper($targetType) }} Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="type" value="{{ $targetType }}">
                    
                    <div class="bg-label-primary p-3 rounded-3 mb-4">
                        <label class="form-label fw-semibold text-primary">Nama {{ strtoupper($targetType) }} <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-home-circle"></i></span>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: {{ strtoupper($targetType) }} 01" required>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1 border-bottom"></div>
                        <span class="px-3 text-muted small fw-semibold">BUAT AKUN ADMIN (OPSIONAL)</span>
                        <div class="flex-grow-1 border-bottom"></div>
                    </div>
                    
                    <p class="text-muted small text-center mb-4">Isi form di bawah jika Anda ingin langsung membuatkan akun akses untuk pengurus wilayah ini.</p>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama Pengurus</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" name="admin_name" class="form-control" placeholder="Contoh: Bpk. Budi Santoso">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Email Pengurus</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                <input type="email" name="admin_email" class="form-control" placeholder="Email aktif (cth: budi@gmail.com)">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Password Sementara</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                                <input type="text" name="admin_password" class="form-control" placeholder="Biarkan kosong untuk: password123">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="bx bx-save me-1"></i> Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
