@extends('admin.layouts.admin')

@section('title', isset($announcement) ? 'Edit Pengumuman' : 'Buat Pengumuman Baru')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Pengumuman & Event /</span> {{ isset($announcement) ? 'Edit' : 'Buat Baru' }}
    </h4>

    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Formulir {{ isset($announcement) ? 'Edit' : 'Buat' }}</h5>
                </div>
                <div class="card-body">
                    
                    @if(isset($laporan))
                        <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                            <span class="alert-icon text-info me-2">
                                <i class="bx bx-info-circle bx-md"></i>
                            </span>
                            <div>
                                <strong>Menindaklanjuti Laporan Warga!</strong><br>
                                Anda sedang membuat event berdasarkan laporan: <em>"{{ $laporan->nama }}"</em> dari {{ $laporan->user->name ?? 'Warga' }}.
                            </div>
                        </div>
                    @endif

                    <form action="{{ isset($announcement) ? route('admin.announcements.update', $announcement->id) : route('admin.announcements.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($announcement))
                            @method('PUT')
                        @endif

                        @if(isset($laporan))
                            <input type="hidden" name="laporan_id" value="{{ $laporan->id }}">
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="Pengumuman" {{ (isset($announcement) && $announcement->type == 'Pengumuman') ? 'selected' : '' }}>Pengumuman Biasa</option>
                                <option value="Event" {{ (isset($announcement) && $announcement->type == 'Event') ? 'selected' : '' }}>Acara / Event</option>
                                <option value="Gotong Royong" {{ (isset($announcement) && $announcement->type == 'Gotong Royong') || isset($laporan) ? 'selected' : '' }}>Gotong Royong</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Target Wilayah (Siapa yang bisa melihat?) <span class="text-danger">*</span></label>
                            <select name="target_region_id" class="form-select" required>
                                <option value="">-- Pilih Target Wilayah --</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}" {{ (isset($announcement) && $announcement->region_id == $region->id) || (!isset($announcement) && auth()->user()->region_id == $region->id) ? 'selected' : '' }}>
                                        {{ $region->name }} ({{ ucfirst($region->type) }})
                                        @if(auth()->user()->region_id == $region->id) - Wilayah Anda @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Pengumuman akan tampil di wilayah yang dipilih beserta seluruh wilayah di bawahnya.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required 
                                value="{{ old('title', $announcement->title ?? (isset($laporan) ? 'Gotong Royong: Menindaklanjuti ' . $laporan->nama : '')) }}"
                                placeholder="Contoh: Gotong Royong Pembersihan Selokan">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Lengkap <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="5" required placeholder="Jelaskan detail pengumuman atau acara...">{!! old('description', $announcement->description ?? (isset($laporan) ? "Mari bersama-sama kita melakukan gotong royong untuk mengatasi masalah:\n\n" . $laporan->deskripsi : '')) !!}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal & Waktu Acara (Opsional)</label>
                                <input type="datetime-local" name="event_date" class="form-control" 
                                    value="{{ old('event_date', isset($announcement->event_date) ? $announcement->event_date->format('Y-m-d\TH:i') : '') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lokasi (Opsional)</label>
                                <input type="text" name="location" class="form-control" 
                                    value="{{ old('location', $announcement->location ?? (isset($laporan) ? $laporan->lokasi : '')) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gambar/Poster (Opsional)</label>
                            @if(isset($announcement) && $announcement->image_path)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($announcement->image_path) }}" alt="Poster" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB.</div>
                        </div>

                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" {{ old('is_active', $announcement->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Publikasikan Langsung</label>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informasi</h5>
                </div>
                <div class="card-body">
                    <p>Modul ini digunakan untuk membuat Pengumuman, Event, atau Ajakan Gotong Royong.</p>
                    <ul>
                        <li><strong>Pengumuman:</strong> Informasi umum untuk warga.</li>
                        <li><strong>Event:</strong> Acara desa/wilayah.</li>
                        <li><strong>Gotong Royong:</strong> Ajakan aksi bersama yang biasanya menindaklanjuti laporan masalah lingkungan dari warga.</li>
                    </ul>
                    <p class="text-muted"><small>Pengumuman yang dibuat hanya akan ditampilkan kepada warga di lingkup wilayah Anda.</small></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
