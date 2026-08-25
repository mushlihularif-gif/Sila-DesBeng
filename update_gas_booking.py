import codecs
import re

filepath = "D:/laragon/www/SilaDesBeng/app/Http/Controllers/User/GasSalesUserController.php"
with codecs.open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

old_booking = """    public function booking($id)
    {
        // Ambil data produk gas spesifik
        $item = Gas::findOrFail($id);

        // Validasi KYC: Pengguna harus sudah terverifikasi
        if (auth()->user()->verification_status !== 'verified') {
            return redirect()->back()->with('show_kyc_modal', true);
        }

        // Validasi: Warga hanya bisa memesan layanan di wilayahnya sendiri
        if (auth()->user()->region_id != $item->region_id) {
            return redirect()->back()->with('error', 'Layanan khusus warga lokal. Silakan sesuaikan wilayah Anda.');
        }"""

new_booking = """    public function booking($id)
    {
        // Ambil data produk gas spesifik
        $item = Gas::findOrFail($id);
        $user = auth()->user();

        // Validasi KYC: Pengguna harus sudah terverifikasi
        if ($user->verification_status !== 'verified') {
            return redirect()->back()->with('show_kyc_modal', true);
        }

        // Validasi: Warga hanya bisa memesan layanan di wilayahnya sendiri
        if ($user->region_id != $item->region_id) {
            return redirect()->back()->with('error', 'Layanan khusus warga lokal. Silakan sesuaikan wilayah Anda.');
        }

        // Validasi Krisis Gas
        $region = \\App\\Models\\Region::find($user->region_id);
        if ($region && $region->is_gas_crisis) {
            // Cek apakah punya KK terverifikasi
            if (!$user->familyMember || !$user->familyMember->familyCard) {
                return redirect()->route('user.gas.sales')->with('error', 'Desa sedang dalam mode krisis gas. Anda wajib memverifikasi Kartu Keluarga (KK) terlebih dahulu.');
            }
        }"""
content = content.replace(old_booking, new_booking)

with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Booking gate updated.")
