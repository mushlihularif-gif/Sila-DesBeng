@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full overflow-x-hidden min-h-screen bg-gray-50 pt-24 pb-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-8 sm:p-12">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                        Gabung Kemitraan Daerah
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        Daftarkan wilayah Anda (Kecamatan, Desa, RW, atau RT) untuk menjadi bagian dari sistem SilaDesBeng dan nikmati kemudahan layanannya.
                    </p>
                </div>

                @if(session('success'))
                    <div class="mb-8 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700 font-medium">
                                    {{ session('success') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500"></i>
                            </div>
                            <div class="ml-3">
                                <ul class="text-sm text-red-700 font-medium list-disc pl-5">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('kemitraan.store') }}" method="POST" class="space-y-8">
                    @csrf
                    
                    <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-8">
                        <div class="sm:col-span-2">
                            <label for="applicant_name" class="block text-sm font-medium text-gray-700">Nama Pendaftar / Perwakilan</label>
                            <div class="mt-1">
                                <input type="text" name="applicant_name" id="applicant_name" value="{{ old('applicant_name') }}" class="py-3 px-4 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 border-gray-300 rounded-md" required>
                            </div>
                        </div>

                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700">Email Kontak</label>
                            <div class="mt-1">
                                <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email') }}" class="py-3 px-4 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 border-gray-300 rounded-md" required>
                            </div>
                        </div>

                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700">Nomor Telepon / WA</label>
                            <div class="mt-1">
                                <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}" class="py-3 px-4 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 border-gray-300 rounded-md" required>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <hr class="my-4 border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Wilayah</h3>
                        </div>

                        <div>
                            <label for="region_type" class="block text-sm font-medium text-gray-700">Tingkat Wilayah</label>
                            <div class="mt-1">
                                <select id="region_type" name="region_type" class="py-3 px-4 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 border-gray-300 rounded-md" required>
                                    <option value="" disabled selected>Pilih Tingkat Wilayah</option>
                                    <option value="kecamatan" {{ old('region_type') == 'kecamatan' ? 'selected' : '' }}>Kecamatan</option>
                                    <option value="desa" {{ old('region_type') == 'desa' ? 'selected' : '' }}>Desa / Kelurahan</option>
                                    <option value="rw" {{ old('region_type') == 'rw' ? 'selected' : '' }}>RW</option>
                                    <option value="rt" {{ old('region_type') == 'rt' ? 'selected' : '' }}>RT</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="region_name" class="block text-sm font-medium text-gray-700">Nama Wilayah (Contoh: Desa Suka Maju)</label>
                            <div class="mt-1">
                                <input type="text" name="region_name" id="region_name" value="{{ old('region_name') }}" class="py-3 px-4 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 border-gray-300 rounded-md" required>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="parent_region_id" class="block text-sm font-medium text-gray-700">Menginduk ke Wilayah Mana?</label>
                            <p class="text-xs text-gray-500 mb-2">Pilih wilayah di atas Anda yang sudah terdaftar di sistem.</p>
                            <div class="mt-1">
                                <select id="parent_region_id" name="parent_region_id" class="py-3 px-4 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 border-gray-300 rounded-md" required>
                                    <option value="" disabled selected>Pilih Wilayah Induk</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}" {{ old('parent_region_id') == $region->id ? 'selected' : '' }}>
                                            [{{ strtoupper($region->type) }}] {{ $region->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="reason" class="block text-sm font-medium text-gray-700">Alasan Bergabung / Pesan Tambahan</label>
                            <div class="mt-1">
                                <textarea id="reason" name="reason" rows="4" class="py-3 px-4 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 border-gray-300 rounded-md" required>{{ old('reason') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2 pt-5">
                        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-4 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            Kirim Permohonan Kemitraan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
