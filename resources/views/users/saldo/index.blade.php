@extends('layouts.user')

@section('title', 'Alamat Tersimpan')

@push('styles')
<style>
    * { font-family: 'Inter', sans-serif; }
</style>
@endpush

@section('page')
<main class="flex-grow relative w-full">
    @include('partials.abstract-bg')

    <section class="relative z-10 min-h-screen pt-32 pb-16">
        <div class="relative max-w-3xl mx-auto px-4">
            <h1 class="text-3xl md:text-4xl font-bold text-center mb-8">
                <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Alamat </span>
                <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Tersimpan</span>
            </h1>

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

                        </div>
                        <div id="al-peta" class="w-full rounded-lg border border-gray-300"
                             style="height: 240px; background:#f3f4f6;"></div>
                        <p class="text-xs text-gray-500 mt-1" id="al-ket-titik">
                            Tentukan titik, lalu tekan Simpan Alamat. Alamat ini dapat dipilih kembali saat memesan.
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
                        // Disiapkan di sini dalam variabel array terpisah agar aman
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
                                <button type="button" class="btn-ubah-alamat text-xs font-semibold text-blue-600 hover:text-blue-700"
                                        data-alamat="{{ json_encode($dataAlamat) }}">
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
        </div>
    </section>
</main>
@endsection

@push('scripts')
@include('partials.google-maps-loader')
<script>
    // ---- Buku alamat ----
    // Satu formulir dipakai untuk tambah dan ubah; yang berubah hanya action
    // dan method-nya, supaya tidak ada dua formulir yang harus dijaga selaras.
    const RUTE_ALAMAT_BARU = "{{ route('user.alamat.store') }}";
    const RUTE_ALAMAT      = "{{ url('alamat') }}";

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

    let addressPicker;
    function pasangPetaAlamat() {
        const element = document.getElementById('al-peta');
        if (!element) return;
        if (addressPicker) return addressPicker.sync();
        SegiMaps.picker({ element,
            latitude: document.getElementById('al-lat'), longitude: document.getElementById('al-lng'),
            address: document.getElementById('al-detail')
        }).then(picker => { addressPicker = picker; picker.sync(); }).catch(() => {});
    }

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

        document.querySelectorAll('.btn-ubah-alamat').forEach(function(btn) {
            btn.addEventListener('click', function() {
                try {
                    const data = JSON.parse(this.getAttribute('data-alamat'));
                    ubahAlamat(data);
                } catch (e) {
                    console.error('Gagal memuat data alamat:', e);
                }
            });
        });

    });
</script>

@endpush
