@extends('layouts.user')

@section('title', 'Kelola Kabar dan Berita - SilaDesBeng')

@push('styles')
<style>
    /* Custom styling if needed */
</style>
@endpush

@section('page')
<main class="flex-grow relative w-full min-h-screen">
    {{-- Custom Vector Abstract Background --}}
    @include('partials.abstract-bg')

    <section class="relative z-10 pt-32 pb-16">
        <div class="max-w-7xl mx-auto px-6" x-data="beritaAdmin()">
            <div x-show="!isFormOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">`n              <!-- Header Section -->
            <div class="text-center mb-12 animate-section">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Kelola Kabar dan </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Berita</span>
                </h1>
                <p class="text-gray-700 text-lg mt-2 mb-6">
                    Buat dan kelola berita atau kegiatan warga.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('announcements.index') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-full bg-white text-blue-500 font-medium hover:bg-gray-50 shadow-md hover:shadow-lg transition-all border border-blue-200 focus:outline-none">
                         Lihat Portal Publik
                    </a>
                    <button @click.prevent="isFormOpen = true" class="inline-flex items-center justify-center px-6 py-3 rounded-full bg-blue-500 text-white font-medium hover:bg-blue-600 shadow-md hover:shadow-lg transition-all border border-blue-500 focus:outline-none">
                         Tulis Berita Baru
                    </button>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="max-w-5xl mx-auto mb-12 animate-section">
                <div class="backdrop-blur-sm bg-white/70 rounded-3xl p-4 md:p-6 border border-white/80 shadow-lg">
                    <div class="flex flex-col lg:flex-row gap-6 justify-between items-center w-full">
                        
                        {{-- Filter Pills --}}
                        <div class="flex flex-wrap md:flex-nowrap gap-2 w-full lg:w-auto justify-center lg:justify-start overflow-x-auto pb-2 md:pb-0 hide-scrollbar">
                            <button type="button" @click.prevent="updateFilter('')" 
                               :class="!type ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-4 py-2 whitespace-nowrap rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none">
                               Semua
                            </button>
                            <button type="button" @click.prevent="updateFilter('Berita')" 
                               :class="type === 'Berita' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-4 py-2 whitespace-nowrap rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none">
                               Berita
                            </button>
                            <button type="button" @click.prevent="updateFilter('Kegiatan')" 
                               :class="type === 'Kegiatan' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-4 py-2 whitespace-nowrap rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none">
                               Kegiatan
                            </button>
                            <button type="button" @click.prevent="updateFilter('Artikel')" 
                               :class="type === 'Artikel' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-4 py-2 whitespace-nowrap rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none">
                               Artikel
                            </button>
                        </div>

                        {{-- Search Input (Style gradient dari beranda) --}}
                        <div class="w-full lg:w-fit flex items-center justify-end">
                            <div class="w-full sm:w-[280px] lg:w-[320px] relative group flex-shrink-0">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 via-blue-400 to-amber-400 rounded-full opacity-70 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative flex items-center bg-white rounded-full overflow-hidden">
                                    <input type="text" x-model="search" @input.debounce.500ms="fetchData()" placeholder="Cari judul..." 
                                        class="w-full pl-6 pr-4 py-3 text-gray-700 text-sm focus:outline-none bg-transparent">
                                    <div class="flex-shrink-0 px-4" :class="loading ? 'text-amber-500 animate-spin' : 'text-blue-500 hover:text-blue-600'">
                                        <svg x-show="!loading" class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <svg x-show="loading" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                                                </div>
                    </div>
                </div>
            </div>

            <div id="berita-list-container" class="transition-all duration-300" :class="{ 'opacity-50 pointer-events-none scale-[0.98]': loading }">
            <!-- Grid Berita -->
            @if($beritas->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($beritas as $item)
                        <div class="group flex flex-col bg-white/80 rounded-3xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            
                            {{-- Image Header (Prominent) --}}
                            <div class="h-56 relative overflow-hidden bg-gray-100">
                                @if($item->image_path)
                                    <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-6xl opacity-50 bg-gradient-to-br from-blue-50 to-indigo-100">
                                        @if($item->type == 'Berita')  
                                        @elseif($item->type == 'Kegiatan') 
                                        @else 
                                        @endif
                                    </div>
                                @endif
                                
                                {{-- Type Badge --}}
                                <div class="absolute top-4 left-4">
                                    @if($item->type == 'Berita')
                                        <span class="px-3 py-1.5 bg-blue-500/90 backdrop-blur-md text-white rounded-full text-xs font-bold shadow-sm flex items-center gap-1.5"> Berita</span>
                                    @elseif($item->type == 'Kegiatan')
                                        <span class="px-3 py-1.5 bg-emerald-500/90 backdrop-blur-md text-white rounded-full text-xs font-bold shadow-sm flex items-center gap-1.5"> Kegiatan</span>
                                    @else
                                        <span class="px-3 py-1.5 bg-purple-500/90 backdrop-blur-md text-white rounded-full text-xs font-bold shadow-sm flex items-center gap-1.5"> Artikel</span>
                                    @endif
                                </div>

                                {{-- Status Badge --}}
                                <div class="absolute top-4 right-4">
                                    @if($item->is_active)
                                        <span class="px-2.5 py-1 bg-green-100/90 backdrop-blur-md text-green-700 rounded-full text-xs font-bold shadow-sm border border-green-200">Aktif</span>
                                    @else
                                        <span class="px-2.5 py-1 bg-gray-100/90 backdrop-blur-md text-gray-600 rounded-full text-xs font-bold shadow-sm border border-gray-200">Draft</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-3 uppercase tracking-wider">
                                    <span class="text-blue-600 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        {{ $item->created_at->format('d M Y') }}
                                    </span>
                                    <span></span>
                                    <span class="text-gray-500 truncate flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        {{ $item->region->name ?? 'Publik' }}
                                    </span>
                                </div>
                                
                                <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2 hover:text-blue-600 transition-colors cursor-pointer">{{ $item->title }}</h3>
                                
                                <p class="text-gray-600 line-clamp-3 mb-4 text-sm leading-relaxed">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($item->description), 120) }}
                                </p>

                                @if($item->event_date)
                                <div class="bg-indigo-50/50 rounded-xl p-3 mb-4 border border-indigo-50">
                                    <div class="flex items-center gap-2 text-indigo-700 font-bold text-[10px] mb-1 uppercase tracking-wider">
                                         Waktu Kegiatan
                                    </div>
                                    <div class="text-gray-800 font-semibold text-xs">
                                        {{ $item->event_date->format('d M Y, H:i') }} WIB
                                    </div>
                                </div>
                                @endif
                                
                                <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                                    <div class="text-xs text-gray-500 font-medium flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold">
                                            {{ substr($item->admin->name ?? 'S', 0, 1) }}
                                        </div>
                                        {{ $item->admin->name ?? 'Sistem' }}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button onclick="alert('Fitur Edit sedang disiapkan.')" class="p-2 text-blue-600 bg-gray-50 hover:bg-blue-500 hover:text-white rounded-lg transition-all" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button onclick="alert('Fitur Hapus sedang disiapkan.')" class="p-2 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($beritas->hasPages())
                <div class="mt-10 flex justify-center">
                    {{ $beritas->appends(request()->query())->links() }}
                </div>
                @endif
            @else
                <div class="backdrop-blur-sm bg-white/70 rounded-3xl text-center border border-white/80 shadow-lg py-16 px-6 max-w-3xl mx-auto">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-5 border border-gray-100">
                        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Belum ada berita</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">Saat ini belum ada berita atau artikel yang diterbitkan.</p>
                    <button @click.prevent="isFormOpen = true" class="inline-flex items-center justify-center px-6 py-3 rounded-full bg-blue-500 text-white font-medium hover:bg-blue-600 shadow-md transition-all">
                         Tulis Berita Baru
                    </button>
                </div>
            @endif
            </div>

            </div>

              <!-- Form Buat Berita -->
              <div x-show="isFormOpen" style="display: none;" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="max-w-3xl mx-auto pb-10">
                  <div class="bg-white rounded-3xl shadow-[0_4px_24px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden">
                        <form action="{{ route('wilayah.berita.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="pt-8 px-8 md:px-12">
                              <div class="relative text-center mb-10">
                                  <button type="button" @click="isFormOpen = false" class="absolute right-0 top-0 bg-slate-50 rounded-full p-2.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus:outline-none transition-all">
                                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                  </button>
                                  <h3 class="text-3xl font-bold text-gray-900">Tulis Berita Baru</h3>
                                  <p class="mt-2 text-sm text-gray-600">Buat dan bagikan berita kegiatan atau dokumentasi wilayah dengan mudah.</p>
                              </div>
                              
                              <div class="space-y-6">
                                  <!-- Judul -->
                                  <div>
                                      <label class="block text-[13px] font-semibold text-slate-600 mb-2">Judul Berita <span class="text-red-500">*</span></label>
                                      <input type="text" name="title" required class="w-full rounded-2xl border-0 bg-slate-50 py-3.5 px-4 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-100 transition-all" placeholder="Contoh: Warga RT 01 Adakan Lomba...">
                                  </div>
                                  
                                                                    <!-- Kategori & Target -->
                                  <input type="hidden" name="target_region_id" value="{{ auth()->user()->region_id }}">
                                  <div>
                                      <label class="block text-[13px] font-semibold text-slate-600 mb-2">Kategori Berita <span class="text-red-500">*</span></label>
                                      <div class="relative">
                                          <select name="type" required class="appearance-none w-full rounded-2xl border-0 bg-slate-50 py-3.5 px-4 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-blue-100 transition-all">
                                              <option value="" disabled selected>Pilih Kategori...</option>
                                              <option value="Berita"> Berita</option>
                                              <option value="Kegiatan"> Kegiatan</option>
                                              <option value="Artikel"> Artikel</option>
                                          </select>
                                          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                          </div>
                                      </div>
                                  </div>

                                  <!-- Upload Foto Utama -->
                                  <div>
                                      <label class="block text-[13px] font-semibold text-slate-600 mb-2">Foto Dokumentasi Berita <span class="text-slate-400 font-normal">(Opsional, bisa lebih dari 1 foto)</span></label>
                                        
                                        <div class="mt-2">
                                            <!-- Grid Preview -->
                                            <div id="multi-preview-grid" class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4" style="display: none;">
                                                <!-- Previews will be injected here -->
                                            </div>
                                            
                                            <!-- Hidden inputs container -->
                                            <div id="hidden-inputs-container"></div>
                                            
                                            <!-- Add Photo Box (KYC Style) -->
                                            <div class="mt-1 flex flex-col items-center justify-center p-6 border-2 border-gray-300 border-dashed rounded-xl relative hover:bg-gray-50 hover:border-gray-400 transition-all cursor-pointer group text-center overflow-hidden" onclick="addPhotoField()">
                                                <div class="space-y-1 text-center w-full">
                                                    <div class="mx-auto w-16 h-16 rounded-full bg-white flex items-center justify-center mb-3 shadow-sm text-slate-500 border border-slate-200 group-hover:scale-110 transition-transform duration-300">
                                                        <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                        </svg>
                                                    </div>
                                                    <div class="flex flex-col text-center">
                                                        <span class="font-bold text-gray-700 text-base">Tambah Foto</span>
                                                        <p class="text-xs text-gray-500 mt-1">PNG, JPG, JPEG up to 5MB. Bisa lebih dari satu.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <script>
                                        let photoCounter = 0;
                                        
                                        function addPhotoField() {
                                            photoCounter++;
                                            const id = 'img_upload_' + photoCounter;
                                            const previewId = 'preview_img_' + photoCounter;
                                            
                                            // Create hidden file input
                                            const input = document.createElement('input');
                                            input.type = 'file';
                                            input.name = 'images[]';
                                            input.accept = 'image/*';
                                            input.className = 'd-none sr-only';
                                            input.id = id;
                                            
                                            input.onchange = function() {
                                                if (this.files && this.files[0]) {
                                                    // Show grid if hidden
                                                    document.getElementById('multi-preview-grid').style.display = 'grid';
                                                    
                                                    // Create preview box
                                                    const grid = document.getElementById('multi-preview-grid');
                                                    const box = document.createElement('div');
                                                    box.className = 'w-full h-32 rounded-lg overflow-hidden border border-slate-200 shadow-sm relative group';
                                                    box.id = 'box_' + photoCounter;
                                                    
                                                    box.innerHTML = `
                                                        <img id="${previewId}" class="w-full h-full object-cover bg-gray-100" src="" alt="Preview">
                                                        <button type="button" onclick="removePhoto(${photoCounter})" class="absolute top-2 right-2 w-8 h-8 bg-red-500/80 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-opacity">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    `;
                                                    grid.appendChild(box);
                                                    
                                                    // Trigger cropper
                                                    if (typeof initGlobalCropper === 'function') {
                                                        initGlobalCropper(this, previewId, NaN, true); // Bebas ratio allowed
                                                    }
                                                } else {
                                                    // User canceled file dialog
                                                    this.remove();
                                                }
                                            };
                                            
                                            document.getElementById('hidden-inputs-container').appendChild(input);
                                            
                                            // Trigger file dialog
                                            input.click();
                                        }
                                        
                                        function removePhoto(idNum) {
                                            const input = document.getElementById('img_upload_' + idNum);
                                            const box = document.getElementById('box_' + idNum);
                                            if (input) input.remove();
                                            if (box) box.remove();
                                            
                                            const grid = document.getElementById('multi-preview-grid');
                                            if (grid.children.length === 0) {
                                                grid.style.display = 'none';
                                            }
                                        }
                                    </script>
                                    
    <!-- Deskripsi / Editor -->
                                  <div>
                                      <label class="block text-[13px] font-semibold text-slate-600 mb-2">Isi Berita <span class="text-red-500">*</span></label>
                                      <textarea name="description" rows="8" required class="w-full rounded-2xl border-0 bg-slate-50 py-4 px-4 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-100 transition-all" placeholder="Tuliskan isi berita di sini..."></textarea>
                                  </div>

                              </div>
                            </div>
                            <div class="mt-10 bg-slate-50 border-t border-slate-100 px-8 md:px-12 py-6 flex justify-end gap-3">
                              <button type="button" @click="isFormOpen = false" class="px-6 py-3 rounded-full border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 font-medium text-sm transition-colors shadow-sm">Batal</button>
                              <button type="submit" class="px-6 py-3 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm transition-all focus:ring-4 focus:ring-blue-100 shadow-sm hover:shadow-md">Terbitkan Berita</button>
                          </div>
                      </form>
                    </div>
                </div>
            </div>
      </section>
</main>
@endsection

@push('scripts')
<script>
    (() => {
        const registerBeritaAdmin = () => {
            if (!window.Alpine) return;
            window.Alpine.data('beritaAdmin', () => ({
                type: '{{ request('type', '') }}',
                search: '{{ request('search', '') }}',
                loading: false,
                isFormOpen: false,

                init() {
                    const container = document.getElementById('berita-list-container');
                    if (container) {
                        container.addEventListener('click', (e) => {
                            let link = e.target.closest('a');
                            if (link && link.href && link.href.includes('page=')) {
                                e.preventDefault();
                                this.fetchData(link.href);
                                window.scrollTo({ top: 100, behavior: 'smooth' });
                            }
                        });
                    }
                },

                updateFilter(newType) {
                    this.type = newType;
                    this.fetchData();
                },

                fetchData(urlOverride = null) {
                    this.loading = true;
                    
                    let url;
                    if (urlOverride) {
                        url = new URL(urlOverride);
                    } else {
                        url = new URL(window.location.origin + window.location.pathname);
                        if (this.type) url.searchParams.append('type', this.type);
                        if (this.search) url.searchParams.append('search', this.search);
                    }

                    fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(res => {
                        if (res.redirected && res.url.includes('/login')) {
                            window.location.href = res.url;
                            return null;
                        }
                        if (res.status === 401 || res.status === 419) {
                            window.location.reload();
                            return null;
                        }
                        return res.text();
                    })
                    .then(html => {
                        if (!html) return;
                        let parser = new DOMParser();
                        let doc = parser.parseFromString(html, 'text/html');
                        
                        let newContainer = doc.querySelector('#berita-list-container');
                        if (newContainer) {
                            document.querySelector('#berita-list-container').innerHTML = newContainer.innerHTML;
                        }
                        
                        window.history.pushState({}, '', url);
                        this.loading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.loading = false;
                    });
                }
            }));
        };

        if (window.Alpine) {
            registerBeritaAdmin();
        } else {
            document.addEventListener('alpine:init', registerBeritaAdmin);
        }

        document.addEventListener('livewire:navigated', () => {
            if (window.Alpine) {
                registerBeritaAdmin();
            }
        });
    })();
</script>
@endpush


















