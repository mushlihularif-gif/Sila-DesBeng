@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <!-- Header Section -->
    <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-center gap-4 border-b pb-4">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Detail Laporan Warga</h2>
            <p class="text-sm text-gray-500 mt-1">ID Referensi: #{{ str_pad($laporan->id, 5, '0', STR_PAD_LEFT) }} &bull; Dilaporkan pada {{ $laporan->created_at->format('d M Y, H:i') }}</p>
        </div>
        <div class="flex gap-3">
            @if(in_array($laporan->status, ['Proses', 'Dilanjutkan', 'Selesai']))
            <a href="{{ route('wilayah.laporan.cetak', $laporan->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Surat Bukti
            </a>
            @endif
            <a href="{{ route('wilayah.laporan.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="rounded-md bg-green-50 p-4 mb-6 border border-green-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-md bg-red-50 p-4 mb-6 border border-red-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Report Information (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Main Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Rincian Informasi Laporan</h3>
                    
                    <!-- Status Badge -->
                    @php
                        $statusColors = [
                            'Pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                            'Proses' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'Dilanjutkan' => 'bg-purple-100 text-purple-800 border-purple-200',
                            'Selesai' => 'bg-green-100 text-green-800 border-green-200',
                            'Ditolak' => 'bg-red-100 text-red-800 border-red-200',
                        ];
                        $statusClass = $statusColors[$laporan->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold border {{ $statusClass }}">
                        {{ $laporan->status }}
                    </span>
                </div>
                <div class="px-6 py-5">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Nama Pelapor</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $laporan->nama }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Kategori Laporan</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $laporan->kategori }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Lokasi Kejadian</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $laporan->lokasi }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Tingkat Eskalasi (Wewenang Saat Ini)</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="uppercase tracking-wider font-bold text-red-600 bg-red-50 px-2 py-1 rounded">{{ $laporan->escalation_level }}</span>
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Deskripsi Lengkap</dt>
                            <dd class="mt-2 text-sm text-gray-900 bg-gray-50 p-4 rounded-md border border-gray-100 leading-relaxed whitespace-pre-wrap">{{ $laporan->deskripsi }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Evidence Photo Card -->
            @if($laporan->bukti)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Lampiran Bukti Foto</h3>
                </div>
                <div class="px-6 py-5">
                    <img src="{{ asset('storage/laporan/' . $laporan->bukti) }}" alt="Bukti Laporan" class="rounded-lg max-w-full h-auto shadow border border-gray-200">
                </div>
            </div>
            @endif

            <!-- Response History Timeline -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Riwayat Penanganan (Matriks Eskalasi)</h3>
                </div>
                <div class="px-6 py-5">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @php $hasResponse = false; @endphp
                            
                            @if($laporan->catatan_rt)
                                @php $hasResponse = true; @endphp
                                <li>
                                    <div class="relative pb-8">
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                    <span class="text-white text-xs font-bold">RT</span>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">Tanggapan dari <span class="font-medium text-gray-900">Tingkat RT</span></p>
                                                    <div class="mt-2 text-sm text-gray-700 bg-gray-50 p-3 rounded-md border border-gray-200">
                                                        {{ $laporan->catatan_rt }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endif
                            
                            @if($laporan->catatan_rw)
                                @php $hasResponse = true; @endphp
                                <li>
                                    <div class="relative pb-8">
                                        @if($laporan->catatan_admin)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center ring-8 ring-white">
                                                    <span class="text-white text-xs font-bold">RW</span>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">Tanggapan dari <span class="font-medium text-gray-900">Tingkat RW</span></p>
                                                    <div class="mt-2 text-sm text-gray-700 bg-gray-50 p-3 rounded-md border border-gray-200">
                                                        {{ $laporan->catatan_rw }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endif

                            @if($laporan->catatan_admin)
                                @php $hasResponse = true; @endphp
                                <li>
                                    <div class="relative">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-purple-600 flex items-center justify-center ring-8 ring-white">
                                                    <span class="text-white text-xs font-bold">A</span>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">Tanggapan dari <span class="font-medium text-gray-900">Admin Instansi (Desa/Kec/Kab)</span></p>
                                                    <div class="mt-2 text-sm text-gray-700 bg-purple-50 p-3 rounded-md border border-purple-200">
                                                        {{ $laporan->catatan_admin }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endif

                            @if(!$hasResponse)
                                <li>
                                    <div class="text-center py-6 text-sm text-gray-500 bg-gray-50 rounded-md border border-dashed border-gray-300">
                                        Belum ada rekam jejak tanggapan dari instansi terkait.
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Actions (1/3 width) -->
        <div class="lg:col-span-1">
            <div class="sticky top-6 space-y-6">
                
                @if(!in_array($laporan->status, ['Selesai', 'Ditolak']))
                    @if(auth()->user()->region->type === $laporan->escalation_level || auth()->user()->role === 'super_admin')
                        
                        <!-- Panel Tindakan Admin -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                            <div class="px-4 py-5 border-b border-gray-200 bg-gray-900">
                                <h3 class="text-lg leading-6 font-medium text-white">Panel Tindakan Admin</h3>
                                <p class="text-xs text-gray-300 mt-1">Sistem Resolusi Laporan</p>
                            </div>
                            
                            <div class="p-4 space-y-6">
                                <!-- Action 1: Proses -->
                                <form action="{{ route('wilayah.laporan.respond', $laporan->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-2">
                                        <label for="catatan_proses" class="block text-sm font-medium text-gray-700">Tanggapi Laporan (Proses)</label>
                                        <p class="text-xs text-gray-500 mb-2">Beri tahu pelapor bahwa masalah sedang ditangani oleh tingkat ini.</p>
                                    </div>
                                    <textarea id="catatan_proses" name="catatan" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" required placeholder="Tulis rencana atau status pengerjaan..."></textarea>
                                    <button type="submit" class="mt-3 w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                        Proses Laporan
                                    </button>
                                </form>

                                <hr class="border-gray-200">

                                <!-- Action 2: Eskalasi -->
                                @if($laporan->canBeEscalated())
                                <form action="{{ route('wilayah.laporan.escalate', $laporan->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-2">
                                        <label for="catatan_eskalasi" class="block text-sm font-medium text-gray-700">Eskalasi Manual</label>
                                        <p class="text-xs text-gray-500 mb-2">Teruskan wewenang laporan ini ke tingkat <strong class="uppercase text-gray-800">{{ $laporan->getNextEscalationLevel() }}</strong>.</p>
                                    </div>
                                    <textarea id="catatan_eskalasi" name="catatan" rows="3" class="shadow-sm focus:ring-yellow-500 focus:border-yellow-500 block w-full sm:text-sm border-gray-300 rounded-md" required placeholder="Tuliskan alasan mengapa tingkat ini tidak sanggup menangani..."></textarea>
                                    <button type="submit" class="mt-3 w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-yellow-800 bg-yellow-100 hover:bg-yellow-200 border-yellow-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors" onclick="return confirm('Apakah Anda yakin ingin melepas tanggung jawab laporan ini dan meneruskannya ke tingkat yang lebih tinggi?')">
                                        Teruskan ke Tingkat Atas
                                    </button>
                                </form>
                                <hr class="border-gray-200">
                                @endif

                                <!-- Action 3: Selesai -->
                                <form action="{{ route('wilayah.laporan.resolve', $laporan->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-2">
                                        <label for="catatan_selesai" class="block text-sm font-medium text-gray-700">Selesaikan Laporan</label>
                                        <p class="text-xs text-gray-500 mb-2">Tandai bahwa laporan ini telah diselesaikan sepenuhnya.</p>
                                    </div>
                                    <textarea id="catatan_selesai" name="catatan" rows="2" class="shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Catatan hasil penyelesaian (opsional)..."></textarea>
                                    <button type="submit" class="mt-3 w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors" onclick="return confirm('Tandai laporan ini sebagai selesai?')">
                                        Selesaikan Laporan
                                    </button>
                                </form>

                            </div>
                        </div>
                    @else
                        <!-- No Permission Notice -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center shadow-sm">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Akses Dibatasi</h3>
                            <p class="text-xs text-gray-500">Laporan ini sedang ditangani oleh tingkat <strong class="uppercase text-gray-900">{{ $laporan->escalation_level }}</strong>. Anda hanya memiliki hak akses untuk memantau status laporan ini.</p>
                        </div>
                    @endif
                @else
                    <!-- Closed Notice -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center shadow-sm">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">Kasus Ditutup</h3>
                        <p class="text-xs text-gray-500">Laporan ini telah ditandai sebagai selesai atau ditolak. Tidak ada tindakan lebih lanjut yang diperlukan.</p>
                    </div>

                    <!-- Tombol Cetak (muncul saat kasus ditutup) -->
                    @if(in_array($laporan->status, ['Selesai']))
                    <a href="{{ route('wilayah.laporan.cetak', $laporan->id) }}" target="_blank" class="w-full inline-flex justify-center items-center py-3 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Surat Bukti Resmi
                    </a>
                    @endif
                @endif
                
            </div>
        </div>
    </div>
</div>
@endsection
