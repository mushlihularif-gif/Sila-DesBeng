@props(['href' => url('/beranda')])
<a href="{{ $href }}" {{ $attributes->except('class') }} style="display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 24px;border-radius:999px;border:1px solid #115789;background:#fff;color:#115789;font:600 15px/1.4 Inter, sans-serif;text-decoration:none;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7-7 7 7 7"/></svg>
    <span>Kembali ke Beranda</span>
</a>
