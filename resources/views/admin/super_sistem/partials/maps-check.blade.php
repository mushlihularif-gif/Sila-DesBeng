<div class="rounded-4 border bg-light bg-opacity-50 p-3 mb-3">
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
        <div>
            <div class="fw-semibold small text-dark">
                <i class="bx bx-search-alt text-primary me-1"></i>Diagnostik Pencarian Alamat (Geocoding)
            </div>
            <p class="small text-muted mb-0 mt-0.5" style="font-size: 0.76rem;">
                Menguji pencarian alamat contoh <em>Pematang Duku Timur, Bengkalis</em> dengan kunci aktif saat ini. Simpan kunci terlebih dahulu sebelum menguji.
            </p>
        </div>
        <button type="button" id="check-google-geocoding" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold flex-shrink-0">
            <i class="bx bx-play-circle me-1"></i>Uji Pencarian Alamat
        </button>
    </div>
    <p id="google-geocoding-result" class="small mt-2 mb-0 fw-medium" role="status" aria-live="polite" data-map-status></p>
</div>
@push('scripts')
@include('partials.google-maps-loader')
<script>
    document.getElementById('check-google-geocoding')?.addEventListener('click', async function () {
        const result = document.getElementById('google-geocoding-result');
        this.disabled = true;
        result.className = 'small mt-2 mb-0 fw-medium text-muted';
        result.textContent = 'Menguji pencarian alamat dengan Google…';
        try {
            const msg = await SegiMaps.checkGeocoding();
            result.textContent = msg;
            result.className = 'small mt-2 mb-0 fw-semibold ' + (msg.startsWith('Berhasil') ? 'text-success' : 'text-danger');
        } catch (_) {
            result.className = 'small mt-2 mb-0 fw-semibold text-danger';
            result.textContent = 'Google Maps belum dapat dimuat. Periksa kunci aktif, izin domain situs ini, koneksi, dan billing Google Cloud.';
        } finally {
            this.disabled = false;
        }
    });
</script>
@endpush
