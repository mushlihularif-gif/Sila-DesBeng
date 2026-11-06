@php
    $shippingForm = old('shipping', $shippingConfig);
    $zoneLabels = ['village' => 'Dalam desa toko', 'district' => 'Desa lain di kecamatan yang sama', 'outside' => 'Luar kecamatan'];
@endphp
<section data-market-shipping-editor class="mb-4" aria-labelledby="shipping-title">
    <h5 id="shipping-title" class="fw-bold">Cara pembeli menerima barang</h5>
    <p class="text-muted">Pilih layanan yang bisa toko Anda sediakan. Ongkir berlaku sekali untuk satu pesanan.</p>
    @if($errors->any())
        <div class="alert alert-danger" role="alert"><strong>Periksa isian sebelum menyimpan.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @foreach(['pickup' => 'Ambil di toko — tanpa ongkir', 'delivery' => 'Diantar oleh toko'] as $key => $label)
        <div class="form-check mb-3">
            <input type="hidden" name="shipping[{{ $key }}]" value="0">
            <input type="checkbox" class="form-check-input" id="shipping-{{ $key }}" name="shipping[{{ $key }}]" value="1" data-shipping-method="{{ $key }}" @checked($shippingForm[$key] ?? false)>
            <label class="form-check-label fw-semibold" for="shipping-{{ $key }}">{{ $label }}</label>
        </div>
    @endforeach
    <div data-delivery-options @if(!($shippingForm['delivery'] ?? false)) hidden @endif>
        <h6 class="fw-bold mt-4">Ke mana toko bisa mengantar?</h6>
        @foreach($zoneLabels as $key => $label)
            <fieldset class="border rounded-3 p-3 mb-3" data-shipping-zone="{{ $key }}" data-label="{{ $label }}">
                <legend class="float-none w-auto fs-6 px-1 mb-0">{{ $label }}</legend>
                <div class="form-check mb-2">
                    <input type="hidden" name="shipping[{{ $key }}][enabled]" value="0">
                    <input type="checkbox" class="form-check-input" id="shipping-{{ $key }}-enabled" name="shipping[{{ $key }}][enabled]" value="1" data-zone-enabled @checked($shippingForm[$key]['enabled'] ?? false)>
                    <label class="form-check-label" for="shipping-{{ $key }}-enabled">Toko melayani pengiriman ke wilayah ini</label>
                </div>
                <div data-zone-price class="row g-2 align-items-end" @if(!($shippingForm[$key]['enabled'] ?? false)) hidden @endif>
                    <div class="col-sm-5">
                        <label class="form-label" for="shipping-{{ $key }}-mode">Ongkir</label>
                        <select class="form-select" id="shipping-{{ $key }}-mode" data-fee-mode>
                            <option value="free" @selected(($shippingForm[$key]['fee'] ?? 0) == 0)>Gratis</option>
                            <option value="paid" @selected(($shippingForm[$key]['fee'] ?? 0) > 0)>Isi ongkir</option>
                        </select>
                    </div>
                    <div class="col-sm-7" data-fee-field @if(($shippingForm[$key]['fee'] ?? 0) == 0) hidden @endif>
                        <label class="form-label" for="shipping-{{ $key }}-fee">Ongkir per pesanan</label>
                        <div class="input-group"><span class="input-group-text">Rp</span><input type="number" class="form-control" id="shipping-{{ $key }}-fee" name="shipping[{{ $key }}][fee]" data-fee min="0" max="10000000" step="1" value="{{ $shippingForm[$key]['fee'] ?? 0 }}"></div>
                    </div>
                </div>
                <small class="text-muted" data-not-served @if($shippingForm[$key]['enabled'] ?? false) hidden @endif>Belum melayani wilayah ini.</small>
            </fieldset>
        @endforeach
        <details class="border rounded-3 p-3 mb-3" @if(count($shippingForm['overrides'] ?? [])) open @endif>
            <summary class="fw-semibold">Atur ongkir berbeda untuk wilayah tertentu</summary>
            <p class="small text-muted mt-2">Opsional. Contoh: hanya Kecamatan Bantan Rp15.000. Tarif khusus desa lebih diutamakan daripada tarif kecamatan.</p>
            <div data-overrides>
                @foreach($shippingForm['overrides'] ?? [] as $index => $override)
                    @include('admin.unit.pasar_daerah.shipping-override', ['rowKey' => $index, 'row' => $override])
                @endforeach
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" data-add-override>Tambah wilayah</button>
            <template data-override-template>@include('admin.unit.pasar_daerah.shipping-override', ['rowKey' => '__INDEX__', 'row' => []])</template>
        </details>
    </div>
    <div class="bg-light border rounded-3 p-3" role="status" aria-live="polite" aria-atomic="true">
        <strong>Pengiriman toko Anda</strong>
        <ul data-shipping-summary class="mb-0 mt-2"></ul>
    </div>
    <p class="small text-muted mt-2 mb-0">Perubahan berlaku untuk pesanan baru. Ongkir pesanan yang sudah dibuat tetap sama.</p>
</section>
