<div class="table-responsive text-nowrap">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Tipe</th>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Target Pembaca</th>
                <th>Tanggal Event</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse($announcements as $item)
            <tr>
                <td>
                    @if($category === 'Berita')
                        <span class="badge bg-label-primary"><i class="bx bx-news me-1"></i> Berita Daerah</span>
                    @else
                        @if($item->type == 'Gotong Royong')
                            <span class="badge bg-label-success"><i class="bx bx-run me-1"></i> Gotong Royong</span>
                        @elseif($item->type == 'Event')
                            <span class="badge bg-label-warning"><i class="bx bx-calendar-event me-1"></i> Event</span>
                        @else
                            <span class="badge bg-label-info"><i class="bx bx-bell me-1"></i> Pengumuman</span>
                        @endif
                    @endif
                </td>
                <td>
                    <strong>{{ \Illuminate\Support\Str::limit($item->title, 40) }}</strong>
                    @if($item->laporan_id)
                        <br><small class="text-muted"><i class="bx bx-link"></i> Terhubung Laporan #{{ $item->laporan_id }}</small>
                    @endif
                </td>
                <td>{{ $item->admin->name ?? 'Sistem' }}</td>
                <td>
                    @if($item->target_audience_type === 'all')
                        <span class="badge bg-primary">Global (Se-Kabupaten)</span>
                    @else
                        {{ $item->targetRegion->name ?? 'Tidak diketahui' }}
                    @endif
                </td>
                <td>{{ $item->event_date ? $item->event_date->format('d M Y H:i') : '-' }}</td>
                <td>
                    <span class="badge bg-label-{{ $item->is_active ? 'primary' : 'secondary' }}">
                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.announcements.edit', $item->id) }}" class="btn btn-sm btn-info rounded-pill px-3 shadow-sm">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <form action="{{ route('admin.announcements.destroy', $item->id) }}" method="POST" class="d-inline" data-konfirmasi="Apakah Anda yakin ingin menghapus ini?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm">
                                <i class="bx bx-trash me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Belum ada pengumuman atau event.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="card-footer" id="pagination-links">
    {{ $announcements->appends(request()->query())->links() }}
</div>
