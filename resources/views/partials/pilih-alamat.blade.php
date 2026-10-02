{{--
    Pemilih alamat tersimpan untuk formulir pemesanan.

    Warga menyimpan alamatnya sekali di halaman Saldo & Alamat, lalu di sini
    tinggal memilihnya — tidak perlu mengetik ulang nama penerima dan alamat
    lengkap di setiap unit layanan.

    Cara pakai:

        @include('partials.pilih-alamat', [
            'alamatTersimpan' => $alamatTersimpan,
            'idNama'          => 'recipient-name',
            'idAlamat'        => 'delivery-address',
            'idTelepon'       => null,          // opsional
        ])

    Kolom yang ditunjuk tetap bisa disunting setelah terisi: alamat tersimpan
    adalah titik awal, bukan kunci. Pesanan sekali-sekali ke alamat lain tidak
    perlu memaksa warga menambah alamat baru ke buku alamatnya.
--}}

<div class="mb-4">
    @if(($alamatTersimpan ?? collect())->isNotEmpty())
    <div class="flex items-center justify-between mb-2">
        <label class="text-sm font-semibold text-gray-700">Alamat Tersimpan</label>
        <a href="{{ route('user.saldo.index') }}" class="text-xs text-blue-600 hover:text-blue-700">
            Kelola alamat
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="daftar-alamat-tersimpan">
        @foreach($alamatTersimpan as $al)
            @php
                $isiAlamat = [
                    'nama'    => $al->nama_penerima,
                    'telepon' => $al->no_telepon,
                    'alamat'  => trim($al->satuBaris() . ($al->patokan ? ' (Patokan: ' . $al->patokan . ')' : '')),
                    // Titik peta ikut terbawa. Warga menentukannya sekali di
                    // halaman Saldo & Alamat, bukan menunjuk peta berulang kali
                    // di setiap formulir pemesanan.
                    'lat'     => $al->latitude,
                    'lng'     => $al->longitude,
                ];
            @endphp
            <button type="button"
                    class="kartu-alamat text-left rounded-xl border px-3 py-2.5 transition
                           {{ $al->is_utama ? 'border-blue-300 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}"
                    data-isi='@json($isiAlamat)'>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-bold text-gray-800">{{ $al->nama_penerima }}</span>
                    @if($al->label)
                        <span class="text-[10px] font-semibold text-gray-600 bg-gray-100 rounded-full px-2 py-0.5">{{ $al->label }}</span>
                    @endif
                    @if($al->is_utama)
                        <span class="text-[10px] font-semibold text-blue-700 bg-blue-100 rounded-full px-2 py-0.5">Utama</span>
                    @endif
                </div>
                <p class="text-xs text-gray-600 mt-0.5 line-clamp-2">{{ $al->satuBaris() }}</p>

                {{-- Alamat tanpa titik peta tetap bisa dipakai, tetapi petugas
                     pengantar hanya menerima teks. Dikatakan di sini supaya warga
                     tahu ada yang perlu dilengkapi, bukan diam-diam kosong. --}}
                @if($al->punyaTitik())
                    <p class="text-[11px] text-green-600 mt-1">
                        <i class="bx bx-map-pin"></i> Titik peta tersimpan
                    </p>
                @else
                    <p class="text-[11px] text-amber-600 mt-1">
                        <i class="bx bx-error-circle"></i> Belum ada titik peta
                    </p>
                @endif
            </button>
        @endforeach
    </div>

    @endif

    @unless($gunakanPetaTerpisah ?? false)
    <div class="mt-3 rounded-xl border border-blue-100 bg-blue-50/60 p-3">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
            <label class="text-sm font-semibold text-gray-700">Tentukan titik alamat baru</label>
            <button type="button" id="btn-simpan-alamat-pesanan"
                    class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white hover:bg-blue-700 disabled:opacity-60">
                Simpan ke Alamat Saya
            </button>
        </div>
        <div id="peta-alamat-pesanan" class="w-full rounded-lg border border-gray-200 bg-gray-100" style="height:220px"></div>
        <p id="status-alamat-pesanan" class="mt-2 text-xs text-gray-500">
            Isi alamat baru di kolom alamat, lalu tentukan titik di peta. Tekan “Simpan ke Alamat Saya” agar bisa dipakai lagi.
        </p>
    </div>
    @else
    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-blue-100 bg-blue-50/60 p-3">
        <p id="status-alamat-pesanan" class="text-xs text-gray-600">Pilih alamat tersimpan atau tentukan titik baru pada peta di bawah.</p>
        <button type="button" id="btn-simpan-alamat-pesanan"
                class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white hover:bg-blue-700 disabled:opacity-60">
            Simpan ke Alamat Saya
        </button>
    </div>
    @endunless

    {{-- Titik antar ikut terkirim bersama pesanan dan alamat baru tersimpan di buku alamat. --}}
    <input type="hidden" name="{{ $latitudeName ?? 'latitude' }}" id="{{ $latitudeId ?? 'alamat-terpilih-lat' }}" value="{{ old($latitudeName ?? 'latitude') }}">
    <input type="hidden" name="{{ $longitudeName ?? 'longitude' }}" id="{{ $longitudeId ?? 'alamat-terpilih-lng' }}" value="{{ old($longitudeName ?? 'longitude') }}">
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const daftar = document.getElementById('daftar-alamat-tersimpan');
        const elNama    = document.getElementById(@json($idNama ?? ''));
        const elAlamat  = document.getElementById(@json($idAlamat ?? ''));
        const elTelepon = @json($idTelepon ?? null) ? document.getElementById(@json($idTelepon ?? '')) : null;
        const elLat = document.getElementById(@json($latitudeId ?? 'alamat-terpilih-lat'));
        const elLng = document.getElementById(@json($longitudeId ?? 'alamat-terpilih-lng'));
        const petaEl = document.getElementById('peta-alamat-pesanan');
        const statusEl = document.getElementById('status-alamat-pesanan');
        const tombolSimpan = document.getElementById('btn-simpan-alamat-pesanan');
        let peta = null, penanda = null;
        let alamatTersimpanTerpilih = false;

        function aturTitik(lat, lng, zoom = 17) {
            if (elLat) elLat.value = Number(lat).toFixed(7);
            if (elLng) elLng.value = Number(lng).toFixed(7);
            if (peta && penanda) {
                penanda.setLatLng([lat, lng]);
                peta.setView([lat, lng], zoom);
            }
        }

        function siapkanPeta() {
            if (!petaEl || !window.L || peta) return;
            const lat = parseFloat(elLat?.value) || 1.4854;
            const lng = parseFloat(elLng?.value) || 102.1512;
            peta = L.map(petaEl).setView([lat, lng], elLat?.value ? 17 : 13);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
            }).addTo(peta);
            penanda = L.marker([lat, lng], { draggable: true }).addTo(peta);
            peta.on('click', e => aturTitik(e.latlng.lat, e.latlng.lng));
            penanda.on('dragend', e => {
                const titik = e.target.getLatLng();
                aturTitik(titik.lat, titik.lng);
            });
            setTimeout(() => peta?.invalidateSize(), 100);
        }

        function muatLeaflet() {
            if (window.L) return siapkanPeta();
            if (document.getElementById('leaflet-address-css')) return;
            const css = document.createElement('link');
            css.id = 'leaflet-address-css'; css.rel = 'stylesheet';
            css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(css);
            const js = document.createElement('script');
            js.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            js.onload = siapkanPeta;
            js.onerror = () => { if (statusEl) statusEl.textContent = 'Peta tidak berhasil dimuat. Periksa koneksi, atau simpan alamat melalui menu Alamat Saya.'; };
            document.head.appendChild(js);
        }

        if (petaEl) muatLeaflet();

        function pakai(kartu) {
            const isi = JSON.parse(kartu.dataset.isi || '{}');

            if (elNama)    elNama.value    = isi.nama    || '';
            if (elAlamat)  elAlamat.value  = isi.alamat  || '';
            if (elTelepon) elTelepon.value = isi.telepon || '';

            // Koordinat ikut alamat yang dipilih. Alamat tanpa titik mengosongkan
            // keduanya, supaya titik alamat sebelumnya tidak tertinggal dan
            // menyesatkan petugas ke rumah yang salah.
            if (elLat) elLat.value = isi.lat ?? '';
            if (elLng) elLng.value = isi.lng ?? '';
            if (isi.lat != null && isi.lng != null) aturTitik(isi.lat, isi.lng);
            else if (peta && penanda) {
                penanda.setLatLng([1.4854, 102.1512]);
                peta.setView([1.4854, 102.1512], 13);
            }
            alamatTersimpanTerpilih = true;
            window.dispatchEvent(new CustomEvent('alamat-warga-dipilih', { detail: isi }));

            // Tandai yang sedang terpilih. Warna dasar 'Utama' tidak dipakai di
            // sini supaya "utama" dan "sedang dipilih" tidak tertukar artinya.
            daftar?.querySelectorAll('.kartu-alamat').forEach(k => k.classList.remove('ring-2', 'ring-blue-500'));
            kartu.classList.add('ring-2', 'ring-blue-500');
        }

        daftar?.querySelectorAll('.kartu-alamat').forEach(kartu => kartu.addEventListener('click', function () { pakai(this); }));

        // Alamat utama diisikan otomatis kalau kolomnya masih kosong. Warga yang
        // hanya punya satu alamat jadi tidak perlu menekan apa pun.
        const utama = daftar?.querySelector('.kartu-alamat');
        if (utama && elAlamat && !elAlamat.value.trim()) {
            pakai(utama);
        }

        elAlamat?.addEventListener('input', () => { alamatTersimpanTerpilih = false; });

        tombolSimpan?.addEventListener('click', async function () {
            if (alamatTersimpanTerpilih) {
                statusEl.textContent = 'Alamat ini sudah tersimpan di Alamat Saya.';
                statusEl.className = 'mt-2 text-xs text-green-700 font-semibold';
                return;
            }
            const detail = elAlamat?.value.trim();
            if (!detail) {
                statusEl.textContent = 'Isi alamat lengkap terlebih dahulu.';
                statusEl.className = 'mt-2 text-xs text-red-600';
                elAlamat?.focus();
                return;
            }
            if (!elLat?.value || !elLng?.value) {
                statusEl.textContent = 'Tentukan titik alamat dengan mengeklik atau menggeser penanda di peta.';
                statusEl.className = 'mt-2 text-xs text-red-600';
                return;
            }
            this.disabled = true;
            try {
                const response = await fetch(@json(route('user.alamat.store')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        nama_penerima: @json(auth()->user()->name),
                        no_telepon: @json(auth()->user()->phone ?? auth()->user()->kontak ?? ''),
                        region_id: @json(auth()->user()->region_id),
                        detail_alamat: detail,
                        latitude: elLat.value,
                        longitude: elLng.value,
                        is_utama: false
                    })
                });
                const hasil = await response.json();
                if (!response.ok) throw new Error(hasil.message || Object.values(hasil.errors || {}).flat()[0] || 'Alamat gagal disimpan.');
                statusEl.textContent = hasil.message || 'Alamat tersimpan. Anda dapat menggunakannya pada pesanan berikutnya.';
                statusEl.className = 'mt-2 text-xs text-green-700 font-semibold';
                window.dispatchEvent(new CustomEvent('alamat-warga-tersimpan', { detail: hasil.alamat || {} }));
            } catch (error) {
                statusEl.textContent = error.message || 'Alamat gagal disimpan.';
                statusEl.className = 'mt-2 text-xs text-red-600';
            } finally {
                this.disabled = false;
            }
        });
    });
</script>
@endpush
