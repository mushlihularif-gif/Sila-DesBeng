<div class="row g-2 border-bottom pb-3 mb-3" data-override-row>
    <div class="col-sm-6">
        <label class="form-label" for="shipping-override-{{ $rowKey }}">Wilayah tujuan</label>
        <select class="form-select" id="shipping-override-{{ $rowKey }}" name="shipping[overrides][{{ $rowKey }}][region_id]" required data-override-region>
            <option value="">Pilih desa atau kecamatan</option>
            @foreach($shippingRegions as $destination)
                <option value="{{ $destination->id }}" @selected(($row['region_id'] ?? '') == $destination->id)>{{ $destination->name }}{{ $destination->type !== 'kecamatan' ? ' — '.$destination->parent?->name : '' }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-4">
        <label class="form-label" for="shipping-override-fee-{{ $rowKey }}">Ongkir (Rp)</label>
        <input class="form-control" id="shipping-override-fee-{{ $rowKey }}" name="shipping[overrides][{{ $rowKey }}][fee]" type="number" min="0" max="10000000" step="1" required value="{{ $row['fee'] ?? 0 }}" data-override-fee>
        <small class="text-muted">Isi 0 untuk gratis.</small>
    </div>
    <div class="col-sm-2 d-flex align-items-center"><button class="btn btn-outline-danger btn-sm" type="button" data-remove-override>Hapus</button></div>
</div>
