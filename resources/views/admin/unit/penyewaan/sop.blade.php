@include('admin.unit.partials.sop_editor', [
    'unitLabel' => 'Penyewaan Alat',
    'subtitle' => 'Atur ketentuan tanggung jawab penyewa yang ditampilkan sebelum pemesanan alat.',
    'bannerText' => 'Pilih satu dari dua kebijakan SOP di bawah untuk diberlakukan kepada warga saat menyewa alat.',
    'backUrl' => route('admin.unit.penyewaan.index'),
    'updateAction' => route('admin.unit.penyewaan.sop.update'),
    'activeField' => 'sop_penyewaan_active',
    'ditanggungField' => 'sop_penyewaan_ditanggung',
    'tidakDitanggungField' => 'sop_penyewaan_tidak_ditanggung',
    'descDitanggung' => 'Kerusakan atau kehilangan alat selama masa sewa menjadi tanggung jawab pengguna.',
    'descTidakDitanggung' => 'Kerusakan tidak disengaja didukung oleh Dana Operasional desa sesuai ketentuan.',
])
