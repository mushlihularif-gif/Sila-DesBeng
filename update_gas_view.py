import codecs

filepath = "D:/laragon/www/SilaDesBeng/resources/views/users/gas-sales.blade.php"
with codecs.open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Insert after the banner/header
banner_marker = """<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 py-12 md:py-20 text-center">"""
end_banner = """</section>"""

overlay_html = """
@if(isset($isGasCrisis) && $isGasCrisis)
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-12">
    @if($pendingKk)
    <div class="bg-yellow-50 border border-yellow-200 rounded-3xl p-6 md:p-8 shadow-lg text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-yellow-500"></div>
        <i class='bx bx-time-five text-yellow-500 text-5xl mb-4'></i>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Pembaruan KK Sedang Diproses</h2>
        <p class="text-gray-600 mb-4 text-sm md:text-base">
            Foto Kartu Keluarga (KK) yang Anda unggah sedang dalam proses verifikasi oleh Admin Desa. <br>
            Silakan tunggu hingga proses ini disetujui untuk dapat memesan Gas Daerah.
        </p>
    </div>
    @elseif(!$hasKk)
    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-xl border border-red-100 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-red-500 to-orange-500"></div>
        <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center text-3xl mb-4">
                <i class='bx bx-error'></i>
            </div>
            <h2 class="text-2xl font-black text-gray-900 mb-1">STATUS DESA: KRISIS GAS</h2>
            <p class="text-red-600 font-semibold mb-6">Pembelian dibatasi 1 Tabung per Keluarga.</p>
            
            <p class="text-gray-700 mb-6 max-w-lg">
                Untuk melanjutkan pemesanan, kami perlu memverifikasi Kartu Keluarga (KK) Anda. 
            </p>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-8 max-w-lg text-left w-full flex gap-3 items-start">
                <i class='bx bx-shield-quarter text-blue-500 text-xl mt-0.5'></i>
                <p class="text-xs md:text-sm text-blue-800 leading-relaxed">
                    <strong>Info Privasi:</strong> Demi keamanan Anda, foto KK tidak akan disimpan oleh sistem setelah diverifikasi oleh Pemerintah Desa.
                </p>
            </div>

            <button type="button" onclick="openKkModal()" class="w-full max-w-xs bg-gradient-to-r from-red-500 to-orange-500 hover:from-red-600 hover:to-orange-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-all hover:scale-105 flex items-center justify-center gap-2">
                <i class='bx bx-scan'></i> SCAN KARTU KELUARGA
            </button>
        </div>
    </div>
    @else
    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-xl border border-green-100 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-green-500 to-emerald-500"></div>
        <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-green-100 text-green-500 rounded-full flex items-center justify-center text-3xl mb-4">
                <i class='bx bx-check-circle'></i>
            </div>
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-1">Data KK Anda Sudah Terdaftar</h2>
            <p class="text-gray-500 font-mono bg-gray-100 px-4 py-1 rounded-full text-sm mb-6">(No. KK: {{ $familyCardNumber }})</p>
            
            <p class="text-gray-700 mb-4 max-w-lg">
                Jika susunan anggota keluarga Anda tidak mengalami perubahan dan sesuai dengan data anggota keluarga yang Anda masukkan sebelumnya, silakan lanjutkan pemesanan gas.
            </p>
            <p class="text-gray-700 mb-8 max-w-lg font-medium text-orange-600">
                Namun, jika terdapat perubahan data pada Kartu Keluarga Anda, mohon perbarui data terlebih dahulu.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 w-full max-w-lg justify-center">
                <button type="button" onclick="document.getElementById('katalog-gas').scrollIntoView({behavior:'smooth'})" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all hover:-translate-y-1">
                    LANJUTKAN PESAN GAS
                    <span class="block text-xs font-normal opacity-80">(Data Lama)</span>
                </button>
                <button type="button" onclick="openKkModal()" class="flex-1 bg-white hover:bg-gray-50 text-gray-800 border-2 border-gray-200 font-bold py-3 px-6 rounded-xl shadow-sm transition-all hover:-translate-y-1">
                    PERBARUI KK
                    <span class="block text-xs font-normal text-gray-500">(Terdapat Perubahan)</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal Scan KK -->
<div id="kkModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeKkModal()"></div>
    <div class="bg-white rounded-3xl w-full max-w-md mx-4 relative z-10 overflow-hidden shadow-2xl animate-fade-up">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Verifikasi Kartu Keluarga</h3>
            <button type="button" onclick="closeKkModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class='bx bx-x text-2xl'></i>
            </button>
        </div>
        <form action="{{ route('user.gas.verify-kk') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            <div class="mb-6 text-center">
                <div class="w-20 h-20 bg-orange-100 text-orange-500 rounded-2xl mx-auto flex items-center justify-center text-4xl mb-4">
                    <i class='bx bx-id-card'></i>
                </div>
                <p class="text-sm text-gray-600">Ambil foto Kartu Keluarga (KK) asli secara jelas. Pastikan NIK anggota keluarga dapat terbaca oleh sistem kami.</p>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-800 mb-2">Foto Kartu Keluarga</label>
                <input type="file" name="kk_image" accept="image/*" capture="environment" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500" required>
            </div>

            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all hover:-translate-y-1 flex items-center justify-center gap-2">
                <i class='bx bx-upload'></i> KIRIM & VERIFIKASI
            </button>
        </form>
    </div>
</div>
<script>
    function openKkModal() {
        document.getElementById('kkModal').classList.remove('hidden');
        document.getElementById('kkModal').classList.add('flex');
    }
    function closeKkModal() {
        document.getElementById('kkModal').classList.add('hidden');
        document.getElementById('kkModal').classList.remove('flex');
    }
</script>
@endif
"""

# Inject before <section id="katalog-gas"
if "<section id=\"katalog-gas\"" in content:
    content = content.replace("<section id=\"katalog-gas\"", overlay_html + "\n<section id=\"katalog-gas\"")
else:
    # Just in case, inject after the hero section
    content = content.replace(end_banner, end_banner + "\n" + overlay_html)

with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Gas Sales view updated.")
