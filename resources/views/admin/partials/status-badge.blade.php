@php
    $statusLabels = [
        'pending' => ['Menunggu', 'bg-warning-subtle text-warning border-warning-subtle', 'bx-time'],
        'approved' => ['Disetujui', 'bg-success-subtle text-success border-success-subtle', 'bx-check-circle'],
        'rejected' => ['Ditolak', 'bg-danger-subtle text-danger border-danger-subtle', 'bx-x-circle'],
        'cancelled' => ['Dibatalkan', 'bg-dark-subtle text-dark border-dark-subtle', 'bx-x'],
        'confirmed' => ['Dikonfirmasi', 'bg-info-subtle text-info border-info-subtle', 'bx-check-circle'],
        'being_prepared' => ['Dipersiapkan', 'bg-info-subtle text-info border-info-subtle', 'bx-package'],
        'in_delivery' => ['Diantar', 'bg-primary-subtle text-primary border-primary-subtle', 'bx-car'],
        'arrived' => ['Tiba', 'bg-primary-subtle text-primary border-primary-subtle', 'bx-map-pin'],
        'completed' => ['Selesai', 'bg-success-subtle text-success border-success-subtle', 'bx-check-double'],
        'resolved' => ['Selesai', 'bg-success-subtle text-success border-success-subtle', 'bx-check-double'],
        'returned' => ['Dikembalikan', 'bg-success-subtle text-success border-success-subtle', 'bx-check-double'],
    ];

    $badgeData = $statusLabels[$status] ?? [$status, 'bg-secondary-subtle text-secondary border-secondary-subtle', 'bx-info-circle'];
    $label = $badgeData[0];
    $class = $badgeData[1];
    $icon = $badgeData[2];

    if(isset($cancelStatus) && $cancelStatus == 'pending') {
        $label = 'Minta Batal';
        $class = 'bg-danger-subtle text-danger border-danger-subtle animate-pulse';
        $icon = 'bx-error-circle';
    }
@endphp

<span class="badge {{ $class }} border px-3 py-2 rounded-pill fw-normal shadow-sm">
    <i class="bx {{ $icon }} me-1"></i> {{ $label }}
</span>
