@extends('admin.layouts.admin')

@section('title', 'Permintaan Pengajuan')

@section('content')
<style>
    .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
        background-color: #0095ff !important;
        color: #fff !important;
        box-shadow: 0 4px 10px rgba(0, 149, 255, 0.3) !important;
    }
    .nav-pills .nav-link {
        color: #64748b;
        transition: all 0.3s ease;
    }
    .nav-pills .nav-link:hover {
        background-color: #eff6ff !important;
        color: #0095ff !important;
    }
    .nav-pills .nav-link.active .badge.bg-white {
        color: #0095ff !important;
    }
</style>
<div class="container-fluid py-4">
    @php
        $activeTab = request('tab', 'rental');
    @endphp
    
    <!-- Judul Header Halaman -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold fs-3 mb-1 text-primary">Permintaan Pengajuan</h4>
            <p class="text-muted mb-0">Kelola dan pantau seluruh aktivitas pesanan masuk</p>
        </div>
        <div class="d-flex gap-2 position-relative" style="z-index: 1050;">
            <button class="btn btn-white border shadow-sm rounded-pill px-4" onclick="location.reload()">
                <i class="bx bx-refresh me-2"></i>Refresh
            </button>
        </div>
    </div>

    <div id="requests-container">
        @include('admin.aktivitas.partials.requests_content')
    </div>
</div>
            } else {
                container.innerHTML = '';
                container.classList.add('d-none');
            }
        }

        // Check initially and poll every 15 seconds
        setInterval(checkNotificationCounts, 15000);
    });
</script>
@endsection
