@push('styles')
<style>
    .up-card {
        border-radius: 16px;
        border: 1px solid #eef1f6;
        background: #ffffff;
        box-shadow: 0 2px 12px rgba(67, 89, 113, 0.05);
    }
    .up-card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f4f9;
        padding: 1rem 1.25rem;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
    }
    .up-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .up-step-num {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .up-form .form-label {
        font-weight: 600;
        font-size: 0.84rem;
        color: #334155;
        margin-bottom: 0.4rem;
    }
    .up-form .form-control,
    .up-form .form-select {
        border: 1px solid #d9e1ec;
        border-radius: 10px;
        padding: 0.58rem 0.85rem;
        font-size: 0.9rem;
        color: #1e293b;
        background-color: #ffffff;
        transition: border-color 0.16s ease, box-shadow 0.16s ease;
    }
    .up-form .form-control:focus,
    .up-form .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3.5px rgba(13, 110, 253, 0.11);
    }
    .up-form .input-group .form-control,
    .up-form .input-group .form-select {
        border-radius: 10px;
    }
    .up-form .input-group > .input-group-text:first-child {
        border: 1px solid #d9e1ec;
        border-right: 0;
        border-radius: 10px 0 0 10px;
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
    }
    .up-form .input-group > .input-group-text:first-child + .form-control,
    .up-form .input-group > .input-group-text:first-child + .form-select {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    .up-form .input-group > .form-select:first-child,
    .up-form .input-group > .form-control:first-child {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    .up-form .input-group > .btn:last-child {
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }
    .up-sticky {
        position: static;
    }
    @media (min-width: 992px) {
        .up-sticky {
            position: sticky;
            top: 86px;
        }
    }

    /* Gallery Viewer (Show Pages) */
    .up-gallery-stage {
        position: relative;
        width: 100%;
        aspect-ratio: 4 / 3;
        max-height: 360px;
        border-radius: 14px;
        overflow: hidden;
        background: #f4f7fb;
        border: 1px solid #eef1f6;
    }
    .up-gallery-stage img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
    }
    .up-gallery-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 0;
        background: rgba(15, 23, 42, 0.52);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
        transition: background 0.16s ease, transform 0.16s ease;
        z-index: 3;
    }
    .up-gallery-nav:hover {
        background: rgba(15, 23, 42, 0.78);
        color: #ffffff;
    }
    .up-gallery-nav.prev { left: 10px; }
    .up-gallery-nav.next { right: 10px; }
    .up-thumb-btn {
        width: 68px;
        height: 52px;
        padding: 0;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid transparent;
        background: #f1f5f9;
        opacity: 0.72;
        transition: all 0.16s ease;
        flex-shrink: 0;
    }
    .up-thumb-btn img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .up-thumb-btn.active,
    .up-thumb-btn:hover {
        opacity: 1;
        border-color: #0d6efd;
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);
    }

    /* Detail Key-Value Tiles & Price Banner */
    .up-price-banner {
        border-radius: 14px;
        background: linear-gradient(135deg, #f0f6ff 0%, #f8fbff 100%);
        border: 1px solid #dbeafe;
        padding: 1rem 1.25rem;
    }
    .up-spec-tile {
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #eef1f6;
        padding: 0.85rem 1rem;
        height: 100%;
    }
    .up-spec-label {
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 0.25rem;
    }

    /* Photo Upload Slots (Compatible with initGlobalCropper) */
    .upload-box {
        border: 1.75px dashed #cbd5e1;
        border-radius: 12px;
        padding: 12px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.18s ease, background-color 0.18s ease;
        background: #f8fafc;
        aspect-ratio: 4 / 3;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .upload-box.ratio-16x9 {
        aspect-ratio: 16 / 10;
    }
    .upload-box:hover {
        border-color: #0d6efd;
        background: #f0f6ff;
    }
    .upload-placeholder {
        color: #64748b;
        padding: 4px;
    }
    .upload-placeholder i {
        color: #94a3b8;
        font-size: 1.9rem;
        transition: color 0.18s ease, transform 0.18s ease;
    }
    .upload-box:hover .upload-placeholder i {
        color: #0d6efd;
        transform: translateY(-2px);
    }
    .preview-container {
        width: 100%;
        height: 100%;
        position: absolute;
        inset: 0;
    }
    .preview-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .btn-remove-image {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(220, 53, 69, 0.92);
        color: #ffffff;
        border: 2px solid #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
        transition: transform 0.15s ease, background-color 0.15s ease;
        z-index: 5;
        padding: 0;
    }
    .btn-remove-image:hover {
        background: #bb2d3b;
        transform: scale(1.08);
    }

    /* Interactive Option Cards */
    .up-choice-card {
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        padding: 0.9rem 1rem;
        cursor: pointer;
        transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
        height: 100%;
    }
    .up-choice-card:hover {
        border-color: #93c5fd;
        background: #fafcff;
    }
    .up-choice-card.is-active-primary {
        border-color: #0d6efd !important;
        background: #f0f6ff !important;
        box-shadow: 0 4px 14px rgba(13, 110, 253, 0.1);
    }
    .up-choice-card.is-active-success {
        border-color: #16a34a !important;
        background: #f0fdf4 !important;
        box-shadow: 0 4px 14px rgba(22, 163, 74, 0.1);
    }

    /* Mode Segmented Switch */
    .up-mode-group {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        background: #f1f5f9;
        padding: 0.3rem;
        border-radius: 12px;
    }
    .up-mode-label {
        cursor: pointer;
        padding: 0.42rem 0.9rem;
        border-radius: 9px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        transition: all 0.16s ease;
        margin: 0;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .btn-check:checked + .up-mode-label {
        background: #ffffff;
        color: #0d6efd;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
    }

    /* Pengurus Selector Card */
    .pengurus-profile-card {
        border-radius: 12px;
        background: #ffffff;
        cursor: pointer;
        border: 1.5px solid #e2e8f0 !important;
        transition: all 0.18s ease;
    }
    .pengurus-profile-card:hover {
        border-color: #86efac !important;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .pengurus-profile-card.selected {
        border-color: #16a34a !important;
        background: #f0fdf4 !important;
        box-shadow: 0 4px 14px rgba(22, 163, 74, 0.12);
    }

    /* SOP Option Card */
    .up-sop-card {
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
        height: 100%;
    }
    .up-sop-card.active-sop {
        border-color: #0d6efd !important;
        background: #f8fbff !important;
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.12);
    }

    @media (max-width: 575.98px) {
        .up-card-header { padding: 0.85rem 1rem; }
        .up-gallery-stage { max-height: 260px; }
        .up-thumb-btn { width: 58px; height: 44px; }
        .upload-placeholder i { font-size: 1.45rem; }
        .upload-placeholder p { font-size: 0.72rem !important; }
        .upload-placeholder small { font-size: 0.65rem !important; }
        .up-mode-group { width: 100%; }
        .up-mode-label { flex: 1 1 auto; justify-content: center; font-size: 0.78rem; padding: 0.45rem 0.6rem; }
    }
</style>
@endpush
