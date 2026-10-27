<style>
    .catalog-detail-section { background: linear-gradient(180deg, #f4f8fb 0%, #fff 24rem); }
    .catalog-detail-header { margin: 0 auto 1.5rem; max-width: 72rem; }
    .catalog-detail-heading { margin: 0; color: #115789; font-size: clamp(1.35rem, 2.5vw, 1.8rem); font-weight: 800; letter-spacing: -.02em; }
    .catalog-detail-card { border: 1px solid #e2e8f0; border-radius: 1.5rem; background: #fff; box-shadow: 0 8px 28px rgba(15, 23, 42, .07); padding: clamp(1rem, 3vw, 2rem); }
    .catalog-detail-gallery { position: relative; aspect-ratio: 1 / 1; width: 100%; overflow: hidden; border: 1px solid #e8edf2; border-radius: 1rem; background: #f8fafc; }
    .catalog-detail-gallery #product-carousel { height: 100%; }
    .catalog-detail-gallery #product-carousel > div { min-width: 100%; flex: 0 0 100%; }
    .catalog-detail-gallery .product-image { width: 100%; height: 100%; padding: 0; object-fit: cover !important; transition: transform .25s ease; }
    .catalog-detail-gallery:hover .product-image { transform: scale(1.05); }
    .catalog-detail-gallery .gas-product-photo { transform: none; }
    .catalog-detail-gallery:focus-visible { outline: 3px solid #60a5fa; outline-offset: 3px; }
    .product-gallery-arrow { position: absolute; top: 50%; z-index: 20; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; padding: 0; transform: translateY(-50%); border: 1px solid #e2e8f0; border-radius: 50%; color: #115789; background: rgba(255,255,255,.96); box-shadow: 0 3px 12px rgba(15,23,42,.2); cursor: pointer; }
    .product-gallery-prev { left: 12px; }
    .product-gallery-next { right: 12px; }
    .product-gallery-arrow:hover { color: #fff; background: #115789; border-color: #115789; }
    .product-gallery-arrow svg { width: 22px; height: 22px; fill: none; stroke: currentColor; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }
    .product-gallery-arrow:focus-visible, .product-gallery-dot:focus-visible { outline: 3px solid #60a5fa; outline-offset: 3px; }
    .product-gallery-pagination { position: absolute; bottom: 12px; left: 50%; z-index: 20; display: flex; align-items: center; transform: translateX(-50%); padding: 0 8px; border-radius: 999px; background: rgba(15,23,42,.72); color: #fff; }
    .product-gallery-dot { display: flex; align-items: center; justify-content: center; width: 36px; height: 44px; padding: 0; border: 0; background: transparent; cursor: pointer; }
    .product-gallery-dot span { width: 9px; height: 9px; border-radius: 999px; background: rgba(255,255,255,.5); transition: width .2s ease; }
    .product-gallery-dot[aria-current="true"] span { width: 24px; background: #fff; }
    .product-gallery-counter { padding: 0 6px; white-space: nowrap; font-size: .75rem; font-weight: 700; font-variant-numeric: tabular-nums; }
    @media (prefers-reduced-motion: reduce) { .catalog-detail-gallery #product-carousel, .catalog-detail-gallery .product-image, .product-gallery-dot span { transition: none; } }
    .catalog-detail-name { color: #172b3d; font-size: clamp(1.4rem, 2.4vw, 1.85rem); font-weight: 800; line-height: 1.2; letter-spacing: -.02em; }
    .catalog-detail-price { color: #2563eb !important; font-size: clamp(1.5rem, 2.5vw, 2rem); font-weight: 850; }
    .catalog-detail-primary { background: #2563eb !important; }
    .catalog-detail-primary:hover { background: #1d4ed8 !important; }
    .catalog-detail-dots { display: flex; justify-content: center; gap: .5rem; padding: .75rem 0 .25rem; }
</style>
