@extends('admin.layouts.admin')

@section('title', 'Profil BUMDes')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- HEADER -->
            <h2 class="text-primary fw-bold mb-4">Struktur <span class="text-info">Pemerintahan dan BUMDes</span></h2>

            <!-- GRID MEMBERS -->
            <div class="row g-4">
                @foreach($members as $member)
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 rounded-4 p-3 text-center" style="background: linear-gradient(135deg, #e6f2ff 0%, #ffffff 100%);">
                            <div class="avatar-container mb-3">
                                <img src="{{ $member->photo_url }}" 
                                     alt="{{ $member->name }}"
                                     class="rounded-circle border border-3 border-info" 
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                            <h5 class="fw-bold text-dark mb-1">{{ $member->name }}</h5>
                            <p class="text-muted mb-0" style="font-size: 14px;">{{ $member->position }}</p>
                            
                            <!-- ACTION BUTTONS -->
                            <div class="mt-3">
                                <a href="{{ route('admin.isewa.bumdes.edit', $member->id) }}" class="btn btn-sm btn-outline-primary me-2">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('admin.isewa.bumdes.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus {{ $member->name }}?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- TOMBOL TAMBAH ANGGOTA -->
            <div class="mt-4 text-center">
                <a href="{{ route('admin.isewa.bumdes.create') }}" class="btn btn-primary px-4 py-2 fw-bold">
                    <i class="bi bi-plus"></i> Tambah Anggota
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Efek animasi saat scroll
        const sections = document.querySelectorAll('.card, .row');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        sections.forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(30px)';
            section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(section);
        });

        // Sidebar Active
        const sidebarLink = document.querySelector('a[href="{{ route("admin.isewa.profile-bumdes") }}"]');
        if (sidebarLink) {
            const listItem = sidebarLink.closest('li');
            if (listItem) {
                listItem.classList.add('active');
            }
        }
    });
</script>
@endsection