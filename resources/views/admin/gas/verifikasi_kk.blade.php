@extends('admin.layouts.admin')
@section('content')
<div class="container-xxl py-4">
    <a href="{{ route('admin.unit.penjualan_gas.index') }}">← Kembali ke Gas Daerah</a>
    <h3 class="mt-3">Periksa Kartu Keluarga</h3>
    <p>Periksa foto, lalu salin nomor KK dan NIK anggotanya. Foto hanya dapat dibuka selama pengajuan menunggu pemeriksaan. Jangan mengunduh atau membagikan foto warga.</p>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    @foreach($pengajuan as $kk)
    <article class="card p-4 mb-4">
        <h5>{{ $kk->submitter?->name ?? 'Pemohon' }}</h5>
        <p>{{ $kk->created_at?->format('d M Y H:i') }} · {{ $kk->submitter?->region?->name }}</p>
        <a href="{{ route('admin.gas.kk.image', $kk->id) }}" target="_blank" rel="noopener" class="btn btn-outline-primary align-self-start mb-3">Lihat Foto KK</a>
        <form method="POST" action="{{ route('admin.gas.kk.approve', $kk->id) }}">
            @csrf
            <label class="form-label" for="kk-{{ $kk->id }}">Nomor KK (16 angka)</label>
            <input id="kk-{{ $kk->id }}" name="no_kk" class="form-control mb-3" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required autocomplete="off">
            <label class="form-label" for="confirm-kk-{{ $kk->id }}">Ketik ulang nomor KK dari bagian atas dokumen (bukan NIK)</label>
            <input id="confirm-kk-{{ $kk->id }}" name="no_kk_confirmation" class="form-control mb-3" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required autocomplete="off">
            <p class="text-muted small">Nomor KK mengidentifikasi keluarga. NIK di bawah adalah milik setiap anggota. Sistem menolak NIK yang sudah terhubung ke KK lain dan mewajibkan NIK pemohon ada di daftar.</p>
            <label class="form-label" for="head-{{ $kk->id }}">Nama kepala keluarga</label>
            <input id="head-{{ $kk->id }}" name="kepala_keluarga" class="form-control mb-3" maxlength="255" required autocomplete="off">
            <label class="form-label" for="niks-{{ $kk->id }}">NIK anggota keluarga — satu NIK per baris, termasuk pemohon</label>
            <textarea id="niks-{{ $kk->id }}" name="niks" class="form-control mb-3" rows="4" required autocomplete="off"></textarea>
            <button class="btn btn-primary" type="submit">Setujui KK</button>
        </form>
        <details class="mt-4"><summary>Foto atau data tidak sesuai?</summary>
            <form method="POST" action="{{ route('admin.gas.kk.reject', $kk->id) }}" class="mt-3">@csrf
                <label for="reason-{{ $kk->id }}">Alasan yang akan diterima warga</label>
                <textarea id="reason-{{ $kk->id }}" name="reason" class="form-control mb-3" required maxlength="500" placeholder="Contoh: nomor KK tidak terbaca. Mohon kirim foto yang lebih jelas."></textarea>
                <button type="submit" class="btn btn-outline-danger">Tolak dengan Alasan</button>
            </form>
        </details>
    </article>
    @endforeach
    @if($pengajuan->isEmpty())<div class="alert alert-info">Tidak ada pengajuan KK yang menunggu pemeriksaan.</div>@endif
    {{ $pengajuan->links() }}
</div>
@endsection
