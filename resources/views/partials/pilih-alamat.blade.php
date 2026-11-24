{{--
    Pemilih alamat tersimpan untuk formulir pemesanan.

    Warga menyimpan alamatnya sekali di halaman Saldo dan Alamat, lalu di sini
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
                    'region_id' => $al->region_id,
                    // Titik peta ikut terbawa. Warga menentukannya sekali di
                    // halaman Saldo dan Alamat, bukan menunjuk peta berulang kali
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

    <button type="button" id="btn-alamat-baru-pesanan" class="inline-flex items-center gap-2 mt-3 px-4 py-2 rounded-xl border border-blue-600 bg-white text-xs font-bold text-blue-600 hover:bg-blue-50 shadow-sm transition-colors">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Gunakan Alamat Baru untuk Pesanan Ini</span>
    </button>
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
    @elseif(!($sembunyikanSimpan ?? false))
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
@include('partials.google-maps-loader')
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
        let picker = null;
        let alamatTersimpanTerpilih = false;

        function aturTitik(lat, lng) {
            if (elLat) elLat.value = Number(lat).toFixed(7);
            if (elLng) elLng.value = Number(lng).toFixed(7);
            picker?.sync();
        }
        if (petaEl) SegiMaps.picker({
            element: petaEl, latitude: elLat, longitude: elLng, address: elAlamat,
            onChange: () => { alamatTersimpanTerpilih = false; }
        }).then(value => { picker = value; }).catch(() => {});

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
            else picker?.sync();
            alamatTersimpanTerpilih = true;
            window.dispatchEvent(new CustomEvent('alamat-warga-dipilih', { detail: isi }));

            // Tandai yang sedang terpilih. Warna dasar 'Utama' tidak dipakai di
            // sini supaya "utama" dan "sedang dipilih" tidak tertukar artinya.
            daftar?.querySelectorAll('.kartu-alamat').forEach(k => k.classList.remove('ring-2', 'ring-blue-500'));
            kartu.classList.add('ring-2', 'ring-blue-500');
        }

        daftar?.querySelectorAll('.kartu-alamat').forEach(kartu => kartu.addEventListener('click', function () { pakai(this); }));
        document.getElementById('btn-alamat-baru-pesanan')?.addEventListener('click', () => {
            alamatTersimpanTerpilih = false;
            if (elAlamat) { elAlamat.value = ''; elAlamat.dispatchEvent(new Event('input', { bubbles: true })); elAlamat.focus(); }
            if (elLat) elLat.value = '';
            if (elLng) elLng.value = '';
            picker?.sync();
            daftar?.querySelectorAll('.kartu-alamat').forEach(k => k.classList.remove('ring-2', 'ring-blue-500'));
            if (statusEl) statusEl.textContent = 'Isi alamat dan pilih titik baru. Alamat tersimpan Anda tetap tersedia.';
        });

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
                        nama_penerima: elNama?.value.trim() || @json(auth()->user()->name),
                        no_telepon: elTelepon?.value.trim() || @json(auth()->user()->phone ?? auth()->user()->kontak ?? ''),
                        // Alamat pesanan bisa berbeda dari domisili akun. Wilayah
                        // opsional; jangan mengirim ID RT sebagai ID desa alamat.
                        detail_alamat: detail,
                        latitude: elLat.value,
                        longitude: elLng.value,
                        is_utama: false
                    })
                });
                const hasil = await response.json();
                if (!response.ok) throw new Error(hasil.message || Object.values(hasil.errors || {}).flat()[0] || 'Alamat gagal disimpan.');
                alamatTersimpanTerpilih = true;
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
