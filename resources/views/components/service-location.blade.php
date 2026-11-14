@props(['item'])
@php
    $lat = $item->latitude;
    $lng = $item->longitude;
    $hasPoint = is_numeric($lat) && is_numeric($lng) && abs((float) $lat) <= 90 && abs((float) $lng) <= 180;
    $address = trim((string) $item->lokasi);
    $regionName = $item->region?->name ?? '';
    $query = $hasPoint ? $lat.','.$lng : trim($address.' '.$regionName);
@endphp
<div class="mt-4 rounded-xl border border-blue-100 bg-white p-4">
    <h3 class="font-semibold text-[#115789]">Lokasi Layanan</h3>
    <p class="text-gray-700 mt-1">{{ $address ?: ($regionName ?: 'Lokasi belum dilengkapi pengelola.') }}</p>
    @if($hasPoint || $address)
        <a href="https://www.google.com/maps?q={{ rawurlencode($query) }}" target="_blank" rel="noopener noreferrer" class="inline-flex mt-3 text-[#115789] font-semibold underline">{{ $hasPoint ? 'Buka Titik Lokasi di Peta' : 'Cari Alamat di Peta' }}</a>
        @unless($hasPoint)<p class="text-sm text-gray-500 mt-2">Titik tepat belum ditentukan. Hasil pencarian alamat perlu dikonfirmasi kepada pengelola.</p>@endunless
    @else
        <p class="text-sm text-gray-500 mt-2">Minta titik lokasi melalui Chat Pengelola.</p>
    @endif
</div>
