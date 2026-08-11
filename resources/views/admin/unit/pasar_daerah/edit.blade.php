@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <div class="mb-4">
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Unit Layanan / Pasar Daerah /</span> Edit Produk
            </h4>
            <p class="text-muted mb-0">Perbarui informasi produk yang ada</p>
        </div>

        <!-- Form Card -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card modern-card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper me-3">
                                <i class='bx bx-edit text-warning' style="font-size: 24px;"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Form Edit Produk Pasar Daerah</h5>
                                <small class="text-muted">Ubah detail produk yang akan dijual</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        {{-- Tampilan error validasi --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show modern-alert" role="alert">
                                <div class="d-flex align-items-start">
                                    <i class='bx bx-error-circle me-2' style="font-size: 20px;"></i>
                                    <div class="flex-grow-1">
                                        <strong>Perhatian!</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.unit.pasar_daerah.update', $produk->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <!-- LEFT COLUMN: Informasi Utama -->
                                <div class="col-md-7">
                                    <div class="form-section mb-4">
                                        <h6 class="section-title mb-3"><i class='bx bx-info-circle me-2'></i>Informasi Produk</h6>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" for="nama_produk">Nama Produk <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="{{ old('nama_produk', $produk->nama_produk) }}" required placeholder="Contoh: Beras Premium" />
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" for="kategori">Kategori</label>
                                            <select class="form-select" id="kategori" name="kategori">
                                                <option value="" disabled>Pilih Kategori</option>
                                                @foreach($kategoriList as $k)
                                                    <option value="{{ $k }}" {{ old('kategori', $produk->kategori) == $k ? 'selected' : '' }}>{{ $k }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" for="deskripsi">Deskripsi</label>
                                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" placeholder="Jelaskan detail produk...">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                                        </div>
                                    </div>

                                    <div class="form-section mb-4">
                                        <h6 class="section-title mb-3"><i class='bx bx-money me-2'></i>Harga & Stok</h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold" for="harga">Harga <span class="text-danger">*</span></label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="number" class="form-control" id="harga" name="harga" value="{{ old('harga', $produk->harga) }}" required min="0" />
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold" for="satuan">Satuan</label>
                                                <input type="text" class="form-control" id="satuan" name="satuan" value="{{ old('satuan', $produk->satuan) }}" />
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold" for="stok">Stok <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="stok" name="stok" value="{{ old('stok', $produk->stok) }}" required min="0" />
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold" for="status">Status <span class="text-danger">*</span></label>
                                                <select class="form-select" id="status" name="status" required>
                                                    <option value="tersedia" {{ old('status', $produk->status) == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                                    <option value="habis" {{ old('status', $produk->status) == 'habis' ? 'selected' : '' }}>Habis</option>
                                                    <option value="nonaktif" {{ old('status', $produk->status) == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- RIGHT COLUMN: Media & Map -->
                                <div class="col-md-5">
                                    <div class="form-section mb-4">
                                        <h6 class="section-title mb-3"><i class='bx bx-image me-2'></i>Foto Produk</h6>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" for="foto">Foto Utama (Opsional: biarkan kosong jika tidak ingin mengubah)</label>
                                            @if($produk->foto)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $produk->foto) }}" alt="Foto Utama" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                            @endif
                                            <input class="form-control" type="file" id="foto" name="foto" accept="image/png, image/jpeg" />
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" for="foto_2">Foto Opsional 1</label>
                                            @if($produk->foto_2)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $produk->foto_2) }}" alt="Foto 2" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                            @endif
                                            <input class="form-control" type="file" id="foto_2" name="foto_2" accept="image/png, image/jpeg" />
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" for="foto_3">Foto Opsional 2</label>
                                            @if($produk->foto_3)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $produk->foto_3) }}" alt="Foto 3" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                            @endif
                                            <input class="form-control" type="file" id="foto_3" name="foto_3" accept="image/png, image/jpeg" />
                                        </div>
                                    </div>

                                    <div class="form-section mb-4">
                                        <h6 class="section-title mb-3"><i class='bx bx-map me-2'></i>Lokasi Penjual</h6>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" for="lokasi">Alamat Lengkap Penjual</label>
                                            <textarea class="form-control" id="lokasi" name="lokasi" rows="2" placeholder="Alamat lengkap toko / penjual">{{ old('lokasi', $produk->lokasi) }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Pilih Titik di Peta</label>
                                            <div id="map" style="height: 250px; border-radius: 8px; z-index: 0;"></div>
                                            <small class="text-muted d-block mt-1">Titik ini akan digunakan untuk menghitung ongkos kirim (opsional).</small>
                                            <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $produk->latitude) }}">
                                            <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $produk->longitude) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="border-top pt-4 mt-2 d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.unit.pasar_daerah.index') }}" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-warning"><i class="bx bx-save me-1"></i> Perbarui Produk</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .modern-card { border: none; border-radius: 12px; }
        .section-title { font-weight: 700; color: #566a7f; border-bottom: 2px solid #e7e7eb; padding-bottom: 10px; margin-bottom: 20px; }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var defaultIcon = L.icon({
                iconUrl: '{{ asset("User/img/marker-icon.png") }}',
                shadowUrl: '{{ asset("User/img/marker-shadow.png") }}',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            var lat = document.getElementById('latitude').value || 1.47271;
            var lng = document.getElementById('longitude').value || 102.13886;
            var map = L.map('map').setView([lat, lng], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            var marker = null;
            if (document.getElementById('latitude').value) {
                marker = L.marker([lat, lng], { draggable: true, icon: defaultIcon }).addTo(map);
                marker.on('dragend', function(event) {
                    updateCoordinates(event.target.getLatLng());
                });
            }

            function updateCoordinates(latlng) {
                document.getElementById('latitude').value = latlng.lat;
                document.getElementById('longitude').value = latlng.lng;
            }

            map.on('click', function(e) {
                if(marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng, { draggable: true, icon: defaultIcon }).addTo(map);
                    marker.on('dragend', function(event) {
                        updateCoordinates(event.target.getLatLng());
                    });
                }
                updateCoordinates(e.latlng);
            });
        });
    </script>
@endpush
