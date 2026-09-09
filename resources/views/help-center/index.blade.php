@extends('layouts.app')

@section('title', 'Pusat Bantuan')

@push('styles')
<style>
    .ornamen-melayu {
        background-image: 
            repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(250,204,21,0.03) 10px, rgba(250,204,21,0.03) 20px),
            repeating-linear-gradient(-45deg, transparent, transparent 10px, rgba(250,204,21,0.03) 10px, rgba(250,204,21,0.03) 20px);
    }

    .card-melayu {
        position: relative;
        overflow: hidden;
    }
    
    .card-melayu::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, transparent, #facc15, transparent);
    }

    .faq-item {
        transition: all 0.3s;
    }

    .faq-item:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-b from-[#004635] to-[#003026] py-10 ornamen-melayu">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 text-center" data-aos="fade-down">
            <h1 class="text-4xl md:text-5xl font-bold text-yellow-400 mb-3 flex items-center justify-center gap-3">
                <i class="fas fa-life-ring"></i> Pusat Bantuan
            </h1>
            <p class="text-gray-300 text-lg">
                Butuh bantuan? Kami siap membantu Anda!
            </p>
            <div class="mt-4 h-1 bg-gradient-to-r from-transparent via-yellow-400 to-transparent rounded-full max-w-md mx-auto"></div>
        </div>


        @foreach($faqs as $faqSection)
        <div class="mb-8" data-aos="fade-up">
            <div class="card-melayu bg-gradient-to-br from-[#003b2f]/60 to-[#004635]/60 backdrop-blur border border-yellow-400/20 rounded-xl overflow-hidden">
                <div class="bg-gradient-to-r from-yellow-400/20 to-transparent px-6 py-4 border-b border-yellow-400/20">
                    <h2 class="text-2xl font-bold text-yellow-400 flex items-center gap-3">
                        @if(!empty($faqSection['icon']))
                        <i class="{{ $faqSection['icon'] }} text-2xl"></i>
                        @endif
                        {{ $faqSection['category'] }}
                    </h2>
                </div>
                
                <div class="p-6 space-y-4">
                    @foreach($faqSection['items'] as $faq)
                    <div class="faq-item bg-[#004635]/40 border border-yellow-400/10 rounded-lg p-4 hover:border-yellow-400/30">
                        <h3 class="font-bold text-white mb-2 flex items-start gap-2">
                            <span class="text-yellow-400">Q:</span>
                            {{ $faq['question'] }}
                        </h3>
                        <p class="text-gray-300 text-sm pl-6">
                            <span class="text-green-400 font-bold">A:</span>
                            {{ $faq['answer'] }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        <div class="card-melayu bg-gradient-to-br from-[#003b2f]/60 to-[#004635]/60 backdrop-blur border border-yellow-400/20 rounded-xl p-8" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-yellow-400 mb-6 text-center flex items-center justify-center gap-2.5"><i class="fas fa-headset"></i> Hubungi Kami</h2>
            
            <div class="grid md:grid-cols-2 gap-6">
                {{-- WhatsApp --}}
                <a href="https://wa.me/6285263158266" target="_blank"
                   class="flex items-center gap-4 bg-green-500/10 border border-green-400/20 rounded-lg p-4 hover:border-green-400/40 transition group">
                    <div class="group-hover:scale-110 transition text-green-400"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg></div>
                    <div>
                        <h4 class="font-bold text-green-400">WhatsApp</h4>
                        <p class="text-sm text-gray-300">+62 852-6315-8266</p>
                    </div>
                </a>

                {{-- Instagram --}}
                <a href="https://instagram.com/kelurahan_sungaipakning" target="_blank"
                   class="flex items-center gap-4 bg-pink-500/10 border border-pink-400/20 rounded-lg p-4 hover:border-pink-400/40 transition group">
                    <div class="group-hover:scale-110 transition text-pink-400"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></div>
                    <div>
                        <h4 class="font-bold text-pink-400">Instagram</h4>
                        <p class="text-sm text-gray-300">@Adhyaksacaturwardana</p>
                    </div>
                </a>

                {{-- Email --}}
                <a href="mailto:kelurahan@sungaipakning.id"
                   class="flex items-center gap-4 bg-blue-500/10 border border-blue-400/20 rounded-lg p-4 hover:border-blue-400/40 transition group">
                    <div class="group-hover:scale-110 transition text-blue-400"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></div>
                    <div>
                        <h4 class="font-bold text-blue-400">Email</h4>
                        <p class="text-sm text-gray-300">kelurahan@sungaipakning.id</p>
                    </div>
                </a>

                {{-- Phone --}}
                <a href="tel:+6285263158266"
                   class="flex items-center gap-4 bg-yellow-500/10 border border-yellow-400/20 rounded-lg p-4 hover:border-yellow-400/40 transition group">
                    <div class="group-hover:scale-110 transition text-yellow-400"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg></div>
                    <div>
                        <h4 class="font-bold text-yellow-400">Telepon</h4>
                        <p class="text-sm text-gray-300">+62 852-6315-8266</p>
                    </div>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection