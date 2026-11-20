@push('modals')
<div class="modal fade" id="modalLokasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" id="formLokasi" action="{{ route('admin.lokasi-layanan.store') }}" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <input type="hidden" name="_method" id="metodeLokasi" value="POST">
            <div class="modal-header bg-light border-bottom px-4 py-3 rounded-top-4">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="ikon-bulat rounded-circle bg-label-primary text-primary" style="width: 40px; height: 40px;">
                        <i class="bx bx-map-pin fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="judulModal">Tambah Lokasi</h5>
                        <small class="text-muted">Dipakai bersama oleh semua unit layanan desa.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-dark" for="nama">Nama Lokasi <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text bg-light"><i class="bx bx-buildings text-muted"></i></span>
                            <input type="text" class="form-control" name="nama" id="nama" required
                                placeholder="Contoh: Gudang BUMDes, Kantor Desa, Pangkalan Gas">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-dark" for="catatan">Patokan Singkat <span class="text-muted fw-normal">(Opsional)</span></label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text bg-light"><i class="bx bx-compass text-muted"></i></span>
                            <input type="text" class="form-control" name="catatan" id="catatan"
                                placeholder="Contoh: Sebelah balai desa, pagar hijau">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small text-dark" for="alamat">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control rounded-3" name="alamat" id="alamat" rows="2" required
                            placeholder="Jalan, RT/RW, atau alamat yang mudah dipahami warga"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small text-dark d-flex align-items-center justify-content-between mb-2">
                            <span><i class="bx bx-map-alt text-primary me-1"></i>Titik Peta Google Maps</span>
                            <span class="text-muted fw-normal" style="font-size: .78rem;">Klik peta atau geser penanda</span>
                        </label>
                        @if(config('services.google_maps.api_key'))
                            <div id="petaLokasi" class="rounded-4 border shadow-xs" style="height: 290px;"></div>
                        @else
                            <div class="alert alert-warning rounded-3 mb-0 small">
                                Kunci Google Maps belum diatur. Anda tetap bisa mengisi nama dan alamat lokasi di atas, atau menempelkan koordinat secara manual di bawah.
                            </div>
                        @endif
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold small text-muted" for="latitude">Garis Lintang (Latitude)</label>
                        <input type="number" step="any" class="form-control form-control-sm bg-light font-monospace" name="latitude" id="latitude" placeholder="1.485400">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold small text-muted" for="longitude">Garis Bujur (Longitude)</label>
                        <input type="number" step="any" class="form-control form-control-sm bg-light font-monospace" name="longitude" id="longitude" placeholder="102.151200">
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch bg-light rounded-3 p-2.5 ps-5 border">
                            <input type="hidden" name="is_aktif" value="0">
                            <input class="form-check-input" type="checkbox" name="is_aktif" id="is_aktif" value="1" checked>
                            <label class="form-check-label fw-semibold small text-dark" for="is_aktif">
                                Tampilkan lokasi ini pada pilihan produk dan layanan desa
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top px-4 py-3 rounded-bottom-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="bx bx-save me-1"></i>Simpan Lokasi
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('styles')
<style>
    .ikon-bulat {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        line-height: 1;
        flex-shrink: 0;
    }
    .ikon-bulat i { line-height: 1; }
    .min-w-0 { min-width: 0; }
</style>
@endpush

@push('scripts')
@include('partials.google-maps-loader')
<script>
    const RUTE_SIMPAN = @json(route('admin.lokasi-layanan.store'));
    const RUTE_UBAH   = @json(url('admin/lokasi-layanan'));
    const TITIK_AWAL  = { lat: 1.4854, lng: 102.1512 };

    let locationPicker;

    function siapkanFormulir(data) {
        const f = document.getElementById('formLokasi');

        if (data) {
            document.getElementById('judulModal').textContent = 'Ubah Lokasi';
            f.action = RUTE_UBAH + '/' + data.id;
            document.getElementById('metodeLokasi').value = 'PUT';
            f.nama.value       = data.nama || '';
            f.alamat.value     = data.alamat || '';
            f.latitude.value   = data.latitude ?? '';
            f.longitude.value  = data.longitude ?? '';
            f.catatan.value    = data.catatan || '';
            f.is_aktif.checked = !!data.is_aktif;
        } else {
            document.getElementById('judulModal').textContent = 'Tambah Lokasi';
            f.action = RUTE_SIMPAN;
            document.getElementById('metodeLokasi').value = 'POST';
            f.reset();
            f.is_aktif.checked = true;
        }

        setTimeout(pasangPeta, 350);
    }

    function pasangPeta() {
        const element = document.getElementById('petaLokasi');
        if (!element) return;
        if (locationPicker) return locationPicker.sync();
        const form = document.getElementById('formLokasi');
        SegiMaps.picker({ element, latitude: form.latitude, longitude: form.longitude, address: form.alamat })
            .then(picker => { locationPicker = picker; picker.sync(); }).catch(() => {});
    }
</script>
@endpush
