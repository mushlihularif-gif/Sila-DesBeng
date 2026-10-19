@extends('layouts.user')
@section('title', 'Edit Konten - SegiDaerah')
@section('page')
<main class="max-w-3xl mx-auto px-4 py-24">
    <h1 class="text-2xl font-bold mb-6">Edit {{ $announcement->post_category }}</h1>
    @if($errors->any())
        <div role="alert" class="bg-red-50 text-red-700 p-4 rounded-xl mb-4">{{ $errors->first() }}</div>
    @endif
    <form action="{{ route('wilayah.konten.update', $announcement) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow p-6 space-y-5">
        @csrf
        @method('PUT')
        <div><label for="title" class="block mb-2 font-semibold">Judul</label><input id="title" name="title" value="{{ old('title', $announcement->title) }}" required maxlength="255" class="w-full border rounded-xl p-3"></div>
        <div><label for="type" class="block mb-2 font-semibold">Kategori</label><input id="type" name="type" value="{{ old('type', $announcement->type) }}" required maxlength="50" class="w-full border rounded-xl p-3"></div>
        <div><label for="target_region_id" class="block mb-2 font-semibold">Jangkauan publikasi</label><select id="target_region_id" name="target_region_id" required class="w-full border rounded-xl p-3">
            @foreach($regions as $region)
                <option value="{{ $region->id }}" @selected(old('target_region_id', $announcement->region_id) == $region->id)>{{ $region->name }}</option>
            @endforeach
        </select></div>
        <div><label for="event_date" class="block mb-2 font-semibold">Tanggal kegiatan (opsional)</label><input id="event_date" name="event_date" type="datetime-local" value="{{ old('event_date', $announcement->event_date?->format('Y-m-d\TH:i')) }}" class="w-full border rounded-xl p-3"></div>
        <div><label for="location" class="block mb-2 font-semibold">Lokasi (opsional)</label><input id="location" name="location" value="{{ old('location', $announcement->location) }}" maxlength="255" class="w-full border rounded-xl p-3"></div>
        @if($announcement->image_path)
            <img src="{{ asset('storage/'.$announcement->image_path) }}" alt="Gambar saat ini" class="max-h-48 rounded-xl">
        @endif
        <div><label for="image" class="block mb-2 font-semibold">Ganti gambar (opsional, maksimal 5 MB)</label><input id="image" name="image" type="file" accept="image/jpeg,image/png,image/gif" class="w-full"></div>
        <div><label for="description" class="block mb-2 font-semibold">Isi</label><textarea id="description" name="description" required rows="10" class="w-full border rounded-xl p-3">{{ old('description', $announcement->description) }}</textarea></div>
        <div class="flex gap-3 justify-end">
            <a href="{{ route($announcement->post_category === 'Berita' ? 'wilayah.berita.index' : 'wilayah.pengumuman.index') }}" class="px-5 py-3 border rounded-full">Batal</a>
            <button type="submit" class="px-5 py-3 bg-blue-600 text-white rounded-full">Simpan perubahan</button>
        </div>
    </form>
</main>
@endsection
