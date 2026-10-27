@if($images->count() > 1)
    <div class="product-gallery-navigation" data-autoplay="{{ ($galleryAutoplay ?? true) ? 'true' : 'false' }}">
        <button type="button" class="product-gallery-arrow product-gallery-prev" aria-label="Foto sebelumnya" aria-controls="product-carousel">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        </button>
        <button type="button" class="product-gallery-arrow product-gallery-next" aria-label="Foto berikutnya" aria-controls="product-carousel">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        </button>
        <div class="product-gallery-pagination">
            @foreach($images as $index => $image)
                <button type="button" class="product-gallery-dot" data-slide="{{ $index }}" aria-label="Lihat foto {{ $index + 1 }}" aria-controls="product-carousel" aria-current="{{ $index === 0 ? 'true' : 'false' }}"><span></span></button>
            @endforeach
            <span class="product-gallery-counter" aria-label="Foto 1 dari {{ $images->count() }}">1 / {{ $images->count() }}</span>
        </div>
    </div>
@endif

@once
    @push('scripts')
        @include('users.partials.product-gallery-scripts')
    @endpush
@endonce
