<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Laporan #{{ str_pad($laporan->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 50px 0 0 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            color: #1a1a1a;
            position: relative;
        }

        .page-wrapper {
            position: relative;
            width: 100%;
            min-height: 100vh;
        }

        /* Background untuk Halaman 2 dan seterusnya (Fixed, berulang) */
        .background-image-fixed {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1000;
        }

        /* Background KHUSUS Halaman 1 (Absolute, menutupi fixed) */
        .background-image-first {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -999;
        }

        /* Padding untuk Halaman 1 (Kop Surat Besar) */
        .content-overlay {
            position: relative;
            z-index: 1;
            padding: 0px 70px 80px 70px; /* Padding atas ditangani oleh @page margin agar tidak menabrak teks background */
        }

        /* ===================== */
        /* HALAMAN 2: LAMPIRAN   */
        /* ===================== */
        .page-break {
            page-break-before: always;
        }

        /* Padding untuk Halaman 2 dst (Tanpa Kop Surat Besar) */
        .lampiran-content {
            position: relative;
            z-index: 1;
            padding: 70px 70px 80px 70px; /* Jarak atas lebih kecil karena tanpa Kop Surat (70 + 50 page margin = 120) */
        }

        /* ===================== */
        /* KOMPONEN UMUM         */
        /* ===================== */

        /* Nomor Surat */
        .surat-header {
            text-align: left;
            margin-bottom: 25px;
            display: table;
            width: 100%;
        }

        .surat-header .title-left {
            display: table-cell;
            width: 50%;
            font-size: 14pt;
            font-weight: bold;
        }
        
        .surat-header .nomor-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            font-size: 11pt;
            color: #333;
        }

        /* Tabel Info */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 5px 10px;
            font-size: 11pt;
            vertical-align: top;
        }

        .info-table .label {
            width: 180px;
            font-weight: bold;
            color: #333;
        }

        .info-table .separator {
            width: 15px;
            text-align: center;
        }

        /* Section Titles */
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 20px 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1.5px solid #333;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Deskripsi Box */
        .deskripsi-box {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px 15px;
            font-size: 11pt;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        /* Riwayat Eskalasi */
        .eskalasi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .eskalasi-table th,
        .eskalasi-table td {
            border: 1px solid #999;
            padding: 7px 10px;
            font-size: 10pt;
            text-align: left;
        }

        .eskalasi-table th {
            background-color: #2c3e50;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
            letter-spacing: 0.5px;
        }

        .eskalasi-table tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        /* Footer Section */
        .footer-section {
            position: absolute;
            bottom: 110px; /* Dinaikkan sedikit */
            right: 0;
            width: 100%;
        }

        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .ttd-table td {
            vertical-align: top;
        }

        .ttd-area {
            text-align: center;
        }

        .ttd-area .tanggal {
            font-size: 10pt;
            margin-bottom: 5px;
        }

        .ttd-area .jabatan {
            font-size: 10pt;
            font-weight: bold;
            margin-top: 18px; 
            margin-bottom: 8px;
        }

        .ttd-area .qr-placeholder {
            margin: 8px auto;
        }

        /* Disclaimer */
        .disclaimer {
            position: absolute;
            bottom: 20px;
            left: 0;
            width: 100%;
            padding-top: 8px;
            border-top: 1px dashed #999;
            font-size: 8pt;
            color: #777;
            text-align: center;
            line-height: 1.5;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10pt;
            text-transform: uppercase;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-proses { background: #cce5ff; color: #004085; }
        .status-dilanjutkan { background: #e8daef; color: #6c3483; }
        .status-selesai { background: #d4edda; color: #155724; }
        .status-ditolak { background: #f8d7da; color: #721c24; }

        /* ===================== */
        /* LAMPIRAN STYLES       */
        /* ===================== */
        .sumber-label {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .sumber-pelapor {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        .sumber-admin {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .bukti-container {
            text-align: center;
            margin: 15px 0 20px 0;
            page-break-inside: avoid;
        }

        .bukti-container img {
            max-width: 480px;
            max-height: 340px;
            border: 2px solid #ccc;
            border-radius: 6px;
        }

        .bukti-container .bukti-caption {
            font-size: 9pt;
            color: #777;
            margin-top: 8px;
            font-style: italic;
        }

        .lokasi-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }

        .lokasi-box .lokasi-title {
            font-size: 10pt;
            font-weight: bold;
            color: #166534;
            margin-bottom: 6px;
        }

        .lokasi-box .lokasi-text {
            font-size: 11pt;
            color: #333;
        }

        .lampiran-footer {
            margin-top: 30px;
            padding-top: 8px;
            border-top: 1px dashed #999;
            font-size: 8pt;
            color: #777;
            text-align: center;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <!-- Background Image (Desain dari user) -->
    <!-- Background untuk Halaman 2 dan seterusnya (Fixed position agar berulang di semua halaman) -->
    <img src="{{ public_path('User/img/buktilapor/Halaman2danseterusnya(tanpakopsurat).png') }}" class="background-image-fixed">

    <!-- Background KHUSUS Halaman 1 (Absolute position agar hanya muncul di halaman 1 dan menutupi fixed) -->
    <img src="{{ public_path('User/img/buktilapor/Halaman1buktipelaporan(kopsurat).jpg') }}" class="background-image-first">

    {{-- ============================================== --}}
    {{-- HALAMAN 1: LEMBAR PENGESAHAN (LEGALITAS RESMI) --}}
    {{-- ============================================== --}}
    <div class="page-wrapper">
        
        <!-- Content di atas background -->
        <div class="content-overlay">
            
            <!-- Spacer khusus halaman 1 untuk menghindari overlap dengan Kop Surat -->
            <div style="height: 170px;"></div>

            <!-- Header Surat -->
            <div class="surat-header">
                <div class="title-left">Bukti Registrasi Pelaporan Warga</div>
                <div class="nomor-right">Nomor Ref: SDB/{{ date('Y') }}/{{ date('m') }}/{{ str_pad($laporan->id, 5, '0', STR_PAD_LEFT) }}</div>
            </div>

            <!-- Informasi Pelapor -->
            <p class="section-title">I. Identitas Pelapor</p>
            <table class="info-table">
                <tr>
                    <td class="label">NIK</td>
                    <td class="separator">:</td>
                    <td>
                        @php
                            $nik = $laporan->user->nik ?? '-';
                            if (strlen($nik) >= 16) {
                                $censoredNik = substr($nik, 0, 2) . str_repeat('*', 12) . substr($nik, -2);
                            } else {
                                $censoredNik = $nik;
                            }
                        @endphp
                        {{ $censoredNik }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Nama Pelapor</td>
                    <td class="separator">:</td>
                    <td>{{ $laporan->nama }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Laporan</td>
                    <td class="separator">:</td>
                    <td>{{ $laporan->created_at->format('d F Y, H:i') }} WIB</td>
                </tr>
                <tr>
                    <td class="label">Kategori</td>
                    <td class="separator">:</td>
                    <td>{{ $laporan->kategori }}</td>
                </tr>
                <tr>
                    <td class="label">Wilayah RT/RW</td>
                    <td class="separator">:</td>
                    <td>RT {{ $laporan->rt_number ?? '-' }} / RW {{ $laporan->rw_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Status Terkini</td>
                    <td class="separator">:</td>
                    <td>
                        @php
                            $statusClasses = [
                                'Pending' => 'status-pending',
                                'Proses' => 'status-proses',
                                'Dilanjutkan' => 'status-dilanjutkan',
                                'Selesai' => 'status-selesai',
                                'Ditolak' => 'status-ditolak',
                            ];
                        @endphp
                        <span class="status-badge {{ $statusClasses[$laporan->status] ?? '' }}">{{ $laporan->status }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="label">Tingkat Penanganan</td>
                    <td class="separator">:</td>
                    <td style="text-transform: uppercase; font-weight: bold;">{{ $laporan->escalation_level }}</td>
                </tr>
                <tr>
                    <td class="label">Ditangani Oleh</td>
                    <td class="separator">:</td>
                    <td><strong>{{ str_replace('Sistem SiladesBeng', '', $handler_name) }}</strong> (Admin {{ ucfirst($laporan->escalation_level) }})</td>
                </tr>
            </table>

            <!-- Isi Laporan (Ringkas) -->
            <p class="section-title">II. Isi Laporan</p>
            <div class="deskripsi-box">
                {{ $laporan->deskripsi }}
            </div>

            <!-- Riwayat Penanganan -->
            <p class="section-title">III. Riwayat Penanganan (Matriks Eskalasi)</p>
            <table class="eskalasi-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Tingkat</th>
                        <th>Catatan / Tanggapan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Tingkat RT</strong></td>
                        <td>{{ $laporan->catatan_rt ?: 'Belum ada tanggapan.' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tingkat RW</strong></td>
                        <td>{{ $laporan->catatan_rw ?: 'Belum ada tanggapan.' }}</td>
                    </tr>
                    <tr>
                        <td>
                            <strong>
                                @php
                                    $adminLabel = 'Admin Instansi';
                                    if (in_array($laporan->escalation_level, ['desa', 'kecamatan', 'kabupaten'])) {
                                        $labels = [
                                            'desa' => 'Pemerintah Desa',
                                            'kecamatan' => 'Tingkat Kecamatan',
                                            'kabupaten' => 'Tingkat Kabupaten',
                                        ];
                                        $adminLabel = $labels[$laporan->escalation_level];
                                    }
                                @endphp
                                {{ $adminLabel }}
                            </strong>
                        </td>
                        <td>{{ $laporan->catatan_admin ?: 'Belum ada tanggapan.' }}</td>
                    </tr>
                </tbody>
            </table>

        </div> <!-- Tutup table-responsive -->

        <!-- Spacer fisik untuk jarak aman dengan tabel -->
        <div style="height: 200px; width: 100%; clear: both;"></div>

        <!-- Footer: TTD Digital -->
        <div class="footer-section">
                <table class="ttd-table">
                    <tr>
                        <td style="width: 55%;"></td>
                        <td style="width: 45%; text-align: center;">
                            <p style="font-size: 10pt; margin-top: 0; margin-bottom: 5px;">Bengkalis, {{ now()->format('d F Y') }}</p>
                            
                            <!-- QR Code dipindah ke antara tanggal dan nama agar menutupi ruang kosong -->
                            <div style="position: relative; width: 80px; height: 80px; margin: 15px auto;">
                                @if(!empty($qrBase64))
                                    <img src="data:image/png;base64,{{ $qrBase64 }}" width="80" height="80" style="position: absolute; top: 0; left: 0;" alt="QR Validasi">
                                @else
                                    <div style="width: 80px; height: 80px; position: absolute; top: 0; left: 0; border: 1px dashed #ccc; text-align: center; line-height: 80px; font-size: 8pt; color: #999;">QR Error</div>
                                @endif
                                <img src="{{ public_path('Admin/img/illustrations/logodomain.png') }}" width="18" height="18" style="position: absolute; top: 31px; left: 31px; background-color: white; padding: 2px; border-radius: 4px;" alt="Logo SiladesBeng">
                            </div>

                            @if(!empty(trim($handler_name ?? '')))
                                <p style="font-size: 10pt; font-weight: bold; margin-top: 5px; margin-bottom: 2px;">{{ $handler_name }}</p>
                            @endif
                            <p style="font-size: 9pt; color: #555; margin-top: 0; margin-bottom: 0;">Admin Desa</p>
                            <p style="font-size: 8pt; color: #999;">Tanda Tangan Elektronik</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Disclaimer -->
            <div class="disclaimer">
                Dokumen ini diterbitkan secara resmi oleh Platform E-Government SiladesBeng.<br>
                Keaslian dokumen dapat diverifikasi dengan memindai QR Code di atas menggunakan kamera ponsel.<br>
                Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB &bull; ID: SDB-{{ str_pad($laporan->id, 5, '0', STR_PAD_LEFT) }}
                @if($laporan->bukti || $laporan->lokasi)
                <br><strong>* Lihat halaman berikutnya untuk Lampiran Bukti Visual.</strong>
                @endif
            </div>

        </div>
    </div>

    {{-- ============================================== --}}
    {{-- HALAMAN 2: LAMPIRAN BUKTI VISUAL DAN LOKASI    --}}
    {{-- ============================================== --}}
    @if($laporan->bukti || $laporan->lokasi)
    <div class="page-break page-wrapper">
        
        <div class="lampiran-content">
            
            <!-- Header Lampiran -->
            <div class="surat-header">
                <div class="title-left">LAMPIRAN BUKTI VISUAL DAN LOKASI</div>
                <div class="nomor-right">Ref: SDB/{{ date('Y') }}/{{ date('m') }}/{{ str_pad($laporan->id, 5, '0', STR_PAD_LEFT) }}</div>
            </div>

            {{-- ======================== --}}
            {{-- BAGIAN A: DATA PELAPOR   --}}
            {{-- ======================== --}}
            <p class="section-title">A. Data Dari Pelapor</p>
            <span class="sumber-label sumber-pelapor">Disubmit oleh Pelapor ({{ $laporan->nama }})</span>
            
            {{-- Lokasi --}}
            @if($laporan->lokasi)
            <div class="lokasi-box">
                <p class="lokasi-title">Lokasi Kejadian yang Dilaporkan</p>
                <p class="lokasi-text">{{ $laporan->lokasi }}</p>
                <p style="font-size: 9pt; color: #555; margin-top: 6px;">Wilayah: RT {{ $laporan->rt_number ?? '-' }} / RW {{ $laporan->rw_number ?? '-' }}</p>
            </div>
            @endif

            {{-- Foto Bukti --}}
            @if(!empty($laporan->bukti_array))
            <div class="bukti-container">
                <table style="width: 100%; border-collapse: separate; border-spacing: 10px 0;">
                    <tr>
                    @foreach($laporan->bukti_array as $foto)
                        @php
                            $buktiPath = public_path('storage/' . $foto);
                            $buktiExists = file_exists($buktiPath);
                        @endphp
                        <td style="width: {{ 100 / count($laporan->bukti_array) }}%; vertical-align: top; text-align: center;">
                            @if($buktiExists)
                                <img src="{{ $buktiPath }}" alt="Bukti Foto Laporan" style="width: 100%; border-radius: 6px;">
                            @else
                                <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 20px; border-radius: 6px;">
                                    <p style="font-size: 10pt; color: #991b1b; font-style: italic;">File tidak tersedia.</p>
                                </div>
                            @endif
                        </td>
                    @endforeach
                    </tr>
                </table>
                <p class="bukti-caption" style="text-align: center; margin-top: 10px;">Foto bukti yang dilampirkan oleh pelapor pada saat pengajuan laporan<br>({{ $laporan->created_at->format('d F Y, H:i') }} WIB)</p>
            </div>
            @endif

            {{-- ======================== --}}
            {{-- BAGIAN B: DATA ADMIN     --}}
            {{-- ======================== --}}
            <p class="section-title">B. Data Dari Pihak Penanganan</p>
            <span class="sumber-label sumber-admin">Dikelola oleh Admin {{ ucfirst($laporan->escalation_level) }} ({{ $handler_name }})</span>

            <table class="info-table" style="margin-top: 12px;">
                <tr>
                    <td class="label">Nama Admin</td>
                    <td class="separator">:</td>
                    <td><strong>{{ $handler_name }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Jabatan</td>
                    <td class="separator">:</td>
                    <td>Admin {{ ucfirst($laporan->escalation_level) }}</td>
                </tr>
                <tr>
                    <td class="label">Status Akhir</td>
                    <td class="separator">:</td>
                    <td>
                        <span class="status-badge {{ $statusClasses[$laporan->status] ?? '' }}">{{ $laporan->status }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="label">Catatan Resolusi</td>
                    <td class="separator">:</td>
                    <td>{{ $laporan->catatan_admin ?: ($laporan->catatan_rw ?: ($laporan->catatan_rt ?: 'Tidak ada catatan tambahan.')) }}</td>
                </tr>
            </table>

            <!-- Footer Lampiran -->
            <div class="lampiran-footer">
                Halaman ini merupakan bagian yang tidak terpisahkan dari Surat Bukti Pelaporan Masyarakat<br>
                Nomor: SDB/{{ date('Y') }}/{{ date('m') }}/{{ str_pad($laporan->id, 5, '0', STR_PAD_LEFT) }}.<br>
                Platform E-Government SiladesBeng &copy; {{ date('Y') }} - Kabupaten Bengkalis
            </div>

        </div>
    </div>
    @endif

</body>
</html>
