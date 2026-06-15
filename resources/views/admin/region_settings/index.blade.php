@extends('admin.layouts.admin')

@section('title', 'Pengaturan Wilayah & Layanan')

@section('page-title', 'Pengaturan Wilayah & Layanan')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Pengaturan Wilayah: {{ $region->name }}</h2>
        <p class="text-sm text-gray-500 mb-6">Kelola informasi kontak, detail rekening kas, dan pilih layanan yang diaktifkan untuk wilayah ini.</p>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <form action="{{ route('admin.region-settings.update') }}" method="POST" class="space-y-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informasi Kontak -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-700 border-b pb-2">Informasi Kontak</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Singkat / Profil</label>
                        <textarea name="profile_text" rows="3" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('profile_text', $region->profile_text) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telepon / WA</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $region->contact_phone) }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $region->contact_email) }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Informasi Kas (Rekening) -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-700 border-b pb-2">Informasi Kas / Pembayaran</h3>
                    <p class="text-xs text-gray-500 mb-2">Dana dari pemesanan layanan di wilayah ini akan diarahkan ke rekening ini.</p>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bank / E-Wallet</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $region->payment_info['bank_name'] ?? '') }}" placeholder="Contoh: BRI, Mandiri, Dana" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Rekening</label>
                        <input type="text" name="account_number" value="{{ old('account_number', $region->payment_info['account_number'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Atas Nama (A/N)</label>
                        <input type="text" name="account_name" value="{{ old('account_name', $region->payment_info['account_name'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Opt-in Layanan -->
            <div class="space-y-4 pt-4">
                <h3 class="text-lg font-semibold text-gray-700 border-b pb-2">Layanan yang Tersedia</h3>
                <p class="text-xs text-gray-500 mb-4">Centang layanan yang ingin Anda aktifkan untuk wilayah ini. Jika tidak dicentang, warga tidak dapat melihat atau memesan layanan tersebut dari wilayah Anda.</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($allServices as $service)
                        <label class="relative flex items-start p-4 border rounded-xl cursor-pointer hover:bg-gray-50 transition-colors {{ in_array($service->id, $activeServices) ? 'border-blue-500 bg-blue-50/50' : 'border-gray-200' }}">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="services[]" value="{{ $service->id }}" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" {{ in_array($service->id, $activeServices) ? 'checked' : '' }}>
                            </div>
                            <div class="ms-3 text-sm flex-1">
                                <span class="font-medium text-gray-900 block">{{ $service->name }}</span>
                                <span class="text-gray-500 text-xs mt-1 block">Aktifkan modul {{ strtolower($service->name) }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-6 border-t flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
