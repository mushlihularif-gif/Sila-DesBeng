@extends('layouts.user')
@section('title','Pengaturan Notifikasi — SegiDaerah')
@section('page')
<main class="max-w-4xl mx-auto w-full px-4 pt-36 pb-16">
    <a href="{{ route('content.index') }}" class="text-blue-600">← Kabar dan Informasi Daerah</a>
    <h1 class="font-bold text-3xl mt-4 mb-3">Pengaturan Notifikasi</h1>
    <p class="text-gray-600 mb-6">Pilih pemberitahuan yang ingin Anda terima. Notifikasi di akun tetap tersedia ketika WhatsApp dinonaktifkan.</p>
    <section class="bg-white border border-slate-200 rounded-2xl p-6 mb-6"><h2 class="font-bold text-xl">Nomor WhatsApp</h2><p class="mt-2">{{ $verified ? 'Nomor pada profil sudah terverifikasi untuk pemberitahuan.' : 'Verifikasi nomor pada profil sebelum mengaktifkan pemberitahuan WhatsApp.' }}</p>
    @if(!$verified)
    <form method="post" action="{{ route('notification-settings.send-code') }}" class="mt-4">@csrf<button class="rounded-xl bg-blue-600 text-white px-4 py-2">Kirim Kode Verifikasi</button></form>
    <form method="post" action="{{ route('notification-settings.verify-code') }}" class="mt-4 flex flex-wrap gap-3">@csrf<label for="code" class="sr-only">Kode enam digit</label><input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required placeholder="Kode 6 digit" class="border border-slate-200 rounded-xl px-4 py-2"><button class="border border-slate-200 rounded-xl px-4 py-2">Verifikasi Nomor</button></form>
    @endif</section>
    <form method="post" action="{{ route('notification-settings.update') }}" class="bg-white border border-slate-200 rounded-2xl p-6">@csrf
        <h2 class="font-bold text-xl mb-5">Pemberitahuan WhatsApp</h2>
        <div class="space-y-4">@foreach(config('notification_channels.categories') as $key=>$label)
            <div class="flex flex-wrap items-center justify-between gap-3"><label for="category-{{ $key }}" class="font-semibold">{{ $label }}</label><select id="category-{{ $key }}" name="categories[{{ $key }}]" class="border border-slate-200 rounded-xl px-3 py-2"><option value="off" @selected(($categories[$key] ?? 'off') === 'off')>Nonaktif</option><option value="instant" @selected(($categories[$key] ?? '') === 'instant')>Saat ada pembaruan</option>@if(in_array($key,['news','market']))<option value="daily" @selected(($categories[$key] ?? '') === 'daily')>Ringkasan harian</option>@endif</select></div>
        @endforeach</div>
        <p class="text-sm text-gray-500 mt-4">Ringkasan kabar dan produk dikirim mulai pukul 08.00 WIB. Pengiriman mengikuti antrean dan ketersediaan layanan.</p>
        <label class="flex gap-3 mt-6"><input type="checkbox" name="consent" value="1" @checked($settings?->consented_at)><span>Saya setuju menerima WhatsApp dari SegiDaerah untuk kategori yang saya pilih. Saya dapat menonaktifkannya kapan saja.</span></label>
        <label class="flex gap-3 mt-4"><input type="checkbox" name="push_enabled" value="1" @checked($settings?->push_enabled ?? true)><span>Aktifkan pemberitahuan aplikasi pada perangkat yang terhubung.</span></label>
        <button class="mt-6 bg-blue-600 text-white rounded-xl px-5 py-3">Simpan Pilihan</button>
    </form>
    <section class="bg-white border border-slate-200 rounded-2xl p-6 mt-6"><h2 class="font-bold text-xl">Riwayat Pengiriman</h2><p class="text-sm text-gray-500 mt-2">Diterima penyedia belum berarti pesan sudah sampai. Status diperbarui ketika ada konfirmasi.</p><div class="overflow-x-auto mt-4"><table class="w-full text-sm text-left"><thead><tr><th class="py-2">Kategori</th><th>Saluran</th><th>Status</th><th>Waktu</th></tr></thead><tbody>@forelse($history as $row)<tr class="border-t"><td class="py-3">{{ config('notification_channels.categories.'.$row->category,$row->category) }}</td><td>{{ $row->channel }}</td><td>{{ ['pending'=>'Mengantre','processing'=>'Diproses','accepted'=>'Diterima penyedia','sent'=>'Terkirim','delivered'=>'Sampai','read'=>'Dibaca','failed'=>'Gagal','unknown'=>'Belum terkonfirmasi','cancelled'=>'Dibatalkan'][$row->state] ?? $row->state }}</td><td>{{ $row->updated_at }}</td></tr>@empty<tr><td colspan="4" class="py-4 text-gray-500">Belum ada pengiriman melalui saluran tambahan.</td></tr>@endforelse</tbody></table></div></section>
</main>
@endsection
