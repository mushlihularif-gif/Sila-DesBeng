@php
    $item = $item ?? null;
    $primaryName = $primaryName ?? 'foto_utama';
    $primaryRequired = $primaryRequired ?? false;
    $isEdit = $item && ($item->exists || !empty($item->id));
    $cropRatioExpr = $cropRatioExpr ?? '4 / 3';
    $aspectClass = $aspectClass ?? '';
    $altPrefix = $altPrefix ?? 'Foto Produk';

    $slots = [
        ['id' => 'foto',   'name' => $primaryName, 'field' => 'foto',   'label' => 'Foto Utama',      'required' => $primaryRequired && !$isEdit],
        ['id' => 'foto_2', 'name' => 'foto_2',     'field' => 'foto_2', 'label' => 'Foto Tambahan 1', 'required' => false],
        ['id' => 'foto_3', 'name' => 'foto_3',     'field' => 'foto_3', 'label' => 'Foto Tambahan 2', 'required' => false],
    ];
@endphp

<div class="row g-3">
    @foreach($slots as $slot)
        @php
            $existingPath = $item ? ($item->{$slot['field']} ?? null) : null;
            $hasExisting = !empty($existingPath);
        @endphp
        <div class="col-6 col-md-4">
            <div class="d-flex align-items-center justify-content-between mb-1.5">
                <label class="form-label mb-0" for="{{ $slot['id'] }}">
                    {{ $slot['label'] }}
                    @if($slot['required'])
                        <span class="text-danger">*</span>
                    @elseif($slot['id'] !== 'foto')
                        <span class="text-muted fw-normal" style="font-size: 0.74rem;">(Opsional)</span>
                    @endif
                </label>
            </div>

            <label for="{{ $slot['id'] }}" class="upload-box {{ $aspectClass }} m-0">
                <div id="preview_{{ $slot['id'] }}" class="preview-container" style="{{ $hasExisting ? '' : 'display:none;' }}">
                    <img src="{{ $hasExisting ? asset('storage/' . $existingPath) : '#' }}"
                         alt="{{ $altPrefix }}"
                         class="preview-image" />
                    <button type="button"
                            class="btn-remove-image"
                            title="Hapus foto"
                            aria-label="Hapus {{ $slot['label'] }}"
                            onclick="event.preventDefault(); event.stopPropagation(); clearFile('{{ $slot['id'] }}', 'preview_{{ $slot['id'] }}')">
                        <i class="bx bx-x fs-5"></i>
                    </button>
                </div>
                <div id="placeholder_{{ $slot['id'] }}" class="upload-placeholder" style="{{ $hasExisting ? 'display:none;' : '' }}">
                    <i class="bx bx-cloud-upload d-block"></i>
                    <p class="mb-0 mt-1 fw-semibold text-dark" style="font-size: 0.78rem;">Pilih Foto</p>
                    <small class="text-muted d-block" style="font-size: 0.7rem;">JPG, PNG, WebP · Maks. 8MB</small>
                </div>
            </label>

            <input type="file"
                   class="d-none"
                   id="{{ $slot['id'] }}"
                   name="{{ $slot['name'] }}"
                   accept="image/jpeg,image/png,image/webp,image/gif"
                   onchange="previewFile(this, 'preview_{{ $slot['id'] }}', 'placeholder_{{ $slot['id'] }}')" />

            @if($isEdit)
                <input type="hidden" name="delete_{{ $slot['id'] }}" id="delete_{{ $slot['id'] }}" value="0">
            @endif

            @error($slot['name'])
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>
    @endforeach
</div>

@once
@push('scripts')
<script>
    window._unitPhotoCropRatio = {{ $cropRatioExpr }};

    function previewFile(input, previewId, placeholderId) {
        if (!input || !input.files || !input.files[0]) return;

        const file = input.files[0];
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);
        const img = preview ? (preview.tagName === 'IMG' ? preview : preview.querySelector('img')) : null;
        const inputId = input.getAttribute('id');
        const deleteInput = document.getElementById('delete_' + inputId);

        if (deleteInput) {
            deleteInput.value = '0';
        }

        if (typeof initGlobalCropper === 'function') {
            try {
                const ratio = typeof window._unitPhotoCropRatio !== 'undefined' ? window._unitPhotoCropRatio : (4 / 3);
                const started = initGlobalCropper(input, img || previewId, ratio, true);
                if (started !== false) {
                    return;
                }
            } catch (err) {
                console.warn('Cropper fallback:', err);
            }
        }

        const url = URL.createObjectURL(file);
        if (img) img.src = url;
        if (preview) {
            preview.style.display = 'block';
            preview.classList.remove('d-none');
        }
        if (placeholder) {
            placeholder.style.display = 'none';
        }
    }

    function clearFile(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById('placeholder_' + inputId);
        const img = preview ? preview.querySelector('img') : null;
        const deleteInput = document.getElementById('delete_' + inputId);

        if (input) input.value = '';
        if (img) img.src = '#';
        if (preview) preview.style.display = 'none';
        if (placeholder) placeholder.style.display = 'block';
        if (deleteInput) deleteInput.value = '1';
    }
</script>
@endpush
@endonce
