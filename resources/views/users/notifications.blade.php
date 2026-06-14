@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16 bg-cover bg-center bg-no-repeat bg-fixed" 
             style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
        
        <!-- White Overlay -->
        <div class="absolute inset-0 bg-white/25 pointer-events-none"></div>

        <div class="max-w-5xl mx-auto px-6 relative z-20">
            <!-- Header with Gradient Text (Centered) -->
            <div class="text-center mb-12 mt-8">
                <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">
                    Notifikasi
                </h1>
                @if($unreadCount > 0)
                <p class="text-gray-600 mt-2">Anda memiliki <span class="font-bold text-blue-600">{{ $unreadCount }}</span> notifikasi belum dibaca</p>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end mb-6 gap-4">
                @if($unreadCount > 0)
                <form action="{{ route('user.notifications.readAll') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-6 py-2.5 bg-blue-500 text-white rounded-lg text-sm font-semibold hover:bg-blue-600 transition-all duration-300 shadow-md hover:shadow-lg">
                        <i class="fas fa-check-double mr-2"></i>Tandai Semua Sudah Dibaca
                    </button>
                </form>
                @endif

                @if($notifications->count() > 0)
                <button type="button" 
                        id="delete-all-notifications-btn"
                        class="px-6 py-2.5 bg-red-100 text-red-600 rounded-lg text-sm font-semibold hover:bg-red-200 transition-all duration-300 shadow-md hover:shadow-lg">
                    <i class="fas fa-trash-alt mr-2"></i>Hapus Semua Notifikasi
                </button>
                @endif
            </div>

            <!-- Notifications List -->
            <div class="space-y-6">
                @forelse($notifications as $notification)
                <div class="bg-white rounded-2xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-300 border-opacity-50">
                    <div class="p-6">
                        <div class="flex gap-6">
                            <!-- Icon/Image -->
                            <div class="flex-shrink-0">
                                @if($notification->image)
                                    <!-- Custom uploaded image -->
                                    <div class="w-20 h-20 rounded-xl overflow-hidden shadow-md">
                                        <img src="{{ asset('storage/' . $notification->image) }}" 
                                             alt="{{ $notification->title }}" 
                                             class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <!-- SVG Icons based on type -->
                                    @php
                                        $iconSvg = '';
                                        $bgClass = 'bg-blue-100';
                                        
                                        switch($notification->type) {
                                            case 'pesan_admin':
                                                $bgClass = 'bg-gradient-to-br from-purple-100 to-purple-200';
                                                $iconSvg = '<svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 13.5997 2.37562 15.1116 3.04346 16.4525C3.22094 16.8088 3.28001 17.2161 3.17712 17.6006L2.58151 19.8267C2.32295 20.793 3.20701 21.677 4.17335 21.4185L6.39939 20.8229C6.78393 20.72 7.19121 20.7791 7.54753 20.9565C8.88837 21.6244 10.4003 22 12 22Z" fill="url(#grad1)"/><path d="M8 12H8.01M12 12H12.01M16 12H16.01" stroke="white" stroke-width="2" stroke-linecap="round"/><defs><linearGradient id="grad1" x1="2" y1="2" x2="22" y2="22"><stop offset="0%" stop-color="#9333EA"/><stop offset="100%" stop-color="#C084FC"/></linearGradient></defs></svg>';
                                                break;
                                            case 'status_berubah':
                                                // Check if it's a rejection based on title/message
                                                if (stripos($notification->title, 'ditolak') !== false || stripos($notification->message, 'ditolak') !== false) {
                                                    $bgClass = 'bg-gradient-to-br from-red-100 to-rose-200';
                                                    $iconSvg = '<svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" fill="url(#grad_reject)"/><path d="M8 8L16 16M16 8L8 16" stroke="white" stroke-width="2.5" stroke-linecap="round"/><defs><linearGradient id="grad_reject" x1="2" y1="2" x2="22" y2="22"><stop offset="0%" stop-color="#DC2626"/><stop offset="100%" stop-color="#F87171"/></linearGradient></defs></svg>';
                                                } else {
                                                    $bgClass = 'bg-gradient-to-br from-green-100 to-emerald-200';
                                                    $iconSvg = '<svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" fill="url(#grad2)"/><path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><defs><linearGradient id="grad2" x1="2" y1="2" x2="22" y2="22"><stop offset="0%" stop-color="#10B981"/><stop offset="100%" stop-color="#34D399"/></linearGradient></defs></svg>';
                                                }
                                                break;
                                            case 'status_update':
                                                $bgClass = 'bg-gradient-to-br from-green-100 to-emerald-200';
                                                $iconSvg = '<svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" fill="url(#grad2)"/><path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><defs><linearGradient id="grad2" x1="2" y1="2" x2="22" y2="22"><stop offset="0%" stop-color="#10B981"/><stop offset="100%" stop-color="#34D399"/></linearGradient></defs></svg>';
                                                break;
                                            case 'delivery_proof':
                                                $bgClass = 'bg-gradient-to-br from-blue-100 to-cyan-200';
                                                $iconSvg = '<svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="6" width="15" height="12" rx="2" fill="url(#grad3)"/><path d="M16 8V18H18C19.1046 18 20 17.1046 20 16V10C20 8.89543 19.1046 8 18 8H16Z" fill="url(#grad3)"/><circle cx="6" cy="19" r="2" fill="white"/><circle cx="18" cy="19" r="2" fill="white"/><defs><linearGradient id="grad3" x1="1" y1="6" x2="20" y2="19"><stop offset="0%" stop-color="#3B82F6"/><stop offset="100%" stop-color="#06B6D4"/></linearGradient></defs></svg>';
                                                break;
                                            case 'cancellation_approved':
                                                $bgClass = 'bg-gradient-to-br from-orange-100 to-amber-200';
                                                $iconSvg = '<svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" fill="url(#grad4)"/><path d="M12 7V13M12 16H12.01" stroke="white" stroke-width="2" stroke-linecap="round"/><defs><linearGradient id="grad4" x1="2" y1="2" x2="22" y2="22"><stop offset="0%" stop-color="#F59E0B"/><stop offset="100%" stop-color="#FBBF24"/></linearGradient></defs></svg>';
                                                break;
                                            case 'cancellation_rejected':
                                                $bgClass = 'bg-gradient-to-br from-red-100 to-rose-200';
                                                $iconSvg = '<svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" fill="url(#grad5)"/><path d="M15 9L9 15M9 9L15 15" stroke="white" stroke-width="2.5" stroke-linecap="round"/><defs><linearGradient id="grad5" x1="2" y1="2" x2="22" y2="22"><stop offset="0%" stop-color="#DC2626"/><stop offset="100%" stop-color="#F87171"/></linearGradient></defs></svg>';
                                                break;
                                            default:
                                                $bgClass = 'bg-gradient-to-br from-blue-100 to-indigo-200';
                                                $iconSvg = '<svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 22C10.9 22 10 21.1 10 20H14C14 21.1 13.1 22 12 22ZM18 16V11C18 7.93 16.36 5.36 13.5 4.68V4C13.5 3.17 12.83 2.5 12 2.5C11.17 2.5 10.5 3.17 10.5 4V4.68C7.63 5.36 6 7.92 6 11V16L4 18V19H20V18L18 16Z" fill="url(#grad6)"/><defs><linearGradient id="grad6" x1="4" y1="2.5" x2="20" y2="22"><stop offset="0%" stop-color="#3B82F6"/><stop offset="100%" stop-color="#6366F1"/></linearGradient></defs></svg>';
                                        }
                                    @endphp
                                    <div class="w-20 h-20 rounded-xl {{ $bgClass }} flex items-center justify-center shadow-md">
                                        {!! $iconSvg !!}
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4 mb-2">
                                    <h3 class="text-xl font-bold text-gray-900">
                                        {{ $notification->title }}
                                        @if(!$notification->is_read)
                                        <span class="inline-block w-2 h-2 bg-blue-500 rounded-full ml-2 animate-pulse"></span>
                                        @endif
                                    </h3>
                                    <span class="text-sm font-medium text-gray-500 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($notification->created_at)->locale('id')->isoFormat('DD MMMM YYYY HH:mm') }} WIB
                                    </span>
                                </div>
                                
                                <p class="text-gray-600 text-sm mb-3 leading-relaxed">
                                    {{ $notification->message }}
                                </p>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-3 mt-4">
                                    @if(!$notification->is_read)
                                    <form action="{{ route('user.notifications.read', $notification->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-semibold hover:bg-blue-100 transition-colors duration-200">
                                            <i class="fas fa-check mr-1"></i>Tandai Sudah Dibaca
                                        </button>
                                    </form>
                                    @else
                                    <span class="px-4 py-2 bg-gray-50 text-gray-500 rounded-lg text-sm font-semibold">
                                        <i class="fas fa-check-double mr-1"></i>Sudah Dibaca
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <div class="mb-4">
                        <svg class="w-24 h-24 mx-auto text-gray-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 22C10.9 22 10 21.1 10 20H14C14 21.1 13.1 22 12 22ZM18 16V11C18 7.93 16.36 5.36 13.5 4.68V4C13.5 3.17 12.83 2.5 12 2.5C11.17 2.5 10.5 3.17 10.5 4V4.68C7.63 5.36 6 7.92 6 11V16L4 18V19H20V18L18 16Z" fill="currentColor" opacity="0.3"/>
                            <path d="M12 2.5L14 4.5M12 2.5L10 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Belum Ada Notifikasi</h3>
                    <p class="text-gray-500">Notifikasi Anda akan muncul di sini</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    * {
        font-family: 'Inter', sans-serif;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteBtn = document.getElementById('delete-all-notifications-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', async function() {
                const result = await Swal.fire({
                    title: 'Hapus Semua Notifikasi?',
                    text: "Semua notifikasi Anda akan dihapus secara permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus Semua',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280'
                });

                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang Menghapus...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const response = await fetch('{{ route("user.notifications.deleteAll") }}', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            await Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                confirmButtonColor: '#3b82f6'
                            });
                            location.reload();
                        } else {
                            await Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message,
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    } catch (error) {
                        await Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat menghapus notifikasi.',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                }
            });
        }
    });
</script>
@endpush
