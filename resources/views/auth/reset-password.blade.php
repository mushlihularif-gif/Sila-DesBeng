@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative" style="background-color: #f8fafc;">
    
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl relative z-10">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Buat Kata Sandi Baru</h2>
            <p class="text-sm text-gray-500 mb-6">Silahkan masukkan kata sandi baru untuk akun Anda</p>
        </div>

        <form action="{{ route('auth.forgot-password.reset') }}" method="POST" class="mt-8 space-y-6">
            @csrf

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4 text-sm text-center font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <div class="space-y-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Kata Sandi Baru</label>
                    <input id="password" name="password" type="password" required
                        class="appearance-none rounded-lg relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                    @error('password')
                        <span class="text-red-500 text-xs block mt-1">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Kata Sandi Baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        class="appearance-none rounded-lg relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                </div>
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-md">
                    Simpan Kata Sandi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
