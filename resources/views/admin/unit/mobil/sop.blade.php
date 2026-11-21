@include('admin.unit.partials.sop_editor', [
    'unitLabel' => 'Penyewaan Transportasi',
    'subtitle' => 'Atur ketentuan tanggung jawab penyewa saat menggunakan kendaraan desa.',
    'bannerText' => 'Pilih satu dari dua kebijakan SOP di bawah untuk diberlakukan kepada warga saat menyewa transportasi.',
    'backUrl' => route('admin.unit.mobil.index'),
    'updateAction' => route('admin.unit.mobil.sop.update'),
    'activeField' => 'sop_mobil_active',
    'ditanggungField' => 'sop_mobil_ditanggung',
    'tidakDitanggungField' => 'sop_mobil_tidak_ditanggung',
    'descDitanggung' => 'Kerusakan atau kehilangan kendaraan selama masa sewa menjadi tanggung jawab pengguna.',
    'descTidakDitanggung' => 'Kerusakan tidak disengaja didukung oleh Dana Operasional desa sesuai ketentuan.',
])
