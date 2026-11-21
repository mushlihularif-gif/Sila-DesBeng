@php
    $galleryId = $galleryId ?? 'productGallery';
    $galleryPhotos = collect($photos ?? [])->filter()->values();
    $fallbackIcon = $fallbackIcon ?? 'bx-image';
    $altText = $altText ?? 'Foto Produk';
@endphp

<div class="up-gallery" data-up-gallery="{{ $galleryId }}">
    <div class="up-gallery-stage">
        @if($galleryPhotos->isNotEmpty())
            <img src="{{ $galleryPhotos->first() }}"
                 alt="{{ $altText }}"
                 data-gallery-main
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="w-100 h-100 align-items-center justify-content-center flex-column text-muted gap-2" style="display: none;">
                <i class="bx {{ $fallbackIcon }}" style="font-size: 3.25rem; color: #cbd5e1;"></i>
                <span class="small">Foto tidak tersedia</span>
            </div>
        @else
            <div class="w-100 h-100 d-flex align-items-center justify-content-center flex-column text-muted gap-2">
                <i class="bx {{ $fallbackIcon }}" style="font-size: 3.25rem; color: #cbd5e1;"></i>
                <span class="small">Belum ada foto diunggah</span>
            </div>
        @endif

        @if(!empty($statusBadgeHtml))
            <div class="position-absolute top-0 start-0 m-2.5" style="z-index: 3;">
                {!! $statusBadgeHtml !!}
            </div>
        @endif

        @if(!empty($stockBadgeHtml))
            <div class="position-absolute top-0 end-0 m-2.5" style="z-index: 3;">
                {!! $stockBadgeHtml !!}
            </div>
        @endif

        @if($galleryPhotos->count() > 1)
            <button type="button" class="up-gallery-nav prev" data-gallery-prev aria-label="Foto sebelumnya">
                <i class="bx bx-chevron-left fs-5"></i>
            </button>
            <button type="button" class="up-gallery-nav next" data-gallery-next aria-label="Foto berikutnya">
                <i class="bx bx-chevron-right fs-5"></i>
            </button>
            <span class="badge bg-dark bg-opacity-75 rounded-pill position-absolute bottom-0 end-0 m-2.5 px-2.5 py-1 fw-medium" style="font-size: 0.7rem; z-index: 3;" data-gallery-counter>
                1 / {{ $galleryPhotos->count() }}
            </span>
        @endif
    </div>

    @if($galleryPhotos->count() > 1)
        <div class="d-flex align-items-center gap-2 mt-2.5 overflow-x-auto pb-1">
            @foreach($galleryPhotos as $idx => $url)
                <button type="button"
                        class="up-thumb-btn {{ $idx === 0 ? 'active' : '' }}"
                        data-gallery-thumb="{{ $idx }}"
                        data-gallery-src="{{ $url }}"
                        aria-label="Lihat foto {{ $idx + 1 }}">
                    <img src="{{ $url }}" alt="{{ $altText }} ({{ $idx + 1 }})">
                </button>
            @endforeach
        </div>
    @endif
</div>

@once
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-up-gallery]').forEach(function (root) {
            const mainImg = root.querySelector('[data-gallery-main]');
            const thumbs = Array.from(root.querySelectorAll('[data-gallery-thumb]'));
            if (!mainImg || thumbs.length <= 1) return;

            const counter = root.querySelector('[data-gallery-counter]');
            let current = 0;

            function showAt(index) {
                current = (index + thumbs.length) % thumbs.length;
                const src = thumbs[current].getAttribute('data-gallery-src');
                if (src) {
                    mainImg.style.display = 'block';
                    if (mainImg.nextElementSibling) mainImg.nextElementSibling.style.display = 'none';
                    mainImg.src = src;
                }
                thumbs.forEach(function (t, i) {
                    t.classList.toggle('active', i === current);
                });
                if (counter) {
                    counter.textContent = (current + 1) + ' / ' + thumbs.length;
                }
            }

            thumbs.forEach(function (thumb, idx) {
                thumb.addEventListener('click', function () { showAt(idx); });
            });
            root.querySelector('[data-gallery-prev]')?.addEventListener('click', function () { showAt(current - 1); });
            root.querySelector('[data-gallery-next]')?.addEventListener('click', function () { showAt(current + 1); });
        });
    });
</script>
@endpush
@endonce
