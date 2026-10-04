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
    .catalog-detail-name { color: #172b3d; font-size: clamp(1.4rem, 2.4vw, 1.85rem); font-weight: 800; line-height: 1.2; letter-spacing: -.02em; }
    .catalog-detail-price { color: #115789 !important; font-size: clamp(1.5rem, 2.5vw, 2rem); font-weight: 850; }
    .catalog-detail-primary { background: #115789 !important; }
    .catalog-detail-primary:hover { background: #0d4267 !important; }
    .catalog-detail-dots { display: flex; justify-content: center; gap: .5rem; padding: .75rem 0 .25rem; }
</style>
