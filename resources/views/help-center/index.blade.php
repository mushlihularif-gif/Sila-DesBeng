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
            <h1 class="text-4xl md:text-5xl font-bold text-yellow-400 mb-3">
                🆘 Pusat Bantuan
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
                        <span class="text-3xl">{{ $faqSection['icon'] }}</span>
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
            <h2 class="text-2xl font-bold text-yellow-400 mb-6 text-center">📞 Hubungi Kami</h2>
            
            <div class="grid md:grid-cols-2 gap-6">
                {{-- WhatsApp --}}
                <a href="https://wa.me/6285263158266" target="_blank"
                   class="flex items-center gap-4 bg-green-500/10 border border-green-400/20 rounded-lg p-4 hover:border-green-400/40 transition group">
                    <div class="text-4xl group-hover:scale-110 transition">💬</div>
                    <div>
                        <h4 class="font-bold text-green-400">WhatsApp</h4>
                        <p class="text-sm text-gray-300">+62 852-6315-8266</p>
                    </div>
                </a>

                {{-- Instagram --}}
                <a href="https://instagram.com/kelurahan_sungaipakning" target="_blank"
                   class="flex items-center gap-4 bg-pink-500/10 border border-pink-400/20 rounded-lg p-4 hover:border-pink-400/40 transition group">
                    <div class="text-4xl group-hover:scale-110 transition">📸</div>
                    <div>
                        <h4 class="font-bold text-pink-400">Instagram</h4>
                        <p class="text-sm text-gray-300">@Adhyaksacaturwardana</p>
                    </div>
                </a>

                {{-- Email --}}
                <a href="mailto:kelurahan@sungaipakning.id"
                   class="flex items-center gap-4 bg-blue-500/10 border border-blue-400/20 rounded-lg p-4 hover:border-blue-400/40 transition group">
                    <div class="text-4xl group-hover:scale-110 transition">📧</div>
                    <div>
                        <h4 class="font-bold text-blue-400">Email</h4>
                        <p class="text-sm text-gray-300">kelurahan@sungaipakning.id</p>
                    </div>
                </a>

                {{-- Phone --}}
                <a href="tel:+6285263158266"
                   class="flex items-center gap-4 bg-yellow-500/10 border border-yellow-400/20 rounded-lg p-4 hover:border-yellow-400/40 transition group">
                    <div class="text-4xl group-hover:scale-110 transition">📱</div>
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