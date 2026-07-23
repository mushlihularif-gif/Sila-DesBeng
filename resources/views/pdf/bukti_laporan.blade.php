<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Laporan #{{ str_pad($laporan->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 0;
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
            padding: 210px 70px 80px 70px; /* Jarak atas disesuaikan untuk Kop Surat */
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
            padding: 120px 70px 80px 70px; /* Jarak atas lebih kecil karena tanpa Kop Surat */
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
            margin-top: 25px;
            width: 100%;
            page-break-inside: avoid;
        }

        .footer-section .ttd-area {
            float: right;
            width: 250px;
            text-align: center;
        }

        .footer-section .ttd-area .tanggal {
            font-size: 10pt;
            margin-bottom: 5px;
        }

        .footer-section .ttd-area .jabatan {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .footer-section .ttd-area .qr-placeholder {
            margin: 8px auto;
        }

        /* Disclaimer */
        .disclaimer {
            clear: both;
            margin-top: 90px;
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
    <img src="{{ public_path('User/img/buktilapor/Halaman1buktipelaporan(kopsurat).png') }}" class="background-image-first">

    {{-- ============================================== --}}
    {{-- HALAMAN 1: LEMBAR PENGESAHAN (LEGALITAS RESMI) --}}
    {{-- ============================================== --}}
    <div class="page-wrapper">
        
        <!-- Content di atas background -->
        <div class="content-overlay">
            
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
                                $censoredNik = substr($nik, 0, 4) . str_repeat('*', 8) . substr($nik, -4);
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
                    <td><strong>{{ $handler_name }}</strong> (Admin {{ ucfirst($laporan->escalation_level) }})</td>
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

            <!-- Footer: TTD Digital -->
            <div class="footer-section">
                <div class="ttd-area">
                    <p class="tanggal">Bengkalis, {{ now()->format('d F Y') }}</p>
                    <p class="jabatan">{{ $handler_name ?? 'Sistem SilaDesBeng' }}</p>
                    <p style="font-size: 9pt; color: #555; margin-bottom: 6px;">Admin {{ ucfirst($laporan->escalation_level ?? 'Desa') }}</p>
                    
                    <!-- QR Code Validasi dengan Logo di Tengah -->
                    <div style="position: relative; width: 120px; height: 120px; margin: 0 auto;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode(url('/validasi/laporan/' . $laporan->id . '?token=' . hash_hmac('sha256', $laporan->id . $laporan->created_at, config('app.key')))) }}" width="120" height="120" style="position: absolute; top: 0; left: 0;" alt="QR Validasi">
                        <img src="{{ public_path('Admin/img/illustrations/logodomain.png') }}" width="26" height="26" style="position: absolute; top: 47px; left: 47px; background-color: white; padding: 2px; border-radius: 4px;" alt="Logo Siladesbeng">
                    </div>
                    
                    <p style="font-size: 8pt; color: #999;">Tanda Tangan Elektronik</p>
                </div>
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
            @if($laporan->bukti)
            <div class="bukti-container">
                @php
                    $buktiPath = public_path('storage/' . $laporan->bukti);
                    $buktiExists = file_exists($buktiPath);
                @endphp
                @if($buktiExists)
                    <img src="{{ $buktiPath }}" alt="Bukti Foto Laporan">
                    <p class="bukti-caption">Foto bukti yang dilampirkan oleh pelapor pada saat pengajuan laporan<br>({{ $laporan->created_at->format('d F Y, H:i') }} WIB)</p>
                @else
                    <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 20px; border-radius: 6px;">
                        <p style="font-size: 10pt; color: #991b1b; font-style: italic;">File foto bukti tidak tersedia di server.</p>
                    </div>
                @endif
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
