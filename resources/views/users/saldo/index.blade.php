@extends('layouts.user')

@section('title', 'Saldo & Alamat')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16 bg-cover bg-center bg-no-repeat bg-fixed"
             style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
        <div class="absolute inset-0 bg-white/25 pointer-events-none"></div>

        <div class="relative max-w-3xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-center text-blue-600 mb-8">Saldo &amp; Alamat</h1>

            @if(session('success'))
                <div class="mb-6 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Kartu saldo + sebaran. Angka "tersedia" saja tidak cukup: saldo
                 yang sedang diajukan penarikan ikut terpotong dari situ, dan tanpa
                 rinciannya warga akan mengira uangnya hilang. --}}
            <div class="rounded-3xl shadow-lg overflow-hidden mb-6 text-white relative"
                 style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 55%, #60a5fa 100%);">
                <div class="absolute rounded-full" style="width:260px;height:260px;right:-90px;top:-110px;background:rgba(255,255,255,.10);"></div>
                <div class="relative px-6 py-7">
                    <p class="text-sm opacity-90 mb-1">Saldo Tersedia</p>
                    <p class="text-4xl font-extrabold tracking-tight">
                        Rp {{ number_format($rincian['tersedia'], 0, ',', '.') }}
                    </p>

                    @if($rincian['diajukan'] > 0 || $rincian['diproses'] > 0)
                    <div class="flex flex-wrap gap-2 mt-4">
                        @if($rincian['diajukan'] > 0)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs" style="background:rgba(255,255,255,.18)">
                                Menunggu diproses Rp {{ number_format($rincian['diajukan'], 0, ',', '.') }}
                            </span>
                        @endif
                        @if($rincian['diproses'] > 0)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs" style="background:rgba(255,255,255,.18)">
                                Sedang ditransfer Rp {{ number_format($rincian['diproses'], 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                    @endif

                    <div class="grid grid-cols-3 gap-3 mt-5 pt-4" style="border-top:1px solid rgba(255,255,255,.25)">
                        <div>
                            <p class="text-xs opacity-80">Total Masuk</p>
                            <p class="font-bold">Rp {{ number_format($rincian['total_masuk'], 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs opacity-80">Sudah Cair</p>
                            <p class="font-bold">Rp {{ number_format($rincian['sudah_cair'], 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs opacity-80">Terpakai Belanja</p>
                            <p class="font-bold">Rp {{ number_format($rincian['terpakai'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pengajuan Dana --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-800">Pengajuan Dana</h2>
                    @if($saldo >= \App\Models\SaldoWarga::MINIMAL_PENARIKAN)
                        <button type="button" id="btn-buka-form"
                                class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                            + Ajukan Penarikan
                        </button>
                    @endif
                </div>

                {{-- Formulir. Tersembunyi sampai ditekan supaya daftar pengajuan
                     yang jadi isi utama bagian ini tidak terdorong ke bawah. --}}
                @if($saldo >= \App\Models\SaldoWarga::MINIMAL_PENARIKAN)
                <form action="{{ route('user.saldo.tarik') }}" method="POST"
                      id="form-tarik" class="space-y-3 mb-5 pb-5 border-b border-gray-100 {{ $errors->any() ? '' : 'hidden' }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1" for="amount">Jumlah</label>
                            <input type="number" name="amount" id="amount" required
                                   min="{{ \App\Models\SaldoWarga::MINIMAL_PENARIKAN }}" max="{{ (int) $saldo }}"
                                   value="{{ old('amount', (int) $saldo) }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1" for="nama_bank">Bank / E-Wallet</label>
                            <input type="text" name="nama_bank" id="nama_bank" required
                                   value="{{ old('nama_bank', $rekeningTerakhir->nama_bank ?? '') }}"
                                   placeholder="BRI, BSI, DANA"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1" for="no_rekening">Nomor Rekening</label>
                            <input type="text" name="no_rekening" id="no_rekening" required
                                   value="{{ old('no_rekening', $rekeningTerakhir->no_rekening ?? '') }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1" for="nama_pemilik">Nama Pemilik Rekening</label>
                        <input type="text" name="nama_pemilik" id="nama_pemilik" required
                               value="{{ old('nama_pemilik', $rekeningTerakhir->nama_pemilik ?? auth()->user()->name) }}"
                               class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="btn-tutup-form"
                                class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
                @endif

                @forelse($pengajuan as $p)
                    @php
                        $gaya = match ($p->status) {
                            \App\Models\SaldoWarga::SELESAI  => ['bg-green-50 border-green-200', 'text-green-700'],
                            \App\Models\SaldoWarga::DIPROSES => ['bg-blue-50 border-blue-200', 'text-blue-700'],
                            \App\Models\SaldoWarga::DITOLAK  => ['bg-gray-50 border-gray-200', 'text-gray-500'],
                            default                          => ['bg-amber-50 border-amber-200', 'text-amber-700'],
                        };
                    @endphp
                    <div class="flex items-start justify-between gap-3 rounded-xl border px-4 py-3 mb-2 {{ $gaya[0] }}">
                        <div class="min-w-0">
                            <p class="font-bold text-gray-800">Rp {{ number_format((float) $p->amount, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-600 truncate">
                                {{ $p->nama_bank }} &middot; {{ $p->no_rekening }} &middot; a.n. {{ $p->nama_pemilik }}
                            </p>
                            <p class="text-xs mt-1 {{ $gaya[1] }}">
                                {{ $p->labelStatus() }} &middot; diajukan {{ $p->created_at->diffForHumans() }}
                                @if($p->diselesaikan_pada)
                                    &middot; selesai {{ \Carbon\Carbon::parse($p->diselesaikan_pada)->diffForHumans() }}
                                @endif
                            </p>
                            @if($p->catatan)
                                <p class="text-xs text-gray-500 mt-1">{{ $p->catatan }}</p>
                            @endif
                        </div>
                        @if($p->status === \App\Models\SaldoWarga::MENUNGGU)
                            <form action="{{ route('user.saldo.batal', $p->id) }}" method="POST"
                                  data-konfirmasi="Batalkan pengajuan penarikan Rp {{ number_format((float) $p->amount, 0, ',', '.') }}? Saldo Anda akan kembali tersedia."
                                  data-konfirmasi-judul="Batalkan Pengajuan"
                                  data-konfirmasi-ya="Ya, Batalkan">
                                @csrf
                                <button type="submit" class="flex-shrink-0 text-xs font-semibold text-red-600 hover:text-red-700 underline">
                                    Batalkan
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-2">
                        @if($saldo < \App\Models\SaldoWarga::MINIMAL_PENARIKAN)
                            Penarikan tersedia mulai Rp {{ number_format(\App\Models\SaldoWarga::MINIMAL_PENARIKAN, 0, ',', '.') }}.
                        @else
                            Belum ada pengajuan.
                        @endif
                    </p>
                @endforelse
            </div>

            {{-- Buku Alamat --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-800">Alamat Tersimpan</h2>
                    <button type="button" id="btn-alamat-baru"
                            class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                        + Tambah Alamat
                    </button>
                </div>

                {{-- Satu formulir dipakai untuk tambah maupun ubah; action dan
                     isinya diganti JavaScript saat tombol Ubah ditekan. --}}
                <form id="form-alamat" method="POST" action="{{ route('user.alamat.store') }}"
                      class="space-y-3 mb-5 pb-5 border-b border-gray-100 hidden">
                    @csrf
                    <input type="hidden" name="_method" id="alamat-method" value="POST">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1" for="al-label">Label</label>
                            <input type="text" name="label" id="al-label" placeholder="Rumah, Kantor, Kebun"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1" for="al-nama">Nama Penerima <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_penerima" id="al-nama" required
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1" for="al-telp">Nomor Telepon <span class="text-red-500">*</span></label>
                            <input type="tel" name="no_telepon" id="al-telp" required
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1" for="al-region">Desa / Kelurahan</label>
                            <select name="region_id" id="al-region"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                                <option value="">— Pilih —</option>
                                @foreach($desa as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}{{ $d->parent ? ', ' . $d->parent->name : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1" for="al-detail">Detail Alamat <span class="text-red-500">*</span></label>
                        <textarea name="detail_alamat" id="al-detail" rows="2" required
                                  placeholder="Nama jalan, nomor rumah, dusun"
                                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm"></textarea>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1" for="al-rt">RT</label>
                            <input type="text" name="rt" id="al-rt" placeholder="003"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1" for="al-rw">RW</label>
                            <input type="text" name="rw" id="al-rw" placeholder="005"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1" for="al-pos">Kode Pos</label>
                            <input type="text" name="kode_pos" id="al-pos" placeholder="28712"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1" for="al-patokan">Patokan</label>
                        <input type="text" name="patokan" id="al-patokan" placeholder="Seberang masjid, pagar hijau"
                               class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                    </div>

                    {{-- Titik peta alamat. Ditentukan SEKALI di sini, lalu ikut
                         terbawa setiap kali alamat ini dipilih saat memesan —
                         warga tidak perlu menunjuk peta berulang kali di tiap
                         formulir pemesanan. --}}
                    @if(config('services.google_maps.api_key'))
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-semibold text-gray-700">Titik Peta</label>
                            <button type="button" id="al-posisi-saya"
                                    class="text-xs font-semibold text-blue-600 hover:text-blue-700">
                                Pakai posisi saya
                            </button>
                        </div>
                        <div id="al-peta" class="w-full rounded-lg border border-gray-300"
                             style="height: 240px; background:#f3f4f6;"></div>
                        <p class="text-xs text-gray-500 mt-1" id="al-ket-titik">
                            Klik peta atau geser penanda supaya petugas tahu persis rumah Anda.
                        </p>
                    </div>
                    @endif

                    <input type="hidden" name="latitude" id="al-lat">
                    <input type="hidden" name="longitude" id="al-lng">

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_utama" id="al-utama" value="1" class="rounded border-gray-300">
                        Jadikan alamat utama
                    </label>

                    <div class="flex gap-2">
                        <button type="button" id="btn-alamat-batal"
                                class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                            Simpan Alamat
                        </button>
                    </div>
                </form>

                @forelse($alamat as $a)
                    @php
                        // Disiapkan di sini, bukan langsung di dalam onclick: Blade
                        // tidak dapat mengurai @json([...]) yang ditulis berbaris-baris
                        // di dalam atribut, dan gagal dengan "Unclosed '['".
                        $dataAlamat = [
                            'id'            => $a->id,
                            'label'         => $a->label,
                            'nama_penerima' => $a->nama_penerima,
                            'no_telepon'    => $a->no_telepon,
                            'region_id'     => $a->region_id,
                            'detail_alamat' => $a->detail_alamat,
                            'rt'            => $a->rt,
                            'rw'            => $a->rw,
                            'kode_pos'      => $a->kode_pos,
                            'patokan'       => $a->patokan,
                            'is_utama'      => $a->is_utama,
                        ];
                    @endphp
                    <div class="rounded-xl border px-4 py-3 mb-2 {{ $a->is_utama ? 'border-blue-300 bg-blue-50' : 'border-gray-200' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-gray-800">{{ $a->nama_penerima }}</span>
                                    @if($a->label)
                                        <span class="text-[11px] font-semibold text-gray-600 bg-gray-100 rounded-full px-2 py-0.5">{{ $a->label }}</span>
                                    @endif
                                    @if($a->is_utama)
                                        <span class="text-[11px] font-semibold text-blue-700 bg-blue-100 rounded-full px-2 py-0.5">Utama</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mt-1">{{ $a->no_telepon }}</p>
                                <p class="text-sm text-gray-700 mt-0.5">{{ $a->satuBaris() }}</p>
                                @if($a->patokan)
                                    <p class="text-xs text-gray-500 mt-0.5">Patokan: {{ $a->patokan }}</p>
                                @endif
                            </div>

                            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                <button type="button" class="text-xs font-semibold text-blue-600 hover:text-blue-700"
                                        onclick='ubahAlamat(@json($dataAlamat))'>
                                    Ubah
                                </button>

                                @if(! $a->is_utama)
                                    <form action="{{ route('user.alamat.utama', $a->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-gray-600 hover:text-gray-800">
                                            Jadikan Utama
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('user.alamat.destroy', $a->id) }}" method="POST"
                                      data-konfirmasi="Hapus alamat &quot;{{ $a->nama_penerima }}&quot;?"
                                      data-konfirmasi-judul="Hapus Alamat"
                                      data-konfirmasi-ya="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-2">Belum ada alamat tersimpan.</p>
                @endforelse
            </div>

            {{-- Riwayat --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-bold text-gray-800 mb-4">Riwayat Saldo</h2>

                @forelse($riwayat as $r)
                    @php $masuk = $r->type === \App\Models\SaldoWarga::REFUND; @endphp
                    <div class="flex items-start justify-between gap-3 py-3 {{ ! $loop->last ? 'border-b border-gray-100' : '' }}">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800 text-sm">{{ $r->labelJenis() }}</p>
                            @if($r->catatan)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $r->catatan }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $r->created_at->translatedFormat('d M Y, H:i') }} WIB &middot; {{ $r->labelStatus() }}
                            </p>
                        </div>
                        <p class="flex-shrink-0 font-bold {{ $masuk ? 'text-green-600' : 'text-gray-800' }}">
                            {{ $masuk ? '+' : '−' }} Rp {{ number_format((float) $r->amount, 0, ',', '.') }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-2">Belum ada riwayat.</p>
                @endforelse

                @if($riwayat->hasPages())
                    <div class="mt-4">{{ $riwayat->links() }}</div>
                @endif
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form  = document.getElementById('form-tarik');
        const buka  = document.getElementById('btn-buka-form');
        const tutup = document.getElementById('btn-tutup-form');
        if (!form) return;

        if (buka)  buka.addEventListener('click',  () => {
            form.classList.remove('hidden');
            form.querySelector('#amount')?.focus();
        });
        if (tutup) tutup.addEventListener('click', () => form.classList.add('hidden'));
    });

    // ---- Buku alamat ----
    // Satu formulir dipakai untuk tambah dan ubah; yang berubah hanya action
    // dan method-nya, supaya tidak ada dua formulir yang harus dijaga selaras.
    const RUTE_ALAMAT_BARU = @json(route('user.alamat.store'));
    const RUTE_ALAMAT      = @json(url('alamat'));

    function isiFormAlamat(data) {
        const f = document.getElementById('form-alamat');
        f.label.value         = data?.label ?? '';
        f.nama_penerima.value = data?.nama_penerima ?? '';
        f.no_telepon.value    = data?.no_telepon ?? '';
        f.region_id.value     = data?.region_id ?? '';
        f.detail_alamat.value = data?.detail_alamat ?? '';
        f.rt.value            = data?.rt ?? '';
        f.rw.value            = data?.rw ?? '';
        f.kode_pos.value      = data?.kode_pos ?? '';
        f.patokan.value       = data?.patokan ?? '';
        f.is_utama.checked    = !!data?.is_utama;
        f.latitude.value      = data?.latitude ?? '';
        f.longitude.value     = data?.longitude ?? '';

        // Peta menyusul setelah formulirnya terlihat; wadah tersembunyi
        // berukuran nol dan petanya akan tampil sebagai kotak abu-abu.
        setTimeout(function () { pasangPetaAlamat(true); }, 250);

        if (data) {
            f.action = RUTE_ALAMAT + '/' + data.id;
            document.getElementById('alamat-method').value = 'PUT';
        } else {
            f.action = RUTE_ALAMAT_BARU;
            document.getElementById('alamat-method').value = 'POST';
        }

        f.classList.remove('hidden');
        f.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function ubahAlamat(data) { isiFormAlamat(data); }

    // ---- Peta alamat ----
    const TITIK_AWAL_ALAMAT = { lat: 1.4854, lng: 102.1512 };   // Kabupaten Bengkalis
    let petaAlamat = null, penandaAlamat = null, geocoderAlamat = null;

    function pasangPetaAlamat(pusatkanUlang) {
        const wadah = document.getElementById('al-peta');
        if (!wadah || typeof google === 'undefined' || !google.maps) return;

        const f = document.getElementById('form-alamat');
        const lat = parseFloat(f.latitude.value), lng = parseFloat(f.longitude.value);
        const adaTitik = !isNaN(lat) && !isNaN(lng);
        const titik = adaTitik ? { lat, lng } : TITIK_AWAL_ALAMAT;

        if (!petaAlamat) {
            geocoderAlamat = new google.maps.Geocoder();
            petaAlamat = new google.maps.Map(wadah, {
                zoom: adaTitik ? 17 : 14, center: titik, mapTypeId: 'roadmap',
                streetViewControl: false, mapTypeControl: false,
                gestureHandling: 'cooperative',
            });
            penandaAlamat = new google.maps.Marker({
                position: titik, map: petaAlamat, draggable: true,
                title: 'Geser ke titik rumah Anda',
            });
            petaAlamat.addListener('click', (e) => pindahTitikAlamat(e.latLng));
            penandaAlamat.addListener('dragend', (e) => pindahTitikAlamat(e.latLng));
        } else if (pusatkanUlang) {
            petaAlamat.setCenter(titik);
            petaAlamat.setZoom(adaTitik ? 17 : 14);
            penandaAlamat.setPosition(titik);
        }

        google.maps.event.trigger(petaAlamat, 'resize');
        petaAlamat.setCenter(titik);
        segarkanKetTitik(adaTitik);
    }

    function pindahTitikAlamat(latLng) {
        const f = document.getElementById('form-alamat');
        penandaAlamat.setPosition(latLng);
        f.latitude.value  = latLng.lat().toFixed(7);
        f.longitude.value = latLng.lng().toFixed(7);
        segarkanKetTitik(true);
    }

    function segarkanKetTitik(ada) {
        const el = document.getElementById('al-ket-titik');
        const f = document.getElementById('form-alamat');
        if (!el) return;
        if (ada && f.latitude.value) {
            el.textContent = 'Titik tersimpan: ' + f.latitude.value + ', ' + f.longitude.value;
            el.className = 'text-xs text-green-600 mt-1 font-semibold';
        } else {
            el.textContent = 'Klik peta atau geser penanda supaya petugas tahu persis rumah Anda.';
            el.className = 'text-xs text-gray-500 mt-1';
        }
    }

    // Dipanggil Google lewat parameter callback= setelah pustakanya siap.
    window.siapkanPetaAlamat = function () { pasangPetaAlamat(false); };

    document.addEventListener('DOMContentLoaded', function () {
        const baru  = document.getElementById('btn-alamat-baru');
        const batal = document.getElementById('btn-alamat-batal');
        const f     = document.getElementById('form-alamat');
        if (!f) return;

        if (baru)  baru.addEventListener('click',  () => {
            isiFormAlamat(null);
            f.nama_penerima.focus();
        });
        if (batal) batal.addEventListener('click', () => f.classList.add('hidden'));

        const btnPos = document.getElementById('al-posisi-saya');
        if (btnPos) {
            btnPos.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    showToast('Perangkat ini tidak mendukung penentuan lokasi otomatis.', 'warning');
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const t = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
                        petaAlamat.setCenter(t); petaAlamat.setZoom(18); pindahTitikAlamat(t);
                    },
                    () => showToast('Tidak bisa membaca posisi Anda. Pastikan izin lokasi diberikan.', 'error')
                );
            });
        }
    });
</script>

@if(config('services.google_maps.api_key'))
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&loading=async&callback=siapkanPetaAlamat"></script>
@endif
@endpush
