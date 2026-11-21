@php
    $categoryType = $categoryType ?? 'barang';
    $categoryPlaceholder = $categoryPlaceholder ?? 'Contoh: Perlengkapan Pesta';
    $satuanPlaceholder = $satuanPlaceholder ?? 'Contoh: Paket';
    $includeSatuanModal = $includeSatuanModal ?? true;
@endphp

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white border-bottom px-4 py-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="up-icon-box bg-label-primary">
                        <i class="bx bx-category fs-5 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="addCategoryModalLabel">Tambah Kategori Baru</h6>
                        <small class="text-muted">Langsung ditambahkan ke daftar pilihan kategori</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4 up-form">
                <label for="new_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="new_kategori" placeholder="{{ $categoryPlaceholder }}">
                <div id="new_kategori_err" class="text-danger small mt-1.5 d-none"></div>
            </div>
            <div class="modal-footer bg-light border-top px-4 py-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" id="saveCategoryBtn">
                    <i class="bx bx-check me-1"></i>Simpan Kategori
                </button>
            </div>
        </div>
    </div>
</div>

@if($includeSatuanModal)
<!-- Modal Tambah Satuan -->
<div class="modal fade" id="addSatuanModal" tabindex="-1" aria-labelledby="addSatuanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white border-bottom px-4 py-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="up-icon-box bg-label-info">
                        <i class="bx bx-ruler fs-5 text-info"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="addSatuanModalLabel">Tambah Satuan Baru</h6>
                        <small class="text-muted">Tambahkan satuan hitung sesuai kebutuhan produk</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4 up-form">
                <label for="new_satuan" class="form-label">Nama Satuan <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="new_satuan" placeholder="{{ $satuanPlaceholder }}">
                <div id="new_satuan_err" class="text-danger small mt-1.5 d-none"></div>
            </div>
            <div class="modal-footer bg-light border-top px-4 py-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" id="saveSatuanBtn">
                    <i class="bx bx-check me-1"></i>Simpan Satuan
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@once
@push('scripts')
<script>
    function formatRupiah(input) {
        if (!input) return;
        let value = input.value.replace(/\D/g, '');
        if (value) {
            input.value = new Intl.NumberFormat('id-ID').format(value);
        } else {
            input.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.format-rupiah').forEach(function (el) {
            el.addEventListener('input', function () { formatRupiah(this); });
        });

        const saveCatBtn = document.getElementById('saveCategoryBtn');
        if (saveCatBtn) {
            saveCatBtn.addEventListener('click', function () {
                const input = document.getElementById('new_kategori');
                const errEl = document.getElementById('new_kategori_err');
                const newKategori = input ? input.value.trim() : '';

                if (errEl) { errEl.classList.add('d-none'); errEl.textContent = ''; }
                if (!newKategori) {
                    if (errEl) { errEl.textContent = 'Silakan masukkan nama kategori terlebih dahulu.'; errEl.classList.remove('d-none'); }
                    input?.focus();
                    return;
                }

                const originalText = saveCatBtn.innerHTML;
                saveCatBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
                saveCatBtn.disabled = true;

                fetch('{{ route("admin.categories.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: newKategori, type: '{{ $categoryType }}' })
                })
                .then(response => response.json())
                .then(data => {
                    saveCatBtn.innerHTML = originalText;
                    saveCatBtn.disabled = false;

                    if (data.success && data.category) {
                        const select = document.getElementById('kategori');
                        if (select) {
                            let existing = Array.from(select.options).find(o => o.value === data.category.name);
                            if (!existing) {
                                existing = document.createElement('option');
                                existing.value = data.category.name;
                                existing.textContent = data.category.name;
                                select.appendChild(existing);
                            }
                            select.value = data.category.name;
                        }
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('addCategoryModal'))?.hide();
                        if (input) input.value = '';
                    } else if (errEl) {
                        errEl.textContent = data.message || 'Gagal menyimpan kategori.';
                        errEl.classList.remove('d-none');
                    }
                })
                .catch(() => {
                    saveCatBtn.innerHTML = originalText;
                    saveCatBtn.disabled = false;
                    if (errEl) {
                        errEl.textContent = 'Terjadi kesalahan jaringan. Coba lagi.';
                        errEl.classList.remove('d-none');
                    }
                });
            });
        }

        const saveSatuanBtn = document.getElementById('saveSatuanBtn');
        if (saveSatuanBtn) {
            saveSatuanBtn.addEventListener('click', function () {
                const input = document.getElementById('new_satuan');
                const errEl = document.getElementById('new_satuan_err');
                const newSatuan = input ? input.value.trim() : '';

                if (errEl) { errEl.classList.add('d-none'); errEl.textContent = ''; }
                if (!newSatuan) {
                    if (errEl) { errEl.textContent = 'Silakan masukkan nama satuan terlebih dahulu.'; errEl.classList.remove('d-none'); }
                    input?.focus();
                    return;
                }

                const select = document.getElementById('satuan');
                if (select) {
                    let existing = Array.from(select.options).find(o => o.value.toLowerCase() === newSatuan.toLowerCase());
                    if (!existing) {
                        existing = document.createElement('option');
                        existing.value = newSatuan;
                        existing.textContent = newSatuan;
                        select.appendChild(existing);
                    }
                    select.value = existing.value;
                }
                bootstrap.Modal.getOrCreateInstance(document.getElementById('addSatuanModal'))?.hide();
                if (input) input.value = '';
            });
        }
    });
</script>
@endpush
@endonce
