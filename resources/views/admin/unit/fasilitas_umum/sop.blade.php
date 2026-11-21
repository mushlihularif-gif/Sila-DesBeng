@include('admin.unit.partials.sop_editor', [
    'unitLabel' => 'Fasilitas Umum',
    'subtitle' => 'Atur ketentuan peminjaman gedung dan aset fasilitas umum desa.',
    'bannerText' => 'Pilih satu dari dua kebijakan SOP di bawah untuk diberlakukan kepada warga saat meminjam fasilitas umum.',
    'backUrl' => route('admin.unit.fasilitas_umum.index'),
    'updateAction' => route('admin.unit.fasilitas_umum.sop.update'),
    'activeField' => 'sop_fasilitas_umum_active',
    'ditanggungField' => 'sop_fasilitas_ditanggung',
    'tidakDitanggungField' => 'sop_fasilitas_tidak_ditanggung',
    'descDitanggung' => 'Kerusakan fasilitas selama masa peminjaman menjadi tanggung jawab pengguna.',
    'descTidakDitanggung' => 'Kerusakan tidak disengaja didukung oleh Dana Operasional desa sesuai ketentuan.',
])
