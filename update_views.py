import codecs

filepath = "D:/laragon/www/SilaDesBeng/resources/views/users/profile.blade.php"
with codecs.open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the Mutasi form in profile.blade.php
old_form = """                <form action="{{ route('user.mutasi.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4" onsubmit="return confirm('Apakah Anda yakin ingin mengajukan pindah desa? Anda tidak dapat memesan fasilitas desa hingga proses ini selesai.')">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Pilih Desa Tujuan</label>
                        <select name="to_region_id" class="w-full bg-white/50 border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all" required>
                            <option value="">-- Pilih Desa --</option>
                            @foreach(\App\Models\Region::where('id', '!=', $user->region_id)->orderBy('desa')->get() as $region)
                                <option value="{{ $region->id }}">{{ $region->desa }} (Kec. {{ $region->kecamatan }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Alasan Pindah</label>
                        <input type="text" name="reason" class="w-full bg-white/50 border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all" placeholder="Contoh: Menikah, Pindah Rumah" required>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-orange-500/30 transition-all hover:scale-[1.02] flex justify-center items-center gap-2">
                            <i class='bx bx-send'></i> Ajukan Pindah Desa
                        </button>
                    </div>
                </form>"""

new_form = """                <form action="{{ route('user.mutasi.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4" onsubmit="return confirm('Apakah Anda yakin ingin mengajukan pindah desa? Data Anda akan dipindahkan secara permanen.')">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-800 mb-2">Pilih Desa Tujuan <span class="text-red-500">*</span></label>
                        <select name="to_region_id" class="w-full bg-white/50 border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all" required>
                            <option value="">-- Pilih Desa Baru --</option>
                            @foreach(\\App\\Models\\Region::where('id', '!=', $user->region_id)->orderBy('desa')->get() as $region)
                                <option value="{{ $region->id }}">{{ $region->desa }} (Kec. {{ $region->kecamatan }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-800 mb-2">Alamat Lengkap Baru <span class="text-red-500">*</span></label>
                        <input type="text" name="alamat_baru" class="w-full bg-white/50 border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all" placeholder="Contoh: Jl. Merdeka No. 45" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">RT Baru <span class="text-red-500">*</span></label>
                        <input type="text" name="rt_baru" class="w-full bg-white/50 border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all" placeholder="001" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">RW Baru <span class="text-red-500">*</span></label>
                        <input type="text" name="rw_baru" class="w-full bg-white/50 border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all" placeholder="002" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-800 mb-2">Upload Foto KTP Baru <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mb-2"><i class='bx bx-shield-quarter'></i> Demi privasi Anda, foto KTP tidak akan disimpan dan akan otomatis dihancurkan setelah disetujui Admin.</p>
                        <!-- Deteksi otomatis kamera/file seperti di KYC -->
                        <input type="file" name="ktp_image" accept="image/*" capture="environment" class="w-full bg-white/50 border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-800 mb-2">Alasan Pindah <span class="text-red-500">*</span></label>
                        <input type="text" name="reason" class="w-full bg-white/50 border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all" placeholder="Contoh: Menikah, Pindah Rumah" required>
                    </div>
                    <div class="md:col-span-2 mt-2">
                        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-orange-500/30 transition-all hover:scale-[1.02] flex justify-center items-center gap-2">
                            <i class='bx bx-send'></i> Ajukan Pindah Desa
                        </button>
                    </div>
                </form>"""
content = content.replace(old_form, new_form)

with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Profile updated.")
