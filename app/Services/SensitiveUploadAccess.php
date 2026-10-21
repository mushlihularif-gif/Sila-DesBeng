<?php

namespace App\Services;

use App\Models\{Laporan, PasarComplaint, HelpTicket, PartnerApplication, Region, ManualReport};

class SensitiveUploadAccess
{
    public const PREFIXES = ['receipts/', 'payment_proofs/', 'delivery_proofs/', 'surat_pengantar/',
        'laporan/', 'pasar_orders/proofs/', 'pasar_complaints/', 'pasar/complaints/',
        'help-tickets/', 'partner_applications/', 'manual-reports/'];

    public function authorize(string $path): void
    {
        if (!collect(self::PREFIXES)->contains(fn ($prefix) => str_starts_with($path, $prefix))) return;
        $user = auth()->user() ?: auth('sanctum')->user();
        abort_unless($user, 401);

        if (str_starts_with($path, 'laporan/')) {
            foreach (Laporan::where('bukti', 'like', '%' . $path . '%')->cursor() as $report) {
                if (in_array($path, $report->bukti_array, true)) {
                    abort_unless(app(ReportAccess::class)->canView($report, $user), 403);
                    return;
                }
            }
            abort(404);
        }
        if (str_starts_with($path, 'help-tickets/')) {
            $ticket = HelpTicket::where('screenshot', $path)->firstOrFail();
            abort_unless((int) $ticket->user_id === (int) $user->id || $user->role === 'admin', 403);
            return;
        }
        if (str_starts_with($path, 'partner_applications/')) {
            $application = PartnerApplication::where('document_path', $path)->firstOrFail();
            if ((int) $application->user_id === (int) $user->id || $user->role === 'super_admin') return;
            abort_unless(in_array($user->role, ['admin', 'admin_kecamatan', 'admin_desa', 'admin_rw'], true)
                && $user->region_id
                && in_array($application->parent_region_id, array_merge([$user->region_id], Region::getDescendantIds($user->region_id))), 403);
            return;
        }
        if (str_starts_with($path, 'manual-reports/')) {
            $report = ManualReport::where('proof_image', $path)->firstOrFail();
            $permission = ['gas' => 'gas', 'penyewaan' => 'sewa_alat', 'mobil' => 'sewa_mobil',
                'fasilitas' => 'fasilitas_umum', 'pasar' => 'pasar_daerah'][$report->category] ?? null;
            abort_unless($user->isAdmin() && $user->region_id
                && (int) $user->region_id === (int) $report->region_id
                && ($permission ? $user->hasUnitPermission($permission) : $user->role !== 'staff'), 403);
            return;
        }
        if (str_starts_with($path, 'pasar_complaints/') || str_starts_with($path, 'pasar/complaints/')) {
            $complaint = PasarComplaint::where('evidence_1', $path)->orWhere('evidence_2', $path)->orWhere('evidence_3', $path)->orWhere('evidence_4', $path)->orWhere('evidence_5', $path)->orWhere('evidence_video', $path)->firstOrFail();
            $order = \App\Models\PasarOrder::withTrashed()->findOrFail($complaint->pasar_order_id);
            abort_unless(app(BookingAccess::class)->canView($order, 'pasar', $user), 403);
            return;
        }

        foreach (BookingAccess::TYPES as $type => [$model]) {
            $columns = ['receipt_path', in_array($type, ['gas', 'pasar'], true) ? 'proof_of_payment' : 'payment_proof'];
            if (in_array($type, ['rental', 'gas', 'mobil', 'pasar'], true)) $columns[] = 'delivery_proof_image';
            if ($type === 'fasilitas') $columns[] = 'surat_pengantar';
            $booking = $model::withTrashed()->where(function ($q) use ($columns, $path) {
                foreach ($columns as $column) $q->orWhere($column, $path);
            })->first();
            if ($booking) {
                abort_unless(app(BookingAccess::class)->canView($booking, $type, $user), 403);
                return;
            }
        }
        abort(404);
    }
}
