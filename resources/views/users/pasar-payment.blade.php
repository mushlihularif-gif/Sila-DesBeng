@extends('layouts.user')

@section('title', 'Pembayaran Pasar Daerah - SiladesBeng')

@push('styles')
<style>
    * { font-family: 'Inter', sans-serif; }

    /* Background styling consistent with checkout */
    .checkout-bg {
        position: fixed; inset: 0; z-index: 0;
        background: linear-gradient(135deg, #f0f7ff 0%, #fafbff 40%, #fff8f0 100%);
    }
    .checkout-bg::before {
        content: ''; position: absolute; top: -50%; right: -30%; width: 80%; height: 80%;
        background: radial-gradient(circle, rgba(17,87,137,0.04) 0%, transparent 70%); border-radius: 50%;
    }
    .checkout-bg::after {
        content: ''; position: absolute; bottom: -30%; left: -20%; width: 60%; height: 60%;
        background: radial-gradient(circle, rgba(245,158,11,0.04) 0%, transparent 70%); border-radius: 50%;
    }

    /* Header styling consistent with checkout */
    .co-header { display: flex; align-items: center; gap: 16px; margin-bottom: 12px; }
    .co-back-btn {
        width: 44px; height: 44px; border-radius: 14px; background: white; border: 1px solid #e2e8f0;
        display: flex; align-items: center; justify-content: center; color: #64748b; text-decoration: none;
        transition: all 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .co-back-btn:hover { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; transform: translateX(-3px); }
    .co-title { font-size: 1.75rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
    
    /* Progress Steps consistent with checkout */
    .progress-steps {
        display: flex; align-items: center; justify-content: center; gap: 0;
        margin-bottom: 32px; padding: 16px 24px;
        background: white; border-radius: 16px; border: 1px solid rgba(226,232,240,0.6);
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
    }
    .step-item { display: flex; align-items: center; gap: 10px; }
    .step-circle {
        width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 800; transition: all 0.3s;
    }
    .step-circle.done { background: linear-gradient(135deg, #10b981, #34d399); color: white; }
    .step-circle.active { background: linear-gradient(135deg, #115789, #1d6aaa); color: white; box-shadow: 0 0 0 4px rgba(17,87,137,0.15); }
    .step-circle.pending { background: #f1f5f9; color: #94a3b8; border: 2px solid #e2e8f0; }
    .step-label { font-size: 0.8rem; font-weight: 600; }
    .step-label.done { color: #10b981; }
    .step-label.active { color: #115789; }
    .step-label.pending { color: #94a3b8; }
    .step-line { width: 48px; height: 2px; margin: 0 12px; border-radius: 2px; }
    .step-line.done { background: linear-gradient(90deg, #10b981, #34d399); }
    .step-line.active { background: linear-gradient(90deg, #10b981, #115789); }
    .step-line.pending { background: #e2e8f0; }

    /* Cards */
    .co-card {
        background: white; border-radius: 20px;
        border: 1px solid rgba(226,232,240,0.6);
        box-shadow: 0 4px 24px rgba(0,0,0,0.04);
        overflow: hidden; margin-bottom: 20px;
    }
    
    /* Payment Summary Header Card */
    .pay-header-card-pending {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: white;
        padding: 32px 24px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .pay-header-card-pending::before {
        content: ''; position: absolute; inset: 0;
        background: radial-gradient(circle at 70% 20%, rgba(17,87,137,0.2) 0%, transparent 60%);
    }
    .pay-header-card-success {
        background: linear-gradient(135deg, #065f46 0%, #047857 100%);
        color: white;
        padding: 32px 24px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    /* VA Display Box */
    .va-display-box {
        display: flex; align-items: center; justify-content: space-between;
        background: #f8fafc; border: 2px solid #e2e8f0;
        border-radius: 16px; padding: 14px 18px; margin-top: 10px;
        transition: all 0.2s;
    }
    .va-display-box:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
    }

    /* Copy Button */
    .btn-copy {
        background: white; border: 1px solid #cbd5e1;
        color: #1e293b; font-weight: 700; font-size: 0.82rem;
        padding: 6px 14px; border-radius: 10px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        transition: all 0.2s;
    }
    .btn-copy:hover {
        border-color: #115789; color: #115789;
        background: rgba(17,87,137,0.02);
    }
    .btn-copy:active { transform: scale(0.97); }

    /* Sandbox Dev Box */
    .sandbox-box {
        background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%);
        border: 1px dashed #f59e0b; border-radius: 16px;
        padding: 20px; margin-bottom: 24px; text-align: center;
    }

    /* Primary and Secondary Action Buttons */
    .btn-action-primary {
        background: linear-gradient(135deg, #115789, #1d6aaa); color: white;
        font-weight: 800; font-size: 0.95rem; border: none; border-radius: 14px;
        padding: 14px 24px; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        transition: all 0.3s; box-shadow: 0 4px 16px rgba(17,87,137,0.2);
    }
    .btn-action-primary:hover {
        transform: translateY(-2px); box-shadow: 0 6px 20px rgba(17,87,137,0.3);
    }
    .btn-action-primary:active { transform: translateY(0); }

    .btn-action-secondary {
        background: white; color: #475569; border: 1px solid #cbd5e1;
        font-weight: 700; font-size: 0.95rem; border-radius: 14px;
        padding: 14px 24px; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        transition: all 0.3s;
    }
    .btn-action-secondary:hover {
        background: #f8fafc; border-color: #94a3b8; color: #1e293b;
    }

    /* Live Countdown styles */
    .countdown-number {
        font-size: 1.25rem; font-weight: 800; font-mono: true;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 6px 12px; border-radius: 10px;
        display: inline-flex; align-items: center; gap: 4px;
    }
</style>
@endpush

@section('page')
<!-- Background elements -->
<div class="checkout-bg"></div>

<main id="main-content" class="relative z-10 flex-grow py-12 md:py-16" style="transition: padding-top 0.3s ease-in-out; padding-top: 50px;">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="co-header">
            <a href="{{ route('pasar.index') }}" class="co-back-btn" title="Kembali ke Katalog">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h1 class="co-title">Pembayaran Pesanan</h1>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step-item">
                <div class="step-circle done">âœ“</div>
                <span class="step-label done">Keranjang</span>
            </div>
            <div class="step-line done"></div>
            <div class="step-item">
                <div class="step-circle done">âœ“</div>
                <span class="step-label done">Checkout</span>
            </div>
            <div class="step-line done"></div>
            <div class="step-item">
                <div class="step-circle {{ $order->status === 'pending' ? 'active' : 'done' }}">
                    {{ $order->status === 'pending' ? '3' : 'âœ“' }}
                </div>
                <span class="step-label {{ $order->status === 'pending' ? 'active' : 'done' }}">Pembayaran</span>
            </div>
        </div>

        <!-- Alert messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl mb-6 flex items-center shadow-sm">
                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        <div class="co-card">
            <!-- Header Block -->
            @if($order->status === 'pending')
                <div class="pay-header-card-pending">
                    <p class="text-slate-300 text-sm font-semibold uppercase tracking-wider mb-1">Total Pembayaran</p>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-3">
                        Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                    </h2>
                    <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/20 text-xs font-semibold text-white">
                        <span>Order ID:</span>
                        <span class="font-mono text-slate-100">{{ $order->order_number }}</span>
                    </div>
                </div>
            @else
                <div class="pay-header-card-success">
                    <div class="w-16 h-16 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center mx-auto mb-4 border border-white/30 shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <p class="text-emerald-100 text-sm font-semibold uppercase tracking-wider mb-1">Status Pembayaran</p>
                    <h2 class="text-3xl font-extrabold tracking-tight mb-2">Terbayar Lunas</h2>
                    <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/20 text-xs font-semibold text-white">
                        <span>Order ID:</span>
                        <span class="font-mono text-white">{{ $order->order_number }}</span>
                    </div>
                </div>
            @endif

            <div class="p-6 md:p-8">
                @if($order->status === 'pending')
                    <!-- Status Header -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-amber-50 text-amber-500 mb-3 border border-amber-100">
                            <svg class="w-7 h-7 animate-spin" fill="none" viewBox="0 0 24 24" style="animation-duration: 4s;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900 mb-1">Menunggu Pembayaran</h2>
                        <p class="text-sm text-gray-500">Selesaikan pembayaran sebelum masa berlaku habis</p>

                        <!-- Live Countdown Display -->
                        @if($order->payment_expiry_time)
                            <div class="mt-4">
                                <span class="countdown-number text-slate-800" id="countdownTimer">
                                    <svg class="w-4 h-4 mr-1 text-[#115789]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span id="countdownText">-- : -- : --</span>
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Payment Information / Instructions -->
                    <div class="bg-slate-50 rounded-2xl p-5 md:p-6 mb-6" style="border: 1px solid #e2e8f0;">
                        <h3 class="font-bold text-gray-800 text-sm tracking-wider uppercase mb-4 pb-2 flex items-center gap-2" style="border-bottom: 1px solid #e2e8f0;">
                            <svg class="w-4 h-4 text-[#115789]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Instruksi Pembayaran
                        </h3>
                        
                        @if(in_array(strtolower($order->payment_method), ['tunai', 'cash']))
                            <div class="flex items-start">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100/80 flex items-center justify-center text-emerald-600 mr-4 flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-gray-900 text-sm">Pembayaran Tunai (COD / Di Tempat)</p>
                                    <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                                        Silakan siapkan uang pas sebesar <strong class="text-gray-900 font-bold">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong> saat menerima pesanan (jika menggunakan pengiriman kurir lokal) atau saat mengambil pesanan langsung di lokasi penjual.
                                    </p>
                                </div>
                            </div>
                        @elseif(in_array(strtolower($order->payment_method), ['bank_transfer', 'transfer_bank', 'transfer manual', 'transfer_manual']))
                            @php
                                $regionSettings = $order->region ? $order->region->settings : [];
                                $bankName = $regionSettings['rekening_bank'] ?? 'Bank Riau Kepri Syariah';
                                $bankNum = $regionSettings['rekening_nomor'] ?? '';
                                $bankHolder = $regionSettings['rekening_nama'] ?? ('BUMDes ' . ($order->region->name ?? 'Desa'));
                            @endphp
                            <div class="space-y-4">
                                <div class="flex justify-between items-center text-xs pb-2" style="border-bottom: 1px solid #e2e8f0;">
                                    <span class="text-slate-400 font-medium">Metode Pembayaran</span>
                                    <span class="font-bold text-[#115789] uppercase tracking-wide bg-blue-50 px-2.5 py-1 rounded-md" style="border: 1px solid #bae6fd;">
                                        Transfer Bank ({{ $bankName }})
                                    </span>
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Nomor Rekening Resmi Toko / BUMDes</label>
                                    <div class="va-display-box">
                                        <div>
                                            <span class="font-mono text-lg md:text-xl font-black text-slate-800 tracking-wider" id="vaNumber">
                                                {{ $bankNum ?: '123-456-7890' }}
                                            </span>
                                            <div class="text-xs text-gray-500 mt-1">Bank <strong>{{ $bankName }}</strong> &bull; a.n <strong>{{ $bankHolder }}</strong></div>
                                        </div>
                                        @if($bankNum)
                                        <button type="button" onclick="copyToClipboard('{{ $bankNum }}', 'Nomor Rekening')" class="btn-copy">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 00-2 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                            Salin
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Silakan transfer sejumlah <strong class="text-gray-900 font-bold">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong> ke rekening resmi di atas. Pesanan akan segera dikonfirmasi dan diproses oleh toko/BUMDes.
                                </p>
                            </div>
                        @elseif(strtolower($order->payment_method) === 'qris')
                            @php
                                $regionSettings = $order->region ? $order->region->settings : [];
                                $qrisImg = $regionSettings['qris_image'] ?? null;
                                $qrisNum = $regionSettings['qris_ewallet_number'] ?? '';
                            @endphp
                            <div class="space-y-4">
                                <div class="flex justify-between items-center text-xs pb-2" style="border-bottom: 1px solid #e2e8f0;">
                                    <span class="text-slate-400 font-medium">Metode Pembayaran</span>
                                    <span class="font-bold text-red-600 uppercase tracking-wide bg-red-50 px-2.5 py-1 rounded-md" style="border: 1px solid #fecaca;">
                                        QRIS / E-Wallet
                                    </span>
                                </div>
                                <div class="text-center pt-2">
                                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-3">Scan Kode QRIS di Bawah Ini</p>
                                    <div class="inline-block p-4 bg-white rounded-2xl shadow-sm relative group overflow-hidden" style="border: 2px solid #e2e8f0;">
                                        @if($qrisImg)
                                            <img src="{{ Storage::url($qrisImg) }}" alt="QRIS Code" class="w-48 h-48 mx-auto object-contain">
                                        @elseif($order->payment_qr_url && $order->payment_qr_url !== 'DUMMY_QR_CODE')
                                            <img src="{{ $order->payment_qr_url }}" alt="QRIS Code" class="w-48 h-48 mx-auto">
                                        @else
                                            <div class="w-48 h-48 bg-slate-100 flex flex-col items-center justify-center text-slate-400 font-mono text-xs border-4 border-dashed border-slate-200 p-4">
                                                <svg class="w-8 h-8 mb-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                [ QRIS Toko Desa ]
                                            </div>
                                        @endif
                                    </div>
                                    @if($qrisNum)
                                        <div class="mt-3 text-xs text-slate-600">Nomor HP E-Wallet: <strong class="text-slate-900 font-bold font-mono">{{ $qrisNum }}</strong></div>
                                    @endif
                                    <p class="text-xs text-slate-400 mt-2 max-w-sm mx-auto">Gunakan aplikasi e-wallet Anda (DANA, Gopay, OVO, ShopeePay) atau m-Banking untuk memindai.</p>
                                </div>
                            </div>
                        @else
                            <!-- Virtual Account / Lainnya -->
                            <div class="space-y-4">
                                <div class="flex justify-between items-center text-xs pb-2" style="border-bottom: 1px solid #e2e8f0;">
                                    <span class="text-slate-400 font-medium">Metode Pembayaran</span>
                                    <span class="font-bold text-[#115789] uppercase tracking-wide bg-blue-50 px-2.5 py-1 rounded-md" style="border: 1px solid #bae6fd;">
                                        {{ str_replace('_', ' ', $order->payment_method) }}
                                    </span>
                                </div>

                                @if($order->payment_va_number)
                                    <div>
                                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Nomor Virtual Account</label>
                                        <div class="va-display-box">
                                            <span class="font-mono text-lg md:text-xl font-black text-slate-800 tracking-wider" id="vaNumber">
                                                {{ $order->payment_va_number }}
                                            </span>
                                            <button onclick="copyToClipboard('{{ $order->payment_va_number }}', 'Nomor Virtual Account')" class="btn-copy">
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 00-2 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                                Salin
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Sandbox Testing Simulator Banner -->
                    @if(config('services.midtrans.is_production') == false && !in_array(strtolower($order->payment_method), ['tunai', 'transfer manual', 'transfer_manual']))
                        <div class="sandbox-box">
                            <div class="flex justify-center items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-amber-600 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 9.172V5L8 4z"></path></svg>
                                <h4 class="font-extrabold text-amber-800 text-sm tracking-wide uppercase">Mode Sandbox (Testing)</h4>
                            </div>
                            <p class="text-xs text-amber-700 mb-4 max-w-md mx-auto leading-relaxed">Sistem mendeteksi Anda menggunakan environment testing/sandbox. Gunakan tombol ini untuk langsung mengubah status pesanan menjadi sukses tanpa bayar manual.</p>
                            <form action="{{ route('pasar.payment.simulate', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-6 rounded-xl transition text-xs shadow-md shadow-amber-600/20 active:scale-95">
                                    Simulasikan Pembayaran Berhasil
                                </button>
                            </form>
                        </div>
                    @endif

                @else
                    <!-- Success Payment State -->
                    <div class="text-center mb-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Terima Kasih atas Pembayaran Anda!</h2>
                        <p class="text-sm text-slate-500 max-w-md mx-auto leading-relaxed mb-6">
                            Pembayaran Anda telah berhasil diproses secara otomatis oleh sistem kami. Pesanan Anda saat ini sedang diteruskan ke penjual untuk segera dipacking dan dikirimkan.
                        </p>
                        
                        <div class="flex flex-col sm:flex-row justify-center gap-3">
                            <a href="{{ route('user.activity') }}" class="btn-action-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                Lihat Status Pesanan
                            </a>
                            <a href="{{ route('pasar.index') }}" class="btn-action-secondary">
                                Belanja Lagi
                            </a>
                        </div>
                    </div>
                @endif
                
                <!-- Order Details Section -->
                <div class="pt-6 mt-6" style="border-top: 1px solid #e2e8f0;">
                    <h3 class="font-bold text-gray-800 text-sm tracking-wider uppercase mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        Detail Pesanan
                    </h3>
                    <div class="space-y-4">
                        @foreach($order->items as $item)
                        <div class="flex justify-between items-start gap-4">
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-slate-800 text-sm truncate">{{ $item->product_name }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $item->quantity }} x Rp {{ number_format($item->product_price, 0, ',', '.') }}</p>
                            </div>
                            <span class="font-bold text-slate-700 text-sm whitespace-nowrap">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
            
            <!-- Footer Action Panel -->
            <div class="bg-slate-50 px-6 py-5 flex flex-col sm:flex-row gap-4 justify-between items-center" style="border-top: 1px solid #e2e8f0;">
                <a href="{{ route('pasar.index') }}" class="text-[#115789] hover:text-[#0c4066] font-bold text-xs md:text-sm flex items-center gap-1.5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7 m0 0l7-7 m-7 7h18"></path></svg>
                    Kembali ke Katalog
                </a>
                <a href="{{ route('user.activity') }}" class="btn-action-secondary py-2.5 px-5 text-xs md:text-sm">
                    Cek Aktivitas / Transaksi
                </a>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
    // Copy to clipboard helper with SweetAlert2
    function copyToClipboard(text, fieldName) {
        navigator.clipboard.writeText(text).then(function() {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: fieldName + ' berhasil disalin!',
                showConfirmButton: false,
                timer: 2000
            });
        }).catch(function(err) {
            console.error('Gagal menyalin: ', err);
        });
    }

    // Countdown Timer logic
    @if($order->status === 'pending' && $order->payment_expiry_time)
        const expiryTime = new Date("{{ $order->payment_expiry_time->toIso8601String() }}").getTime();
        const countdownTimer = setInterval(function() {
            const now = new Date().getTime();
            const distance = expiryTime - now;

            if (distance < 0) {
                clearInterval(countdownTimer);
                document.getElementById("countdownText").innerHTML = "EXPIRED";
                document.getElementById("countdownTimer").classList.remove("text-slate-800");
                document.getElementById("countdownTimer").classList.add("text-red-600", "bg-red-50", "border-red-100");
                return;
            }

            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Format numbers to always show two digits
            const formattedHours = hours < 10 ? "0" + hours : hours;
            const formattedMinutes = minutes < 10 ? "0" + minutes : minutes;
            const formattedSeconds = seconds < 10 ? "0" + seconds : seconds;

            document.getElementById("countdownText").innerHTML = formattedHours + " : " + formattedMinutes + " : " + formattedSeconds;
        }, 1000);
    @endif

    // Sync Padding with Navbar visibility
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('master-navbar');
        const mainContent = document.getElementById('main-content');
        
        if (header && mainContent) {
            const updatePositions = () => {
                const isHidden = header.classList.contains('hidden-nav');
                mainContent.style.paddingTop = isHidden ? '0px' : '50px';
            };
            
            updatePositions();
            
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'class') {
                        updatePositions();
                    }
                });
            });
            
            observer.observe(header, { attributes: true });
        }
    });
</script>

<!-- QRIS Scan line animation helper style -->
<style>
    @keyframes scan {
        0% { top: 0%; }
        50% { top: 100%; }
        100% { top: 0%; }
    }
</style>
@endpush
@endsection

