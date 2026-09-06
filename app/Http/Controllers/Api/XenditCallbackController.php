<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GasOrder;
use App\Models\RentalBooking;
use App\Models\WalletTransaction;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Penerima notifikasi pembayaran dari Xendit.
 *
 * Berbeda dari Midtrans yang SDK-nya menanyakan balik status ke servernya,
 * Xendit hanya mengirim JSON. Karena itu keaslian permintaan WAJIB diperiksa
 * lewat header X-CALLBACK-TOKEN sebelum satu baris pun datanya dipercaya —
 * tanpa itu siapa pun bisa mengirim "lunas" palsu ke endpoint ini.
 */
class XenditCallbackController extends Controller
{
    public function __construct(private XenditService $xendit)
    {
    }

    public function handle(Request $request)
    {
        if (! $this->xendit->callbackSah($request->header('x-callback-token'))) {
            Log::warning('Xendit callback DITOLAK: token tidak cocok', [
                'ip'   => $request->ip(),
                'jenis' => $request->input('event') ?? $request->input('status'),
            ]);

            return response()->json(['message' => 'Invalid callback token'], 401);
        }

        // Xendit memakai external_id untuk VA dan reference_id untuk QRIS.
        $externalId = $request->input('external_id')
            ?? $request->input('reference_id')
            ?? $request->input('data.reference_id');

        if (! $externalId) {
            Log::warning('Xendit callback tanpa external_id/reference_id', $request->all());

            return response()->json(['message' => 'Missing reference'], 422);
        }

        // QRIS memakai akhiran -QR saat dibuat; kembalikan ke nomor pesanan asli.
        $nomorPesanan = preg_replace('/-QR$/', '', $externalId);

        $pesanan = $this->cariPesanan($nomorPesanan);

        if (! $pesanan) {
            Log::warning("Xendit callback: pesanan {$nomorPesanan} tidak ditemukan");

            // 200 supaya Xendit berhenti mencoba ulang untuk referensi yang
            // memang bukan milik kita.
            return response()->json(['message' => 'Order not found'], 200);
        }

        [$model, $jenis] = $pesanan;

        $statusBaru = $this->petakanStatus($request);

        if ($statusBaru === null) {
            Log::info("Xendit callback: status diabaikan untuk {$nomorPesanan}", [
                'status' => $request->input('status'),
            ]);

            return response()->json(['message' => 'Ignored'], 200);
        }

        $model->status = $statusBaru;

        if ($statusBaru === 'confirmed' && ! $model->confirmed_at) {
            $model->confirmed_at = now();
        }

        $model->save();

        $this->sinkronkanBukuBesar($model, $jenis, $statusBaru);

        Log::info("Xendit callback: {$nomorPesanan} -> {$statusBaru}");

        return response()->json(['message' => 'OK'], 200);
    }

    /** @return array{0:mixed,1:string}|null */
    private function cariPesanan(string $nomor): ?array
    {
        if (str_starts_with($nomor, 'GAS-')) {
            $m = GasOrder::where('order_number', $nomor)->first();

            return $m ? [$m, 'gas'] : null;
        }

        if (str_starts_with($nomor, 'RNTL-')) {
            $m = RentalBooking::where('booking_number', $nomor)->first();

            return $m ? [$m, 'rental'] : null;
        }

        return null;
    }

    /**
     * Status Xendit -> status pesanan.
     * Mengembalikan null untuk kejadian yang tidak perlu mengubah apa pun.
     */
    private function petakanStatus(Request $request): ?string
    {
        $status = strtoupper((string) ($request->input('status') ?? $request->input('data.status') ?? ''));

        return match ($status) {
            // VA terbayar penuh, QRIS sukses
            'PAID', 'SUCCEEDED', 'COMPLETED', 'ACTIVE_PAID' => 'confirmed',
            'EXPIRED', 'FAILED', 'INACTIVE'                 => 'cancelled',
            default                                          => null,
        };
    }

    /**
     * Selaraskan buku besar dengan hasil pembayaran.
     *
     * Dana tetap berstatus "ditahan" sampai pesanan dinyatakan selesai —
     * callback ini hanya memastikan uangnya benar-benar sudah masuk.
     */
    private function sinkronkanBukuBesar($model, string $jenis, string $statusBaru): void
    {
        // Menyentuh SELURUH baris pesanan ini, bukan latest()->first(): pesanan
        // pada unit yang dikelola mitra punya dua baris (porsi wilayah + porsi
        // mitra), dan dulu hanya satu yang ikut berubah.
        if ($statusBaru === 'confirmed') {
            WalletTransaction::where('reference_type', $jenis)
                ->where('reference_id', $model->id)
                ->where('source', 'gateway')
                ->where('status', 'pending')
                ->update(['status' => 'verified']);

            return;
        }

        // Kedaluwarsa/gagal. Kalau uangnya terlanjur terbayar, dikembalikan ke
        // dompet warga — bukan dihilangkan begitu saja dari pembukuan.
        WalletTransaction::batalkanDanRefund(
            $jenis,
            $model->id,
            'Pembayaran dibatalkan/kedaluwarsa di Xendit.'
        );
    }
}
