@extends('layouts.lurah')

@section('title', 'Pengaturan Akun')
@section('page-title', 'Pengaturan Akun Lurah')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-[#004635] to-[#003026] rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/40" data-aos="fade-down">
        <div class="flex items-center gap-3 mb-2">
            <span class="text-4xl">⚙️</span>
            <h2 class="text-3xl font-bold text-yellow-400">Pengaturan Akun</h2>
        </div>
        <p class="text-gray-300 text-lg">Kelola informasi profil dan keamanan akun Anda</p>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
    <div class="bg-green-500/20 border-2 border-green-500 text-green-300 px-6 py-4 rounded-xl flex items-center gap-3 animate-fade-in" data-aos="fade-down">
        <span class="text-2xl">✅</span>
        <span class="font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Error Alert --}}
    @if($errors->any())
    <div class="bg-red-500/20 border-2 border-red-500 text-red-300 px-6 py-4 rounded-xl" data-aos="fade-down">
        <div class="flex items-center gap-3 mb-2">
            <span class="text-2xl">❌</span>
            <span class="font-semibold">Terjadi Kesalahan:</span>
        </div>
        <ul class="list-disc list-inside ml-8 space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Sidebar Profile Card --}}
        <div class="lg:col-span-1">
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20 sticky top-6" data-aos="fade-right">
                <div class="text-center mb-6">
                    {{-- Profile Avatar with Initials --}}
                    @if(isset($user->avatar) && $user->avatar)
                        <img src="{{ asset($user->avatar) }}" alt="{{ $user->name }}" 
                            class="w-32 h-32 mx-auto rounded-full object-cover border-4 border-yellow-400 shadow-2xl mb-4">
                    @else
                        @php
                            $userName = $user->name;
                            $initials = strtoupper(substr($userName, 0, 1));
                            
                            // Jika nama ada 2 kata atau lebih, ambil inisial pertama dari 2 kata pertama
                            $nameParts = explode(' ', $userName);
                            if (count($nameParts) >= 2) {
                                $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
                            }
                        @endphp
                        <div class="w-32 h-32 mx-auto bg-gradient-to-br from-yellow-400 to-amber-500 rounded-full flex items-center justify-center mb-4 shadow-2xl border-4 border-yellow-400">
                            <span class="text-[#004635] font-bold text-6xl">{{ $initials }}</span>
                        </div>
                    @endif
                    
                    <h3 class="text-white font-bold text-2xl mb-2">{{ $user->name }}</h3>
                    <div class="inline-block px-4 py-2 bg-yellow-400/20 text-yellow-300 rounded-xl font-bold text-sm border-2 border-yellow-400/40">
                        👑 LURAH
                    </div>
                </div>

                <div class="space-y-4 mt-6">
                    <div class="bg-white/5 rounded-xl p-4 border border-yellow-400/10">
                        <p class="text-gray-400 text-xs mb-1">Email</p>
                        <p class="text-white font-semibold break-all">{{ $user->email }}</p>
                    </div>

                    @if(isset($user->no_hp) && $user->no_hp)
                    <div class="bg-white/5 rounded-xl p-4 border border-yellow-400/10">
                        <p class="text-gray-400 text-xs mb-1">No. HP</p>
                        <p class="text-white font-semibold">{{ $user->no_hp }}</p>
                    </div>
                    @endif
                    
                    <div class="bg-white/5 rounded-xl p-4 border border-yellow-400/10">
                        <p class="text-gray-400 text-xs mb-1">Terdaftar Sejak</p>
                        <p class="text-white font-semibold">{{ $user->created_at->format('d F Y') }}</p>
                    </div>

                    <div class="bg-white/5 rounded-xl p-4 border border-yellow-400/10">
                        <p class="text-gray-400 text-xs mb-1">Terakhir Update</p>
                        <p class="text-white font-semibold">{{ $user->updated_at->format('d F Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Form Update Profile --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20" data-aos="fade-left">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-3xl">👤</span>
                    <h3 class="text-yellow-400 font-bold text-2xl">Informasi Profil</h3>
                </div>

                <form method="POST" action="{{ route('lurah.profile.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Nama Lengkap --}}
                    <div>
                        <label class="text-white text-sm mb-2 block font-semibold">
                            Nama Lengkap <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full bg-white/10 border-2 border-yellow-400/30 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50 transition-all"
                            placeholder="Masukkan nama lengkap">
                        @error('name')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="text-white text-sm mb-2 block font-semibold">
                            Email <span class="text-red-400">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full bg-white/10 border-2 border-yellow-400/30 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50 transition-all"
                            placeholder="Masukkan email">
                        @error('email')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- No HP --}}
                    <div>
                        <label class="text-white text-sm mb-2 block font-semibold">
                            No. HP <span class="text-gray-400 text-xs font-normal">(Opsional)</span>
                        </label>
                        <input type="tel" name="no_hp" value="{{ old('no_hp', $user->no_hp ?? '') }}"
                            class="w-full bg-white/10 border-2 border-yellow-400/30 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50 transition-all"
                            placeholder="08xxxxxxxxxx">
                        @error('no_hp')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Role (Read Only) --}}
                    <div>
                        <label class="text-white text-sm mb-2 block font-semibold">Role</label>
                        <input type="text" value="Lurah" readonly
                            class="w-full bg-gray-700/50 border-2 border-gray-600 rounded-xl px-4 py-3 text-gray-400 cursor-not-allowed">
                        <p class="text-gray-400 text-xs mt-1">Role tidak dapat diubah</p>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex gap-3 pt-4">
                        <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-yellow-400 to-yellow-600 text-[#004635] font-bold py-4 rounded-xl hover:scale-105 hover:shadow-2xl transition-all flex items-center justify-center gap-2">
                            <span class="text-xl">💾</span>
                            <span>Simpan Perubahan</span>
                        </button>
                        <a href="{{ route('lurah.dashboard') }}" 
                            class="px-6 bg-gray-600 hover:bg-gray-700 text-white font-bold py-4 rounded-xl transition-all flex items-center justify-center">
                            Batal
                        </a>
                    </div>
                </form>
            </div>

            {{-- Form Ubah Password --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20" data-aos="fade-left" data-aos-delay="100">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-3xl">🔐</span>
                    <h3 class="text-yellow-400 font-bold text-2xl">Ubah Password</h3>
                </div>

                <form method="POST" action="{{ route('lurah.profile.password') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Password Lama --}}
                    <div>
                        <label class="text-white text-sm mb-2 block font-semibold">
                            Password Lama <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="current_password" id="current_password" required
                                class="w-full bg-white/10 border-2 border-yellow-400/30 rounded-xl px-4 py-3 pr-12 text-white placeholder-gray-400 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50 transition-all"
                                placeholder="Masukkan password lama">
                            <button type="button" onclick="togglePassword('current_password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-yellow-400 transition-colors">
                                <svg id="current_password-icon-hide" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="current_password-icon-show" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('current_password')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password Baru --}}
                    <div>
                        <label class="text-white text-sm mb-2 block font-semibold">
                            Password Baru <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="new_password" id="new_password" required
                                class="w-full bg-white/10 border-2 border-yellow-400/30 rounded-xl px-4 py-3 pr-12 text-white placeholder-gray-400 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50 transition-all"
                                placeholder="Masukkan password baru (min. 8 karakter)">
                            <button type="button" onclick="togglePassword('new_password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-yellow-400 transition-colors">
                                <svg id="new_password-icon-hide" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="new_password-icon-show" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('new_password')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-400 text-xs mt-1">⚠️ Password minimal 8 karakter</p>
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div>
                        <label class="text-white text-sm mb-2 block font-semibold">
                            Konfirmasi Password Baru <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                                class="w-full bg-white/10 border-2 border-yellow-400/30 rounded-xl px-4 py-3 pr-12 text-white placeholder-gray-400 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50 transition-all"
                                placeholder="Ulangi password baru">
                            <button type="button" onclick="togglePassword('new_password_confirmation')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-yellow-400 transition-colors">
                                <svg id="new_password_confirmation-icon-hide" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="new_password_confirmation-icon-show" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex gap-3 pt-4">
                        <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-red-500 to-red-700 text-white font-bold py-4 rounded-xl hover:scale-105 hover:shadow-2xl transition-all flex items-center justify-center gap-2">
                            <span class="text-xl">🔒</span>
                            <span>Ubah Password</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Info Keamanan --}}
            <div class="bg-gradient-to-br from-blue-500/20 to-blue-700/20 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-blue-400/40" data-aos="fade-left" data-aos-delay="200">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-3xl">🛡️</span>
                    <h3 class="text-blue-300 font-bold text-xl">Tips Keamanan</h3>
                </div>
                <ul class="space-y-2 text-gray-300 text-sm">
                    <li class="flex items-start gap-2">
                        <span class="text-yellow-400 mt-1">✓</span>
                        <span>Gunakan password yang kuat (kombinasi huruf besar, kecil, angka, dan simbol)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-yellow-400 mt-1">✓</span>
                        <span>Jangan gunakan password yang sama dengan akun lain</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-yellow-400 mt-1">✓</span>
                        <span>Ubah password secara berkala (minimal 3 bulan sekali)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-yellow-400 mt-1">✓</span>
                        <span>Jangan bagikan password Anda kepada siapapun</span>
                    </li>
                </ul>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const iconHide = document.getElementById(fieldId + '-icon-hide');
        const iconShow = document.getElementById(fieldId + '-icon-show');
        
        if (field.type === 'password') {
            field.type = 'text';
            iconHide.classList.add('hidden');
            iconShow.classList.remove('hidden');
        } else {
            field.type = 'password';
            iconHide.classList.remove('hidden');
            iconShow.classList.add('hidden');
        }
    }
</script>
@endpush

@endsection