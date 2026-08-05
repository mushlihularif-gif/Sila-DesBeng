@extends('layouts.app')

@section('title', $produk->nama_produk . ' - Pasar Daerah')

@section('content')
<div class="min-h-screen bg-gray-50 pt-24 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb -->
        <nav class="flex mb-8 text-sm text-gray-500">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('beranda') }}" class="hover:text-green-600 transition">Beranda</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-xs mx-2"></i>
                        <a href="{{ route('pasar.index') }}" class="hover:text-green-600 transition">Pasar Daerah</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-xs mx-2"></i>
                        <span class="text-gray-900 font-medium">{{ $produk->nama_produk }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex flex-col md:flex-row">
                <!-- Product Images -->
                <div class="w-full md:w-1/2 p-8 border-b md:border-b-0 md:border-r border-gray-100">
                    <div class="aspect-w-4 aspect-h-3 bg-gray-100 rounded-xl overflow-hidden mb-4">
                        @if($produk->foto)
                            <img src="{{ Storage::url($produk->foto) }}" alt="{{ $produk->nama_produk }}" class="object-cover w-full h-full" id="mainImage">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <i class="fas fa-image text-5xl"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4">
                        @if($produk->foto)
                        <button onclick="document.getElementById('mainImage').src='{{ Storage::url($produk->foto) }}'" class="aspect-w-1 aspect-h-1 rounded-lg overflow-hidden border-2 border-transparent hover:border-green-500 focus:border-green-500 transition">
                            <img src="{{ Storage::url($produk->foto) }}" class="object-cover w-full h-full">
                        </button>
                        @endif
                        @if($produk->foto_2)
                        <button onclick="document.getElementById('mainImage').src='{{ Storage::url($produk->foto_2) }}'" class="aspect-w-1 aspect-h-1 rounded-lg overflow-hidden border-2 border-transparent hover:border-green-500 focus:border-green-500 transition">
                            <img src="{{ Storage::url($produk->foto_2) }}" class="object-cover w-full h-full">
                        </button>
                        @endif
                        @if($produk->foto_3)
                        <button onclick="document.getElementById('mainImage').src='{{ Storage::url($produk->foto_3) }}'" class="aspect-w-1 aspect-h-1 rounded-lg overflow-hidden border-2 border-transparent hover:border-green-500 focus:border-green-500 transition">
                            <img src="{{ Storage::url($produk->foto_3) }}" class="object-cover w-full h-full">
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Product Info -->
                <div class="w-full md:w-1/2 p-8 flex flex-col">
                    <span class="inline-block bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full mb-4 w-fit">{{ $produk->kategori }}</span>
                    
                    <h1 class="text-3xl font-extrabold text-gray-900 mb-2">{{ $produk->nama_produk }}</h1>
                    
                    <div class="flex items-center text-sm text-gray-500 mb-6">
                        <i class="fas fa-store text-gray-400 mr-2"></i>
                        Dikirim dari: <strong class="text-gray-900 ml-1">{{ $produk->region->name ?? 'Wilayah Tidak Diketahui' }}</strong>
                    </div>
                    
                    <div class="text-4xl font-black text-green-600 mb-6">
                        Rp {{ number_format($produk->harga, 0, ',', '.') }}
                        <span class="text-lg text-gray-500 font-normal">/ {{ $produk->satuan ?? 'pcs' }}</span>
                    </div>

                    <div class="prose prose-sm text-gray-600 mb-8 flex-1">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Deskripsi Produk</h3>
                        <p>{!! nl2br(e($produk->deskripsi ?? 'Tidak ada deskripsi.')) !!}</p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-gray-600 font-medium">Sisa Stok</span>
                            <span class="text-lg font-bold text-gray-900">{{ $produk->stok }}</span>
                        </div>
                        
                        @auth
                            <form id="addToCartForm" class="flex items-center gap-4">
                                <input type="hidden" name="pasar_produk_id" value="{{ $produk->id }}">
                                
                                <div class="flex items-center border border-gray-300 rounded-xl bg-white overflow-hidden">
                                    <button type="button" onclick="updateQty(-1)" class="w-12 h-12 text-gray-500 hover:bg-gray-100 flex items-center justify-center transition"><i class="fas fa-minus"></i></button>
                                    <input type="number" id="qtyInput" name="quantity" value="1" min="1" max="{{ $produk->stok }}" class="w-16 h-12 text-center border-0 focus:ring-0 text-lg font-bold p-0 text-gray-900">
                                    <button type="button" onclick="updateQty(1)" class="w-12 h-12 text-gray-500 hover:bg-gray-100 flex items-center justify-center transition"><i class="fas fa-plus"></i></button>
                                </div>
                                
                                <button type="button" onclick="submitCart()" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold h-12 rounded-xl flex items-center justify-center transition shadow-lg shadow-green-200">
                                    <i class="fas fa-cart-plus mr-2"></i> Tambahkan
                                </button>
                            </form>
                        @else
                            <a href="{{ route('auth.login') }}" class="w-full block text-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-lg shadow-green-200">
                                Login untuk Membeli
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const maxStok = {{ $produk->stok }};
    const qtyInput = document.getElementById('qtyInput');

    function updateQty(change) {
        if (!qtyInput) return;
        let current = parseInt(qtyInput.value) || 1;
        let next = current + change;
        if (next >= 1 && next <= maxStok) {
            qtyInput.value = next;
        }
    }

    function submitCart() {
        fetch('{{ route('pasar.cart.add') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                pasar_produk_id: document.querySelector('input[name="pasar_produk_id"]').value,
                quantity: document.getElementById('qtyInput').value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Berhasil ditambahkan ke keranjang!', showConfirmButton: false, timer: 1500}).then(() => {
                    window.location.href = '{{ route('pasar.cart') }}';
                });
            } else {
                Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Gagal', text: data.message || 'Gagal menambahkan ke keranjang', showConfirmButton: false, timer: 3000});
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem', showConfirmButton: false, timer: 3000});
        });
    }
</script>
@endpush
@endsection
