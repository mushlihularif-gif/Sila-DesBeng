@extends('admin.layouts.admin')

@section('title', 'Hasil Pencarian')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Search Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden; background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <!-- SVG Search Illustration -->
                                <div class="search-illustration">
                                    <svg width="80" height="80" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="40" cy="40" r="25" stroke="white" stroke-width="6" fill="none" opacity="0.9"/>
                                        <line x1="58" y1="58" x2="75" y2="75" stroke="white" stroke-width="6" stroke-linecap="round" opacity="0.9"/>
                                        <circle cx="40" cy="40" r="15" fill="white" opacity="0.3"/>
                                        <circle cx="35" cy="35" r="5" fill="white" opacity="0.6"/>
                                    </svg>
                                </div>
                                <div class="text-white">
                                    <h4 class="mb-1 fw-bold text-white">Hasil Pencarian</h4>
                                    <p class="mb-0" style="opacity: 0.95;">
                                        Ditemukan <span class="badge bg-white text-primary rounded-pill px-3 py-1 fw-bold">{{ $totalResults }}</span> hasil untuk 
                                        "<strong>{{ $search }}</strong>"
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-light rounded-pill px-4 shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($totalResults > 0)
            <!-- Results Grid -->
            <div class="row g-4">
                @foreach($results as $result)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm result-card" style="border-radius: 20px; overflow: hidden; transition: all 0.3s ease;">
                            <div class="card-body p-0">
                                <!-- Image Section -->
                                @if($result['image'])
                                    <div class="position-relative" style="height: 250px; overflow: hidden; background: #f8f9fa;">
                                        <img src="{{ asset('storage/' . $result['image']) }}" 
                                             alt="{{ $result['title'] }}" 
                                             class="w-100 h-100"
                                             style="object-fit: contain; object-position: center; padding: 10px;">
                                        <!-- Badge Overlay -->
                                        <div class="position-absolute top-0 start-0 m-3">
                                            <span class="badge bg-{{ $result['badge_color'] }} shadow-sm px-3 py-2" style="border-radius: 10px; font-size: 11px; font-weight: 600; letter-spacing: 0.5px;">
                                                {{ $result['badge'] }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <!-- Placeholder for items without image -->
                                    <div class="position-relative d-flex align-items-center justify-content-center" style="height: 250px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        <i class="bx bx-{{ $result['type'] == 'user' ? 'user' : ($result['type'] == 'bumdes_member' ? 'group' : 'receipt') }} text-white" style="font-size: 80px; opacity: 0.3;"></i>
                                        <!-- Badge Overlay -->
                                        <div class="position-absolute top-0 start-0 m-3">
                                            <span class="badge bg-{{ $result['badge_color'] }} shadow-sm px-3 py-2" style="border-radius: 10px; font-size: 11px; font-weight: 600; letter-spacing: 0.5px;">
                                                {{ $result['badge'] }}
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                <!-- Content Section -->
                                <div class="p-4">
                                    <h5 class="card-title mb-2 fw-bold" style="color: #2c3e50; font-size: 18px; line-height: 1.4;">
                                        {{ Str::limit($result['title'], 50) }}
                                    </h5>
                                    <p class="text-muted small mb-2" style="font-size: 13px;">
                                        <i class="bx bx-info-circle me-1"></i>{{ $result['subtitle'] }}
                                    </p>
                                    
                                    @if($result['description'])
                                        <p class="card-text text-muted mb-3" style="font-size: 13px; line-height: 1.6;">
                                            {{ Str::limit($result['description'], 80) }}
                                        </p>
                                    @endif

                                    <!-- Action Button -->
                                    <a href="{{ $result['link'] }}" class="btn btn-primary w-100 rounded-pill py-2 btn-hover" style="font-weight: 600; letter-spacing: 0.3px;">
                                        <i class="bx bx-right-arrow-alt me-2"></i>Lihat Halaman
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                        <div class="card-body text-center py-5">
                            <div class="empty-state-icon mb-4">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                                    <i class="bx bx-search-alt" style="font-size: 80px; color: #cbd5e0;"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold mb-3" style="color: #2c3e50;">Tidak Ditemukan</h3>
                            <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto; font-size: 15px; line-height: 1.6;">
                                Tidak ada hasil yang cocok dengan pencarian "<strong class="text-primary">{{ $search }}</strong>". 
                                Coba gunakan kata kunci lain atau periksa ejaan Anda.
                            </p>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                                <i class="bx bx-home me-2"></i>Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        /* SVG Search Illustration Animation */
        .search-illustration svg {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .search-illustration circle:first-child {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 0.9;
            }
            50% {
                opacity: 0.5;
            }
        }

        /* Card Hover Effects */
        .result-card {
            transform: translateY(0);
        }
        
        .result-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
        }

        /* Button Hover Effect */
        .btn-hover {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 123, 255, 0.3);
        }

        .btn-hover::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-hover:hover::before {
            width: 300px;
            height: 300px;
        }

        /* Smooth Animations */
        .result-card {
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Stagger animation for cards */
        .result-card:nth-child(1) { animation-delay: 0.1s; }
        .result-card:nth-child(2) { animation-delay: 0.2s; }
        .result-card:nth-child(3) { animation-delay: 0.3s; }
        .result-card:nth-child(4) { animation-delay: 0.4s; }
        .result-card:nth-child(5) { animation-delay: 0.5s; }
        .result-card:nth-child(6) { animation-delay: 0.6s; }
        .result-card:nth-child(7) { animation-delay: 0.7s; }
        .result-card:nth-child(8) { animation-delay: 0.8s; }
        .result-card:nth-child(9) { animation-delay: 0.9s; }
        .result-card:nth-child(10) { animation-delay: 1.0s; }
        .result-card:nth-child(11) { animation-delay: 1.1s; }
        .result-card:nth-child(12) { animation-delay: 1.2s; }

        /* Image hover effect */
        .result-card img {
            transition: transform 0.5s ease;
        }

        .result-card:hover img {
            transform: scale(1.1);
        }

        /* Badge styling */
        .badge {
            text-transform: uppercase;
        }
    </style>
@endsection
