<style>
    .product-card {
        position: relative;
        display: flex;
        height: 100%;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e8edf2;
        border-radius: 1.25rem;
        background: #fff;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .045);
        transition: box-shadow .2s ease, border-color .2s ease, transform .2s ease;
    }
    .product-card:hover {
        transform: translateY(-3px);
        border-color: #cbdce9;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .09);
    }
    .product-image-wrapper {
        position: relative;
        display: flex;
        width: 100%;
        aspect-ratio: 4 / 3;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 1rem;
        background: #f8fafc;
    }
    .product-image-wrapper > .product-image {
        width: 100%;
        height: 100%;
        padding: 0;
        object-fit: cover !important;
        transition: transform .25s ease;
    }
    .product-image-wrapper > .product-image.image-fallback { object-fit: contain !important; }
    .product-item:hover .product-image { transform: scale(1.05); }
    .product-name {
        margin-top: 0;
        color: #172b3d;
        font-size: clamp(.9rem, 1.4vw, 1.05rem);
        font-weight: 750;
        line-height: 1.35;
    }
    .catalog-filter-btn {
        display: inline-flex;
        min-height: 2.5rem;
        align-items: center;
        justify-content: center;
        border: 1px solid #d9e1e8;
        border-radius: 999px;
        background: #fff;
        padding: .55rem 1rem;
        color: #475569;
        font-size: .875rem;
        font-weight: 650;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease;
    }
    .catalog-filter-btn:hover:not(.active) {
        border-color: #b8cfdf;
        background: #f3f8fb;
        color: #115789;
    }
    .catalog-filter-btn.active {
        border-color: #115789;
        background: #115789;
        color: #fff;
        box-shadow: 0 2px 6px rgba(17, 87, 137, .18);
    }
    .catalog-card-cta {
        display: flex;
        min-height: 2.5rem;
        align-items: center;
        justify-content: center;
        margin-top: 1rem;
        border-radius: .7rem;
        padding: .55rem .75rem;
        color: #fff;
        font-size: .85rem;
        font-weight: 750;
        line-height: 1.2;
        text-align: center;
        transition: filter .15s ease;
    }
    .product-item:hover .catalog-card-cta { filter: brightness(.94); }
    @media (max-width: 639px) {
        .product-card { border-radius: 1rem; }
        .product-image-wrapper { border-radius: .75rem; }
        .catalog-filter-btn { min-height: 2.25rem; padding: .45rem .75rem; font-size: .8rem; }
        .catalog-card-cta { min-height: 2.2rem; margin-top: .75rem; padding: .45rem .5rem; font-size: .75rem; }
    }
</style>
