{{--
    Modal tambah/ubah Lokasi Layanan — satu sumber untuk semua halaman.

    Disertakan di halaman kelola Lokasi Layanan DAN di halaman unit (mis.
    Penjualan Gas), supaya petugas bisa mendaftarkan gudang atau pangkalan
    langsung dari tempat ia bekerja, tanpa berpindah halaman lebih dulu.

    Cara pakai di halaman mana pun:

        @include('admin.lokasi_layanan._modal')

        <button data-bs-toggle="modal" data-bs-target="#modalLokasi"
                onclick="siapkanFormulir()">Tambah Lokasi</button>

    Untuk mengubah, kirimkan barisnya: onclick='siapkanFormulir(@json($l))'

    Formulirnya menyimpan lewat POST biasa dan controller membalas back(),
    jadi setelah tersimpan halaman pemanggilnya sendiri yang dimuat ulang —
    berlaku sama di halaman kelola maupun di halaman unit.
--}}

@push('modals')
<div class="modal fade" id="modalLokasi" tabindex="-1" aria-hidden="true">
    {{-- Tanpa modal-dialog-scrollable: kelas itu memberi overflow-y:auto pada
         .modal-body, dan daftar saran alamat yang mengambang jadi terpotong di
         tepi bawahnya. Biar seluruh modal yang menggulir. --}}
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" id="formLokasi" class="modal-content rounded-4 border-0">
            @csrf
            <input type="hidden" name="_method" id="metodeLokasi" value="POST">

            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center">
                    <span class="ikon-bulat rounded-circle bg-primary-subtle text-primary me-3"
                          style="width: 40px; height: 40px;">
                        <i class="bx bx-map-pin fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="judulModal">Tambah Lokasi</h5>
                        <small class="text-muted">Dipakai bersama oleh semua unit layanan.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body pt-4">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold" for="nama">Nama Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="nama" name="nama" required
                               placeholder="Contoh: Gudang BUM Desa, Kantor Desa, Pangkalan Gas RW 03">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold" for="alamat">Alamat</label>
                        <textarea class="form-control rounded-3" id="alamat" name="alamat" rows="2"
                                  placeholder="Jalan, RT/RW, patokan"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold d-flex align-items-center justify-content-between">
                            <span>Titik Peta</span>
                            <span class="text-muted fw-normal" style="font-size: .78rem;">
                                Klik peta atau geser penanda
                            </span>
                        </label>

                        @if(config('services.google_maps.api_key'))
                            <div id="petaLokasi" class="rounded-3 border"
                                 style="width: 100%; height: 300px; background: #f5f5f9;"></div>
                        @else
                            {{-- Tanpa kunci API, peta tidak akan pernah tampil; lebih baik
                                 dikatakan terus terang daripada memperlihatkan kotak abu-abu. --}}
                            <div class="alert alert-warning mb-2">
                                <i class="bx bx-error-circle"></i>
                                Kunci Google Maps belum diisi, jadi peta tidak dapat ditampilkan.
                                Koordinat masih bisa diisi manual di bawah. Super Admin dapat mengisinya
                                di <strong>Integrasi API pada Sistem Platform</strong>.
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="latitude">Latitude</label>
                        <input type="text" class="form-control rounded-3" id="latitude" name="latitude"
                               placeholder="1.4854">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="longitude">Longitude</label>
                        <input type="text" class="form-control rounded-3" id="longitude" name="longitude"
                               placeholder="102.1512">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold" for="catatan">Catatan</label>
                        <input type="text" class="form-control rounded-3" id="catatan" name="catatan"
                               placeholder="Contoh: buka 08.00-16.00, masuk lewat gerbang samping">
                    </div>

                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_aktif" name="is_aktif" value="1" checked>
                            <label class="form-check-label" for="is_aktif">
                                Aktif — tampil sebagai pilihan di formulir unit
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                <a href="{{ route('admin.lokasi-layanan.index') }}" class="text-decoration-none small">
                    <i class="bx bx-list-ul"></i> Kelola semua lokasi
                </a>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-label-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-3">
                        <i class="bx bx-check me-1"></i> Simpan Lokasi
                    </button>
                </div>
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

    // Titik awal peta: Kabupaten Bengkalis, dipakai kalau lokasinya belum
    // punya koordinat sama sekali.
    const TITIK_AWAL = { lat: 1.4854, lng: 102.1512 };

    let locationPicker;

    function siapkanFormulir(data) {
        const f = document.getElementById('formLokasi');

        if (data) {
            document.getElementById('judulModal').textContent = 'Ubah Lokasi';
            f.action = RUTE_UBAH + '/' + data.id;
            document.getElementById('metodeLokasi').value = 'PUT';
            f.nama.value      = data.nama || '';
            f.alamat.value    = data.alamat || '';
            f.latitude.value  = data.latitude ?? '';
            f.longitude.value = data.longitude ?? '';
            f.catatan.value   = data.catatan || '';
            f.is_aktif.checked = !!data.is_aktif;
        } else {
            document.getElementById('judulModal').textContent = 'Tambah Lokasi';
            f.action = RUTE_SIMPAN;
            document.getElementById('metodeLokasi').value = 'POST';
            f.reset();
            f.is_aktif.checked = true;
        }

        // Peta baru bisa dipasang setelah modalnya benar-benar tampil: Google
        // Maps menghitung ukuran wadahnya, dan wadah yang masih tersembunyi
        // berukuran nol sehingga petanya tampil sebagai kotak abu-abu.
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
