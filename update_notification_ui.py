import os

file_path = r"D:\laragon\www\SilaDesBeng\resources\views\admin\dashboard\index.blade.php"

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Using regex or exact string to replace the table with a modern list
import re

start_str = '<div class="table-responsive">'
end_str = '</table>\n                                </div>'
start_idx = content.find(start_str)
end_idx = content.find(end_str) + len(end_str)

if start_idx != -1 and end_idx != -1:
    old_section = content[start_idx:end_idx]
    
    new_section = """<div class="list-group list-group-flush border-0">
                                    @forelse($latestRequests as $request)
                                        @php
                                            $icon = 'bx-bell';
                                            $bgClass = 'bg-primary-subtle text-primary';
                                            $badgeClass = 'bg-primary text-white';
                                            $serviceName = 'Umum';
                                            $detailLink = route('admin.aktivitas.permintaan-pengajuan.show', [$request->id, $request->type ?? 'rental']);
                                            $canQuickAction = in_array($request->type, ['rental', 'gas', 'mobil', 'fasilitas_umum']);
                                            
                                            if ($request->type == 'rental') {
                                                $icon = 'bx-wrench'; $bgClass = 'bg-warning-subtle text-warning'; $badgeClass = 'bg-warning text-white'; $serviceName = 'Penyewaan Alat';
                                            } elseif ($request->type == 'gas') {
                                                $icon = 'bxs-gas-pump'; $bgClass = 'bg-danger-subtle text-danger'; $badgeClass = 'bg-danger text-white'; $serviceName = 'Penjualan Gas';
                                            } elseif ($request->type == 'mobil') {
                                                $icon = 'bx-car'; $bgClass = 'bg-info-subtle text-info'; $badgeClass = 'bg-info text-white'; $serviceName = 'Penyewaan Mobil';
                                            } elseif ($request->type == 'fasilitas_umum') {
                                                $icon = 'bx-building-house'; $bgClass = 'bg-success-subtle text-success'; $badgeClass = 'bg-success text-white'; $serviceName = 'Fasilitas Umum';
                                            } elseif ($request->type == 'pasar_daerah') {
                                                $icon = 'bx-store-alt'; $bgClass = 'bg-primary-subtle text-primary'; $badgeClass = 'bg-primary text-white'; $serviceName = 'Pasar Daerah';
                                                $detailLink = Route::has('admin.unit.pasar_daerah.pesanan.show') ? route('admin.unit.pasar_daerah.pesanan.show', $request->id) : '#';
                                            } elseif ($request->type == 'laporan') {
                                                $icon = 'bx-message-error'; $bgClass = 'bg-dark-subtle text-dark'; $badgeClass = 'bg-dark text-white'; $serviceName = 'Pelaporan Warga';
                                                $detailLink = Route::has('admin.laporan.show') ? route('admin.laporan.show', $request->id) : '#';
                                            }
                                            
                                            $requestName = $request->full_name ?? $request->recipient_name ?? $request->user->name ?? 'User';
                                        @endphp
                                        
                                        <div class="list-group-item list-group-item-action d-flex align-items-center p-4 border-bottom-0 border-top" style="gap: 1.25rem; transition: all 0.2s ease;">
                                            <!-- Icon / Avatar -->
                                            <div class="avatar flex-shrink-0" style="width: 48px; height: 48px;">
                                                <span class="avatar-initial rounded-circle {{ $bgClass }} shadow-sm">
                                                    <i class="bx {{ $icon }} fs-4"></i>
                                                </span>
                                            </div>
                                            
                                            <!-- Info -->
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="d-flex align-items-center mb-1 gap-2 flex-wrap">
                                                    <h6 class="mb-0 fw-bold text-dark text-truncate">{{ $request->item_name }}</h6>
                                                    <span class="badge {{ $badgeClass }} rounded-pill px-2 shadow-sm" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                                        {{ strtoupper($serviceName) }}
                                                    </span>
                                                    @if(isset($request->cancellation_status) && $request->cancellation_status == 'pending')
                                                        <span class="badge bg-danger rounded-pill px-2 shadow-sm" style="font-size: 0.65rem; letter-spacing: 0.5px;"><i class="bx bx-error-circle me-1"></i>MINTA BATAL</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center text-muted small gap-3 flex-wrap">
                                                    <span class="d-flex align-items-center"><i class="bx bx-user me-1 text-secondary"></i> <span class="fw-medium">{{ $requestName }}</span></span>
                                                    <span class="d-flex align-items-center"><i class="bx bx-time-five me-1 text-secondary"></i> {{ $request->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="d-flex gap-2 flex-shrink-0">
                                                <a href="{{ $detailLink }}" class="btn btn-sm btn-white rounded-pill px-3 shadow-sm text-primary fw-bold border">
                                                    Lihat Detail <i class="bx bx-right-arrow-alt ms-1"></i>
                                                </a>
                                                
                                                @if($canQuickAction)
                                                    @if(isset($request->cancellation_status) && $request->cancellation_status == 'pending')
                                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-bold" onclick="handleCancellation({{ $request->id }}, '{{ $request->type }}', 'approve')">
                                                            <i class="bx bx-check me-1"></i> Setujui Batal
                                                        </button>
                                                    @elseif($request->status == 'pending' || $request->status == 'waiting')
                                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-bold" onclick="approveRequest({{ $request->id }}, '{{ $request->type }}')">
                                                            <i class="bx bx-check me-1"></i> Proses
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-5 text-center">
                                            <div class="bg-label-primary rounded-circle d-inline-flex p-4 mb-3 shadow-sm">
                                                <i class="bx bx-check-shield fs-1 text-primary"></i>
                                            </div>
                                            <h5 class="fw-bold text-dark mb-1">Wah, Semua Beres!</h5>
                                            <p class="text-muted mb-0">Tidak ada notifikasi permintaan baru untuk saat ini.</p>
                                        </div>
                                    @endforelse
                                </div>"""
    
    content = content.replace(old_section, new_section)
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print("Dashboard UI updated successfully!")
else:
    print("Could not find the target section to replace.")
