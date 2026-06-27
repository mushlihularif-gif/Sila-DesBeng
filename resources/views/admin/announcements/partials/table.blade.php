<div class="table-responsive text-nowrap">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Tipe</th>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Wilayah</th>
                <th>Tanggal Event</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse($announcements as $item)
            <tr>
                <td>
                    @if($item->type == 'Gotong Royong')
                        <span class="badge bg-label-success"><i class="bx bx-run me-1"></i> Gotong Royong</span>
                    @elseif($item->type == 'Event')
                        <span class="badge bg-label-warning"><i class="bx bx-calendar-event me-1"></i> Event</span>
                    @else
                        <span class="badge bg-label-info"><i class="bx bx-bell me-1"></i> Pengumuman</span>
                    @endif
                </td>
                <td>
                    <strong>{{ \Illuminate\Support\Str::limit($item->title, 40) }}</strong>
                    @if($item->laporan_id)
                        <br><small class="text-muted"><i class="bx bx-link"></i> Terhubung Laporan #{{ $item->laporan_id }}</small>
                    @endif
                </td>
                <td>{{ $item->admin->name ?? 'Sistem' }}</td>
                <td>{{ $item->region->name ?? 'Semua Wilayah' }}</td>
                <td>{{ $item->event_date ? $item->event_date->format('d M Y H:i') : '-' }}</td>
                <td>
                    <span class="badge bg-label-{{ $item->is_active ? 'primary' : 'secondary' }}">
                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('admin.announcements.edit', $item->id) }}" class="btn btn-sm btn-info">Edit</a>
                    <form action="{{ route('admin.announcements.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
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
    {{ $announcements->appends(request()->except('page'))->links() }}
</div>
