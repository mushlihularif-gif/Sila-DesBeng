@extends('layouts.app')

@section('title', 'Keranjang Belanja - Pasar Daerah')

@section('content')
<div class="min-h-screen bg-gray-50 pt-24 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex items-center mb-8">
            <a href="{{ route('pasar.index') }}" class="text-gray-500 hover:text-green-600 mr-4 transition">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Keranjang Belanja</h1>
        </div>

        @if(session('error'))
        <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 flex items-center shadow-sm border border-red-100">
            <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
            {{ session('error') }}
        </div>
        @endif
        @if(session('success'))
        <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 flex items-center shadow-sm border border-green-100">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            {{ session('success') }}
        </div>
        @endif

        @if($carts->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-32 h-32 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-shopping-cart text-5xl text-gray-400"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Keranjang Anda Kosong</h2>
            <p class="text-gray-500 mb-8 max-w-md mx-auto">Sepertinya Anda belum menambahkan produk apapun ke keranjang. Yuk, mulai belanja produk unggulan dari desa-desa di Kabupaten Bengkalis!</p>
            <a href="{{ route('pasar.index') }}" class="inline-flex bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-xl transition duration-300 shadow-lg shadow-green-200">
                Mulai Belanja
            </a>
        </div>
        @else
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Cart Items -->
            <div class="w-full lg:w-2/3">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">Daftar Produk</h3>
                        <span class="text-sm font-medium text-gray-500">{{ $carts->count() }} Item</span>
                    </div>
                    
                    <ul class="divide-y divide-gray-100">
                        @php $total = 0; @endphp
                        @foreach($carts as $cart)
                            @php 
                                $subtotal = $cart->produk->harga * $cart->quantity;
                                $total += $subtotal;
                            @endphp
                            <li class="p-6 flex flex-col sm:flex-row gap-6 items-start sm:items-center">
                                <a href="{{ route('pasar.show', $cart->produk->id) }}" class="w-24 h-24 flex-shrink-0 bg-gray-100 rounded-xl overflow-hidden block border border-gray-200">
                                    @if($cart->produk->foto)
                                        <img src="{{ Storage::url($cart->produk->foto) }}" alt="{{ $cart->produk->nama_produk }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <i class="fas fa-image text-2xl"></i>
                                        </div>
                                    @endif
                                </a>
                                
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-1">
                                        <a href="{{ route('pasar.show', $cart->produk->id) }}" class="text-lg font-bold text-gray-900 hover:text-green-600 transition">{{ $cart->produk->nama_produk }}</a>
                                        <button type="button" onclick="removeItem({{ $cart->id }})" class="text-gray-400 hover:text-red-500 transition p-2 -mr-2 -mt-2 rounded-full hover:bg-red-50">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                    <div class="text-sm text-gray-500 mb-3 flex items-center">
                                        <i class="fas fa-store text-gray-400 mr-2"></i> {{ $cart->produk->region->name ?? '-' }}
                                    </div>
                                    
                                    <div class="flex flex-wrap justify-between items-end gap-4 mt-auto">
                                        <div class="text-green-600 font-bold">
                                            Rp {{ number_format($cart->produk->harga, 0, ',', '.') }}
                                        </div>
                                        
                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center border border-gray-300 rounded-lg bg-white overflow-hidden shadow-sm">
                                                <button type="button" onclick="updateItem({{ $cart->id }}, -1, {{ $cart->produk->stok }})" class="w-8 h-8 text-gray-500 hover:bg-gray-100 flex items-center justify-center transition"><i class="fas fa-minus text-xs"></i></button>
                                                <input type="number" id="qty_{{ $cart->id }}" value="{{ $cart->quantity }}" readonly class="w-12 h-8 text-center border-0 focus:ring-0 text-sm font-bold p-0 text-gray-900 bg-gray-50">
                                                <button type="button" onclick="updateItem({{ $cart->id }}, 1, {{ $cart->produk->stok }})" class="w-8 h-8 text-gray-500 hover:bg-gray-100 flex items-center justify-center transition"><i class="fas fa-plus text-xs"></i></button>
                                            </div>
                                            <div class="text-right w-24">
                                                <span class="text-sm text-gray-500 block mb-1">Subtotal</span>
                                                <strong class="text-gray-900 block font-black">Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Summary -->
            <div class="w-full lg:w-1/3">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Ringkasan Belanja</h3>
                    
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between text-gray-600">
                            <span>Total Harga ({{ $carts->sum('quantity') }} barang)</span>
                            <span class="font-medium">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-sm text-amber-600 bg-amber-50 p-3 rounded-lg border border-amber-100 flex items-start mt-4">
                            <i class="fas fa-info-circle mt-0.5 mr-2"></i>
                            <p>Biaya pengiriman akan dihitung pada langkah selanjutnya (Checkout).</p>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-100 pt-4 mb-6">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-gray-900 font-bold">Total Sementara</span>
                            <span class="text-2xl font-black text-green-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <a href="{{ route('pasar.checkout') }}" class="w-full block text-center bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-4 rounded-xl transition duration-300 shadow-lg shadow-green-200">
                        Lanjut ke Checkout
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function updateItem(cartId, change, maxStok) {
        const input = document.getElementById(`qty_${cartId}`);
        let current = parseInt(input.value);
        let next = current + change;
        
        if (next < 1) next = 1;
        if (next > maxStok) {
            Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Stok tidak mencukupi!', showConfirmButton: false, timer: 3000});
            return;
        }
        
        // Optimistic UI update
        input.value = next;
        
        fetch('{{ route('pasar.cart.update') }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                cart_id: cartId,
                quantity: next
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to update totals
            } else {
                Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Gagal', text: data.message || 'Gagal update keranjang', showConfirmButton: false, timer: 3000}).then(() => {
                    location.reload(); // Revert UI
                });
            }
        });
    }

    function removeItem(cartId) {
        if (!confirm('Hapus produk ini dari keranjang?')) return;
        
        fetch(`/pasar-daerah/cart/remove/${cartId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
</script>
@endpush
@endsection
