<section id="notifikasi" class="scroll-mt-28 bg-white border border-slate-200 rounded-2xl p-5 sm:p-7 my-6">
    <h2 class="font-bold text-2xl text-[#115789]">Pengaturan Pemberitahuan</h2>
    <p class="text-gray-600 mt-2 mb-5">Pilih kabar yang ingin dikirim ke WhatsApp. Pemberitahuan di menu lonceng tetap tersedia.</p>
    <div class="rounded-xl bg-blue-50 p-4 mb-5">
        <h3 class="font-semibold">Nomor WhatsApp</h3>
        @if($verified)
            <p class="mt-2">Nomor pada profil sudah terverifikasi. Anda tidak perlu memasukkan kode lagi.</p>
        @elseif(!auth()->user()->phone)
            <p class="mt-2">Nomor WhatsApp belum diisi. Lengkapi nomor pada profil jika ingin menerima pesan WhatsApp.</p>
            <a href="{{ in_array(auth()->user()->role, ['user', 'admin_rt', 'admin_rw'], true) ? route('profile') : route('admin.profile') }}" class="segi-outline inline-flex mt-3">Lengkapi Profil</a>
        @else
            <p class="mt-2 text-sm">Nomor sudah tercatat di profil, tetapi kepemilikannya belum dikonfirmasi melalui WhatsApp. Konfirmasi sekali agar pesan tidak dikirim ke nomor yang salah. Verifikasi identitas dan OTP email tidak menggantikan konfirmasi nomor ini.</p>
            <details class="mt-3"><summary class="font-semibold text-[#115789] cursor-pointer">Konfirmasi nomor WhatsApp</summary>
                <form method="post" action="{{ route('notification-settings.send-code') }}" class="mt-3">@csrf<button class="segi-primary">Kirim Kode ke WhatsApp Saya</button></form>
                <form method="post" action="{{ route('notification-settings.verify-code') }}" class="mt-3 flex flex-wrap items-end gap-3">@csrf
                    <label class="text-sm font-semibold">Kode dari WhatsApp<input name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required placeholder="6 digit" class="block border border-slate-200 bg-white rounded-xl px-4 py-2 mt-1"></label>
                    <button class="segi-outline">Konfirmasi Nomor</button>
                </form>
            </details>
        @endif
    </div>
    <form method="post" action="{{ route('notification-settings.update') }}">@csrf
        <h3 class="font-bold text-lg mb-3">Pesan WhatsApp yang Saya Terima</h3>
        <div class="grid md:grid-cols-2 gap-4">@foreach(config('notification_channels.categories') as $key=>$label)
            <label class="block rounded-xl border border-slate-200 p-3"><span class="block font-semibold mb-2">{{ $label }}</span><select name="categories[{{ $key }}]" class="w-full border border-slate-200 bg-white rounded-xl px-3 py-2"><option value="off" @selected(($categories[$key] ?? 'off') === 'off')>Tidak dikirim ke WhatsApp</option><option value="instant" @selected(($categories[$key] ?? '') === 'instant')>Kirim saat ada kabar baru</option>@if(in_array($key,['news','market']))<option value="daily" @selected(($categories[$key] ?? '') === 'daily')>Rangkum sekali sehari</option>@endif</select></label>
        @endforeach</div>
        <p class="text-sm text-gray-500 mt-3">Jika memilih rangkuman, pesan mulai dikirim pukul 08.00 WIB. Waktu diterima dapat berbeda.</p>
        <label class="flex items-start gap-3 mt-5"><input type="checkbox" name="consent" value="1" class="mt-1" @checked($settings?->consented_at)><span>Saya ingin menerima pesan WhatsApp sesuai pilihan di atas. Pilihan ini dapat saya matikan kapan saja.</span></label>
        @if(auth()->user()->fcm_token)
        <label class="flex gap-3 mt-4"><input type="checkbox" name="push_enabled" value="1" @checked($settings?->push_enabled ?? true)><span>Tampilkan pemberitahuan pada aplikasi SegiDaerah di HP saya.</span></label>
        @else<input type="hidden" name="push_enabled" value="{{ ($settings?->push_enabled ?? true) ? '1' : '0' }}">@endif
        <button class="mt-5 segi-primary">Simpan Pengaturan Pemberitahuan</button>
    </form>
    @if($history->isNotEmpty())
    <details class="border-t border-slate-200 pt-4 mt-6"><summary class="cursor-pointer font-semibold text-[#115789]">Lihat Riwayat Pemberitahuan</summary>
        <p class="text-sm text-gray-500 mt-2">Lihat apakah pesan masih menunggu, sudah sampai, atau gagal dikirim.</p>
        <ul class="mt-3 space-y-2">@foreach($history as $row)<li class="flex flex-wrap justify-between gap-2 rounded-xl bg-slate-50 p-3 text-sm"><span>{{ config('notification_channels.categories.'.$row->category,'Pemberitahuan') }} · {{ $row->channel === 'whatsapp' ? 'WhatsApp' : 'Aplikasi' }}</span><span>{{ ['pending'=>'Menunggu pengiriman','processing'=>'Sedang dikirim','accepted'=>'Menunggu konfirmasi pengiriman','sent'=>'Terkirim','delivered'=>'Sampai','read'=>'Dibaca','failed'=>'Gagal dikirim','unknown'=>'Status belum tersedia','cancelled'=>'Dibatalkan'][$row->state] ?? 'Status belum tersedia' }} · {{ $row->updated_at }}</span></li>@endforeach</ul>
    </details>
    @endif
</section>
