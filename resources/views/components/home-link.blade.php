@props(['href' => url('/beranda')])
<a href="{{ $href }}" {{ $attributes->class(['segi-back']) }} style="display:inline-flex;align-items:center;justify-content:center;padding:10px 20px;border-radius:999px;border:1px solid #e2e8f0;background:#fff;color:#115789;font:600 14px/1.4 Inter,sans-serif;text-decoration:none;box-shadow:0 2px 4px #00000014;">
    <span>Kembali ke Beranda</span>
</a>
