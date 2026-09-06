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

@if(($alamatTersimpan ?? collect())->isNotEmpty())
<div class="mb-4">
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

    {{-- Titik antar ikut terkirim bersama pesanan. --}}
    <input type="hidden" name="latitude" id="alamat-terpilih-lat" value="{{ old('latitude') }}">
    <input type="hidden" name="longitude" id="alamat-terpilih-lng" value="{{ old('longitude') }}">

    <p class="text-xs text-gray-500 mt-2">
        Pilih salah satu untuk mengisi kolom di bawah — masih bisa Anda sunting.
        Titik petanya diatur di <a href="{{ route('user.saldo.index') }}" class="text-blue-600 hover:underline">Saldo &amp; Alamat</a>.
    </p>
</div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const daftar = document.getElementById('daftar-alamat-tersimpan');
        if (!daftar) return;

        const elNama    = document.getElementById(@json($idNama ?? ''));
        const elAlamat  = document.getElementById(@json($idAlamat ?? ''));
        const elTelepon = @json($idTelepon ?? null) ? document.getElementById(@json($idTelepon ?? '')) : null;

        const elLat = document.getElementById('alamat-terpilih-lat');
        const elLng = document.getElementById('alamat-terpilih-lng');

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

            // Tandai yang sedang terpilih. Warna dasar 'Utama' tidak dipakai di
            // sini supaya "utama" dan "sedang dipilih" tidak tertukar artinya.
            daftar.querySelectorAll('.kartu-alamat').forEach(function (k) {
                k.classList.remove('ring-2', 'ring-blue-500');
            });
            kartu.classList.add('ring-2', 'ring-blue-500');
        }

        daftar.querySelectorAll('.kartu-alamat').forEach(function (kartu) {
            kartu.addEventListener('click', function () { pakai(this); });
        });

        // Alamat utama diisikan otomatis kalau kolomnya masih kosong. Warga yang
        // hanya punya satu alamat jadi tidak perlu menekan apa pun.
        const utama = daftar.querySelector('.kartu-alamat');
        if (utama && elAlamat && !elAlamat.value.trim()) {
            pakai(utama);
        }
    });
</script>
@endpush
